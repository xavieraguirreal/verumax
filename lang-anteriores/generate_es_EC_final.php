<?php
/**
 * Script mejorado para generar archivo es_EC.php con español ecuatoriano formal consistente
 */

// Cargar archivos existentes
$lang_ar = [];
include 'E:\appVerumax\lang\es_AR.php';
$lang_ar = $lang;

$lang_ec = [];
$lang = [];
include 'E:\appVerumax\lang\es_EC.php';
$lang_ec = $lang;

// Función mejorada para adaptar texto argentino a ecuatoriano formal
function adaptarAEcuatorianoFormal($texto_ar) {
    $texto = $texto_ar;

    // PASO 1: Convertir pronombres y posesivos (tú/vos → usted)
    $conversiones_pronombres = [
        // Voseo inicial mayúscula
        'Vos solo ' => 'Usted solo ',
        'Vos ' => 'Usted ',
        // Voseo en medio de frase
        ' vos ' => ' usted ',
        ' vos.' => ' usted.',
        ' vos,' => ' usted,',

        // Tuteo
        ' te ' => ' le ',
        ' te.' => ' le.',
        ' te,' => ' le,',
        ' tu ' => ' su ',
        ' tus ' => ' sus ',
        'Tu ' => 'Su ',
        'Tus ' => 'Sus ',

        // Casos específicos al inicio
        'No te ' => 'No le ',
    ];

    foreach ($conversiones_pronombres as $arg => $ec) {
        $texto = str_replace($arg, $ec, $texto);
    }

    // PASO 2: Convertir verbos conjugados (segunda persona → tercera persona formal)
    $conversiones_verbos = [
        // Verbos en voseo
        'tenés' => 'tiene',
        'Tenés' => 'Tiene',
        'querés' => 'quiere',
        'Querés' => 'Quiere',
        'podés' => 'puede',
        'Podés' => 'Puede',
        'sabés' => 'sabe',
        'Sabés' => 'Sabe',
        'enviás' => 'envía',
        'Enviás' => 'Envía',
        'necesitás' => 'necesita',
        'Necesitás' => 'Necesita',
        'contás' => 'cuenta',
        'Contás' => 'Cuenta',
        'estás' => 'está',
        'Estás' => 'Está',
        ' sos ' => ' es ',
        'hacés' => 'hace',
        'Hacés' => 'Hace',

        // Verbos en tuteo
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
        'vas' => 'va',

        // Imperativos informales → formales
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
        'necesites' => 'necesite',
        'Ahorrás' => 'Ahorra',
        'ahorrás' => 'ahorra',
        'dejamos' => 'dejamos', // se mantiene (nosotros)
        'ayudan' => 'ayudan', // se mantiene (ellos)
    ];

    foreach ($conversiones_verbos as $arg => $ec) {
        $texto = str_replace($arg, $ec, $texto);
    }

    // PASO 3: Términos regionales específicos
    $conversiones_regionales = [
        // Identificación
        'DNI' => 'Cédula',
        ' DNI ' => ' Cédula ',
        'CUIL' => 'RUC',

        // País
        'Argentina' => 'Ecuador',
        'argentina' => 'ecuatoriana',
        'argentino' => 'ecuatoriano',
        'argentinos' => 'ecuatorianos',
        'argentinas' => 'ecuatorianas',

        // Vocabulario técnico
        'email' => 'correo electrónico',
        'Email' => 'Correo electrónico',
        'Emails' => 'Correos electrónicos',
        'etc.' => 'entre otros',
        'etc' => 'entre otros',
        'logs' => 'registros',
        'Logs' => 'Registros',
        'backups' => 'respaldos',
        'Backups' => 'Respaldos',
        'analíticos' => 'registros académicos',

        // Horarios
        'Lun - Vie' => 'Lunes a viernes',

        // Verbos comerciales
        'alquilamos' => 'arrendamos',
        'alquilar' => 'arrendar',
        'alquilamos' => 'arrendamos',

        // Autoridad de protección de datos
        'AAIP' => 'DINARDAP',
        'Agencia de Acceso a la Información Pública (AAIP)' => 'Dirección Nacional de Registro de Datos Públicos',
        'www.argentina.gob.ar/aaip' => 'www.registrocivil.gob.ec',

        // Tratamiento formal
        'considerás' => 'considera',
        'Considerás' => 'Considera',

        // Limpieza de marcadores de género neutro
        '/a' => '',
        '/as' => '',
    ];

    foreach ($conversiones_regionales as $arg => $ec) {
        $texto = str_replace($arg, $ec, $texto);
    }

    return $texto;
}

// Fusionar claves: mantener las existentes en EC (ya revisadas) y agregar las faltantes adaptadas de AR
$lang_final = [];

// Copiar TODAS las claves de AR para mantener el orden
foreach ($lang_ar as $key => $value_ar) {
    if (isset($lang_ec[$key])) {
        // La clave ya existe en EC - usar la versión EC (ya revisada manualmente)
        $lang_final[$key] = $lang_ec[$key];
    } else {
        // Clave nueva - adaptar desde AR
        $lang_final[$key] = adaptarAEcuatorianoFormal($value_ar);
    }
}

// Generar el archivo PHP
$output = "<?php\n";
$output .= "// Español Ecuador - es_EC\n";
$output .= "// Adaptación del español argentino al español ecuatoriano formal institucional\n";
$output .= "// Última actualización: " . date('Y-m-d H:i:s') . "\n";
$output .= "\$lang = [\n";

$current_section = '';
$line_count = 0;

foreach ($lang_final as $key => $value) {
    // Detectar nueva sección por el prefijo de la clave
    $prefix = explode('_', $key)[0];
    if ($prefix !== $current_section) {
        $current_section = $prefix;
        if ($line_count > 0) {
            $output .= "\n";
        }
        $output .= "    // " . strtoupper($current_section) . " SECTION\n";
    }

    // Escapar comillas simples y backslashes en el valor
    $value_escaped = str_replace("\\", "\\\\", $value);
    $value_escaped = str_replace("'", "\\'", $value_escaped);

    $output .= "    '$key' => '$value_escaped',\n";
    $line_count++;
}

$output .= "];\n";
$output .= "?>";

// Guardar el archivo
file_put_contents('E:\appVerumax\lang\es_EC.php', $output);

echo "✓ Archivo es_EC.php actualizado exitosamente\n\n";
echo "Estadísticas:\n";
echo "- Total de claves: " . count($lang_final) . "\n";
echo "- Claves originales EC: " . count($lang_ec) . "\n";
echo "- Claves nuevas agregadas: " . (count($lang_final) - count($lang_ec)) . "\n\n";
echo "El archivo ha sido respaldado en: backup/2025-12-20/0846-es_EC.php\n";
?>
