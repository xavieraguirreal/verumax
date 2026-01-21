# Estructura de Base de Datos - Certificatum

**Última actualización:** 2025-12-06
**Preparado para:** Migración a Academicus/Nexus

---

## Base de Datos: `verumax_certifi`

### Diagrama de Relaciones

```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│    instances    │     │    docentes     │     │     cursos      │
│  (verumax_gen)  │     │                 │     │                 │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id_instancia PK │◄────│ id_instancia FK │     │ id_curso PK     │
│ slug            │     │ id_docente PK   │     │ id_instancia FK │────►
│ nombre          │     │ dni             │     │ codigo_curso    │
└────────┬────────┘     │ nombre_completo │     │ nombre_curso    │
         │              │ email           │     │ carga_horaria   │
         │              │ especialidad    │     │ activo          │
         │              └────────┬────────┘     └────────┬────────┘
         │                       │                       │
         │                       │                       │
         ▼                       ▼                       ▼
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   estudiantes   │     │cohorte_docentes │     │    cohortes     │
├─────────────────┤     ├─────────────────┤     ├─────────────────┤
│ id_estudiante PK│     │ id_cohorte FK   │◄────│ id_cohorte PK   │
│ id_instancia FK │     │ id_docente FK   │     │ id_curso FK     │────►
│ institucion     │     │ rol             │     │ id_instancia FK │
│ dni             │     └─────────────────┘     │ codigo_cohorte  │
│ nombre_completo │                             │ id_docente_tit. │────►
│ fecha_registro  │                             │ fecha_inicio    │
└────────┬────────┘                             │ fecha_fin       │
         │                                      │ modalidad       │
         │                                      │ estado          │
         │                                      └────────┬────────┘
         │                                               │
         │              ┌────────────────────────────────┘
         │              │
         ▼              ▼
┌─────────────────────────────┐
│       inscripciones         │
├─────────────────────────────┤
│ id_inscripcion PK           │
│ id_estudiante FK            │────► estudiantes
│ id_curso FK                 │────► cursos
│ id_cohorte FK (opcional)    │────► cohortes
│ estado                      │
│ fecha_inscripcion           │
│ fecha_inicio                │
│ fecha_finalizacion          │
│ nota_final                  │
│ asistencia                  │
└──────────────┬──────────────┘
               │
       ┌───────┴───────┐
       │               │
       ▼               ▼
┌─────────────┐  ┌─────────────┐     ┌─────────────────┐
│ trayectoria │  │competencias │     │  competencias   │
│             │  │_inscripcion │     │   (del curso)   │
├─────────────┤  ├─────────────┤     ├─────────────────┤
│id_inscripPK │  │id_inscripFK │     │ id_competencia  │
│fecha        │  │competencia  │     │ id_curso FK     │────► cursos
│evento       │  │orden        │     │ competencia     │
│detalle      │  └─────────────┘     │ descripcion     │
│orden        │                      │ orden           │
└─────────────┘                      │ activo          │
                                     └─────────────────┘
```

---

## Tablas Detalladas

### 1. `estudiantes`

Personas que cursan formaciones. **Migración futura a Nexus.**

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_estudiante` | INT PK AUTO | Identificador único |
| `id_instancia` | INT UNSIGNED NULL | FK a `verumax_general.instances` |
| `institucion` | VARCHAR(100) | Slug de la institución (legacy, mantener por compatibilidad) |
| `dni` | VARCHAR(20) | Documento de identidad |
| `nombre_completo` | VARCHAR(255) | Nombre completo del estudiante |
| `fecha_registro` | TIMESTAMP | Fecha de alta en el sistema |

**Índices:**
- `idx_estudiantes_instancia` (id_instancia)
- `uk_estudiante_dni` (institucion, dni) - Único por institución

**Notas para migración:**
- Cuando migres a Nexus, usar `id_instancia` en lugar de `institucion`
- El campo `institucion` se mantiene por compatibilidad con código legacy

---

### 2. `docentes`

Profesores y facilitadores. **Migración futura a Nexus.**

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_docente` | INT PK AUTO | Identificador único |
| `id_instancia` | INT UNSIGNED NULL | FK a `verumax_general.instances` |
| `dni` | VARCHAR(20) | Documento de identidad |
| `nombre_completo` | VARCHAR(255) | Nombre completo |
| `email` | VARCHAR(255) NULL | Correo electrónico |
| `telefono` | VARCHAR(50) NULL | Teléfono de contacto |
| `especialidad` | VARCHAR(255) NULL | Área de especialización |
| `titulo` | VARCHAR(255) NULL | Título académico |
| `bio` | TEXT NULL | Biografía corta para mostrar |
| `foto_url` | VARCHAR(500) NULL | URL de foto de perfil |
| `activo` | TINYINT(1) | 1=activo, 0=inactivo |
| `fecha_registro` | TIMESTAMP | Fecha de alta |
| `fecha_actualizacion` | TIMESTAMP | Última modificación |

