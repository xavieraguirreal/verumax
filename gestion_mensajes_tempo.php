<?php
// Sistema de Formación SAJUR - Gestión de Mensajes
// Versión: 2.0.0 - Mejorada con Editor WYSIWYG y mejor UX

require_once 'config.php';
mb_internal_encoding("UTF-8");

// Conexión a la Base de Datos
$conn = getDBConnection();

// Variables
$action = $_GET['action'] ?? 'list';
$id_mensaje_get = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;
$feedback_msg = isset($_GET['feedback_msg']) ? urldecode($_GET['feedback_msg']) : '';
$error_msg = isset($_GET['error_msg']) ? urldecode($_GET['error_msg']) : '';

// Lógica de Procesamiento POST (Crear/Actualizar Mensajes)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $post_action = $_POST['form_action'] ?? '';
    $id_mensaje_post = isset($_POST['id_mensaje']) ? filter_var($_POST['id_mensaje'], FILTER_VALIDATE_INT) : null;

    $asunto = trim($_POST['asunto_mensaje'] ?? '');
    $cuerpo = $_POST['cuerpo_mensaje'] ?? '';
    $tipo_destinatario = $_POST['tipo_destinatario'] ?? '';
    $fecha_programada_raw = trim($_POST['fecha_programada_envio'] ?? '');
    $fecha_programada = !empty($fecha_programada_raw) ? $fecha_programada_raw : null;

    // NUEVOS CAMPOS
    $notificar_mensaje = isset($_POST['notificar_mensaje']) ? 1 : 0;
    $es_html = isset($_POST['es_html']) ? 1 : 0;

    // Inclusiones
    $ids_formaciones_seleccionadas = $_POST['ids_formaciones'] ?? [];
    $ids_estudiantes_seleccionados = $_POST['ids_estudiantes'] ?? [];

    // Validación de datos
    $errors_post = [];
    if (empty($asunto)) $errors_post[] = "El asunto del mensaje es obligatorio.";
    if (empty($cuerpo)) $errors_post[] = "El cuerpo del mensaje es obligatorio.";
    if (empty($tipo_destinatario)) {
        $errors_post[] = "Debe seleccionar un tipo de destinatario.";
    } elseif (!in_array($tipo_destinatario, ['todos', 'formacion_especifica', 'estudiantes_seleccionados'])) {
        $errors_post[] = "Tipo de destinatario inválido.";
    }

    // Si marca notificar, debe tener destinatarios válidos
    if ($notificar_mensaje) {
        if ($tipo_destinatario == 'estudiantes_seleccionados' && empty($ids_estudiantes_seleccionados)) {
            $errors_post[] = "Debe seleccionar al menos un estudiante para notificar.";
        }
        if ($tipo_destinatario == 'formacion_especifica' && empty($ids_formaciones_seleccionadas)) {
            $errors_post[] = "Debe seleccionar al menos una formación para notificar.";
        }
    }

    if ($fecha_programada) {
        $d = DateTime::createFromFormat('Y-m-d\TH:i', $fecha_programada);
        if (!$d || $d->format('Y-m-d\TH:i') !== $fecha_programada) {
            $errors_post[] = "Formato de fecha programada inválido.";
        } else {
            $fecha_programada = $d->format('Y-m-d H:i:s');
        }
    }

    if (empty($errors_post)) {
        try {
            $conn->beginTransaction();

            // Guardar/Actualizar Mensaje Principal
            if ($post_action == 'create') {
                $sql = "INSERT INTO mensajes (asunto_mensaje, cuerpo_mensaje, tipo_destinatario, fecha_programada_envio,
                        estado_mensaje, notificar_mensaje, es_html)
                        VALUES (:asunto, :cuerpo, :tipo_dest, :fecha_prog, 'pendiente_envio', :notificar, :es_html)";
                $stmt = $conn->prepare($sql);
            } elseif ($post_action == 'update' && $id_mensaje_post) {
                $sql = "UPDATE mensajes SET asunto_mensaje = :asunto, cuerpo_mensaje = :cuerpo,
                        tipo_destinatario = :tipo_dest, fecha_programada_envio = :fecha_prog,
                        notificar_mensaje = :notificar, es_html = :es_html
                        WHERE id_mensaje = :id_mensaje";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':id_mensaje', $id_mensaje_post, PDO::PARAM_INT);
            } else {
                throw new Exception("Acción de formulario no válida.");
            }

            $stmt->bindParam(':asunto', $asunto);
            $stmt->bindParam(':cuerpo', $cuerpo);
            $stmt->bindParam(':tipo_dest', $tipo_destinatario);
            $stmt->bindValue(':fecha_prog', $fecha_programada, ($fecha_programada === null ? PDO::PARAM_NULL : PDO::PARAM_STR));
            $stmt->bindParam(':notificar', $notificar_mensaje, PDO::PARAM_INT);
            $stmt->bindParam(':es_html', $es_html, PDO::PARAM_INT);
            $stmt->execute();

            if ($post_action == 'create') {
                $id_mensaje_post = $conn->lastInsertId();
                $feedback_msg = "Mensaje creado exitosamente.";
            } else {
                $feedback_msg = "Mensaje actualizado exitosamente.";
            }

            // Limpiar relaciones antiguas en caso de actualización
            if ($post_action == 'update' && $id_mensaje_post) {
                $conn->prepare("DELETE FROM mensajes_destinatarios_formaciones WHERE id_mensaje = ?")->execute([$id_mensaje_post]);
                $conn->prepare("DELETE FROM mensajes_destinatarios_estudiantes WHERE id_mensaje = ?")->execute([$id_mensaje_post]);
            }

            // Función auxiliar para insertar en tablas de relación
            function guardarRelaciones($conn, $id_mensaje, $ids, $tabla, $columnaId) {
                if (!empty($ids)) {
                    $sql = "INSERT INTO $tabla (id_mensaje, $columnaId) VALUES (:id_mensaje, :id_relacion)";
                    $stmt = $conn->prepare($sql);
                    foreach ($ids as $id) {
                        if (filter_var($id, FILTER_VALIDATE_INT)) {
                            $stmt->execute([':id_mensaje' => $id_mensaje, ':id_relacion' => $id]);
                        }
                    }
                }
            }

            // Guardar Inclusiones
            if ($id_mensaje_post) {
                if ($tipo_destinatario == 'formacion_especifica') {
                    guardarRelaciones($conn, $id_mensaje_post, $ids_formaciones_seleccionadas, 'mensajes_destinatarios_formaciones', 'id_formacion');
                } elseif ($tipo_destinatario == 'estudiantes_seleccionados') {
                    guardarRelaciones($conn, $id_mensaje_post, $ids_estudiantes_seleccionados, 'mensajes_destinatarios_estudiantes', 'id_estudiante');
                }
            }

            $conn->commit();
            header("Location: ?action=list&feedback_msg=" . urlencode($feedback_msg));
            exit;

        } catch (Exception $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log("Error al guardar mensaje: " . $e->getMessage());
            $error_msg = "Error al guardar el mensaje: " . $e->getMessage();
            $action = ($post_action == 'update') ? 'edit' : 'add';
            $id_mensaje_get = $id_mensaje_post;
        }
    } else {
        $error_msg = implode("<br>", $errors_post);
        $action = ($post_action == 'update') ? 'edit' : 'add';
        $id_mensaje_get = $id_mensaje_post;
    }
}

