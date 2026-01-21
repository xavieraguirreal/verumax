<?php
/**
 * IDENTITAS ENGINE - Motor Base
 * Sistema VERUMax - Presencia Digital Profesional
 *
 * Motor que renderiza instancias de Identitas con su branding personalizado
 */

require_once 'config.php';

class IdentitasEngine {
    private $instance_slug;
    private $instance_config;
    private $pdo;

    public function __construct($slug) {
        $this->instance_slug = $slug;
        $this->pdo = getDBConnection();
        $this->loadInstanceConfig();

        // Verificar si Identitas está activo
        $this->checkIdentitasActivo();
    }

    /**
     * Carga la configuración de la instancia desde la BD
     */
    private function loadInstanceConfig() {
        $this->instance_config = getInstanceConfig($this->instance_slug);

        if (!$this->instance_config) {
            http_response_code(404);
            die('Instancia no encontrada');
        }
    }

    /**
     * Verifica si Identitas está activo, si no muestra página "En construcción"
     * o delega a Certificatum si está activo
     */
    private function checkIdentitasActivo() {
        // Si identitas_activo es 0
        if (isset($this->instance_config['identitas_activo']) &&
            $this->instance_config['identitas_activo'] == 0) {

            // Si Certificatum está activo, mostrar portal solo de certificados
            if (isset($this->instance_config['certificatum_activo']) &&
                $this->instance_config['certificatum_activo'] == 1) {
                $this->renderCertificatumSolo();
                exit;
            }

            // Si no, mostrar página "En construcción"
            $this->renderEnConstruccion();
            exit;
        }
    }

    /**
     * Renderiza la página "En construcción"
     */
    private function renderEnConstruccion() {
        $nombre = $this->instance_config['nombre'] ?? 'Sitio';
        $logo = $this->instance_config['logo_url'] ?? '';
        $color = $this->instance_config['color_primario'] ?? '#2E7D32';

        ?>
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo htmlspecialchars($nombre); ?> - En Construcción</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <script src="https://unpkg.com/lucide@latest"></script>
            <style>
                body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; }
                @keyframes float {
                    0%, 100% { transform: translateY(0px); }
                    50% { transform: translateY(-20px); }
                }
                .float-animation { animation: float 3s ease-in-out infinite; }
            </style>
        </head>
        <body class="bg-gradient-to-br from-gray-50 via-white to-gray-100 min-h-screen flex items-center justify-center">
            <div class="text-center px-6 py-12 max-w-2xl">
                <!-- Logo -->
                <?php if ($logo): ?>
                    <div class="mb-8 flex justify-center">
                        <img src="<?php echo htmlspecialchars($logo); ?>"
                             alt="Logo"
                             class="h-24 w-24 md:h-32 md:w-32 rounded-full shadow-lg">
                    </div>
                <?php endif; ?>

                <!-- Icono animado -->
                <div class="mb-8 float-animation">
                    <div class="inline-block p-6 rounded-full shadow-xl"
                         style="background: linear-gradient(135deg, <?php echo htmlspecialchars($color); ?>, <?php echo htmlspecialchars($color); ?>cc);">
                        <i data-lucide="hammer" class="w-16 h-16 md:w-20 md:h-20 text-white"></i>
                    </div>
                </div>

                <!-- Título -->
                <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-4">
                    Sitio en Construcción
                </h1>

                <p class="text-xl text-gray-600 mb-8">
                    Estamos trabajando en algo increíble para <strong><?php echo htmlspecialchars($nombre); ?></strong>
                </p>

                <!-- Mensaje -->
                <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                    <p class="text-gray-700 leading-relaxed">
                        Nuestro sitio web está actualmente en desarrollo.
                        Pronto tendrás acceso a una experiencia digital profesional completamente renovada.
                    </p>
                </div>

                <!-- Información de contacto -->
                <p class="text-sm text-gray-500">
                    Para más información, contactá con el equipo de <?php echo htmlspecialchars($nombre); ?>
                </p>
            </div>