**Índices:**
- `idx_docentes_instancia` (id_instancia)
- `idx_docentes_dni` (dni)
- `uk_docente_instancia_dni` (id_instancia, dni) - Único por institución

---

### 3. `cursos`

Definición de programas formativos. **Migración futura a Academicus.**

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_curso` | INT PK AUTO | Identificador único |
| `id_instancia` | INT UNSIGNED NULL | FK a instances (NULL = curso global) |
| `codigo_curso` | VARCHAR(50) | Código único del curso (ej: "DIP-JR-2024") |
| `nombre_curso` | VARCHAR(255) | Nombre completo del curso |
| `carga_horaria` | INT | Horas totales del curso |
| `activo` | TINYINT(1) | 1=activo, 0=inactivo |

**Índices:**
- `idx_cursos_instancia` (id_instancia)
- `uk_curso_codigo` (codigo_curso) - Código único global

**Notas:**
- `id_instancia = NULL` significa que el curso es **global** (visible para todas las instituciones)
- Al migrar a Academicus, asignar `id_instancia` para cursos específicos de cada institución

---

### 4. `cohortes`

Ediciones/grupos de un curso. **Migración futura a Academicus.**

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_cohorte` | INT PK AUTO | Identificador único |
| `id_curso` | INT | FK a `cursos` |
| `id_instancia` | INT UNSIGNED NULL | FK a instances |
| `codigo_cohorte` | VARCHAR(50) | Código de la edición (ej: "2024-A") |
| `nombre_cohorte` | VARCHAR(255) NULL | Nombre descriptivo opcional |
| `id_docente_titular` | INT NULL | FK a `docentes` (docente principal) |
| `fecha_inicio` | DATE NULL | Inicio de la cohorte |
| `fecha_fin` | DATE NULL | Fin de la cohorte |
| `cupo_maximo` | INT UNSIGNED NULL | Límite de inscripciones |
| `modalidad` | ENUM | 'presencial', 'virtual', 'hibrido' |
| `horario` | VARCHAR(255) NULL | Descripción del horario |
| `ubicacion` | VARCHAR(255) NULL | Aula o link de clase virtual |
| `estado` | ENUM | 'programada', 'en_curso', 'finalizada', 'cancelada' |
| `activo` | TINYINT(1) | 1=activo, 0=inactivo |
| `created_at` | TIMESTAMP | Fecha de creación |
| `updated_at` | TIMESTAMP | Última modificación |

**Índices:**
- `idx_cohortes_curso` (id_curso)
- `idx_cohortes_instancia` (id_instancia)
- `idx_cohortes_docente` (id_docente_titular)
- `uk_cohorte_curso_codigo` (id_curso, codigo_cohorte) - Único por curso

**Ejemplo de uso:**
```
Curso: "Diplomatura en Justicia Restaurativa" (id_curso=1)
├── Cohorte 2024-A (id_cohorte=1) - Mar-Jun 2024, Prof. García
├── Cohorte 2024-B (id_cohorte=2) - Ago-Nov 2024, Prof. López
└── Cohorte 2025-A (id_cohorte=3) - Mar-Jun 2025, Prof. García
```

---

### 5. `cohorte_docentes`

Relación muchos-a-muchos entre cohortes y docentes.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_cohorte` | INT | FK a `cohortes` |
| `id_docente` | INT | FK a `docentes` |
| `rol` | ENUM | 'titular', 'adjunto', 'invitado', 'tutor' |
| `fecha_asignacion` | TIMESTAMP | Cuándo se asignó |

**PK Compuesta:** (id_cohorte, id_docente)

---

### 6. `inscripciones`

Relación estudiante-curso (y opcionalmente cohorte).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_inscripcion` | INT PK AUTO | Identificador único |
| `id_estudiante` | INT | FK a `estudiantes` |
| `id_curso` | INT | FK a `cursos` |
| `id_cohorte` | INT NULL | FK a `cohortes` (preparado para futuro) |
| `estado` | VARCHAR(50) | 'Aprobado', 'En Curso', 'Por Iniciar', 'Finalizado', 'Reprobado' |
| `fecha_inscripcion` | TIMESTAMP | Cuándo se inscribió |
| `fecha_inicio` | DATE NULL | Inicio real del estudiante |
| `fecha_finalizacion` | DATE NULL | Fin real del estudiante |
| `nota_final` | DECIMAL(4,2) NULL | Calificación final |
| `asistencia` | VARCHAR(10) NULL | Porcentaje de asistencia (ej: "95%") |

**Índices:**
- `idx_inscripciones_estudiante` (id_estudiante)
- `idx_inscripciones_curso` (id_curso)
- `idx_inscripciones_cohorte` (id_cohorte)
- `uk_inscripcion` (id_estudiante, id_curso) - Un estudiante, un curso

**Notas para migración:**
- Actualmente `id_cohorte` es NULL para todas las inscripciones
- Cuando implementes cohortes, crear script para asignar inscripciones a cohortes existentes

---

### 7. `competencias`

