<?php
// Load the Spanish Argentina file
$es_content = file_get_contents('es_AR.php');
$el_content = file_get_contents('el_GR.php');

// Extract the language arrays using regex
preg_match('/\\$lang = \[(.*?)\];/s', $es_content, $es_matches);
preg_match('/\\$lang = \[(.*?)\];/s', $el_content, $el_matches);

$es_lang_content = $es_matches[1];
$el_lang_content = $el_matches[1];

// Process the Spanish Argentina array content
$es_lang = [];
$pattern = "/'([^']*)'\s*=>\s*'([^']*)'/";
preg_match_all($pattern, $es_lang_content, $matches, PREG_SET_ORDER);
foreach ($matches as $match) {
    $key = $match[1];
    $value = $match[2];
    $es_lang[$key] = $value;
}

// Process the Greek Greece array content
$el_lang = [];
preg_match_all($pattern, $el_lang_content, $matches, PREG_SET_ORDER);
foreach ($matches as $match) {
    $key = $match[1];
    $value = $match[2];
    $el_lang[$key] = $value;
}

// Find entries that exist in es_AR but not in el_GR
$faltantes = [];
foreach ($es_lang as $key => $value) {
    if (!isset($el_lang[$key])) {
        $faltantes[$key] = $value;
    }
}

// Find entries that have different values between the two files
$diferentes = [];
foreach ($es_lang as $key => $es_value) {
    if (isset($el_lang[$key]) && $el_lang[$key] !== $es_value) {
        $diferentes[$key] = [
            'es_AR' => $es_value,
            'el_GR' => $el_lang[$key]
        ];
    }
}

echo "=== ENTRIES MISSING IN el_GR.php ===\n\n";
foreach ($faltantes as $key => $value) {
    echo "'{$key}' => '{$value}',\n";
}

echo "\n=== ENTRIES WITH DIFFERENT VALUES ===\n\n";
foreach ($diferentes as $key => $values) {
    echo "'{$key}' => es_AR: '{$values['es_AR']}', el_GR: '{$values['el_GR']}',\n";
}
?>