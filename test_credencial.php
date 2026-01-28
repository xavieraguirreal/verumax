<?php
/**
 * Test de credenciales - DEBUG
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Test de Credenciales</h2>";

// Paso 1: Cargar config
echo "<h3>1. Cargando config.php...</h3>";
try {
    require_once __DIR__ . '/certificatum/config.php';
    echo "<p style='color:green'>✓ config.php cargado</p>";
} catch (Exception $e) {
    die("<p style='color:red'>ERROR: " . $e->getMessage() . "</p>");
}

// Paso 2: Cargar init
echo "<h3>2. Cargando init.php...</h3>";
try {
    require_once __DIR__ . '/certificatum/init.php';
    echo "<p style='color:green'>✓ init.php cargado</p>";
} catch (Exception $e) {
    die("<p style='color:red'>ERROR: " . $e->getMessage() . "</p>");
}

// Paso 3: Inicializar contexto
echo "<h3>3. Inicializando contexto...</h3>";
$_GET['institutio'] = 'sajur';
try {
    $ctx = initCertificatum();
    extract($ctx);
    echo "<p style='color:green'>✓ Contexto inicializado</p>";
    echo "<p>Institución: " . htmlspecialchars($institucion ?? 'NULL') . "</p>";
    echo "<p>ID Instancia: " . htmlspecialchars($instance_config['id_instancia'] ?? 'NULL') . "</p>";
} catch (Exception $e) {
    die("<p style='color:red'>ERROR: " . $e->getMessage() . "</p>");
}

// Paso 4: Cargar MemberService
echo "<h3>4. Cargando MemberService...</h3>";
try {
    require_once __DIR__ . '/src/VERUMax/Services/MemberService.php';
    echo "<p style='color:green'>✓ MemberService cargado</p>";
} catch (Exception $e) {
    die("<p style='color:red'>ERROR: " . $e->getMessage() . "</p>");
}

// Paso 5: Cargar autodetect
echo "<h3>5. Cargando autodetect.php...</h3>";
try {
    require_once __DIR__ . '/certificatum/autodetect.php';
    echo "<p style='color:green'>✓ autodetect.php cargado</p>";

    // Test de la función
    if (function_exists('obtenerURLBaseInstitucion')) {
        echo "<p style='color:green'>✓ Función obtenerURLBaseInstitucion existe</p>";
    } else {
        echo "<p style='color:red'>✗ Función obtenerURLBaseInstitucion NO existe</p>";
    }
} catch (Exception $e) {
    die("<p style='color:red'>ERROR: " . $e->getMessage() . "</p>");
}

// Paso 6: Buscar miembro
echo "<h3>6. Buscando miembro DNI 21090771...</h3>";
$dni = '21090771';
$id_instancia = $instance_config['id_instancia'] ?? 1;
try {
    $miembro = \VERUMax\Services\MemberService::getByIdentificador($id_instancia, $dni);
    if ($miembro) {
        echo "<p style='color:green'>✓ Miembro encontrado: " . htmlspecialchars($miembro['nombre'] . ' ' . $miembro['apellido']) . "</p>";
        echo "<pre>" . print_r($miembro, true) . "</pre>";
    } else {
        echo "<p style='color:red'>✗ Miembro NO encontrado</p>";
    }
} catch (Exception $e) {
    die("<p style='color:red'>ERROR: " . $e->getMessage() . "</p>");
}

// Paso 7: Generar código de validación
echo "<h3>7. Generando código de validación...</h3>";
use VERUMax\Services\CertificateService;
try {
    $codigo_validacion = CertificateService::getValidationCode(
        $institucion,
        $dni,
        'credencial_' . $miembro['id_miembro'],
        'credentialis'
    );
    echo "<p style='color:green'>✓ Código generado: " . htmlspecialchars($codigo_validacion) . "</p>";
} catch (Exception $e) {
    die("<p style='color:red'>ERROR: " . $e->getMessage() . "</p>");
}

// Paso 8: Generar QR
echo "<h3>8. Generando QR...</h3>";
use VERUMax\Services\QRCodeService;
try {
    $url_validacion = "https://verumax.com/certificatum/validare.php?codigo=" . $codigo_validacion;
    echo "<p>URL de validación: " . htmlspecialchars($url_validacion) . "</p>";

    $qr_url = QRCodeService::generateDataUri($url_validacion, 150);
    echo "<p style='color:green'>✓ QR generado (longitud: " . strlen($qr_url) . " chars)</p>";
    echo "<img src='" . $qr_url . "' alt='QR'>";
} catch (Exception $e) {
    die("<p style='color:red'>ERROR: " . $e->getMessage() . "</p>");
}

echo "<hr><p style='color:green; font-weight:bold'>✓ Todos los pasos completados correctamente</p>";
echo "<p><a href='/certificatum/creare.php?institutio=sajur&documentum=21090771&genus=credentialis'>Probar URL real</a></p>";
