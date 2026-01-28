<?php
/**
 * Test temporal de búsqueda - ELIMINAR después de debug
 */
require_once __DIR__ . '/certificatum/config.php';
require_once __DIR__ . '/src/VERUMax/Services/MemberService.php';

use VERUMax\Services\MemberService;

$buscar = $_GET['q'] ?? '21090771';
$id_instancia = (int)($_GET['inst'] ?? 1);

echo "<h2>Test de búsqueda MemberService</h2>";
echo "<p><strong>Buscando:</strong> '$buscar'</p>";
echo "<p><strong>Instancia:</strong> $id_instancia</p>";
echo "<hr>";

try {
    echo "<h3>1. Probando getConInscripciones()</h3>";
    $resultados = MemberService::getConInscripciones($id_instancia, $buscar);
    echo "<p style='color:green'>Encontrados: " . count($resultados) . " resultados</p>";

    if (count($resultados) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>DNI</th><th>Nombre</th><th>Estado</th><th>Rol</th></tr>";
        foreach ($resultados as $r) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($r['id_miembro'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($r['dni'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars(($r['nombre'] ?? '') . ' ' . ($r['apellido'] ?? '')) . "</td>";
            echo "<td>" . htmlspecialchars($r['estado'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($r['tipo_miembro'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>ERROR en getConInscripciones: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";

try {
    echo "<h3>2. Probando getAll() con filtro</h3>";
    $resultados2 = MemberService::getAll($id_instancia, ['buscar' => $buscar]);
    echo "<p style='color:green'>Encontrados: " . count($resultados2) . " resultados</p>";

    if (count($resultados2) > 0) {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr><th>ID</th><th>DNI</th><th>Nombre</th><th>Estado</th></tr>";
        foreach ($resultados2 as $r) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($r['id_miembro'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($r['identificador_principal'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars(($r['nombre'] ?? '') . ' ' . ($r['apellido'] ?? '')) . "</td>";
            echo "<td>" . htmlspecialchars($r['estado'] ?? '') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>ERROR en getAll: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";
echo "<p><small>Archivo temporal de debug - eliminar después de uso</small></p>";