// Lógica para Eliminar Mensaje
if ($action == 'delete' && $id_mensaje_get) {
    try {
        $stmt_check = $conn->prepare("SELECT estado_mensaje FROM mensajes WHERE id_mensaje = :id_mensaje");
        $stmt_check->execute([':id_mensaje' => $id_mensaje_get]);
        $mensaje_estado = $stmt_check->fetchColumn();

        if ($mensaje_estado === 'pendiente_envio' || $mensaje_estado === 'borrador' || $mensaje_estado === 'fallido') {
            $conn->beginTransaction();
            $sql_delete_msg = "DELETE FROM mensajes WHERE id_mensaje = :id_mensaje";
            $stmt_delete_msg = $conn->prepare($sql_delete_msg);
            $stmt_delete_msg->execute([':id_mensaje' => $id_mensaje_get]);

            if ($stmt_delete_msg->rowCount() > 0) {
                $conn->commit();
                $feedback_msg = "Mensaje eliminado exitosamente.";
            } else {
                $conn->rollBack();
                $error_msg = "No se encontró el mensaje para eliminar.";
            }
        } else {
            $error_msg = "No se puede eliminar el mensaje porque su estado es '$mensaje_estado'.";
        }
    } catch(PDOException $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        error_log("Error al eliminar mensaje ID $id_mensaje_get: " . $e->getMessage());
        $error_msg = "Error al eliminar el mensaje.";
    }
    header("Location: ?action=list&feedback_msg=" . urlencode($feedback_msg) . "&error_msg=" . urlencode($error_msg));
    exit;
}

// Obtener Datos para Formularios (Add, Edit)
$mensaje_actual = null;
$formaciones_todas = [];
$estudiantes_todos = [];
$destinatarios_form_actual = [];
$destinatarios_est_actual = [];

