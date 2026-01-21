# Sistema de Gestión de Asistencias - SAJUR

Sistema completo para registrar y gestionar asistencias a formaciones de la Sociedad Argentina de Justicia Restaurativa.

## 📋 Características

### Formulario Público de Asistencia
- ✅ Registro simple con validación en tiempo real
- ✅ Verificación de DNI duplicado vía AJAX
- ✅ Validación de ventana de tiempo (desde inicio hasta 1 hora después del fin)
- ✅ Campos en MAYÚSCULAS automáticamente (nombres y apellidos)
- ✅ Modal de confirmación de datos antes de enviar
- ✅ Email de confirmación automático
- ✅ Responsive design moderno
- ✅ Prevención de registros duplicados

### Gestor Administrativo
- ✅ Generador de enlaces de asistencia por formación
- ✅ Visualización de asistencias por formación
- ✅ Estadísticas en tiempo real
- ✅ Exportación a CSV con UTF-8 BOM
- ✅ Copia rápida de enlaces al portapapeles
- ✅ Interfaz intuitiva y moderna
- ✅ Indicadores visuales de estado

## 🗂️ Estructura de Archivos

```
appAsistenciaSajur/
├── config.php                    # Configuración y funciones auxiliares
├── asistencia.php               # Formulario público de registro
├── gestion_asistencias.php      # Panel administrativo
├── sql_asistencias.sql          # Script SQL para crear tabla
└── README.md                    # Esta documentación
```

## 📊 Base de Datos

### Tabla: `asistencias_formaciones`

```sql
CREATE TABLE asistencias_formaciones (
    id_asistencia INT AUTO_INCREMENT PRIMARY KEY,
    id_formacion INT NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    dni VARCHAR(50) NOT NULL,
    correo_electronico VARCHAR(150) NOT NULL,
    ip_registro VARCHAR(50),
    user_agent TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_formacion (id_formacion),
    INDEX idx_dni_formacion (dni, id_formacion),
    UNIQUE KEY unique_asistencia (dni, id_formacion),
    FOREIGN KEY (id_formacion) REFERENCES formaciones(id_formacion) ON DELETE CASCADE
);
```

## 🚀 Instalación

### 1. Ejecutar Script SQL

Ejecuta el contenido de `sql_asistencias.sql` en phpMyAdmin:

```sql
-- Copiar y pegar el contenido de sql_asistencias.sql
```

### 2. Verificar Configuración

El archivo `config.php` reutiliza la configuración de SAJUR Formación. Verifica que las rutas sean correctas:

```php
// Debe apuntar a la configuración de SAJUR
require_once __DIR__ . '/../appSajur/formacion/config.php';
```

### 3. Configurar URLs

Edita `config.php` para actualizar la URL base:

```php
define('ASISTENCIAS_BASE_URL', 'https://www.sajur.org/asistencias/');
```

## 📤 Archivos para Subir con Filezilla

Sube estos archivos a la carpeta `/asistencias/` en el servidor remoto:

1. **config.php**
2. **asistencia.php**
3. **gestion_asistencias.php**

## 🔐 Seguridad Implementada

- ✅ Sanitización de datos de entrada
- ✅ Validación en cliente y servidor
- ✅ Protección contra inyección SQL (PDO preparado)
- ✅ Prevención de duplicados con UNIQUE KEY
- ✅ Validación de ventana de tiempo
- ✅ Registro de IP y User Agent para auditoría
- ✅ Validación de formato de email
- ✅ Limitación de caracteres especiales en nombres

## 📧 Email de Confirmación

El sistema envía automáticamente un email de confirmación con:

- ✅ Datos del participante
- ✅ Detalles de la formación
- ✅ Fecha y hora de registro
- ✅ Información de contacto para correcciones

Configuración SMTP reutilizada de SAJUR Formación:
- Host: vps-5361869-x.dattaweb.com
- Puerto: 465
- Usuario: formacion@sajur.org

## 🕐 Ventana de Tiempo para Registro

El sistema permite registrar asistencia:

- **Desde:** Hora de inicio de la formación
- **Hasta:** 1 hora después de la hora de fin

Esto se configura en `config.php`:

```php
define('MINUTOS_ANTES_INICIO', 0);
define('HORAS_DESPUES_FIN', 1);
```

## 📊 Uso del Gestor Administrativo

### Generar Enlaces de Asistencia

