<?php
/**
 * Script para convertir el contenido de index.php a multi-idioma
 * Reemplaza textos hardcodeados por variables de idioma
 */

$file = 'index.php';
$content = file_get_contents($file);

// Array de reemplazos: [texto original => clave de idioma]
$replacements = [
    // Hero Section
    'Confiado por instituciones y profesionales globalmente' => "<?php echo \$lang['hero_badge']; ?>",
    'Documentación con Validación QR<br>para Cada Sector' => "<?php echo \$lang['hero_title']; ?>",
    'Soluciones de validación y emisión de documentos digitales para instituciones educativas, profesionales independientes, organizadores de eventos, empresas, cooperativas y mutuales. Validación QR infalsificable y portal personalizado.' => "<?php echo \$lang['hero_subtitle']; ?>",
    'Explorar Soluciones' => "<?php echo \$lang['hero_cta_primary']; ?>",
    '>Validar Documento<' => "><?php echo \$lang['hero_cta_secondary']; ?><",

    // Categorías
    'Soluciones por Industria' => "<?php echo \$lang['cat_title']; ?>",
    'Elegí tu sector para descubrir la solución perfecta diseñada para tus necesidades específicas' => "<?php echo \$lang['cat_subtitle']; ?>",

    '>Académico<' => "><?php echo \$lang['cat_academico']; ?><",
    '>Instituciones Educativas<' => "><?php echo \$lang['cat_academico_1']; ?><",
    '>Centros de Formación<' => "><?php echo \$lang['cat_academico_2']; ?><",
    '>Formadores Particulares<' => "><?php echo \$lang['cat_academico_3']; ?><",
    '>Ver Solución →<' => "><?php echo \$lang['cat_academico_cta']; ?><",

    '>Profesional<' => "><?php echo \$lang['cat_profesional']; ?><",
    '>Salud y Bienestar<' => "><?php echo \$lang['cat_profesional_1']; ?><",
    '>Ingeniería y Arquitectura<' => "><?php echo \$lang['cat_profesional_2']; ?><",
    '>Todos los Profesionales<' => "><?php echo \$lang['cat_profesional_3']; ?><",

    '>Eventos<' => "><?php echo \$lang['cat_eventos']; ?><",
    '>Congresos y Conferencias<' => "><?php echo \$lang['cat_eventos_1']; ?><",
    '>Festivales y Conciertos<' => "><?php echo \$lang['cat_eventos_2']; ?><",
    '>Control de Acceso<' => "><?php echo \$lang['cat_eventos_3']; ?><",

    '>Empresarial<' => "><?php echo \$lang['cat_empresarial']; ?><",
    '>Garantías y Autenticidad<' => "><?php echo \$lang['cat_empresarial_1']; ?><",
    '>Certificados de Calidad<' => "><?php echo \$lang['cat_empresarial_2']; ?><",
    '>Recibos Validados<' => "><?php echo \$lang['cat_empresarial_3']; ?><",

    '>Cooperativas<' => "><?php echo \$lang['cat_cooperativas']; ?><",
    '>Credenciales de Socios<' => "><?php echo \$lang['cat_cooperativas_1']; ?><",
    '>Certificados de Participación<' => "><?php echo \$lang['cat_cooperativas_2']; ?><",
    '>Constancias de Actividades<' => "><?php echo \$lang['cat_cooperativas_3']; ?><",

    '>Mutuales<' => "><?php echo \$lang['cat_mutuales']; ?><",
    '>Credenciales de Asociados<' => "><?php echo \$lang['cat_mutuales_1']; ?><",
    '>Comprobantes de Servicios<' => "><?php echo \$lang['cat_mutuales_2']; ?><",
    '>Cartillas de Beneficios<' => "><?php echo \$lang['cat_mutuales_3']; ?><",

    '>Próximamente<' => "><?php echo \$lang['cat_proximamente']; ?><",

    // Servicios Principales
    'Nuestros Servicios Principales' => "<?php echo \$lang['servicios_title']; ?>",
    'Funcionalidades esenciales que todas nuestras organizaciones disfrutan, sin importar su sector' => "<?php echo \$lang['servicios_subtitle']; ?>",

    'Seguridad Anti-Fraude Total' => "<?php echo \$lang['serv_antifraude']; ?>",
    'Códigos QR únicos e imposibles de falsificar que garantizan la autenticidad de cada documento.' => "<?php echo \$lang['serv_antifraude_desc']; ?>",

    '>Portal 24/7<' => "><?php echo \$lang['serv_portal']; ?><",
    'Acceso permanente para estudiantes, clientes, socios o empleados desde cualquier dispositivo.' => "<?php echo \$lang['serv_portal_desc']; ?>",

    '>Impresión Premium<' => "><?php echo \$lang['serv_impresion']; ?><",
    'Documentos físicos de alta calidad con hologramas y laminado, enviados a domicilio.' => "<?php echo \$lang['serv_impresion_desc']; ?>",

    'Branding Personalizado' => "<?php echo \$lang['serv_branding']; ?>",
    'Portal y documentos con tu logo, colores y diseño institucional único.' => "<?php echo \$lang['serv_branding_desc']; ?>",

    '>Emisión Masiva<' => "><?php echo \$lang['serv_emision']; ?><",
    'Genera cientos de documentos automáticamente con notificación instantánea.' => "<?php echo \$lang['serv_emision_desc']; ?>",

    'Portal de Validación' => "<?php echo \$lang['serv_validacion']; ?>",
    'Cualquier persona puede verificar la autenticidad de tus documentos al instante.' => "<?php echo \$lang['serv_validacion_desc']; ?>",

    // Casos de Éxito
    'Casos de Éxito' => "<?php echo \$lang['casos_title']; ?>",
    'Instituciones y profesionales que confían en OriginalisDoc' => "<?php echo \$lang['casos_subtitle']; ?>",

    // FAQ
    'Preguntas Frecuentes' => "<?php echo \$lang['faq_title']; ?>",
    'Todo lo que necesitas saber sobre OriginalisDoc' => "<?php echo \$lang['faq_subtitle']; ?>",
];

// Aplicar reemplazos
foreach ($replacements as $search => $replace) {
    $content = str_replace($search, $replace, $content);
}

// Guardar archivo
file_put_contents($file, $content);

echo "✅ Conversión completada! Se han reemplazado " . count($replacements) . " textos.\n";
?>
