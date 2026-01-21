<?php
$file = 'E:/appVerumax/tools/template-editor/index.html';
$content = file_get_contents($file);

// Agregar barra de formato antes del textarea
$search = '<label class="property-label" style="width: 100%; margin-bottom: 0.25rem;">Contenido:</label>
                            <textarea class="property-input" id="prop-text" rows="3" style="resize: vertical; font-size: 0.75rem;"></textarea>';

$replace = '<label class="property-label" style="width: 100%; margin-bottom: 0.25rem;">Contenido:</label>
                            <div class="text-format-toolbar">
                                <button type="button" class="text-format-btn bold" onclick="insertFormat(\'**\')" title="Negrita (Ctrl+B)">B</button>
                                <button type="button" class="text-format-btn italic" onclick="insertFormat(\'*\')" title="Italica (Ctrl+I)">I</button>
                                <span style="font-size: 0.65rem; color: var(--gray-400); margin-left: auto;">**negrita** *italica*</span>
                            </div>
                            <textarea class="property-input" id="prop-text" rows="4"></textarea>';

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "HTML de barra de formato agregado\n";
} else {
    echo "No se encontro el patron\n";
    // Intentar buscar patron alternativo
    $search2 = '<textarea class="property-input" id="prop-text" rows="3" style="resize: vertical; font-size: 0.75rem;"></textarea>';
    if (strpos($content, $search2) !== false) {
        echo "Encontrado textarea alternativo\n";
    }
}
