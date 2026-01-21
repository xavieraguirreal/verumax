<?php
/**
 * LUMEN - Base de datos de portfolios
 * Actualizado: 2025-10-12 12:58:41
 */

$lumen_portfolios = array (
  'fotosjuan' => 
  array (
    'nombre_marca' => 'FotosJuan Photography',
    'nombre_artista' => 'Juan Martínez',
    'tagline' => 'Capturando momentos que permanecen para siempre',
    'biografia' => 'Fotógrafo profesional con más de 10 años de experiencia especializado en bodas, eventos corporativos y retratos. Mi trabajo se caracteriza por capturar la esencia única de cada momento, combinando técnica fotográfica con sensibilidad artística.',
    'email' => 'info@fotosjuan.com',
    'telefono' => '+54 11 5555-1234',
    'ubicacion' => 'Palermo, Buenos Aires, Argentina',
    'redes' => 
    array (
      'instagram' => 'fotosjuan',
      'instagram_url' => 'https://instagram.com/fotosjuan',
      'facebook' => 'fotosjuanphoto',
      'facebook_url' => 'https://facebook.com/fotosjuanphoto',
      'behance' => 'juanmartinez',
      'behance_url' => 'https://behance.net/juanmartinez',
      'whatsapp' => '541155551234',
    ),
    'plantilla' => 'masonry',
    'tema_color' => '#0ea5e9',
    'tema_secundario' => '#8b5cf6',
    'dark_mode_default' => true,
    'marca_agua' => 
    array (
      'activa' => true,
      'tipo' => 'logo',
      'archivo' => 'fotosjuan_watermark.png',
      'texto' => '© FotosJuan Photography',
      'opacidad' => 40,
      'posicion' => 'centro',
      'tamano' => 30,
    ),
    'galerias' => 
    array (
      'bodas' => 
      array (
        'id' => 'bodas',
        'nombre' => 'Bodas',
        'slug' => 'bodas',
        'descripcion' => 'Momentos únicos del día más especial. Cobertura completa desde la ceremonia hasta la fiesta.',
        'icono' => 'heart',
        'color' => '#ec4899',
        'orden' => 1,
        'publica' => true,
        'fecha_creacion' => '2024-03-10',
        'fotos' => 
        array (
          0 => 
          array (
            'id' => 'boda_001',
            'archivo_original' => 'foto-pettine-IfjHaIoAoqE-unsplash.jpg',
            'titulo' => 'Ceremonia Romántica',
            'descripcion' => 'El momento del "sí, acepto"',
            'fecha' => '2024-03-15',
            'orden' => 1,
            'destacada' => true,
            'tags' => 
            array (
              0 => 'boda',
              1 => 'ceremonia',
              2 => 'romantico',
            ),
          ),
          1 => 
          array (
            'id' => 'boda_002',
            'archivo_original' => 'hisu-lee-FTW8ADj5igs-unsplash.jpg',
            'titulo' => 'Entrada de la Novia',
            'descripcion' => 'Momento emotivo',
            'fecha' => '2024-03-15',
            'orden' => 2,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'boda',
              1 => 'novia',
              2 => 'emocion',
            ),
          ),
          2 => 
          array (
            'id' => 'boda_003',
            'archivo_original' => 'jakob-owens-mLIurLmSRAY-unsplash.jpg',
            'titulo' => 'Preparativos de la Novia',
            'descripcion' => 'Momentos antes de la ceremonia',
            'fecha' => '2024-03-15',
            'orden' => 3,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'boda',
              1 => 'preparativos',
              2 => 'novia',
            ),
          ),
          3 => 
          array (
            'id' => 'boda_004',
            'archivo_original' => 'jeremy-wong-weddings-464ps_nOflw-unsplash.jpg',
            'titulo' => 'Primer Baile',
            'descripcion' => 'Inaugurando la pista',
            'fecha' => '2024-03-15',
            'orden' => 4,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'boda',
              1 => 'fiesta',
              2 => 'baile',
            ),
          ),
          4 => 
          array (
            'id' => 'boda_005',
            'archivo_original' => 'jeremy-wong-weddings-K8KiCHh4WU4-unsplash.jpg',
            'titulo' => 'Alegría Compartida',
            'descripcion' => 'Momentos de felicidad',
            'fecha' => '2024-03-15',
            'orden' => 5,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'boda',
              1 => 'fiesta',
              2 => 'alegria',
            ),
          ),
          5 => 
          array (
            'id' => 'boda_006',
            'archivo_original' => 'leonardo-miranda-dvF6s1H1x68-unsplash.jpg',
            'titulo' => 'Sesión de Novios',
            'descripcion' => 'Fotografía artística',
            'fecha' => '2024-03-15',
            'orden' => 6,
            'destacada' => true,
            'tags' => 
            array (
              0 => 'boda',
              1 => 'novios',
              2 => 'artistico',
            ),
          ),
        ),
      ),
      'eventos' => 
      array (
        'id' => 'eventos',
        'nombre' => 'Eventos Corporativos',
        'slug' => 'eventos-corporativos',
        'descripcion' => 'Cobertura profesional para lanzamientos, conferencias y eventos empresariales.',
        'icono' => 'briefcase',
        'color' => '#8b5cf6',
        'orden' => 2,
        'publica' => true,
        'fecha_creacion' => '2024-03-10',
        'fotos' => 
        array (
          0 => 
          array (
            'id' => 'evento_001',
            'archivo_original' => 'al-elmes-ULHxWq8reao-unsplash.jpg',
            'titulo' => 'Conferencia Tecnológica',
            'descripcion' => 'Keynote speaker en acción',
            'fecha' => '2024-03-28',
            'orden' => 1,
            'destacada' => true,
            'tags' => 
            array (
              0 => 'corporativo',
              1 => 'conferencia',
              2 => 'tecnologia',
            ),
          ),
          1 => 
          array (
            'id' => 'evento_002',
            'archivo_original' => 'andrea-mininni-VLlkOJdzLG0-unsplash.jpg',
            'titulo' => 'Networking Empresarial',
            'descripcion' => 'Conexiones profesionales',
            'fecha' => '2024-02-10',
            'orden' => 2,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'corporativo',
              1 => 'networking',
              2 => 'profesional',
            ),
          ),
          2 => 
          array (
            'id' => 'evento_003',
            'archivo_original' => 'jakob-dalbjorn-cuKJre3nyYc-unsplash.jpg',
            'titulo' => 'Presentación de Producto',
            'descripcion' => 'Lanzamiento innovador',
            'fecha' => '2024-03-20',
            'orden' => 3,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'corporativo',
              1 => 'producto',
              2 => 'lanzamiento',
            ),
          ),
          3 => 
          array (
            'id' => 'evento_004',
            'archivo_original' => 'lavi-perchik-FCPV_n0lOxc-unsplash.jpg',
            'titulo' => 'Reunión Estratégica',
            'descripcion' => 'Equipo ejecutivo',
            'fecha' => '2024-03-25',
            'orden' => 4,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'corporativo',
              1 => 'reunion',
              2 => 'estrategia',
            ),
          ),
          4 => 
          array (
            'id' => 'evento_005',
            'archivo_original' => 'quan-nguyen-yDSe7sggb9Q-unsplash.jpg',
            'titulo' => 'Seminario de Capacitación',
            'descripcion' => 'Desarrollo profesional',
            'fecha' => '2024-03-18',
            'orden' => 5,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'corporativo',
              1 => 'capacitacion',
              2 => 'desarrollo',
            ),
          ),
          5 => 
          array (
            'id' => 'evento_006',
            'archivo_original' => 'scott-warman-rrYF1RfotSM-unsplash.jpg',
            'titulo' => 'Panel de Discusión',
            'descripcion' => 'Intercambio de ideas',
            'fecha' => '2024-03-22',
            'orden' => 6,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'corporativo',
              1 => 'panel',
              2 => 'discusion',
            ),
          ),
        ),
      ),
      'retratos' => 
      array (
        'id' => 'retratos',
        'nombre' => 'Retratos',
        'slug' => 'retratos',
        'descripcion' => 'Sesiones personales y corporativas con estilo único. Retratos que cuentan historias.',
        'icono' => 'user-circle',
        'color' => '#f59e0b',
        'orden' => 3,
        'publica' => false,
        'fecha_creacion' => '2024-03-10',
        'fotos' => 
        array (
        ),
      ),
      'familiar' => 
      array (
        'id' => 'familiar',
        'nombre' => 'Sesiones Familiares',
        'slug' => 'sesiones-familiares',
        'descripcion' => 'Momentos naturales y espontáneos al aire libre. Recuerdos que duran para siempre.',
        'icono' => 'users',
        'color' => '#10b981',
        'orden' => 4,
        'publica' => true,
        'fecha_creacion' => '2024-03-10',
        'fotos' => 
        array (
          0 => 
          array (
            'id' => 'familiar_001',
            'archivo_original' => 'justin-simmonds-7FDz8mWnMsw-unsplash.jpg',
            'titulo' => 'Tarde en el Parque',
            'descripcion' => 'Familia disfrutando al aire libre',
            'fecha' => '2024-03-10',
            'orden' => 1,
            'destacada' => true,
            'tags' => 
            array (
              0 => 'familiar',
              1 => 'exterior',
              2 => 'parque',
            ),
          ),
          1 => 
          array (
            'id' => 'familiar_002',
            'archivo_original' => 'justin-simmonds-BURcCv6RkBg-unsplash.jpg',
            'titulo' => 'Risas y Complicidad',
            'descripcion' => 'Momentos genuinos entre hermanos',
            'fecha' => '2024-03-10',
            'orden' => 2,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'familiar',
              1 => 'niños',
              2 => 'exterior',
            ),
          ),
          2 => 
          array (
            'id' => 'familiar_003',
            'archivo_original' => 'justin-simmonds-OQQNi4ClJO0-unsplash.jpg',
            'titulo' => 'Picnic Familiar',
            'descripcion' => 'Tarde de diversión en el parque',
            'fecha' => '2024-03-10',
            'orden' => 3,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'familiar',
              1 => 'exterior',
              2 => 'parque',
            ),
          ),
          3 => 
          array (
            'id' => 'familiar_004',
            'archivo_original' => 'justin-simmonds-Y8WpLUxoCYU-unsplash.jpg',
            'titulo' => 'Retrato Familiar',
            'descripcion' => 'Unión y amor de familia',
            'fecha' => '2024-03-10',
            'orden' => 4,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'familiar',
              1 => 'retrato',
              2 => 'grupo',
            ),
          ),
          4 => 
          array (
            'id' => 'familiar_005',
            'archivo_original' => 'marquise-de-photographie-BGBYxCtjrbM-unsplash.jpg',
            'titulo' => 'Conexión Madre e Hija',
            'descripcion' => 'Momento íntimo y tierno',
            'fecha' => '2024-03-10',
            'orden' => 5,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'familiar',
              1 => 'madre',
              2 => 'hija',
            ),
          ),
          5 => 
          array (
            'id' => 'familiar_006',
            'archivo_original' => 'rodrigo-araya-W-SrAGzCmXg-unsplash.jpg',
            'titulo' => 'Generaciones Unidas',
            'descripcion' => 'Abuelos, padres e hijos',
            'fecha' => '2024-03-10',
            'orden' => 6,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'familiar',
              1 => 'generaciones',
              2 => 'amor',
            ),
          ),
        ),
      ),
      'remeras' => 
      array (
        'id' => 'remeras',
        'nombre' => 'Remeras',
        'slug' => 'remeras',
        'descripcion' => '',
        'icono' => 'folder',
        'color' => '#667eea',
        'orden' => 5,
        'publica' => true,
        'fecha_creacion' => '2025-10-12',
        'fotos' => 
        array (
          0 => 
          array (
            'id' => 'foto_68ebd031d07d7',
            'archivo_original' => 'img-20240510-wa0024_1760284721_2298.jpg',
            'titulo' => 'Img-20240510-wa0024',
            'descripcion' => '',
            'fecha' => '2025-10-12',
            'orden' => 1,
            'destacada' => false,
            'tags' => 
            array (
              0 => 'remeras',
            ),
          ),
        ),
      ),
    ),
    'seo' => 
    array (
      'titulo' => 'FotosJuan Photography - Fotógrafo Profesional en Buenos Aires',
      'descripcion' => 'Portfolio de Juan Martínez, fotógrafo profesional especializado en bodas, eventos y retratos en Buenos Aires. +10 años de experiencia.',
      'keywords' => 'fotografo profesional, bodas buenos aires, eventos corporativos, retratos, fotografia argentina',
    ),
    'configuracion' => 
    array (
      'dominio_personalizado' => 'fotosjuan.com',
      'google_analytics' => '',
      'facebook_pixel' => '',
      'lazy_loading' => true,
      'proteccion_descarga' => true,
      'formulario_contacto' => true,
      'galeria_privada' => false,
      'mostrar_exif' => false,
    ),
    'estadisticas' => 
    array (
      'visitas_totales' => 0,
      'visitas_mes_actual' => 0,
      'galeria_mas_vista' => 'bodas',
    ),
  ),
);

