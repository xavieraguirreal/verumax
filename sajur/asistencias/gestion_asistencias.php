<?php
// Sistema de Gestión de Asistencias SAJUR
// Versión: 1.0.0

require_once 'config.php';

$conn = getDBConnection();
$filtro_formacion = isset($_GET['formacion']) ? (int)$_GET['formacion'] : 0;
$mensaje = '';
$error = '';

// Obtener formaciones disponibles
try {
    $sql_formaciones = "SELECT
                            f.id_formacion,
                            f.codigo_formacion,
                            f.nombre_formacion,
                            f.fecha_inicio,
                            f.hora_inicio,
                            f.hora_fin,
                            f.modalidad,
                            COUNT(a.id_asistencia) as total_asistencias
                         FROM formaciones f
                         LEFT JOIN asistencias_formaciones a ON f.id_formacion = a.id_formacion
                         GROUP BY f.id_formacion
                         ORDER BY f.fecha_inicio DESC";

    $stmt_formaciones = $conn->prepare($sql_formaciones);
    $stmt_formaciones->execute();
    $formaciones_disponibles = $stmt_formaciones->fetchAll();

} catch (PDOException $e) {
    $error = "Error al cargar formaciones: " . $e->getMessage();
}

// Obtener asistencias si hay filtro
$asistencias = [];
$stats = ['total' => 0];
$formacion_seleccionada = null;