if ($action == 'add' || $action == 'edit') {
    try {
        $formaciones_todas = $conn->query("SELECT id_formacion, nombre_formacion, codigo_formacion FROM formaciones ORDER BY nombre_formacion")->fetchAll(PDO::FETCH_ASSOC);
        $estudiantes_todos = $conn->query("SELECT id_estudiante, nombres, apellidos, correo_electronico FROM estudiantes WHERE activo = 1 ORDER BY apellidos, nombres LIMIT 500")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_msg = "Error cargando datos para el formulario: " . $e->getMessage();
    }

    if ($action == 'edit' && $id_mensaje_get) {
        try {
            $stmt_fetch_edit = $conn->prepare("SELECT * FROM mensajes WHERE id_mensaje = :id_mensaje");
            $stmt_fetch_edit->execute([':id_mensaje' => $id_mensaje_get]);
            $mensaje_actual = $stmt_fetch_edit->fetch(PDO::FETCH_ASSOC);

            if (!$mensaje_actual) {
                header("Location: ?action=list&error_msg=" . urlencode("Mensaje no encontrado."));
                exit;
            }

            // Función auxiliar para obtener relaciones
            function obtenerRelaciones($conn, $id_mensaje, $tabla, $columnaId) {
                $stmt = $conn->prepare("SELECT $columnaId FROM $tabla WHERE id_mensaje = :id_mensaje");
                $stmt->execute([':id_mensaje' => $id_mensaje]);
                return $stmt->fetchAll(PDO::FETCH_COLUMN);
            }

            // Inclusiones
            $destinatarios_form_actual = obtenerRelaciones($conn, $id_mensaje_get, 'mensajes_destinatarios_formaciones', 'id_formacion');
            $destinatarios_est_actual = obtenerRelaciones($conn, $id_mensaje_get, 'mensajes_destinatarios_estudiantes', 'id_estudiante');

        } catch(PDOException $e) {
            error_log("Error al obtener mensaje ID $id_mensaje_get: " . $e->getMessage());
            header("Location: ?action=list&error_msg=" . urlencode("Error al cargar datos."));
            exit;
        }
    }
}