/**
 * Función auxiliar para obtener portfolio por cliente
 */
function obtenerPortfolioLumen($cliente_id) {
    global $lumen_portfolios;

    if (isset($lumen_portfolios[$cliente_id])) {
        return $lumen_portfolios[$cliente_id];
    }

    return null;
}

/**
 * Función para obtener galería específica
 */
function obtenerGaleriaLumen($cliente_id, $galeria_id) {
    $portfolio = obtenerPortfolioLumen($cliente_id);

    if ($portfolio && isset($portfolio['galerias'][$galeria_id])) {
        return $portfolio['galerias'][$galeria_id];
    }

    return null;
}

/**
 * Función para obtener todas las fotos destacadas
 */
function obtenerFotosDestacadasLumen($cliente_id) {
    $portfolio = obtenerPortfolioLumen($cliente_id);
    $destacadas = [];

    if ($portfolio) {
        foreach ($portfolio['galerias'] as $galeria) {
            if ($galeria['publica']) {
                foreach ($galeria['fotos'] as $foto) {
                    if ($foto['destacada']) {
                        $foto['galeria'] = $galeria['nombre'];
                        $foto['galeria_id'] = $galeria['id'];
                        $destacadas[] = $foto;
                    }
                }
            }
        }
    }

    return $destacadas;
}

/**
 * Función para contar total de fotos
 */
function contarFotosLumen($cliente_id) {
    $portfolio = obtenerPortfolioLumen($cliente_id);
    $total = 0;

    if ($portfolio) {
        foreach ($portfolio['galerias'] as $galeria) {
            if ($galeria['publica']) {
                $total += count($galeria['fotos']);
            }
        }
    }

    return $total;
}
?>
