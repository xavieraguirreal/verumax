# LUMEN - Plan de Producto

## 📸 Concepto

**Nombre:** Lumen
**Significado:** En latín, *lumen* significa "luz"
**Concepto:** La luz es la materia prima de la fotografía y el arte visual. El nombre evoca la idea de "sacar a la luz" el trabajo del artista, de iluminar su talento.

**Lema:** *Lumen: Ars tua, perfecte exhibita* (Tu arte, perfectamente expuesto)

---

## 🎯 Filosofía del Producto

**Misión:** Ser la forma más rápida y segura para que un profesional creativo ponga su trabajo en línea con una calidad de exhibición excepcional.

**Principio:** Eliminamos toda la complejidad. El proceso debe ser tan simple como rellenar un perfil y arrastrar una carpeta de imágenes.

---

## 🏗️ Arquitectura Técnica

### Implementación
- **Backend:** `lumen.php` - Servicio de gestión y procesamiento
- **Frontend:** Portfolio público (vista del artista)
- **Adaptación por cliente:** Sistema multi-tenant basado en parámetros
- **Base de datos:** Arrays PHP por ahora (migración a MySQL en fase posterior)
- **Integración:** Parte del ecosistema TarjetaDigital (hereda datos del dashboard principal)

### Separación Backend/Frontend
```
┌─────────────────────────────────────┐
│   LUMEN (Backend Service)           │
│   - Procesamiento de imágenes       │
│   - Conversión automática           │
│   - Aplicación de marca de agua     │
│   - Gestión de datos                │
└─────────────┬───────────────────────┘
              │
              ▼
┌─────────────────────────────────────┐
│   PORTFOLIO (Frontend Público)      │
│   - Vista del artista               │
│   - Galerías visuales               │
│   - Experiencia de usuario          │
│   - fotosjuan/index.php             │
└─────────────────────────────────────┘
```

---

## 📋 FASE 1: MVP - "Tu Galería Elegante y Segura"

### 1.1 Flujo de Creación Simplificado

#### **Paso 1: Registro Rápido**
- Usuario se registra con email
- Por ahora: datos en arrays PHP (como `sajur/datos.php`)

#### **Paso 2: Formulario de Perfil Único**
Información esencial absorbida del dashboard:
- ✅ **Nombre / Nombre de Marca** (heredado de TarjetaDigital, editable)
- ✅ **Biografía / Descripción**
- ✅ **Contacto** (email, teléfono - heredado del dashboard)
- ✅ **Redes Sociales** (enlaces)

#### **Paso 3: Selección de Plantilla**
3-5 plantillas minimalistas prediseñadas:
- **Plantilla 1:** Cuadrícula clásica (grid 3 columnas)
- **Plantilla 2:** Masonry (Pinterest-style)
- **Plantilla 3:** Carrusel vertical (scroll infinito)
- **Plantilla 4:** Pantalla completa con navegación lateral
- **Plantilla 5:** Galería con categorías (tabs)

*Variación solo en disposición, no en funcionalidades.*

#### **Paso 4: Carga de Imágenes**
- Área drag & drop grande y clara
- Soporte para archivos individuales o carpetas completas
- Aceptación de cualquier formato (TIFF, JPG, PNG, RAW, etc.)

---

### 1.2 Motor de Procesamiento de Imágenes

#### **Problema a Resolver:**
El cliente sube TIFFs de 80MB → La web debe cargar rápido sin perder calidad visual

#### **Solución Técnica:**

**A. Carga sin restricciones**
```
Cliente sube: boda_ceremonia_001.TIFF (85 MB)
Sistema acepta: ✅ Sin quejas ni validaciones de tamaño
```

**B. Conversión Automática en Servidor**
Usando **ImageMagick** (librería PHP estándar):

1. **Backup Original**
   - Guarda archivo original intacto
   - Ubicación: `/uploads/originals/{cliente_id}/`
   - Nunca se expone públicamente

2. **Generación de Versiones Web**
   Crea múltiples versiones optimizadas en **WebP/AVIF**:
   ```
   - boda_ceremonia_001_large.webp   (1920x1280px) - Desktop
   - boda_ceremonia_001_medium.webp  (1280x854px)  - Tablet
   - boda_ceremonia_001_small.webp   (640x427px)   - Mobile
   - boda_ceremonia_001_thumb.webp   (300x200px)   - Thumbnails
   ```

3. **Exhibición Inteligente (Responsive)**
   ```php
   // Detección automática del dispositivo
   if (mobile) → entrega small.webp
   if (tablet) → entrega medium.webp
   if (desktop) → entrega large.webp
   ```

**Beneficios:**
- ✅ Artista no piensa en formatos ni compresión
- ✅ Sitio carga ultra-rápido
- ✅ Calidad visual impecable en todos los dispositivos

---

### 1.3 Seguridad Robusta (Diferenciador Clave)

#### **A. Protección Anti-Descarga**

**Nivel 1: Deshabilitación Básica**
- Bloqueo de clic derecho
- Bloqueo de arrastrar imagen
- Atributo `oncontextmenu="return false"`

