<?php
// Configuración para Sistema de Asistencias SAJUR
// Sociedad Argentina de Justicia Restaurativa
// Versión: 1.0.0
//
// IMPORTANTE: Este sistema se ejecuta en dominio VERUMAX
// pero usa la base de datos y email de SAJUR

// =====================================================
// CONFIGURACIÓN DE BASE DE DATOS (SAJUR - REMOTO)
// =====================================================
define('DB_HOST', 'localhost');  // Cambiar si SAJUR está en otro servidor
define('DB_USER', 'sajurorg_formac');
define('DB_PASSWORD', 'zYg*HZg0xA');
define('DB_NAME', 'sajurorg_formac');

// =====================================================
// CONFIGURACIÓN DE EMAIL (SAJUR)
// =====================================================
define('MAIL_HOST', 'vps-5361869-x.dattaweb.com');
define('MAIL_USER', 'formacion@sajur.org');
define('MAIL_PASSWORD', '37Dq**T6fY');
define('MAIL_PORT', 465);
define('MAIL_FROM', 'formacion@sajur.org');
define('MAIL_FROM_NAME', 'SAJUR - Formación');

// =====================================================
// CONFIGURACIÓN DE ZONA HORARIA
// =====================================================
date_default_timezone_set('America/Argentina/Buenos_Aires');

// =====================================================
// VERSIÓN DEL SISTEMA
// =====================================================
define('ASISTENCIAS_VERSION', '1.0.0');
define('ASISTENCIAS_SISTEMA_NOMBRE', 'Sistema de Asistencias SAJUR');
define('SISTEMA_VERSION', '1.0.0');
define('SISTEMA_NOMBRE', 'Sistema de Asistencias SAJUR');
define('ORGANIZACION_NOMBRE', 'Sociedad Argentina de Justicia Restaurativa');

// =====================================================
// CONFIGURACIÓN DE URLs (DOMINIO VERUMAX)
// =====================================================
define('ASISTENCIAS_BASE_URL', 'https://www.verumax.com/sajur/asistencias/');
define('BASE_URL', 'https://www.verumax.com/sajur/asistencias/');
define('SITE_URL', 'https://www.sajur.org');

// Configuración de ventana de tiempo para registro
define('MINUTOS_ANTES_INICIO', 0);           // Permitir desde la hora de inicio
define('HORAS_DESPUES_FIN', 1);              // Permitir hasta 1 hora después del fin

// =====================================================
// FUNCIONES BÁSICAS (REUTILIZADAS DE SAJUR)
// =====================================================

/**
 * Función de conexión a base de datos
 */
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASSWORD
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        $conn->exec("SET NAMES 'utf8mb4'");
        return $conn;
    } catch(PDOException $e) {
        error_log("Error de conexión BD SAJUR desde VERUMAX: " . $e->getMessage());
        die("Error de conexión a la base de datos. Por favor, intente más tarde.");
    }
}

/**
 * Función para formatear fechas
 */
function formatearFecha($fecha, $formato = 'd/m/Y') {
    if (empty($fecha)) return 'Fecha no disponible';

    try {
        $date = new DateTime($fecha);
        return $date->format($formato);
    } catch (Exception $e) {
        return 'Fecha inválida';
    }
}

/**
 * Función para formatear fecha y hora
 */
function formatearFechaHora($fecha, $formato = 'd/m/Y H:i') {
    if (empty($fecha)) return 'Fecha no disponible';

    try {
        $date = new DateTime($fecha);
        return $date->format($formato);
    } catch (Exception $e) {
        return 'Fecha inválida';
    }
}

/**
 * Función para limpiar entrada de texto
 */
function limpiarTexto($texto) {
    return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
}

// =====================================================
// FUNCIONES ESPECÍFICAS PARA ASISTENCIAS
// =====================================================

/**
 * Formatea una hora en formato 24hs a 12hs con AM/PM
 */
function formatearHora($hora) {
    if (empty($hora)) return 'Hora no disponible';

    try {
        $time = new DateTime($hora);
        return $time->format('H:i') . ' hs';
    } catch (Exception $e) {
        return 'Hora inválida';
    }
}

