# CLAUDE.md

Este archivo proporciona orientación a Claude Code (claude.ai/code) cuando trabaja con código en este repositorio.

---

## ⚠️ INSTRUCCIONES DE DESPLIEGUE (OBLIGATORIO)

**IMPORTANTE:** Al finalizar cualquier tarea que modifique archivos o base de datos, SIEMPRE debo informar al usuario:

1. **Archivos modificados:** Lista completa de archivos que fueron creados o editados, para que el usuario pueda subirlos al servidor remoto.

2. **Scripts SQL a ejecutar:** Si se requieren cambios en la base de datos (nuevas tablas, columnas, inserts, updates), indicar el SQL exacto que debe ejecutarse en producción.

**Formato de resumen:**

```
📁 ARCHIVOS MODIFICADOS:
- ruta/archivo1.php
- ruta/archivo2.php

🗃️ SQL A EJECUTAR (si aplica):
[código SQL aquí]
```

---

## Agentes Especializados

Ver `CLAUDE_AGENTS.md` para la configuración de agentes especializados:

- **help-manual-auditor:** Audita cobertura de ayuda y mantiene el manual de usuario

### Manual de Usuario

- **Fuente:** `docs/manual_usuario.md`
- **Vista online:** `admin/manual.php` (requiere autenticación)
- **Descarga PDF:** Disponible desde la vista online

---

## Descripción General del Proyecto

**Verumax** es una plataforma multi-tenant de gestión de certificados académicos y documentos educativos para instituciones en Argentina. La plataforma permite:

- Generar certificados digitales, analíticos académicos y constancias
- Validar documentos mediante códigos QR únicos
- Gestionar estudiantes, cursos y participaciones docentes
- Soportar múltiples idiomas (español, portugués brasileño)
- Personalización de branding por institución

**Instituciones Activas:**
- SAJuR - Sociedad Argentina de Justicia Restaurativa (Argentina)
- Liberté - Instituto Libertad Educativa
- FotosJuan

---

## Arquitectura Multi-Tenant

### Estructura de Carpetas

```
/appVerumax/
├── certificatum/                    # Motor central (compartido)
│   ├── config.php                   # Configuración y conexión BD
│   ├── cursus.php                   # Lista de cursos del estudiante
│   ├── creare.php                   # Generación visual de documentos
│   ├── creare_pdf.php               # Conversión a PDF (mPDF)
│   ├── creare_content.php           # Contenido HTML para PDF
│   ├── creare_pdf_tcpdf.php         # Generador legacy (TCPDF)
│   ├── tabularium.php               # Trayectoria académica detallada
│   ├── validare.php                 # Validación de códigos QR
│   ├── verificatio.php              # Vista pública de documento validado
│   ├── administrare.php             # Panel administrativo
│   ├── administrare_procesador.php  # Carga masiva de datos
│   ├── administrare_gestionar.php   # CRUD estudiantes/cursos
│   ├── autodetect.php               # Detección por subdominio
│   ├── instituta.php                # Lista de instituciones
│   └── templates/                   # Plantillas compartidas
│
├── sajur/                           # Institución 1: SAJuR
│   ├── index.php                    # Landing page
│   ├── style.css                    # Estilos específicos
│   ├── creare_pdf.php               # Proxy al motor central
│   └── certificatum/                # Proxies locales
│       ├── index.php
│       ├── creare.php
│       ├── cursus.php
│       └── tabularium.php
│
├── liberte/                         # Institución 2: Liberté
├── fotosjuan/                       # Institución 3: FotosJuan
│
├── assets/                          # Recursos compartidos
│   ├── templates/certificados/      # Plantillas por institución
│   │   ├── sajur/
│   │   │   └── template_clasico.jpg # Imagen de fondo certificado
│   │   └── liberte/
│   ├── images/
│   │   ├── firmas/                  # Firmas digitales
│   │   │   ├── sajur_firma.png
│   │   │   └── liberte_firma.png
│   │   └── logos/                   # Logos institucionales
│   └── fonts/                       # Fuentes custom
│
└── src/VERUMax/Services/            # Servicios PSR-4
    ├── StudentService.php           # Gestión de estudiantes/cursos
    ├── InstitutionService.php       # Configuración institucional
    ├── LanguageService.php          # Traducciones multiidioma
    ├── CertificateService.php       # Códigos de validación
    ├── PDFService.php               # Generación de PDFs
    └── QRCodeService.php            # Generación de códigos QR
```

