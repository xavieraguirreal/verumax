# Arquitectura de Templates + Bloques para Identitas

## Concepto

El cliente puede:
1. **Elegir un template** (layout predefinido)
2. **Editar textos** de cada bloque (con CKEditor simple: negrita, cursiva, listas)
3. **Elegir paleta de colores** (ya existe)

El HTML/CSS de la estructura queda fijo, solo cambian los contenidos.

---

## Ejemplo Visual

### Template "Clásico" para Sobre Nosotros:
```
┌─────────────────────────────────────────────────────────┐
│ [BLOQUE: Hero]                                          │
│  Título: "Sobre Nosotros"                               │
│  Subtítulo: "Conoce nuestra historia"                   │
├─────────────────────────────────────────────────────────┤
│ [BLOQUE: Misión]                    │ [BLOQUE: Stats]   │
│  Título: "Nuestra Misión"           │  ┌─────┐ ┌─────┐  │
│  Texto: <p>La SAJuR es...</p>       │  │Visión│ │Valor│  │
│  Link: "Conoce más"                 │  └─────┘ └─────┘  │
│                                     │  ┌─────┐ ┌─────┐  │
│                                     │  │Impac│ │Forma│  │
│                                     │  └─────┘ └─────┘  │
└─────────────────────────────────────────────────────────┘
```

### Template "Moderno" para Sobre Nosotros:
```
┌─────────────────────────────────────────────────────────┐
│ [BLOQUE: Hero Full]                                     │
│  Imagen de fondo + Título centrado                      │
├─────────────────────────────────────────────────────────┤
│ [BLOQUE: Stats] - 4 columnas horizontales               │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌──────┐                    │
│  │Visión│ │Valores│ │Impacto│ │Formac│                   │
│  └──────┘ └──────┘ └──────┘ └──────┘                    │
├─────────────────────────────────────────────────────────┤
│ [BLOQUE: Misión] - Texto centrado ancho completo        │
└─────────────────────────────────────────────────────────┘
```

---

## Estructura de Base de Datos

### Tabla: `identitas_templates`
Define los templates disponibles.

