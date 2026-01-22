# Roadmap: Super Admin de Verumax

**Fecha de creación:** 25 de Diciembre de 2024
**Última actualización:** 22 de Enero de 2026
**Estado:** En desarrollo

---

## 🔄 En Progreso (Sesión 22-Ene-2026)

### Email de Bienvenida a Clientes
**Estado:** Funcional, pendiente revisión final

**Completado:**
- ✅ Botón "Enviar" en lista de clientes
- ✅ Formulario con preview del email
- ✅ Integración con SendGrid
- ✅ Auto-carga de contraseña inicial desde `admin_password_plain`
- ✅ Indicador si el cliente cambió su contraseña
- ✅ URLs corregidas a formato subdominio (`codigo.verumax.com`)
- ✅ Sección "Primeros Pasos" con guía paso a paso
- ✅ Tip de F1 para ayuda contextual

**Pendiente revisar en próxima sesión:**
- [ ] Probar email completo y verificar formato visual
- [ ] ¿Agregar más contenido? ¿Videos? ¿FAQ?
- [ ] Ejecutar SQL en producción: `sql/20260121_agregar_password_inicial.sql`

**Archivos modificados:**
- `verumax-admin/clientes.php`
- `sql/20260121_agregar_password_inicial.sql` (nuevo)

---

## Principio Fundamental

> **El Super Admin hace lo mínimo necesario para que el cliente exista.**
> **Todo lo demás (logo, colores, misión, firmantes) lo configura el cliente desde su admin.**

---

## Objetivo

Crear un panel de administración interno para Verumax que permita:
- Gestionar clientes/instituciones
- Asignar soluciones y planes
- Controlar límites de uso
- Monitorear el estado de la plataforma

**Decisiones Confirmadas:**
- **Ubicación:** `/verumax-admin/`
- **Seguridad:** 2FA con TOTP obligatorio
- **Credenciales cliente:** Mostrar en pantalla (sin emails automáticos)
- **SendGrid:** Cuenta única de Verumax, se registra sender por cliente
- **Solución inicial:** Solo Certificatum (Identitas pendiente de completar)

---

## 1. Arquitectura

### Estructura de Carpetas

```
E:\appVerumax\verumax-admin\
├── index.php                    # Dashboard principal
├── login.php                    # Paso 1: Usuario + Contraseña
├── login_2fa.php                # Paso 2: Código TOTP
├── setup_2fa.php                # Configuración inicial 2FA (QR)
├── config.php                   # Configuración y conexiones BD
├── logout.php
├── assets/
│   ├── css/admin.css
│   └── js/admin.js
├── modulos/
│   ├── clientes/
│   │   ├── index.php            # Listado de clientes
│   │   ├── crear.php            # Wizard de creación (3 pasos)
│   │   ├── editar.php           # Edición de cliente
│   │   ├── ver.php              # Detalle + uso + límites
│   │   ├── clonar.php           # Clonar configuración
│   │   └── procesador.php       # Backend AJAX
│   ├── planes/
│   │   ├── index.php            # Listado de planes por solución
│   │   └── editar.php           # Editar plan + límites
│   ├── estadisticas/
│   │   └── index.php            # Dashboard global
│   ├── facturacion/             # Fase futura
│   │   └── index.php
│   └── sistema/
│       ├── index.php            # Config general
│       ├── super_admins.php     # Gestión de super admins
│       └── dominios.php         # Gestión de dominios
├── includes/
│   ├── header.php
│   ├── sidebar.php
│   ├── footer.php
│   └── auth.php                 # Verificación sesión + 2FA
├── classes/
│   ├── ClienteGenerator.php     # Creación automática de clientes
│   ├── Database.php             # Conexiones PDO
│   ├── LimitesService.php       # Verificación de límites
│   └── AuditLog.php             # Registro de acciones
├── instructivos/
│   └── sendgrid.md              # Guía interna para config email
└── templates/
    └── institucion/             # Templates para nuevos clientes
        ├── index.php.tpl
        ├── header.php.tpl
        ├── footer.php.tpl
        ├── style.css.tpl
        ├── creare_pdf.php.tpl
        └── certificatum/
            ├── index.php.tpl
            ├── creare.php.tpl
            ├── cursus.php.tpl
            └── tabularium.php.tpl
```

