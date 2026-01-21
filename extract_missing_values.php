<?php
// Safely include the language file
$lang_es_ar = [];
$lang_temp = [];
eval('?>' . str_replace('<?php', '', file_get_contents('E:\appVerumax\lang\es_AR.php')));
$lang_es_ar = $lang;

// Read missing keys
$missing_keys = array_filter(array_map('trim', file('E:\appVerumax\missing_keys_list.txt')));

// Create output file with PHP array format
$output = "<?php\n// Euskera - eu_ES\n\$lang = [\n";

// Process each missing key
foreach ($missing_keys as $key) {
    if (isset($lang_es_ar[$key])) {
        $value = $lang_es_ar[$key];
        // Escape the value properly
        $escaped_value = addslashes($value);
        $output .= "    '" . addslashes($key) . "' => '" . str_replace("'", "\'", $value) . "',\n";
    }
}

echo "Processed " . count($missing_keys) . " missing keys\n";
echo "Sample of first 5 keys to translate:\n\n";

$count = 0;
foreach ($missing_keys as $key) {
    if (++$count <= 5) {
        echo "Key: " . $key . "\n";
        echo "Value (es_AR): " . (isset($lang_es_ar[$key]) ? substr($lang_es_ar[$key], 0, 100) : "NOT FOUND") . "\n\n";
    }
}