### Base de Datos Multi-Tenant

**Bases de Datos:**
- `verumax_general` - Configuración de instancias, traducciones, plantillas email
- `verumax_nexus` - Miembros (estudiantes/docentes) compartidos
- `verumax_academi` - Cursos, inscripciones, competencias
- `verumax_certifi` - Participaciones docentes, certificados generados

**Aislamiento de Datos:**
Todas las tablas principales incluyen campo `institucion` o `id_instancia` para separación lógica:

```sql
-- Ejemplo: tabla estudiantes
SELECT * FROM estudiantes
WHERE institucion = 'sajur' AND dni = '12345678';

-- Ejemplo: tabla cursos
SELECT * FROM cursos
WHERE id_instancia = 1;  -- SAJuR
```

**IMPORTANTE:** Siempre filtrar por institución en queries para mantener aislamiento.

---

## Tipos de Documentos

El sistema genera documentos con identificadores en **latín** (parámetro `genus`):

### Para Estudiantes

| Tipo | ID (`genus`) | Descripción | Orientación |
|------|-------------|-------------|-------------|
| Analítico Académico | `analyticum` | Registro completo con timeline, notas, competencias | Vertical (A4) |
| Certificado de Aprobación | `certificatum_approbationis` | Documento formal de aprobación del curso | Horizontal (A4) |
| Constancia de Alumno Regular | `testimonium_regulare` | Comprueba inscripción activa | Vertical (A4) |
| Constancia de Finalización | `testimonium_completionis` | Curso completado sin nota | Vertical (A4) |
| Constancia de Inscripción | `testimonium_inscriptionis` | Prueba de inscripción con fecha de inicio | Vertical (A4) |

### Para Docentes/Formadores

| Tipo | ID (`genus`) | Descripción | Estado Requerido |
|------|-------------|-------------|------------------|
| Certificado de Participación | `certificatum_doctoris` | Documento formal final | `Completado` |
| Constancia de Asignación | `testimonium_doctoris` | Documento provisional | `Asignado` |
| Constancia de Participación | `testimonium_doctoris` | Documento provisional | `En curso` |

**Alias:** `certificatum_docente` → `certificatum_doctoris` (compatibilidad)

### Estados de Participación Docente

| Estado | Documentos Disponibles | Acciones |
|--------|----------------------|----------|
| **Asignado** | Constancia de Asignación | Curso aún no iniciado |
| **En curso** | Constancia de Participación (provisional) | Curso en progreso |
| **Completado** | Certificado + Constancia | Curso finalizado |

**Regla de Negocio:** El certificado solo está disponible cuando `estado = 'Completado'`. Si el docente intenta acceder antes, se muestra página de bloqueo con opción de descargar constancia.

---

## Generación de PDFs

### Bibliotecas Utilizadas

**mPDF (Principal - Actual):**
- **Archivo:** `creare_pdf.php`
- **Uso:** Conversión de HTML/CSS complejo a PDF
- **Ventajas:** Soporte de Tailwind CSS, caracteres Unicode, fuentes custom
- **Documentos:** Analíticos, constancias, certificados sin imagen de fondo

**TCPDF (Legacy/Especializado):**
- **Archivo:** `creare_pdf_tcpdf.php`
- **Uso:** Dibujo nativo sobre imagen de fondo JPG
- **Ventajas:** Control preciso de posicionamiento
- **Documentos:** Solo certificados con plantilla de imagen (SAJuR)

