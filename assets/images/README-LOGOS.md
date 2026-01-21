# Guía de Logos Verumax

Esta carpeta contiene los logos y assets visuales de Verumax y de las instituciones asociadas.

## 📐 Tamaños y Formatos Requeridos

### Logo Principal Verumax

Preparar los siguientes archivos en esta carpeta (`assets/images/`):

#### 1. Logo para Header/Navegación
- **Archivo**: `logo-verumax-150.png`
- **Tamaño**: 150x150px
- **Formato**: PNG con fondo transparente
- **Uso**: Se escala a 40x40px en headers y navegación
- **Aplicado con clases**: `h-10 w-10 rounded-full`

#### 2. Logo Mediano para Tarjetas
- **Archivo**: `logo-verumax-200.png`
- **Tamaño**: 200x200px
- **Formato**: PNG con fondo transparente
- **Uso**: Se escala a 80x80px en tarjetas y secciones de detalle
- **Aplicado con clases**: `h-20 w-20 rounded-2xl shadow-lg ring-4 ring-white`

#### 3. Logo para Documentos PDF
- **Archivo**: `logo-verumax-300.png`
- **Tamaño**: 300x300px (alta resolución)
- **Formato**: PNG con fondo transparente
- **Uso**: Generación de PDFs, certificados, analíticos
- **Dimensiones en PDF**: 150x150px (300dpi para calidad de impresión)

#### 4. Logo Vectorial (Opcional pero Recomendado)
- **Archivo**: `logo-verumax.svg`
- **Formato**: SVG
- **Uso**: Escalable para cualquier tamaño, ideal para web

---

## 🏫 Logos Institucionales

Cada institución debe tener su logo en su carpeta correspondiente:

### Ubicación
```
[institucion]/logo.png
```

Ejemplos:
- `sajur/logo.png` - Logo de SAJuR (Sociedad Argentina de Justicia Restaurativa)
- `liberte/logo.png` - Logo de Cooperativa Liberté
- `fotosjuan/logo.png` - Logo de Fotos Juan

### Especificaciones
- **Tamaño recomendado**: 150x150px
- **Formato**: PNG con fondo transparente
- **Relación de aspecto**: 1:1 (cuadrado)
- **Fallback**: Si no existe, se usa placeholder generado automáticamente

### Placeholder Automático

Si una institución no tiene logo, el sistema genera uno automático:

- **SAJuR**: Fondo verde oscuro (#006837) con texto "SJ"
- **Liberté**: Fondo verde claro (#16a34a) con texto "L"
- **Otras**: Fondo azul (#3b82f6) con texto "?"

URL de placeholder: `https://placehold.co/80x80/{color}/ffffff?text={iniciales}`

---

## 🎨 Consideraciones de Diseño

### Transparencia
Todos los logos PNG deben tener fondo transparente para adaptarse a:
- Fondos oscuros (modo dark)
- Fondos claros (modo light)
- Tarjetas con colores institucionales

### Espacio de Respiro
Dejar mínimo 10% de padding interno en el diseño del logo para que no quede pegado a los bordes cuando se apliquen las clases de Tailwind CSS.

### Colores
- **Logos monocromáticos**: Considerar que funcionan mejor con fondos variables
- **Logos a color**: Asegurar contraste suficiente en ambos modos (light/dark)

---

## 📦 Archivos Actuales en Raíz

Los siguientes archivos de logo se encuentran actualmente en la raíz del proyecto (`D:\appVerumax\`):

- `logo VERUMax.png` - Logo principal con naming original
- `VERUMAX.png` - Logo alternativo
- `VERUMax minus.png` - Logo simplificado
- `isologo mayus.jpg` - Isotipo mayúsculas (formato JPG)
- `escudo solo.png` - Solo el escudo/badge
- `vx.png` - Monograma VX
- `tilde.png` - Elemento de marca tilde

### ⚠️ Acción Recomendada

Seleccionar uno de estos archivos y crear las versiones optimizadas con los tamaños especificados arriba (150px, 200px, 300px) para colocarlos en `assets/images/`.

---

## 🔧 Implementación en Código

### Ejemplo de uso en PHP:

```php
// Header con logo institucional
<img src="<?php echo $institucion; ?>/logo.png"
     onerror="this.src='<?php echo $logo_placeholder; ?>'"
     alt="Logo <?php echo $nombre_institucion; ?>"
     class="h-10 w-10 rounded-full">

// Logo en página de detalle
<img src="<?php echo $institucion; ?>/logo.png"
     onerror="this.src='https://placehold.co/80x80/3b82f6/ffffff?text=?'"
     alt="Logo Institución"
     class="h-20 w-20 rounded-2xl shadow-lg ring-4 ring-white">

// Logo Verumax en landing principal
<img src="assets/images/logo-verumax-150.png"
     alt="Verumax"
     class="h-10 w-10">
```

---

## ✅ Checklist de Implementación

- [ ] Crear `logo-verumax-150.png` (150x150px, PNG transparente)
- [ ] Crear `logo-verumax-200.png` (200x200px, PNG transparente)
- [ ] Crear `logo-verumax-300.png` (300x300px, PNG transparente)
- [ ] (Opcional) Crear `logo-verumax.svg` (formato vectorial)
- [ ] Verificar que cada institución tenga su `[institucion]/logo.png`
- [ ] Actualizar referencias en código si es necesario
- [ ] Probar visualización en modo light y dark
- [ ] Verificar generación correcta en PDFs

---

## 📝 Notas

- Los logos se cargan con `onerror` fallback para mostrar placeholder si falla la carga
- El sistema usa Tailwind CSS para dimensionamiento responsivo
- Los PDFs requieren logos de alta resolución (300px o 300dpi)
- Mantener logos optimizados (comprimir PNG sin pérdida de calidad)

---

**Última actualización**: 2025-10-22
**Versión Verumax**: 2.0.0
