<?php
/**
 * Template de Credencial de Socio/Miembro
 * Sistema CERTIFICATUM - VERUMax
 *
 * Variables esperadas:
 * - $miembro: array con datos del socio
 * - $instance_config: configuración de la institución
 * - $codigo_validacion: código único para QR
 * - $qr_url: URL de la imagen QR
 * - $es_instancia_test: bool para watermark
 * - $t: función de traducción
 */

// Extraer datos del miembro
$nombre_completo = $miembro['nombre_completo'] ?? ($miembro['nombre'] . ' ' . $miembro['apellido']);
$dni = $miembro['identificador_principal'] ?? $miembro['dni'] ?? '';
$numero_asociado = $miembro['numero_asociado'] ?? '';
$tipo_asociado = $miembro['tipo_asociado'] ?? '';
$nombre_entidad = $miembro['nombre_entidad'] ?? '';
$categoria_servicio = $miembro['categoria_servicio'] ?? '';
$fecha_ingreso = $miembro['fecha_ingreso'] ?? '';
$foto_url = $miembro['foto_url'] ?? null;

// Datos de la institución
$nombre_institucion = $instance_config['nombre_completo'] ?? $instance_config['nombre'] ?? 'Institución';
$logo_url = $instance_config['logo_url'] ?? '';
$logo_secundario_url = $instance_config['logo_secundario_url'] ?? '';
$color_primario = $instance_config['color_primario'] ?? '#2E7D32';
$color_secundario = $instance_config['color_secundario'] ?? '#1B5E20';

// Config de credencial (JSON desde instances)
$credencial_config = json_decode($instance_config['credencial_config'] ?? '{}', true);
$texto_superior = $credencial_config['texto_superior'] ?? 'CREDENCIAL DE SOCIO';
$texto_inferior = $credencial_config['texto_inferior'] ?? '';
$mostrar_foto = $credencial_config['mostrar_foto'] ?? false;
$template_url = $credencial_config['template_url'] ?? null;

// Formatear fecha de ingreso
if ($fecha_ingreso) {
    $fecha_ingreso_fmt = date('d/m/Y', strtotime($fecha_ingreso));
} else {
    $fecha_ingreso_fmt = '';
}

// Formatear DNI con puntos
$dni_formateado = number_format((int)preg_replace('/[^0-9]/', '', $dni), 0, '', '.');
?>

