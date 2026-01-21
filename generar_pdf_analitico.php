<?php
// Generador de PDF para Analítico
// Requiere TCPDF instalado en includes/tcpdf/

// 1. Obtener parámetros
$institucion = $_GET['institucion'] ?? null;
$dni = $_GET['dni'] ?? null;
$curso_id = $_GET['curso_id'] ?? null;

if (!$institucion || !$dni || !$curso_id) {
    die('Error: Parámetros incompletos.');
}

// 2. Cargar datos
if (!is_dir($institucion)) {
    die('Error: Institución no válida.');
}

require_once $institucion . '/datos.php';

$alumno = $base_de_datos_maestra[$dni] ?? null;
$curso = $alumno['cursos'][$curso_id] ?? null;

if (!$alumno || !$curso) {
    die('Error: Datos no encontrados.');
}

// 3. Configurar variables institucionales
if ($institucion == 'sajur') {
    $nombre_inst = 'Sociedad Argentina de Justicia Restaurativa';
    $nombre_corto = 'SAJuR';
    $color_primary = array(0, 104, 55); // Verde SAJuR
    $logo_url = 'https://placehold.co/150x150/006837/ffffff?text=SJ';
} elseif ($institucion == 'liberte') {
    $nombre_inst = 'Cooperativa de Trabajo Liberté';
    $nombre_corto = 'Liberté';
    $color_primary = array(22, 163, 74); // Verde Liberté
    $logo_url = 'https://placehold.co/150x150/16a34a/ffffff?text=L';
} else {
    $nombre_inst = 'Institución';
    $nombre_corto = 'INST';
    $color_primary = array(59, 130, 246);
    $logo_url = 'https://placehold.co/150x150/3b82f6/ffffff?text=?';
}

// 4. Preparar datos del curso
$nombre_alumno = htmlspecialchars($alumno['nombre_completo']);
$nombre_curso = htmlspecialchars($curso['nombre_curso']);
$carga_horaria = htmlspecialchars($curso['carga_horaria']);
$estado = htmlspecialchars($curso['estado']);
$codigo_validacion = 'VALID-' . substr(md5($dni . $curso_id), 0, 12);
$url_validacion = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/validar.php?codigo=' . urlencode($codigo_validacion);

// 5. Cargar TCPDF
require_once('includes/tcpdf/tcpdf.php');

// 6. Crear PDF
class PDF_Analitico extends TCPDF {
    public $institucion_nombre;
    public $color_primary;