1. Accede a `gestion_asistencias.php`
2. Verás todas las formaciones disponibles
3. Haz clic en el enlace para copiarlo al portapapeles
4. Comparte el enlace con los participantes

### Ver Asistencias Registradas

1. Haz clic en "Ver Asistencias" de una formación
2. Verás la lista completa de participantes
3. Puedes exportar a CSV para análisis externo

### Exportar a CSV

1. En la vista de asistencias, haz clic en "Exportar CSV"
2. El archivo se descargará automáticamente
3. Formato: UTF-8 con BOM (compatible con Excel)

## 🔗 Ejemplo de Enlace de Asistencia

```
https://www.sajur.org/asistencias/asistencia.php?formacion=FOR-141125-789
```

Donde `FOR-141125-789` es el código único de la formación.

## 📱 Responsive Design

El sistema es completamente responsive y funciona en:

- ✅ Desktop (1920px+)
- ✅ Laptop (1366px)
- ✅ Tablet (768px)
- ✅ Móvil (375px)

## 🎨 Tecnologías Utilizadas

- **Backend:** PHP 7.4+ con PDO
- **Base de Datos:** MySQL 8.0+
- **Email:** PHPMailer 6.x
- **Frontend:** HTML5, CSS3, JavaScript (vanilla)
- **Estilos:** Tailwind CSS 3.x vía CDN
- **Iconos:** Font Awesome 6.x
- **Fuentes:** Inter (Google Fonts)

## 🔧 Funciones Principales del Config

### `verificarDisponibilidadAsistencia($formacion)`
Verifica si el registro está disponible según la fecha/hora actual.

### `verificarAsistenciaDuplicada($id_formacion, $dni)`
Verifica si un DNI ya registró asistencia.

### `registrarAsistencia($id_formacion, $nombres, $apellidos, $dni, $email)`
Registra una nueva asistencia en la base de datos.

### `generarEnlaceAsistencia($codigo_formacion)`
Genera el enlace público para registrar asistencia.

### `limpiarNombreApellido($input)`
Sanitiza y convierte a MAYÚSCULAS nombres y apellidos.

### `limpiarDNI($dni)`
Sanitiza el DNI quitando caracteres no permitidos.

## 📊 Estadísticas Disponibles

El gestor muestra:

- Total de asistencias por formación
- Fecha y hora de la formación
- Modalidad (Presencial/Virtual/Híbrida)
- Estado de la formación
- Lista completa de participantes con DNI y email
- Fecha y hora de cada registro

## 🐛 Solución de Problemas

### El email no llega

1. Verifica que la configuración SMTP sea correcta
2. Revisa la carpeta de SPAM
3. Verifica que el servidor permita envío de emails

### No puedo registrar asistencia

1. Verifica que estés dentro de la ventana de tiempo permitida
2. Verifica que no hayas registrado asistencia previamente
3. Verifica que todos los campos estén completos

### Error de base de datos

1. Verifica que la tabla `asistencias_formaciones` exista
2. Verifica que el usuario de BD tenga permisos
3. Revisa los logs de PHP para detalles del error

## 📝 Logs y Debugging

Los errores se registran automáticamente en el log de errores de PHP:

```php
error_log("Error: " . $mensaje);
```

Revisa el archivo de logs del servidor para debugging.

## 🔄 Actualización de Ventanas de Tiempo

Si necesitas cambiar las ventanas de tiempo, edita `config.php`:

```php
// Permitir desde 30 minutos antes del inicio
define('MINUTOS_ANTES_INICIO', 30);

// Permitir hasta 2 horas después del fin
define('HORAS_DESPUES_FIN', 2);
```

## 🎓 Integración con Sistema de Certificados

Este sistema está preparado para integrarse con el sistema de generación de certificados. Los datos registrados incluyen:

- Nombre completo en MAYÚSCULAS (listo para certificado)
- DNI (identificador único)
- Email (para envío de certificado)
- Fecha de asistencia

## 📞 Soporte

Para consultas o problemas:
- Email: formacion@sajur.org
- Organización: Sociedad Argentina de Justicia Restaurativa

## 📄 Licencia

Sistema desarrollado específicamente para SAJUR.
© 2025 Sociedad Argentina de Justicia Restaurativa

---

**Versión:** 1.0.0
**Fecha:** 14/11/2025
**Desarrollado para:** SAJUR - Sociedad Argentina de Justicia Restaurativa