if ($filtro_formacion > 0) {
    try {
        // Obtener datos de la formación seleccionada
        $stmt_form = $conn->prepare("SELECT * FROM formaciones WHERE id_formacion = ?");
        $stmt_form->execute([$filtro_formacion]);
        $formacion_seleccionada = $stmt_form->fetch();

        // Obtener asistencias
        $sql_asistencias = "SELECT
                                a.*,
                                f.nombre_formacion,
                                f.codigo_formacion,
                                f.fecha_inicio
                            FROM asistencias_formaciones a
                            JOIN formaciones f ON a.id_formacion = f.id_formacion
                            WHERE a.id_formacion = :id_formacion
                            ORDER BY a.fecha_registro DESC";

        $stmt_asistencias = $conn->prepare($sql_asistencias);
        $stmt_asistencias->execute([':id_formacion' => $filtro_formacion]);
        $asistencias = $stmt_asistencias->fetchAll();

        $stats['total'] = count($asistencias);

        // Exportar CSV si se solicita
        if (isset($_POST['exportar_csv'])) {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=asistencias_' . $formacion_seleccionada['codigo_formacion'] . '_' . date('Ymd_His') . '.csv');

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($output, ['Nombres', 'Apellidos', 'DNI', 'Email', 'Fecha Registro', 'Hora Registro'], ';');

            foreach ($asistencias as $asistencia) {
                fputcsv($output, [
                    $asistencia['nombres'],
                    $asistencia['apellidos'],
                    $asistencia['dni'],
                    $asistencia['correo_electronico'],
                    date('d/m/Y', strtotime($asistencia['fecha_registro'])),
                    date('H:i', strtotime($asistencia['fecha_registro']))
                ], ';');
            }

            fclose($output);
            exit;
        }

    } catch (PDOException $e) {
        $error = "Error al cargar asistencias: " . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Asistencias - SAJUR</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        }

        .admin-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .enlace-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            padding: 12px;
            font-family: monospace;
            word-break: break-all;
            cursor: pointer;
            transition: all 0.3s;
        }

        .enlace-box:hover {
            transform: scale(1.02);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
        }

        .tooltip {
            position: relative;
        }

        .tooltip:hover::after {
            content: 'Click para copiar';
            position: absolute;
            bottom: 100%;
            left: 50%;
            transform: translateX(-50%);
            background: #1f2937;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <div class="min-h-screen p-6">
        <!-- Header -->
        <header class="mb-8">
            <div class="max-w-7xl mx-auto">
                <div class="bg-white shadow-sm rounded-lg px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                                <i class="fas fa-clipboard-check mr-3 text-purple-600"></i>
                                Gestión de Asistencias - SAJUR
                            </h1>
                            <p class="text-gray-600 mt-1">Genera enlaces y administra el registro de asistencias</p>
                        </div>
                        <div>
                            <a href="../appSajur/formacion/index.php" class="inline-block bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                                <i class="fas fa-arrow-left mr-2"></i> Volver a SAJUR
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Contenido -->
        <main class="max-w-7xl mx-auto">
            <?php if ($mensaje): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-check-circle mr-2"></i><?= htmlspecialchars($mensaje) ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <!-- Generador de Enlaces -->
            <div class="admin-card p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                    <i class="fas fa-link mr-3 text-purple-600"></i>
                    Generador de Enlaces de Asistencia
                </h2>

                <p class="text-gray-600 mb-6">Selecciona una formación para generar su enlace de registro de asistencia. Los participantes podrán registrar su asistencia durante la franja horaria permitida.</p>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <?php foreach ($formaciones_disponibles as $formacion): ?>
                        <?php
                            $enlace = generarEnlaceAsistencia($formacion['codigo_formacion']);
                        ?>
                        <div class="border border-gray-200 rounded-lg p-5 hover:border-purple-300 transition">
                            <div class="flex justify-between items-start mb-3">
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-800 mb-2"><?= htmlspecialchars($formacion['nombre_formacion']) ?></h3>
                                    <p class="text-sm text-gray-600">
                                        <i class="fas fa-calendar mr-1"></i> <?= formatearFecha($formacion['fecha_inicio'], 'd/m/Y') ?>
                                        <?php if (!empty($formacion['hora_inicio'])): ?>
                                            <i class="fas fa-clock ml-3 mr-1"></i> <?= formatearHora($formacion['hora_inicio']) ?>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-barcode mr-1"></i> Código: <span class="font-mono font-semibold"><?= $formacion['codigo_formacion'] ?></span>
                                    </p>
                                    <p class="text-sm text-gray-600 mt-1">
                                        <i class="fas fa-laptop mr-1"></i> <?= ucfirst($formacion['modalidad']) ?>
                                    </p>
                                </div>
                                <div class="flex flex-col items-end gap-2">
                                    <?php if ($formacion['total_asistencias'] > 0): ?>
                                        <span class="px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="fas fa-check mr-1"></i><?= $formacion['total_asistencias'] ?> asistencias
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Enlace de asistencia -->
                            <div class="mt-4 mb-3">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-link mr-1"></i> Enlace de Registro:
                                </label>
                                <div class="enlace-box tooltip text-sm"
                                     onclick="copiarEnlace('<?= $enlace ?>', this)"
                                     id="enlace-<?= $formacion['id_formacion'] ?>">
                                    <?= $enlace ?>
                                </div>
                                <p class="text-xs text-gray-500 mt-2">
                                    <i class="fas fa-info-circle mr-1"></i> Haz clic para copiar al portapapeles
                                </p>
                            </div>

                            <!-- Botones -->
                            <div class="flex gap-2 mt-4">
                                <a href="?formacion=<?= $formacion['id_formacion'] ?>"
                                   class="flex-1 bg-purple-600 text-white px-4 py-2 rounded-lg text-center hover:bg-purple-700 transition text-sm">
                                    <i class="fas fa-list mr-1"></i> Ver Asistencias
                                </a>
                                <a href="<?= $enlace ?>" target="_blank"
                                   class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition text-sm">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($formaciones_disponibles)): ?>
                        <div class="col-span-2 text-center py-12 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3 block"></i>
                            <p>No hay formaciones disponibles</p>
                            <a href="../appSajur/formacion/gestion_formaciones.php" class="mt-4 inline-block text-purple-600 hover:text-purple-800">
                                <i class="fas fa-plus mr-1"></i> Crear nueva formación
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Listado de Asistencias -->
            <?php if ($filtro_formacion > 0 && $formacion_seleccionada): ?>
                <div class="admin-card p-6">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                                <i class="fas fa-users mr-3 text-purple-600"></i>
                                Asistencias Registradas
                            </h2>
                            <p class="text-gray-600 mt-1"><?= htmlspecialchars($formacion_seleccionada['nombre_formacion']) ?></p>
                        </div>
                        <div class="flex gap-3">
                            <?php if (!empty($asistencias)): ?>
                                <form method="POST" class="inline">
                                    <button type="submit" name="exportar_csv" value="1"
                                            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition">
                                        <i class="fas fa-file-csv mr-2"></i> Exportar CSV
                                    </button>
                                </form>
                            <?php endif; ?>
                            <a href="?" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition">
                                <i class="fas fa-arrow-left mr-2"></i> Volver
                            </a>
                        </div>
                    </div>

                    <!-- Estadísticas -->
                    <div class="bg-gradient-to-r from-purple-50 to-indigo-50 rounded-lg p-6 mb-6">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div class="bg-white rounded-lg p-4 text-center">
                                <div class="text-3xl font-bold text-purple-600"><?= $stats['total'] ?></div>
                                <div class="text-sm text-gray-600 mt-1">Total Asistencias</div>
                            </div>
                            <div class="bg-white rounded-lg p-4 text-center">
                                <div class="text-3xl font-bold text-blue-600"><?= formatearFecha($formacion_seleccionada['fecha_inicio'], 'd/m/Y') ?></div>
                                <div class="text-sm text-gray-600 mt-1">Fecha Formación</div>
                            </div>
                            <?php if (!empty($formacion_seleccionada['hora_inicio'])): ?>
                            <div class="bg-white rounded-lg p-4 text-center">
                                <div class="text-3xl font-bold text-green-600"><?= formatearHora($formacion_seleccionada['hora_inicio']) ?></div>
                                <div class="text-sm text-gray-600 mt-1">Hora Inicio</div>
                            </div>
                            <?php endif; ?>
                            <?php if (!empty($formacion_seleccionada['hora_fin'])): ?>
                            <div class="bg-white rounded-lg p-4 text-center">
                                <div class="text-3xl font-bold text-orange-600"><?= formatearHora($formacion_seleccionada['hora_fin']) ?></div>
                                <div class="text-sm text-gray-600 mt-1">Hora Fin</div>
                            </div>
                            <?php endif; ?>
                            <div class="bg-white rounded-lg p-4 text-center">
                                <div class="text-3xl font-bold text-gray-600"><?= ucfirst($formacion_seleccionada['modalidad']) ?></div>
                                <div class="text-sm text-gray-600 mt-1">Modalidad</div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de asistencias -->
                    <?php if (!empty($asistencias)): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Participante</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">DNI</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha Registro</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hora Registro</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($asistencias as $asistencia): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 bg-purple-100 rounded-full flex items-center justify-center">
                                                        <span class="text-purple-600 font-semibold">
                                                            <?= mb_substr($asistencia['nombres'], 0, 1) ?><?= mb_substr($asistencia['apellidos'], 0, 1) ?>
                                                        </span>
                                                    </div>
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">
                                                            <?= htmlspecialchars($asistencia['nombres']) ?> <?= htmlspecialchars($asistencia['apellidos']) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900 font-mono"><?= htmlspecialchars($asistencia['dni']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900"><?= htmlspecialchars($asistencia['correo_electronico']) ?></div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <i class="fas fa-calendar mr-1 text-gray-400"></i>
                                                    <?= date('d/m/Y', strtotime($asistencia['fecha_registro'])) ?>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm text-gray-900">
                                                    <i class="fas fa-clock mr-1 text-gray-400"></i>
                                                    <?= date('H:i', strtotime($asistencia['fecha_registro'])) ?> hs
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12 text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-3 block"></i>
                            <p class="text-lg font-medium">No hay asistencias registradas</p>
                            <p class="text-sm mt-2">Los participantes podrán registrarse durante la franja horaria permitida</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>

        <!-- Footer -->
        <footer class="mt-12 text-center text-gray-600 text-sm">
            <p>Sistema de Gestión de Asistencias SAJUR v<?= ASISTENCIAS_VERSION ?></p>
            <p class="mt-1">© <?= date('Y') ?> Sociedad Argentina de Justicia Restaurativa</p>
        </footer>
    </div>

    <script>
        function copiarEnlace(enlace, elemento) {
            // Copiar al portapapeles
            navigator.clipboard.writeText(enlace).then(function() {
                // Feedback visual
                const originalBg = elemento.style.background;
                elemento.style.background = '#10b981';
                elemento.innerHTML = '<i class="fas fa-check mr-2"></i>¡Enlace copiado!';

                setTimeout(function() {
                    elemento.style.background = '';
                    elemento.textContent = enlace;
                }, 2000);
            }).catch(function(err) {
                console.error('Error al copiar: ', err);
                alert('Enlace: ' + enlace);
            });
        }

        // Actualizar tooltips
        document.querySelectorAll('.tooltip').forEach(el => {
            el.addEventListener('mouseleave', () => {
                setTimeout(() => {
                    if (el.textContent.includes('¡Enlace copiado!')) {
                        // No hacer nada, el timeout ya lo restaurará
                    }
                }, 100);
            });
        });
    </script>
</body>
</html>