// Lógica para DUPLICAR Mensaje
if ($action == 'duplicate' && $id_mensaje_get) {
    try {
        $stmt_original = $conn->prepare("SELECT * FROM mensajes WHERE id_mensaje = :id_mensaje");
        $stmt_original->execute([':id_mensaje' => $id_mensaje_get]);
        $mensaje_original = $stmt_original->fetch(PDO::FETCH_ASSOC);

        if ($mensaje_original) {
            $conn->beginTransaction();

            // Crear copia del mensaje
            $sql_duplicate = "INSERT INTO mensajes (asunto_mensaje, cuerpo_mensaje, tipo_destinatario,
                              fecha_programada_envio, estado_mensaje, notificar_mensaje, es_html)
                              VALUES (:asunto, :cuerpo, :tipo_dest, NULL, 'pendiente_envio', :notificar, :es_html)";
            $stmt_dup = $conn->prepare($sql_duplicate);
            $stmt_dup->execute([
                ':asunto' => '[COPIA] ' . $mensaje_original['asunto_mensaje'],
                ':cuerpo' => $mensaje_original['cuerpo_mensaje'],
                ':tipo_dest' => $mensaje_original['tipo_destinatario'],
                ':notificar' => $mensaje_original['notificar_mensaje'],
                ':es_html' => $mensaje_original['es_html']
            ]);

            $nuevo_id = $conn->lastInsertId();

            // Copiar destinatarios de formaciones
            $conn->query("INSERT INTO mensajes_destinatarios_formaciones (id_mensaje, id_formacion)
                          SELECT $nuevo_id, id_formacion FROM mensajes_destinatarios_formaciones
                          WHERE id_mensaje = $id_mensaje_get");

            // Copiar destinatarios estudiantes
            $conn->query("INSERT INTO mensajes_destinatarios_estudiantes (id_mensaje, id_estudiante)
                          SELECT $nuevo_id, id_estudiante FROM mensajes_destinatarios_estudiantes
                          WHERE id_mensaje = $id_mensaje_get");

            $conn->commit();
            header("Location: ?action=edit&id=$nuevo_id&feedback_msg=" . urlencode("Mensaje duplicado. Edita y guarda cuando quieras."));
            exit;
        } else {
            $error_msg = "Mensaje no encontrado para duplicar.";
        }
    } catch (Exception $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        $error_msg = "Error al duplicar mensaje: " . $e->getMessage();
    }
    header("Location: ?action=list&error_msg=" . urlencode($error_msg));
    exit;
}

// Lógica para la VISTA DE LISTA
$mensajes_listado = [];
$contador_mensajes = [];

if ($action == 'list') {
    try {
        $sql_list = "SELECT
                        m.id_mensaje,
                        m.asunto_mensaje,
                        m.tipo_destinatario,
                        m.fecha_creacion,
                        m.fecha_programada_envio,
                        m.estado_mensaje,
                        m.notificar_mensaje,

                        -- Contar emails enviados exitosamente
                        (SELECT COUNT(*) FROM log_envio_mensajes lem
                         WHERE lem.id_mensaje = m.id_mensaje
                         AND lem.estado_envio_email = 'exitoso') as emails_enviados,

                        -- Contar emails fallidos
                        (SELECT COUNT(*) FROM log_envio_mensajes lem
                         WHERE lem.id_mensaje = m.id_mensaje
                         AND lem.estado_envio_email = 'fallido') as emails_fallidos,

                        -- Contar total de intentos
                        (SELECT COUNT(*) FROM log_envio_mensajes lem
                         WHERE lem.id_mensaje = m.id_mensaje) as total_intentos,

                        -- Fecha del primer envío
                        (SELECT MIN(fecha_intento_envio) FROM log_envio_mensajes lem
                         WHERE lem.id_mensaje = m.id_mensaje) as fecha_inicio_envio,

                        -- Calcular total de destinatarios esperados según tipo
                        CASE
                            WHEN m.tipo_destinatario = 'todos' THEN
                                (SELECT COUNT(*) FROM estudiantes WHERE activo = 1)
                            WHEN m.tipo_destinatario = 'formacion_especifica' THEN
                                (SELECT COUNT(DISTINCT e.id_estudiante)
                                 FROM estudiantes e
                                 JOIN inscripciones i ON e.id_estudiante = i.id_estudiante
                                 JOIN mensajes_destinatarios_formaciones mdf
                                    ON i.id_formacion = mdf.id_formacion AND mdf.id_mensaje = m.id_mensaje
                                 WHERE e.activo = 1
                                 AND i.estado_inscripcion = 'activa')
                            WHEN m.tipo_destinatario = 'estudiantes_seleccionados' THEN
                                (SELECT COUNT(*)
                                 FROM mensajes_destinatarios_estudiantes mde
                                 INNER JOIN estudiantes e ON e.id_estudiante = mde.id_estudiante
                                 WHERE mde.id_mensaje = m.id_mensaje
                                 AND e.activo = 1)
                            ELSE 0
                        END as total_destinatarios_esperados

                     FROM mensajes m
                     ORDER BY m.fecha_creacion DESC LIMIT 200";
        $mensajes_listado = $conn->query($sql_list)->fetchAll(PDO::FETCH_ASSOC);

        $sql_contador = "SELECT estado_mensaje, COUNT(*) as cantidad
                         FROM mensajes
                         GROUP BY estado_mensaje
                         ORDER BY estado_mensaje";
        $contador_result = $conn->query($sql_contador)->fetchAll(PDO::FETCH_ASSOC);

        foreach($contador_result as $row) {
            $contador_mensajes[$row['estado_mensaje']] = $row['cantidad'];
        }
        $contador_mensajes['total'] = array_sum($contador_mensajes);

    } catch(PDOException $e) {
        error_log("Error SQL en listado de mensajes: " . $e->getMessage());
        $error_msg = "Error al obtener la lista de mensajes: " . htmlspecialchars($e->getMessage());
    }
}

// Valores para poblar el formulario
function getValue($key, $default, $source_edit) {
    if (isset($source_edit[$key])) return $source_edit[$key];
    return $default;
}

$form_asunto = getValue('asunto_mensaje', '', $mensaje_actual);
$form_cuerpo = getValue('cuerpo_mensaje', '', $mensaje_actual);
$form_tipo_dest = getValue('tipo_destinatario', '', $mensaje_actual);
$form_fecha_prog_raw = getValue('fecha_programada_envio', '', $mensaje_actual);
$form_notificar = getValue('notificar_mensaje', 1, $mensaje_actual); // Por defecto marcado
$form_es_html = getValue('es_html', 1, $mensaje_actual); // Por defecto HTML

$form_ids_formaciones = $destinatarios_form_actual;
$form_ids_estudiantes = $destinatarios_est_actual;

$form_fecha_prog_input = '';
if ($form_fecha_prog_raw) {
    try {
        $dt = new DateTime($form_fecha_prog_raw);
        $form_fecha_prog_input = $dt->format('Y-m-d\TH:i');
    } catch (Exception $e) { /* Fallo silencioso */ }
}

?>
<?php require_once 'header.php'; ?>

<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>

<!-- Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<style>
    .form-label { @apply block text-sm font-medium text-gray-700 mb-1; }
    .form-section { @apply mt-6 p-6 border border-gray-200 rounded-lg bg-white shadow-sm; }
    .form-section-title { @apply text-lg font-semibold text-gray-800 mb-4 pb-2 border-b-2 border-purple-200 flex items-center; }
    .form-section-title i { @apply mr-2 text-purple-600; }
    #destinatarios_formaciones_div, #destinatarios_estudiantes_div { display: none; }
    .help-text { @apply text-xs text-gray-500 mt-1 flex items-center; }
    .help-text i { @apply mr-1 text-gray-400; }
    .checkbox-label { @apply flex items-center space-x-2 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition; }
    .checkbox-label input[type="checkbox"] { @apply w-5 h-5 text-purple-600 rounded focus:ring-purple-500; }
    .checkbox-label-text { @apply text-sm font-medium text-gray-700; }
    .checkbox-label-help { @apply text-xs text-gray-500 block mt-1; }
    .ck-editor__editable { min-height: 300px; }
    .badge { @apply px-3 py-1 text-xs font-semibold rounded-full; }
    .badge-success { @apply bg-green-100 text-green-800; }
    .badge-warning { @apply bg-yellow-100 text-yellow-800; }
    .badge-info { @apply bg-blue-100 text-blue-800; }
    .badge-danger { @apply bg-red-100 text-red-800; }
</style>

<div class="container mx-auto p-4 md:p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-3xl font-bold text-gray-800">
            <i class="fas fa-envelope text-purple-600 mr-2"></i>
            Gestión de Mensajes
        </h1>
        <?php if ($action == 'list'): ?>
        <a href="?action=add" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>Crear Nuevo Mensaje
        </a>
        <?php endif; ?>
    </div>

    <?php if ($feedback_msg): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($feedback_msg) ?>
    </div>
    <?php endif; ?>

    <?php if ($error_msg): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle mr-2"></i><?= $error_msg ?>
    </div>
    <?php endif; ?>

    <?php if ($action == 'list'): ?>
        <?php if (!empty($contador_mensajes)): ?>
        <div class="bg-gradient-to-r from-purple-50 to-indigo-50 shadow-md rounded-lg p-6 mb-6 border border-purple-100">
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                <i class="fas fa-chart-bar mr-2 text-purple-600"></i>Estadísticas de Mensajes
            </h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                    <div class="text-3xl font-bold text-gray-800"><?= $contador_mensajes['total'] ?? 0 ?></div>
                    <div class="text-sm text-gray-600 mt-1">Total</div>
                </div>
                <?php if (isset($contador_mensajes['enviado_completo'])): ?>
                <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                    <div class="text-3xl font-bold text-green-600"><?= $contador_mensajes['enviado_completo'] ?></div>
                    <div class="text-sm text-gray-600 mt-1">Enviados</div>
                </div>
                <?php endif; ?>
                <?php if (isset($contador_mensajes['pendiente_envio'])): ?>
                <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                    <div class="text-3xl font-bold text-yellow-600"><?= $contador_mensajes['pendiente_envio'] ?></div>
                    <div class="text-sm text-gray-600 mt-1">Pendientes</div>
                </div>
                <?php endif; ?>
                <?php if (isset($contador_mensajes['enviando'])): ?>
                <div class="text-center p-4 bg-white rounded-lg shadow-sm">
                    <div class="text-3xl font-bold text-blue-600"><?= $contador_mensajes['enviando'] ?></div>
                    <div class="text-sm text-gray-600 mt-1">Enviando</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asunto / Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estadísticas de Envío</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Destinatarios</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Creación</th>
                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (count($mensajes_listado) > 0): ?>
                        <?php foreach ($mensajes_listado as $msg): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <!-- Asunto y Estado -->
                                <td class="px-6 py-4">
                                    <div class="flex items-start">
                                        <?php if ($msg['notificar_mensaje']): ?>
                                            <i class="fas fa-envelope text-green-500 mr-2 mt-1" title="Notificación activa"></i>
                                        <?php else: ?>
                                            <i class="fas fa-envelope-open text-gray-300 mr-2 mt-1" title="Sin notificación"></i>
                                        <?php endif; ?>
                                        <div>
                                            <div class="font-medium text-gray-900"><?= htmlspecialchars($msg['asunto_mensaje']) ?></div>
                                            <div class="text-xs text-gray-500 mt-1">
                                                ID: <?= $msg['id_mensaje'] ?>
                                                <?php
                                                $estado_class = 'badge-info';
                                                $estado_icon = 'fa-info-circle';
                                                if ($msg['estado_mensaje'] == 'enviado_completo') {
                                                    $estado_class = 'badge-success';
                                                    $estado_icon = 'fa-check-circle';
                                                }
                                                if ($msg['estado_mensaje'] == 'pendiente_envio') {
                                                    $estado_class = 'badge-warning';
                                                    $estado_icon = 'fa-clock';
                                                }
                                                if ($msg['estado_mensaje'] == 'enviando') {
                                                    $estado_class = 'badge-info';
                                                    $estado_icon = 'fa-paper-plane';
                                                }
                                                if ($msg['estado_mensaje'] == 'fallido') {
                                                    $estado_class = 'badge-danger';
                                                    $estado_icon = 'fa-exclamation-circle';
                                                }
                                                ?>
                                                <span class="badge <?= $estado_class ?> ml-2">
                                                    <i class="fas <?= $estado_icon ?> mr-1"></i>
                                                    <?= htmlspecialchars(ucfirst(str_replace('_', ' ', $msg['estado_mensaje']))) ?>
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Estadísticas de Envío -->
                                <td class="px-6 py-4">
                                    <?php
                                    $total_esperado = $msg['total_destinatarios_esperados'];
                                    $enviados = $msg['emails_enviados'];
                                    $fallidos = $msg['emails_fallidos'];
                                    $pendientes = max(0, $total_esperado - ($enviados + $fallidos));
                                    ?>

                                    <?php if ($msg['notificar_mensaje']): ?>
                                        <div class="space-y-1">
                                            <!-- Total esperado -->
                                            <div class="flex items-center text-sm mb-2 pb-2 border-b border-gray-200">
                                                <i class="fas fa-users text-purple-600 mr-2 w-4"></i>
                                                <span class="font-semibold text-purple-700"><?= $total_esperado ?></span>
                                                <span class="text-gray-600 ml-1">destinatarios</span>
                                            </div>

                                            <!-- Enviados exitosamente -->
                                            <?php if ($enviados > 0): ?>
                                            <div class="flex items-center text-sm">
                                                <i class="fas fa-check-circle text-green-600 mr-2 w-4"></i>
                                                <span class="font-semibold text-green-700"><?= $enviados ?></span>
                                                <span class="text-gray-600 ml-1">enviados</span>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Fallidos -->
                                            <?php if ($fallidos > 0): ?>
                                            <div class="flex items-center text-sm">
                                                <i class="fas fa-times-circle text-red-600 mr-2 w-4"></i>
                                                <span class="font-semibold text-red-700"><?= $fallidos ?></span>
                                                <span class="text-gray-600 ml-1">fallidos</span>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Pendientes -->
                                            <?php if ($pendientes > 0): ?>
                                            <div class="flex items-center text-sm">
                                                <i class="fas fa-clock text-orange-600 mr-2 w-4"></i>
                                                <span class="font-semibold text-orange-700"><?= $pendientes ?></span>
                                                <span class="text-gray-600 ml-1">pendientes</span>
                                            </div>
                                            <?php endif; ?>

                                            <!-- Fecha inicio -->
                                            <?php if ($msg['fecha_inicio_envio']): ?>
                                            <div class="text-xs text-gray-500 mt-2 pt-2 border-t border-gray-200">
                                                <i class="fas fa-play-circle mr-1"></i>
                                                Inicio: <?= date("d/m/Y H:i", strtotime($msg['fecha_inicio_envio'])) ?>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-sm text-gray-400 italic">
                                            <i class="fas fa-bell-slash mr-1"></i>
                                            Sin notificación email
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <!-- Destinatarios -->
                                <td class="px-6 py-4">
                                    <?php
                                    $tipo = str_replace('_', ' ', $msg['tipo_destinatario']);
                                    $icon = 'fa-users';
                                    $color = 'text-purple-500';
                                    if ($msg['tipo_destinatario'] == 'formacion_especifica') {
                                        $icon = 'fa-graduation-cap';
                                        $color = 'text-blue-500';
                                    }
                                    if ($msg['tipo_destinatario'] == 'estudiantes_seleccionados') {
                                        $icon = 'fa-user-check';
                                        $color = 'text-green-500';
                                    }
                                    ?>
                                    <div class="flex items-center">
                                        <i class="fas <?= $icon ?> <?= $color ?> mr-2"></i>
                                        <span class="text-sm text-gray-700"><?= htmlspecialchars(ucfirst($tipo)) ?></span>
                                    </div>
                                </td>

                                <!-- Fecha Creación -->
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-700">
                                        <i class="fas fa-calendar text-gray-400 mr-1"></i>
                                        <?= date("d/m/Y", strtotime($msg['fecha_creacion'])) ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <i class="fas fa-clock text-gray-400 mr-1"></i>
                                        <?= date("H:i", strtotime($msg['fecha_creacion'])) ?>
                                    </div>
                                </td>

                                <!-- Acciones -->
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center space-x-2">
                                        <a href="?action=edit&id=<?= $msg['id_mensaje'] ?>"
                                           class="text-indigo-600 hover:text-indigo-900 p-1"
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>

                                        <a href="?action=duplicate&id=<?= $msg['id_mensaje'] ?>"
                                           class="text-blue-600 hover:text-blue-900 p-1"
                                           title="Duplicar mensaje"
                                           onclick="return confirm('¿Duplicar este mensaje?');">
                                            <i class="fas fa-copy"></i>
                                        </a>

                                        <?php if (in_array($msg['estado_mensaje'], ['pendiente_envio', 'borrador', 'fallido'])): ?>
                                        <a href="?action=delete&id=<?= $msg['id_mensaje'] ?>"
                                           class="text-red-600 hover:text-red-900 p-1"
                                           title="Eliminar"
                                           onclick="return confirm('¿Eliminar este mensaje?');">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-inbox text-4xl mb-3 block text-gray-300"></i>
                                No hay mensajes registrados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    <?php elseif ($action == 'add' || ($action == 'edit' && $mensaje_actual)):
        $form_title_text = ($action == 'add') ? 'Crear Nuevo Mensaje' : 'Editar Mensaje';
        $form_action_value = ($action == 'add') ? 'create' : 'update';
    ?>
        <div class="bg-white shadow-lg rounded-lg p-8 max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-6 pb-4 border-b-2 border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas <?= ($action == 'add') ? 'fa-plus-circle' : 'fa-edit' ?> text-purple-600 mr-3"></i>
                    <?= $form_title_text ?>
                </h2>
                <a href="?action=list" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times text-2xl"></i>
                </a>
            </div>

            <form id="mensaje-form" action="<?= htmlspecialchars($_SERVER["PHP_SELF"]) ?>" method="POST">
                <input type="hidden" name="form_action" value="<?= $form_action_value ?>">
                <?php if ($id_mensaje_get): ?><input type="hidden" name="id_mensaje" value="<?= htmlspecialchars($id_mensaje_get) ?>"><?php endif; ?>

                <!-- SECCIÓN 1: Datos del Mensaje -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-file-alt"></i>
                        Datos del Mensaje
                    </h3>

                    <div class="mb-6">
                        <label for="asunto_mensaje" class="form-label">
                            Asunto <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="asunto_mensaje" id="asunto_mensaje" required
                               value="<?= htmlspecialchars($form_asunto) ?>"
                               class="form-input"
                               placeholder="Ej: Bienvenida al curso de Mediación Penal">
                        <p class="help-text">
                            <i class="fas fa-info-circle"></i>
                            El asunto que verán los destinatarios en su bandeja de entrada
                        </p>
                    </div>

                    <div class="mb-6">
                        <label for="cuerpo_mensaje" class="form-label">
                            Cuerpo del Mensaje <span class="text-red-500">*</span>
                        </label>
                        <textarea name="cuerpo_mensaje" id="cuerpo_mensaje" class="form-input"><?= htmlspecialchars($form_cuerpo) ?></textarea>
                        <p class="help-text">
                            <i class="fas fa-palette"></i>
                            Use el editor para dar formato al mensaje con negrita, listas, enlaces, etc.
                        </p>
                    </div>

                    <!-- Opciones de Formato -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <label class="checkbox-label">
                            <input type="checkbox" name="es_html" id="es_html" value="1" <?= $form_es_html ? 'checked' : '' ?>>
                            <div>
                                <span class="checkbox-label-text">
                                    <i class="fas fa-code mr-1"></i>Enviar como HTML
                                </span>
                                <span class="checkbox-label-help">Permite formato, colores e imágenes</span>
                            </div>
                        </label>

                        <label class="checkbox-label border-2 border-purple-300 bg-purple-50">
                            <input type="checkbox" name="notificar_mensaje" id="notificar_mensaje" value="1" <?= $form_notificar ? 'checked' : '' ?>>
                            <div>
                                <span class="checkbox-label-text text-purple-700">
                                    <i class="fas fa-bell mr-1"></i>Notificar por Email
                                </span>
                                <span class="checkbox-label-help text-purple-600">⚠️ El CRON enviará este mensaje automáticamente</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- SECCIÓN 2: Destinatarios -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-users"></i>
                        Destinatarios
                    </h3>

                    <div class="mb-6">
                        <label for="tipo_destinatario" class="form-label">
                            Enviar a <span class="text-red-500">*</span>
                        </label>
                        <select name="tipo_destinatario" id="tipo_destinatario" required class="form-select">
                            <option value="" disabled <?= ($form_tipo_dest == '') ? 'selected' : '' ?>>-- Seleccione quiénes recibirán el mensaje --</option>
                            <option value="todos" <?= ($form_tipo_dest == 'todos') ? 'selected' : '' ?>>
                                📢 Todos los estudiantes activos
                            </option>
                            <option value="formacion_especifica" <?= ($form_tipo_dest == 'formacion_especifica') ? 'selected' : '' ?>>
                                🎓 Estudiantes de formaciones específicas
                            </option>
                            <option value="estudiantes_seleccionados" <?= ($form_tipo_dest == 'estudiantes_seleccionados') ? 'selected' : '' ?>>
                                👤 Estudiantes seleccionados manualmente
                            </option>
                        </select>
                        <p class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Seleccione el criterio para determinar quiénes recibirán este mensaje
                        </p>
                    </div>

                    <div id="destinatarios_formaciones_div" class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                        <label for="ids_formaciones" class="form-label text-blue-800">
                            <i class="fas fa-graduation-cap mr-2"></i>Seleccionar Formaciones
                        </label>
                        <select name="ids_formaciones[]" id="ids_formaciones" multiple="multiple" style="width: 100%;">
                            <?php foreach($formaciones_todas as $form): ?>
                                <option value="<?= $form['id_formacion'] ?>" <?= in_array($form['id_formacion'], $form_ids_formaciones) ? 'selected' : '' ?>>
                                    [<?= htmlspecialchars($form['codigo_formacion']) ?>] <?= htmlspecialchars($form['nombre_formacion']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="help-text text-blue-700">
                            <i class="fas fa-mouse-pointer"></i>
                            Puede seleccionar múltiples formaciones. Se enviará a todos los estudiantes inscriptos.
                        </p>
                    </div>

                    <div id="destinatarios_estudiantes_div" class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <label for="ids_estudiantes" class="form-label text-green-800">
                            <i class="fas fa-user-check mr-2"></i>Seleccionar Estudiantes
                        </label>
                        <select name="ids_estudiantes[]" id="ids_estudiantes" multiple="multiple" style="width: 100%;">
                            <?php foreach($estudiantes_todos as $est): ?>
                                <option value="<?= $est['id_estudiante'] ?>" <?= in_array($est['id_estudiante'], $form_ids_estudiantes) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($est['apellidos'] . ', ' . $est['nombres']) ?> - <?= htmlspecialchars($est['correo_electronico']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="help-text text-green-700">
                            <i class="fas fa-search"></i>
                            Escriba para buscar por nombre, apellido o email
                        </p>
                    </div>
                </div>

                <!-- SECCIÓN 3: Programación -->
                <div class="form-section">
                    <h3 class="form-section-title">
                        <i class="fas fa-clock"></i>
                        Programación de Envío
                    </h3>

                    <div class="mb-4">
                        <label for="fecha_programada_envio" class="form-label">
                            <i class="fas fa-calendar-alt mr-1"></i>Fecha y Hora de Envío
                        </label>
                        <input type="datetime-local" name="fecha_programada_envio" id="fecha_programada_envio"
                               value="<?= htmlspecialchars($form_fecha_prog_input) ?>"
                               class="form-input max-w-md">
                        <p class="help-text">
                            <i class="fas fa-info-circle"></i>
                            Dejar vacío para enviar lo antes posible. El CRON se ejecuta cada 15 minutos.
                        </p>
                    </div>

                    <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700">
                                    <strong>Importante:</strong> Los emails se envían solo entre las 7:00 y 22:00 hora local de cada estudiante.
                                    El CRON respeta las zonas horarias automáticamente.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="flex justify-between items-center border-t-2 pt-6 mt-8">
                    <a href="?action=list" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save mr-2"></i>
                        <?= ($action == 'add') ? 'Crear Mensaje' : 'Guardar Cambios' ?>
                    </button>
                </div>
            </form>
        </div>

    <?php else: ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            Acción no válida o mensaje no encontrado.
            <a href="?action=list" class="font-bold underline ml-2">Volver al listado</a>
        </div>
    <?php endif; ?>
</div>

<script>
// CKEditor 5
let editorInstance;
ClassicEditor
    .create(document.querySelector('#cuerpo_mensaje'), {
        toolbar: {
            items: [
                'heading', '|',
                'bold', 'italic', 'underline', '|',
                'link', '|',
                'bulletedList', 'numberedList', '|',
                'indent', 'outdent', '|',
                'blockQuote', '|',
                'undo', 'redo'
            ]
        },
        language: 'es',
        placeholder: 'Escriba aquí el contenido del mensaje...'
    })
    .then(editor => {
        editorInstance = editor;
        console.log('CKEditor cargado correctamente');
    })
    .catch(error => {
        console.error('Error al cargar CKEditor:', error);
    });

// Select2
$(document).ready(function() {
    function initSelect2(selector, placeholder) {
        $(selector).select2({
            placeholder: placeholder,
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });
    }

    initSelect2('#ids_formaciones', 'Seleccione una o más formaciones...');
    initSelect2('#ids_estudiantes', 'Busque estudiantes por nombre o email...');

    // Toggle destinatarios
    const tipoDestinatarioSelect = document.getElementById('tipo_destinatario');
    const divFormaciones = document.getElementById('destinatarios_formaciones_div');
    const divEstudiantes = document.getElementById('destinatarios_estudiantes_div');

    function toggleDestinatariosDivs() {
        const selectedType = tipoDestinatarioSelect.value;

        divFormaciones.style.display = 'none';
        divEstudiantes.style.display = 'none';

        if (selectedType === 'formacion_especifica') {
            divFormaciones.style.display = 'block';
        } else if (selectedType === 'estudiantes_seleccionados') {
            divEstudiantes.style.display = 'block';
        }
    }

    if (tipoDestinatarioSelect) {
        tipoDestinatarioSelect.addEventListener('change', toggleDestinatariosDivs);
        toggleDestinatariosDivs();
    }

    // Ayuda visual para notificar
    const checkboxNotificar = document.getElementById('notificar_mensaje');
    if (checkboxNotificar) {
        checkboxNotificar.addEventListener('change', function() {
            if (this.checked) {
                console.log('✓ Este mensaje se enviará automáticamente por email');
            }
        });
    }
});
</script>

<?php require_once 'footer.php'; ?>
<?php $conn = null; ?>