**Nivel 2: Tecnología de Tiling (Mosaico)**
```
┌─────────┬─────────┬─────────┐
│ tile1   │ tile2   │ tile3   │
├─────────┼─────────┼─────────┤
│ tile4   │ tile5   │ tile6   │
├─────────┼─────────┼─────────┤
│ tile7   │ tile8   │ tile9   │
└─────────┴─────────┴─────────┘
```

**Implementación:**
- La imagen completa se divide en 9-16 "teselas"
- El navegador las renderiza como canvas HTML5
- Imposible descargar imagen completa (no existe como archivo único)
- Similar a Google Maps

**Código Base:**
```javascript
// Canvas con tiles que impiden descarga
const canvas = document.getElementById('gallery-canvas');
const ctx = canvas.getContext('2d');
// Renderiza tiles sin exponer imagen original
```

#### **B. Marcas de Agua Nativas**

**Panel de Configuración Simple:**
```
┌─────────────────────────────────┐
│ ☑ Activar Marca de Agua         │
│                                  │
│ Tipo: ○ Texto  ● Logo/Imagen   │
│                                  │
│ [📁 Subir Logo]                 │
│                                  │
│ Opacidad:    [====•----] 40%    │
│ Posición:    [▼ Centro]         │
│ Tamaño:      [===•-----] 30%    │
└─────────────────────────────────┘
```

**Aplicación:**
- Se aplica automáticamente al generar versiones web
- Opción de marca de agua solo en previews (versión pagada sin marca)

---

### 1.4 Funcionalidades Básicas MVP

#### **Esenciales:**
- ✅ **Dominio personalizado:** `fotografo.com` → apunta a su galería
- ✅ **Edición inline:** Click directo en texto para editar biografía
- ✅ **Formulario de contacto:** Email funcional integrado
- ✅ **SEO básico:** Meta tags automáticos por cada galería
- ✅ **Dark Mode:** Toggle automático
- ✅ **Responsive:** Mobile-first design

#### **Panel Admin Básico:**
```
Dashboard simple con:
- Subir/Eliminar imágenes
- Reordenar fotos (drag & drop)
- Editar perfil
- Ver estadísticas básicas (visitas)
- Activar/desactivar marca de agua
```

---

## 🗂️ Estructura de Datos (Fase 1)

### Archivo: `lumen_datos.php`

```php
<?php
$lumen_portfolios = [
    'fotosjuan' => [
        'nombre_marca' => 'FotosJuan Photography',
        'nombre_artista' => 'Juan Martínez',
        'biografia' => 'Fotógrafo profesional especializado en bodas...',
        'email' => 'info@fotosjuan.com',
        'telefono' => '+54 11 5555-1234',
        'redes' => [
            'instagram' => '@fotosjuan',
            'facebook' => 'fotosjuanphoto',
            'behance' => 'juanmartinez'
        ],
        'plantilla' => 'masonry', // masonry|grid|carousel|fullscreen|tabs
        'marca_agua' => [
            'activa' => true,
            'tipo' => 'logo', // logo|texto
            'archivo' => 'fotosjuan_watermark.png',
            'opacidad' => 40,
            'posicion' => 'centro',
            'tamaño' => 30
        ],
        'galerias' => [
            'bodas' => [
                'nombre' => 'Bodas',
                'descripcion' => 'Momentos únicos del día más especial',
                'fotos' => [
                    ['archivo' => 'boda_001.jpg', 'titulo' => 'María & Pedro', 'orden' => 1],
                    ['archivo' => 'boda_002.jpg', 'titulo' => 'Ceremonia', 'orden' => 2],
                    // ...
                ]
            ],
            'eventos' => [
                'nombre' => 'Eventos Corporativos',
                'descripcion' => 'Cobertura profesional',
                'fotos' => [...]
            ]
        ],
        'configuracion' => [
            'dominio_personalizado' => 'fotosjuan.com',
            'tema_color' => '#0ea5e9',
            'dark_mode' => true
        ]
    ]
];
?>
```

---

## 🎨 Arquitectura de Archivos

```
validarcert/
├── lumen.php                    # Vista pública de galería
├── lumen_datos.php              # Base de datos simulada
├── lumen_admin.php              # Dashboard del fotógrafo
├── lumen/
│   ├── uploads/
│   │   ├── originals/           # Archivos originales (no públicos)
│   │   │   └── fotosjuan/
│   │   │       └── boda_001.TIFF
│   │   ├── web/                 # Versiones web optimizadas
│   │   │   └── fotosjuan/
│   │   │       ├── boda_001_large.webp
│   │   │       ├── boda_001_medium.webp
│   │   │       └── boda_001_small.webp
│   │   └── watermarks/          # Logos de marca de agua
│   │       └── fotosjuan_logo.png
│   ├── templates/
│   │   ├── masonry.php
│   │   ├── grid.php
│   │   ├── carousel.php
│   │   ├── fullscreen.php
│   │   └── tabs.php
│   └── includes/
│       ├── image_processor.php  # Motor de conversión ImageMagick
│       ├── security.php         # Tiling y protecciones
│       └── watermark.php        # Aplicación de marcas de agua
```