            <script>
                lucide.createIcons();
            </script>
        </body>
        </html>
        <?php
    }

    /**
     * Obtiene el valor de configuración
     */
    public function getConfig($key, $default = null) {
        if (isset($this->instance_config[$key])) {
            return $this->instance_config[$key];
        }

        if (isset($this->instance_config['config'][$key])) {
            return $this->instance_config['config'][$key];
        }

        return $default;
    }

    /**
     * Renderiza la página principal (index)
     */
    public function renderHome() {
        // Si está en modo "seccion" y viene con DNI válido, delegar a cursus.php
        $dni = $_POST['documentum'] ?? $_GET['documentum'] ?? '';
        if ($this->instance_config['certificatum_modo'] === 'seccion' &&
            $this->instance_config['modulo_certificatum'] &&
            !empty($dni) && preg_match('/^[0-9]{7,8}$/', $dni)) {
            $this->delegarACertificatum();
            return;
        }

        $data = [
            'instance' => $this->instance_config,
            'paginas' => $this->getPaginas(),
            'modulos_activos' => $this->getModulosActivos(),
            'certificatum_config' => $this->getCertificatumConfig(),
        ];

        $this->renderTemplate('home', $data);
    }

    /**
     * Renderiza una página específica
     */
    public function renderPage($page_slug) {
        // Caso especial: página de certificados en modo "pagina"
        if ($page_slug === 'certificados' &&
            $this->instance_config['modulo_certificatum'] &&
            $this->instance_config['certificatum_modo'] === 'pagina') {

            $data = [
                'instance' => $this->instance_config,
                'paginas' => $this->getPaginas(),
                'modulos_activos' => $this->getModulosActivos(),
                'certificatum_config' => $this->getCertificatumConfig(),
            ];

            // Renderizar con header/footer de Identitas
            $this->renderTemplate('certificatum_page', $data);
            return;
        }

        // Página normal de la base de datos
        $pagina = $this->getPagina($page_slug);

        if (!$pagina) {
            http_response_code(404);
            die('Página no encontrada');
        }

        $data = [
            'instance' => $this->instance_config,
            'pagina' => $pagina,
            'paginas' => $this->getPaginas(),
            'modulos_activos' => $this->getModulosActivos(),
            'certificatum_config' => $this->getCertificatumConfig(),
        ];

        $this->renderTemplate('page', $data);
    }

    /**
     * Obtiene las páginas de la instancia
     */
    private function getPaginas() {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM identitas_paginas
                WHERE id_instancia = :id_instancia
                  AND activo = 1
                  AND visible_menu = 1
                ORDER BY orden ASC
            ");
            $stmt->execute(['id_instancia' => $this->instance_config['id_instancia']]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al obtener páginas: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene una página específica
     */
    private function getPagina($slug) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT * FROM identitas_paginas
                WHERE id_instancia = :id_instancia
                  AND slug = :slug
                  AND activo = 1
            ");
            $stmt->execute([
                'id_instancia' => $this->instance_config['id_instancia'],
                'slug' => $slug
            ]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Error al obtener página: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Obtiene lista de módulos activos
     */
    private function getModulosActivos() {
        return [
            'certificatum' => (bool)$this->instance_config['modulo_certificatum'],
            'scripta' => (bool)$this->instance_config['modulo_scripta'],
            'nexus' => (bool)$this->instance_config['modulo_nexus'],
            'vitae' => (bool)$this->instance_config['modulo_vitae'],
            'lumen' => (bool)$this->instance_config['modulo_lumen'],
            'opera' => (bool)$this->instance_config['modulo_opera'],
        ];
    }

    /**
     * Procesa formulario de contacto
     */
    public function procesarContacto($datos) {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO identitas_contactos (
                    id_instancia, nombre, email, telefono, asunto, mensaje, ip_origen, user_agent
                ) VALUES (
                    :id_instancia, :nombre, :email, :telefono, :asunto, :mensaje, :ip, :user_agent
                )
            ");

            $stmt->execute([
                'id_instancia' => $this->instance_config['id_instancia'],
                'nombre' => $datos['nombre'],
                'email' => $datos['email'],
                'telefono' => $datos['telefono'] ?? null,
                'asunto' => $datos['asunto'] ?? null,
                'mensaje' => $datos['mensaje'],
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ]);

            return ['success' => true, 'mensaje' => 'Mensaje enviado correctamente'];

        } catch (PDOException $e) {
            error_log("Error al guardar contacto: " . $e->getMessage());
            return ['success' => false, 'mensaje' => 'Error al enviar el mensaje'];
        }
    }

    /**
     * Renderiza un template con los datos
     */
    private function renderTemplate($template_name, $data) {
        // Asegurar que certificatum_config esté siempre disponible
        if (!isset($data['certificatum_config'])) {
            $data['certificatum_config'] = $this->getCertificatumConfig();
        }

        // Pasar el engine para que el header pueda llamar getCustomCSS()
        $data['engine'] = $this;

        // Extraer variables para el template
        extract($data);

        // Incluir header
        include IDENTITAS_PATH . '/templates/header.php';

        // Incluir contenido del template
        $template_file = IDENTITAS_PATH . "/templates/{$template_name}.php";
        if (file_exists($template_file)) {
            include $template_file;
        } else {
            echo "<p>Template no encontrado: {$template_name}</p>";
        }

        // Incluir footer
        include IDENTITAS_PATH . '/templates/footer.php';
    }

    /**
     * Genera estilos personalizados basados en branding
     */
    public function getCustomCSS() {
        $color_primario = $this->getConfig('color_primario', '#D4AF37');
        $color_secundario = $this->getConfig('color_secundario', '#2E7D32');

        return "
        <style>
            :root {
                --color-primario: {$color_primario};
                --color-secundario: {$color_secundario};
            }
            .bg-primario { background-color: var(--color-primario); }
            .text-primario { color: var(--color-primario); }
            .border-primario { border-color: var(--color-primario); }
            .hover-bg-primario:hover { background-color: var(--color-primario); filter: brightness(0.9); }
        </style>
        ";
    }

    /**
     * Obtiene configuración de Certificatum
     */
    private function getCertificatumConfig() {
        if (!$this->instance_config['modulo_certificatum']) {
            return null;
        }

        return [
            'modo' => $this->instance_config['certificatum_modo'] ?? 'seccion',
            'titulo' => $this->instance_config['certificatum_titulo'] ?? 'Certificados',
            'posicion' => $this->instance_config['certificatum_posicion'] ?? 99,
            'icono' => $this->instance_config['certificatum_icono'] ?? 'award',
        ];
    }

    /**
     * Delega al motor de Certificatum
     */
    private function delegarACertificatum() {
        // Validar que cursus.php existe
        $cursus_path = ROOT_PATH . '/certificatum/cursus.php';
        if (!file_exists($cursus_path)) {
            http_response_code(503);
            die('Error: Módulo Certificatum no disponible');
        }

        define('PROXY_MODE', true);
        define('INSTITUCION_SLUG', $this->instance_slug);
        define('INSTITUCION_PATH', ROOT_PATH . '/' . $this->instance_slug . '/');

        $_GET['institutio'] = $this->instance_slug;
        $_POST['institutio'] = $this->instance_slug;

        // Registrar log de delegación
        error_log(sprintf(
            '[IDENTITAS] Delegando a Certificatum - Institución: %s, DNI: %s, Modo: %s',
            $this->instance_slug,
            $_POST['documentum'] ?? $_GET['documentum'] ?? 'N/A',
            $this->instance_config['certificatum_modo'] ?? 'desconocido'
        ));

        chdir(ROOT_PATH . '/certificatum');
        require_once $cursus_path;
        exit;
    }

    /**
     * Renderiza Certificatum como página principal
     */
    private function renderCertificatumPrincipal() {
        // Si viene con DNI, delegar a cursus.php
        if (isset($_POST['documentum']) || isset($_GET['documentum'])) {
            $this->delegarACertificatum();
            return;
        }

        // Si no hay DNI, mostrar formulario en página principal
        $data = [
            'instance' => $this->instance_config,
            'paginas' => $this->getPaginas(),
            'modulos_activos' => $this->getModulosActivos(),
            'certificatum_config' => $this->getCertificatumConfig(),
        ];

        $this->renderCertificatumTemplate('principal', $data);
    }

    /**
     * Renderiza Certificatum Solo (sin Identitas)
     * Se usa cuando identitas_activo=0 pero certificatum_activo=1
     */
    private function renderCertificatumSolo() {
        // Si viene con DNI, delegar a cursus.php
        if (isset($_POST['documentum']) || isset($_GET['documentum'])) {
            $this->delegarACertificatum();
            return;
        }

        // Si no hay DNI, mostrar formulario standalone
        $data = [
            'instance' => $this->instance_config,
            'paginas' => [],
            'modulos_activos' => $this->getModulosActivos(),
            'certificatum_config' => $this->getCertificatumConfig(),
        ];

        $this->renderCertificatumTemplate('solo', $data);
    }

    /**
     * Renderiza un template de Certificatum desde certificatum/templates/
     */
    private function renderCertificatumTemplate($template_name, $data) {
        // Extraer variables para el template
        extract($data);

        // Template de Certificatum está en certificatum/templates/
        $template_file = ROOT_PATH . "/certificatum/templates/{$template_name}.php";

        if (file_exists($template_file)) {
            include $template_file;
        } else {
            http_response_code(500);
            die("Error: Template de Certificatum no encontrado: {$template_name}");
        }
    }
}
