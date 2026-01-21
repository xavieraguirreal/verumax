<?php
// Debug de login - ELIMINAR DESPUÉS DE USAR
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/config.php';

use VERUMaxAdmin\Database;

echo "<h2>Debug Login</h2>";

// 1. Verificar conexión
echo "<h3>1. Conexión a BD:</h3>";
try {
    $test = Database::query("SELECT 1");
    echo "OK - Conectado<br>";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "<br>";
}

// 2. Verificar usuario
echo "<h3>2. Datos del usuario 'admin':</h3>";
$user = Database::queryOne("SELECT * FROM super_admins WHERE username = 'admin'");
if ($user) {
    echo "ID: " . $user['id'] . "<br>";
    echo "Username: " . $user['username'] . "<br>";
    echo "Activo: " . $user['activo'] . "<br>";
    echo "Bloqueado hasta: " . ($user['bloqueado_hasta'] ?? 'NULL') . "<br>";
    echo "Hash guardado: " . $user['password_hash'] . "<br>";
} else {
    echo "Usuario NO encontrado<br>";
}

// 3. Verificar password
echo "<h3>3. Verificación de password:</h3>";
$password = 'VERUMax2026!';
echo "Password a verificar: " . $password . "<br>";

if ($user) {
    $result = password_verify($password, $user['password_hash']);
    echo "password_verify() resultado: " . ($result ? 'TRUE' : 'FALSE') . "<br>";

    // Generar nuevo hash para comparar
    $nuevo_hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    echo "<br>Nuevo hash generado: " . $nuevo_hash . "<br>";
    echo "Verificación con nuevo hash: " . (password_verify($password, $nuevo_hash) ? 'TRUE' : 'FALSE') . "<br>";
}

// 4. Actualizar directamente
echo "<h3>4. Actualizar password ahora:</h3>";
if (isset($_GET['fix'])) {
    $nuevo_hash = password_hash('VERUMax2026!', PASSWORD_BCRYPT, ['cost' => 12]);
    Database::execute(
        "UPDATE super_admins SET password_hash = ?, activo = 1, bloqueado_hasta = NULL, intentos_fallidos = 0 WHERE username = 'admin'",
        [$nuevo_hash]
    );
    echo "Password actualizado! <a href='login.php'>Ir al login</a>";
} else {
    echo "<a href='?fix=1'>Click aquí para actualizar password automáticamente</a>";
}
