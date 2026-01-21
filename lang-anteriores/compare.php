<?php
// This script compares es_AR.php and ca_ES.php language files

// Function to extract language array from file
function getLangArray($file) {
    $content = file_get_contents($file);
    // Remove PHP opening tag and everything before the array
    $content = preg_replace('/^.*?<\\?php[\\r\\n]*/s', '', $content);
    $content = trim($content);
    
    // Create a temporary file to include the array
    $tempFile = tempnam(sys_get_temp_dir(), 'lang_');
    file_put_contents($tempFile, "<?php\n" . $content);
    
    // Extract the array by including the file
    $lang = [];
    include $tempFile;
    
    // Clean up
    unlink($tempFile);
    
    return $lang;
}

$es_ar = getLangArray('D:/validarcert/lang/es_AR.php');
$ca_es = getLangArray('D:/validarcert/lang/ca_ES.php');

// Find missing entries in ca_ES compared to es_AR
$missing = array_diff_key($es_ar, $ca_es);

// Find entries with different values
$different = [];
foreach ($es_ar as $key => $value) {
    if (isset($ca_es[$key]) && $ca_es[$key] !== $value) {
        $different[$key] = [
            'es_AR' => $value,
            'ca_ES' => $ca_es[$key]
        ];
    }
}

// Output missing entries in PHP format
echo "Missing entries in ca_ES.php:\n\n";
if (empty($missing)) {
    echo "No missing entries found.\n";
} else {
    foreach ($missing as $key => $value) {
        echo "    '$key' => '" . addslashes($value) . "',\n";
    }
}

echo "\n\nEntries with different values:\n\n";
if (empty($different)) {
    echo "No entries with different values found.\n";
} else {
    foreach ($different as $key => $values) {
        echo "Key: $key\n";
        echo "  es_AR: '" . addslashes($values['es_AR']) . "'\n";
        echo "  ca_ES: '" . addslashes($values['ca_ES']) . "'\n\n";
    }
}
?>