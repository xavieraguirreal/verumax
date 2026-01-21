<?php
/**
 * Script para generar archivo es_EC.php actualizado con traducciones al español ecuatoriano formal
 *
 * Convierte voseo argentino a ustedeo ecuatoriano formal institucional
 * Adapta terminología regional
 */

// Cargar archivos existentes
$lang_ar = [];
include 'E:\appVerumax\lang\es_AR.php';
$lang_ar = $lang;

$lang_ec = [];
$lang = [];
include 'E:\appVerumax\lang\es_EC.php';
$lang_ec = $lang;

// Función para adaptar texto argentino a ecuatoriano formal
function adaptarAEcuatoriano($texto_ar) {
    // Convertir voseo a ustedeo
    $conversiones = [
        // Verbos en voseo → ustedeo
        'Vos ' => 'Usted ',
        ' vos ' => ' usted ',
        'Tenés' => 'Tiene',
        'tenés' => 'tiene',
        'Querés' => 'Quiere',
        'querés' => 'quiere',
        'Podés' => 'Puede',
        'podés' => 'puede',
        'Sabés' => 'Sabe',
        'sabés' => 'sabe',
        'Enviás' => 'Envía',
        'enviás' => 'envía',
        'Necesitás' => 'Necesita',
        'necesitás' => 'necesita',
        'Contás' => 'Cuenta',
        'contás' => 'cuenta',
        'Estás' => 'Está',
        'estás' => 'está',
        'Sos' => 'Es',
        ' sos ' => ' es ',
        'Hacés' => 'Hace',
        'hacés' => 'hace',

        // Tuteo → ustedeo
        'tú ' => 'usted ',
        'Tu ' => 'Su ',
        ' tu ' => ' su ',
        ' tus ' => ' sus ',
        'Tus ' => 'Sus ',
        'tienes' => 'tiene',
        'Tienes' => 'Tiene',
        'puedes' => 'puede',
        'Puedes' => 'Puede',
        'quieres' => 'quiere',
        'Quieres' => 'Quiere',
        'necesitas' => 'necesita',
        'Necesitas' => 'Necesita',
        'sabes' => 'sabe',
        'Sabes' => 'Sabe',

        // Términos regionales
        'DNI' => 'Cédula',
        'CUIL' => 'RUC',
        'Argentina' => 'Ecuador',
        'argentina' => 'ecuatoriana',
        'argentino' => 'ecuatoriano',
        'argentinos' => 'ecuatorianos',
        'argentinas' => 'ecuatorianas',
        'Argentinas' => 'Ecuatorianas',

        // Adaptaciones de tono
        'Contanos' => 'Cuéntenos',
        'contanos' => 'cuéntenos',
        'Contactanos' => 'Contáctenos',
        'contactanos' => 'contáctenos',
        'Escribinos' => 'Escríbanos',
        'escribinos' => 'escríbanos',
        'Descubrí' => 'Descubra',
        'descubrí' => 'descubra',
        'Elegí' => 'Elija',
        'elegí' => 'elija',
        'Ingresá' => 'Ingrese',
        'ingresá' => 'ingrese',
        'Comenzá' => 'Comience',
        'comenzá' => 'comience',

        // Vocabulario
        'email' => 'correo electrónico',
        'Email' => 'Correo electrónico',
        'Emails' => 'Correos electrónicos',
        'etc.' => 'entre otros',
        'logs' => 'registros',
        'Logs' => 'Registros',
        'backups' => 'respaldos',
        'Backups' => 'Respaldos',
        'Lun - Vie' => 'Lunes a viernes',
        'alquilamos' => 'arrendamos',
        'alquilar' => 'arrendar',

        // Autoridades
        'AAIP' => 'DINARDAP',
        'Agencia de Acceso a la Información Pública (AAIP)' => 'Dirección Nacional de Registro de Datos Públicos',
        'www.argentina.gob.ar/aaip' => 'www.registrocivil.gob.ec',
        'considerás' => 'considera',
        'Considerás' => 'Considera',

        // Expresiones informales
        'Ahorrás' => 'Ahorra',
        'ahorrás' => 'ahorra',

        // Género neutro
        'los/as' => 'los',
        '/as' => '',
        '/a' => '',
    ];

    $texto_ec = $texto_ar;
    foreach ($conversiones as $arg => $ec) {
        $texto_ec = str_replace($arg, $ec, $texto_ec);
    }

    return $texto_ec;
}

// Fusionar claves: mantener las existentes en EC (ya adaptadas) y agregar las faltantes adaptadas de AR
$lang_final = [];

// Primero, copiar TODAS las claves de AR para mantener el orden
foreach ($lang_ar as $key => $value_ar) {
    // Si la clave ya existe en EC, mantener la versión EC (ya adaptada manualmente)
    if (isset($lang_ec[$key])) {
        $lang_final[$key] = $lang_ec[$key];
    } else {
        // Si la clave no existe en EC, adaptarla desde AR
        $lang_final[$key] = adaptarAEcuatoriano($value_ar);
    }
}

// Generar el archivo PHP
$output = "<?php\n";
$output .= "// Español Ecuador - es_EC\n";
$output .= "// Adaptación del español argentino al español ecuatoriano formal institucional\n";
$output .= "// Última actualización: " . date('Y-m-d H:i:s') . "\n";
$output .= "\$lang = [\n";

$current_section = '';
foreach ($lang_final as $key => $value) {
    // Detectar nueva sección por el prefijo de la clave
    $prefix = explode('_', $key)[0];
    if ($prefix !== $current_section) {
        $current_section = $prefix;
        $output .= "\n    // " . strtoupper($current_section) . " Section\n";
    }

    // Escapar comillas simples en el valor
    $value_escaped = addslashes($value);
    $output .= "    '$key' => '$value_escaped',\n";
}

$output .= "];\n";
$output .= "?>";

// Guardar el archivo
file_put_contents('E:\appVerumax\lang\es_EC_updated.php', $output);

echo "Archivo generado exitosamente: es_EC_updated.php\n";
echo "Total de claves: " . count($lang_final) . "\n";
echo "Claves nuevas agregadas: " . (count($lang_final) - count($lang_ec)) . "\n";
?>
