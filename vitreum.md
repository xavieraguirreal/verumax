# VITREUM - CATÁLOGO DIGITAL DE PRODUCTOS

**Archivo Landing:** `vitreum.php` (pendiente)
**Archivo Aplicación:** `vitreum_app.php` (pendiente desarrollo)
**Archivo Datos:** `vitreum_datos.php`
**Estado:** 📋 Planeado | 🔜 Desarrollo pendiente

---

## 📋 CONCEPTO GENERAL

### Tagline Principal

**"Tu Catálogo de Productos, Hermoso y Profesional"**

### Propuesta de Valor

**"Mostrá tus Productos sin Complicaciones de E-commerce"**

Vitreum NO es una tienda online (eso es Emporium). Es un **catálogo digital integrado** directamente en tu sitio Identitas para exhibir productos de forma profesional **sin necesidad de vender online**.

**¿Qué incluye?**

1. **Catálogo Visual Integrado** (tunombre.verumax.com/catalogo)
2. **Gestión Simple de Productos** (fotos, descripciones, precios opcionales)
3. **Categorías Personalizables** (organización por tipo, línea, colección)
4. **Formulario de Consulta por Producto** (botón "Consultar" en cada producto)
5. **Vista Responsiva** (grid adaptable, mobile-first)
6. **Sin Carrito ni Pagos** (exhibición pura, ventas por contacto)

---

## 🎯 DIFERENCIADOR CLAVE: CATÁLOGO INTEGRADO EN IDENTITAS

### Filosofía Central

**El problema:** Muchos profesionales tienen productos para mostrar pero NO necesitan vender online (precios variables, productos a pedido, consultoría previa necesaria).

**La solución:** Vitreum es un catálogo visual hermoso que se integra directamente en el sitio Identitas, sin la complejidad ni costo de una tienda completa.

### Diferencia con Otras Soluciones VERUMax

| Producto | Propósito | Integración | Ventas |
|----------|-----------|-------------|--------|
| **Vitreum** | Catálogo de productos (exhibición) | ✅ Integrado en Identitas | ❌ No, solo consulta |
| **Emporium** | Tienda online completa | ⚠️ Instancia separada | ✅ Carrito, pagos, pedidos |
| **Lumen** | Portfolio fotográfico | ✅ Integrado en Identitas | ❌ Solo exhibe fotos |
| **Opera** | Portfolio de servicios/proyectos | ✅ Integrado en Identitas | ❌ Casos de estudio |

---

## 🎨 DIFERENCIADOR ÚNICO: INTEGRACIÓN TOTAL CON IDENTITAS

### Experiencia Unificada

El catálogo Vitreum NO es un sitio separado. Se integra **directamente en la landing page de Identitas**:

**Menú del sitio:**
```
Inicio | Sobre Mí | Servicios | [Catálogo] | Contacto
```

**URL:**
```
tunombre.verumax.com/catalogo
```

**Hereda automáticamente:**
- ✅ Branding (colores, tipografías, logo)
- ✅ Información de contacto
- ✅ Diseño y estructura del sitio
- ✅ Formulario de contacto
- ✅ Footer con redes sociales

---

## 💼 PÚBLICO OBJETIVO

### Artesanos / Makers

**Ejemplos:**
- Cerámica artesanal
- Joyería hecha a mano
- Productos de cuero
- Velas aromáticas
- Jabones artesanales

**Por qué Vitreum (no Emporium):**
- Productos únicos con precios variables
- Venta por consulta/pedido personalizado
- No necesitan gestión de stock automático
- Prefieren contacto directo con clientes

---

### Artistas Visuales

**Ejemplos:**
- Pinturas originales
- Esculturas
- Ilustraciones
- Fotografía de arte
- Instalaciones

**Por qué Vitreum (no Emporium):**
- Cada obra es única (no hay stock)
- Precio se consulta según cliente/tamaño/ubicación
- Requiere conversación previa a venta

**Integración con Lumen:**
- **Lumen:** Portfolio completo de obra (exhibición artística)
- **Vitreum:** Catálogo de obra disponible para venta

---

### Profesionales con Productos Físicos

**Ejemplos:**
- Arquitecto: catálogo de muebles de diseño
- Nutricionista: productos de su línea (libros, guides, suplementos)
- Coach: materiales impresos (workbooks, planners)
- Consultor: herramientas y kits