---

## 2. Autenticación con 2FA

### Flujo de Login Super Admin

```
[Super Admin] → login.php (usuario + contraseña)
    ↓ válido
[Super Admin] → login_2fa.php (código 6 dígitos)
    ↓ válido
[Sesión] → Dashboard Super Admin
```

### Implementación TOTP

- **Biblioteca:** `RobThree/TwoFactorAuth`
- **Instalación:** `composer require robthree/twofactorauth`
- **Algoritmo:** SHA1, 6 dígitos, 30 segundos

### Seguridad

- Contraseñas con bcrypt (cost 12)
- Rate limiting: 3 intentos, bloqueo 15 min
- CSRF tokens en formularios
- Prepared statements (PDO)
- Audit log de acciones críticas

---

## 3. Wizard Crear Cliente (3 Pasos)

### PASO 1: Identificación del Cliente

| Campo | Tipo | Descripción | Ejemplo |
|-------|------|-------------|---------|
| **Slug*** | texto | Identificador único, minúsculas | `sajur` |
| **Nombre completo*** | texto | Nombre legal/oficial | `Sociedad Argentina de Justicia Restaurativa` |
| **Tipo cliente*** | select | Normal / Beta / Test | `Normal` |
| **Email contacto*** | email | Comunicación VERUMax ↔ Cliente | `info@sajur.org` |
| **Teléfono contacto** | texto | Opcional | `+54 11 1234-5678` |
| **Notas internas** | textarea | Solo visible para VERUMax | `Cliente referido por...` |

**Validaciones:**
- Slug único (AJAX en tiempo real)
- Slug solo permite: `a-z`, `0-9`, `-`

**Auto-generado:**
- URL: `{slug}.verumax.com`
- Email VERUMax: `{slug}@verumax.com`

---

### PASO 2: Soluciones y Planes

```
┌─────────────────────────────────────────────────────────────────┐
│  SOLUCIONES DISPONIBLES                                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ ☑ CERTIFICATUM                                 [Plan ▼] │   │
│  │   Certificados y documentos académicos                  │   │
│  │                                                         │   │
│  │   Plan: Premium ▼                                       │   │
│  │   └─ Certificados/mes: 200                              │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ ☐ IDENTITAS (próximamente)                              │   │
│  │   Landing page institucional                            │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                 │
│  ─────────────────────────────────────────────────────────────  │
│  Fecha inicio:      [13/01/2026]                               │
│  Fecha vencimiento: [13/01/2027] (auto 1 año)                  │
│  ─────────────────────────────────────────────────────────────  │
│                                                                 │
└─────────────────────────────────────────────────────────────────┘
```

#### Planes Certificatum (desde PRICING_STRATEGY.md)

| Plan | Precio/mes | Certificados/mes | Características |
|------|------------|------------------|-----------------|
| **Essentialis** | $12 | 50 | Branding, QR, Emisión masiva |
| **Premium** | $24 | 200 | + Cohortes, Analíticos, Múltiples firmantes |
| **Excellens** | $40 | 1,000 | + API REST, Integración LMS, Webhooks |
| **Supremus** | $80 | Ilimitados | + Blockchain, Soporte 24/7, SLA |

---

### PASO 3: Email y Credenciales

#### Sección A: Email del Sistema

| Opción | Descripción |
|--------|-------------|
| ◉ **Usar @verumax.com** | Se creará `{slug}@verumax.com` |
| ○ **Usar dominio propio** | Cliente provee su email |

#### Sección B: Credenciales Admin

| Campo | Valor |
|-------|-------|
| **Usuario** | `admin` (editable) |
| **Contraseña** | [Generar automática] o [Manual] |
| **2FA** | ☐ Habilitar desde el inicio |

