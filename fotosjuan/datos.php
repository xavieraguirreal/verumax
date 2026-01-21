<?php
/**
 * Base de Datos Simulada - FotosJuan
 * Fotógrafo Profesional - Juan Martínez
 * Especializado en: Bodas, Eventos, Retratos
 */

// Estructura: DNI del cliente => datos de galerías
$base_de_datos_maestra = [
    '33456789' => [
        'nombre_completo' => 'María González',
        'cursos' => [ // En contexto fotográfico, "cursos" representa galerías/sesiones
            'FJ-BOD-2024-001' => [
                'nombre_curso' => 'Boda María & Pedro - Hotel Alvear',
                'estado' => 'Finalizado', // Finalizado = Galería lista para descargar
                'carga_horaria' => 8, // Horas de cobertura
                'fecha_finalizacion' => '15/03/2024',
                'nota_final' => '', // No aplica para fotografía
                'asistencia' => '', // No aplica
                'competencias' => [
                    'Ceremonia religiosa',
                    'Sesión de novios',
                    'Fiesta y baile',
                    '450 fotos editadas'
                ],
                'trayectoria' => [
                    ['fecha' => '10/02/2024', 'evento' => 'Reunión previa', 'detalle' => 'Planificación de cobertura y momentos clave'],
                    ['fecha' => '01/03/2024', 'evento' => 'Pre-boda', 'detalle' => 'Sesión casual en Palermo'],
                    ['fecha' => '15/03/2024', 'evento' => 'Día de la boda', 'detalle' => 'Cobertura completa 8hs'],
                    ['fecha' => '30/03/2024', 'evento' => 'Entrega galería', 'detalle' => 'Galería privada disponible']
                ]
            ],
            'FJ-BOD-2024-ALB' => [
                'nombre_curso' => 'Álbum Premium - Boda María & Pedro',
                'estado' => 'En Proceso',
                'carga_horaria' => 0,
                'fecha_finalizacion' => '30/04/2024',
                'nota_final' => '',
                'asistencia' => '',
                'competencias' => [
                    'Álbum 30x30 cm',
                    '60 páginas',
                    'Tapa en cuero italiano',
                    'Caja de presentación'
                ],
                'trayectoria' => [
                    ['fecha' => '05/04/2024', 'evento' => 'Selección de fotos', 'detalle' => 'Cliente seleccionó 80 favoritas'],
                    ['fecha' => '12/04/2024', 'evento' => 'Diseño de álbum', 'detalle' => 'Diagramación en proceso']
                ]
            ]
        ]
    ],
    '28765432' => [
        'nombre_completo' => 'Carlos Rodríguez',
        'cursos' => [
            'FJ-CORP-2024-001' => [
                'nombre_curso' => 'Sesión Corporativa - LinkedIn Profesional',
                'estado' => 'Finalizado',
                'carga_horaria' => 2,
                'fecha_finalizacion' => '20/02/2024',
                'nota_final' => '',
                'asistencia' => '',
                'competencias' => [
                    'Retratos profesionales',
                    'Fondo neutro y corporativo',
                    '25 fotos editadas',
                    'Retoque profesional'
                ],
                'trayectoria' => [
                    ['fecha' => '18/02/2024', 'evento' => 'Sesión en estudio', 'detalle' => 'Diferentes outfits y fondos'],
                    ['fecha' => '20/02/2024', 'evento' => 'Entrega exprés', 'detalle' => 'Selección y edición 48hs']
                ]
            ]
        ]
    ],
    '35678901' => [
        'nombre_completo' => 'Ana Fernández',
        'cursos' => [
            'FJ-FAMI-2024-001' => [
                'nombre_curso' => 'Sesión Familiar - Parque Centenario',
                'estado' => 'Finalizado',
                'carga_horaria' => 3,
                'fecha_finalizacion' => '10/03/2024',
                'nota_final' => '',
                'asistencia' => '',
                'competencias' => [
                    'Sesión al aire libre',
                    'Fotos familiares naturales',
                    'Retratos individuales niños',
                    '120 fotos editadas'
                ],
                'trayectoria' => [
                    ['fecha' => '10/03/2024', 'evento' => 'Sesión familiar', 'detalle' => 'Golden hour en el parque'],
                    ['fecha' => '17/03/2024', 'evento' => 'Galería disponible', 'detalle' => 'Con opción de impresiones']
                ]
            ],
            'FJ-FAMI-2024-002' => [
                'nombre_curso' => 'Book Infantil - Valentina (5 años)',
                'estado' => 'Por Iniciar',
                'carga_horaria' => 2,
                'fecha_finalizacion' => '15/05/2024',
                'nota_final' => '',
                'asistencia' => '',
                'competencias' => [
                    'Book temático princesas',
                    'Vestuario incluido',
                    'Props y decoración',
                    'Sesión en estudio'
                ],
                'trayectoria' => [
                    ['fecha' => '05/05/2024', 'evento' => 'Sesión programada', 'detalle' => 'Cumpleaños de Valentina']
                ]
            ]
        ]
    ],
    '40123456' => [
        'nombre_completo' => 'Empresa Tech Solutions S.A.',
        'cursos' => [
            'FJ-CORP-2024-002' => [
                'nombre_curso' => 'Cobertura Evento Corporativo - Lanzamiento Producto',
                'estado' => 'Finalizado',
                'carga_horaria' => 6,
                'fecha_finalizacion' => '28/03/2024',
                'nota_final' => '',
                'asistencia' => '',
                'competencias' => [
                    'Fotografía de evento corporativo',
                    'Cobertura presentación',
                    'Networking y catering',
                    '300 fotos editadas',
                    'Entrega para redes sociales'
                ],
                'trayectoria' => [
                    ['fecha' => '25/03/2024', 'evento' => 'Reunión previa', 'detalle' => 'Brief y momentos clave'],
                    ['fecha' => '28/03/2024', 'evento' => 'Cobertura evento', 'detalle' => 'Hotel Four Seasons'],
                    ['fecha' => '29/03/2024', 'evento' => 'Entrega urgente 50 fotos', 'detalle' => 'Para comunicación inmediata'],
                    ['fecha' => '02/04/2024', 'evento' => 'Galería completa', 'detalle' => 'Con certificados de autenticidad']
                ]
            ]
        ]
    ]
];

// Función para obtener datos de un cliente por DNI
function obtenerDatosCliente($dni) {
    global $base_de_datos_maestra;

    if (isset($base_de_datos_maestra[$dni])) {
        return $base_de_datos_maestra[$dni];
    }

    return null;
}

// Función para obtener galería específica
function obtenerGaleria($dni, $galeria_id) {
    $datos_cliente = obtenerDatosCliente($dni);

    if ($datos_cliente && isset($datos_cliente['cursos'][$galeria_id])) {
        return $datos_cliente['cursos'][$galeria_id];
    }

    return null;
}

// Función para generar código de validación (para certificados de autenticidad)
function generarCodigoValidacion($dni, $galeria_id) {
    return "VALID-" . strtoupper(substr(md5($dni . $galeria_id), 0, 12));
}
?>