**Por qué Vitreum:**
- Productos complementarios a servicios principales
- Venta no es el foco, sino complemento
- No justifica costo/complejidad de tienda completa

---

### Servicios que se Presentan como "Productos"

**Ejemplos:**
- Paquetes de consultoría (Bronze, Silver, Gold)
- Planes de servicios recurrentes
- Talleres y capacitaciones
- Sesiones fotográficas (paquetes)

**Por qué Vitreum:**
- Cada "producto" requiere consulta previa
- Personalización según cliente
- Precio variable según alcance

---

## 🏗️ ARQUITECTURA TÉCNICA

### Integración con Identitas

Vitreum NO es un sistema separado. Es un **módulo que se activa** en Identitas:

```
Identitas (Base)
├── Home
├── Sobre Mí
├── Servicios
├── [VITREUM - Catálogo] ← Se activa según plan
├── Blog (Scripta)
└── Contacto
```

**Archivos:**
```
verumax/
├── identitas.php                    # Landing principal
├── vitreum/
│   ├── vitreum_datos.php           # Base de datos de productos
│   ├── vitreum_catalogo.php        # Vista pública del catálogo
│   ├── vitreum_app.php             # Dashboard de gestión
│   ├── includes/
│   │   ├── product_manager.php     # CRUD de productos
│   │   └── image_handler.php       # Gestión de imágenes
│   └── uploads/
│       └── {id_usuario}/
│           ├── producto_001.jpg
│           ├── producto_002.jpg
│           └── producto_003.jpg
```

---

### Base de Datos (Estructura MySQL Futura)

#### Tabla: `vitreum_productos`
```sql
CREATE TABLE vitreum_productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria VARCHAR(100),
    precio_mostrar BOOLEAN DEFAULT FALSE,
    precio_valor DECIMAL(10,2),
    precio_texto VARCHAR(100), -- "Consultar", "Desde $500", etc.
    imagen_principal VARCHAR(500),
    estado ENUM('publicado', 'borrador', 'agotado') DEFAULT 'publicado',
    orden INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES identitas_usuarios(id)
);
```

#### Tabla: `vitreum_imagenes`
```sql
CREATE TABLE vitreum_imagenes (
    id_imagen INT AUTO_INCREMENT PRIMARY KEY,
    id_producto INT NOT NULL,
    url_imagen VARCHAR(500),
    orden INT DEFAULT 0,
    FOREIGN KEY (id_producto) REFERENCES vitreum_productos(id_producto) ON DELETE CASCADE
);
```

#### Tabla: `vitreum_categorias`
```sql
CREATE TABLE vitreum_categorias (
    id_categoria INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    nombre VARCHAR(100),
    descripcion TEXT,
    orden INT DEFAULT 0,
    FOREIGN KEY (id_usuario) REFERENCES identitas_usuarios(id)
);
```

---

### Estructura de Datos (Arrays PHP - Fase 1)

**Archivo:** `vitreum_datos.php`

```php
<?php
$vitreum_catalogos = [
    'artesaniasmaria' => [
        'nombre_marca' => 'Artesanías María',
        'biografia' => 'Cerámica artesanal hecha a mano desde 2010',
        'email' => 'maria@artesanias.com',
        'whatsapp' => '+54 9 11 5555-1234',
        'mostrar_precios' => false, // true o false
        'texto_consulta' => 'Consultar precio',
        'categorias' => [
            'tazas' => [
                'nombre' => 'Tazas',
                'descripcion' => 'Tazas de cerámica esmaltada',
                'productos' => [
                    [
                        'id' => 'prod_001',
                        'nombre' => 'Taza Artesanal Azul Cobalto',
                        'descripcion' => 'Taza de cerámica esmaltada en azul cobalto. 350ml. Apta lavavajillas.',
                        'precio' => 3500, // ARS (opcional)
                        'imagenes' => ['taza_azul_1.jpg', 'taza_azul_2.jpg'],
                        'estado' => 'disponible' // disponible|agotado|por_encargo
                    ],
                    [
                        'id' => 'prod_002',
                        'nombre' => 'Taza Rústica Natural',
                        'descripcion' => 'Taza de arcilla natural sin esmalte. 300ml.',
                        'precio' => 2800,
                        'imagenes' => ['taza_rustica.jpg'],
                        'estado' => 'disponible'
                    ]
                ]
            ],
            'platos' => [
                'nombre' => 'Platos',
                'descripcion' => 'Platos decorativos y funcionales',
                'productos' => [
                    [
                        'id' => 'prod_003',
                        'nombre' => 'Plato Decorativo Mandala',
                        'descripcion' => 'Plato decorativo de 30cm con diseño mandala pintado a mano.',
                        'precio' => 8500,
                        'imagenes' => ['plato_mandala.jpg'],
                        'estado' => 'disponible'
                    ]
                ]
            ]
        ],
        'configuracion' => [
            'tema_color' => '#8B4513', // marrón tierra
            'plantilla' => 'grid', // grid|masonry|lista
            'productos_por_pagina' => 12
        ]
    ]
];
?>
```

