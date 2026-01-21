<?php
/**
 * MANUAL DE USUARIO - VERUMax
 * Renderiza el manual desde docs/manual_usuario.md
 * Permite descarga en PDF
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['admin_verumax'])) {
    header('Location: login.php');
    exit;
}

$admin = $_SESSION['admin_verumax'];

// Leer el contenido del manual
$manual_path = __DIR__ . '/../docs/manual_usuario.md';
$manual_content = file_exists($manual_path) ? file_get_contents($manual_path) : '# Manual no encontrado';

// Acción: Generar PDF
if (isset($_GET['pdf'])) {
    require_once __DIR__ . '/../vendor/autoload.php';

    // Convertir markdown a HTML básico para el PDF
    $html_content = convertMarkdownToHtml($manual_content);

    $mpdf = new \Mpdf\Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 20,
        'margin_right' => 20,
        'margin_top' => 25,
        'margin_bottom' => 20,
        'margin_header' => 10,
        'margin_footer' => 10,
    ]);

    $mpdf->SetTitle('Manual de Usuario - VERUMax');
    $mpdf->SetAuthor('VERUMax');
    $mpdf->SetCreator('VERUMax Admin Panel');

    // Header
    $mpdf->SetHTMLHeader('
        <div style="border-bottom: 1px solid #ddd; padding-bottom: 5px; font-size: 10px; color: #666;">
            <span style="font-weight: bold;">Manual de Usuario - VERUMax</span>
            <span style="float: right;">Página {PAGENO} de {nbpg}</span>
        </div>
    ');

    // Footer
    $mpdf->SetHTMLFooter('
        <div style="border-top: 1px solid #ddd; padding-top: 5px; font-size: 9px; color: #999; text-align: center;">
            Generado desde el Panel de Administración de VERUMax - ' . date('d/m/Y H:i') . '
        </div>
    ');

    // Estilos del PDF
    $css = '
        <style>
            body { font-family: Arial, sans-serif; font-size: 11pt; line-height: 1.6; color: #333; }
            h1 { color: #1e40af; font-size: 24pt; border-bottom: 2px solid #1e40af; padding-bottom: 10px; margin-top: 30px; }
            h2 { color: #1e3a8a; font-size: 18pt; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; margin-top: 25px; }
            h3 { color: #1e40af; font-size: 14pt; margin-top: 20px; }
            h4 { color: #374151; font-size: 12pt; margin-top: 15px; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th { background: #f3f4f6; border: 1px solid #d1d5db; padding: 10px; text-align: left; font-weight: 600; }
            td { border: 1px solid #d1d5db; padding: 8px; }
            code { background: #f3f4f6; padding: 2px 6px; border-radius: 3px; font-family: monospace; font-size: 10pt; }
            pre { background: #1f2937; color: #f9fafb; padding: 15px; border-radius: 5px; overflow-x: auto; }
            pre code { background: transparent; color: inherit; }
            blockquote { border-left: 4px solid #3b82f6; padding-left: 15px; margin: 15px 0; color: #4b5563; }
            ul, ol { margin-left: 20px; }
            li { margin-bottom: 5px; }
            hr { border: none; border-top: 1px solid #e5e7eb; margin: 20px 0; }
            a { color: #2563eb; text-decoration: none; }
            strong { color: #111827; }
            .toc { background: #f9fafb; padding: 20px; border-radius: 8px; margin-bottom: 30px; }
            .toc h2 { margin-top: 0; border: none; }
            .toc ul { list-style: none; margin: 0; padding: 0; }
            .toc li { padding: 5px 0; }
        </style>
    ';

    $mpdf->WriteHTML($css . $html_content);
    $mpdf->Output('Manual_Usuario_VERUMax.pdf', 'D');
    exit;
}

/**
 * Convierte Markdown básico a HTML
 * Para renderizado en PDF
 */