<style>
    /* Estilos de credencial tipo tarjeta */
    .credencial-container {
        width: 500px;
        min-height: 300px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        overflow: hidden;
        position: relative;
        font-family: 'Inter', Arial, sans-serif;
        margin: 20px auto;
    }

    .credencial-header {
        background: linear-gradient(135deg, <?php echo $color_primario; ?> 0%, <?php echo $color_secundario; ?> 100%);
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .credencial-header img {
        max-height: 50px;
        max-width: 120px;
        object-fit: contain;
    }

    .credencial-banner {
        background: <?php echo $color_primario; ?>;
        color: white;
        text-align: center;
        padding: 8px 15px;
        font-size: 14px;
        font-weight: 600;
        letter-spacing: 1px;
    }

    .credencial-body {
        padding: 20px;
        display: flex;
        gap: 20px;
    }

    .credencial-foto {
        width: 100px;
        height: 120px;
        background: #f0f0f0;
        border: 2px solid #ddd;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }

    .credencial-foto img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .credencial-foto-placeholder {
        color: #999;
        font-size: 12px;
        text-align: center;
    }

    .credencial-datos {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .credencial-nombre {
        font-size: 20px;
        font-weight: 700;
        color: #333;
        margin-bottom: 5px;
        text-transform: uppercase;
    }

    .credencial-dni {
        font-size: 16px;
        font-weight: 600;
        color: #555;
        margin-bottom: 15px;
    }

    .credencial-asociado {
        font-size: 18px;
        font-weight: 700;
        color: <?php echo $color_primario; ?>;
        margin-bottom: 5px;
    }

    .credencial-servicio {
        font-size: 16px;
        font-weight: 600;
        color: <?php echo $color_primario; ?>;
        margin-bottom: 5px;
    }

    .credencial-ingreso {
        font-size: 14px;
        font-weight: 600;
        color: <?php echo $color_primario; ?>;
    }

    .credencial-qr {
        position: absolute;
        bottom: 50px;
        right: 20px;
        text-align: center;
    }

    .credencial-qr img {
        width: 70px;
        height: 70px;
    }

    .credencial-qr-code {
        font-size: 8px;
        color: #666;
        margin-top: 2px;
        font-family: monospace;
    }

    .credencial-footer {
        background: <?php echo $color_primario; ?>;
        color: white;
        text-align: center;
        padding: 10px 15px;
        font-size: 12px;
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
    }

    .credencial-footer a {
        color: white;
        text-decoration: none;
    }

    /* Watermark para modo test */
    .credencial-watermark {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        z-index: 100;
    }

    .credencial-watermark::before {
        content: 'PRUEBA';
        font-size: 60px;
        font-weight: bold;
        color: rgba(220, 38, 38, 0.2);
        transform: rotate(-35deg);
        letter-spacing: 10px;
    }

    @media print {
        .credencial-container {
            box-shadow: none;
            border: 1px solid #ddd;
        }
        .credencial-watermark::before {
            color: rgba(220, 38, 38, 0.25) !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
    }
</style>

<div class="credencial-container">
    <?php if ($es_instancia_test): ?>
        <div class="credencial-watermark"></div>
    <?php endif; ?>

    <!-- Header con logos -->
    <div class="credencial-header">
        <?php if ($logo_url): ?>
            <img src="<?php echo htmlspecialchars($logo_url); ?>" alt="Logo">
        <?php else: ?>
            <div></div>
        <?php endif; ?>

        <?php if ($logo_secundario_url): ?>
            <img src="<?php echo htmlspecialchars($logo_secundario_url); ?>" alt="Logo secundario">
        <?php endif; ?>
    </div>

    <!-- Banner con texto -->
    <?php if ($texto_superior): ?>
        <div class="credencial-banner">
            <?php echo htmlspecialchars($texto_superior); ?>
        </div>
    <?php endif; ?>

    <!-- Cuerpo -->
    <div class="credencial-body">
        <?php if ($mostrar_foto): ?>
            <div class="credencial-foto">
                <?php if ($foto_url): ?>
                    <img src="<?php echo htmlspecialchars($foto_url); ?>" alt="Foto">
                <?php else: ?>
                    <span class="credencial-foto-placeholder">Sin foto</span>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="credencial-datos">
            <div class="credencial-nombre"><?php echo htmlspecialchars($nombre_completo); ?></div>
            <div class="credencial-dni">DNI <?php echo htmlspecialchars($dni_formateado); ?></div>

            <?php if ($numero_asociado): ?>
                <div class="credencial-asociado">
                    <?php
                    // Mostrar ASOCIADA/ASOCIADO + número + tipo
                    $genero_asociado = $miembro['genero'] ?? '';
                    $prefijo = ($genero_asociado === 'Femenino') ? 'ASOCIADA' : 'ASOCIADO';
                    if ($nombre_entidad) {
                        $prefijo = 'ASOCIADA'; // Si es entidad, usar femenino
                    }
                    echo $prefijo . ' ' . htmlspecialchars($numero_asociado);
                    if ($tipo_asociado) {
                        echo ' ' . htmlspecialchars($tipo_asociado);
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php if ($categoria_servicio): ?>
                <div class="credencial-servicio"><?php echo htmlspecialchars($categoria_servicio); ?></div>
            <?php endif; ?>

            <?php if ($fecha_ingreso_fmt): ?>
                <div class="credencial-ingreso">INGRESO <?php echo $fecha_ingreso_fmt; ?></div>
            <?php endif; ?>
        </div>

        <!-- QR -->
        <div class="credencial-qr">
            <img src="<?php echo htmlspecialchars($qr_url); ?>" alt="QR">
            <div class="credencial-qr-code"><?php echo htmlspecialchars($codigo_validacion); ?></div>
        </div>
    </div>

    <!-- Footer -->
    <?php if ($texto_inferior): ?>
        <div class="credencial-footer">
            <?php echo $texto_inferior; ?>
        </div>
    <?php endif; ?>
</div>
