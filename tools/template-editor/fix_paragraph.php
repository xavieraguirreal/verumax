<?php
// =============================================
// PARTE 1: Modificar index.html del editor
// =============================================
$file_editor = 'E:/appVerumax/tools/template-editor/index.html';
$content_editor = file_get_contents($file_editor);

// 1a. Modificar exportJSON para incluir 'paragraph' en la exportación de texto
$search1 = "if (el.type === 'text' || el.type === 'title' || el.type === 'text-custom') {";
$replace1 = "if (el.type === 'text' || el.type === 'title' || el.type === 'text-custom' || el.type === 'paragraph') {";

if (strpos($content_editor, $search1) !== false) {
    $content_editor = str_replace($search1, $replace1, $content_editor);
    echo "Editor: exportJSON modificado para incluir paragraph\n";
} else {
    echo "Editor: No se encontro patron exportJSON\n";
}

// 1b. Modificar el texto por defecto del párrafo para incluir marcadores de formato
$search2 = 'data-text="El día {{fecha}} se certifica que {{nombre_completo}} con DNI {{dni}} ha completado el curso {{nombre_curso}}."';
$replace2 = 'data-text="El día {{fecha}} se certifica que **{{nombre_completo}}** con DNI **{{dni}}** ha completado y aprobado satisfactoriamente el curso **{{nombre_curso}}** con una carga horaria de {{carga_horaria}} horas."';

if (strpos($content_editor, $search2) !== false) {
    $content_editor = str_replace($search2, $replace2, $content_editor);
    echo "Editor: Texto por defecto del parrafo actualizado con marcadores\n";
} else {
    echo "Editor: No se encontro patron texto parrafo\n";
}

file_put_contents($file_editor, $content_editor);
echo "Editor: Modificaciones completadas\n\n";

// =============================================
// PARTE 2: Modificar creare.php
// =============================================
$file_creare = 'E:/appVerumax/certificatum/creare.php';
$content_creare = file_get_contents($file_creare);

// 2a. Modificar renderizado del párrafo para usar texto del JSON si existe
$search3 = '                <?php elseif ($type === \'paragraph\'): ?>
                <?php
                    // Párrafo usa valores por defecto si no están definidos
                    $p_font = !empty($element[\'font\']) ? $element[\'font\'] : \'Arial\';
                    // El editor exporta size en PUNTOS (px * 0.75), convertir de vuelta a px
                    $p_size_pt = !empty($element[\'size\']) ? $element[\'size\'] : 10;
                    $p_size = $p_size_pt / 0.75;  // pt to px
                    $p_color = !empty($element[\'color\']) ? $element[\'color\'] : \'#333333\';
                    $p_align = !empty($element[\'align\']) ? $element[\'align\'] : \'left\';
                ?>
                <div class="template-element"
                     style="left: <?php echo $x_px; ?>px;
                            top: <?php echo $y_px; ?>px;
                            width: <?php echo $w_px; ?>px;
                            height: <?php echo $h_px; ?>px;">
                    <span style="font-family: \'<?php echo $p_font; ?>\', sans-serif;
                            font-size: <?php echo $p_size; ?>px;
                            color: <?php echo $p_color; ?>;
                            text-align: <?php echo $p_align; ?>;
                            display: block;
                            width: 100%;
                            line-height: 1.4;
                            white-space: normal;
                            overflow: hidden;"><?php echo applyTextFormatting($parrafo_default); ?></span>
                </div>';

$replace3 = '                <?php elseif ($type === \'paragraph\'): ?>
                <?php
                    // Párrafo usa valores por defecto si no están definidos
                    $p_font = !empty($element[\'font\']) ? $element[\'font\'] : \'Antic Didone\';
                    // El editor exporta size en PUNTOS (px * 0.75), convertir de vuelta a px
                    $p_size_pt = !empty($element[\'size\']) ? $element[\'size\'] : 10;
                    $p_size = $p_size_pt / 0.75;  // pt to px
                    $p_color = !empty($element[\'color\']) ? $element[\'color\'] : \'#333333\';
                    $p_align = !empty($element[\'align\']) ? $element[\'align\'] : \'left\';

                    // Usar texto del JSON si existe, sino usar párrafo por defecto
                    $p_texto = $parrafo_default;
                    if (!empty($element[\'text\'])) {
                        // Reemplazar variables en el texto del JSON
                        $p_texto = preg_replace_callback(\'/\\{\\{(\\w+)\\}\\}/\', function($matches) use ($variables) {
                            return $variables[$matches[1]] ?? $matches[0];
                        }, $element[\'text\']);
                    }
                ?>
                <div class="template-element"
                     style="left: <?php echo $x_px; ?>px;
                            top: <?php echo $y_px; ?>px;
                            width: <?php echo $w_px; ?>px;
                            height: <?php echo $h_px; ?>px;">
                    <span style="font-family: \'<?php echo $p_font; ?>\', sans-serif;
                            font-size: <?php echo $p_size; ?>px;
                            color: <?php echo $p_color; ?>;
                            text-align: <?php echo $p_align; ?>;
                            display: block;
                            width: 100%;
                            line-height: 1.4;
                            white-space: normal;
                            overflow: hidden;"><?php echo applyTextFormatting($p_texto); ?></span>
                </div>';

if (strpos($content_creare, $search3) !== false) {
    $content_creare = str_replace($search3, $replace3, $content_creare);
    echo "Creare: Renderizado de parrafo modificado para usar texto del JSON\n";
} else {
    echo "Creare: No se encontro patron de parrafo\n";
}

file_put_contents($file_creare, $content_creare);
echo "Creare: Modificaciones completadas\n";
