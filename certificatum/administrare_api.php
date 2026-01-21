<?php
/**
 * ADMINISTRARE API
 * Endpoints AJAX para gestión de templates de certificados
 * Sistema CERTIFICATUM - VERUMax
 *
 * @version 1.0
 * @date 2025-12-26
 */

require_once 'config.php';

use VERUMax\Services\CertificateTemplateService;
use VERUMax\Services\CursoService;
use VERUMax\Services\InstitutionService;

// Configurar headers para JSON
header('Content-Type: application/json; charset=utf-8');

// Verificar que sea una petición AJAX o API
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Obtener acción
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Router de acciones
try {
    switch ($action) {
        case 'getTemplates':
            handleGetTemplates();
            break;

        case 'getTemplate':
            handleGetTemplate();
            break;

        case 'getTemplateConfig':
            handleGetTemplateConfig();
            break;

        case 'updateTemplateConfig':
            handleUpdateTemplateConfig();
            break;

        case 'deleteTemplateConfig':
            handleDeleteTemplateConfig();
            break;

        case 'getCursoTemplate':
            handleGetCursoTemplate();
            break;

        case 'assignTemplate':
            handleAssignTemplate();
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Acción no válida', 'action' => $action]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error interno',
        'message' => $e->getMessage()
    ]);
}

// =========================================================================
// HANDLERS
// =========================================================================

/**
 * Obtiene todos los templates disponibles
 * GET: ?action=getTemplates
 */
function handleGetTemplates(): void
{
    $templates = CertificateTemplateService::getAll();

    // Agregar opción "Sin template" al inicio
    $response = [
        'success' => true,
        'templates' => array_merge(
            [
                [
                    'id_template' => null,
                    'slug' => 'default',
                    'nombre' => 'Predeterminado (actual)',
                    'descripcion' => 'Usa el sistema de certificados actual de la institución',
                    'preview_url' => null,
                    'is_default' => true
                ]
            ],
            $templates
        )
    ];

    echo json_encode($response);
}

/**
 * Obtiene un template específico por ID
 * GET: ?action=getTemplate&id=1
 */
function handleGetTemplate(): void
{
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de template inválido']);
        return;
    }

    $template = CertificateTemplateService::getById($id);

    if (!$template) {
        http_response_code(404);
        echo json_encode(['error' => 'Template no encontrado']);
        return;
    }

    // Parsear config
    $template['config_parsed'] = CertificateTemplateService::getConfig($id);

    echo json_encode([
        'success' => true,
        'template' => $template
    ]);
}

/**
 * Obtiene el template asignado a un curso
 * GET: ?action=getCursoTemplate&id_curso=1
 */
function handleGetCursoTemplate(): void
{
    $idCurso = isset($_GET['id_curso']) ? (int) $_GET['id_curso'] : 0;

    if ($idCurso <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de curso inválido']);
        return;
    }

    $template = CertificateTemplateService::getForCursoById($idCurso);

    echo json_encode([
        'success' => true,
        'id_curso' => $idCurso,
        'template' => $template,
        'uses_default' => $template === null
    ]);
}

/**
 * Asigna un template a un curso
 * POST: action=assignTemplate, id_curso=1, id_template=2 (o null)
 */
function handleAssignTemplate(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Use POST para esta acción']);
        return;
    }

    $idCurso = isset($_POST['id_curso']) ? (int) $_POST['id_curso'] : 0;
    $idTemplate = isset($_POST['id_template']) && $_POST['id_template'] !== ''
        ? (int) $_POST['id_template']
        : null;

    if ($idCurso <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de curso inválido']);
        return;
    }

    // Verificar que el template existe (si no es null)
    if ($idTemplate !== null && !CertificateTemplateService::exists($idTemplate)) {
        http_response_code(400);
        echo json_encode(['error' => 'Template no existe']);
        return;
    }

    // Asignar
    $success = CertificateTemplateService::assignToCurso($idCurso, $idTemplate);

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => $idTemplate === null
                ? 'Se asignó el template predeterminado'
                : 'Template asignado correctamente',
            'id_curso' => $idCurso,
            'id_template' => $idTemplate
        ]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'No se pudo asignar el template']);
    }
}

/**
 * Obtiene la configuración JSON de un template
 * GET: ?action=getTemplateConfig&id=1
 */
function handleGetTemplateConfig(): void
{
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de template inválido']);
        return;
    }

    $template = CertificateTemplateService::getById($id);

    if (!$template) {
        http_response_code(404);
        echo json_encode(['error' => 'Template no encontrado']);
        return;
    }

    echo json_encode([
        'success' => true,
        'id_template' => $id,
        'slug' => $template['slug'],
        'nombre' => $template['nombre'],
        'config' => $template['config'],
        'config_parsed' => json_decode($template['config'], true),
        'has_config' => !empty($template['config'])
    ]);
}

/**
 * Actualiza la configuración JSON de un template
 * POST: action=updateTemplateConfig, id_template=1, config={json}
 */
function handleUpdateTemplateConfig(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Use POST para esta acción']);
        return;
    }

    $idTemplate = isset($_POST['id_template']) ? (int) $_POST['id_template'] : 0;
    $config = $_POST['config'] ?? '';

    if ($idTemplate <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de template inválido']);
        return;
    }

    // Validar que el JSON sea válido
    if (!empty($config)) {
        $parsed = json_decode($config, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            http_response_code(400);
            echo json_encode([
                'error' => 'JSON inválido',
                'json_error' => json_last_error_msg()
            ]);
            return;
        }
    }

    try {
        $success = CertificateTemplateService::updateConfig($idTemplate, $config);

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Configuración actualizada correctamente',
                'id_template' => $idTemplate
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo actualizar la configuración']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Error al actualizar',
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Elimina/resetea la configuración JSON de un template
 * POST: action=deleteTemplateConfig, id_template=1
 */
function handleDeleteTemplateConfig(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['error' => 'Use POST para esta acción']);
        return;
    }

    $idTemplate = isset($_POST['id_template']) ? (int) $_POST['id_template'] : 0;

    if ($idTemplate <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de template inválido']);
        return;
    }

    try {
        // Resetear config a vacío
        $success = CertificateTemplateService::updateConfig($idTemplate, '');

        if ($success) {
            echo json_encode([
                'success' => true,
                'message' => 'Configuración eliminada. El template usará el sistema predeterminado.',
                'id_template' => $idTemplate
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'No se pudo eliminar la configuración']);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'error' => 'Error al eliminar',
            'message' => $e->getMessage()
        ]);
    }
}