---

## 📱 FLUJO DE USUARIO

### Vista Pública (tunombre.verumax.com/catalogo)

#### Página Principal: Vista de Categorías (opcional)

Si hay categorías definidas:
```
┌─────────────────────────────────────────┐
│  CATÁLOGO DE PRODUCTOS                   │
├─────────────────────────────────────────┤
│  [Tazas] [Platos] [Bowls] [Sets]       │ ← Tabs de categorías
├─────────────────────────────────────────┤
│  ┌──────┐  ┌──────┐  ┌──────┐           │
│  │ IMG  │  │ IMG  │  │ IMG  │           │
│  │      │  │      │  │      │           │
│  │Taza  │  │Taza  │  │Taza  │           │
│  │Azul  │  │Verde │  │Roja  │           │
│  │$3500 │  │Cons. │  │$2800 │           │
│  │[Cons]│  │[Cons]│  │[Cons]│           │
│  └──────┘  └──────┘  └──────┘           │
└─────────────────────────────────────────┘
```

#### Card de Producto (Vista Grid)

```
┌────────────────────┐
│                    │
│   [IMAGEN]         │ ← Imagen principal
│                    │
├────────────────────┤
│ Taza Artesanal     │ ← Nombre
│ Azul Cobalto       │
├────────────────────┤
│ $3,500             │ ← Precio (si está habilitado)
│ o "Consultar"      │
├────────────────────┤
│ [Consultar]        │ ← Botón de acción
└────────────────────┘
```

---

#### Página Detalle de Producto

**Click en producto abre modal o página dedicada:**

```
┌──────────────────────────────────────────┐
│  ✕ Cerrar                                 │
├──────────────────────────────────────────┤
│  ┌────────────┐  Taza Artesanal          │
│  │            │  Azul Cobalto             │
│  │  IMAGEN    │                           │
│  │  PRINCIPAL │  $3,500                   │
│  │            │                           │
│  └────────────┘  Taza de cerámica        │
│                  esmaltada en azul        │
│  [img] [img]     cobalto. 350ml.         │
│  [img] [img]     Apta lavavajillas.      │
│                                           │
│  ┌──────────────────────────┐            │
│  │ 📧 Consultar por WhatsApp │            │
│  └──────────────────────────┘            │
│  ┌──────────────────────────┐            │
│  │ 📧 Enviar Consulta        │            │
│  └──────────────────────────┘            │
└──────────────────────────────────────────┘
```

---

### Acción de "Consultar"

**Opción 1: WhatsApp (preferida)**
```
Click en "Consultar por WhatsApp"
    ↓
Abre WhatsApp Web/App
    ↓
Mensaje pre-rellenado:
"Hola! Me interesa el producto:
*Taza Artesanal Azul Cobalto*
¿Está disponible?"
```

**Opción 2: Formulario de Contacto**
```
Click en "Enviar Consulta"
    ↓
Abre formulario:
├── Nombre: [input]
├── Email: [input]
├── Teléfono: [input]
├── Mensaje: [textarea pre-rellenado con nombre del producto]
└── [Enviar Consulta]
    ↓
Email al dueño del catálogo
Copia al cliente
Contacto se añade a Nexus CRM
```

---

## 🚀 DESARROLLO DE vitreum_app.php

### Funcionalidades Mínimas (MVP)

**[ ] Dashboard Principal**
- Resumen: Total productos, por categoría, consultas recibidas
- Vista rápida de productos
- Botón "Agregar Nuevo Producto"

