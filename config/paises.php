<?php
/**
 * VERUMAX - Lista de Países Normalizada
 * ======================================
 *
 * Estructura: código ISO 3166-1 alpha-2 => nombre en español
 * Ordenado: Argentina primero (default), luego alfabéticamente por región
 *
 * EJEMPLOS DE USO:
 * ----------------
 *
 * 1. Cargar la lista de países:
 *    $paises = include ROOT_PATH . '/config/paises.php';
 *    // o desde admin/modulos:
 *    $paises = include __DIR__ . '/../../config/paises.php';
 *
 * 2. Generar un <select> HTML:
 *    <select name="pais">
 *        <?php foreach ($paises as $codigo => $nombre): ?>
 *            <option value="<?= $codigo ?>" <?= $codigo === 'AR' ? 'selected' : '' ?>>
 *                <?= htmlspecialchars($nombre) ?>
 *            </option>
 *        <?php endforeach; ?>
 *    </select>
 *
 * 3. Obtener nombre del país desde código:
 *    $codigo = 'AR';
 *    $nombrePais = $paises[$codigo] ?? 'Desconocido';
 *    // Resultado: "Argentina"
 *
 * 4. Buscar código desde nombre:
 *    $nombre = 'Brasil';
 *    $codigo = array_search($nombre, $paises);
 *    // Resultado: "BR"
 *
 * 5. Validar si un código existe:
 *    if (isset($paises[$codigoRecibido])) {
 *        // Código válido
 *    }
 *
 * NOTA: En la base de datos guardar el CÓDIGO (AR, BR, etc.)
 *       y usar esta lista para mostrar el nombre al usuario.
 */

return [
    // Argentina (default para instituciones argentinas)
    'AR' => 'Argentina',

    // América del Sur (alfabético)
    'BO' => 'Bolivia',
    'BR' => 'Brasil',
    'CL' => 'Chile',
    'CO' => 'Colombia',
    'EC' => 'Ecuador',
    'GY' => 'Guyana',
    'PY' => 'Paraguay',
    'PE' => 'Perú',
    'SR' => 'Surinam',
    'UY' => 'Uruguay',
    'VE' => 'Venezuela',

    // América Central y Caribe (alfabético)
    'BZ' => 'Belice',
    'CR' => 'Costa Rica',
    'CU' => 'Cuba',
    'SV' => 'El Salvador',
    'GT' => 'Guatemala',
    'HT' => 'Haití',
    'HN' => 'Honduras',
    'JM' => 'Jamaica',
    'MX' => 'México',
    'NI' => 'Nicaragua',
    'PA' => 'Panamá',
    'PR' => 'Puerto Rico',
    'DO' => 'República Dominicana',
    'TT' => 'Trinidad y Tobago',

    // América del Norte
    'CA' => 'Canadá',
    'US' => 'Estados Unidos',

    // Europa (hispanohablante/lusófono + principales)
    'AD' => 'Andorra',
    'ES' => 'España',
    'PT' => 'Portugal',
];