---

### Pantalla Final: Resumen

```
╔═══════════════════════════════════════════════════════════════════╗
║                    ✅ CLIENTE CREADO EXITOSAMENTE                 ║
╠═══════════════════════════════════════════════════════════════════╣
║                                                                   ║
║  INFORMACIÓN DEL CLIENTE                                          ║
║  ───────────────────────────────────────────────────────────────  ║
║  Nombre:        Sociedad Argentina de Justicia Restaurativa       ║
║  Slug:          sajur                                             ║
║  Tipo:          Normal                                            ║
║  Contacto:      info@sajur.org                                    ║
║                                                                   ║
║  SOLUCIONES ACTIVAS                                               ║
║  ───────────────────────────────────────────────────────────────  ║
║  • Certificatum → Plan Premium (200 certs/mes)                   ║
║  • Vencimiento: 13/01/2027                                       ║
║                                                                   ║
╠═══════════════════════════════════════════════════════════════════╣
║                                                                   ║
║  📧 DATOS PARA ENVIAR AL CLIENTE                                  ║
║  ┌─────────────────────────────────────────────────────────────┐  ║
║  │                                                             │  ║
║  │  Hola,                                                      │  ║
║  │                                                             │  ║
║  │  Tu cuenta en VERUMax está lista:                           │  ║
║  │                                                             │  ║
║  │  🌐 Tu sitio: https://sajur.verumax.com                     │  ║
║  │  🔐 Admin: https://sajur.verumax.com/admin/                 │  ║
║  │  👤 Usuario: admin                                          │  ║
║  │  🔑 Contraseña: Xk9#mP2$vL7n                                │  ║
║  │  📧 Email certificados: sajur@verumax.com                   │  ║
║  │                                                             │  ║
║  │  Saludos, Equipo VERUMax                                    │  ║
║  │                                                             │  ║
║  └─────────────────────────────────────────────────────────────┘  ║
║                                                                   ║
║  [📋 Copiar mensaje]  [💾 Guardar PDF]                           ║
║                                                                   ║
╠═══════════════════════════════════════════════════════════════════╣
║                                                                   ║
║  🔧 TAREAS PENDIENTES (interno VERUMax)                           ║
║  ───────────────────────────────────────────────────────────────  ║
║  ☐ Crear email sajur@verumax.com en proveedor                    ║
║  ☐ Registrar sender en SendGrid                                   ║
║  ☐ Verificar sender en SendGrid                                   ║
║  ☐ Enviar datos al cliente                                        ║
║                                                                   ║
╚═══════════════════════════════════════════════════════════════════╝
```

---

## 4. Instructivo Interno: Configuración SendGrid

### Caso A: Cliente usa `{slug}@verumax.com`

**Paso 1: Crear cuenta de email**
```
1. Acceder al panel del proveedor de email (Google Workspace, cPanel)
2. Crear nueva cuenta: sajur@verumax.com
3. Contraseña: [generar y guardar en gestor]
4. Opcional: Configurar reenvío a soporte@verumax.com
```

**Paso 2: Registrar en SendGrid**
```
1. Ir a SendGrid → Settings → Sender Authentication
2. Click en "Verify a Single Sender"
3. Completar:
   - From Email: sajur@verumax.com
   - From Name: SAJuR - VERUMax
   - Reply To: sajur@verumax.com
   - Company: VERUMax
4. Click "Create"
```

**Paso 3: Verificar sender**
```
1. Acceder al email sajur@verumax.com
2. Buscar email de SendGrid "Please verify your sender"
3. Click en "Verify Single Sender"
4. Confirmar verificación exitosa
```

**Paso 4: Actualizar en Super Admin**
```
1. Super Admin → Clientes → sajur → Editar
2. Marcar: "✅ Email verificado en SendGrid"
3. Guardar
```

### Caso B: Cliente usa dominio propio

