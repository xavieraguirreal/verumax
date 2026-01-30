<?php
/**
 * Generador de imágenes Open Graph - Versión Pro con TTF
 * Ejecutar: php tools/generate_og_images.php
 */

// Rutas de fuentes
$fontBold = __DIR__ . '/../assets/fonts/PlayfairDisplay-Bold.ttf';
$fontRegular = __DIR__ . '/../assets/fonts/AnticDidone-Regular.ttf';

// Verificar fuentes
if (!file_exists($fontBold)) {
    die("Error: No se encontró la fuente $fontBold\n");
}

// Configuración de imágenes
$images = [
    'og-image-verumax.png' => [
        'bg_color' => [5, 5, 5],
        'accent_color' => [212, 175, 55],    // Dorado
        'accent_light' => [240, 211, 119],   // Dorado claro
        'title' => 'VERUMAX',
        'subtitle' => 'Del Código C a la Nube',
        'tagline' => 'Desarrollo de Software a Medida | PHP · Python · JavaScript',
    ],
    'og-image-fabricatum.png' => [
        'bg_color' => [10, 10, 15],
        'accent_color' => [0, 255, 242],     // Neon cyan
        'accent_light' => [100, 255, 250],
        'title' => 'FABRICATUM',
        'subtitle' => 'Desarrollo Web y Sistemas',
        'tagline' => 'Código Propio + Inteligencia Artificial | by Verumax',
    ],
    'og-image-credencialis.png' => [
        'bg_color' => [5, 5, 10],
        'accent_color' => [8, 145, 178],     // Cyan/Teal
        'accent_light' => [34, 211, 238],
        'title' => 'CREDENCIALIS',
        'subtitle' => 'Credenciales Digitales',
        'tagline' => 'Carnets de Membresía con QR Verificable | by Verumax',
    ],
];

$width = 1200;
$height = 630;

foreach ($images as $filename => $config) {
    echo "Generando $filename...\n";

    // Crear imagen con alpha
    $img = imagecreatetruecolor($width, $height);
    imagesavealpha($img, true);

    // Colores
    $bg = imagecolorallocate($img, $config['bg_color'][0], $config['bg_color'][1], $config['bg_color'][2]);
    $accent = imagecolorallocate($img, $config['accent_color'][0], $config['accent_color'][1], $config['accent_color'][2]);
    $accentLight = imagecolorallocate($img, $config['accent_light'][0], $config['accent_light'][1], $config['accent_light'][2]);
    $white = imagecolorallocate($img, 255, 255, 255);
    $gray = imagecolorallocate($img, 156, 163, 175);
    $darkGray = imagecolorallocate($img, 40, 40, 45);

    // Fondo
    imagefill($img, 0, 0, $bg);

    // Patrón de grid diagonal sutil
    for ($i = -$height; $i < $width + $height; $i += 40) {
        $gridColor = imagecolorallocatealpha($img, $config['accent_color'][0], $config['accent_color'][1], $config['accent_color'][2], 123);
        imageline($img, $i, 0, $i + $height, $height, $gridColor);
    }

    // Gradiente superior (barra de acento)
    for ($y = 0; $y < 6; $y++) {
        $alpha = 30 + $y * 15;
        $lineColor = imagecolorallocatealpha($img, $config['accent_color'][0], $config['accent_color'][1], $config['accent_color'][2], $alpha);
        imagefilledrectangle($img, 0, $y, $width, $y, $lineColor);
    }

    // Gradiente inferior
    for ($y = 0; $y < 6; $y++) {
        $alpha = 30 + $y * 15;
        $lineColor = imagecolorallocatealpha($img, $config['accent_color'][0], $config['accent_color'][1], $config['accent_color'][2], $alpha);
        imagefilledrectangle($img, 0, $height - 1 - $y, $width, $height - 1 - $y, $lineColor);
    }

    // Cargar logo de Verumax
    $logoPath = __DIR__ . '/../assets/images/logo-verumax-escudo.png';
    $logoY = 80;
    if (file_exists($logoPath)) {
        $logo = imagecreatefrompng($logoPath);
        if ($logo) {
            $logoWidth = imagesx($logo);
            $logoHeight = imagesy($logo);
            $newLogoHeight = 140;
            $newLogoWidth = (int)($logoWidth * ($newLogoHeight / $logoHeight));

            $logoX = ($width - $newLogoWidth) / 2;

            imagecopyresampled($img, $logo, $logoX, $logoY, 0, 0, $newLogoWidth, $newLogoHeight, $logoWidth, $logoHeight);
            imagedestroy($logo);
        }
    }

    // Título principal con TTF
    $titleSize = 72;
    $titleBox = imagettfbbox($titleSize, 0, $fontBold, $config['title']);
    $titleWidth = abs($titleBox[4] - $titleBox[0]);
    $titleX = ($width - $titleWidth) / 2;
    $titleY = 310;

    // Sombra del título
    imagettftext($img, $titleSize, 0, $titleX + 3, $titleY + 3, $darkGray, $fontBold, $config['title']);
    // Título
    imagettftext($img, $titleSize, 0, $titleX, $titleY, $accent, $fontBold, $config['title']);

    // Subtítulo
    $subSize = 32;
    $subBox = imagettfbbox($subSize, 0, $fontRegular, $config['subtitle']);
    $subWidth = abs($subBox[4] - $subBox[0]);
    $subX = ($width - $subWidth) / 2;
    $subY = 380;
    imagettftext($img, $subSize, 0, $subX, $subY, $white, $fontRegular, $config['subtitle']);

    // Tagline
    $tagSize = 18;
    $tagBox = imagettfbbox($tagSize, 0, $fontRegular, $config['tagline']);
    $tagWidth = abs($tagBox[4] - $tagBox[0]);
    $tagX = ($width - $tagWidth) / 2;
    $tagY = 440;
    imagettftext($img, $tagSize, 0, $tagX, $tagY, $gray, $fontRegular, $config['tagline']);

    // URL en la esquina inferior
    $urlSize = 16;
    $url = "verumax.com";
    $urlBox = imagettfbbox($urlSize, 0, $fontRegular, $url);
    $urlWidth = abs($urlBox[4] - $urlBox[0]);
    imagettftext($img, $urlSize, 0, $width - $urlWidth - 40, $height - 30, $accent, $fontRegular, $url);

    // Decoraciones: líneas en las esquinas
    imagesetthickness($img, 2);
    // Esquina superior izquierda
    imageline($img, 30, 30, 30, 80, $accent);
    imageline($img, 30, 30, 80, 30, $accent);
    // Esquina superior derecha
    imageline($img, $width - 30, 30, $width - 30, 80, $accent);
    imageline($img, $width - 30, 30, $width - 80, 30, $accent);
    // Esquina inferior izquierda
    imageline($img, 30, $height - 30, 30, $height - 80, $accent);
    imageline($img, 30, $height - 30, 80, $height - 30, $accent);
    // Esquina inferior derecha
    imageline($img, $width - 30, $height - 30, $width - 30, $height - 80, $accent);
    imageline($img, $width - 30, $height - 30, $width - 80, $height - 30, $accent);

    // Guardar con máxima calidad
    $outputPath = __DIR__ . '/../' . $filename;
    imagepng($img, $outputPath, 1); // Menor compresión = mejor calidad
    imagedestroy($img);

    $size = filesize($outputPath);
    echo "  -> Guardado: $outputPath (" . round($size/1024) . " KB)\n";
}

echo "\n✓ Imágenes OG generadas correctamente!\n";
echo "  Tamaño: {$width}x{$height} píxeles\n";
echo "  Formato: PNG optimizado\n";
