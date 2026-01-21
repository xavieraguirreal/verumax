<?php
/**
 * VERUMAX SUPER ADMIN - Client Setup
 *
 * Crea automáticamente la estructura de carpetas y archivos
 * para un nuevo cliente.
 */

namespace VERUMaxAdmin;

class ClientSetup {

    private string $codigo;
    private string $nombre;
    private string $rootPath;
    private array $errors = [];

    public function __construct(string $codigo, string $nombre) {
        $this->codigo = $codigo;
        $this->nombre = $nombre;
        $this->rootPath = VERUMAX_ROOT_PATH;
    }

    /**
     * Crea toda la estructura del cliente
     */
    public function create(): bool {
        $clientPath = $this->rootPath . '/' . $this->codigo;

        // 1. Crear carpeta principal
        if (!$this->createDirectory($clientPath)) {
            return false;
        }

        // 2. Crear subcarpeta certificatum
        if (!$this->createDirectory($clientPath . '/certificatum')) {
            return false;
        }

        // 3. Crear subcarpeta assets/favicons (para favicon generator)
        if (!$this->createDirectory($clientPath . '/assets/favicons')) {
            return false;
        }

        // 4. Crear subcarpeta admin (para proxy al admin central)
        if (!$this->createDirectory($clientPath . '/admin')) {
            return false;
        }

        // 5. Crear archivos
        $files = [
            // Archivos raíz
            '/index.php' => $this->getIndexContent(),
            '/style.css' => $this->getStyleContent(),

            // Proxies raíz para Certificatum (URLs directas sin /certificatum/)
            '/creare.php' => $this->getRootCreareContent(),
            '/creare_pdf.php' => $this->getCrearePdfProxyContent(),
            '/cursus.php' => $this->getRootCursusContent(),
            '/tabularium.php' => $this->getRootTabulariumContent(),
            '/validare.php' => $this->getRootValidareContent(),
            '/verificatio.php' => $this->getRootVerificatioContent(),

            // Proxies en subcarpeta /certificatum/
            '/certificatum/index.php' => $this->getCertificatumIndexContent(),
            '/certificatum/creare.php' => $this->getCertificatumCreareContent(),
            '/certificatum/cursus.php' => $this->getCertificatumCursusContent(),
            '/certificatum/tabularium.php' => $this->getCertificatumTabulariumContent(),

            // Admin proxies
            '/admin/index.php' => $this->getAdminIndexContent(),
            '/admin/login.php' => $this->getAdminLoginContent(),
            '/admin/logout.php' => $this->getAdminLogoutContent(),
            '/admin/manual.php' => $this->getAdminManualContent(),
        ];

        foreach ($files as $file => $content) {
            if (!$this->createFile($clientPath . $file, $content)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retorna errores ocurridos
     */
    public function getErrors(): array {
        return $this->errors;
    }

    /**
     * Crea un directorio
     */
    private function createDirectory(string $path): bool {
        if (is_dir($path)) {
            return true;
        }

        if (!@mkdir($path, 0755, true)) {
            $this->errors[] = "No se pudo crear directorio: $path";
            return false;
        }

        return true;
    }

    /**
     * Crea un archivo
     */
    private function createFile(string $path, string $content): bool {
        if (@file_put_contents($path, $content) === false) {
            $this->errors[] = "No se pudo crear archivo: $path";
            return false;
        }
        return true;
    }

    // =========================================================================
    // TEMPLATES DE ARCHIVOS
    // =========================================================================

    private function getIndexContent(): string {
        return "<?php
/**
 * {$this->nombre} - Punto de Entrada Principal
 * Generado automáticamente por VERUMax Super Admin
 *
 * Este archivo maneja la lógica de módulos:
 * - PRIORIDAD: Modo construcción → Muestra página de mantenimiento
 * - CASO 1: Solo Certificatum activo → Muestra portal de certificados
 * - CASO 2: Ningún módulo activo → Muestra página de construcción
 * - CASO 3: Identitas activo → Usa motor Identitas para landing page
 */

// Definir slug de esta instancia
\$slug = '{$this->codigo}';

// Incluir configuración core VERUMax
require_once __DIR__ . '/../verumax/config.php';

// Cargar servicio de idiomas
use VERUMax\\Services\\LanguageService;

// Obtener configuración de la instancia
\$instance_config = getInstanceConfig(\$slug);

if (!\$instance_config) {
    die('Error: Institución no configurada');
}

// Inicializar idioma
\$lang_request = \$_GET['lang'] ?? null;
LanguageService::init(\$slug, \$lang_request);

// ============================================================================
// VERIFICACIÓN DE MODO CONSTRUCCIÓN
// ============================================================================

// Si sitio_en_construccion está activo, mostrar página de mantenimiento
if (!empty(\$instance_config['sitio_en_construccion'])) {
    ?>
    <!DOCTYPE html>
    <html lang=\"es\">
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title><?php echo htmlspecialchars(\$instance_config['nombre']); ?> - En Construcción</title>
        <script src=\"https://cdn.tailwindcss.com\"></script>
        <link rel=\"preconnect\" href=\"https://fonts.googleapis.com\">
        <link href=\"https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap\" rel=\"stylesheet\">
        <style>body { font-family: 'Inter', sans-serif; }</style>
    </head>
    <body class=\"bg-gradient-to-br from-amber-50 via-orange-50 to-yellow-50 min-h-screen flex items-center justify-center p-6\">
        <div class=\"max-w-lg w-full bg-white rounded-2xl shadow-xl p-8 text-center border border-amber-100\">
            <div class=\"w-20 h-20 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-6\">
                <svg class=\"w-10 h-10 text-amber-600\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z\"></path>
                </svg>
            </div>
            <h1 class=\"text-2xl font-bold text-gray-900 mb-3\">
                <?php echo htmlspecialchars(\$instance_config['nombre']); ?>
            </h1>
            <p class=\"text-lg text-amber-700 font-medium mb-4\">Sitio en Construcción</p>
            <p class=\"text-gray-600 mb-6\">
                Estamos trabajando para mejorar tu experiencia. Pronto estaremos de vuelta con novedades.
            </p>
            <div class=\"inline-flex items-center gap-2 text-sm text-gray-500 bg-gray-50 px-4 py-2 rounded-full\">
                <svg class=\"w-4 h-4\" fill=\"none\" stroke=\"currentColor\" viewBox=\"0 0 24 24\">
                    <path stroke-linecap=\"round\" stroke-linejoin=\"round\" stroke-width=\"2\" d=\"M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z\"></path>
                </svg>
                <span>Vuelve pronto</span>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// ============================================================================
// LÓGICA DE MÓDULOS
// ============================================================================

// CASO 1: Identitas desactivado + Certificatum activo → Solo portal de certificados
if (!\$instance_config['identitas_activo'] && \$instance_config['modulo_certificatum']) {
    \$instance = \$instance_config;
    \$certificatum_config = [
        'modo' => \$instance_config['certificatum_modo'] ?? 'pagina',
        'titulo' => \$instance_config['certificatum_titulo'] ?? 'Certificados',
    ];
    \$template_path = __DIR__ . '/../certificatum/templates/solo.php';
    if (file_exists(\$template_path)) {
        include \$template_path;
    } else {
        echo '<h1>Portal de Certificados</h1>';
        echo '<p>Acceda a <a href=\"certificatum/\">certificatum/</a></p>';
    }
    exit;
}

// CASO 2: Ambos módulos desactivados → Sitio en construcción
if (!\$instance_config['identitas_activo'] && !\$instance_config['modulo_certificatum']) {
    ?>
    <!DOCTYPE html>
    <html lang=\"es\">
    <head>
        <meta charset=\"UTF-8\">
        <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">
        <title><?php echo htmlspecialchars(\$instance_config['nombre']); ?></title>
        <script src=\"https://cdn.tailwindcss.com\"></script>
    </head>
    <body class=\"bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex items-center justify-center p-6\">
        <div class=\"max-w-md w-full bg-white rounded-2xl shadow-xl p-8 text-center\">
            <h1 class=\"text-2xl font-bold text-gray-900 mb-4\">
                <?php echo htmlspecialchars(\$instance_config['nombre']); ?>
            </h1>
            <p class=\"text-gray-600 mb-6\">
                Estamos trabajando en nuestro sitio web. Pronto tendremos novedades.
            </p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// CASO 3: Identitas activo → Funciona normal
require_once __DIR__ . '/../identitas/identitas_engine.php';
\$identitas = new IdentitasEngine(\$slug);

if (\$_SERVER['REQUEST_METHOD'] === 'POST' && isset(\$_GET['action']) && \$_GET['action'] === 'enviar') {
    \$resultado = \$identitas->procesarContacto(\$_POST);
    header('Location: ?' . (\$resultado['success'] ? 'enviado=1' : 'error=envio') . '#contacto');
    exit;
}

if (isset(\$_GET['page'])) {
    \$identitas->renderPage(\$_GET['page']);
} else {
    \$identitas->renderHome();
}
";
    }

    private function getStyleContent(): string {
        return "/**
 * Estilos personalizados para {$this->nombre}
 * Generado automáticamente por VERUMax Super Admin
 */

/* Colores institucionales - personalizar según branding */
:root {
    --color-primary: #2E7D32;
    --color-secondary: #1B5E20;
    --color-accent: #66bb6a;
}

/* Agregar estilos personalizados aquí */
";
    }

    private function getCrearePdfProxyContent(): string {
        return "<?php
/**
 * Proxy a Certificatum - Creare PDF (Generación de Documentos PDF)
 * {$this->nombre}
 *
 * Este archivo actúa como puente transparente al motor central de Certificatum.
 * Usa TCPDF para certificados con imagen de fondo, mPDF para otros documentos.
 */

// Auto-configuración de institución
\$_POST['institutio'] = \$_GET['institutio'] = '{$this->codigo}';

// Definir rutas base
define('PROXY_MODE', true);
define('INSTITUCION_PATH', './');
define('CERTIFICATUM_PATH', '../certificatum/');

// Usar path absoluto al archivo de certificatum
\$certificatum_path = dirname(__DIR__) . '/certificatum';

// Cambiar al directorio del motor central para que las rutas relativas de PHP funcionen
chdir(\$certificatum_path);

// Determinar qué generador usar según el tipo de documento
\$tipo_documento = \$_GET['genus'] ?? 'analyticum';

// TCPDF solo para certificados con imagen de fondo
\$tipos_tcpdf = ['certificatum_approbationis', 'certificatum_completionis', 'certificatum_doctoris', 'certificatum_docente'];

if (in_array(\$tipo_documento, \$tipos_tcpdf)) {
    // Usar TCPDF para certificados elegantes con imagen de fondo
    require_once \$certificatum_path . '/creare_pdf_tcpdf.php';
} else {
    // Usar mPDF para analíticos, constancias y otros documentos
    require_once \$certificatum_path . '/creare_pdf.php';
}
";
    }

    private function getCertificatumIndexContent(): string {
        return "<?php
/**
 * Certificatum - Página principal
 * Proxy al motor central
 */

\$institucion = '{$this->codigo}';
require_once __DIR__ . '/../../certificatum/instituta.php';
";
    }

    private function getCertificatumCreareContent(): string {
        return "<?php
/**
 * Certificatum - Generador de documentos
 * Proxy al motor central
 */

\$_GET['institutio'] = '{$this->codigo}';
require_once __DIR__ . '/../../certificatum/creare.php';
";
    }

    private function getCertificatumCursusContent(): string {
        return "<?php
/**
 * Certificatum - Lista de cursos
 * Proxy al motor central
 */

\$_GET['institutio'] = '{$this->codigo}';
require_once __DIR__ . '/../../certificatum/cursus.php';
";
    }

    private function getCertificatumTabulariumContent(): string {
        return "<?php
/**
 * Certificatum - Trayectoria académica
 * Proxy al motor central
 */

\$_GET['institutio'] = '{$this->codigo}';
require_once __DIR__ . '/../../certificatum/tabularium.php';
";
    }

    private function getAdminIndexContent(): string {
        return "<?php
/**
 * Admin Proxy - Redirige al panel de administración central
 * Generado automáticamente por VERUMax Super Admin
 *
 * Permite acceder al admin desde: {$this->codigo}.verumax.com/admin/
 */

// Definir la institución para el admin
\$_SESSION['admin_institucion'] = '{$this->codigo}';

// Redirigir al admin central
require_once __DIR__ . '/../../admin/index.php';
";
    }

    private function getAdminLoginContent(): string {
        return "<?php
/**
 * Login Proxy - Redirige al login de administración central
 * Generado automáticamente por VERUMax Super Admin
 */

require_once __DIR__ . '/../../admin/login.php';
";
    }

    private function getAdminLogoutContent(): string {
        return "<?php
/**
 * Logout Proxy - Redirige al logout de administración central
 * Generado automáticamente por VERUMax Super Admin
 */

require_once __DIR__ . '/../../admin/logout.php';
";
    }

    private function getAdminManualContent(): string {
        return "<?php
/**
 * Manual Proxy - Redirige al manual de usuario central
 * Generado automáticamente por VERUMax Super Admin
 */

// Detectar la institución desde el subdominio o la carpeta
\$host = \$_SERVER['HTTP_HOST'] ?? '';
\$institucion = null;

if (preg_match('/^([a-z0-9-]+)\\.verumax\\.(local|com)\$/i', \$host, \$matches)) {
    \$institucion = \$matches[1];
}

if (!\$institucion) {
    \$path = dirname(\$_SERVER['SCRIPT_NAME']);
    if (preg_match('/\\/([a-z0-9-]+)\\/admin/i', \$path, \$matches)) {
        \$institucion = \$matches[1];
    }
}

if (\$institucion) {
    \$_GET['inst'] = \$institucion;
    define('ADMIN_INSTITUCION', \$institucion);
}

require_once __DIR__ . '/../../admin/manual.php';
";
    }

    // =========================================================================
    // PROXIES RAÍZ PARA CERTIFICATUM (URLs directas sin /certificatum/)
    // =========================================================================

    private function getRootCreareContent(): string {
        return "<?php
/**
 * Proxy a Certificatum - Creare (Generación de Documentos)
 * {$this->nombre}
 *
 * Este archivo actúa como puente transparente al motor central de Certificatum
 * manteniendo la URL bajo {$this->codigo}.verumax.com para branding consistente.
 */

// Auto-configuración de institución
\$_POST['institutio'] = \$_GET['institutio'] = '{$this->codigo}';

// Definir rutas base para que el motor central use rutas absolutas en HTML
define('PROXY_MODE', true);
define('INSTITUCION_PATH', './');
define('CERTIFICATUM_PATH', '../certificatum/');

// Usar path absoluto al archivo de certificatum
\$certificatum_path = dirname(__DIR__) . '/certificatum';

// Cambiar al directorio del motor central para que las rutas relativas de PHP funcionen
chdir(\$certificatum_path);

// Incluir el motor central de Certificatum
require_once \$certificatum_path . '/creare.php';
";
    }

    private function getRootCursusContent(): string {
        return "<?php
/**
 * Proxy a Certificatum - Cursus (Lista de Cursos)
 * {$this->nombre}
 *
 * Este archivo actúa como puente transparente al motor central de Certificatum
 * manteniendo la URL bajo {$this->codigo}.verumax.com para branding consistente.
 */

// Auto-configuración de institución
\$_POST['institutio'] = \$_GET['institutio'] = '{$this->codigo}';

// Definir rutas base para que el motor central use rutas absolutas en HTML
define('PROXY_MODE', true);
define('INSTITUCION_PATH', './');
define('CERTIFICATUM_PATH', '../certificatum/');

// Usar path absoluto al archivo de certificatum
\$certificatum_path = dirname(__DIR__) . '/certificatum';

// Cambiar al directorio del motor central para que las rutas relativas de PHP funcionen
chdir(\$certificatum_path);

// Incluir el motor central de Certificatum
require_once \$certificatum_path . '/cursus.php';
";
    }

    private function getRootTabulariumContent(): string {
        return "<?php
/**
 * Proxy a Certificatum - Tabularium (Trayectoria Académica)
 * {$this->nombre}
 *
 * Este archivo actúa como puente transparente al motor central de Certificatum
 * manteniendo la URL bajo {$this->codigo}.verumax.com para branding consistente.
 */

// Auto-configuración de institución
\$_POST['institutio'] = \$_GET['institutio'] = '{$this->codigo}';

// Definir rutas base para que el motor central use rutas absolutas en HTML
define('PROXY_MODE', true);
define('INSTITUCION_PATH', './');
define('CERTIFICATUM_PATH', '../certificatum/');

// Usar path absoluto al archivo de certificatum
\$certificatum_path = dirname(__DIR__) . '/certificatum';

// Cambiar al directorio del motor central para que las rutas relativas de PHP funcionen
chdir(\$certificatum_path);

// Incluir el motor central de Certificatum
require_once \$certificatum_path . '/tabularium.php';
";
    }

    private function getRootValidareContent(): string {
        return "<?php
/**
 * Proxy a Certificatum - Validare (Validación de Documentos)
 * {$this->nombre}
 *
 * Este archivo actúa como puente transparente al motor central de Certificatum
 * manteniendo la URL bajo {$this->codigo}.verumax.com para branding consistente.
 */

// Auto-configuración de institución
\$_POST['institutio'] = \$_GET['institutio'] = '{$this->codigo}';

// Definir rutas base para que el motor central use rutas absolutas en HTML
define('PROXY_MODE', true);
define('INSTITUCION_PATH', './');
define('CERTIFICATUM_PATH', '../certificatum/');

// Usar path absoluto al archivo de certificatum
\$certificatum_path = dirname(__DIR__) . '/certificatum';

// Cambiar al directorio del motor central para que las rutas relativas de PHP funcionen
chdir(\$certificatum_path);

// Incluir el motor central de Certificatum
require_once \$certificatum_path . '/validare.php';
";
    }

    private function getRootVerificatioContent(): string {
        return "<?php
/**
 * Proxy a Certificatum - Verificatio (Vista Pública de Validación)
 * {$this->nombre}
 *
 * Este archivo actúa como puente transparente al motor central de Certificatum
 * manteniendo la URL bajo {$this->codigo}.verumax.com para branding consistente.
 */

// Auto-configuración de institución
\$_POST['institutio'] = \$_GET['institutio'] = '{$this->codigo}';

// Definir rutas base para que el motor central use rutas absolutas en HTML
define('PROXY_MODE', true);
define('INSTITUCION_PATH', './');
define('CERTIFICATUM_PATH', '../certificatum/');

// Usar path absoluto al archivo de certificatum
\$certificatum_path = dirname(__DIR__) . '/certificatum';

// Cambiar al directorio del motor central para que las rutas relativas de PHP funcionen
chdir(\$certificatum_path);

// Incluir el motor central de Certificatum
require_once \$certificatum_path . '/verificatio.php';
";
    }
}
