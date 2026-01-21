<?php
/**
 * Traduções Certificatum - Português Brasil (pt_BR)
 * Linguagem formal para contexto institucional/educacional
 */

return [
    // Página principal
    'page_title' => 'Certificados',
    'page_subtitle' => 'Acesse seus certificados, declarações e histórico acadêmico completo. Baixe seus documentos em formato PDF com código QR de validação.',
    'search_title' => 'Número do Documento',
    'search_placeholder' => 'Digite seu documento (somente números)',
    'search_help' => 'Somente números, sem pontos ou espaços',
    'search_button' => 'Ver meus certificados',
    'search_enter_document' => 'Digite seu número de documento',
    'search_example' => 'Ex: 12345678',
    'search_not_found' => 'Nenhum certificado encontrado',
    'search_not_found_desc' => 'Não foram encontrados certificados associados ao documento informado. Verifique se o número está correto ou entre em contato com a instituição.',

    // Lista de cursos
    'my_courses' => 'Meus Cursos',
    'courses_as_student' => 'Cursos como Estudante',
    'courses_as_teacher' => 'Participações como Docente',
    'no_courses_found' => 'Nenhum curso registrado para este documento',
    'course_hours' => 'Carga horária',
    'course_status' => 'Status',
    'course_grade' => 'Nota final',
    'course_completion' => 'Conclusão',
    'view_trajectory' => 'Ver Trajetória Completa',

    // Estados de curso
    'status_approved' => 'Aprovado',
    'status_in_progress' => 'Em Andamento',
    'status_enrolled' => 'Matriculado',
    'status_finished' => 'Concluído',
    'status_completed' => 'Concluído',
    'status_pending' => 'A Iniciar',
    'status_preregistered' => 'Pré-matriculado',

    // Tipos de documento (genus em latim)
    'doc_analyticum' => 'Histórico Acadêmico',
    'doc_certificatum_approbationis' => 'Certificado de Conclusão',
    'doc_testimonium_regulare' => 'Declaração de Aluno Regular',
    'doc_testimonium_completionis' => 'Declaração de Conclusão',
    'doc_testimonium_inscriptionis' => 'Declaração de Matrícula',
    'doc_certificatum_doctoris' => 'Certificado de Docente/Instrutor',
    'doc_testimonium_doctoris' => 'Declaração de Participação Docente',

    // Funções de docentes
    'role_docente' => 'Docente',
    'role_instructor' => 'Instrutor/a',
    'role_orador' => 'Palestrante',
    'role_expositor' => 'Expositor/a',
    'role_conferencista' => 'Conferencista',
    'role_facilitador' => 'Facilitador/a',
    'role_tutor' => 'Tutor/a',
    'role_coordinador' => 'Coordenador/a',

    // Navegação e botões
    'back_to_trajectory' => 'Voltar à trajetória',
    'btn_print_pdf' => 'Imprimir / Salvar como PDF',
    'btn_download_pdf' => 'Baixar PDF',
    'btn_print' => 'Imprimir',
    'print_landscape_hint' => 'Sugestão: Ao imprimir, selecione a orientação "Paisagem" (Landscape).',

    // Certificado com imagem de fundo
    'cert_type_trainer' => 'Certificado de {formador}',
    'cert_type_approval' => 'Certificado de Conclusão',
    'cert_type_completion' => 'Certificado de Conclusão',
    'cert_hereby_grants' => 'Pelo presente, {institucion} confere o presente',
    'cert_desc_trainer' => 'Em {fecha}, certifica-se que {nombre} com documento {dni} desempenhou com excelência a função de {formador} em "{nombre_curso}", transmitindo seus conhecimentos com alto nível de competência.',
    'cert_desc_approval' => 'Em {fecha}, certifica-se que {nombre} com documento {dni} concluiu satisfatoriamente e foi {aprovado} no curso "{nombre_curso}" com carga horária de {carga_horaria} horas.',
    'cert_desc_completion' => 'Em {fecha}, certifica-se que {nombre} com documento {dni} concluiu satisfatoriamente o curso "{nombre_curso}" com carga horária de {carga_horaria} horas.',

    // Datas do curso (para novo sistema de datas)
    'dictado_el' => 'realizado em {fecha}',
    'dictado_del_al' => 'realizado de {fecha_inicio} a {fecha_fin}',

    // Local e data (variável inteligente {{lugar_fecha}})
    'lugar_fecha_con_ciudad' => 'Na cidade de {ciudad}, aos {dia} dias do mês de {mes} de {anio}',
    'lugar_fecha_sin_ciudad' => 'Aos {dia} dias do mês de {mes} de {anio}',

    'qr_code' => 'Código QR',
    'validate_certificate' => 'Validar certificado',

    // Certificado moderno (sem imagem)
    'academic_certification' => 'Certificação Acadêmica',
    'cert_granted_to' => 'Confere-se o presente certificado a',
    'cert_for_completing' => 'por ter concluído satisfatoriamente e sido {aprovado} na formação',
    'workload' => 'Carga Horária',
    'hours' => 'horas',
    'hours_short' => 'h',
    'completion_date' => 'Data de Conclusão',
    'signature' => 'Assinatura',
    'authorized_signature' => 'Assinatura Autorizada',
    'verify_at' => 'Verifique este certificado em',

    // Certificado docente
    'teacher_certification' => 'Certificação Docente',
    'participation_certificate' => 'Certificado de Participação',
    'it_is_certified_that' => 'Certifica-se que',
    'participated_as' => 'participou como {rol} em',
    'participated_as_simple' => 'participou como {rol} em',
    'is_participating_as' => 'está participando como {rol} em',
    'has_been_assigned_as' => 'foi {asignado} como {rol} na',
    'has_been_assigned_body' => 'foi {asignado} como {rol} na formação:',
    'is_participating_body' => 'está participando como {rol} na formação:',
    'assigned_root' => 'designad', // raíz para designado/designada
    'provisional_certificate' => 'Declaração de Participação',
    'provisional_note' => 'Documento provisório - Curso em andamento',
    'assignment_certificate' => 'Declaração de Designação',
    'assignment_note' => 'Curso a iniciar',
    'status_assigned' => 'Status: Designado - Curso a iniciar',
    'status_in_progress' => 'Status: Em andamento',
    'cohort' => 'Turma',
    'period' => 'Período',
    'to' => 'a',

    // Declarações
    'constancy' => 'Declaração',
    'constancy_regular_student' => 'Declaração de Aluno Regular',
    'constancy_body_regular' => 'está cursando ativamente a formação:',
    'constancy_completion' => 'Declaração de Conclusão',
    'constancy_body_completion' => 'concluiu a formação:',
    'constancy_enrollment' => 'Declaração de Matrícula',
    'constancy_body_enrollment' => 'está {inscripto} para iniciar a formação:',
    'constancy_intro' => 'Pela presente, declaramos que',
    'dni_label' => 'Documento N°',
    'dni_short' => 'Doc.',
    'start_date_scheduled' => 'A data de início prevista é {fecha_inicio}.',
    'constancy_closing' => 'Esta declaração é emitida para os devidos fins.',
    'scan_qr_to_verify' => 'Escaneie o código QR para validar este documento.',

    // Histórico acadêmico
    'academic_trajectory' => 'Trajetória Acadêmica',
    'student' => 'Estudante',
    'course_timeline' => 'Linha do Tempo do Curso',
    'no_timeline' => 'Informações de trajetória não disponíveis',
    'summary' => 'Resumo',
    'final_grade' => 'Nota Final',
    'attendance' => 'Frequência',
    'completion' => 'Conclusão',
    'competencies' => 'Competências',

    // Validação
    'validation_title' => 'Documento Validado',
    'validation_success' => 'Documento Validado com Sucesso',
    'validation_success_text' => 'As informações apresentadas a seguir foram verificadas nos registros de',
    'validation_success_confirms' => 'e confirmam a autenticidade do documento apresentado.',
    'validation_know_more' => 'Saiba mais sobre a instituição certificadora',
    'validation_invalid' => 'Documento Inválido',
    'validation_invalid_text' => 'O código de validação informado não corresponde a nenhum documento registrado.',

    // Participação docente
    'teacher_participation' => 'Participação Docente',
    'teacher_role' => 'Função',
    'teacher_hours' => 'Carga Horária Ministrada',
    'teacher_period' => 'Período',
    'teacher_cohort' => 'Turma',

    // Features da página principal
    'feature_verifiable' => 'Certificados Verificáveis',
    'feature_verifiable_desc' => 'Com código QR de validação única e segura',
    'feature_access' => 'Acesso 24/7',
    'feature_access_desc' => 'Disponível a qualquer momento e de qualquer lugar',
    'feature_download' => 'Download Imediato',
    'feature_download_desc' => 'PDF de alta qualidade pronto para imprimir',

    // Página Meus Cursos (cursus.php)
    'my_courses_title' => 'Meus Cursos',
    'courses_as_student_count' => 'Cursos como estudante',
    'approved_count' => 'Aprovados',
    'in_progress_count' => 'Em andamento',
    'as_teacher_count' => 'Como docente/instrutor',
    'my_courses_as_student' => 'Meus Cursos como Estudante',
    'my_participations_as_teacher' => 'Minhas Participações como Docente/Instrutor',
    'teacher_participation_desc' => 'Cursos em que você participou como docente, instrutor, palestrante ou outra função.',
    'no_courses_message' => 'Você não tem cursos associados ao seu documento.',
    'starts_on' => 'Inicia em',
    'view_full_trajectory' => 'Ver Trajetória Completa',
    'view_full_participation' => 'Ver Participação Completa',
    'pdf_analytical' => 'PDF Histórico',
    'certificate' => 'Certificado',
    'constancy' => 'Declaração',
    'hours_taught' => 'Horas ministradas',

    // Tabularium (trajetória acadêmica completa)
    'back_to_my_courses' => 'Voltar aos meus cursos',
    'teacher_participation_badge' => 'PARTICIPAÇÃO DOCENTE',
    'participation_details' => 'Detalhes da Participação',
    'description' => 'Descrição',
    'role' => 'Função',
    'available_documents' => 'Documentos Disponíveis',
    'participation_constancy' => 'Declaração de Participação',
    'teacher_certificate_of' => 'Certificado de {rol}',
    'available' => 'Disponível',
    'view' => 'Ver',
    'download_pdf' => 'Baixar PDF',
    'analytical' => 'Histórico',
    'complete_academic_record' => 'Registro acadêmico completo',
    'approval_certificate' => 'Certificado de Conclusão',
    'regular_student_constancy' => 'Declaração de Aluno Regular',
    'completion_constancy' => 'Declaração de Conclusão',
    'enrollment_constancy' => 'Declaração de Matrícula',

    // Timeline e eventos
    'start_date' => 'Data de Início',
    'not_defined' => 'Não definido',
    'timeline_subtitle' => 'Seu percurso no curso',
    'pending' => 'Pendente',
    'no_competencies' => 'Sem competências atribuídas',
    'no_events_yet' => 'Sem eventos registrados',
    'events_will_appear' => 'Os eventos do curso aparecerão aqui',
    'progress' => 'Progresso',

    // Eventos de timeline (para tradução dinâmica)
    'event_inicio_del_curso' => 'Início do curso',
    'event_fecha_finalizacion_registrada' => 'Data de conclusão registrada',
    'event_inscripcion_al_curso' => 'Inscrição no curso',
    'event_finalizacion_del_curso' => 'Conclusão do curso',
    'event_aprobacion' => 'Aprovação',
    'event_evaluacion' => 'Avaliação',
    'event_certificacion' => 'Certificação',

    // Tipos de documentos descritivos
    'provisional_document' => 'Documento provisório',
    'official_document' => 'Documento oficial',
    'available_when_completed' => 'Disponível após conclusão',
    'official_approval' => 'Documento oficial de aprovação',
    'active_enrollment_proof' => 'Comprovante de matrícula ativa',
    'course_completed_proof' => 'Comprovante de curso concluído',
    'enrollment_proof' => 'Comprovante de matrícula',

    // Competências (traduções dinâmicas)
    'competency_mediacion' => 'Mediação',
    'competency_facilitacion_de_circulos' => 'Facilitação de Círculos',
    'competency_facilitacion' => 'Facilitação',
    'competency_practicas_restaurativas' => 'Práticas Restaurativas',
    'competency_comunicacion_no_violenta' => 'Comunicação Não-Violenta',
    'competency_resolucion_de_conflictos' => 'Resolução de Conflitos',
    'competency_justicia_restaurativa' => 'Justiça Restaurativa',
    'competency_trabajo_en_equipo' => 'Trabalho em Equipe',
    'competency_liderazgo' => 'Liderança',
    'competency_negociacion' => 'Negociação',

    // Textos de templates (para internacionalização de elementos estáticos)
    'template.titulo_certificado' => 'CERTIFICADO',
    'template.titulo_constancia' => 'DECLARAÇÃO',
    'template.otorgamiento' => 'Pelo presente, outorga-se o presente',
    'template.subtitulo_aprobacion' => 'Certificado de Conclusão',
    'template.subtitulo_finalizacion' => 'Certificado de Conclusão',
    'template.subtitulo_docente' => 'Certificado de Formação',

    // Parágrafos completos para certificados (com variáveis)
    'template.parrafo_aprobacion' => 'Em {{fecha}}, certifica-se que **{{nombre_completo}}** com documento **{{dni}}** concluiu satisfatoriamente e foi aprovado no curso **{{nombre_curso}}** com carga horária de {{carga_horaria}} horas.',
    'template.parrafo_finalizacion' => 'Em {{fecha}}, certifica-se que **{{nombre_completo}}** com documento **{{dni}}** concluiu satisfatoriamente o curso **{{nombre_curso}}** com carga horária de {{carga_horaria}} horas.',
    'template.parrafo_docente' => 'Em {{fecha}}, certifica-se que **{{nombre_completo}}** com documento **{{dni}}** desempenhou com excelência a função de {{rol}} no curso **{{nombre_curso}}**, transmitindo seus conhecimentos com alto nível de competência.',

    // Parágrafos v2: com data de emissão + período do curso
    // {{lugar_fecha}} = "Na cidade de X, aos Y dias do mês de Z de W" ou "Aos Y dias do mês de Z de W"
    // {{fecha_curso}} = "realizado em X" ou "realizado de X a Y"
    'template.parrafo_aprobacion_v2' => '{{lugar_fecha}}, certifica-se que **{{nombre_completo}}** com documento **{{dni}}** concluiu satisfatoriamente e foi aprovado no curso **{{nombre_curso}}** {{fecha_curso}}, com carga horária de {{carga_horaria}} horas.',
    'template.parrafo_finalizacion_v2' => '{{lugar_fecha}}, certifica-se que **{{nombre_completo}}** com documento **{{dni}}** concluiu satisfatoriamente o curso **{{nombre_curso}}** {{fecha_curso}}, com carga horária de {{carga_horaria}} horas.',
    'template.parrafo_docente_v2' => '{{lugar_fecha}}, certifica-se que **{{nombre_completo}}** com documento **{{dni}}** atuou como {{rol}} no curso **{{nombre_curso}}** {{fecha_curso}}, transmitindo seus conhecimentos com alto nível de competência.',
];