### Proxy Inteligente

El archivo `sajur/creare_pdf.php` actúa como **router** que selecciona la biblioteca según el tipo:

```php
// Proxy en sajur/creare_pdf.php
$tipo_documento = $_GET['genus'] ?? 'analyticum';
$tipos_tcpdf = ['certificatum_approbationis', 'certificatum_doctoris', 'certificatum_docente'];

if (in_array($tipo_documento, $tipos_tcpdf)) {
    // Certificados con imagen → TCPDF
    require_once $certificatum_path . '/creare_pdf_tcpdf.php';
} else {
    // Analíticos, constancias → mPDF
    require_once $certificatum_path . '/creare_pdf.php';
}
```

### Flujo de Generación

```
Usuario hace clic en "Descargar PDF"
        ↓
sajur/creare_pdf.php (proxy)
        ↓
    ┌─────────────────┬──────────────────┐
    │ Si certificado  │ Si analítico     │
    ↓                 ↓                  │
creare_pdf_tcpdf    creare_pdf.php     │
    │                 │                  │
    │                 ↓                  │
    │        creare_content.php         │
    │        (genera HTML)              │
    │                 ↓                  │
    │        mPDF::Output()             │
    ↓                 ↓                  │
PDF con imagen      PDF moderno        │
```

---

## Sistema de Idiomas

### Implementación

Usa `LanguageService` (PSR-4) con archivos de traducción por idioma:

```php
use VERUMax\Services\LanguageService;

// Inicializar
LanguageService::init($institucion, $lang_request);

// Función helper
$t = fn($key, $params = [], $default) => LanguageService::get($key, $params, $default);

// Uso
echo $t('certificatum.my_courses_title', [], 'Mis Cursos');
echo $t('certificatum.cert_desc_approval', [
    'nombre' => 'Juan Pérez',
    'dni' => '12345678'
], 'Texto por defecto...');
```

### Idiomas Soportados

| Idioma | Código | Ubicación | Estado |
|--------|--------|-----------|--------|
| Español (Argentina) | `es_AR` | `lang/es_AR/certificatum.php` | Completo |
| Português (Brasil) | `pt_BR` | `lang/pt_BR/certificatum.php` | Completo |
| English (US) | `en_US` | `lang/en_US.php` | Parcial |

### Soporte de Género

El sistema adapta textos según el género de la persona:

```php
// Ejemplo: "aprobado" vs "aprobada"
$aprobado_texto = LanguageService::getGenderedText(
    $genero_persona,  // 'Masculino', 'Femenino', 'Otro'
    'aprobad',        // Raíz
    'sufijo_o'        // Tipo de sufijo
);
// Resultado: 'aprobado' o 'aprobada'

// Ejemplo: "Docente" vs "Docenta"
$rol_texto = LanguageService::getGenderedTitle($genero_docente, 'docente');
```

### Formateo de Fechas

```php
// Español: "Viernes, 19 de Diciembre de 2025"
// Português: "Sexta-feira, 19 de Dezembro de 2025"
$fecha_formateada = LanguageService::formatDate('2025-12-19', true);
```

### Preservación de Idioma en URLs

**Regla:** Siempre incluir parámetro `lang` en enlaces para mantener idioma:

```php
// ✅ CORRECTO
$url = 'cursus.php?institutio=' . $inst . '&documentum=' . $dni . '&lang=' . $current_lang;

// ❌ INCORRECTO (pierde idioma)
$url = 'cursus.php?institutio=' . $inst . '&documentum=' . $dni;
```

---

## Roles de Docentes

### Roles Soportados

```php
$roles_display = [
    'docente' => 'Docente',
    'instructor' => 'Instructor',
    'orador' => 'Orador',
    'conferencista' => 'Conferencista',
    'facilitador' => 'Facilitador',
    'tutor' => 'Tutor',
    'coordinador' => 'Coordinador'
];
```

