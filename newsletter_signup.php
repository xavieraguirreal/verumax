<?php
/**
 * VERUMax - Newsletter Signup (Modo Mantenimiento)
 * Captura emails de usuarios interesados en el lanzamiento
 */

// Verificar que sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: maintenance.php?error=method#newsletter');
    exit;
}

// Verificar que tenga email
if (!isset($_POST['email']) || empty($_POST['email'])) {
    header('Location: maintenance.php?error=empty#newsletter');
    exit;
}

// Sanitizar y validar email
$email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: maintenance.php?error=invalid#newsletter');
    exit;
}

// Archivo donde guardar los emails
$file_path = 'newsletter_emails.txt';

// Verificar si el email ya existe (evitar duplicados)
if (file_exists($file_path)) {
    $existing_emails = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($existing_emails as $line) {
        // Formato: timestamp|email
        $parts = explode('|', $line);
        if (isset($parts[1]) && $parts[1] === $email) {
            // Ya existe, pero igualmente confirmar (UX positiva)
            header('Location: maintenance.php?success=1#newsletter');
            exit;
        }
    }
}

// Guardar email con timestamp
$timestamp = date('Y-m-d H:i:s');
$line = $timestamp . '|' . $email . PHP_EOL;

$result = file_put_contents($file_path, $line, FILE_APPEND | LOCK_EX);

if ($result === false) {
    header('Location: maintenance.php?error=save#newsletter');
    exit;
}

// Éxito - redirigir con mensaje de confirmación
header('Location: maintenance.php?success=1#newsletter');
exit;
?>