**[ ] Gestión de Productos**
- Formulario de producto:
  - Nombre (requerido)
  - Descripción (textarea)
  - Categoría (selector)
  - Precio: [checkbox] Mostrar precio → [input] valor
  - Texto alternativo: "Consultar", "Desde $X", "A pedido"
  - Estado: Disponible / Agotado / Por encargo
  - Subida de imágenes (múltiples)
- Listado de productos (editar, eliminar, duplicar)
- Reordenamiento (drag & drop)

**[ ] Gestión de Categorías**
- Crear/Editar/Eliminar categorías
- Reordenar categorías
- Asignar productos a categorías

**[ ] Configuración de Catálogo**
- Mostrar/Ocultar precios globalmente
- Texto por defecto ("Consultar", "A pedido", etc.)
- Plantilla de visualización (Grid / Masonry / Lista)
- Productos por página
- Color de tema del catálogo

**[ ] Gestión de Consultas**
- Listado de consultas recibidas
- Ver detalle (producto consultado, datos del cliente)
- Marcar como respondida
- Integración con Nexus (contacto se añade automáticamente)

---

## 💡 CASOS DE USO POR INDUSTRIA

### Ceramista / Artesana

**Plan recomendado:** Premium (Vitreum incluido)

**Configuración:**
- Mostrar precios: ✅ Sí
- Categorías: Tazas, Platos, Bowls, Sets
- Productos: 40 piezas únicas
- Botón: "Consultar disponibilidad" (WhatsApp)

**ROI:** Genera consultas calificadas sin necesidad de tienda completa.

---

### Artista Visual

**Plan recomendado:** Premium

**Configuración:**
- Mostrar precios: ❌ No (precio se consulta según tamaño/cliente)
- Categorías: Pinturas Originales, Copias Limitadas, Ilustraciones
- Productos: 25 obras disponibles
- Botón: "Consultar precio y disponibilidad"

**Integración:**
- **Lumen:** Portfolio completo (toda la obra)
- **Vitreum:** Catálogo de obra en venta

---

### Nutricionista con Línea de Productos

**Plan recomendado:** Premium

**Configuración:**
- Mostrar precios: ✅ Sí
- Categorías: Libros, Guías Digitales, Planners, Consultas
- Productos: 8 productos/servicios
- Botón: "Solicitar información"

**Uso:**
- Productos físicos (libro impreso)
- Productos digitales (guías PDF)
- Servicios "empaquetados" (planes de consultoría)

---

### Arquitecto - Muebles de Diseño

**Plan recomendado:** Excellens

**Configuración:**
- Mostrar precios: ⚠️ "Desde $X" (orientativo)
- Categorías: Sillas, Mesas, Estanterías, A Medida
- Productos: 15 diseños
- Botón: "Solicitar cotización"

**Uso:** Cada mueble se fabrica a pedido, precio varía según materiales y tamaño.

---

## 🔗 INTEGRACIONES CON ECOSISTEMA VERUMAX

### Con Identitas (Core)
- Vitreum se integra como sección del sitio Identitas
- Pestaña "Catálogo" o "Productos" en menú
- Hereda todo el branding (colores, fuentes, logo)
- URL: tunombre.verumax.com/catalogo

### Con Nexus (CRM)
- Consultas de productos crean contacto automáticamente
- Tag: "Consultó producto: [nombre]"
- Permite seguimiento de interesados
- Segmentación por productos de interés

### Con Communica (Email Marketing)
- Email automático al recibir consulta
- Campañas a "Consultantes de [categoría]"
- Newsletter de productos nuevos
- Follow-up automatizado

### Con Emporium (Tienda)
**Flujo de upgrade:**
1. Cliente usa Vitreum (catálogo simple)
2. Negocio crece, necesita vender online
3. Upgrade a Emporium (tienda completa con PrestaShop)
4. Importación de productos desde Vitreum a Emporium

**Uso simultáneo (casos especiales):**
- Vitreum: Productos personalizados (a consulta)
- Emporium: Productos estándar (venta directa)

---

## 💰 MODELO DE NEGOCIO

### Inclusión en Planes Identitas

| Plan | Vitreum Incluido | Productos | Categorías |
|------|-----------------|-----------|------------|
| Basicum | ❌ No | - | - |
| Premium | ✅ Sí | Hasta 50 | Hasta 5 |
| Excellens | ✅ Sí | Hasta 200 | Hasta 10 |
| Supremus | ✅ Sí | Ilimitado | Ilimitado |