### Códigos de Color por Rol

| Rol | Color de Fondo | Color de Texto |
|-----|---------------|----------------|
| Docente | `bg-purple-100` | `text-purple-800` |
| Instructor | `bg-blue-100` | `text-blue-800` |
| Orador | `bg-orange-100` | `text-orange-800` |
| Conferencista | `bg-amber-100` | `text-amber-800` |
| Facilitador | `bg-teal-100` | `text-teal-800` |
| Tutor | `bg-indigo-100` | `text-indigo-800` |
| Coordinador | `bg-rose-100` | `text-rose-800` |

---

## Plantillas y Branding

### Configuración por Institución

```php
$instance_config = InstitutionService::getConfig('sajur');

// Retorna:
[
    'nombre' => 'Sociedad Argentina de Justicia Restaurativa',
    'nombre_completo' => 'Sociedad Argentina de Justicia Restaurativa',
    'logo_url' => 'https://verumax.com/uploads/logos/sajur-logo.png',
    'logo_estilo' => 'rectangular-rounded',
    'color_primario' => '#2E7D32',      // Verde SAJuR
    'color_secundario' => '#1B5E20',    // Verde oscuro
    'color_acento' => '#66bb6a',        // Verde claro
    'firmante_nombre' => 'Dra. Diana Márquez',
    'firmante_cargo' => 'Presidenta SAJuR',
    'idioma_default' => 'es_AR',
    'idiomas_habilitados' => 'es_AR,pt_BR'
]
```

### Tipos de Plantillas

**1. Certificado con Imagen de Fondo (SAJuR)**

**Archivo:** `assets/templates/certificados/sajur/template_clasico.jpg`
**Dimensiones:** 1122 x 793 px (A4 horizontal)
**Contenido estático:** Logo, título "CERTIFICADO", bordes decorativos, firma escaneada
**Contenido dinámico posicionado con CSS absolute:**

```css
.cert-curso {
    position: absolute;
    top: 158px;           /* Nombre del curso */
    font-size: 22px;
    color: #1a5276;
}

.cert-nombre-texto {
    position: absolute;
    top: 415px;           /* Nombre de la persona */
    font-family: 'Great Vibes', cursive;
    font-size: 52px;
    color: #7d6608;       /* Dorado */
}

.cert-descripcion {
    top: 510px;           /* Texto descriptivo */
}

.cert-qr {
    bottom: 70px;         /* Código QR de validación */
}
```

**2. Certificado Moderno HTML/CSS (sin imagen)**

Diseño moderno con Tailwind CSS, gradientes dinámicos y colores institucionales:

```html
<div class="certificado-moderno">
    <div class="marco-decorativo">
        <!-- Header con logo + nombre -->
        <div style="background: linear-gradient(90deg,
            {{color_primario}} 0%,
            {{color_secundario}} 100%);">
        </div>

        <!-- Línea decorativa -->
        <div class="linea-decorativa"></div>

        <!-- Contenido principal -->
        <h2 style="color: {{color_primario}};">Certificado de Aprobación</h2>
        <p class="nombre-persona">{{nombre_completo}}</p>

        <!-- Tarjetas de información -->
        <div class="grid grid-cols-2">
            <div style="background-color: {{color_primario}}15;">
                Carga Horaria: {{carga_horaria}}
            </div>
            <div style="background-color: {{color_secundario}}15;">
                Finalización: {{fecha_finalizacion}}
            </div>
        </div>

        <!-- Footer: Firma + QR -->
        <div class="flex justify-between">
            <div>
                <img src="{{firma_url}}">
                <p>{{firmante_nombre}}</p>
                <p>{{firmante_cargo}}</p>
            </div>
            <div>
                <img src="{{qr_url}}">
                <p>verumax.com</p>
            </div>
        </div>
    </div>
</div>
```

**3. Analítico Académico (Vertical A4)**

Documento con timeline de eventos, resumen de notas y competencias:

