<?php
$file = __DIR__ . '/creare.php';
$content = file_get_contents($file);

// Buscar y reemplazar línea por línea
$content = str_replace(
    'white-space: nowrap;',
    'white-space: normal; word-wrap: break-word;',
    $content,
    $count
);

// También cambiar overflow: hidden a visible para textos
$content = preg_replace(
    '/overflow: hidden;"\>\<\?php echo applyTextFormatting\(\$content\);/',
    'overflow: visible;"><?php echo applyTextFormatting($content);',
    $content
);

file_put_contents($file, $content);
echo "OK: Realizados $count reemplazos de white-space\n";
