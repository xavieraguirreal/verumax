# Despliegue: Sistema de Templates de Certificados

**Fecha:** 2025-12-26
**Versión:** 1.1 (Actualizado para admin centralizado)

---

## Resumen

Este despliegue agrega la funcionalidad de selección de templates de certificados por curso. El sistema actual sigue funcionando sin cambios (fallback automático).

---

## 1. SQL a Ejecutar en Producción

Ejecutar el archivo:
```
database/migrations/2025_12_26_certificatum_templates.sql
```

O ejecutar estos comandos en orden:

### Paso 1: Crear tabla (en verumax_certifi)
```sql
USE verumax_certifi;

CREATE TABLE IF NOT EXISTS `certificatum_templates` (
  `id_template` INT AUTO_INCREMENT PRIMARY KEY,
  `slug` VARCHAR(50) NOT NULL UNIQUE,
  `nombre` VARCHAR(100) NOT NULL,
  `descripcion` TEXT,
  `tipo_generador` ENUM('tcpdf', 'mpdf') DEFAULT 'mpdf',
  `orientacion` ENUM('landscape', 'portrait') DEFAULT 'landscape',
  `preview_url` VARCHAR(255),
  `config` JSON,
  `tiene_imagen_fondo` TINYINT(1) DEFAULT 0,
  `imagen_fondo_path` VARCHAR(255),
  `activo` TINYINT(1) DEFAULT 1,
  `orden` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Paso 2: Insertar templates iniciales
```sql
INSERT INTO `certificatum_templates`
  (`slug`, `nombre`, `descripcion`, `tipo_generador`, `orientacion`, `preview_url`, `config`, `tiene_imagen_fondo`, `orden`)
VALUES
  ('moderno', 'Moderno', 'Diseño contemporáneo con gradientes', 'mpdf', 'landscape', '/assets/templates/certificados/moderno/preview.jpg', '{"fuente_titulo": "Montserrat", "fuente_nombre": "Playfair Display", "fuente_cuerpo": "Open Sans", "estilo": "gradiente"}', 0, 1),
  ('minimalista', 'Minimalista', 'Diseño limpio con espacios en blanco', 'mpdf', 'landscape', '/assets/templates/certificados/minimalista/preview.jpg', '{"fuente_titulo": "Josefin Sans", "fuente_nombre": "Lora", "fuente_cuerpo": "Source Sans 3", "estilo": "minimal"}', 0, 2),
  ('formal', 'Formal Académico', 'Estilo tradicional universitario', 'mpdf', 'landscape', '/assets/templates/certificados/formal/preview.jpg', '{"fuente_titulo": "Cormorant Garamond", "fuente_nombre": "Great Vibes", "fuente_cuerpo": "Crimson Text", "estilo": "academico"}', 0, 3),
  ('corporativo', 'Corporativo', 'Diseño profesional para empresas', 'mpdf', 'landscape', '/assets/templates/certificados/corporativo/preview.jpg', '{"fuente_titulo": "Poppins", "fuente_nombre": "Raleway", "fuente_cuerpo": "Roboto", "estilo": "empresarial"}', 0, 4);
```

### Paso 3: Agregar campo a cursos (en verumax_academi)
```sql
USE verumax_academi;

ALTER TABLE `cursos`
ADD COLUMN `id_template` INT DEFAULT NULL
COMMENT 'Template de certificado (NULL=usar sistema actual)';
```

---

## 2. Archivos a Subir

### Archivos NUEVOS (crear)
| Archivo | Ruta |
|---------|------|
| CertificateTemplateService.php | `src/VERUMax/Services/CertificateTemplateService.php` |
| templates_certificatum.php | `admin/ajax/templates_certificatum.php` |
| Migración SQL | `database/migrations/2025_12_26_certificatum_templates.sql` |

### Archivos MODIFICADOS (reemplazar)
| Archivo | Ruta | Cambio |
|---------|------|--------|
| certificatum.php | `admin/modulos/certificatum.php` | +Selector visual de templates en modal + funciones JS |
| CursoService.php | `src/VERUMax/Services/CursoService.php` | +id_template en campos permitidos |

### Archivos LEGACY (no necesarios para admin centralizado)
| Archivo | Ruta | Nota |
|---------|------|------|
| administrare.php | `certificatum/administrare.php` | Solo si se usa admin antiguo |
| administrare_gestionar.php | `certificatum/administrare_gestionar.php` | Solo si se usa admin antiguo |
| administrare_api.php | `certificatum/administrare_api.php` | Solo si se usa admin antiguo |

---

## 3. Orden de Ejecución

1. **Primero:** Ejecutar SQL (crea tabla y campo)
2. **Segundo:** Subir archivos nuevos
3. **Tercero:** Subir archivos modificados
4. **Cuarto:** Probar en panel admin

---

## 4. Verificación Post-Despliegue

1. Ir a Administrare → Gestión de Cursos
2. Hacer clic en "Editar" en cualquier curso
3. Verificar que aparece el selector "Template de Certificado"
4. El template "Predeterminado" debe estar seleccionado por defecto
5. Guardar sin cambiar template → debe seguir funcionando como antes

---

## 5. Rollback (si es necesario)

Si algo falla, el sistema sigue funcionando porque:
- Todos los cursos tienen `id_template = NULL` por defecto
- El código usa fallback al sistema actual cuando `id_template` es NULL

Para rollback completo:
```sql
-- Revertir cambio en cursos
ALTER TABLE verumax_academi.cursos DROP COLUMN id_template;

-- Eliminar tabla de templates
DROP TABLE verumax_certifi.certificatum_templates;
```

Y restaurar archivos originales desde backup.

---

## 6. Próximos Pasos (Fase 4+)

Después de verificar que el selector funciona:

1. Crear los templates HTML en `assets/templates/certificados/`
2. Modificar `creare_pdf_tcpdf.php` y `creare_content.php` para usar templates
3. Generar previews de cada template
4. Testing completo de generación de PDFs
