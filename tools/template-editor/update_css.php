<?php
$file = 'E:/appVerumax/tools/template-editor/index.html';
$content = file_get_contents($file);

$search = '        .property-input-small {
            width: 70px;
        }

        .property-select {';

$replace = '        .property-input-small {
            width: 70px;
        }

        /* Textarea resize mejorado */
        textarea.property-input {
            min-height: 80px;
            max-height: 300px;
            resize: vertical;
            line-height: 1.4;
        }

        /* Barra de formato */
        .text-format-toolbar {
            display: flex;
            gap: 0.25rem;
            margin-bottom: 0.5rem;
            padding: 0.25rem;
            background: var(--gray-50);
            border-radius: 4px;
            border: 1px solid var(--gray-200);
        }

        .text-format-btn {
            padding: 0.25rem 0.5rem;
            border: 1px solid var(--gray-300);
            background: white;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-600);
            transition: all 0.15s;
        }

        .text-format-btn:hover {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        .text-format-btn.bold { font-weight: 700; }
        .text-format-btn.italic { font-style: italic; }

        .format-help {
            font-size: 0.65rem;
            color: var(--gray-400);
            margin-top: 0.25rem;
        }

        .property-select {';

if (strpos($content, $search) !== false) {
    $content = str_replace($search, $replace, $content);
    file_put_contents($file, $content);
    echo "CSS agregado correctamente\n";
} else {
    echo "No se encontro el patron a reemplazar\n";
}
