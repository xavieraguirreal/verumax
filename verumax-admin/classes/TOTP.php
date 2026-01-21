<?php
/**
 * VERUMAX SUPER ADMIN - TOTP (Time-based One-Time Password)
 *
 * Implementación propia sin dependencias externas.
 * Compatible con Google Authenticator, Authy, etc.
 */

namespace VERUMaxAdmin;

class TOTP {
    private string $issuer;
    private int $digits;
    private int $period;

    public function __construct(string $issuer = 'VERUMax', int $digits = 6, int $period = 30) {
        $this->issuer = $issuer;
        $this->digits = $digits;
        $this->period = $period;
    }

    /**
     * Genera un secret aleatorio en Base32
     */
    public function createSecret(int $length = 16): string {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Verifica un código TOTP
     */
    public function verifyCode(string $secret, string $code, int $discrepancy = 1): bool {
        $currentTime = floor(time() / $this->period);

        // Verificar código actual y códigos adyacentes (por desfase de tiempo)
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            $calculatedCode = $this->getCode($secret, $currentTime + $i);
            if (hash_equals($calculatedCode, $code)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Genera el código TOTP para un momento dado
     */
    public function getCode(string $secret, ?int $timeSlice = null): string {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / $this->period);
        }

        $secretKey = $this->base32Decode($secret);

        // Convertir time slice a bytes (8 bytes, big-endian)
        $time = pack('N*', 0, $timeSlice);

        // Calcular HMAC-SHA1
        $hash = hash_hmac('sha1', $time, $secretKey, true);

        // Obtener offset dinámico
        $offset = ord($hash[19]) & 0x0F;

        // Extraer 4 bytes y convertir a número
        $binary = (ord($hash[$offset]) & 0x7F) << 24
                | (ord($hash[$offset + 1]) & 0xFF) << 16
                | (ord($hash[$offset + 2]) & 0xFF) << 8
                | (ord($hash[$offset + 3]) & 0xFF);

        // Obtener código de N dígitos
        $otp = $binary % pow(10, $this->digits);

        return str_pad((string)$otp, $this->digits, '0', STR_PAD_LEFT);
    }

    /**
     * Genera URL para código QR (compatible con Google Authenticator)
     */
    public function getQRCodeUrl(string $label, string $secret): string {
        $params = [
            'secret' => $secret,
            'issuer' => $this->issuer,
            'algorithm' => 'SHA1',
            'digits' => $this->digits,
            'period' => $this->period,
        ];

        $otpauth = 'otpauth://totp/' . rawurlencode($this->issuer . ':' . $label)
                 . '?' . http_build_query($params);

        // Usar API de QRServer (gratuito y funcional)
        return 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data='
             . urlencode($otpauth);
    }

    /**
     * Genera QR como Data URI (usando servicio externo)
     */
    public function getQRCodeImageAsDataUri(string $label, string $secret): string {
        return $this->getQRCodeUrl($label, $secret);
    }

    /**
     * Decodifica Base32 a binario
     */
    private function base32Decode(string $input): string {
        $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input = strtoupper($input);
        $input = str_replace('=', '', $input);

        $buffer = 0;
        $bitsLeft = 0;
        $output = '';

        for ($i = 0; $i < strlen($input); $i++) {
            $val = strpos($map, $input[$i]);
            if ($val === false) {
                continue;
            }

            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;

            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $output .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }

        return $output;
    }
}
