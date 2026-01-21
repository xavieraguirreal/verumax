<?php
/**
 * VERUMAX SUPER ADMIN - Autenticación
 *
 * Funciones para gestión de autenticación y 2FA.
 * Usa clase TOTP propia (sin dependencias externas).
 */

namespace VERUMaxAdmin;

require_once VERUMAX_ADMIN_PATH . '/classes/TOTP.php';

class Auth {
    private static ?TOTP $tfa = null;

    /**
     * Obtiene instancia de TOTP
     */
    public static function getTFA(): TOTP {
        if (self::$tfa === null) {
            self::$tfa = new TOTP(TOTP_ISSUER, TOTP_DIGITS, TOTP_PERIOD);
        }
        return self::$tfa;
    }

    /**
     * Valida credenciales de usuario
     */
    public static function validateCredentials(string $username, string $password): ?array {
        $user = Database::queryOne(
            "SELECT * FROM super_admins WHERE username = ? AND activo = 1",
            [$username]
        );

        if (!$user) {
            return null;
        }

        // Verificar si está bloqueado
        if ($user['bloqueado_hasta'] && strtotime($user['bloqueado_hasta']) > time()) {
            return null;
        }

        // Verificar contraseña
        if (!password_verify($password, $user['password_hash'])) {
            // Incrementar intentos fallidos
            self::incrementFailedAttempts($user['id']);
            return null;
        }

        // Resetear intentos fallidos
        self::resetFailedAttempts($user['id']);

        return $user;
    }

    /**
     * Incrementa intentos fallidos y bloquea si es necesario
     */
    private static function incrementFailedAttempts(int $userId): void {
        $bloqueado_hasta = null;

        // Obtener intentos actuales
        $user = Database::queryOne(
            "SELECT intentos_fallidos FROM super_admins WHERE id = ?",
            [$userId]
        );

        $intentos = ($user['intentos_fallidos'] ?? 0) + 1;

        if ($intentos >= LOGIN_MAX_ATTEMPTS) {
            $bloqueado_hasta = date('Y-m-d H:i:s', strtotime('+' . LOGIN_LOCKOUT_MINUTES . ' minutes'));
        }

        Database::execute(
            "UPDATE super_admins SET intentos_fallidos = ?, bloqueado_hasta = ? WHERE id = ?",
            [$intentos, $bloqueado_hasta, $userId]
        );
    }

    /**
     * Resetea intentos fallidos
     */
    private static function resetFailedAttempts(int $userId): void {
        Database::execute(
            "UPDATE super_admins SET intentos_fallidos = 0, bloqueado_hasta = NULL WHERE id = ?",
            [$userId]
        );
    }

    /**
     * Valida código TOTP
     */
    public static function validateTOTP(string $secret, string $code): bool {
        return self::getTFA()->verifyCode($secret, $code);
    }

    /**
     * Genera nuevo secret para 2FA
     */
    public static function generateSecret(): string {
        return self::getTFA()->createSecret();
    }

    /**
     * Genera URL para código QR
     */
    public static function getQRCodeUrl(string $label, string $secret): string {
        return self::getTFA()->getQRCodeImageAsDataUri($label, $secret);
    }

    /**
     * Registra acceso exitoso
     */
    public static function logSuccessfulLogin(int $userId): void {
        Database::execute(
            "UPDATE super_admins SET ultimo_acceso = NOW() WHERE id = ?",
            [$userId]
        );
    }

    /**
     * Crea sesión de usuario
     */
    public static function createSession(array $user, bool $twoFactorVerified = false): void {
        $_SESSION['superadmin_id'] = $user['id'];
        $_SESSION['superadmin_username'] = $user['username'];
        $_SESSION['superadmin_nombre'] = $user['nombre'];
        $_SESSION['superadmin_rol'] = $user['rol'];
        $_SESSION['superadmin_2fa_verified'] = $twoFactorVerified;
        $_SESSION['superadmin_pending_2fa'] = !$twoFactorVerified;

        if ($twoFactorVerified) {
            self::logSuccessfulLogin($user['id']);
        }
    }

    /**
     * Marca 2FA como verificado
     */
    public static function mark2FAVerified(): void {
        $_SESSION['superadmin_2fa_verified'] = true;
        $_SESSION['superadmin_pending_2fa'] = false;

        if (isset($_SESSION['superadmin_id'])) {
            self::logSuccessfulLogin($_SESSION['superadmin_id']);
        }
    }

    /**
     * Destruye sesión
     */
    public static function logout(): void {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Genera hash de contraseña seguro
     */
    public static function hashPassword(string $password): string {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    /**
     * Genera contraseña aleatoria
     */
    public static function generatePassword(int $length = 12): string {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }
}
