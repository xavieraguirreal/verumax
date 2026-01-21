<?php
/**
 * Script de verificación de deploy - ELIMINAR DESPUÉS DE USAR
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Verificación Deploy</title>";
echo "<style>body{font-family:Arial,sans-serif;max-width:900px;margin:20px auto;padding:20px}";
echo ".ok{color:green}.error{color:red}.warn{color:orange}";
echo "h2{border-bottom:2px solid #333;padding-bottom:10px}";
echo ".section{background:#f5f5f5;padding:15px;margin:10px 0;border-radius:8px}</style></head><body>";

echo "<h1>🔍 Verificación de Deploy - VERUMax</h1>";

$errores = 0;
$warnings = 0;

// ============================================================================
// 1. VERIFICAR CARPETAS
// ============================================================================
echo "<div class='section'><h2>1. Carpetas</h2>";

$carpetas = [
    'admin', 'assets', 'certificatum', 'componentes', 'config',
    'general', 'identitas', 'img', 'includes', 'lang',
    'sajur', 'src', 'templates', 'vendor',
    'src/VERUMax', 'src/VERUMax/Services',
    'templates/shared'
];

foreach ($carpetas as $carpeta) {
    $path = __DIR__ . '/' . $carpeta;
    if (is_dir($path)) {
        echo "<p class='ok'>✓ /$carpeta/</p>";
    } else {
        echo "<p class='error'>✗ /$carpeta/ - NO EXISTE</p>";
        $errores++;
    }
}
echo "</div>";

// ============================================================================
// 2. VERIFICAR ARCHIVOS CRÍTICOS
// ============================================================================
echo "<div class='section'><h2>2. Archivos Críticos</h2>";

$archivos = [
    '.env' => 'Configuración de entorno',
    '.htaccess' => 'Configuración Apache',
    'env_loader.php' => 'Cargador de variables',
    'identitas/config.php' => 'Config Identitas',
    'certificatum/config.php' => 'Config Certificatum',
    'src/VERUMax/Services/LanguageService.php' => 'Servicio de idiomas',
    'src/VERUMax/Services/EmailService.php' => 'Servicio de emails',
    'templates/shared/header.php' => 'Header compartido',
    'templates/shared/footer.php' => 'Footer compartido',
    'includes/url_helper.php' => 'Helper de URLs',
    'vendor/autoload.php' => 'Autoload de Composer',
    'lang/es_AR.php' => 'Idioma Español Argentina',
    'lang/pt_BR.php' => 'Idioma Portugués Brasil',
    'config/sendgrid.php' => 'Config SendGrid',
];

foreach ($archivos as $archivo => $desc) {
    $path = __DIR__ . '/' . $archivo;
    if (file_exists($path)) {
        echo "<p class='ok'>✓ $archivo - $desc</p>";
    } else {
        echo "<p class='error'>✗ $archivo - $desc - NO EXISTE</p>";
        $errores++;
    }
}
echo "</div>";

// ============================================================================
// 3. VERIFICAR .ENV
// ============================================================================
echo "<div class='section'><h2>3. Variables de Entorno</h2>";

if (file_exists(__DIR__ . '/.env')) {
    $envContent = file_get_contents(__DIR__ . '/.env');

    $variables = ['GENERAL_DB', 'CERTIFI_DB', 'NEXUS_DB', 'ACADEMI_DB'];
    foreach ($variables as $var) {
        if (strpos($envContent, $var) !== false) {
            echo "<p class='ok'>✓ {$var}_* definidas</p>";
        } else {
            echo "<p class='error'>✗ {$var}_* - NO DEFINIDAS</p>";
            $errores++;
        }
    }
} else {
    echo "<p class='error'>✗ No se puede leer .env</p>";
    $errores++;
}
echo "</div>";

// ============================================================================
// 4. VERIFICAR CONEXIONES BD
// ============================================================================
echo "<div class='section'><h2>4. Conexiones a Base de Datos</h2>";

// Cargar .env manualmente
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$databases = [
    'general' => ['GENERAL_DB_HOST', 'GENERAL_DB_USER', 'GENERAL_DB_PASS', 'GENERAL_DB_NAME'],
    'certifi' => ['CERTIFI_DB_HOST', 'CERTIFI_DB_USER', 'CERTIFI_DB_PASS', 'CERTIFI_DB_NAME'],
    'nexus' => ['NEXUS_DB_HOST', 'NEXUS_DB_USER', 'NEXUS_DB_PASS', 'NEXUS_DB_NAME'],
    'academi' => ['ACADEMI_DB_HOST', 'ACADEMI_DB_USER', 'ACADEMI_DB_PASS', 'ACADEMI_DB_NAME'],
];

foreach ($databases as $name => $keys) {
    $host = $_ENV[$keys[0]] ?? 'localhost';
    $user = $_ENV[$keys[1]] ?? '';
    $pass = $_ENV[$keys[2]] ?? '';
    $db = $_ENV[$keys[3]] ?? '';

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
        echo "<p class='ok'>✓ $name ($db) - Conectado</p>";
    } catch (Exception $e) {
        echo "<p class='error'>✗ $name ($db) - " . $e->getMessage() . "</p>";
        $errores++;
    }
}
echo "</div>";

// ============================================================================
// 5. VERIFICAR INSTANCIA SAJUR
// ============================================================================
echo "<div class='section'><h2>5. Instancia SAJuR</h2>";

try {
    $pdo = new PDO(
        "mysql:host=" . ($_ENV['GENERAL_DB_HOST'] ?? 'localhost') . ";dbname=" . ($_ENV['GENERAL_DB_NAME'] ?? 'verumax_general') . ";charset=utf8mb4",
        $_ENV['GENERAL_DB_USER'] ?? '',
        $_ENV['GENERAL_DB_PASS'] ?? ''
    );

    $stmt = $pdo->query("SELECT slug, nombre, idioma_default, idiomas_habilitados, modulo_certificatum, identitas_activo FROM instances WHERE slug = 'sajur'");
    $instance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($instance) {
        echo "<p class='ok'>✓ Instancia encontrada: {$instance['nombre']}</p>";
        echo "<p>- idioma_default: <strong>" . ($instance['idioma_default'] ?? 'NULL') . "</strong></p>";
        echo "<p>- idiomas_habilitados: <strong>" . ($instance['idiomas_habilitados'] ?? 'NULL') . "</strong></p>";
        echo "<p>- modulo_certificatum: <strong>" . ($instance['modulo_certificatum'] ? 'Activo' : 'Inactivo') . "</strong></p>";
        echo "<p>- identitas_activo: <strong>" . ($instance['identitas_activo'] ? 'Activo' : 'Inactivo') . "</strong></p>";

        if (empty($instance['idiomas_habilitados'])) {
            echo "<p class='warn'>⚠ idiomas_habilitados está vacío - el selector no aparecerá</p>";
            $warnings++;
        }
    } else {
        echo "<p class='error'>✗ Instancia 'sajur' no encontrada en la BD</p>";
        $errores++;
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error: " . $e->getMessage() . "</p>";
    $errores++;
}
echo "</div>";

// ============================================================================
// 6. VERIFICAR TABLAS EMAIL
// ============================================================================
echo "<div class='section'><h2>6. Tablas de Email</h2>";

try {
    $tablas = ['email_config', 'email_templates', 'email_logs'];
    foreach ($tablas as $tabla) {
        $stmt = $pdo->query("SELECT COUNT(*) FROM $tabla");
        $count = $stmt->fetchColumn();
        echo "<p class='ok'>✓ $tabla - $count registros</p>";
    }
} catch (Exception $e) {
    echo "<p class='error'>✗ Error en tablas email: " . $e->getMessage() . "</p>";
    $errores++;
}
echo "</div>";

// ============================================================================
// RESUMEN
// ============================================================================
echo "<div class='section' style='background:" . ($errores > 0 ? '#ffe0e0' : '#e0ffe0') . "'>";
echo "<h2>📊 Resumen</h2>";
echo "<p><strong>Errores:</strong> $errores</p>";
echo "<p><strong>Advertencias:</strong> $warnings</p>";

if ($errores == 0) {
    echo "<p class='ok' style='font-size:1.2em'>✓ ¡Deploy verificado correctamente!</p>";
} else {
    echo "<p class='error' style='font-size:1.2em'>✗ Hay $errores errores que corregir</p>";
}
echo "</div>";

echo "<hr><p><em>⚠️ IMPORTANTE: Eliminar este archivo después de verificar (verificar_deploy.php)</em></p>";
echo "</body></html>";