```html
<div class="analitico-container">
    <header class="bg-gray-50">
        <p style="color: {{color_primario}};">TRAYECTORIA ACADÉMICA</p>
        <h1>{{nombre_curso}}</h1>
        <p>Estudiante: {{nombre}} (DNI: {{dni}})</p>
    </header>

    <div class="columnas">
        <!-- Columna izquierda: Timeline -->
        <div class="timeline">
            <div class="timeline-item">
                <div class="dot" style="background: {{color_primario}};"></div>
                <p>Inscripción al curso</p>
                <p>01/03/2025</p>
            </div>
            <!-- Más eventos... -->
        </div>

        <!-- Columna derecha: Resumen -->
        <div class="resumen">
            <p>Nota Final: {{nota_final}}</p>
            <p>Asistencia: {{asistencia}}</p>
            <p>Carga Horaria: {{carga_horaria}} hs.</p>
        </div>
    </div>

    <footer>
        <img src="{{qr_url}}">
        <p>{{codigo_validacion}}</p>
    </footer>
</div>
```

**4. Constancias (Vertical A4)**

Documentos formales para alumno regular, inscripción, finalización:

```html
<div class="constancia-container">
    <header>
        <h1>{{nombre_institucion}}</h1>
        <p>{{titulo_constancia}}</p>  <!-- Ej: "Constancia de Alumno Regular" -->
        <img src="{{logo_url}}">
    </header>

    <main>
        <p>Por medio de la presente, se deja constancia que
           <strong>{{nombre_completo}}</strong>,
           D.N.I. N° <strong>{{dni}}</strong>,
           {{cuerpo_constancia}}:  <!-- Ej: "se encuentra cursando activamente" -->
        </p>

        <p class="nombre-curso">{{nombre_curso}}</p>

        <p>Se extiende la presente a los fines que estime corresponder.</p>
    </main>

    <footer>
        <!-- Firma digital -->
        <img src="{{firma_url}}">
        <div class="linea-firma"></div>
        <p>{{firmante_nombre}}</p>
        <p>{{firmante_cargo}}</p>

        <!-- QR de validación -->
        <img src="{{qr_url}}">
        <p>{{codigo_validacion}}</p>
    </footer>
</div>
```

---

## Parámetros de URL

El sistema usa nomenclatura en **latín** para parámetros (decisión de diseño):

| Parámetro | Significado | Valores Ejemplo |
|-----------|-------------|-----------------|
| `institutio` | Institución | `sajur`, `liberte` |
| `documentum` | DNI del estudiante/docente | `12345678` |
| `cursus` | ID del curso | `SA-CUR-2025-001` |
| `genus` | Tipo de documento | `certificatum_approbationis`, `analyticum` |
| `participacion` | ID participación docente | `5` (número) |
| `tipo` | Tipo de usuario | `estudiante`, `docente` |
| `lang` | Idioma | `es_AR`, `pt_BR` |

**Ejemplo de URLs:**

```
# Certificado de estudiante
/creare.php?institutio=sajur&documentum=12345678&cursus=SA-CUR-2025-001&genus=certificatum_approbationis&lang=es_AR

# Analítico de estudiante
/creare_pdf.php?institutio=sajur&documentum=12345678&cursus=SA-CUR-2025-001&genus=analyticum&lang=es_AR

# Certificado de docente
/creare.php?institutio=sajur&documentum=98765432&participacion=5&genus=certificatum_doctoris&lang=es_AR

# Validación de QR
/validare.php?codigo=VALID-ABCD1234
```

---

## Sistema de Backup

**Política de respaldos:**

Antes de modificar cualquier archivo, crear backup con estructura cronológica:

```
backup/
  └── 2025-12-19/
      ├── 0826-creare.php
      ├── 0826-cursus.php
      └── 1430-creare.php  (si se modifica el mismo archivo más tarde)
```

