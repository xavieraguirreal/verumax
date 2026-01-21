<?php
/**
 * WRAPPER: Certificatum como página dentro de Identitas
 * Incluye el template de certificatum desde certificatum/templates/
 */

// Incluir el template de certificatum
$certificatum_template = ROOT_PATH . '/certificatum/templates/integrado.php';
if (file_exists($certificatum_template)) {
    include $certificatum_template;
} else {
    echo '<p class="text-center text-red-600 py-12">Error: Template de certificatum no encontrado</p>';
}
