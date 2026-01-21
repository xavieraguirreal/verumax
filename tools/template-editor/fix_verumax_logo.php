<?php
// Script para agregar elemento Logo Verumax

$file = __DIR__ . '/index.html';
$content = file_get_contents($file);

// 1. Agregar elemento draggable Logo Verumax despues de Logo Secundario
$search1 = '<div class="element-item" draggable="true" data-type="image" data-variable="{{logo_2}}" data-label="Logo Secundario">
                        <svg class="element-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                        Logo Secundario
                    </div>
                    <div class="element-item" draggable="true" data-type="qr"';

$replace1 = '<div class="element-item" draggable="true" data-type="image" data-variable="{{logo_2}}" data-label="Logo Secundario">
                        <svg class="element-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>
                        Logo Secundario
                    </div>
                    <div class="element-item" draggable="true" data-type="image" data-variable="{{logo_verumax}}" data-label="Logo Verumax">
                        <svg class="element-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 12l2 2 4-4"/></svg>
                        Logo Verumax
                    </div>
                    <div class="element-item" draggable="true" data-type="qr"';

$count = 0;

if (strpos($content, $search1) !== false) {
    $content = str_replace($search1, $replace1, $content);
    $count++;
    echo "1. Elemento Logo Verumax agregado\n";
} else {
    echo "1. Patron elemento no encontrado (quizas ya existe)\n";
}

// 2. Agregar uploader para Logo Verumax en el preview (despues de Logo Secundario uploader)
$search2 = '<div class="preview-upload-group">
                        <label>Logo Secundario:</label>
                        <div class="preview-upload-box" id="preview-logo2-box" onclick="document.getElementById(\'file-logo2\').click()">
                            <img id="preview-logo2-img" style="display:none; max-width:100%; max-height:60px;">
                            <span id="preview-logo2-text">Click para cargar</span>
                        </div>
                        <input type="file" id="file-logo2" accept="image/*" style="display:none;">
                    </div>

                    <div class="preview-upload-group">
                        <label>Firma 1:</label>';

$replace2 = '<div class="preview-upload-group">
                        <label>Logo Secundario:</label>
                        <div class="preview-upload-box" id="preview-logo2-box" onclick="document.getElementById(\'file-logo2\').click()">
                            <img id="preview-logo2-img" style="display:none; max-width:100%; max-height:60px;">
                            <span id="preview-logo2-text">Click para cargar</span>
                        </div>
                        <input type="file" id="file-logo2" accept="image/*" style="display:none;">
                    </div>

                    <div class="preview-upload-group">
                        <label>Logo Verumax:</label>
                        <div class="preview-upload-box" id="preview-logo-verumax-box" onclick="document.getElementById(\'file-logo-verumax\').click()">
                            <img id="preview-logo-verumax-img" style="display:none; max-width:100%; max-height:60px;">
                            <span id="preview-logo-verumax-text">Click para cargar</span>
                        </div>
                        <input type="file" id="file-logo-verumax" accept="image/*" style="display:none;">
                    </div>

                    <div class="preview-upload-group">
                        <label>Firma 1:</label>';

if (strpos($content, $search2) !== false) {
    $content = str_replace($search2, $replace2, $content);
    $count++;
    echo "2. Uploader Logo Verumax agregado\n";
} else {
    echo "2. Patron uploader no encontrado\n";
}

// 3. Agregar event listener para el uploader
$search3 = "document.getElementById('file-logo2').addEventListener('change', (e) => handlePreviewImageUpload(e, 'logo_2'));
            document.getElementById('file-firma1')";

$replace3 = "document.getElementById('file-logo2').addEventListener('change', (e) => handlePreviewImageUpload(e, 'logo_2'));
            document.getElementById('file-logo-verumax').addEventListener('change', (e) => handlePreviewImageUpload(e, 'logo_verumax'));
            document.getElementById('file-firma1')";

if (strpos($content, $search3) !== false) {
    $content = str_replace($search3, $replace3, $content);
    $count++;
    echo "3. Event listener agregado\n";
} else {
    echo "3. Patron event listener no encontrado\n";
}

if ($count > 0) {
    file_put_contents($file, $content);
    echo "\nArchivo actualizado con $count cambios\n";
} else {
    echo "\nNo se realizaron cambios\n";
}
