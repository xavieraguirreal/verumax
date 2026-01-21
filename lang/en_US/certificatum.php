<?php
/**
 * Translations Certificatum - United States English (en_US)
 * Formal language with inclusive binary representation for educational/institutional context
 *
 * Inclusive Language Standards Applied:
 * - Gendered terms: Include both forms (teachers and educators, instructor and instructors)
 * - Pronouns: they/them preferred, or he or she where applicable
 * - Professional titles: gender-neutral where possible (educator, teaching professional)
 * - Collective language: Neutral constructions (teaching personnel, educational staff)
 *
 * Translation Agent: translator-en-us-inclusive
 * Source Language: Spanish Argentine (es_AR)
 * Target Language: English US (en_US)
 * Version: 1.0
 * Last Updated: 2025-12-23
 */

return [
    // Homepage / Main page
    'page_title' => 'Certificates and Credentials',
    'page_subtitle' => 'Access your certificates, letters of completion, and full academic record. Download your documents in PDF format with unique QR validation code.',
    'search_title' => 'Document Number',
    'search_placeholder' => 'Enter your document (numbers only)',
    'search_help' => 'Numbers only, no periods or spaces',
    'search_button' => 'View My Certificates',
    'search_enter_document' => 'Enter your document number',
    'search_example' => 'E.g.: 12345678',
    'search_not_found' => 'No certificates found',
    'search_not_found_desc' => 'No certificates were found associated with the document entered. Please verify the number is correct or contact the institution.',

    // Course list
    'my_courses' => 'My Courses',
    'courses_as_student' => 'Courses as Student',
    'courses_as_teacher' => 'Participations as Teaching Professional',
    'no_courses_found' => 'No courses were found registered for this ID number',
    'course_hours' => 'Workload',
    'course_status' => 'Status',
    'course_grade' => 'Final Grade',
    'course_completion' => 'Completion',
    'view_trajectory' => 'View Full Academic Record',

    // Course status
    'status_approved' => 'Approved',
    'status_in_progress' => 'In Progress',
    'status_enrolled' => 'Enrolled',
    'status_finished' => 'Completed',
    'status_pending' => 'Pending Start',
    'status_preregistered' => 'Pre-Registered',

    // Document types (genus in Latin)
    'doc_analyticum' => 'Academic Transcript',
    'doc_certificatum_approbationis' => 'Certificate of Completion',
    'doc_testimonium_regulare' => 'Letter of Regular Student Status',
    'doc_testimonium_completionis' => 'Letter of Course Completion',
    'doc_testimonium_inscriptionis' => 'Letter of Enrollment',
    'doc_certificatum_doctoris' => 'Teaching Professional Certificate',
    'doc_testimonium_doctoris' => 'Teaching Professional Letter of Participation',

    // Teacher/educator roles
    'role_docente' => 'Teacher',
    'role_instructor' => 'Instructor',
    'role_orador' => 'Speaker',
    'role_expositor' => 'Exhibitor',
    'role_conferencista' => 'Lecturer',
    'role_facilitador' => 'Facilitator',
    'role_tutor' => 'Tutor',
    'role_coordinador' => 'Coordinator',

    // Navigation and buttons
    'back_to_trajectory' => 'Return to Academic Record',
    'btn_print_pdf' => 'Print / Save as PDF',
    'btn_download_pdf' => 'Download PDF',
    'btn_print' => 'Print',
    'print_landscape_hint' => 'Tip: When printing, select the "Landscape" orientation.',

    // Certificate with background image
    'cert_type_trainer' => 'Certificate of {formador}',
    'cert_type_approval' => 'Certificate of Completion',
    'cert_hereby_grants' => '{institucion} hereby grants this certificate to recognize',
    'cert_desc_trainer' => 'This certifies that on {fecha}, {nombre} with ID {dni} has served with distinction as {formador} of "{nombre_curso}", demonstrating high-level competency in delivering educational content.',
    'cert_desc_approval' => 'This certifies that on {fecha}, {nombre} with ID {dni} has successfully completed and passed the course "{nombre_curso}" with a workload of {carga_horaria} hours.',
    'qr_code' => 'QR Code',
    'validate_certificate' => 'Validate Certificate',

    // Modern certificate (without image)
    'academic_certification' => 'Academic Credential',
    'cert_granted_to' => 'This certificate is awarded to',
    'cert_for_completing' => 'for having successfully completed and passed the training',
    'workload' => 'Workload',
    'hours' => 'hours',
    'hours_short' => 'hrs.',
    'completion_date' => 'Completion Date',
    'signature' => 'Signature',
    'authorized_signature' => 'Authorized Signature',
    'verify_at' => 'Verify this certificate at',

    // Teaching professional certificate
    'teacher_certification' => 'Teaching Professional Credential',
    'participation_certificate' => 'Certificate of Participation',
    'it_is_certified_that' => 'It is hereby certified that',
    'participated_as' => 'served as {rol} in',
    'participated_as_simple' => 'served as {rol} in',
    'is_participating_as' => 'is currently serving as {rol} in',
    'has_been_assigned_as' => 'has been {asignado} as {rol} in',
    'provisional_certificate' => 'Letter of Teaching Participation',
    'provisional_note' => 'Provisional Document - Course in Progress',
    'assignment_certificate' => 'Letter of Teaching Assignment',
    'assignment_note' => 'Course pending start',
    'cohort' => 'Cohort',
    'period' => 'Period',
    'to' => 'to',

    // Letters of completion and status
    'constancy' => 'Letter of Status',
    'constancy_regular_student' => 'Letter of Regular Student Status',
    'constancy_body_regular' => 'is actively enrolled in the following course or training program:',
    'constancy_completion' => 'Letter of Course Completion',
    'constancy_body_completion' => 'has completed the following course or training program:',
    'constancy_enrollment' => 'Letter of Enrollment',
    'constancy_body_enrollment' => 'is {inscripto} to begin the following training:',
    'constancy_intro' => 'This letter certifies that',
    'dni_label' => 'ID Number',
    'dni_short' => 'ID',
    'start_date_scheduled' => 'The scheduled start date is {fecha_inicio}.',
    'constancy_closing' => 'This letter is issued for all purposes to which it may serve.',
    'scan_qr_to_verify' => 'To verify the authenticity of this document, please scan the QR code.',

    // Academic transcript
    'academic_trajectory' => 'Academic Record',
    'student' => 'Student',
    'course_timeline' => 'Course Timeline',
    'no_timeline' => 'No academic trajectory information available',
    'summary' => 'Summary',
    'final_grade' => 'Final Grade',
    'attendance' => 'Attendance',
    'completion' => 'Completion',
    'competencies' => 'Competencies',

    // Document validation
    'validation_title' => 'Verified Document',
    'validation_success' => 'Document Successfully Verified',
    'validation_success_text' => 'The information displayed below has been verified in the records of',
    'validation_success_confirms' => 'and confirms the authenticity of the presented document.',
    'validation_know_more' => 'Learn More About the Issuing Institution',
    'validation_invalid' => 'Invalid Document',
    'validation_invalid_text' => 'The validation code entered does not correspond to any registered document.',

    // Teaching professional participation
    'teacher_participation' => 'Teaching Professional Participation',
    'teacher_role' => 'Role',
    'teacher_hours' => 'Hours Taught',
    'teacher_period' => 'Period',
    'teacher_cohort' => 'Cohort',

    // Homepage features
    'feature_verifiable' => 'Verifiable Certificates',
    'feature_verifiable_desc' => 'With unique and secure QR validation code',
    'feature_access' => '24/7 Access',
    'feature_access_desc' => 'Available anytime and from anywhere',
    'feature_download' => 'Instant Download',
    'feature_download_desc' => 'High-quality PDF ready to print',

    // My Courses page (cursus.php)
    'my_courses_title' => 'My Courses',
    'courses_as_student_count' => 'Courses as student',
    'approved_count' => 'Approved',
    'in_progress_count' => 'In progress',
    'as_teacher_count' => 'As teaching professional',
    'my_courses_as_student' => 'My Courses as Student',
    'my_participations_as_teacher' => 'My Participations as Teaching Professional',
    'teacher_participation_desc' => 'Courses where you have served as teacher, instructor, speaker, or in another teaching or facilitation role.',
    'no_courses_message' => 'You do not have any courses associated with your ID number.',
    'starts_on' => 'Starts on',
    'view_full_trajectory' => 'View Full Academic Record',
    'view_full_participation' => 'View Full Teaching Record',
    'pdf_analytical' => 'Analytical PDF',
    'certificate' => 'Certificate',
    'constancy' => 'Letter of Status',
    'hours_taught' => 'Hours taught',

    // Tabularium (complete academic trajectory)
    'back_to_my_courses' => 'Return to My Courses',
    'teacher_participation_badge' => 'TEACHING PROFESSIONAL PARTICIPATION',
    'participation_details' => 'Participation Details',
    'description' => 'Description',
    'role' => 'Role',
    'available_documents' => 'Available Documents',
    'participation_constancy' => 'Letter of Teaching Participation',
    'teacher_certificate_of' => 'Certificate of {rol}',
    'available' => 'Available',
    'view' => 'View',
    'download_pdf' => 'Download PDF',
    'analytical' => 'Transcript',
    'complete_academic_record' => 'Complete academic record',
    'approval_certificate' => 'Certificate of Completion',
    'regular_student_constancy' => 'Letter of Regular Student Status',
    'completion_constancy' => 'Letter of Course Completion',
    'enrollment_constancy' => 'Letter of Enrollment',

    // Competencies (dynamic translations)
    'competency_mediacion' => 'Mediation',
    'competency_facilitacion_de_circulos' => 'Facilitation of Restorative Circles',
    'competency_facilitacion' => 'Facilitation',
    'competency_practicas_restaurativas' => 'Restorative Practices',
    'competency_comunicacion_no_violenta' => 'Nonviolent Communication',
    'competency_resolucion_de_conflictos' => 'Conflict Resolution',
    'competency_justicia_restaurativa' => 'Restorative Justice',
    'competency_trabajo_en_equipo' => 'Teamwork',
    'competency_liderazgo' => 'Leadership',
    'competency_negociacion' => 'Negotiation',
];