    public function Header() {
        // Logo
        if (!empty($this->logo_url)) {
            $this->Image($this->logo_url, 15, 10, 25, 25, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
        }

        // Título institucional
        $this->SetFont('helvetica', 'B', 16);
        $this->SetTextColor($this->color_primary[0], $this->color_primary[1], $this->color_primary[2]);
        $this->SetY(12);
        $this->Cell(0, 10, $this->institucion_nombre, 0, false, 'C', 0, '', 0, false, 'T', 'M');

        $this->SetFont('helvetica', '', 10);
        $this->SetTextColor(100, 100, 100);
        $this->Ln(8);
        $this->Cell(0, 5, 'Analítico Académico', 0, false, 'C', 0, '', 0, false, 'T', 'M');

        // Línea separadora
        $this->Ln(8);
        $this->SetLineStyle(array('width' => 0.5, 'color' => $this->color_primary));
        $this->Line(15, $this->GetY(), 195, $this->GetY());
    }

    public function Footer() {
        $this->SetY(-25);
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(150, 150, 150);

        // Código de validación
        $this->Cell(0, 5, 'Código de validación: ' . $this->codigo_validacion, 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->Ln(4);

        // URL de validación
        $this->SetFont('helvetica', '', 7);
        $this->Cell(0, 5, 'Verificar autenticidad en: ' . $this->url_validacion, 0, false, 'C', 0, '', 0, false, 'T', 'M');
        $this->Ln(4);

        // Fecha de generación
        $this->Cell(0, 5, 'Documento generado el ' . date('d/m/Y H:i'), 0, false, 'C', 0, '', 0, false, 'T', 'M');

        // Línea superior del footer
        $this->SetY(-27);
        $this->SetLineStyle(array('width' => 0.3, 'color' => array(200, 200, 200)));
        $this->Line(15, $this->GetY(), 195, $this->GetY());
    }
}

// 7. Inicializar PDF
$pdf = new PDF_Analitico(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Variables personalizadas
$pdf->institucion_nombre = $nombre_inst;
$pdf->color_primary = $color_primary;
$pdf->logo_url = $logo_url;
$pdf->codigo_validacion = $codigo_validacion;
$pdf->url_validacion = $url_validacion;

// Configuración del documento
$pdf->SetCreator('ValidarCert');
$pdf->SetAuthor($nombre_inst);
$pdf->SetTitle('Analítico - ' . $nombre_curso);
$pdf->SetSubject('Analítico Académico');

// Márgenes
$pdf->SetMargins(15, 50, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(30);
$pdf->SetAutoPageBreak(TRUE, 30);

// Fuente
$pdf->SetFont('helvetica', '', 10);

// Agregar página
$pdf->AddPage();

// 8. Contenido del PDF

// Datos del alumno
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(0, 0, 0);
$pdf->Cell(0, 8, 'Datos del Estudiante', 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('helvetica', '', 10);
$pdf->SetFillColor(245, 245, 245);
$pdf->Cell(50, 7, 'Nombre Completo:', 1, 0, 'L', true);
$pdf->Cell(0, 7, $nombre_alumno, 1, 1, 'L');
$pdf->Cell(50, 7, 'DNI:', 1, 0, 'L', true);
$pdf->Cell(0, 7, $dni, 1, 1, 'L');

$pdf->Ln(8);

// Datos del curso
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'Datos del Curso', 0, 1, 'L');
$pdf->Ln(2);

$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(50, 7, 'Curso:', 1, 0, 'L', true);
$pdf->Cell(0, 7, $nombre_curso, 1, 1, 'L');
$pdf->Cell(50, 7, 'Código:', 1, 0, 'L', true);
$pdf->Cell(0, 7, $curso_id, 1, 1, 'L');
$pdf->Cell(50, 7, 'Carga Horaria:', 1, 0, 'L', true);
$pdf->Cell(0, 7, $carga_horaria . ' horas', 1, 1, 'L');
$pdf->Cell(50, 7, 'Estado:', 1, 0, 'L', true);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 7, $estado, 1, 1, 'L');

$pdf->Ln(8);

// Trayectoria académica
if (!empty($curso['trayectoria'])) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Trayectoria Académica', 0, 1, 'L');
    $pdf->Ln(2);

    $pdf->SetFont('helvetica', '', 9);

    foreach ($curso['trayectoria'] as $evento) {
        $fecha = htmlspecialchars($evento['fecha']);
        $tipo = htmlspecialchars($evento['tipo']);
        $descripcion = htmlspecialchars($evento['descripcion']);

        // Icono según tipo
        $icono = '';
        if ($tipo == 'Inscripción') $icono = '📝';
        elseif ($tipo == 'Inicio') $icono = '🚀';
        elseif ($tipo == 'Evaluación') $icono = '📋';
        elseif ($tipo == 'Aprobación') $icono = '✅';
        elseif ($tipo == 'Certificación') $icono = '🎓';

        $pdf->SetFillColor(250, 250, 250);
        $pdf->MultiCell(0, 6, $icono . ' ' . $fecha . ' - ' . $tipo . ': ' . $descripcion, 1, 'L', true, 1);
        $pdf->Ln(1);
    }
}

$pdf->Ln(5);

// Documentos emitidos
if (!empty($curso['documentos'])) {
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 8, 'Documentos Emitidos', 0, 1, 'L');
    $pdf->Ln(2);

    $pdf->SetFont('helvetica', '', 9);

    foreach ($curso['documentos'] as $doc) {
        $tipo_doc = htmlspecialchars($doc['tipo']);
        $fecha_emision = htmlspecialchars($doc['fecha_emision']);

        $pdf->SetFillColor(250, 250, 250);
        $pdf->Cell(70, 6, $tipo_doc, 1, 0, 'L', true);
        $pdf->Cell(0, 6, 'Emitido el ' . $fecha_emision, 1, 1, 'L', true);
    }
}

// QR Code con código de validación
$pdf->Ln(10);
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 6, 'Código QR de Validación', 0, 1, 'C');
$pdf->Ln(2);

// Generar QR
$style = array(
    'border' => 2,
    'vpadding' => 'auto',
    'hpadding' => 'auto',
    'fgcolor' => array(0,0,0),
    'bgcolor' => array(255,255,255),
    'module_width' => 1,
    'module_height' => 1
);

$pdf->write2DBarcode($url_validacion, 'QRCODE,L', 80, '', 50, 50, $style, 'N');

$pdf->Ln(55);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, 'Escanee el código QR para verificar la autenticidad de este documento', 0, 1, 'C');

// 9. Salida del PDF
$pdf->Output('Analitico_' . str_replace(' ', '_', $nombre_curso) . '_' . $dni . '.pdf', 'D');
?>