**Comunicar al cliente:**
```
Para usar tu email (formacion@sajur.org) en los certificados:

1. Te enviaremos un email de verificación de SendGrid
2. Abrí ese email y hacé click en "Verify Single Sender"
3. Una vez verificado, avisanos para activar

⚠️ Hasta verificar, los emails salen desde noreply@verumax.com
```

---

## 5. Estructura de Carpetas a Crear

Cuando se crea un cliente nuevo `sajur`:

```
E:\appVerumax\
│
├── sajur/                              ← CREAR
│   ├── index.php                       ← Proxy a landing
│   ├── header.php                      ← Proxy header compartido
│   ├── footer.php                      ← Proxy footer compartido
│   ├── style.css                       ← CSS específico (vacío)
│   ├── creare_pdf.php                  ← Proxy inteligente PDFs
│   └── certificatum/                   ← CREAR
│       ├── index.php                   ← Proxy a cursus
│       ├── creare.php                  ← Proxy a creare
│       ├── cursus.php                  ← Proxy a cursus
│       └── tabularium.php              ← Proxy a tabularium
│
├── uploads/
│   ├── logos/
│   │   └── sajur_logo.png              ← Cuando cliente sube logo
│   └── favicons/
│       └── sajur_favicon.ico           ← Generado del logo
│
└── assets/templates/certificados/
    └── sajur/                          ← CREAR (vacía)
```

### Contenido de Archivos Proxy

**`sajur/index.php`:**
```php
<?php
$institucion = 'sajur';
require_once __DIR__ . '/../identitas/index.php';
```

**`sajur/creare_pdf.php`:**
```php
<?php
$institucion = 'sajur';
$certificatum_path = __DIR__ . '/../certificatum';
$tipo = $_GET['genus'] ?? 'analyticum';
$tipos_tcpdf = ['certificatum_approbationis', 'certificatum_doctoris'];

if (in_array($tipo, $tipos_tcpdf)) {
    require_once $certificatum_path . '/creare_pdf_tcpdf.php';
} else {
    require_once $certificatum_path . '/creare_pdf.php';
}
```

**`sajur/certificatum/creare.php`:**
```php
<?php
$institucion = 'sajur';
require_once __DIR__ . '/../../certificatum/creare.php';
```

---

## 6. Base de Datos

### Tabla: `super_admins`

