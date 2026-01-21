<?php
/**
 * Traducciones Certificatum - Español Argentina (es_AR)
 * Lenguaje formal para contexto institucional/educativo
 */

return [
    // Página principal
    'page_title' => 'Certificados',
    'page_subtitle' => 'Acceda a sus certificados, constancias y registro académico completo. Descargue sus documentos en formato PDF con código QR de validación.',
    'search_title' => 'Número de Documento',
    'search_placeholder' => 'Ingresá tu documento (solo números)',
    'search_help' => 'Solo números, sin puntos ni espacios',
    'search_button' => 'Ver mis certificados',
    'search_enter_document' => 'Ingresá tu número de documento',
    'search_example' => 'Ej: 12345678',
    'search_not_found' => 'No se encontraron certificados',
    'search_not_found_desc' => 'No se encontraron certificados asociados al documento ingresado. Verificá que el número sea correcto o contactá a la institución.',

    // Lista de cursos
    'my_courses' => 'Mis Cursos',
    'courses_as_student' => 'Cursos como Estudiante',
    'courses_as_teacher' => 'Participaciones como Docente',
    'no_courses_found' => 'No se encontraron cursos registrados para este DNI',
    'course_hours' => 'Carga horaria',
    'course_status' => 'Estado',
    'course_grade' => 'Nota final',
    'course_completion' => 'Finalización',
    'view_trajectory' => 'Ver Trayectoria Completa',

    // Estados de curso
    'status_approved' => 'Aprobado',
    'status_in_progress' => 'En Curso',
    'status_enrolled' => 'Inscrito',
    'status_finished' => 'Finalizado',
    'status_completed' => 'Finalizado',
    'status_pending' => 'Por Iniciar',
    'status_preregistered' => 'Preinscrito',

    // Tipos de documento (genus en latín)
    'doc_analyticum' => 'Analítico Académico',
    'doc_certificatum_approbationis' => 'Certificado de Aprobación',
    'doc_certificatum_completionis' => 'Certificado de Finalización',
    'doc_testimonium_regulare' => 'Constancia de Alumno Regular',
    'doc_testimonium_completionis' => 'Constancia de Finalización',
    'doc_testimonium_inscriptionis' => 'Constancia de Inscripción',
    'doc_certificatum_doctoris' => 'Certificado de Docente/Instructor',
    'doc_testimonium_doctoris' => 'Constancia de Participación Docente',

    // Roles de docentes
    'role_docente' => 'Docente',
    'role_instructor' => 'Instructor/a',
    'role_orador' => 'Orador/a',
    'role_expositor' => 'Expositor/a',
    'role_conferencista' => 'Conferencista',
    'role_facilitador' => 'Facilitador/a',
    'role_tutor' => 'Tutor/a',
    'role_coordinador' => 'Coordinador/a',

    // Navegación y botones
    'back_to_trajectory' => 'Volver a la trayectoria',
    'btn_print_pdf' => 'Imprimir / Guardar como PDF',
    'btn_download_pdf' => 'Descargar PDF',
    'btn_print' => 'Imprimir',
    'print_landscape_hint' => 'Sugerencia: Al imprimir, seleccione la orientación "Horizontal" (Landscape).',

    // Certificado con imagen de fondo
    'cert_type_trainer' => 'Certificado de {formador}',
    'cert_type_approval' => 'Certificado de Aprobación',
    'cert_hereby_grants' => 'Por la presente, {institucion} otorga el presente',
    'cert_desc_trainer' => 'El día {fecha} se certifica que {nombre} con DNI {dni} ha desempeñado una destacada labor como {formador} de "{nombre_curso}", impartiendo sus conocimientos con un alto nivel de competencia.',
    'cert_desc_approval' => 'El día {fecha} se certifica que {nombre} con DNI {dni} ha completado y aprobado satisfactoriamente el curso "{nombre_curso}" con una carga horaria de {carga_horaria} horas.',
    'cert_type_completion' => 'Certificado de Finalización',
    'cert_desc_completion' => 'El día {fecha} se certifica que {nombre} con DNI {dni} ha completado satisfactoriamente el curso "{nombre_curso}" con una carga horaria de {carga_horaria} horas.',

    // Fechas de curso (para nuevo sistema de fechas)
    'dictado_el' => 'dictado el {fecha}',
    'dictado_del_al' => 'dictado del {fecha_inicio} al {fecha_fin}',

    // Lugar y fecha (variable inteligente {{lugar_fecha}})
    'lugar_fecha_con_ciudad' => 'En la ciudad de {ciudad}, a los {dia} días del mes de {mes} de {anio}',
    'lugar_fecha_sin_ciudad' => 'A los {dia} días del mes de {mes} de {anio}',

    'qr_code' => 'Código QR',
    'validate_certificate' => 'Validar certificado',

    // Certificado moderno (sin imagen)
    'academic_certification' => 'Certificación Académica',
    'cert_granted_to' => 'Se otorga el presente certificado a',
    'cert_for_completing' => 'por haber completado y aprobado satisfactoriamente la formación',
    'workload' => 'Carga Horaria',
    'hours' => 'horas',
    'hours_short' => 'hs.',
    'completion_date' => 'Fecha de Finalización',
    'signature' => 'Firma',
    'authorized_signature' => 'Firma Autorizada',
    'verify_at' => 'Verifica este certificado en',

    // Certificado docente
    'teacher_certification' => 'Certificación Docente',
    'participation_certificate' => 'Certificado de Participación',
    'it_is_certified_that' => 'Se certifica que',
    'participated_as' => 'participó como {rol} en',
    'participated_as_simple' => 'participó como {rol} en',
    'is_participating_as' => 'está participando como {rol} en',
    'has_been_assigned_as' => 'ha sido {asignado} como {rol} en',
    'provisional_certificate' => 'Constancia de Participación',
    'provisional_note' => 'Documento provisional - Curso en progreso',
    'assignment_certificate' => 'Constancia de Asignación',
    'assignment_note' => 'Curso por iniciar',
    'cohort' => 'Cohorte',
    'period' => 'Período',
    'to' => 'al',

    // Constancias
    'constancy' => 'Constancia',
    'constancy_regular_student' => 'Constancia de Alumno Regular',
    'constancy_body_regular' => 'se encuentra cursando activamente la formación:',
    'constancy_completion' => 'Constancia de Finalización',
    'constancy_body_completion' => 'ha finalizado la cursada de la formación:',
    'constancy_enrollment' => 'Constancia de Inscripción',
    'constancy_body_enrollment' => 'se encuentra {inscripto} para comenzar la formación:',
    'constancy_intro' => 'Por medio de la presente, se deja constancia que',
    'dni_label' => 'D.N.I. N°',
    'dni_short' => 'DNI',
    'start_date_scheduled' => 'La fecha de inicio estipulada es el {fecha_inicio}.',
    'constancy_closing' => 'Se extiende la presente constancia a los fines que estime corresponder.',
    'scan_qr_to_verify' => 'Para verificar la validez de este documento, escanee el código QR.',

    // Analítico académico
    'academic_trajectory' => 'Trayectoria Académica',
    'student' => 'Estudiante',
    'course_timeline' => 'Línea de Tiempo del Curso',
    'no_timeline' => 'Sin información de trayectoria disponible',
    'summary' => 'Resumen',
    'final_grade' => 'Nota Final',
    'attendance' => 'Asistencia',
    'completion' => 'Finalización',
    'competencies' => 'Competencias',

    // Validación
    'validation_title' => 'Documento Validado',
    'validation_success' => 'Documento Validado Correctamente',
    'validation_success_text' => 'La información que se muestra a continuación ha sido verificada en los registros de',
    'validation_success_confirms' => 'y confirma la autenticidad del documento presentado.',
    'validation_know_more' => 'Conocer más sobre la institución certificadora',
    'validation_invalid' => 'Documento No Válido',
    'validation_invalid_text' => 'El código de validación ingresado no corresponde a ningún documento registrado.',

    // Participación docente
    'teacher_participation' => 'Participación Docente',
    'teacher_role' => 'Rol',
    'teacher_hours' => 'Carga Horaria Dictada',
    'teacher_period' => 'Período',
    'teacher_cohort' => 'Cohorte',

    // Features de la página principal
    'feature_verifiable' => 'Certificados Verificables',
    'feature_verifiable_desc' => 'Con código QR de validación única y segura',
    'feature_access' => 'Acceso 24/7',
    'feature_access_desc' => 'Disponible en cualquier momento y desde cualquier lugar',
    'feature_download' => 'Descarga Inmediata',
    'feature_download_desc' => 'PDF de alta calidad listo para imprimir',

    // Página Mis Cursos (cursus.php)
    'my_courses_title' => 'Mis Cursos',
    'courses_as_student_count' => 'Cursos como estudiante',
    'approved_count' => 'Aprobados',
    'in_progress_count' => 'En curso',
    'as_teacher_count' => 'Como docente/instructor',
    'my_courses_as_student' => 'Mis Cursos como Estudiante',
    'my_participations_as_teacher' => 'Mis Participaciones como Docente/Instructor',
    'teacher_participation_desc' => 'Cursos en los que participó como docente, instructor, orador u otro rol.',
    'no_courses_message' => 'No tiene cursos asociados a su DNI.',
    'starts_on' => 'Inicia el',
    'view_full_trajectory' => 'Ver Trayectoria Completa',
    'view_full_participation' => 'Ver Participación Completa',
    'pdf_analytical' => 'PDF Analítico',
    'certificate' => 'Certificado',
    'constancy' => 'Constancia',
    'hours_taught' => 'Horas dictadas',

    // Tabularium (trayectoria académica completa)
    'back_to_my_courses' => 'Volver a mis cursos',
    'teacher_participation_badge' => 'PARTICIPACIÓN DOCENTE',
    'participation_details' => 'Detalles de la Participación',
    'description' => 'Descripción',
    'role' => 'Rol',
    'available_documents' => 'Documentos Disponibles',
    'participation_constancy' => 'Constancia de Participación',
    'teacher_certificate_of' => 'Certificado de {rol}',
    'available' => 'Disponible',
    'view' => 'Ver',
    'download_pdf' => 'Descargar PDF',
    'analytical' => 'Analítico',
    'complete_academic_record' => 'Registro académico completo',
    'approval_certificate' => 'Certificado de Aprobación',
    'regular_student_constancy' => 'Constancia de Alumno Regular',
    'completion_constancy' => 'Constancia de Finalización',
    'enrollment_constancy' => 'Constancia de Inscripción',

    // Timeline y eventos
    'start_date' => 'Inicio',
    'not_defined' => 'No definido',
    'timeline_subtitle' => 'Su recorrido en el curso',
    'pending' => 'Pendiente',
    'no_competencies' => 'Sin competencias asignadas',
    'no_events_yet' => 'Sin eventos registrados',
    'events_will_appear' => 'Los eventos del curso aparecerán aquí',
    'progress' => 'Progreso',

    // Eventos de timeline (para traducción dinámica)
    'event_inicio_del_curso' => 'Inicio del curso',
    'event_fecha_finalizacion_registrada' => 'Fecha de finalización registrada',
    'event_inscripcion_al_curso' => 'Inscripción al curso',
    'event_finalizacion_del_curso' => 'Finalización del curso',
    'event_aprobacion' => 'Aprobación',
    'event_evaluacion' => 'Evaluación',
    'event_certificacion' => 'Certificación',

    // Tipos de documentos descriptivos
    'provisional_document' => 'Documento provisional',
    'official_document' => 'Documento oficial',
    'available_when_completed' => 'Disponible al completar',
    'official_approval' => 'Documento oficial de aprobación',
    'active_enrollment_proof' => 'Comprobante de inscripción activa',
    'course_completed_proof' => 'Comprobante de curso completado',
    'enrollment_proof' => 'Comprobante de inscripción',

    // Competencias (traducciones dinámicas)
    'competency_mediacion' => 'Mediación',
    'competency_facilitacion_de_circulos' => 'Facilitación de Círculos',
    'competency_facilitacion' => 'Facilitación',
    'competency_practicas_restaurativas' => 'Prácticas Restaurativas',
    'competency_comunicacion_no_violenta' => 'Comunicación No Violenta',
    'competency_resolucion_de_conflictos' => 'Resolución de Conflictos',
    'competency_justicia_restaurativa' => 'Justicia Restaurativa',
    'competency_trabajo_en_equipo' => 'Trabajo en Equipo',
    'competency_liderazgo' => 'Liderazgo',
    'competency_negociacion' => 'Negociación',

    // Textos de templates (para internacionalización de elementos estáticos)
    'template.titulo_certificado' => 'CERTIFICADO',
    'template.titulo_constancia' => 'CONSTANCIA',
    'template.otorgamiento' => 'Por el presente se otorga el presente',
    'template.subtitulo_aprobacion' => 'Certificado de Aprobación',
    'template.subtitulo_finalizacion' => 'Certificado de Finalización',
    'template.subtitulo_docente' => 'Certificado de Formación',

    // Párrafos completos para certificados (con variables)
    'template.parrafo_aprobacion' => 'El día {{fecha}} se certifica que **{{nombre_completo}}** con DNI **{{dni}}** ha completado y aprobado satisfactoriamente el curso **{{nombre_curso}}** con una carga horaria de {{carga_horaria}} horas.',
    'template.parrafo_finalizacion' => 'El día {{fecha}} se certifica que **{{nombre_completo}}** con DNI **{{dni}}** ha completado satisfactoriamente el curso **{{nombre_curso}}** con una carga horaria de {{carga_horaria}} horas.',
    'template.parrafo_docente' => 'El día {{fecha}} se certifica que **{{nombre_completo}}** con DNI **{{dni}}** ha desempeñado una destacada labor como {{rol}} del curso **{{nombre_curso}}**, impartiendo sus conocimientos con un alto nivel de competencia.',

    // Párrafos v2: con lugar_fecha (ciudad opcional) + período del curso
    // {{lugar_fecha}} = "En la ciudad de X, a los Y días del mes de Z de W" o "A los Y días del mes de Z de W"
    // {{fecha_curso}} = "dictado el X" o "dictado del X al Y"
    'template.parrafo_aprobacion_v2' => '{{lugar_fecha}}, se certifica que **{{nombre_completo}}** con DNI **{{dni}}** ha completado y aprobado satisfactoriamente el curso **{{nombre_curso}}** {{fecha_curso}}, con una carga horaria de {{carga_horaria}} horas.',
    'template.parrafo_finalizacion_v2' => '{{lugar_fecha}}, se certifica que **{{nombre_completo}}** con DNI **{{dni}}** ha completado satisfactoriamente el curso **{{nombre_curso}}** {{fecha_curso}}, con una carga horaria de {{carga_horaria}} horas.',
    'template.parrafo_docente_v2' => '{{lugar_fecha}}, se certifica que **{{nombre_completo}}** con DNI **{{dni}}** se ha desempeñado como {{rol}} en el curso **{{nombre_curso}}** {{fecha_curso}}, impartiendo sus conocimientos con un alto nivel de competencia.',
];