/**
 * Limpia y formatea nombres y apellidos para certificados
 */
function limpiarNombreApellido($input) {
    // Solo letras, espacios y acentos - todo en mayúsculas
    $input = mb_strtoupper(trim($input), 'UTF-8');
    // Remover caracteres no permitidos (mantener letras, espacios, acentos)
    $input = preg_replace("/[^A-ZÁÉÍÓÚÑÜ\s]/u", "", $input);
    // Normalizar espacios múltiples
    $input = preg_replace("/\s+/", " ", $input);
    return $input;
}

/**
 * Limpia y formatea DNI/documento
 */
function limpiarDNI($dni) {
    // Solo dígitos y guiones (para formatos internacionales)
    $dni = trim($dni);
    $dni = preg_replace("/[^0-9\-]/", "", $dni);
    return strtoupper($dni); // Por si tiene letras al final (ej: DNI español)
}

/**
 * Valida formato de email
 */
function validarEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Verifica si el registro de asistencia está disponible para una formación
 * Retorna: ['disponible' => bool, 'mensaje' => string, 'datos' => array]
 */
function verificarDisponibilidadAsistencia($formacion, $fecha_override = null, $hora_inicio_override = null, $hora_fin_override = null) {
    try {
        // Usar overrides si están definidos (para testing), sino usar datos reales
        $fecha_evento = $fecha_override ?? $formacion['fecha_inicio'];
        $hora_inicio = $hora_inicio_override ?? $formacion['hora_inicio'];
        $hora_fin = $hora_fin_override ?? $formacion['hora_fin'];

        // Si no hay hora_fin, asumir 2 horas después del inicio
        if (empty($hora_fin)) {
            $hora_fin_dt = new DateTime($fecha_evento . ' ' . $hora_inicio);
            $hora_fin_dt->modify('+2 hours');
            $hora_fin = $hora_fin_dt->format('H:i:s');
        }

        // Crear DateTimes
        $ahora = new DateTime('now', new DateTimeZone('America/Argentina/Buenos_Aires'));
        $inicio_permitido = new DateTime($fecha_evento . ' ' . $hora_inicio, new DateTimeZone('America/Argentina/Buenos_Aires'));
        $fin_evento = new DateTime($fecha_evento . ' ' . $hora_fin, new DateTimeZone('America/Argentina/Buenos_Aires'));
        $fin_permitido = clone $fin_evento;
        $fin_permitido->modify('+' . HORAS_DESPUES_FIN . ' hours');

        // Verificar ventana de tiempo
        if ($ahora < $inicio_permitido) {
            return [
                'disponible' => false,
                'mensaje' => 'El registro de asistencia aún no está disponible. Podrás registrarte desde las ' .
                            formatearHora($hora_inicio) . ' hasta las ' . formatearHora($fin_permitido->format('H:i:s')) .
                            ' del ' . formatearFecha($fecha_evento, 'd/m/Y') . '.',
                'datos' => [
                    'inicio_permitido' => $inicio_permitido->format('Y-m-d H:i:s'),
                    'fin_permitido' => $fin_permitido->format('Y-m-d H:i:s'),
                    'ahora' => $ahora->format('Y-m-d H:i:s')
                ]
            ];
        } elseif ($ahora > $fin_permitido) {
            return [
                'disponible' => false,
                'mensaje' => 'El plazo para registrar asistencia ha finalizado. El registro estuvo disponible hasta ' .
                            $fin_permitido->format('H:i') . ' del ' . formatearFecha($fecha_evento, 'd/m/Y') . '.',
                'datos' => [
                    'inicio_permitido' => $inicio_permitido->format('Y-m-d H:i:s'),
                    'fin_permitido' => $fin_permitido->format('Y-m-d H:i:s'),
                    'ahora' => $ahora->format('Y-m-d H:i:s')
                ]
            ];
        }

        return [
            'disponible' => true,
            'mensaje' => 'Registro disponible',
            'datos' => [
                'inicio_permitido' => $inicio_permitido->format('Y-m-d H:i:s'),
                'fin_permitido' => $fin_permitido->format('Y-m-d H:i:s'),
                'ahora' => $ahora->format('Y-m-d H:i:s')
            ]
        ];

    } catch (Exception $e) {
        error_log("Error verificando disponibilidad de asistencia: " . $e->getMessage());
        return [
            'disponible' => false,
            'mensaje' => 'Error al verificar disponibilidad. Por favor, intenta nuevamente.',
            'datos' => []
        ];
    }
}

