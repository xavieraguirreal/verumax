<?php
// Script para comparar archivos de idioma es_AR.php y es_ES.php

// Leer el contenido de ambos archivos
$es_ar_content = file_get_contents('lang/es_AR.php');
$es_es_content = file_get_contents('lang/es_ES.php');

// Extraer las entradas de cada archivo
function extract_entries($content) {
    $entries = [];
    // Patrón para detectar entradas de idioma
    preg_match_all("/'([^']*)'\s*=>\s*'([^']*)'/", $content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $entries[$match[1]] = $match[2];
    }
    
    // También buscar valores con comillas dobles
    preg_match_all('/"([^"]*)"\s*=>\s*"([^"]*)"/', $content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $match) {
        $entries[$match[1]] = $match[2];
    }
    
    return $entries;
}

$es_ar_entries = extract_entries($es_ar_content);
$es_es_entries = extract_entries($es_es_content);

// 1. Lista de entradas que están en es_AR.php pero faltan completamente en es_ES.php
$faltantes_es_es = [];
foreach ($es_ar_entries as $clave => $valor) {
    if (!isset($es_es_entries[$clave])) {
        $faltantes_es_es[$clave] = $valor;
    }
}

// 2. Lista de entradas que existen en ambos archivos pero tienen valores diferentes
$diferentes = [];
foreach ($es_ar_entries as $clave => $valor_ar) {
    if (isset($es_es_entries[$clave]) && $es_es_entries[$clave] !== $valor_ar) {
        $diferentes[$clave] = [
            'es_AR' => $valor_ar,
            'es_ES' => $es_es_entries[$clave]
        ];
    }
}

// 3. Adaptaciones lingüísticas necesarias entre español argentino y español de España
$adaptaciones_linguisticas = [
    // Variaciones típicas argentinas vs españolas
    'voseo' => [
        'title' => 'Voseo vs Tuteo',
        'description' => 'El español argentino usa "vos" en lugar de "tú", mientras que el español de España usa "tú".',
        'examples' => [
            'es_AR' => [
                '"Contanos sobre tu necesidad..."',
                '"Validar Ahora"',
                '"Podés cargar estudiantes de dos formas..."'
            ],
            'es_ES' => [
                '"Contáctenos sobre su necesidad..."',
                '"Validar Ahora"',
                '"Puede cargar estudiantes de dos formas..."'
            ]
        ]
    ],
    'terminos_argentinos' => [
        'title' => 'Términos específicos del español argentino',
        'description' => 'Términos que son propios del español argentino y deben adaptarse al español de España.',
        'examples' => [
            'es_AR' => [
                '"DNI"',
                '"CBU/CVU"',
                '"Peso argentino"',
                '"Garantías y Autenticidad"'
            ],
            'es_ES' => [
                '"DNI" o "NIF/NIE"',
                '"IBAN"',
                '"Euro"',
                '"Garantías y Autenticidad"'
            ]
        ]
    ],
    'expresiones_culturales' => [
        'title' => 'Expresiones culturales argentinas',
        'description' => 'Expresiones o ejemplos específicos de la cultura argentina que deben adaptarse al contexto español.',
        'examples' => [
            'es_AR' => [
                '"Sociedad Argentina de Justicia Restaurativa"',
                '"Cooperativa de Trabajo Liberté"',
                '"Emisión en 24hs"',
                '"Comenzar a emitir mañana"'
            ],
            'es_ES' => [
                '"Organización Española de Justicia Restaurativa"',
                '"Cooperativa de Trabajo Liberté"',
                '"Implementación en 24h"',
                '"Comenzar a emitir mañana"'
            ]
        ]
    ],
    'vocabulario_diferente' => [
        'title' => 'Vocabulario diferente entre variantes',
        'description' => 'Términos que se usan de forma diferente entre el español argentino y español de España.',
        'examples' => [
            'es_AR' => [
                '"emisión" (en lugar de "expedición")',
                '"estudiante" (más común que "alumno" en ciertos contextos)',
                '"tarjeta de contacto digital"'
            ],
            'es_ES' => [
                '"expedición" (más común en contextos oficiales)',
                '"alumno"',
                '"tarjeta de visita digital"'
            ]
        ]
    ]
];

// Imprimir los resultados
echo "=== ENTRADAS FALTANTES EN es_ES.php ===\n";
if (empty($faltantes_es_es)) {
    echo "No hay entradas que falten en es_ES.php.\n";
} else {
    foreach ($faltantes_es_es as $clave => $valor) {
        echo "'{$clave}' => '{$valor}'\n";
    }
}

echo "\n=== ENTRADAS CON VALORES DIFERENTES ===\n";
if (empty($diferentes)) {
    echo "No hay entradas con valores diferentes.\n";
} else {
    foreach ($diferentes as $clave => $valores) {
        echo "'{$clave}':\n";
        echo "  es_AR: '{$valores['es_AR']}'\n";
        echo "  es_ES: '{$valores['es_ES']}'\n";
    }
}

echo "\n=== ADAPTACIONES LINGÜÍSTICAS NECESARIAS ===\n";
foreach ($adaptaciones_linguisticas as $categoria => $info) {
    echo "\n{$info['title']}:\n";
    echo "{$info['description']}\n";
    echo "Ejemplos:\n";
    echo "  Español Argentino: ";
    foreach ($info['examples']['es_AR'] as $ejemplo) {
        echo $ejemplo . ", ";
    }
    echo "\n  Español España: ";
    foreach ($info['examples']['es_ES'] as $ejemplo) {
        echo $ejemplo . ", ";
    }
    echo "\n";
}
?>