**Formato:**
- Carpeta: `backup/YYYY-MM-DD/`
- Archivo: `HHMM-nombre_original.php`

**Ventajas:**
- Organización cronológica clara
- Múltiples backups del mismo archivo en un día
- Fácil limpieza de backups antiguos (eliminar carpetas por fecha)
- Ordenamiento automático por hora

---

## Problemas Conocidos y Limitaciones

### 1. Hardcodeos de Instituciones

**Ubicaciones con hardcodeos de SAJuR/Liberté:**

```php
// ❌ INCORRECTO - verificatio.php (línea 71-80)
if ($institucion == 'sajur') {
    $color_primary_text = 'sajur-green-dark-text';  // Clase CSS hardcodeada
}

// ✅ CORRECTO - Usar configuración dinámica
$color_primario = $instance_config['color_primario'];
$color_texto = "color: " . $color_primario . ";";
```

**Archivos a refactorizar:**
- `verificatio.php` (líneas 71-80)
- `administrare.php` (líneas 226-228)
- Archivos en `viejo/` (legacy, no prioritario)

### 2. Validación de Institución Vulnerable

**Problema actual:**
```php
// ❌ RIESGO: Path traversal
if (!is_dir('../' . $institucion)) {
    die('Error');
}
```

**Solución recomendada:**
```php
// ✅ SEGURO: Whitelist
$instituciones_validas = ['sajur', 'liberte', 'fotosjuan'];
if (!in_array($institucion, $instituciones_validas)) {
    die('Error: Institución no válida');
}
```

### 3. Falta de Protección SQL Injection

**Advertencia en `config.php`:**
```php
// IMPORTANTE: Para producción con MySQL, implementar protección
// contra SQL injection (prepared statements).
```

**Solución pendiente:** Migrar funciones wrapper a PDO con prepared statements.

### 4. Código Duplicado

**Bloques repetidos en 8+ archivos:**

```php
// Inicialización repetida en: cursus.php, creare.php, tabularium.php, etc.
$institucion = $_GET['institutio'] ?? null;
if (!$institucion) die('Error');

$instance_config = InstitutionService::getConfig($institucion);
LanguageService::init($institucion, $_GET['lang'] ?? null);
$t = fn($key, $params = [], $default) => LanguageService::get($key, $params, $default);
```

**Solución recomendada:** Crear archivo `init.php` con función reutilizable.

### 5. Credenciales Expuestas

**Archivo:** `INSTRUCCIONES_MIGRACION.md`
```php
define('CERT_DB_PASSWORD', '/hPfiYd6xH');  // ⚠️ Expuesta en documentación
```

**Solución:** Mover credenciales a archivo `.env` fuera del repositorio.

---

## Criterios de Desarrollo

### Reglas de Multi-Tenancy

1. **Nunca hardcodear instituciones específicas** - Usar siempre `$instance_config`
2. **Siempre filtrar por institución en queries** - Agregar `WHERE institucion = ?`
3. **Validar institución con whitelist** - No usar `is_dir()` sobre input de usuario
4. **Usar colores dinámicos** - Obtener de `$instance_config['color_primario']`
5. **Preservar idioma en URLs** - Incluir `&lang=` en todos los enlaces

### Reglas de Idioma

1. **Usar función `$t()` para todos los textos visibles** - No hardcodear textos
2. **Proveer texto por defecto** - `$t('key', [], 'Texto por defecto')`
3. **Aplicar género cuando corresponda** - Usar `getGenderedText()` y `getGenderedTitle()`
4. **Formatear fechas con `formatDate()`** - No usar `date()` directamente

### Reglas de Generación de PDFs

1. **Certificados con imagen → TCPDF** - Mejor control de posicionamiento
2. **Analíticos/Constancias → mPDF** - Soporte de HTML/CSS complejo
3. **Usar proxy inteligente** - Dejar que `sajur/creare_pdf.php` decida
4. **Incluir QR de validación** - Obligatorio en todos los documentos
5. **Agregar firma institucional** - Usar `$firmante_nombre` y `$firmante_cargo`

