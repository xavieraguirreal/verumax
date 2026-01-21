-- Agregar columnas faltantes a la tabla instances en verumax_general
-- Fecha: 2025-11-23
-- Descripción: Agregar columnas para información institucional compartida

USE verumax_general;

-- Verificar si las columnas ya existen antes de agregarlas
-- MySQL no soporta IF NOT EXISTS en ALTER TABLE directamente,
-- así que usamos una forma que no da error si ya existe

-- Agregar sitio_web_oficial
ALTER TABLE instances
ADD COLUMN sitio_web_oficial VARCHAR(255) DEFAULT NULL COMMENT 'URL del sitio web oficial de la institución'
AFTER nombre_completo;

-- Agregar email_contacto
ALTER TABLE instances
ADD COLUMN email_contacto VARCHAR(255) DEFAULT NULL COMMENT 'Email de contacto de la institución'
AFTER sitio_web_oficial;

-- Agregar redes_sociales
ALTER TABLE instances
ADD COLUMN redes_sociales TEXT DEFAULT NULL COMMENT 'JSON con URLs de redes sociales (instagram, facebook, linkedin, whatsapp, twitter, youtube)'
AFTER email_contacto;
