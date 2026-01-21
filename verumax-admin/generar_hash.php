<?php
// Archivo temporal para generar hash de contraseña
// ELIMINAR DESPUÉS DE USAR

$password = 'VERUMax2026!';
$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

echo "Password: " . $password . "<br>";
echo "Hash: " . $hash . "<br><br>";
echo "SQL para ejecutar:<br>";
echo "<code>UPDATE super_admins SET password_hash = '" . $hash . "' WHERE username = 'admin';</code>";