Competencias/habilidades que otorga un **curso**.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_competencia` | INT PK AUTO | Identificador único |
| `id_curso` | INT | FK a `cursos` |
| `competencia` | VARCHAR(255) | Texto de la competencia |
| `descripcion` | TEXT NULL | Descripción ampliada |
| `orden` | TINYINT UNSIGNED | Orden de visualización |
| `activo` | TINYINT(1) | 1=activo, 0=eliminado (soft delete) |
| `created_at` | TIMESTAMP | Fecha de creación |

**Índices:**
- `idx_competencias_curso` (id_curso)

---

### 8. `competencias_inscripcion`

Competencias específicas adquiridas en una inscripción (histórico).

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_inscripcion` | INT | FK a `inscripciones` |
| `competencia` | VARCHAR(255) | Texto de la competencia |
| `orden` | TINYINT UNSIGNED | Orden de visualización |

**Notas:**
- Esta tabla mantiene un histórico de las competencias al momento de la inscripción
- Útil si las competencias del curso cambian pero quieres mantener lo que el estudiante cursó

---

### 9. `trayectoria`

Eventos/hitos durante una inscripción.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| `id_inscripcion` | INT | FK a `inscripciones` |
| `fecha` | DATE | Fecha del evento |
| `evento` | VARCHAR(255) | Nombre del evento |
| `detalle` | TEXT NULL | Detalles adicionales |
| `orden` | TINYINT UNSIGNED | Orden cronológico |

---

## Guía de Migración a Academicus/Nexus

### Fase 0: Ya Completada ✅

1. ✅ Agregar `id_instancia` a `cursos`
2. ✅ Agregar `id_instancia` a `estudiantes`
3. ✅ Crear tabla `competencias` separada del curso
4. ✅ Renombrar `competencias_curso` → `competencias_inscripcion`
5. ✅ Crear tabla `docentes`
6. ✅ Crear tabla `cohortes`
7. ✅ Crear tabla `cohorte_docentes`
8. ✅ Agregar `id_cohorte` a `inscripciones`

### Fase 1: Crear Services (Pendiente)

```php
// nexus/NexusService.php
class NexusService {
    public static function getEstudiantes($id_instancia, $buscar = '') { }
    public static function crearEstudiante($id_instancia, $data) { }
    public static function getDocentes($id_instancia) { }
    public static function crearDocente($id_instancia, $data) { }
}

// academicus/AcademicusService.php
class AcademicusService {
    public static function getCursos($id_instancia = null) { }
    public static function crearCurso($data) { }
    public static function getCohortes($id_curso) { }
    public static function crearCohorte($id_curso, $data) { }
    public static function getInscripciones($id_instancia) { }
}
```

### Fase 2: Refactorizar Panel

Modificar `certificatum/administrare.php` para llamar a los Services en lugar de las funciones directas.

### Fase 3: Asignar Cursos a Instituciones

```sql
-- Asignar cursos existentes a una institución específica
UPDATE cursos SET id_instancia = 1 WHERE id_instancia IS NULL;

-- O mantener algunos como globales y otros específicos
UPDATE cursos SET id_instancia = 1 WHERE codigo_curso LIKE 'SAJ%';
UPDATE cursos SET id_instancia = 2 WHERE codigo_curso LIKE 'LIB%';
```

### Fase 4: Implementar Cohortes

1. Crear cohortes para cursos existentes
2. Asignar inscripciones existentes a cohortes
3. Modificar UI para gestionar cohortes

---

## Queries Útiles

### Ver estructura actual
```sql
-- Estudiantes por institución
SELECT id_instancia, institucion, COUNT(*) as total
FROM estudiantes GROUP BY id_instancia, institucion;

-- Cursos globales vs específicos
SELECT
    CASE WHEN id_instancia IS NULL THEN 'Global' ELSE CONCAT('Inst #', id_instancia) END as tipo,
    COUNT(*) as total
FROM cursos GROUP BY id_instancia;

-- Inscripciones sin cohorte asignada
SELECT COUNT(*) as sin_cohorte FROM inscripciones WHERE id_cohorte IS NULL;
```

### Asignar id_instancia a cursos
```sql
-- Ver qué cursos tienen inscripciones de qué institución
SELECT c.id_curso, c.codigo_curso, c.nombre_curso, e.institucion, COUNT(*) as inscripciones
FROM cursos c
JOIN inscripciones i ON c.id_curso = i.id_curso
JOIN estudiantes e ON i.id_estudiante = e.id_estudiante
GROUP BY c.id_curso, e.institucion;
```

---

## Archivos de Migración

| Archivo | Propósito | Estado |
|---------|-----------|--------|
| `migracion_preparar_academicus.php` | Agregar id_instancia a cursos y estudiantes | ✅ Ejecutado |
| `migracion_paso3.php` | Crear tabla competencias | ✅ Ejecutado |
| `migracion_docentes_cohortes.php` | Crear docentes, cohortes, cohorte_docentes | ⏳ Pendiente |

**Nota:** Después de ejecutar todas las migraciones, estos archivos pueden eliminarse o moverse a `/backup/migraciones/`.
