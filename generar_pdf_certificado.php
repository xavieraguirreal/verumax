<?php
// Generador de PDF para Certificado de Aprobación
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

// Verificar que el curso esté aprobado
if ($curso['estado'] !== 'Aprobado') {
    die('Error: El curso no está aprobado. No se puede generar certificado.');
}

// 3. Configurar variables institucionales
if ($institucion == 'sajur') {
    $nombre_inst = 'Sociedad Argentina de Justicia Restaurativa';
    $nombre_corto = 'SAJuR';
    $color_primary = array(0, 104, 55); // Verde SAJuR
    $color_secondary = array(0, 68, 36);
    $logo_url = 'https://placehold.co/200x200/006837/ffffff?text=SAJUR';
} elseif ($institucion == 'liberte') {
    $nombre_inst = 'Cooperativa de Trabajo Liberté';
    $nombre_corto = 'Liberté';
    $color_primary = array(22, 163, 74); // Verde Liberté
    $color_secondary = array(21, 128, 61);
    $logo_url = 'https://placehold.co/200x200/16a34a/ffffff?text=LIBERTE';
} else {
    $nombre_inst = 'Institución';
    $nombre_corto = 'INST';
    $color_primary = array(59, 130, 246);
    $color_secondary = array(37, 99, 235);
    $logo_url = 'https://placehold.co/200x200/3b82f6/ffffff?text=INST';
}

// 4. Preparar datos del curso
$nombre_alumno = htmlspecialchars($alumno['nombre_completo']);
$nombre_curso = htmlspecialchars($curso['nombre_curso']);
$carga_horaria = htmlspecialchars($curso['carga_horaria']);
$codigo_validacion = 'VALID-' . substr(md5($dni . $curso_id), 0, 12);
$url_validacion = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/validar.php?codigo=' . urlencode($codigo_validacion);

// Buscar fecha de aprobación
$fecha_aprobacion = date('d/m/Y');
if (!empty($curso['trayectoria'])) {
    foreach ($curso['trayectoria'] as $evento) {
        if ($evento['tipo'] == 'Aprobación' || $evento['tipo'] == 'Certificación') {
            $fecha_aprobacion = $evento['fecha'];
            break;
        }
    }
}

// 5. Cargar TCPDF
require_once('includes/tcpdf/tcpdf.php');

// 6. Crear PDF personalizado para certificado (Horizontal)
class PDF_Certificado extends TCPDF {
    public $institucion_nombre;
    public $color_primary;
    public $color_secondary;

    public function Header() {
        // Sin header para certificado (diseño limpio)
    }

    public function Footer() {
        // Código de validación en el footer
        $this->SetY(-15);
        $this->SetFont('helvetica', '', 7);
        $this->SetTextColor(120, 120, 120);
        $this->Cell(0, 5, 'Código de validación: ' . $this->codigo_validacion . ' | Verificar en: ' . $this->url_validacion, 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }

    // Método para agregar marca de agua
    public function addWatermark($text) {
        $this->SetAlpha(0.1);
        $this->StartTransform();
        $this->Rotate(45, 148, 105);
        $this->SetFont('helvetica', 'B', 60);
        $this->SetTextColor($this->color_secondary[0], $this->color_secondary[1], $this->color_secondary[2]);
        $this->Text(60, 120, $text);
        $this->StopTransform();
        $this->SetAlpha(1);
    }
}

// 7. Inicializar PDF (Formato horizontal)
$pdf = new PDF_Certificado('L', PDF_UNIT, 'A4', true, 'UTF-8', false);

// Variables personalizadas
$pdf->institucion_nombre = $nombre_inst;
$pdf->color_primary = $color_primary;
$pdf->color_secondary = $color_secondary;
$pdf->logo_url = $logo_url;
$pdf->codigo_validacion = $codigo_validacion;
$pdf->url_validacion = $url_validacion;

// Configuración del documento
$pdf->SetCreator('ValidarCert');
$pdf->SetAuthor($nombre_inst);
$pdf->SetTitle('Certificado - ' . $nombre_curso);
$pdf->SetSubject('Certificado de Aprobación');

// Márgenes
$pdf->SetMargins(20, 20, 20);
$pdf->SetAutoPageBreak(FALSE, 20);

// Agregar página
$pdf->AddPage();

// 8. Diseño del certificado

// Marca de agua
$pdf->addWatermark($nombre_corto);

// Borde decorativo
$pdf->SetLineStyle(array('width' => 1.5, 'color' => $color_primary));
$pdf->Rect(10, 10, 277, 190, 'D');
$pdf->SetLineStyle(array('width' => 0.5, 'color' => $color_secondary));
$pdf->Rect(12, 12, 273, 186, 'D');

// Logo institucional
if (!empty($logo_url)) {
    $pdf->Image($logo_url, 125, 25, 45, 45, '', '', 'T', false, 300, '', false, false, 0, false, false, false);
}

// Título "CERTIFICADO"
$pdf->SetY(75);
$pdf->SetFont('helvetica', 'B', 28);
$pdf->SetTextColor($color_primary[0], $color_primary[1], $color_primary[2]);
$pdf->Cell(0, 10, 'CERTIFICADO DE APROBACIÓN', 0, 1, 'C');

// Línea decorativa
$pdf->Ln(3);
$pdf->SetLineStyle(array('width' => 0.8, 'color' => $color_primary));
$pdf->Line(90, $pdf->GetY(), 207, $pdf->GetY());

$pdf->Ln(8);

// Nombre de la institución
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 6, $nombre_inst, 0, 1, 'C');