### Reglas de Seguridad

1. **Sanitizar output HTML** - Usar `htmlspecialchars()` en todos los `echo`
2. **Validar entrada de usuario** - Whitelist de valores permitidos
3. **No exponer credenciales** - Usar variables de entorno
4. **Implementar CSRF tokens** - Para formularios administrativos
5. **Validar permisos de acceso** - Verificar que usuario solo vea sus datos

---

## Versionado

### Archivos con Versiones Definidas

| Archivo | Versión Actual | Notas |
|---------|---------------|-------|
| `cursus.php` | 3.1 | Soporte multiidioma |
| `creare.php` | 3.1 | Soporte multiidioma |
| `tabularium.php` | 3.2 | Soporte multiidioma + docentes |
| `verificatio.php` | 2.3 | Validación pública |
| `validare.php` | 2.1 | Refactorizado |
| `administrare.php` | V2 | Panel multi-tenant |
| `creare_pdf.php` | 1.0 | mPDF |
| `config.php` | 1.0.0 | Wrapper functions |

### Criterio de Versionado

- **Major (X.0):** Cambios arquitectónicos, nuevas funcionalidades principales
- **Minor (0.X):** Nuevas features, soporte de idiomas
- **Patch (0.0.X):** Bugfixes, mejoras menores

**Ejemplo:** Cuando se agregue soporte para un nuevo idioma, incrementar versión minor (3.1 → 3.2).

---

## Planes de Suscripción

Todas las soluciones de Verumax usan los mismos planes:

| Plan | Características | Perfil de Cliente |
|------|----------------|-------------------|
| **Essentialis** | Branding personalizado, validación QR, 50 certificados/mes | Emprendedores, coaches, pequeñas academias |
| **Premium** | Emisión masiva (CSV), gestión de cohortes, 200 certificados/mes | Academias medianas, centros de capacitación |
| **Excellens** | API REST, integración Moodle/Canvas, 1,000 certificados/mes | Universidades, instituciones con LMS |
| **Supremus** | Blockchain, certificados ilimitados, soporte dedicado, SLA | Ministerios, redes educativas |

**Documentación comercial:**
- `certificatum/PRICING_STRATEGY.md` - Tarifas, descuentos, comisiones afiliados
- `certificatum/POLITICA_RETENCION.md` - Qué pasa con certificados al cancelar

---

## Tareas Pendientes

### Prioridad Alta

- [ ] Refactorizar hardcodeos de SAJuR en `verificatio.php` y `administrare.php`
- [ ] Implementar whitelist de instituciones válidas
- [ ] Migrar a PDO con prepared statements
- [ ] Mover credenciales a archivo `.env`

### Prioridad Media

- [ ] Consolidar código de inicialización en `init.php`
- [ ] Crear constantes para tipos de documento
- [ ] Unificar nomenclatura de variables
- [ ] Agregar validación de permisos de acceso

### Prioridad Baja

- [ ] Limpiar archivos legacy en `viejo/`
- [ ] Optimizar queries de base de datos
- [ ] Agregar tests unitarios
- [ ] Documentar API de servicios PSR-4

---

## Debug y Troubleshooting

### Modo Debug

Para depurar generación de PDFs sin descargar:

```php
// Agregar parámetro &debug=1 a la URL
if (isset($_GET['debug'])) {
    echo $html_content;
    exit;
}
```

### Logs de Errores

```php
// Activar en desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Loguear errores en producción
error_log("Error en certificatum: " . $mensaje);
```

### Consola del Navegador

```php
echo "<script>console.log(" . json_encode($variable) . ");</script>";
```

---

## Contacto y Contribuciones

Para preguntas sobre arquitectura o para proponer cambios, consultar con el equipo de desarrollo.

**Última actualización:** 19 de Diciembre de 2025
**Versión de documentación:** 2.0