```sql
CREATE TABLE super_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    nombre VARCHAR(200),
    totp_secret VARCHAR(32),
    totp_habilitado TINYINT(1) DEFAULT 0,
    rol ENUM('superadmin', 'soporte') DEFAULT 'superadmin',
    activo TINYINT(1) DEFAULT 1,
    ultimo_acceso DATETIME,
    intentos_fallidos INT DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tabla: `planes_suscripcion`

```sql
CREATE TABLE planes_suscripcion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    solucion VARCHAR(50) NOT NULL,              -- 'certificatum', 'identitas'
    codigo VARCHAR(50) NOT NULL,                -- 'essentialis', 'premium', etc.
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio_mensual DECIMAL(10,2),
    precio_anual DECIMAL(10,2),
    -- Límites cuantitativos (0 = ilimitado)
    limite_pdfs_mes INT DEFAULT 0,
    limite_emails_mes INT DEFAULT 0,
    limite_ia_llamadas_mes INT DEFAULT 0,
    limite_ia_imagenes_mes INT DEFAULT 0,
    limite_import_registros_mes INT DEFAULT 0,
    limite_usuarios_admin INT DEFAULT 0,
    -- Límites cualitativos (features habilitadas)
    features JSON,
    -- Idiomas disponibles (array de códigos)
    idiomas_disponibles JSON,
    -- Retención de logs (días, 0 = ilimitado)
    retencion_logs_dias INT DEFAULT 30,
    -- Metadata
    activo TINYINT(1) DEFAULT 1,
    orden INT DEFAULT 0,
    UNIQUE KEY uk_solucion_codigo (solucion, codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Planes Certificatum (precios según PRICING_STRATEGY.md)
INSERT INTO planes_suscripcion (
    solucion, codigo, nombre, precio_mensual, precio_anual,
    limite_pdfs_mes, limite_emails_mes, limite_ia_llamadas_mes, limite_ia_imagenes_mes,
    limite_import_registros_mes, limite_usuarios_admin, retencion_logs_dias,
    idiomas_disponibles, features, orden
) VALUES
-- Essentialis: $12/mes
('certificatum', 'essentialis', 'Essentialis', 12.00, 120.00,
 50, 100, 10, 5, 500, 2, 30,
 '["es_AR","pt_BR"]',
 '{"branding":true,"qr":true,"emision_masiva":true,"dashboard":true,"exportar_csv":true}',
 1),
-- Premium: $24/mes
('certificatum', 'premium', 'Premium', 24.00, 240.00,
 200, 500, 50, 20, 5000, 5, 30,
 '["es_AR","pt_BR","en_US","es_ES"]',
 '{"branding":true,"qr":true,"emision_masiva":true,"dashboard":true,"exportar_csv":true,"cohortes":true,"analiticos":true,"multiples_firmantes":true,"plantillas_custom":true}',
 2),
-- Excellens: $40/mes
('certificatum', 'excellens', 'Excellens', 40.00, 400.00,
 1000, 5000, 200, 100, 50000, 10, 0,
 '["es_AR","pt_BR","en_US","es_ES","ca_ES","eu_ES","pt_PT"]',
 '{"branding":true,"qr":true,"emision_masiva":true,"dashboard":true,"exportar_csv":true,"cohortes":true,"analiticos":true,"multiples_firmantes":true,"plantillas_custom":true,"api":true,"lms":true,"webhooks":true}',
 3),
-- Supremus: $80/mes
('certificatum', 'supremus', 'Supremus', 80.00, 800.00,
 0, 0, 0, 0, 0, 0, 0,
 '["*"]',
 '{"branding":true,"qr":true,"emision_masiva":true,"dashboard":true,"exportar_csv":true,"cohortes":true,"analiticos":true,"multiples_firmantes":true,"plantillas_custom":true,"api":true,"lms":true,"webhooks":true,"blockchain":true,"soporte_24_7":true,"sla":true}',
 4);
```

### Matriz de Límites Certificatum

| Límite | Essentialis | Premium | Excellens | Supremus |
|--------|-------------|---------|-----------|----------|
| **PDFs/mes** | 50 | 200 | 1,000 | ∞ |
| **Emails/mes** | 100 | 500 | 5,000 | ∞ |
| **Llamadas IA/mes** | 10 | 50 | 200 | ∞ |
| **Imágenes DALL-E/mes** | 5 | 20 | 100 | ∞ |
| **Import registros/mes** | 500 | 5,000 | 50,000 | ∞ |
| **Usuarios admin** | 2 | 5 | 10 | ∞ |
| **Retención logs** | 30 días | 30 días | ∞ | ∞ |
| **Idiomas** | es_AR, pt_BR | +en_US, es_ES | +ca_ES, eu_ES, pt_PT | Todos |

### Features por Plan

| Feature | Essentialis | Premium | Excellens | Supremus |
|---------|:-----------:|:-------:|:---------:|:--------:|
| Branding personalizado | ✓ | ✓ | ✓ | ✓ |
| Validación QR | ✓ | ✓ | ✓ | ✓ |
| Emisión masiva | ✓ | ✓ | ✓ | ✓ |
| Dashboard métricas | ✓ | ✓ | ✓ | ✓ |
| Exportar CSV | ✓ | ✓ | ✓ | ✓ |
| Gestión cohortes | - | ✓ | ✓ | ✓ |
| Analíticos avanzados | - | ✓ | ✓ | ✓ |
| Múltiples firmantes | - | ✓ | ✓ | ✓ |
| Plantillas custom | - | ✓ | ✓ | ✓ |
| API REST | - | - | ✓ | ✓ |
| Integración LMS | - | - | ✓ | ✓ |
| Webhooks | - | - | ✓ | ✓ |
| Registro Blockchain | - | - | - | ✓ |
| Soporte 24/7 | - | - | - | ✓ |
| SLA garantizado | - | - | - | ✓ |

### Tabla: `instancia_soluciones`

```sql
CREATE TABLE instancia_soluciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT NOT NULL,
    solucion VARCHAR(50) NOT NULL,              -- 'certificatum'
    id_plan INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_vencimiento DATE NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_instancia_solucion (id_instancia, solucion),
    FOREIGN KEY (id_instancia) REFERENCES instances(id),
    FOREIGN KEY (id_plan) REFERENCES planes_suscripcion(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tabla: `instancia_uso_mensual`

```sql
CREATE TABLE instancia_uso_mensual (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT NOT NULL,
    solucion VARCHAR(50) NOT NULL,              -- 'certificatum'
    anio_mes VARCHAR(7) NOT NULL,               -- "2026-01"
    -- Contadores de uso
    pdfs_generados INT DEFAULT 0,
    emails_enviados INT DEFAULT 0,
    ia_llamadas INT DEFAULT 0,
    ia_imagenes INT DEFAULT 0,
    import_registros INT DEFAULT 0,
    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_instancia_solucion_mes (id_instancia, solucion, anio_mes),
    INDEX idx_anio_mes (anio_mes)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Tabla: `audit_log`

```sql
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_super_admin INT,
    accion VARCHAR(100) NOT NULL,
    entidad VARCHAR(50) NOT NULL,
    id_entidad INT,
    datos_anteriores JSON,
    datos_nuevos JSON,
    ip_address VARCHAR(45),
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_entidad (entidad, id_entidad),
    INDEX idx_fecha (fecha),
    FOREIGN KEY (id_super_admin) REFERENCES super_admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

### Modificaciones a `instances`

```sql
-- Campos de identificación y contacto
ALTER TABLE instances ADD COLUMN tipo_cliente ENUM('normal', 'beta', 'test') DEFAULT 'normal';
ALTER TABLE instances ADD COLUMN email_contacto VARCHAR(255);
ALTER TABLE instances ADD COLUMN telefono_contacto VARCHAR(50);
ALTER TABLE instances ADD COLUMN notas_internas TEXT;

-- Campos de email SendGrid
ALTER TABLE instances ADD COLUMN email_sistema VARCHAR(255);
ALTER TABLE instances ADD COLUMN email_sistema_tipo ENUM('verumax', 'propio') DEFAULT 'verumax';
ALTER TABLE instances ADD COLUMN email_verificado TINYINT(1) DEFAULT 0;

-- Campos de 2FA para admin cliente
ALTER TABLE instances ADD COLUMN admin_totp_secret VARCHAR(32);
ALTER TABLE instances ADD COLUMN admin_totp_habilitado TINYINT(1) DEFAULT 0;

-- Campos de idiomas disponibles (configurados desde Super Admin)
ALTER TABLE instances ADD COLUMN idiomas_habilitados JSON DEFAULT '["es_AR"]';
ALTER TABLE instances ADD COLUMN idioma_default VARCHAR(10) DEFAULT 'es_AR';

-- Campos de IA
ALTER TABLE instances ADD COLUMN ia_habilitada TINYINT(1) DEFAULT 0;
```

### Idiomas Soportados

| Código | Idioma | Disponible desde |
|--------|--------|------------------|
| `es_AR` | Español (Argentina) | Todos los planes |
| `pt_BR` | Português (Brasil) | Essentialis+ |
| `en_US` | English (US) | Premium+ |
| `es_ES` | Español (España) | Premium+ |
| `ca_ES` | Català (Catalunya) | Excellens+ |
| `eu_ES` | Euskara (País Vasco) | Excellens+ |
| `pt_PT` | Português (Portugal) | Excellens+ |

**Configuración en Super Admin:**
- Al crear/editar cliente, se muestran solo los idiomas permitidos por su plan
- El cliente elige cuáles habilitar de los disponibles
- El idioma default debe estar dentro de los habilitados

---

## 7. Sistema de Límites

### Clase LimitesService

```php
namespace VERUMax\Services;

class LimitesService {

    /**
     * Verifica si puede realizar acción
     * @return array ['permitido' => bool, 'mensaje' => string, 'uso' => int, 'limite' => int]
     */
    public static function verificar(int $id_instancia, string $solucion): array {
        // Obtener plan actual
        $plan = self::obtenerPlan($id_instancia, $solucion);
        if (!$plan) {
            return ['permitido' => false, 'mensaje' => 'Sin plan activo'];
        }

        // Verificar vencimiento
        if (strtotime($plan['fecha_vencimiento']) < time()) {
            return ['permitido' => false, 'mensaje' => 'Plan vencido'];
        }

        // Límite 0 = ilimitado
        if ($plan['limite_principal'] == 0) {
            return ['permitido' => true, 'limite' => 0, 'uso' => 0];
        }

        // Obtener uso del mes
        $uso = self::obtenerUsoMes($id_instancia, $solucion);

        if ($uso >= $plan['limite_principal']) {
            return [
                'permitido' => false,
                'mensaje' => "Límite alcanzado ({$uso}/{$plan['limite_principal']} {$plan['limite_principal_nombre']})",
                'uso' => $uso,
                'limite' => $plan['limite_principal']
            ];
        }

        return [
            'permitido' => true,
            'uso' => $uso,
            'limite' => $plan['limite_principal']
        ];
    }

    /**
     * Incrementa contador de uso
     */
    public static function incrementar(int $id_instancia, string $solucion, int $cantidad = 1): void {
        $anio_mes = date('Y-m');
        // INSERT ... ON DUPLICATE KEY UPDATE uso_actual = uso_actual + $cantidad
    }
}
```

### Integración en Certificatum

**En `creare_pdf.php`:**
```php
use VERUMax\Services\LimitesService;

// Antes de generar
$verificacion = LimitesService::verificar($id_instancia, 'certificatum');
if (!$verificacion['permitido']) {
    // Mostrar página de límite alcanzado
    include 'limite_alcanzado.php';
    exit;
}

// ... generar certificado ...

// Después de generar exitosamente
LimitesService::incrementar($id_instancia, 'certificatum');
```

---

## 8. Política de Retención (desde POLITICA_RETENCION.md)

### Al cancelar suscripción:

| Elemento | Plan Activo | Cancelado (0-12 meses) | Cancelado (+12 meses) |
|----------|:-----------:|:----------------------:|:---------------------:|
| Panel gestión | ✓ | ✗ | ✗ |
| Emitir nuevos | ✓ | ✗ | ✗ |
| Descarga PDF | ✓ | ✓ Grace period | ✗ |
| Validación QR | ✓ | ✓ Permanente | ✓ Permanente |
| Branding | ✓ Institución | ✗ VERUMax | ✗ VERUMax |

**Principio:** El certificado pertenece al estudiante. La validación QR funciona siempre.

---

## 9. Features Adicionales

### Confirmadas (incluir en fases futuras)

| Feature | Descripción | Fase |
|---------|-------------|------|
| **Clonar cliente** | Copiar configuración de uno existente | 6 |
| **Exportar datos** | Backup JSON/SQL del cliente | 6 |
| **Gestión dominios** | cliente.verumax.com vs dominio custom | 6 |
| **Vencimiento planes** | Alertas, renovación, grace period | 5 |
| **Facturación** | Stripe/MercadoPago | 7+ |

### Pendientes de definir

| Feature | Descripción | Decisión |
|---------|-------------|----------|
| **Multi super-admin** | Roles: Admin, Soporte, Comercial | ¿Necesario? |
| **Impersonar cliente** | "Acceder como" sin contraseña | ¿Útil para soporte? |
| **Webhooks** | Notificar eventos externos | ¿Prioridad? |
| **API REST** | Crear clientes programáticamente | ¿Prioridad? |

---

## 10. Fases de Implementación

### Fase 1: Estructura + Login (Prioridad Alta)
- [ ] Crear estructura de carpetas `verumax-admin/`
- [ ] Ejecutar SQL para crear tablas
- [ ] `composer require robthree/twofactorauth`
- [ ] Implementar `config.php` y `Database.php`
- [ ] Implementar login con 2FA
- [ ] Crear super admin inicial
- [ ] Dashboard básico

### Fase 2: Listado de Clientes (Prioridad Alta)
- [ ] Listado con filtros (tipo, plan, estado)
- [ ] Detalle de cliente con uso actual
- [ ] Conexión con datos reales

### Fase 3: Wizard Crear Cliente (Prioridad Alta)
- [ ] Wizard 3 pasos
- [ ] Clase `ClienteGenerator.php`
- [ ] Templates de archivos proxy
- [ ] Validación de slug
- [ ] Pantalla de resumen con "Copiar"
- [ ] Instructivo SendGrid interno

### Fase 4: Sistema de Límites (Prioridad Alta)
- [ ] `LimitesService.php`
- [ ] Integrar en `creare_pdf.php`
- [ ] Página de "Límite alcanzado"
- [ ] Mostrar uso en detalle de cliente

### Fase 5: Vencimiento de Planes (Prioridad Media)
- [ ] Alertas de vencimiento próximo
- [ ] Proceso de grace period
- [ ] Notificaciones automáticas

### Fase 6: Edición + Extras (Prioridad Media)
- [ ] Editar cliente
- [ ] Clonar cliente
- [ ] Exportar datos
- [ ] Gestión de dominios
- [ ] CRUD de planes

### Fase 7: Estadísticas + Facturación (Prioridad Baja)
- [ ] Dashboard estadísticas globales
- [ ] Gráficos con Chart.js
- [ ] Integración Stripe/MercadoPago

---

## 11. Acciones Automáticas al Crear Cliente

1. Validar todos los datos
2. Iniciar transacción BD
3. INSERT en `instances`
4. INSERT en `instancia_soluciones` (Certificatum + plan)
5. INSERT en `email_config`
6. **Crear carpeta física:** `/{slug}/`
7. **Crear archivos proxy:**
   - `{slug}/index.php`
   - `{slug}/header.php`
   - `{slug}/footer.php`
   - `{slug}/style.css`
   - `{slug}/creare_pdf.php`
   - `{slug}/certificatum/index.php`
   - `{slug}/certificatum/creare.php`
   - `{slug}/certificatum/cursus.php`
   - `{slug}/certificatum/tabularium.php`
8. **Crear carpeta templates:** `assets/templates/certificados/{slug}/`
9. **Si es Test:** Cargar datos de prueba
10. Commit transacción
11. Log en `audit_log`
12. Mostrar pantalla de éxito

---

## 12. Impacto en Producción

### Análisis por Fase

| Fase | ¿Afecta clientes existentes? |
|------|------------------------------|
| **1-3** | ❌ NO - Carpeta nueva, tablas nuevas |
| **4** | ⚠️ Controlable - Bypass si `id_plan = NULL` |
| **5-7** | ❌ NO - Features nuevas |

### Bypass para Clientes Existentes

```php
// En creare_pdf.php
$tiene_plan = isset($instance_config['id_plan']) && $instance_config['id_plan'];

if ($tiene_plan) {
    // Verificar límites
    $verificacion = LimitesService::verificar($id_instancia, 'certificatum');
    if (!$verificacion['permitido']) {
        // Mostrar límite
    }
}
// Si no tiene plan → sin límites (clientes legacy)
```

---

## 13. Dependencias

### PHP (Composer)
```bash
composer require robthree/twofactorauth
```

### Frontend (CDN)
```html
<script src="https://cdn.tailwindcss.com"></script>
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://unpkg.com/lucide@latest"></script>
```

---

**Última actualización:** 13 de Enero de 2026
