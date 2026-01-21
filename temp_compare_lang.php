<?php
include 'lang/es_AR.php';
$ar = $lang;
include 'lang/es_CL.php';
$cl = $lang;

echo "AR keys: " . count($ar) . PHP_EOL;
echo "CL keys: " . count($cl) . PHP_EOL;
echo "Difference: " . (count($ar) - count($cl)) . PHP_EOL . PHP_EOL;

// Find missing keys in CL
$missing_in_cl = array_diff_key($ar, $cl);
echo "Keys missing in es_CL.php: " . count($missing_in_cl) . PHP_EOL . PHP_EOL;

// Show first 50 missing keys
$count = 0;
foreach ($missing_in_cl as $key => $value) {
    if ($count >= 50) {
        echo "... and " . (count($missing_in_cl) - 50) . " more" . PHP_EOL;
        break;
    }
    echo "  - $key" . PHP_EOL;
    $count++;
}
