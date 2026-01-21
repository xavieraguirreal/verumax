# 🎯 PLAN DE IMPLEMENTACIÓN: SISTEMA QR UNIFICADO + IDENTITAS

**Fecha:** 16 de Enero de 2026
**Versión:** 1.0
**Estado:** Propuesta para revisión

---

## 📋 ÍNDICE

1. [Visión General](#visión-general)
2. [Arquitectura del Sistema QR Unificado](#arquitectura-del-sistema-qr-unificado)
3. [Sistema de Tarjetas Digitales Identitas](#sistema-de-tarjetas-digitales-identitas)
4. [Base de Datos](#base-de-datos)
5. [Estructura de Archivos](#estructura-de-archivos)
6. [Plan de Implementación por Fases](#plan-de-implementación-por-fases)
7. [Migración de Certificatum](#migración-de-certificatum)
8. [Testing y Validación](#testing-y-validación)
9. [Consideraciones de Seguridad](#consideraciones-de-seguridad)
10. [Roadmap](#roadmap)

---

## 🎯 VISIÓN GENERAL

### Objetivo Principal
Crear un **sistema unificado de códigos QR** que sirva a todas las soluciones de VERUMax (Certificatum, Identitas, Nexus, etc.) sin duplicar datos ni lógica, manteniendo independencia entre soluciones.

### Principios Fundamentales

1. **DRY (Don't Repeat Yourself):** Un solo código QR por documento/tarjeta
2. **Independencia de Soluciones:** Certificatum no depende de Identitas, ni viceversa
3. **Compartir sin Conflictos:** Mismo formato de código, diferente comportamiento
4. **Escalabilidad:** Preparado para nuevas soluciones futuras
5. **Analytics Centralizados:** Todos los escaneos en un solo lugar

### Diferencias Clave entre Soluciones

| Aspecto | Certificatum | Identitas |
|---------|-------------|-----------|
| **Producto** | Certificados académicos | Tarjeta digital + Sitio web |
| **Suscripción** | Mensual por volumen | Anual todo incluido |
| **QR Redirige a** | Validación de documento | Landing page profesional |
| **Planes** | Singularis, Essentialis, Premium, Excellens, Supremus | Essentialis, Premium, Excellens, Supremus |
| **Singularis** | Pago por certificado (sin suscripción) | NO aplica |
| **Target** | Instituciones educativas | Profesionales individuales |

---

## 🏗️ ARQUITECTURA DEL SISTEMA QR UNIFICADO

### Componentes Principales

```
┌─────────────────────────────────────────────────────────────┐
│                    VERUMAX ECOSYSTEM                        │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐       │
│  │ Certificatum│  │  Identitas  │  │    Nexus    │       │
│  └──────┬──────┘  └──────┬──────┘  └──────┬──────┘       │
│         │                 │                 │              │
│         └─────────────────┼─────────────────┘              │
│                           ▼                                │
│              ┌─────────────────────────┐                   │
│              │  VERUMaxCodeService     │                   │
│              │  (Sistema Unificado QR) │                   │
│              └────────────┬────────────┘                   │
│                           │                                │
│              ┌────────────▼────────────┐                   │
│              │   codigos_verumax       │                   │
│              │   (Tabla centralizada)  │                   │
│              └─────────────────────────┘                   │
│                                                             │
└─────────────────────────────────────────────────────────────┘
```

### Flujo de Generación de QR

```
1. Certificatum genera certificado para Juan (DNI 12345678)
   ↓
2. Llama a VERUMaxCodeService::generar(
      tipo: 'certificado',
      idInstancia: 1,
      solucion: 'certificatum',
      identificador: '12345678',
      metadata: {codigo_curso: 'SA-CUR-2025-001', ...}
   )
   ↓
3. Servicio verifica si ya existe código para esos datos
   ↓
4. Si NO existe: genera VALID-XXXXXXXXXXXX
   ↓
5. Guarda en codigos_verumax con tipo_codigo='certificado'
   ↓
6. Calcula URL destino: verumax.com/certificatum/validare.php?codigo=VALID-XXX
   ↓
7. Retorna código + URL del QR visual
```

### Flujo de Escaneo de QR

```
Usuario escanea QR con su celular
   ↓
Redirige a: verumax.com/c/VALID-XXXXXXXXXXXX
   ↓
.htaccess captura /c/{codigo}
   ↓
Redirige a redirigir.php?codigo=VALID-XXX
   ↓
redirigir.php consulta codigos_verumax
   ↓
Obtiene tipo_codigo y url_destino
   ↓
Registra escaneo en log_escaneos_qr
   ↓
Redirige según tipo:
   - certificado → certificatum/validare.php
   - tarjeta_digital → Landing del profesional
   - credencial → nexus/verificar-credencial.php
```

---

## 💳 SISTEMA DE TARJETAS DIGITALES IDENTITAS

### Concepto

La tarjeta digital de Identitas es un **JPG de alta calidad** (300 DPI para impresión) con:
- Foto del profesional
- Nombre y cargo
- Datos de contacto
- QR infalsificable que lleva a su landing page

**Ventaja competitiva:** No necesita app especial, es una imagen que cualquiera puede abrir.

### Componentes

#### 1. Generador de Tarjetas (`identitas/tarjetas/generar.php`)

**Entrada:**
- Datos del profesional (nombre, cargo, foto, contacto)
- Diseño seleccionado (clásico, moderno, minimalista)
- Colores institucionales

**Salida:**
- JPG de alta resolución (2100x1500 px = 300 DPI para tarjeta estándar)
- PNG con transparencia (opcional)
- PDF para imprenta (opcional)

**Tecnología:** PHP GD Library o Imagick

#### 2. Templates de Diseño

```
identitas/tarjetas/templates/
├── clasico.php          # Diseño formal con bordes
├── moderno.php          # Diseño limpio estilo Apple
├── minimalista.php      # Solo lo esencial
├── ejecutivo.php        # Para corporativos (Excellens+)
└── creativo.php         # Colores vibrantes (Premium+)
```

Cada template es una clase PHP que extiende `TarjetaTemplate`:

```php
class TarjetaClasica extends TarjetaTemplate {
    public function render($datos, $qr_path) {
        // Crea imagen con GD
        $img = imagecreatetruecolor(2100, 1500);

        // Dibuja fondo, foto, textos, QR

        return $img;
    }
}
```

#### 3. Sistema de QR para Tarjetas

**Características especiales:**
- **Un solo QR por profesional:** No cambia aunque actualice su información
- **Código persistente:** El QR de la tarjeta impresa seguirá funcionando siempre
- **Redirección inteligente:** Siempre lleva a la última versión de su sitio

**Implementación:**

```php
// Generar QR para tarjeta de Juan Pérez (SAJuR)
$qrData = VERUMaxCodeService::generar(
    tipo: 'tarjeta_digital',
    idInstancia: 1,  // SAJuR
    solucion: 'identitas',
    identificadorTitular: 'juanperez',  // slug único
    metadata: [
        'slug_instancia' => 'juanperez',
        'nombre_completo' => 'Juan Pérez',
        'cargo' => 'Arquitecto',
        'email' => 'juan@estudioarq.com',
        'telefono' => '+54 11 5555-1234'
    ]
);

// Resultado:
// codigo: VALID-TDG-JUANPEREZ-A1B2C3
// url_destino: https://verumax.com/t/VALID-TDG-JUANPEREZ-A1B2C3
// url_qr: https://api.qrserver.com/v1/create-qr-code/?data=...
```

#### 4. Landing Page Pública (`identitas/tarjeta-publica.php`)

Cuando alguien escanea el QR de la tarjeta:

**URL corta:** `verumax.com/t/VALID-XXX`

**Redirige a:** Landing page personalizada del profesional

**Contenido de la landing:**

```html
┌─────────────────────────────────────────┐
│          [FOTO PROFESIONAL]             │
│                                         │
│         JUAN PÉREZ                      │
│         Arquitecto                      │
│                                         │
│  ┌─────────┐ ┌─────────┐ ┌─────────┐  │
│  │WhatsApp │ │  Email  │ │LinkedIn │  │
│  └─────────┘ └─────────┘ └─────────┘  │
│                                         │
├─────────────────────────────────────────┤
│  Sobre Mí                               │
│  ────────                               │
│  [Biografía profesional...]             │
│                                         │
├─────────────────────────────────────────┤
│  Servicios                              │
│  ─────────                              │
│  ▸ Diseño arquitectónico                │
│  ▸ Gestión de obras                     │
│  ▸ Asesoramiento técnico                │
│                                         │
├─────────────────────────────────────────┤
│  Proyectos Destacados                   │
│  ───────────────────                    │
│  [Galería de imágenes]                  │
│                                         │
├─────────────────────────────────────────┤
│  Contacto                               │
│  ────────                               │
│  [Formulario + Mapa]                    │
└─────────────────────────────────────────┘
```

**Características:**
- Responsive (mobile-first)
- Carga rápida (< 2 segundos)
- SEO optimizado
- Schema.org markup (Person/ProfessionalService)
- Open Graph para compartir en redes

#### 5. Dashboard de Tarjetas (`identitas/app/tarjetas.php`)

Panel donde el usuario:

1. **Diseña su tarjeta:**
   - Sube foto profesional
   - Elige diseño (clásico, moderno, etc.)
   - Personaliza colores
   - Previsualiza en tiempo real

2. **Descarga en múltiples formatos:**
   - JPG alta resolución (300 DPI para imprimir)
   - PNG con fondo transparente
   - PDF listo para imprenta
   - Versión web (JPG optimizado)

3. **Ve estadísticas:**
   - Cuántas veces escanearon su QR
   - Desde qué países/ciudades
   - Qué dispositivos usan (iOS, Android, etc.)
   - Gráfico de escaneos por fecha

---

## 💾 BASE DE DATOS

### Tabla: `codigos_verumax` (Nueva - en `verumax_general`)

```sql
CREATE TABLE codigos_verumax (
    id_codigo INT AUTO_INCREMENT PRIMARY KEY,

    -- Código único
    codigo_validacion VARCHAR(50) NOT NULL UNIQUE COMMENT 'VALID-XXXXXXXXXXXX',

    -- Tipo determina el comportamiento al escanear
    tipo_codigo ENUM(
        -- CERTIFICATUM
        'certificado',
        'constancia',
        'analitico',
        'certificado_docente',

        -- IDENTITAS
        'tarjeta_digital',

        -- NEXUS
        'credencial_estudiante',
        'credencial_socio',

        -- GENÉRICO
        'documento_custom'
    ) NOT NULL,

    -- Origen
    id_instancia INT NOT NULL COMMENT 'FK a identitas_instances',
    solucion VARCHAR(50) NOT NULL COMMENT 'certificatum, identitas, nexus',

    -- Titular
    identificador_titular VARCHAR(50) NOT NULL COMMENT 'DNI, slug, email',
    nombre_titular VARCHAR(255),

    -- Metadata flexible (JSON)
    metadata JSON COMMENT 'Datos específicos según tipo_codigo',

    -- URL pre-calculada
    url_destino VARCHAR(500) NOT NULL,

    -- Analytics
    veces_escaneado INT DEFAULT 0,
    primer_escaneo TIMESTAMP NULL,
    ultimo_escaneo TIMESTAMP NULL,

    -- Control
    activo BOOLEAN DEFAULT 1,
    fecha_generacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NULL COMMENT 'NULL = no expira',

    -- Índices
    INDEX idx_tipo_codigo (tipo_codigo),
    INDEX idx_instancia (id_instancia),
    INDEX idx_identificador (identificador_titular),
    INDEX idx_solucion (solucion),
    INDEX idx_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Sistema unificado de códigos QR - Todas las soluciones VERUMax';
```

### Tabla: `log_escaneos_qr` (Nueva - en `verumax_general`)

```sql
CREATE TABLE log_escaneos_qr (
    id_log BIGINT AUTO_INCREMENT PRIMARY KEY,

    -- Código escaneado
    codigo_validacion VARCHAR(50) NOT NULL,
    tipo_codigo VARCHAR(50),
    id_instancia INT,

    -- Timestamp
    fecha_escaneo TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    -- Geolocalización
    ip_address VARCHAR(45),
    pais VARCHAR(100),
    ciudad VARCHAR(100),

    -- Dispositivo
    user_agent TEXT,
    dispositivo VARCHAR(50) COMMENT 'iOS, Android, Windows, Mac, Linux, Otro',
    navegador VARCHAR(50),

    -- Origen del escaneo
    referer TEXT,
    utm_source VARCHAR(100),
    utm_medium VARCHAR(100),
    utm_campaign VARCHAR(100),

    -- Resultado
    exitoso BOOLEAN DEFAULT 1,
    error_message TEXT,

    -- Índices
    INDEX idx_codigo (codigo_validacion),
    INDEX idx_fecha (fecha_escaneo),
    INDEX idx_instancia (id_instancia),
    INDEX idx_tipo (tipo_codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Log detallado de todos los escaneos de QR';
```

### Tabla: `identitas_tarjetas` (Actualizar en `verumax_identi`)

```sql
-- Agregar campos necesarios
ALTER TABLE identitas_tarjetas
ADD COLUMN codigo_qr VARCHAR(50) COMMENT 'FK a codigos_verumax.codigo_validacion',
ADD COLUMN template_usado VARCHAR(50) DEFAULT 'clasico' COMMENT 'Template de diseño',
ADD COLUMN colores_personalizados JSON COMMENT 'Paleta de colores custom',
ADD COLUMN foto_url VARCHAR(500) COMMENT 'URL de la foto profesional',
ADD COLUMN imagen_tarjeta_jpg VARCHAR(500) COMMENT 'URL del JPG generado',
ADD COLUMN imagen_tarjeta_png VARCHAR(500) COMMENT 'URL del PNG generado',
ADD COLUMN imagen_tarjeta_pdf VARCHAR(500) COMMENT 'URL del PDF generado',
ADD COLUMN estadisticas JSON COMMENT 'Stats de escaneos precalculadas',
ADD INDEX idx_codigo_qr (codigo_qr);
```

---

## 📁 ESTRUCTURA DE ARCHIVOS

### Nuevos Archivos a Crear

```
E:\appVerumax\
│
├── src/VERUMax/Services/
│   └── VERUMaxCodeService.php          # ★ NUEVO - Servicio unificado de QR
│
├── identitas/
│   ├── tarjetas/
│   │   ├── generar.php                 # ★ NUEVO - Generador de tarjetas
│   │   ├── descargar.php               # ★ NUEVO - Descarga JPG/PNG/PDF
│   │   ├── TarjetaTemplate.php         # ★ NUEVO - Clase base de templates
│   │   └── templates/
│   │       ├── clasico.php             # ★ NUEVO
│   │       ├── moderno.php             # ★ NUEVO
│   │       ├── minimalista.php         # ★ NUEVO
│   │       ├── ejecutivo.php           # ★ NUEVO
│   │       └── creativo.php            # ★ NUEVO
│   │
│   ├── tarjeta-publica.php             # ★ NUEVO - Landing pública
│   │
│   └── app/
│       └── tarjetas.php                # ★ NUEVO - Dashboard de tarjetas
│
├── redirigir.php                       # ★ NUEVO - Router central de QR
│
└── sql/
    └── 20260116_sistema_qr_unificado.sql  # ★ NUEVO - Script de BD
```

### Archivos a Modificar

```
certificatum/
├── creare.php                          # Migrar a VERUMaxCodeService
├── creare_content.php                  # Migrar a VERUMaxCodeService
└── creare_pdf_tcpdf.php                # Migrar a VERUMaxCodeService

src/VERUMax/Services/
└── CertificateService.php              # Wrapper al nuevo servicio

.htaccess                               # Agregar reglas de rewrite
```

---

## 🚀 PLAN DE IMPLEMENTACIÓN POR FASES

### **FASE 1: Infraestructura Base** (Semana 1)

**Objetivo:** Crear el sistema unificado de QR sin afectar Certificatum

#### Tareas:

1. **Base de Datos** (2 horas)
   - [ ] Crear tabla `codigos_verumax` en `verumax_general`
   - [ ] Crear tabla `log_escaneos_qr` en `verumax_general`
   - [ ] Actualizar tabla `identitas_tarjetas` con campos nuevos
   - [ ] Script SQL con datos de prueba

2. **Servicio VERUMaxCodeService** (4 horas)
   - [ ] Crear `src/VERUMax/Services/VERUMaxCodeService.php`
   - [ ] Método `generar()`
   - [ ] Método `registrarEscaneo()`
   - [ ] Método `obtenerInfo()`
   - [ ] Método `invalidar()`
   - [ ] Tests unitarios básicos

3. **Router Central** (2 horas)
   - [ ] Crear `redirigir.php` en raíz
   - [ ] Agregar reglas `.htaccess` para URLs amigables
   - [ ] Página de error para códigos inválidos/expirados

4. **Testing** (2 horas)
   - [ ] Generar códigos de prueba de cada tipo
   - [ ] Verificar que redirija correctamente
   - [ ] Verificar que registre escaneos

**Entregable:** Sistema QR funcional en paralelo (Certificatum sigue usando su método viejo)

---

### **FASE 2: Generador de Tarjetas Digitales** (Semana 2)

**Objetivo:** Crear el sistema de generación de tarjetas JPG con QR

#### Tareas:

1. **Clase Base de Templates** (3 horas)
   - [ ] Crear `identitas/tarjetas/TarjetaTemplate.php`
   - [ ] Métodos abstractos: `render()`, `getDimensions()`
   - [ ] Helper para escribir texto con GD
   - [ ] Helper para redimensionar/recortar fotos

2. **Templates de Diseño** (8 horas)
   - [ ] Template Clásico (formal con bordes)
   - [ ] Template Moderno (estilo Apple)
   - [ ] Template Minimalista (solo esencial)
   - [ ] Preview de cada template

3. **Generador Principal** (6 horas)
   - [ ] `identitas/tarjetas/generar.php`
   - [ ] Integración con VERUMaxCodeService (genera QR único)
   - [ ] Generación de JPG 300 DPI
   - [ ] Generación de PNG con transparencia
   - [ ] Generación de PDF con mPDF

4. **Sistema de Descarga** (2 horas)
   - [ ] `identitas/tarjetas/descargar.php`
   - [ ] Validar que el usuario tenga permisos
   - [ ] Headers correctos según formato (JPG/PNG/PDF)
   - [ ] Nombre de archivo descriptivo

5. **Testing** (2 horas)
   - [ ] Generar tarjeta de cada template
   - [ ] Verificar resolución 300 DPI
   - [ ] Escanear QR con celular → debe redirigir
   - [ ] Imprimir en papel → verificar calidad

**Entregable:** Generador de tarjetas funcional, descargables en 3 formatos

---

### **FASE 3: Landing Page Pública** (Semana 3)

**Objetivo:** Página a donde redirige el QR de la tarjeta

#### Tareas:

1. **Página de Destino** (6 horas)
   - [ ] `identitas/tarjeta-publica.php`
   - [ ] Hero con foto, nombre, cargo
   - [ ] Botones de contacto (WhatsApp, Email, LinkedIn, etc.)
   - [ ] Sección "Sobre Mí"
   - [ ] Sección "Servicios"
   - [ ] Sección "Contacto" con formulario
   - [ ] Footer con QR Analytics

2. **SEO y Meta Tags** (2 horas)
   - [ ] Open Graph optimizado
   - [ ] Schema.org Person/ProfessionalService
   - [ ] Twitter Cards
   - [ ] Sitemap dinámico

3. **Performance** (2 horas)
   - [ ] Lazy loading de imágenes
   - [ ] Minificación de CSS/JS inline
   - [ ] Cache de 1 hora
   - [ ] Lighthouse score > 90

4. **Testing** (2 horas)
   - [ ] Escanear QR → verificar que carga rápido
   - [ ] Probar en iOS, Android, Desktop
   - [ ] Verificar compartir en WhatsApp (preview)
   - [ ] Verificar compartir en LinkedIn (preview)

**Entregable:** Landing page pública optimizada y funcional

---

### **FASE 4: Dashboard de Usuario** (Semana 4)

**Objetivo:** Panel donde el usuario gestiona su tarjeta

#### Tareas:

1. **Interfaz Principal** (6 horas)
   - [ ] `identitas/app/tarjetas.php`
   - [ ] Preview de tarjeta actual
   - [ ] Editor de información (nombre, cargo, foto)
   - [ ] Selector de template con preview
   - [ ] Personalizador de colores

2. **Subida de Foto** (3 horas)
   - [ ] Drag & drop de imagen
   - [ ] Crop/resize en cliente (JS)
   - [ ] Validaciones (tamaño, formato)
   - [ ] Guardar en servidor

3. **Descarga de Archivos** (2 horas)
   - [ ] Botones: Descargar JPG, PNG, PDF
   - [ ] Modal con instrucciones de impresión
   - [ ] Botón "Compartir en WhatsApp"

4. **Estadísticas** (4 horas)
   - [ ] Widget: Total de escaneos
   - [ ] Widget: Escaneos últimos 7 días
   - [ ] Gráfico de escaneos por fecha
   - [ ] Tabla: Últimos escaneos (fecha, país, dispositivo)
   - [ ] Mapa de calor (países)

5. **Testing** (2 horas)
   - [ ] Crear tarjeta desde cero
   - [ ] Cambiar diseño
   - [ ] Cambiar foto
   - [ ] Descargar en 3 formatos
   - [ ] Verificar estadísticas

**Entregable:** Dashboard completo de gestión de tarjetas

---

### **FASE 5: Migración de Certificatum** (Semana 5)

**Objetivo:** Migrar Certificatum al sistema unificado SIN romper nada

#### Estrategia: Migración Gradual con Wrapper

**Paso 1:** Mantener compatibilidad con código viejo

```php
// En CertificateService.php (NO modificar lógica existente)

public static function getValidationCode(
    string $institution,
    string $dni,
    string $courseCode,
    string $documentType = self::TYPE_CERTIFICATE
): string {
    // NUEVO: Verificar si la instancia tiene el nuevo sistema activo
    if (self::useNewQRSystem($institution)) {
        return self::getValidationCodeUnified($institution, $dni, $courseCode, $documentType);
    }

    // VIEJO: Código original (intacto)
    try {
        $existing = self::findExistingCode($institution, $dni, $courseCode, $documentType);
        if ($existing) {
            return $existing;
        }

        $code = ValidationCodeService::generate($dni, $courseCode);
        self::storeCode($institution, $dni, $courseCode, $code, $documentType);

        return $code;

    } catch (PDOException $e) {
        error_log("Error generando código: " . $e->getMessage());
        return ValidationCodeService::generate($dni, $courseCode);
    }
}

// NUEVO: Método que usa el sistema unificado
private static function getValidationCodeUnified(
    string $institution,
    string $dni,
    string $courseCode,
    string $documentType
): string {
    $instance = InstitutionService::getConfig($institution);

    $result = VERUMaxCodeService::generar(
        tipo: 'certificado',
        idInstancia: $instance['id_instancia'],
        solucion: 'certificatum',
        identificadorTitular: $dni,
        metadata: [
            'codigo_curso' => $courseCode,
            'tipo_documento' => $documentType,
            'institucion' => $institution
        ]
    );

    return $result['codigo'];
}

// Helper: Verifica si la instancia usa el nuevo sistema
private static function useNewQRSystem(string $institution): bool {
    // Por defecto FALSE (usar sistema viejo)
    // Cuando todo esté probado, cambiar a TRUE
    return false;
}
```

**Paso 2:** Testing paralelo

1. Activar nuevo sistema para SAJuR (piloto)
2. Generar 10 certificados de prueba
3. Verificar que QR funcione
4. Comparar con sistema viejo
5. Si todo OK → activar para todas las instituciones

**Paso 3:** Activación gradual

```php
// Config por institución
private static function useNewQRSystem(string $institution): bool {
    $enabled_institutions = [
        'sajur',      // Piloto
        'liberte',    // Segundo piloto
        // Agregar más conforme se valide
    ];

    return in_array($institution, $enabled_institutions);
}
```

**Paso 4:** Migración de datos históricos

```php
// Script: migracion_codigos_certificatum.php

/**
 * Migra códigos existentes de codigos_validacion → codigos_verumax
 * Sin eliminar los originales (por seguridad)
 */

$stmt = $pdo->query("
    SELECT * FROM codigos_validacion
    WHERE institucion = 'sajur'
");

foreach ($stmt->fetchAll() as $old_code) {
    $metadata = [
        'codigo_curso' => $old_code['codigo_curso'],
        'tipo_documento' => $old_code['tipo_documento'],
        'institucion' => $old_code['institucion']
    ];

    // Insertar en nuevo sistema
    $pdo->prepare("
        INSERT IGNORE INTO codigos_verumax
        (codigo_validacion, tipo_codigo, id_instancia, solucion,
         identificador_titular, metadata, url_destino, fecha_generacion)
        VALUES (?, 'certificado', ?, 'certificatum', ?, ?, ?, ?)
    ")->execute([
        $old_code['codigo_validacion'],
        $old_code['id_instancia'],
        $old_code['dni'],
        json_encode($metadata),
        "https://verumax.com/certificatum/validare.php?codigo={$old_code['codigo_validacion']}",
        $old_code['fecha_generacion']
    ]);
}
```

#### Tareas:

1. **Wrapper en CertificateService** (3 horas)
   - [ ] Método `useNewQRSystem()` con flag por institución
   - [ ] Método `getValidationCodeUnified()` (nuevo)
   - [ ] Mantener método viejo intacto

2. **Testing con SAJuR** (4 horas)
   - [ ] Activar nuevo sistema solo para SAJuR
   - [ ] Generar 20 certificados de prueba
   - [ ] Escanear QR → verificar redirección
   - [ ] Comparar con sistema viejo
   - [ ] Verificar analytics

3. **Migración de Datos** (3 horas)
   - [ ] Script `migracion_codigos_certificatum.php`
   - [ ] Migrar códigos históricos de SAJuR
   - [ ] Verificar integridad de datos
   - [ ] Backup antes de migrar

4. **Rollout Gradual** (2 horas)
   - [ ] SAJuR OK → activar Liberté
   - [ ] Liberté OK → activar resto
   - [ ] Monitorear logs de errores

5. **Limpieza** (1 hora)
   - [ ] Deprecar tabla `codigos_validacion` (no eliminar)
   - [ ] Documentar cambio en CLAUDE.md

**Entregable:** Certificatum migrado al sistema unificado, 100% compatible

---

### **FASE 6: Integración con Nexus** (Semana 6)

**Objetivo:** Generar credenciales digitales con QR para estudiantes/socios

#### Concepto:

**Credencial Digital = Carnet de Estudiante/Socio con QR**

Diferencias con la tarjeta digital de Identitas:
- **Identitas:** Para profesionales que venden servicios
- **Nexus:** Para estudiantes/socios de una institución

Ejemplo:
```
┌────────────────────────────────────────┐
│  SOCIEDAD ARGENTINA DE JUSTICIA        │
│  RESTAURATIVA (SAJuR)                  │
│                                        │
│  ┌────────┐                            │
│  │ FOTO   │  JUAN PÉREZ                │
│  │        │  DNI: 12.345.678           │
│  └────────┘  Estudiante Regular        │
│                                        │
│              [QR CODE]                 │
│                                        │
│  Válido hasta: 31/12/2026              │
│  N° Credencial: EST-2025-00123         │
└────────────────────────────────────────┘
```

Al escanear el QR → Página de verificación:

```
✓ CREDENCIAL VÁLIDA

Juan Pérez
DNI: 12.345.678
Estudiante Regular en SAJuR

Válido hasta: 31 de Diciembre de 2026
Fecha de emisión: 15 de Enero de 2025

Esta credencial fue emitida por:
Sociedad Argentina de Justicia Restaurativa
```

#### Tareas:

1. **Generador de Credenciales** (6 horas)
   - [ ] `nexus/credenciales/generar.php`
   - [ ] Template de credencial (formato carnet)
   - [ ] Integración con VERUMaxCodeService
   - [ ] QR tipo `credencial_estudiante` o `credencial_socio`

2. **Página de Verificación** (4 horas)
   - [ ] `nexus/verificar-credencial.php`
   - [ ] Muestra datos del estudiante/socio
   - [ ] Estado: Activo/Inactivo/Vencido
   - [ ] Logo de la institución
   - [ ] Marca de agua "VERIFICADO"

3. **Integración en Admin** (3 horas)
   - [ ] Botón "Generar Credencial" en ficha de miembro
   - [ ] Vista previa de credencial
   - [ ] Descargar PDF

4. **Testing** (2 horas)
   - [ ] Generar credencial de estudiante
   - [ ] Escanear QR → verificar página
   - [ ] Probar con credencial vencida
   - [ ] Probar con credencial inactiva

**Entregable:** Sistema de credenciales digitales para Nexus

---

## 🔄 MIGRACIÓN DE CERTIFICATUM

### Estrategia: Zero Downtime Migration

**Principio:** El sistema viejo y el nuevo coexisten hasta validar

### Código de Migración

```php
// certificatum/config.php

// Flag global de migración (empezar en FALSE)
define('USE_UNIFIED_QR_SYSTEM', false);

// Lista de instituciones en el nuevo sistema
define('UNIFIED_QR_INSTITUTIONS', [
    // 'sajur',    // Descomentar cuando esté listo
    // 'liberte',
]);
```

```php
// src/VERUMax/Services/CertificateService.php

private static function shouldUseUnifiedSystem(string $institution): bool {
    // Si flag global está OFF → usar sistema viejo
    if (!defined('USE_UNIFIED_QR_SYSTEM') || !USE_UNIFIED_QR_SYSTEM) {
        return false;
    }

    // Si flag global está ON → verificar si institución está migrada
    return in_array($institution, UNIFIED_QR_INSTITUTIONS);
}
```

### Plan de Rollback

Si algo falla:

1. Cambiar `USE_UNIFIED_QR_SYSTEM` a `false`
2. Reiniciar servidor (limpiar cache)
3. Todos los códigos nuevos se generan con sistema viejo
4. Los códigos generados con sistema nuevo siguen funcionando (no se pierden)

---

## 🧪 TESTING Y VALIDACIÓN

### Tests Unitarios

```php
// tests/VERUMaxCodeServiceTest.php

class VERUMaxCodeServiceTest extends TestCase {

    public function testGenerarCodigoCertificado() {
        $result = VERUMaxCodeService::generar(
            'certificado',
            1,
            'certificatum',
            '12345678',
            ['codigo_curso' => 'TEST-001']
        );

        $this->assertArrayHasKey('codigo', $result);
        $this->assertStringStartsWith('VALID-', $result['codigo']);
        $this->assertStringContainsString('validare.php', $result['url_destino']);
    }

    public function testGenerarCodigoTarjetaDigital() {
        $result = VERUMaxCodeService::generar(
            'tarjeta_digital',
            1,
            'identitas',
            'juanperez',
            ['slug_instancia' => 'juanperez']
        );

        $this->assertStringContainsString('/t/', $result['url_destino']);
    }

    public function testNoGenerarDuplicados() {
        $result1 = VERUMaxCodeService::generar(
            'certificado',
            1,
            'certificatum',
            '12345678',
            ['codigo_curso' => 'TEST-001']
        );

        $result2 = VERUMaxCodeService::generar(
            'certificado',
            1,
            'certificatum',
            '12345678',
            ['codigo_curso' => 'TEST-001']
        );

        // Mismo código
        $this->assertEquals($result1['codigo'], $result2['codigo']);
    }
}
```

### Tests de Integración

**Checklist de validación manual:**

- [ ] Generar certificado en Certificatum → escanear QR → debe mostrar validare.php
- [ ] Generar tarjeta en Identitas → escanear QR → debe mostrar landing profesional
- [ ] Generar credencial en Nexus → escanear QR → debe mostrar verificación
- [ ] Escanear código inválido → debe mostrar página de error
- [ ] Escanear código expirado → debe mostrar "Código expirado"
- [ ] Verificar analytics: cada escaneo debe registrarse en log_escaneos_qr
- [ ] Imprimir tarjeta → escanear QR desde papel → debe funcionar

### Tests de Performance

```bash
# Benchmark: Generación de 1000 códigos
ab -n 1000 -c 10 http://localhost/test_generar_codigo.php

# Objetivo: < 50ms por código
```

---

## 🔒 CONSIDERACIONES DE SEGURIDAD

### 1. Prevención de Falsificación

**Problema:** Alguien podría crear un QR falso con código inventado

**Solución:**
- Cada código se genera con `ValidationCodeService::generate()` que usa hash criptográfico
- La base de datos es la única fuente de verdad
- Si el código no está en `codigos_verumax` → Inválido

### 2. Prevención de Clonación

**Problema:** Alguien copia el JPG de una tarjeta y lo usa como propio

**Mitigación:**
- Marca de agua invisible en la tarjeta (steganografía)
- Al validar, mostrar foto del titular real
- Registrar TODAS las validaciones (detectar uso anómalo)

### 3. Expiración de Códigos

**Casos de uso:**

| Tipo | Expira | Motivo |
|------|--------|--------|
| Certificado académico | Nunca | Debe ser válido para siempre |
| Tarjeta digital | Nunca | El profesional sigue siendo el mismo |
| Credencial de estudiante | Sí (fecha definida) | Al graduarse/darse de baja |
| Credencial de socio | Sí (fecha de vencimiento) | Al vencer la membresía |

**Implementación:**

```php
// Al generar credencial con vencimiento
VERUMaxCodeService::generar(
    tipo: 'credencial_estudiante',
    // ...
    metadata: [
        'fecha_vencimiento' => '2025-12-31'
    ]
);

// En la BD:
UPDATE codigos_verumax
SET fecha_expiracion = '2025-12-31 23:59:59'
WHERE codigo_validacion = 'VALID-XXX';
```

### 4. Rate Limiting

**Problema:** Alguien escanea miles de códigos para hacer scraping

**Solución:**

```php
// En redirigir.php

$ip = getClientIP();

// Verificar cuántos escaneos hizo esta IP en la última hora
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total
    FROM log_escaneos_qr
    WHERE ip_address = :ip
      AND fecha_escaneo >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
");
$stmt->execute([':ip' => $ip]);
$count = $stmt->fetchColumn();

// Máximo 100 escaneos por hora por IP
if ($count > 100) {
    http_response_code(429); // Too Many Requests
    die('Rate limit exceeded');
}
```

### 5. Sanitización de Datos

**Siempre escapar:**
- Nombre del titular (XSS)
- Metadata JSON (inyección)
- User-Agent (log injection)

```php
// Malo
echo $titular['nombre'];

// Bueno
echo htmlspecialchars($titular['nombre'], ENT_QUOTES, 'UTF-8');
```

---

## 📅 ROADMAP

### Corto Plazo (Q1 2026)

- [x] Definir arquitectura
- [ ] Implementar FASE 1: Infraestructura Base
- [ ] Implementar FASE 2: Generador de Tarjetas
- [ ] Implementar FASE 3: Landing Page Pública
- [ ] Implementar FASE 4: Dashboard de Usuario

### Mediano Plazo (Q2 2026)

- [ ] Implementar FASE 5: Migración de Certificatum
- [ ] Implementar FASE 6: Integración con Nexus
- [ ] Analytics avanzados (Google Analytics 4 integration)
- [ ] Geolocalización de escaneos (MaxMind GeoIP)

### Largo Plazo (Q3-Q4 2026)

- [ ] App móvil para escanear QR (iOS + Android)
- [ ] Sistema de notificaciones push al escanear
- [ ] Blockchain para certificados (inmutabilidad)
- [ ] NFC para tarjetas físicas premium
- [ ] Integración con Apple Wallet / Google Pay

---

## 📊 MÉTRICAS DE ÉXITO

### KPIs Técnicos

| Métrica | Objetivo |
|---------|----------|
| Tiempo de generación de código | < 50ms |
| Tiempo de carga landing page | < 2 segundos |
| Disponibilidad del sistema | > 99.9% |
| Errores en producción | < 0.1% |

### KPIs de Negocio

| Métrica | Objetivo |
|---------|----------|
| % de usuarios que descargan su tarjeta | > 80% |
| % de QR escaneados al menos 1 vez | > 40% |
| Promedio de escaneos por tarjeta/mes | > 5 |
| NPS de la funcionalidad | > 8/10 |

---

## 🎓 DOCUMENTACIÓN TÉCNICA

### Nomenclatura de Códigos

**Formato:** `VALID-{TIPO}-{IDENTIFICADOR}-{HASH}`

Ejemplos:
- `VALID-CER-12345678-A1B2C3` (Certificado, DNI 12345678)
- `VALID-TDG-JUANPEREZ-X9Y8Z7` (Tarjeta Digital, slug juanperez)
- `VALID-CRE-98765432-M5N4P3` (Credencial, DNI 98765432)

### URLs de Redirección

| Tipo | URL Corta | Redirige a |
|------|-----------|------------|
| Certificado | `verumax.com/c/VALID-XXX` | `certificatum/validare.php` |
| Tarjeta Digital | `verumax.com/t/VALID-XXX` | Landing profesional |
| Credencial | `verumax.com/cred/VALID-XXX` | `nexus/verificar-credencial.php` |
| Genérico | `verumax.com/v/VALID-XXX` | `redirigir.php` (router) |

### Reglas .htaccess

```apache
# QR de Certificados
RewriteRule ^c/([A-Z0-9-]+)$ /certificatum/validare.php?codigo=$1 [L]

# QR de Tarjetas Digitales
RewriteRule ^t/([A-Z0-9-]+)$ /identitas/tarjeta-publica.php?codigo=$1 [L]

# QR de Credenciales
RewriteRule ^cred/([A-Z0-9-]+)$ /nexus/verificar-credencial.php?codigo=$1 [L]

# Router genérico (fallback)
RewriteRule ^v/([A-Z0-9-]+)$ /redirigir.php?codigo=$1 [L]
```

---

## 📞 CONTACTO Y SOPORTE

**Desarrollador Principal:** Claude (AI Assistant)
**Documentación:** `E:\appVerumax\PLAN_SISTEMA_QR_IDENTITAS.md`
**Fecha de Creación:** 16 de Enero de 2026
**Última Actualización:** 16 de Enero de 2026

---

## ✅ CHECKLIST FINAL DE IMPLEMENTACIÓN

Antes de considerar el proyecto completo:

### Funcionalidad
- [ ] Usuario puede crear tarjeta digital desde dashboard
- [ ] Usuario puede descargar tarjeta en JPG/PNG/PDF
- [ ] Usuario puede compartir su tarjeta por WhatsApp
- [ ] QR de la tarjeta redirige a landing profesional
- [ ] Landing carga en < 2 segundos
- [ ] Usuario puede ver estadísticas de escaneos
- [ ] Certificatum sigue generando certificados (sin afectarse)
- [ ] Certificatum migrado al nuevo sistema (opcional)

### Seguridad
- [ ] Códigos QR son únicos e irrepetibles
- [ ] No se puede falsificar un código
- [ ] Rate limiting implementado
- [ ] Todos los inputs sanitizados
- [ ] HTTPS en todas las URLs

### Performance
- [ ] Generación de código < 50ms
- [ ] Generación de tarjeta JPG < 2 segundos
- [ ] Landing page Lighthouse score > 90
- [ ] Analytics no afectan performance

### Documentación
- [ ] CLAUDE.md actualizado con nuevos archivos
- [ ] README de cada carpeta nueva
- [ ] Comentarios en código crítico
- [ ] Este documento (PLAN_SISTEMA_QR_IDENTITAS.md) completo

### Testing
- [ ] Tests unitarios pasan (> 90% coverage)
- [ ] Tests de integración pasan
- [ ] Validación manual completa
- [ ] No hay errores en logs de producción

---

**FIN DEL PLAN**

**Próximos pasos:**
1. Revisar y aprobar este plan
2. Comenzar FASE 1: Infraestructura Base
3. Iterar y ajustar según feedback

---

**Notas:**
- Este plan es flexible y puede ajustarse según necesidades
- Las estimaciones de tiempo son aproximadas
- Priorizar calidad sobre velocidad
- Siempre hacer backup antes de cambios importantes
