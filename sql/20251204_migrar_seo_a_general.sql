-- ================================================================================
-- MIGRAR SEO DESDE identitas_config A instances
-- Fecha: 04/12/2025 - 21:30
-- ================================================================================

USE verumax_general;

-- ================================================================================
-- VERIFICAR DATOS ACTUALES
-- ================================================================================

-- Ver qué tiene instances actualmente
SELECT
    id_instancia,
    slug,
    seo_title,
    seo_description,
    seo_keywords
FROM instances
WHERE id_instancia = 1;

-- Ver qué tiene identitas_config
SELECT
    id_instancia,
    seo_title,
    seo_description,
    seo_keywords
FROM verumax_identi.identitas_config
WHERE id_instancia = 1;

-- ================================================================================
-- MIGRAR DATOS (solo si instances tiene valores vacíos)
-- ================================================================================

UPDATE verumax_general.instances i
INNER JOIN verumax_identi.identitas_config ic ON i.id_instancia = ic.id_instancia
SET
    i.seo_title = COALESCE(i.seo_title, ic.seo_title),
    i.seo_description = COALESCE(i.seo_description, ic.seo_description),
    i.seo_keywords = COALESCE(i.seo_keywords, ic.seo_keywords)
WHERE i.id_instancia = 1;

-- ================================================================================
-- VERIFICAR RESULTADO
-- ================================================================================

SELECT
    id_instancia,
    slug,
    seo_title,
    seo_description,
    seo_keywords
FROM verumax_general.instances
WHERE id_instancia = 1;

-- ================================================================================
-- ELIMINAR DE identitas_config (ejecutar después de verificar)
-- ================================================================================

-- USE verumax_identi;
-- ALTER TABLE identitas_config
-- DROP COLUMN seo_title,
-- DROP COLUMN seo_description,
-- DROP COLUMN seo_keywords;

-- ================================================================================
-- NOTAS
-- ================================================================================
--
-- Después de ejecutar este SQL:
-- 1. El SEO estará en verumax_general.instances
-- 2. Agregar sección SEO en admin/modulos/general.php
-- 3. Quitar sección SEO de admin/modulos/identitas.php
--
-- ================================================================================