```sql
CREATE TABLE identitas_templates (
    id_template INT AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    thumbnail_url VARCHAR(255),
    pagina VARCHAR(50) NOT NULL,  -- 'sobre-nosotros', 'servicios', 'contacto', 'home'
    activo TINYINT(1) DEFAULT 1,
    orden INT DEFAULT 0,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Tabla: `identitas_template_bloques`
Define qué bloques tiene cada template y en qué orden.

```sql
CREATE TABLE identitas_template_bloques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_template INT NOT NULL,
    tipo_bloque VARCHAR(50) NOT NULL,  -- 'hero', 'mision', 'stats_2x2', 'servicios_grid', etc.
    orden INT DEFAULT 0,
    config JSON,  -- Configuración específica del bloque (columnas, iconos, etc.)
    FOREIGN KEY (id_template) REFERENCES identitas_templates(id_template) ON DELETE CASCADE
);
```

### Tabla: `identitas_instancia_templates`
Qué template eligió cada instancia para cada página.

```sql
CREATE TABLE identitas_instancia_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT NOT NULL,
    pagina VARCHAR(50) NOT NULL,  -- 'sobre-nosotros', 'servicios', etc.
    id_template INT NOT NULL,
    UNIQUE KEY unique_instancia_pagina (id_instancia, pagina),
    FOREIGN KEY (id_instancia) REFERENCES identitas_instances(id_instancia) ON DELETE CASCADE,
    FOREIGN KEY (id_template) REFERENCES identitas_templates(id_template)
);
```

### Tabla: `identitas_contenido_bloques`
El contenido editado por cada cliente para cada bloque.

```sql
CREATE TABLE identitas_contenido_bloques (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_instancia INT NOT NULL,
    pagina VARCHAR(50) NOT NULL,
    tipo_bloque VARCHAR(50) NOT NULL,
    contenido JSON NOT NULL,  -- {"titulo": "...", "texto": "...", "link": "...", "items": [...]}
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_contenido (id_instancia, pagina, tipo_bloque),
    FOREIGN KEY (id_instancia) REFERENCES identitas_instances(id_instancia) ON DELETE CASCADE
);
```

---

## Tipos de Bloques Disponibles

### 1. `hero`
```json
{
    "titulo": "Sobre Nosotros",
    "subtitulo": "Conoce nuestra historia y valores"
}
```

### 2. `mision`
```json
{
    "titulo": "Nuestra Misión",
    "texto": "<p>La SAJuR es una asociación civil...</p>",
    "link_texto": "Conoce más",
    "link_url": "https://sajur.org"
}
```

### 3. `stats_2x2` (grid 2x2)
```json
{
    "items": [
        {"titulo": "Visión", "texto": "Ser un referente..."},
        {"titulo": "Valores", "texto": "Diálogo, Respeto..."},
        {"titulo": "Impacto", "texto": "Fortalecimiento..."},
        {"titulo": "Formación", "texto": "Capacitación..."}
    ]
}
```

### 4. `stats_4col` (grid 4 columnas)
```json
{
    "items": [
        {"titulo": "Visión", "texto": "..."},
        {"titulo": "Valores", "texto": "..."},
        {"titulo": "Impacto", "texto": "..."},
        {"titulo": "Formación", "texto": "..."}
    ]
}
```

### 5. `servicios_grid` (grid de servicios con iconos)
```json
{
    "items": [
        {"icono": "award", "titulo": "Certificados", "texto": "..."},
        {"icono": "users", "titulo": "Formación", "texto": "..."},
        {"icono": "book-open", "titulo": "Recursos", "texto": "..."}
    ]
}
```

### 6. `contacto_info`
```json
{
    "titulo": "Contacto",
    "texto": "Si tienes problemas...",
    "email": "info@sajur.org",
    "web": "https://sajur.org",
    "telefono": "+54 11 1234-5678"
}
```

### 7. `texto_libre`
```json
{
    "contenido": "<p>Texto con <strong>formato</strong> libre...</p>"
}
```

---

## Flujo en el Admin

### Paso 1: Elegir Template
```
┌─────────────────────────────────────────────────────────┐
│ Página: Sobre Nosotros                                  │
│                                                         │
│ Elegir Template:                                        │
│ ┌─────────┐  ┌─────────┐  ┌─────────┐                   │
│ │ Clásico │  │ Moderno │  │ Minimal │                   │
│ │  [img]  │  │  [img]  │  │  [img]  │                   │
│ │   ✓     │  │         │  │         │                   │
│ └─────────┘  └─────────┘  └─────────┘                   │
└─────────────────────────────────────────────────────────┘
```

### Paso 2: Editar Bloques
```
┌─────────────────────────────────────────────────────────┐
│ Editando: Template "Clásico" - Sobre Nosotros           │
├─────────────────────────────────────────────────────────┤
│ ▼ Bloque: Hero                                          │
│   Título:    [Sobre Nosotros________________]           │
│   Subtítulo: [Conoce nuestra historia_______]           │
├─────────────────────────────────────────────────────────┤
│ ▼ Bloque: Misión                                        │
│   Título: [Nuestra Misión___________________]           │
│   Texto:  [CKEditor - negrita, cursiva, etc]            │
│   Link:   [https://sajur.org________________]           │
├─────────────────────────────────────────────────────────┤
│ ▼ Bloque: Estadísticas (2x2)                            │
│   Item 1: Título [Visión__] Texto [Ser un ref...]       │
│   Item 2: Título [Valores_] Texto [Diálogo...]          │
│   Item 3: Título [Impacto_] Texto [Fortalec...]         │
│   Item 4: Título [Formación] Texto [Capacit...]         │
└─────────────────────────────────────────────────────────┘
│                              [Guardar Cambios]          │
└─────────────────────────────────────────────────────────┘
```

---

## Estructura de Archivos

```
identitas/
├── templates/
│   ├── bloques/                    # Renderizado de cada tipo de bloque
│   │   ├── hero.php
│   │   ├── mision.php
│   │   ├── stats_2x2.php
│   │   ├── stats_4col.php
│   │   ├── servicios_grid.php
│   │   ├── contacto_info.php
│   │   └── texto_libre.php
│   │
│   ├── paginas/                    # Templates completos de páginas
│   │   ├── sobre-nosotros/
│   │   │   ├── clasico.php
│   │   │   ├── moderno.php
│   │   │   └── minimal.php
│   │   ├── servicios/
│   │   │   ├── grid.php
│   │   │   └── lista.php
│   │   └── contacto/
│   │       └── formulario.php
│   │
│   └── render.php                  # Motor de renderizado
│
├── services/
│   └── TemplateService.php         # Servicio PSR-4 para templates
│
└── admin/
    └── templates.php               # Panel de administración de templates
```

---

## Renderizado (ejemplo)

### `identitas/templates/bloques/stats_2x2.php`
```php
<?php
// $contenido viene del JSON de la BD
// $colores viene de la configuración de la instancia
?>
<div class="grid grid-cols-2 gap-8 text-center">
    <?php foreach ($contenido['items'] as $item): ?>
    <div>
        <span class="text-4xl font-bold" style="color: var(--color-primario);">
            <?php echo htmlspecialchars($item['titulo']); ?>
        </span>
        <p class="mt-2 text-sm text-gray-700 dark:text-gray-300">
            <?php echo htmlspecialchars($item['texto']); ?>
        </p>
    </div>
    <?php endforeach; ?>
</div>
```

---

## Ventajas de esta Arquitectura

1. **Diseño consistente** - El cliente no puede romper el layout
2. **Fácil de editar** - Solo campos de texto, no HTML complejo
3. **Personalizable** - Elige template + colores + textos
4. **Extensible** - Fácil agregar nuevos templates y bloques
5. **Mantenible** - Cambios de diseño se hacen en un solo lugar
6. **CKEditor simple** - Solo para formato de texto (negrita, listas)

---

## Plan de Implementación

### Fase 1: Base de Datos
- [ ] Crear tablas SQL
- [ ] Insertar templates iniciales
- [ ] Migrar contenido actual a nuevo formato

### Fase 2: Backend
- [ ] Crear TemplateService.php
- [ ] Crear archivos de bloques
- [ ] Crear motor de renderizado

### Fase 3: Admin
- [ ] Selector de templates
- [ ] Editor de bloques con CKEditor
- [ ] Preview en tiempo real (opcional)

### Fase 4: Frontend
- [ ] Integrar renderizado en home.php
- [ ] Probar con SAJuR
- [ ] Documentar para nuevas instituciones

---

## Tiempo Estimado

- Fase 1: 1-2 horas
- Fase 2: 2-3 horas
- Fase 3: 3-4 horas
- Fase 4: 1-2 horas

**Total: 7-11 horas de desarrollo**

---

## Próximos Pasos

1. Confirmar diseño de esta arquitectura
2. Definir templates iniciales para cada página
3. Crear SQL de tablas
4. Comenzar implementación

