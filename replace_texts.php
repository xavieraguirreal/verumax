<?php
/**
 * Script temporal para reemplazar textos hardcoded en academico.php
 * Solo reemplaza textos exactos, no modifica estructura
 */

$file = 'academico.php';
$content = file_get_contents($file);

// Array de reemplazos: texto original => clave de traducción
$replacements = [
    // Tipos de Documentos
    'Tipos de Documentos Académicos' => "<?php echo \$lang['acad_documentos_titulo']; ?>",
    'Todos los documentos que tu institución necesita emitir' => "<?php echo \$lang['acad_documentos_subtitulo']; ?>",
    'Documentos Principales' => "<?php echo \$lang['acad_documentos_principales']; ?>",
    'Certificados de Aprobación' => "<?php echo \$lang['acad_doc_certificado_aprobacion']; ?>",
    'Certificado oficial que acredita la aprobación de un curso, materia o programa completo.' => "<?php echo \$lang['acad_doc_certificado_aprobacion_desc']; ?>",
    'Diplomas de Finalización' => "<?php echo \$lang['acad_doc_diplomas']; ?>",
    'Título o diploma formal que certifica la culminación exitosa de un programa académico completo.' => "<?php echo \$lang['acad_doc_diplomas_desc']; ?>",
    'Analíticos / Registros Académicos' => "<?php echo \$lang['acad_doc_analiticos']; ?>",
    'Historial completo con todas las materias cursadas, calificaciones, asistencias y competencias adquiridas.' => "<?php echo \$lang['acad_doc_analiticos_desc']; ?>",
    'Constancias de Alumno Regular' => "<?php echo \$lang['acad_doc_constancia_regular']; ?>",
    'Documento que acredita que el estudiante está actualmente cursando en la institución.' => "<?php echo \$lang['acad_doc_constancia_regular_desc']; ?>",
    'Constancias de Inscripción' => "<?php echo \$lang['acad_doc_constancia_inscripcion']; ?>",
    'Comprobante de inscripción a un curso, materia o programa académico específico.' => "<?php echo \$lang['acad_doc_constancia_inscripcion_desc']; ?>",
    'Constancias de Finalización de Cursada' => "<?php echo \$lang['acad_doc_constancia_finalizacion']; ?>",
    'Certificado que acredita haber completado la cursada, previo a la evaluación final.' => "<?php echo \$lang['acad_doc_constancia_finalizacion_desc']; ?>",
    'Documentos Complementarios' => "<?php echo \$lang['acad_documentos_complementarios']; ?>",
    'Certificados de Asistencia' => "<?php echo \$lang['acad_doc_asistencia']; ?>",
    'Para talleres, seminarios, webinars o eventos educativos puntuales.' => "<?php echo \$lang['acad_doc_asistencia_desc']; ?>",
    'Reconocimientos y Menciones' => "<?php echo \$lang['acad_doc_reconocimientos']; ?>",
    'Premios especiales, menciones honoríficas o reconocimientos por desempeño destacado.' => "<?php echo \$lang['acad_doc_reconocimientos_desc']; ?>",
    'Certificados de Competencias' => "<?php echo \$lang['acad_doc_competencias']; ?>",
    'Acreditan habilidades y competencias específicas adquiridas durante la formación.' => "<?php echo \$lang['acad_doc_competencias_desc']; ?>",
    'Constancias de Práctica Profesional' => "<?php echo \$lang['acad_doc_practica']; ?>",
    'Documentan pasantías, prácticas profesionales o experiencia laboral supervisada.' => "<?php echo \$lang['acad_doc_practica_desc']; ?>",
    'Certificados de Participación en Congresos' => "<?php echo \$lang['acad_doc_congresos']; ?>",
    'Para asistencia o ponencias en congresos, jornadas académicas y conferencias.' => "<?php echo \$lang['acad_doc_congresos_desc']; ?>",

    // Footer
    'Certificaciones Digitales Profesionales' => "<?php echo \$lang['acad_footer_tagline']; ?>",
    '© 2025 OriginalisDoc. Todos los derechos reservados.' => "<?php echo \$lang['acad_footer_derechos']; ?>",
];

// Hacer los reemplazos
foreach ($replacements as $old => $new) {
    $content = str_replace($old, $new, $content);
}

// Guardar backup
copy($file, $file . '.backup_' . date('YmdHis'));

// Guardar archivo modificado
file_put_contents($file, $content);

echo "Reemplazos completados. Backup creado: {$file}.backup_" . date('YmdHis') . "\n";
echo "Total de reemplazos: " . count($replacements) . "\n";