$pdf->Ln(5);

// Texto principal
$pdf->SetFont('helvetica', '', 12);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(0, 6, 'Certifica que', 0, 1, 'C');

$pdf->Ln(3);

// Nombre del alumno (destacado)
$pdf->SetFont('helvetica', 'B', 20);
$pdf->SetTextColor($color_secondary[0], $color_secondary[1], $color_secondary[2]);
$pdf->Cell(0, 10, strtoupper($nombre_alumno), 0, 1, 'C');

// Línea bajo el nombre
$pdf->SetLineStyle(array('width' => 0.5, 'color' => $color_secondary));
$pdf->Line(80, $pdf->GetY(), 217, $pdf->GetY());

$pdf->Ln(6);

// DNI
$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(100, 100, 100);
$pdf->Cell(0, 5, 'DNI: ' . $dni, 0, 1, 'C');

$pdf->Ln(5);

// Texto curso
$pdf->SetFont('helvetica', '', 12);
$pdf->SetTextColor(60, 60, 60);
$pdf->Cell(0, 6, 'ha completado satisfactoriamente el curso', 0, 1, 'C');

$pdf->Ln(2);

// Nombre del curso (destacado)
$pdf->SetFont('helvetica', 'B', 16);
$pdf->SetTextColor($color_primary[0], $color_primary[1], $color_primary[2]);
$pdf->MultiCell(0, 8, '"' . $nombre_curso . '"', 0, 'C', false, 1);

$pdf->Ln(2);

// Carga horaria y fecha
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(80, 80, 80);
$pdf->Cell(0, 5, 'con una carga horaria de ' . $carga_horaria . ' horas', 0, 1, 'C');
$pdf->Ln(1);
$pdf->Cell(0, 5, 'Aprobado el ' . $fecha_aprobacion, 0, 1, 'C');

$pdf->Ln(8);

// QR Code (más pequeño, en esquina)
$style = array(
    'border' => 1,
    'vpadding' => 'auto',
    'hpadding' => 'auto',
    'fgcolor' => array(0,0,0),
    'bgcolor' => array(255,255,255),
    'module_width' => 1,
    'module_height' => 1
);

$pdf->write2DBarcode($url_validacion, 'QRCODE,L', 245, 165, 30, 30, $style, 'N');

// Texto junto al QR
$pdf->SetXY(20, 172);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(100, 100, 100);
$pdf->MultiCell(50, 4, 'Escanee para verificar autenticidad', 0, 'L', false, 0);

// Firmas (espacio)
$pdf->SetY(165);
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(80, 80, 80);

// Línea de firma izquierda
$pdf->Line(50, 168, 100, 168);
$pdf->SetXY(50, 169);
$pdf->Cell(50, 4, 'Firma Autorizada', 0, 0, 'C');

// Línea de firma derecha
$pdf->Line(130, 168, 180, 168);
$pdf->SetXY(130, 169);
$pdf->Cell(50, 4, 'Dirección Académica', 0, 0, 'C');

// 9. Salida del PDF
$pdf->Output('Certificado_' . str_replace(' ', '_', $nombre_curso) . '_' . $dni . '.pdf', 'D');
?>
