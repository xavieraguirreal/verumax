<?php
/**
 * VCardService
 *
 * Servicio para generar archivos vCard (.vcf) para descargar contactos.
 * Permite a los usuarios agregar instituciones/personas a sus contactos
 * desde dispositivos móviles (iOS, Android) y de escritorio (Outlook, etc.).
 *
 * Formato: vCard 3.0 (RFC 2426) - máxima compatibilidad
 *
 * @package VERUMax\Services
 */

namespace VERUMax\Services;

class VCardService
{
    /**
     * Versión de vCard utilizada
     */
    private const VCARD_VERSION = '3.0';

    /**
     * Genera contenido vCard para una institución
     *
     * @param array $data Datos de la institución
     *   - nombre: string (requerido) - Nombre de la institución
     *   - nombre_completo: string - Nombre completo/descripción
     *   - telefono: string - Teléfono de contacto
     *   - email: string - Email de contacto
     *   - direccion: string - Dirección física
     *   - sitio_web: string - URL del sitio web
     *   - logo_url: string - URL del logo (para foto de contacto)
     *   - redes_sociales: array - Redes sociales (opcional)
     * @return string Contenido del archivo vCard
     */
    public static function generateForInstitution(array $data): string
    {
        $lines = [];

        // Header
        $lines[] = 'BEGIN:VCARD';
        $lines[] = 'VERSION:' . self::VCARD_VERSION;

        // Nombre formateado (FN es requerido en vCard 3.0)
        $nombre = $data['nombre'] ?? 'Sin nombre';
        $nombreCompleto = $data['nombre_completo'] ?? $nombre;
        $lines[] = 'FN:' . self::escape($nombreCompleto);

        // Nombre estructurado (N) - para instituciones usamos el nombre como "apellido"
        $lines[] = 'N:' . self::escape($nombre) . ';;;;';

        // Organización
        $lines[] = 'ORG:' . self::escape($nombreCompleto);

        // Teléfono
        if (!empty($data['telefono'])) {
            $lines[] = 'TEL;TYPE=WORK,VOICE:' . self::escape($data['telefono']);
        }

        // Email
        if (!empty($data['email'])) {
            $lines[] = 'EMAIL;TYPE=WORK,INTERNET:' . self::escape($data['email']);
        }

        // Dirección
        if (!empty($data['direccion'])) {
            // ADR: PO Box;Extended;Street;City;Region;PostalCode;Country
            $lines[] = 'ADR;TYPE=WORK:;;' . self::escape($data['direccion']) . ';;;;';
        }

        // Sitio web
        if (!empty($data['sitio_web'])) {
            $lines[] = 'URL:' . self::escape($data['sitio_web']);
        }

        // URL de landing de Verumax (si existe slug)
        if (!empty($data['slug'])) {
            $landingUrl = 'https://' . $data['slug'] . '.verumax.com';
            $lines[] = 'URL;TYPE=PREF:' . self::escape($landingUrl);
        }

        // Redes sociales como URLs adicionales (X-SOCIALPROFILE para compatibilidad)
        if (!empty($data['redes_sociales']) && is_array($data['redes_sociales'])) {
            foreach ($data['redes_sociales'] as $red => $url) {
                if (!empty($url)) {
                    $redUpper = strtoupper($red);
                    $lines[] = 'X-SOCIALPROFILE;TYPE=' . $redUpper . ':' . self::escape($url);
                }
            }
        }

        // Nota/Descripción
        if (!empty($data['mision'])) {
            $lines[] = 'NOTE:' . self::escape($data['mision']);
        }

        // Categoría
        $lines[] = 'CATEGORIES:VERUMax,Instituciones';

        // Fecha de generación (revisión)
        $lines[] = 'REV:' . gmdate('Ymd\THis\Z');

        // Identificador único
        $uid = 'verumax-' . ($data['slug'] ?? uniqid()) . '@verumax.com';
        $lines[] = 'UID:' . $uid;

        // Footer
        $lines[] = 'END:VCARD';

        return implode("\r\n", $lines);
    }

