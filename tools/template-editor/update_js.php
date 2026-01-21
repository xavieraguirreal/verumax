<?php
$file = 'E:/appVerumax/tools/template-editor/index.html';
$content = file_get_contents($file);

// 1. Agregar función insertFormat() antes de replaceVariables()
$search1 = '        function replaceVariables(text) {';

$replace1 = '        // Insertar formato en textarea
        function insertFormat(marker) {
            const textarea = document.getElementById(\'prop-text\');
            if (!textarea) return;

            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const text = textarea.value;
            const selectedText = text.substring(start, end);

            let newText;
            if (selectedText) {
                // Hay texto seleccionado - envolverlo con marcadores
                newText = text.substring(0, start) + marker + selectedText + marker + text.substring(end);
                textarea.value = newText;
                // Posicionar cursor después del texto formateado
                textarea.selectionStart = textarea.selectionEnd = end + marker.length * 2;
            } else {
                // No hay selección - insertar marcadores y posicionar cursor en medio
                newText = text.substring(0, start) + marker + marker + text.substring(end);
                textarea.value = newText;
                textarea.selectionStart = textarea.selectionEnd = start + marker.length;
            }

            textarea.focus();
            // Disparar evento input para actualizar el elemento
            textarea.dispatchEvent(new Event(\'input\', { bubbles: true }));
        }

        // Atajos de teclado para formato
        document.addEventListener(\'keydown\', function(e) {
            const textarea = document.getElementById(\'prop-text\');
            if (document.activeElement !== textarea) return;

            if (e.ctrlKey && e.key === \'b\') {
                e.preventDefault();
                insertFormat(\'**\');
            } else if (e.ctrlKey && e.key === \'i\') {
                e.preventDefault();
                insertFormat(\'*\');
            }
        });

        // Convertir marcadores de formato a HTML
        function applyTextFormatting(text) {
            if (!text) return text;
            // Negrita: **texto** -> <b>texto</b>
            let result = text.replace(/\*\*(.+?)\*\*/g, \'<b>$1</b>\');
            // Italica: *texto* -> <i>texto</i> (solo asteriscos simples, no dobles)
            result = result.replace(/(?<!\*)\*([^*]+)\*(?!\*)/g, \'<i>$1</i>\');
            return result;
        }

        function replaceVariables(text) {';

if (strpos($content, $search1) !== false) {
    $content = str_replace($search1, $replace1, $content);
    echo "Funcion insertFormat() agregada\n";
} else {
    echo "No se encontro patron para insertFormat()\n";
}

// 2. Modificar replaceVariables para aplicar formato al final
$search2 = '            result = result.replace(/\{\{firmante_2_cargo\}\}/g, vars.firmante_2_cargo);

            return result;
        }';

$replace2 = '            result = result.replace(/\{\{firmante_2_cargo\}\}/g, vars.firmante_2_cargo);

            // Aplicar formato de texto (negrita, italica)
            result = applyTextFormatting(result);

            return result;
        }';

if (strpos($content, $search2) !== false) {
    $content = str_replace($search2, $replace2, $content);
    echo "replaceVariables() modificada para aplicar formato\n";
} else {
    echo "No se encontro patron para modificar replaceVariables()\n";
}

// 3. También necesitamos aplicar formato cuando NO está en modo preview
// Buscar en renderElements donde se usa displayText
$search3 = 'const displayText = replaceVariables(el.text);';
$replace3 = 'let displayText = replaceVariables(el.text);
                    // Aplicar formato incluso fuera de preview
                    if (!state.previewMode) {
                        displayText = applyTextFormatting(el.text);
                    }';

if (strpos($content, $search3) !== false) {
    $content = str_replace($search3, $replace3, $content);
    echo "renderElements() modificada para formato fuera de preview\n";
} else {
    echo "No se encontro patron para renderElements()\n";
}

file_put_contents($file, $content);
echo "Modificaciones JS completadas\n";