**Sin costo adicional** - Incluido en los planes Premium+

---

## 📊 MÉTRICAS DE ÉXITO (Fase 1)

- ✅ Tiempo de carga de catálogo < 15 minutos
- ✅ Tiempo de creación de producto < 3 minutos
- ✅ 80%+ de consultas llegan correctamente
- ✅ 20+ catálogos activos en primer mes
- ✅ 10%+ de visitantes del catálogo realizan consulta

---

## 🔧 STACK TECNOLÓGICO

**Frontend:**
- Tailwind CSS (diseño responsive)
- JavaScript vanilla (interactividad)
- Lightbox para galería de imágenes
- Grid CSS nativo (no frameworks pesados)

**Backend:**
- PHP 7.4+ (procesamiento)
- MySQL (futuro, fase 1 con arrays)
- ImageMagick (optimización de imágenes)

**Integraciones:**
- WhatsApp API (botón de consulta)
- API de Nexus (añadir contactos)
- API de Communica (emails)

---

## 🚀 ROADMAP DE IMPLEMENTACIÓN

### FASE 1: MVP (1-2 meses)
**Objetivo:** Catálogo funcional integrado en Identitas

**Tareas:**
1. [ ] Estructura de datos `vitreum_datos.php`
2. [ ] Vista pública de catálogo (grid responsive)
3. [ ] Modal/página de detalle de producto
4. [ ] Formulario de consulta
5. [ ] Integración con WhatsApp
6. [ ] Dashboard básico (CRUD productos)
7. [ ] Sistema de categorías

---

### FASE 2: Gestión Avanzada (1 mes post-MVP)
**Objetivo:** Herramientas de gestión profesionales

**Tareas:**
1. [ ] Reordenamiento drag & drop
2. [ ] Carga masiva de productos (CSV)
3. [ ] Gestión de consultas recibidas
4. [ ] Integración completa con Nexus
5. [ ] Estadísticas (productos más consultados)

---

### FASE 3: Mejoras Visuales (2-3 meses post-lanzamiento)

**Características adicionales:**
1. [ ] Plantilla Masonry (estilo Pinterest)
2. [ ] Plantilla Lista (con descripciones)
3. [ ] Filtros por categoría/precio
4. [ ] Búsqueda de productos
5. [ ] Vista "Productos destacados" en home de Identitas

---

## 🎯 DIFERENCIADORES vs COMPETENCIA

| Característica | Instagram como catálogo | Vitreum |
|----------------|------------------------|---------|
| **Profesionalismo** | Informal | ✅ Sitio web profesional |
| **Integración con sitio** | Link externo | ✅ Integrado en Identitas |
| **Consultas organizadas** | DMs caóticos | ✅ CRM Nexus |
| **Control de diseño** | Limitado | ✅ Total |
| **SEO** | No indexable | ✅ Google indexa productos |
| **Propiedad de datos** | De Instagram | ✅ Tuya |

---

## 📝 NOTAS IMPORTANTES

### Diferencia con Lumen
- **Lumen:** Portfolio fotográfico para artistas visuales (foco: exhibir arte/fotos)
- **Vitreum:** Catálogo de productos para venta (foco: generar consultas comerciales)

### Diferencia con Emporium
- **Vitreum:** Catálogo simple sin carrito ni pagos (incluido en Premium+, sin costo)
- **Emporium:** Tienda completa con PrestaShop (add-on premium, $300+ USD/año)

### Cuándo usar Vitreum vs Emporium

**Usar Vitreum si:**
- ✅ Productos únicos o personalizados
- ✅ Precios variables según cliente
- ✅ Requiere consulta previa
- ✅ Menos de 100 productos
- ✅ No necesita gestión automática de stock

**Usar Emporium si:**
- ✅ Productos estandarizados
- ✅ Precios fijos
- ✅ Venta directa online
- ✅ Más de 100 productos
- ✅ Necesita automatización de ventas

### Migración Vitreum → Emporium
**Flujo automático:**
1. Cliente exporta productos desde Vitreum
2. Sistema convierte a formato PrestaShop CSV
3. Importación automática a nueva instancia Emporium
4. Cliente decide si mantener ambos o solo Emporium

---

**Fecha de creación:** 8 de noviembre, 2025
**Última actualización:** 8 de noviembre, 2025
**Estado:** 📋 Planeado - Desarrollo pendiente