    /**
     * Genera contenido vCard para una persona (tarjeta personal)
     *
     * @param array $data Datos de la persona
     *   - nombre: string (requerido) - Nombre completo
     *   - cargo: string - Cargo/Título
     *   - organizacion: string - Nombre de la organización
     *   - telefono: string - Teléfono
     *   - email: string - Email
     *   - direccion: string - Dirección
     *   - sitio_web: string - URL personal o de la organización
     *   - redes_sociales: array - Redes sociales
     * @return string Contenido del archivo vCard
     */
    public static function generateForPerson(array $data): string
    {
        $lines = [];

        // Header
        $lines[] = 'BEGIN:VCARD';
        $lines[] = 'VERSION:' . self::VCARD_VERSION;

        // Nombre formateado
        $nombre = $data['nombre'] ?? 'Sin nombre';
        $lines[] = 'FN:' . self::escape($nombre);

        // Nombre estructurado - intentar separar nombre y apellido
        $partes = self::parseNombre($nombre);
        $lines[] = 'N:' . self::escape($partes['apellido']) . ';' .
                   self::escape($partes['nombre']) . ';;;';

        // Organización y cargo
        if (!empty($data['organizacion'])) {
            $lines[] = 'ORG:' . self::escape($data['organizacion']);
        }

        if (!empty($data['cargo'])) {
            $lines[] = 'TITLE:' . self::escape($data['cargo']);
        }

        // Teléfono
        if (!empty($data['telefono'])) {
            $lines[] = 'TEL;TYPE=CELL,VOICE:' . self::escape($data['telefono']);
        }

        // Email
        if (!empty($data['email'])) {
            $lines[] = 'EMAIL;TYPE=INTERNET:' . self::escape($data['email']);
        }

        // Dirección
        if (!empty($data['direccion'])) {
            $lines[] = 'ADR:;;' . self::escape($data['direccion']) . ';;;;';
        }

        // Sitio web
        if (!empty($data['sitio_web'])) {
            $lines[] = 'URL:' . self::escape($data['sitio_web']);
        }

        // Redes sociales
        if (!empty($data['redes_sociales']) && is_array($data['redes_sociales'])) {
            foreach ($data['redes_sociales'] as $red => $url) {
                if (!empty($url)) {
                    $redUpper = strtoupper($red);
                    $lines[] = 'X-SOCIALPROFILE;TYPE=' . $redUpper . ':' . self::escape($url);
                }
            }
        }

        // Categoría
        $lines[] = 'CATEGORIES:VERUMax,Contactos';

        // Fecha de generación
        $lines[] = 'REV:' . gmdate('Ymd\THis\Z');

        // Identificador único
        $uid = 'verumax-persona-' . uniqid() . '@verumax.com';
        $lines[] = 'UID:' . $uid;

        // Footer
        $lines[] = 'END:VCARD';

        return implode("\r\n", $lines);
    }

    /**
     * Genera vCard desde configuración de instancia de Identitas
     *
     * @param array $instanceConfig Configuración de instancia desde getInstanceConfig()
     * @return string Contenido del archivo vCard
     */
    public static function generateFromInstance(array $instanceConfig): string
    {
        // Extraer redes sociales del JSON si existe
        $redesSociales = [];
        if (!empty($instanceConfig['redes_sociales'])) {
            if (is_string($instanceConfig['redes_sociales'])) {
                $redesSociales = json_decode($instanceConfig['redes_sociales'], true) ?: [];
            } else {
                $redesSociales = $instanceConfig['redes_sociales'];
            }
        }

        // Extraer config adicional si existe
        $config = [];
        if (!empty($instanceConfig['config'])) {
            if (is_string($instanceConfig['config'])) {
                $config = json_decode($instanceConfig['config'], true) ?: [];
            } else {
                $config = $instanceConfig['config'];
            }
        }

        $data = [
            'nombre' => $instanceConfig['nombre'] ?? '',
            'nombre_completo' => $instanceConfig['nombre_completo'] ?? $instanceConfig['nombre'] ?? '',
            'slug' => $instanceConfig['slug'] ?? '',
            'telefono' => $instanceConfig['telefono'] ?? $config['telefono'] ?? '',
            'email' => $instanceConfig['email_contacto'] ?? $config['email_contacto'] ?? '',
            'direccion' => $instanceConfig['direccion'] ?? $config['direccion'] ?? '',
            'sitio_web' => $config['sitio_web_oficial'] ?? '',
            'mision' => $config['mision'] ?? '',
            'redes_sociales' => $redesSociales,
        ];

        return self::generateForInstitution($data);
    }

    /**
     * Envía headers HTTP para descarga de vCard
     *
     * @param string $filename Nombre del archivo (sin extensión)
     */
    public static function sendDownloadHeaders(string $filename): void
    {
        $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $filename);

        header('Content-Type: text/vcard; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $safeFilename . '.vcf"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    /**
     * Escapa caracteres especiales para vCard
     *
     * @param string $value Valor a escapar
     * @return string Valor escapado
     */
    private static function escape(string $value): string
    {
        // Escapar caracteres especiales según RFC 2426
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace(',', '\\,', $value);
        $value = str_replace(';', '\\;', $value);
        $value = str_replace("\n", '\\n', $value);
        $value = str_replace("\r", '', $value);

        return $value;
    }

    /**
     * Intenta separar nombre y apellido de un nombre completo
     *
     * @param string $nombreCompleto Nombre completo
     * @return array ['nombre' => string, 'apellido' => string]
     */
    private static function parseNombre(string $nombreCompleto): array
    {
        $partes = explode(' ', trim($nombreCompleto));

        if (count($partes) === 1) {
            return ['nombre' => $partes[0], 'apellido' => ''];
        }

        if (count($partes) === 2) {
            return ['nombre' => $partes[0], 'apellido' => $partes[1]];
        }

        // Para nombres con más de 2 partes, asumimos que el último es el apellido
        $apellido = array_pop($partes);
        $nombre = implode(' ', $partes);

        return ['nombre' => $nombre, 'apellido' => $apellido];
    }
}
