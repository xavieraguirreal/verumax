<?php
/**
 * Test temporal de búsqueda - ELIMINAR después de debug
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/certificatum/config.php';
require_once __DIR__ . '/src/VERUMax/Services/MemberService.php';
require_once __DIR__ . '/src/VERUMax/Services/DatabaseService.php';

use VERUMax\Services\MemberService;
use VERUMax\Services\DatabaseService;

$buscar = $_GET['q'] ?? '21090771';
$id_instancia = (int)($_GET['inst'] ?? 1);

echo "<h2>Test de búsqueda MemberService</h2>";
echo "<p><strong>Buscando:</strong> '$buscar'</p>";
echo "<p><strong>Instancia:</strong> $id_instancia</p>";
echo "<hr>";

// Test 0: Verificar conexión a base de datos
echo "<h3>0. Verificando conexión a BD</h3>";
try {
    $conn = DatabaseService::get('nexus');
    echo "<p style='color:green'>✓ Conexión a 'nexus' OK</p>";

    // Mostrar qué base de datos estamos usando
    $stmt = $conn->query("SELECT DATABASE() as db");
    $db = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Base de datos actual:</strong> " . htmlspecialchars($db['db'] ?? 'NULL') . "</p>";

    // Contar total de miembros para instancia 1
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM miembros WHERE id_instancia = ?");
    $stmt->execute([$id_instancia]);
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Total miembros en instancia $id_instancia:</strong> " . ($count['total'] ?? '0') . "</p>";

    // Buscar directamente con SQL
    echo "<h3>1. Query SQL directa</h3>";
    $sql = "SELECT id_miembro, identificador_principal, nombre, apellido, estado
            FROM miembros
            WHERE id_instancia = ?
            AND identificador_principal LIKE ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id_instancia, "%$buscar%"]);
    $directos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<p style='color:" . (count($directos) > 0 ? 'green' : 'red') . "'>Query directa encontró: " . count($directos) . " resultados</p>";

    if (count($directos) > 0) {
        echo "<pre>" . print_r($directos, true) . "</pre>";
    }

} catch (Exception $e) {
    echo "<p style='color:red'>ERROR conexión: " . htmlspecialchars($e->getMessage()) . "</p>";
}

echo "<hr>";

// Test 2: MemberService::getConInscripciones
try {
    echo "<h3>2. MemberService::getConInscripciones()</h3>";
    $resultados = MemberService::getConInscripciones($id_instancia, $buscar);
    echo "<p style='color:" . (count($resultados) > 0 ? 'green' : 'red') . "'>Encontrados: " . count($resultados) . " resultados</p>";

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
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";

// Test 3: MemberService::getAll
try {
    echo "<h3>3. MemberService::getAll() con filtro</h3>";
    $resultados2 = MemberService::getAll($id_instancia, ['buscar' => $buscar]);
    echo "<p style='color:" . (count($resultados2) > 0 ? 'green' : 'red') . "'>Encontrados: " . count($resultados2) . " resultados</p>";

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
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

echo "<hr>";
echo "<p><small>Archivo temporal de debug - eliminar después de uso</small></p>";