---

## 🚀 Roadmap de Implementación

### **FASE 1: MVP (Actual)**
**Objetivo:** Galería funcional, segura y hermosa

**Tareas:**
1. ✅ Crear estructura base `lumen.php` + `lumen_datos.php`
2. ✅ Implementar plantilla Masonry (responsive)
3. ✅ Motor de procesamiento de imágenes (ImageMagick)
4. ✅ Sistema de tiling para seguridad
5. ✅ Marca de agua configurable
6. ✅ Formulario de contacto funcional
7. ✅ Dashboard admin básico (CRUD de imágenes)

**Tiempo estimado:** 2-3 semanas

---

### **FASE 2: Evolución Post-Lanzamiento**
**Sin IA compleja - Características demandadas**

1. **Más Plantillas** (+ 3-5 diseños nuevos)
2. **Galerías Privadas con Contraseña**
   ```
   Ejemplo: /lumen?id=fotosjuan&galeria=boda-cliente-123&pass=abc123
   ```
3. **Estadísticas Simplificadas**
   - Panel con visitas por galería
   - Gráfico simple de tráfico mensual
4. **Personalización de Colores/Tipografías**
   - Selector de paleta de colores
   - 3-5 opciones de fuentes

**Tiempo estimado:** 1-2 meses

---

### **FASE 3: Introducción a IA (Largo Plazo)**
**Solo cuando el producto base sea sólido**

1. **Curador Inteligente**
   - Sugiere orden óptimo de fotos
   - Análisis de composición y colores
2. **Etiquetado Automático**
   - Reconocimiento de contenido (boda, retrato, paisaje)
3. **Asistente de Texto**
   - Generación de biografías profesionales
   - Descripciones de galerías

**Tiempo estimado:** 6+ meses (requiere investigación)

---

## 💡 Diferenciadores Clave de Lumen

| Característica | Competencia | Lumen |
|----------------|-------------|-------|
| **Sube cualquier formato** | Solo JPG/PNG | ✅ TIFF, RAW, cualquier formato |
| **Conversión automática** | Manual | ✅ Automática invisible |
| **Seguridad anti-descarga** | Clic derecho bloqueado | ✅ Tiling avanzado |
| **Velocidad de carga** | Lenta con imágenes pesadas | ✅ Ultra-rápida con WebP |
| **Marca de agua nativa** | Plugin externo | ✅ Integrada y configurable |
| **Setup** | 1-2 horas | ✅ 5 minutos |

---

## 🎯 Mercado Objetivo (MVP)

**Primarios:**
- Fotógrafos freelance
- Estudios fotográficos pequeños/medianos
- Artistas visuales

**Secundarios:**
- Ilustradores digitales
- Diseñadores gráficos
- Arquitectos (renders)

---

## 📊 Métricas de Éxito (Fase 1)

- ✅ Tiempo de setup < 10 minutos
- ✅ Carga de página < 2 segundos
- ✅ 0 quejas sobre formatos no soportados
- ✅ 95%+ satisfacción con seguridad de imágenes
- ✅ 10+ clientes activos en primer mes

---

## 🔧 Stack Tecnológico

**Frontend:**
- Tailwind CSS (diseño responsive)
- JavaScript vanilla (interactividad)
- Canvas API (tiling de seguridad)

**Backend:**
- PHP 7.4+ (procesamiento)
- ImageMagick (conversión de imágenes)
- Arrays PHP → MySQL (migración futura)

**Integraciones:**
- TarjetaDigital (herencia de datos)
- Sistema de validación existente (para certificados)

---

## 📝 Notas Técnicas Importantes

### Consideraciones de Seguridad:
1. **Nunca exponer carpeta `/originals/` públicamente**
   - Configurar `.htaccess` para bloquear acceso directo
2. **Ofuscar nombres de archivos web**
   - `boda_001.jpg` → `f8e9a3b2c1d0.webp`
3. **Implementar rate limiting** en carga de imágenes
4. **Validación robusta** de tipos de archivo en servidor

### Optimizaciones de Rendimiento:
1. **Lazy loading** de imágenes (solo cargar las visibles)
2. **Precarga inteligente** (próximas 3 imágenes)
3. **CDN** para versiones web (fase futura)
4. **Compresión Gzip/Brotli** en servidor

---

## 🎬 Próximos Pasos Inmediatos

### ✅ FASE 1 - Semana 1:
1. Crear `lumen.php` con estructura base multi-tenant
2. Diseñar plantilla Masonry responsive
3. Implementar `lumen_datos.php` con cliente demo (fotosjuan)
4. Sistema básico de carga de imágenes

### ✅ FASE 1 - Semana 2:
1. Motor de procesamiento ImageMagick
2. Generación automática de versiones web
3. Sistema de tiling para seguridad
4. Marca de agua configurable

### ✅ FASE 1 - Semana 3:
1. Dashboard admin básico
2. Formulario de contacto
3. Testing completo
4. Documentación

---

**Fecha de creación:** 12 de Octubre, 2025
**Última actualización:** 12 de Octubre, 2025
**Estado:** 🟡 En Desarrollo - Fase 1
