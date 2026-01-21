-- Ejecutar en phpMyAdmin del servidor remoto
-- Copiar y pegar el resultado completo

-- 1. Estructura de miembros (estudiantes/docentes)
SHOW CREATE TABLE verumax_nexus.miembros;

-- 2. Estructura de inscripciones
SHOW CREATE TABLE verumax_academi.inscripciones;

-- 3. Estructura de cursos
SHOW CREATE TABLE verumax_academi.cursos;

-- 4. Estructura de cohortes
SHOW CREATE TABLE verumax_academi.cohortes;

-- 5. Estructura de competencias
SHOW CREATE TABLE verumax_academi.competencias;

-- 6. Estructura de competencias por inscripción
SHOW CREATE TABLE verumax_academi.competencias_inscripcion;

-- 7. Estructura de trayectoria
SHOW CREATE TABLE verumax_academi.trayectoria;

-- 8. Estructura de participaciones docente
SHOW CREATE TABLE verumax_certifi.participaciones_docente;

-- 9. Estructura de instances (instituciones)
SHOW CREATE TABLE verumax_general.instances;

-- 10. Ver valores ENUM del campo genero en miembros
SHOW COLUMNS FROM verumax_nexus.miembros WHERE Field = 'genero';
