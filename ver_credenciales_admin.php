<?php
/**
 * Ver credenciales de admin de SAJuR
 */
require_once __DIR__ . '/env_loader.php';
use VERUMax\Services\DatabaseService;

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Credenciales Admin</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; }
        h2 { color: #2E7D32; }
        .cred-box { background: #E8F5E9; padding: 20px; border-radius: 5px; margin: 20px 0; border: 2px solid #2E7D32; }
        .cred-box strong { color: #1B5E20; font-size: 16px; }
        .value { font-size: 18px; color: #1B5E20; font-weight: bold; margin: 10px 0; }
        .warning { background: #FFF3E0; padding: 15px; border-radius: 5px; margin: 20px 0; border: 2px solid #F57C00; }
        .delete-btn { background: #C62828; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin-top: 20px; }
        .delete-btn:hover { background: #B71C1C; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔐 Credenciales Admin - SAJuR</h1>

        <?php
        try {
            $pdo = DatabaseService::get('general');
            $stmt = $pdo->query("SELECT admin_usuario, admin_email FROM instances WHERE slug = 'sajur'");
            $admin = $stmt->fetch();

            if ($admin) {
                echo '<div class="cred-box">';
                echo '<strong>Usuario:</strong>';
                echo '<div class="value">' . htmlspecialchars($admin['admin_usuario']) . '</div>';
                echo '<strong>Email:</strong>';
                echo '<div class="value">' . htmlspecialchars($admin['admin_email']) . '</div>';
                echo '</div>';

                echo '<div class="warning">';
                echo '<strong>⚠️ Nota de Seguridad:</strong><br>';
                echo 'La contraseña está hasheada en la base de datos (bcrypt).<br>';
                echo 'Si no recuerdas la contraseña, deberás resetearla.';
                echo '</div>';

                echo '<h2>¿Necesitas resetear la contraseña?</h2>';
                echo '<p>Puedes crear una nueva contraseña hasheada con el siguiente código:</p>';
                echo '<pre style="background: #263238; color: #A5D6A7; padding: 15px; border-radius: 5px;">
// Ejemplo para resetear contraseña
$nueva_password = "tu_nueva_contraseña";
$hash = password_hash($nueva_password, PASSWORD_DEFAULT);

// Luego actualizar en la BD:
// UPDATE instances SET admin_password = \'' . $hash . '\' WHERE slug = \'sajur\'
</pre>';

            } else {
                echo '<p style="color: red;">No se encontró el admin de SAJuR</p>';
            }
        } catch (Exception $e) {
            echo '<p style="color: red;">Error: ' . $e->getMessage() . '</p>';
        }
        ?>

        <h2>🔄 Resetear Contraseña</h2>
        <form method="POST" onsubmit="return confirm('¿Estás seguro de resetear la contraseña?');">
            <label><strong>Nueva contraseña:</strong></label><br>
            <input type="password" name="nueva_password" required style="width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px;">
            <br>
            <button type="submit" name="resetear" style="background: #2E7D32; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
                Resetear Contraseña
            </button>
        </form>

        <?php
        if (isset($_POST['resetear']) && isset($_POST['nueva_password'])) {
            try {
                $nueva_password = $_POST['nueva_password'];
                $hash = password_hash($nueva_password, PASSWORD_DEFAULT);

                $pdo = DatabaseService::get('general');
                $stmt = $pdo->prepare("UPDATE instances SET admin_password = :hash WHERE slug = 'sajur'");
                $stmt->execute(['hash' => $hash]);

                echo '<div style="background: #E8F5E9; padding: 20px; border-radius: 5px; margin: 20px 0; border: 2px solid #2E7D32;">';
                echo '<strong>✅ Contraseña actualizada exitosamente</strong><br><br>';
                echo 'Usuario: <strong>' . htmlspecialchars($admin['admin_usuario']) . '</strong><br>';
                echo 'Nueva contraseña: <strong>' . htmlspecialchars($nueva_password) . '</strong>';
                echo '</div>';

            } catch (Exception $e) {
                echo '<div style="background: #FFEBEE; padding: 20px; border-radius: 5px; margin: 20px 0; border: 2px solid #C62828;">';
                echo '<strong>❌ Error:</strong> ' . $e->getMessage();
                echo '</div>';
            }
        }
        ?>

        <div style="margin-top: 30px; padding: 15px; background: #FFEBEE; border-radius: 5px;">
            <strong>⚠️ IMPORTANTE:</strong><br>
            Elimina este archivo después de usarlo por seguridad:<br>
            <code>E:\appVerumax\ver_credenciales_admin.php</code>
        </div>
    </div>
</body>
</html>