/**
 * Genera enlace de asistencia para una formación
 */
function generarEnlaceAsistencia($codigo_formacion) {
    return ASISTENCIAS_BASE_URL . 'asistencia.php?formacion=' . urlencode($codigo_formacion);
}

/**
 * Verifica si un DNI ya registró asistencia para una formación
 */
function verificarAsistenciaDuplicada($id_formacion, $dni) {
    try {
        $conn = getDBConnection();

        $stmt = $conn->prepare("SELECT * FROM asistencias_formaciones
                                WHERE dni = :dni AND id_formacion = :id_formacion
                                LIMIT 1");
        $stmt->execute([
            ':dni' => $dni,
            ':id_formacion' => $id_formacion
        ]);

        $asistencia = $stmt->fetch();

        if ($asistencia) {
            return [
                'existe' => true,
                'datos' => $asistencia
            ];
        }

        return ['existe' => false, 'datos' => null];

    } catch (PDOException $e) {
        error_log("Error verificando asistencia duplicada: " . $e->getMessage());
        return ['existe' => false, 'datos' => null];
    }
}

/**
 * Registra una asistencia en la base de datos
 */
function registrarAsistencia($id_formacion, $nombres, $apellidos, $dni, $email) {
    try {
        $conn = getDBConnection();

        // Obtener IP y User Agent
        $ip_registro = $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $stmt = $conn->prepare("
            INSERT INTO asistencias_formaciones
            (id_formacion, nombres, apellidos, dni, correo_electronico, ip_registro, user_agent, fecha_registro)
            VALUES
            (:id_formacion, :nombres, :apellidos, :dni, :email, :ip, :user_agent, NOW())
        ");

        $resultado = $stmt->execute([
            ':id_formacion' => $id_formacion,
            ':nombres' => $nombres,
            ':apellidos' => $apellidos,
            ':dni' => $dni,
            ':email' => $email,
            ':ip' => $ip_registro,
            ':user_agent' => $user_agent
        ]);

        if ($resultado) {
            return [
                'exito' => true,
                'id_asistencia' => $conn->lastInsertId(),
                'mensaje' => 'Asistencia registrada exitosamente'
            ];
        }

        return [
            'exito' => false,
            'mensaje' => 'Error al registrar la asistencia'
        ];

    } catch (PDOException $e) {
        error_log("Error registrando asistencia: " . $e->getMessage());

        // Verificar si es error de duplicado
        if ($e->getCode() == 23000) {
            return [
                'exito' => false,
                'mensaje' => 'Ya registraste tu asistencia a esta formación anteriormente'
            ];
        }

        return [
            'exito' => false,
            'mensaje' => 'Error al registrar la asistencia: ' . $e->getMessage()
        ];
    }
}

/**
 * Obtiene estadísticas de asistencias para una formación
 */
function obtenerEstadisticasAsistencias($id_formacion = null) {
    try {
        $conn = getDBConnection();

        if ($id_formacion) {
            // Estadísticas de una formación específica
            $stmt = $conn->prepare("
                SELECT COUNT(*) as total_asistencias
                FROM asistencias_formaciones
                WHERE id_formacion = :id_formacion
            ");
            $stmt->execute([':id_formacion' => $id_formacion]);
            return $stmt->fetch();
        } else {
            // Estadísticas generales
            $sql = "SELECT
                        COUNT(*) as total_asistencias,
                        COUNT(DISTINCT id_formacion) as formaciones_con_asistencia,
                        COUNT(DISTINCT dni) as participantes_unicos
                    FROM asistencias_formaciones";
            $stmt = $conn->query($sql);
            return $stmt->fetch();
        }

    } catch (PDOException $e) {
        error_log("Error obteniendo estadísticas de asistencias: " . $e->getMessage());
        return null;
    }
}

?>
