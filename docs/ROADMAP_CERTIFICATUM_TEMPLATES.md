# Roadmap: Sistema de Templates de Certificados por Curso

> **Estado:** Planificado
> **Prioridad:** Media
> **Fecha:** 2025-12-26

---

## Objetivo

Permitir a los administradores elegir entre templates de certificados **globales y adaptables** para cada curso. Los templates no contienen elementos institucionales - logo, firma y colores se inyectan dinámicamente según la institución.

---

## Estrategia de Migración Segura

```
FASE ACTUAL (Producción)
├── Template SAJuR hardcodeado → INTACTO, sigue funcionando
└── Sin selector de templates

FASE DE IMPLEMENTACIÓN
├── Crear nuevos templates globales (moderno, minimalista, formal, corporativo)
├── Agregar selector en admin
├── Cursos SIN template asignado → usan el actual hardcodeado (fallback)
└── Cursos CON template asignado → usan el nuevo sistema

FASE FINAL (cuando esté probado)
├── Crear template "clásico" global basado en el actual
├── Migrar cursos de SAJuR al nuevo "clásico"
└── Eliminar código hardcodeado legacy
```

**Garantía:** El sistema actual de SAJuR NO se modifica hasta que los nuevos estén probados.

---

## Estructura de Datos

### Nueva tabla `certificatum_templates` (verumax_certifi)

```sql
CREATE TABLE `certificatum_templates` (
  `id_template` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT,
  `tipo_generador` ENUM('tcpdf', 'mpdf') DEFAULT 'mpdf',
  `orientacion` ENUM('landscape', 'portrait') DEFAULT 'landscape',
  `preview_url` VARCHAR(255),
  `config` JSON COMMENT 'Posicionamiento, fuentes, estilos',
  `tiene_imagen_fondo` TINYINT(1) DEFAULT 0,
  `imagen_fondo_path` VARCHAR(255),
  `activo` TINYINT(1) DEFAULT 1,
  `orden` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Nuevo campo en `cursos` (verumax_academi)

```sql
ALTER TABLE `cursos` ADD COLUMN `id_template` INT DEFAULT NULL;
```

### Templates iniciales

| slug | nombre | tipo_generador |
|------|--------|----------------|
| moderno | Moderno | mpdf |
| minimalista | Minimalista | mpdf |
| formal | Formal Académico | mpdf |
| corporativo | Corporativo | mpdf |

---

## Estructura de Carpetas

```
assets/templates/certificados/
├── sajur/                         # INTACTO - NO TOCAR
│   └── template_clasico.jpg
│
├── moderno/                       # NUEVO
│   ├── preview.jpg
│   ├── config.json
│   └── template.html
├── minimalista/                   # NUEVO
├── formal/                        # NUEVO
└── corporativo/                   # NUEVO
```

---

## Tareas

### FASE 1: Base de Datos
- [ ] Crear tabla `certificatum_templates`
- [ ] Agregar campo `id_template` a tabla `cursos`
- [ ] Insertar los 4 templates iniciales

### FASE 2: Servicio Backend
- [ ] Crear `CertificateTemplateService.php`
  - `getAll()` - Lista templates activos
  - `getById(int $id)` - Template por ID
  - `getForCurso(int $id_curso)` - Template del curso o NULL
  - `getConfig(int $id)` - Configuración parseada

### FASE 3: Panel Admin
- [ ] Crear `administrare_api.php` para endpoints AJAX
- [ ] Modificar `administrare.php` - agregar selector visual en modal de curso
- [ ] Modificar `administrare_gestionar.php` - incluir `id_template` en actualización

### FASE 4: Generación PDF (con fallback)
- [ ] Modificar `CursoService.php` - agregar `id_template` a campos permitidos
- [ ] Modificar `creare_pdf_tcpdf.php` - lógica de fallback
- [ ] Modificar `creare_content.php` - lógica de fallback
- [ ] Modificar `sajur/creare_pdf.php` - router con verificación

### FASE 5: Crear Templates HTML
- [ ] Diseñar template "Moderno"
- [ ] Diseñar template "Minimalista"
- [ ] Diseñar template "Formal"
- [ ] Diseñar template "Corporativo"
- [ ] Crear previews (300x200) para cada template

### FASE 6: Testing
- [ ] Probar que cursos SIN template siguen funcionando (fallback)
- [ ] Probar generación con cada template nuevo
- [ ] Verificar inyección de elementos institucionales
- [ ] Probar selector en admin

### FASE 7: Migración Final (después de testing)
- [ ] Crear template "clásico" global basado en el actual de SAJuR
- [ ] Editar imagen para remover elementos de SAJuR
- [ ] Asignar template "clásico" a cursos existentes
- [ ] Eliminar código hardcodeado legacy

---

## Archivos a Modificar

| Archivo | Cambio |
|---------|--------|
| `src/VERUMax/Services/CursoService.php` | Agregar `id_template` a campos permitidos |
| `certificatum/creare_pdf_tcpdf.php` | Lógica de fallback + nuevo sistema |
| `certificatum/creare_content.php` | Lógica de fallback + nuevo sistema |
| `certificatum/administrare.php` | Selector de templates en modal |
| `certificatum/administrare_gestionar.php` | Modificar `actualizarCurso()` |
| `sajur/creare_pdf.php` | Router con verificación de template |

## Archivos Nuevos

| Archivo | Propósito |
|---------|-----------|
| `src/VERUMax/Services/CertificateTemplateService.php` | Servicio de templates |
| `certificatum/administrare_api.php` | Endpoints AJAX |
| `assets/templates/certificados/moderno/*` | Template moderno |
| `assets/templates/certificados/minimalista/*` | Template minimalista |
| `assets/templates/certificados/formal/*` | Template formal |
| `assets/templates/certificados/corporativo/*` | Template corporativo |

---

## Lógica de Fallback

```php
$id_template = $curso['id_template'] ?? null;

if ($id_template === null) {
    // FALLBACK: Sistema actual (SAJuR hardcodeado)
    $template_path = '.../' . $institucion . '/template_clasico.jpg';
    // código actual sin cambios
} else {
    // NUEVO: Template dinámico con inyección de elementos
    $template = CertificateTemplateService::getById($id_template);
    // inyectar logo, firma, colores de instance_config
}
```

---

## Garantías de Seguridad

1. **Producción intacta** - El código actual de SAJuR NO se modifica
2. **Fallback automático** - Si `id_template = NULL`, usa sistema actual
3. **Rollback fácil** - Solo quitar `id_template` del curso
4. **Migración gradual** - Probar con un curso antes de migrar todos
5. **Sin downtime** - Cambios aditivos, no destructivos
