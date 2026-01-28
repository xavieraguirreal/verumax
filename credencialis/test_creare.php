<?php
/**
 * TEST: Debug paso a paso de creare.php
 */

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h2>Test Creare.php paso a paso</h2>";

// Paso 1: Config
echo "<p>1. Cargando config.php... ";
try {
    require_once __DIR__ . '/config.php';
    echo "<span style='color:green'>OK</span></p>";
} catch (Throwable $e) {
    echo "<span style='color:red'>ERROR: " . $e->getMessage() . "</span></p>";
    exit;
}

// Paso 2: Autodetect
echo "<p>2. Cargando autodetect.php... ";
try {
    require_once __DIR__ . '/../certificatum/autodetect.php';
    echo "<span style='color:green'>OK</span></p>";
} catch (Throwable $e) {
    echo "<span style='color:red'>ERROR: " . $e->getMessage() . "</span></p>";
    exit;
}

// Paso 3: Services
echo "<p>3. Verificando servicios...</p>";
$services = [
    'VERUMax\Services\LanguageService',
    'VERUMax\Services\InstitutionService',
    'VERUMax\Services\MemberService',
    'VERUMax\Services\QRCodeService',
    'VERUMax\Services\CertificateService'
];

foreach ($services as $service) {
    echo "<p>&nbsp;&nbsp;- $service: ";
    if (class_exists($service)) {
        echo "<span style='color:green'>OK</span></p>";
    } else {
        echo "<span style='color:red'>NO EXISTE</span></p>";
    }
}

// Paso 4: Obtener parámetros
$institucion = $_GET['institutio'] ?? 'sajur';
$dni = $_GET['documentum'] ?? '21090771';
echo "<p>4. Parámetros: institucion=$institucion, dni=$dni <span style='color:green'>OK</span></p>";

// Paso 5: InstitutionService
echo "<p>5. InstitutionService::getConfig('$institucion')... ";
try {
    $instance_config = \VERUMax\Services\InstitutionService::getConfig($institucion);
    if ($instance_config) {
        echo "<span style='color:green'>OK - " . $instance_config['nombre'] . "</span></p>";
    } else {
        echo "<span style='color:red'>NULL</span></p>";
    }
} catch (Throwable $e) {
    echo "<span style='color:red'>ERROR: " . $e->getMessage() . "</span></p>";
    exit;
}

// Paso 6: LanguageService
echo "<p>6. LanguageService::init()... ";
try {
    \VERUMax\Services\LanguageService::init($institucion, null);
    echo "<span style='color:green'>OK</span></p>";
} catch (Throwable $e) {
    echo "<span style='color:red'>ERROR: " . $e->getMessage() . "</span></p>";
    exit;
}

// Paso 7: MemberService
echo "<p>7. MemberService::getCredentialData()... ";
try {
    $miembro = \VERUMax\Services\MemberService::getCredentialData($institucion, $dni);
    if ($miembro) {
        echo "<span style='color:green'>OK - " . $miembro['nombre_completo'] . "</span></p>";
    } else {
        echo "<span style='color:red'>NULL</span></p>";
    }
} catch (Throwable $e) {
    echo "<span style='color:red'>ERROR: " . $e->getMessage() . "</span></p>";
    exit;
}

// Paso 8: CertificateService
echo "<p>8. CertificateService::getValidationCode()... ";
try {
    $codigo = \VERUMax\Services\CertificateService::getValidationCode($institucion, $dni, '', 'credentialis');
    echo "<span style='color:green'>OK - $codigo</span></p>";
} catch (Throwable $e) {
    echo "<span style='color:red'>ERROR: " . $e->getMessage() . "</span></p>";
    exit;
}

// Paso 9: obtenerURLBaseInstitucion
echo "<p>9. obtenerURLBaseInstitucion()... ";
try {
    if (function_exists('obtenerURLBaseInstitucion')) {
        $url = obtenerURLBaseInstitucion();
        echo "<span style='color:green'>OK - $url</span></p>";
    } else {
        echo "<span style='color:red'>FUNCIÓN NO EXISTE</span></p>";
    }
} catch (Throwable $e) {
    echo "<span style='color:red'>ERROR: " . $e->getMessage() . "</span></p>";
    exit;
}

// Paso 10: QRCodeService
echo "<p>10. QRCodeService::generate()... ";
try {
    $qr_url = \VERUMax\Services\QRCodeService::generate('https://test.com', 150);
    echo "<span style='color:green'>OK - $qr_url</span></p>";
} catch (Throwable $e) {
    echo "<span style='color:red'>ERROR: " . $e->getMessage() . "</span></p>";
    exit;
}

// Paso 11: Template credencial
echo "<p>11. Verificando template credencial.php... ";
$template_path = __DIR__ . '/../certificatum/templates/credencial.php';
if (file_exists($template_path)) {
    echo "<span style='color:green'>EXISTE</span></p>";
} else {
    echo "<span style='color:red'>NO EXISTE en $template_path</span></p>";
}

// Paso 12: Probar include del template
echo "<p>12. Probando include del template credencial.php... ";
try {
    // Variables necesarias para el template
    $codigo_validacion = $codigo;
    $url_validacion = obtenerURLBaseInstitucion() . "/credencialis/validare.php?codigo=" . $codigo_validacion;
    $qr_url = \VERUMax\Services\QRCodeService::generate($url_validacion, 150);
    $es_instancia_test = false;
    $t = fn($key, $params = [], $default = null) => \VERUMax\Services\LanguageService::get($key, $params, $default);

    ob_start();
    include $template_path;
    $html = ob_get_clean();

    if (strlen($html) > 100) {
        echo "<span style='color:green'>OK - " . strlen($html) . " bytes</span></p>";
        echo "<details><summary>Ver HTML generado</summary><pre>" . htmlspecialchars(substr($html, 0, 2000)) . "...</pre></details>";
    } else {
        echo "<span style='color:orange'>HTML muy corto: " . strlen($html) . " bytes</span></p>";
    }
} catch (Throwable $e) {
    echo "<span style='color:red'>ERROR: " . $e->getMessage() . "</span></p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr><p style='color:green;font-weight:bold'>Todos los pasos OK!</p>";
?>