function convertMarkdownToHtml($markdown) {
    $html = $markdown;

    // Escapar HTML existente
    $html = htmlspecialchars($html, ENT_NOQUOTES);

    // Bloques de código
    $html = preg_replace('/```(\w*)\n([\s\S]*?)```/m', '<pre><code>$2</code></pre>', $html);

    // Código inline
    $html = preg_replace('/`([^`]+)`/', '<code>$1</code>', $html);

    // Headers
    $html = preg_replace('/^######\s+(.*)$/m', '<h6>$1</h6>', $html);
    $html = preg_replace('/^#####\s+(.*)$/m', '<h5>$1</h5>', $html);
    $html = preg_replace('/^####\s+(.*)$/m', '<h4>$1</h4>', $html);
    $html = preg_replace('/^###\s+(.*)$/m', '<h3>$1</h3>', $html);
    $html = preg_replace('/^##\s+(.*)$/m', '<h2>$1</h2>', $html);
    $html = preg_replace('/^#\s+(.*)$/m', '<h1>$1</h1>', $html);

    // Bold e Italic
    $html = preg_replace('/\*\*\*([^*]+)\*\*\*/', '<strong><em>$1</em></strong>', $html);
    $html = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $html);
    $html = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $html);

    // Links
    $html = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $html);

    // HR
    $html = preg_replace('/^---+$/m', '<hr>', $html);

    // Tablas (básico)
    $html = preg_replace_callback('/^\|(.+)\|$/m', function($matches) {
        $cells = explode('|', trim($matches[1]));
        $row = '<tr>';
        foreach ($cells as $cell) {
            $cell = trim($cell);
            if (preg_match('/^[-:]+$/', $cell)) {
                return ''; // Línea separadora, ignorar
            }
            $row .= '<td>' . $cell . '</td>';
        }
        $row .= '</tr>';
        return $row;
    }, $html);

    // Envolver filas de tabla
    $html = preg_replace('/(<tr>.*?<\/tr>\s*)+/s', '<table>$0</table>', $html);

    // Listas
    $html = preg_replace('/^\s*[-*]\s+(.*)$/m', '<li>$1</li>', $html);
    $html = preg_replace('/(<li>.*<\/li>\s*)+/s', '<ul>$0</ul>', $html);

    // Listas numeradas
    $html = preg_replace('/^\s*\d+\.\s+(.*)$/m', '<li>$1</li>', $html);

    // Párrafos
    $html = preg_replace('/\n\n+/', '</p><p>', $html);
    $html = '<p>' . $html . '</p>';

    // Limpiar párrafos vacíos y alrededor de bloques
    $html = preg_replace('/<p>\s*<(h[1-6]|ul|ol|table|pre|hr|blockquote)/s', '<$1', $html);
    $html = preg_replace('/<\/(h[1-6]|ul|ol|table|pre|hr|blockquote)>\s*<\/p>/s', '</$1>', $html);
    $html = preg_replace('/<p>\s*<\/p>/', '', $html);

    return $html;
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Manual de Usuario - <?php echo htmlspecialchars($admin['nombre']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }

        /* Estilos para el contenido renderizado del manual */
        .manual-content {
            line-height: 1.8;
        }
        .manual-content h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1e40af;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 0.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .manual-content h2 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #1e3a8a;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 0.5rem;
            margin-top: 2rem;
            margin-bottom: 1rem;
        }
        .manual-content h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1e40af;
            margin-top: 1.5rem;
            margin-bottom: 0.75rem;
        }
        .manual-content h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #374151;
            margin-top: 1.25rem;
            margin-bottom: 0.5rem;
        }
        .manual-content p {
            margin-bottom: 1rem;
            color: #374151;
        }
        .manual-content table {
            width: 100%;
            border-collapse: collapse;
            margin: 1rem 0;
            font-size: 0.9rem;
        }
        .manual-content th {
            background: #f3f4f6;
            border: 1px solid #d1d5db;
            padding: 0.75rem;
            text-align: left;
            font-weight: 600;
        }
        .manual-content td {
            border: 1px solid #d1d5db;
            padding: 0.75rem;
        }
        .manual-content code {
            background: #f3f4f6;
            padding: 0.15rem 0.4rem;
            border-radius: 0.25rem;
            font-family: ui-monospace, monospace;
            font-size: 0.875rem;
        }
        .manual-content pre {
            background: #1f2937;
            color: #f9fafb;
            padding: 1rem;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin: 1rem 0;
        }
        .manual-content pre code {
            background: transparent;
            color: inherit;
            padding: 0;
        }
        .manual-content ul, .manual-content ol {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
        .manual-content li {
            margin-bottom: 0.5rem;
        }
        .manual-content hr {
            border: none;
            border-top: 1px solid #e5e7eb;
            margin: 2rem 0;
        }
        .manual-content blockquote {
            border-left: 4px solid #3b82f6;
            padding-left: 1rem;
            margin: 1rem 0;
            color: #4b5563;
            font-style: italic;
        }
        .manual-content a {
            color: #2563eb;
            text-decoration: underline;
        }
        .manual-content a:hover {
            color: #1d4ed8;
        }
        /* Demo links - styled as buttons */
        .manual-content a[href*="demos/demo_"] {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            color: white !important;
            text-decoration: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.2s;
            box-shadow: 0 4px 6px -1px rgba(34, 197, 94, 0.3);
        }
        .manual-content a[href*="demos/demo_"]:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 12px -1px rgba(34, 197, 94, 0.4);
        }
        .manual-content a[href*="demos/demo_"]::before {
            content: "";
            display: inline-block;
            width: 20px;
            height: 20px;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolygon points='5 3 19 12 5 21 5 3'%3E%3C/polygon%3E%3C/svg%3E");
            background-size: contain;
            background-repeat: no-repeat;
        }
        /* Disabled demo links (Próximamente) */
        .manual-content p:has(a[href*="demos/demo_"]):has(em) a[href*="demos/demo_"] {
            background: linear-gradient(135deg, #9ca3af 0%, #6b7280 100%);
            cursor: not-allowed;
            pointer-events: none;
            box-shadow: none;
        }
        .manual-content p:has(a[href*="demos/demo_"]):has(em) a[href*="demos/demo_"]::before {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='10'%3E%3C/circle%3E%3Cline x1='12' y1='8' x2='12' y2='12'%3E%3C/line%3E%3Cline x1='12' y1='16' x2='12.01' y2='16'%3E%3C/line%3E%3C/svg%3E");
        }
        .manual-content strong {
            color: #111827;
        }

        /* TOC */
        .toc {
            position: sticky;
            top: 80px;
            max-height: calc(100vh - 100px);
            overflow-y: auto;
        }
        .toc a {
            display: block;
            padding: 0.35rem 0;
            color: #6b7280;
            text-decoration: none;
            font-size: 0.875rem;
            transition: all 0.2s;
        }
        .toc a:hover {
            color: #1e40af;
        }
        .toc a.active {
            color: #1e40af;
            font-weight: 500;
        }
        .toc .toc-h3 {
            padding-left: 1rem;
            font-size: 0.8rem;
        }

        /* Scrollbar */
        .toc::-webkit-scrollbar {
            width: 4px;
        }
        .toc::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .toc::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 2px;
        }

        /* Print */
        @media print {
            .no-print { display: none !important; }
            .manual-content { max-width: 100% !important; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">

    <!-- Header -->
    <header class="bg-white shadow-sm border-b sticky top-0 z-50 no-print">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center gap-4">
                    <a href="index.php" class="flex items-center gap-2 text-gray-600 hover:text-gray-900 transition">
                        <i data-lucide="arrow-left" class="w-5 h-5"></i>
                        <span>Volver al Panel</span>
                    </a>
                    <div class="w-px h-6 bg-gray-300"></div>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg flex items-center justify-center">
                            <i data-lucide="book-open" class="w-6 h-6 text-white"></i>
                        </div>
                        <div>
                            <h1 class="text-lg font-bold text-gray-900">Manual de Usuario</h1>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($admin['nombre']); ?></p>
                        </div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button onclick="window.print()"
                            class="flex items-center gap-2 px-4 py-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-lg transition">
                        <i data-lucide="printer" class="w-4 h-4"></i>
                        <span class="hidden sm:inline">Imprimir</span>
                    </button>
                    <a href="?pdf=1"
                       class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition shadow-sm">
                        <i data-lucide="download" class="w-4 h-4"></i>
                        <span>Descargar PDF</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="flex gap-8">

            <!-- Sidebar TOC -->
            <aside class="hidden lg:block w-64 flex-shrink-0 no-print">
                <div class="toc bg-white rounded-xl shadow-sm border p-4">
                    <h2 class="font-semibold text-gray-900 mb-3 flex items-center gap-2">
                        <i data-lucide="list" class="w-4 h-4 text-blue-600"></i>
                        Contenido
                    </h2>
                    <nav id="toc-nav">
                        <!-- Se genera dinámicamente -->
                    </nav>
                </div>
            </aside>

            <!-- Manual Content -->
            <article class="flex-1 min-w-0">
                <div class="bg-white rounded-xl shadow-sm border p-8 lg:p-12">
                    <div id="manual-content" class="manual-content">
                        <!-- Se renderiza con marked.js -->
                    </div>
                </div>

                <!-- Metadata -->
                <div class="mt-6 text-center text-sm text-gray-500">
                    <p>Manual generado desde el sistema de ayuda de VERUMax</p>
                    <p>Versión 1.0 - Actualizado: <?php echo date('d/m/Y'); ?></p>
                </div>
            </article>

        </div>
    </main>

    <!-- Botón flotante para móvil -->
    <button id="btn-toc-mobile"
            class="lg:hidden fixed bottom-6 right-6 w-14 h-14 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg flex items-center justify-center z-40 transition-all hover:scale-110 no-print">
        <i data-lucide="list" class="w-6 h-6"></i>
    </button>

    <!-- Modal TOC móvil -->
    <div id="modal-toc" class="lg:hidden fixed inset-0 bg-black/50 z-50 hidden no-print">
        <div class="absolute bottom-0 left-0 right-0 bg-white rounded-t-2xl max-h-[70vh] overflow-y-auto p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="font-semibold text-lg">Contenido</h2>
                <button onclick="document.getElementById('modal-toc').classList.add('hidden')" class="p-2 hover:bg-gray-100 rounded-full">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            <nav id="toc-nav-mobile">
                <!-- Se copia del nav principal -->
            </nav>
        </div>
    </div>

    <script>
        // Contenido del manual en Markdown
        const markdownContent = <?php echo json_encode($manual_content); ?>;

        // Configurar marked
        marked.setOptions({
            breaks: true,
            gfm: true
        });

        // Renderizar el manual
        document.getElementById('manual-content').innerHTML = marked.parse(markdownContent);

        // Inicializar íconos Lucide
        lucide.createIcons();

        // Generar tabla de contenidos
        function generateTOC() {
            const content = document.getElementById('manual-content');
            const headings = content.querySelectorAll('h1, h2, h3');
            const tocNav = document.getElementById('toc-nav');
            const tocNavMobile = document.getElementById('toc-nav-mobile');

            let tocHtml = '';

            headings.forEach((heading, index) => {
                // Crear ID si no existe
                if (!heading.id) {
                    heading.id = 'section-' + index;
                }

                const level = heading.tagName.toLowerCase();
                const levelClass = level === 'h3' ? 'toc-h3' : '';

                if (level !== 'h1' || index === 0) { // Incluir solo el primer H1
                    tocHtml += `<a href="#${heading.id}" class="${levelClass}" onclick="scrollToSection('${heading.id}')">${heading.textContent}</a>`;
                }
            });

            tocNav.innerHTML = tocHtml;
            tocNavMobile.innerHTML = tocHtml;
        }

        // Scroll suave a sección
        function scrollToSection(id) {
            const element = document.getElementById(id);
            if (element) {
                const offset = 100; // Compensar header fijo
                const elementPosition = element.getBoundingClientRect().top;
                const offsetPosition = elementPosition + window.pageYOffset - offset;

                window.scrollTo({
                    top: offsetPosition,
                    behavior: 'smooth'
                });

                // Cerrar modal móvil si está abierto
                document.getElementById('modal-toc').classList.add('hidden');
            }
        }

        // Resaltar sección activa en TOC
        function highlightActiveTOC() {
            const headings = document.querySelectorAll('.manual-content h1, .manual-content h2, .manual-content h3');
            const tocLinks = document.querySelectorAll('#toc-nav a');

            let current = '';

            headings.forEach(heading => {
                const sectionTop = heading.offsetTop - 150;
                if (window.pageYOffset >= sectionTop) {
                    current = heading.id;
                }
            });

            tocLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        }

        // Botón TOC móvil
        document.getElementById('btn-toc-mobile').addEventListener('click', function() {
            document.getElementById('modal-toc').classList.remove('hidden');
        });

        // Cerrar modal al hacer clic fuera
        document.getElementById('modal-toc').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        // Inicializar
        generateTOC();
        window.addEventListener('scroll', highlightActiveTOC);
        highlightActiveTOC();
    </script>

</body>
</html>
