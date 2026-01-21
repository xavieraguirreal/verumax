# ACADEMICUS - PLATAFORMA DE GESTIÓN ACADÉMICA

**Archivo Landing:** `academicus.php`
**Archivo Aplicación:** `academicus/` (Directorio de la aplicación)
**Color Distintivo:** Azul Zafiro (#0F52BA)
**Estado:** ✅ Parcialmente Implementado (~80% del core en producción)

---

## CONCEPTO GENERAL

### Nombres

**Nombre Técnico (Latín):** Academicus

* **Significado:** "Relativo a la academia"
* **Raíz:** *academia* (del griego *akademeia*)

**Nombre Comercial:** VERUMax Academicus / Plataforma de Gestión Académica

**Lema:** *"Academicus: Structura et Sapientia"* (Estructura y Sabiduría)

### Propuesta de Valor

> "La forma más sencilla de crear, gestionar y escalar tu oferta educativa, desde un único curso hasta una academia completa, con la confianza del ecosistema VERUMax."

### Filosofía

Academicus es el cerebro organizativo detrás de la oferta educativa. Su misión es separar y especializar la **gestión académica**, permitiendo que `Certificatum` se enfoque exclusivamente en la **certificación y validación**.

---

## PRINCIPIO ARQUITECTÓNICO: SEPARACIÓN DE RESPONSABILIDADES

#### `Academicus`: La Fuente de Verdad Académica

Se encarga del **QUÉ, QUIÉN, CÓMO y CUÁNDO** de la educación.

* **Qué se enseña:** Formaciones, Cursos, Módulos.
* **Quién enseña:** Docentes, Formadores.
* **Quién aprende:** Estudiantes.
* **Cómo y Cuándo:** Cohortes, Inscripciones, Calendarios, Notas, **Evaluaciones**.

#### `Certificatum`: El Motor de Validación

Se encarga de la **PRUEBA** de la educación.

* **Qué se emite:** Gestiona las plantillas de Diplomas, Certificados, Constancias.
* **Cuándo se emite:** Aplica reglas de negocio sobre los datos de Academicus.
* **Cómo se valida:** Provee el motor de validación por QR y la vista pública.

---

## ESTADO ACTUAL DE IMPLEMENTACIÓN

> **IMPORTANTE:** El core de Academicus ya está funcionando en producción para SAJuR, aunque actualmente vive dentro del panel de `certificatum/administrare.php`. Esta sección documenta lo que YA EXISTE.

### Entidades Implementadas

| Entidad | Estado | CRUD | Base de Datos | Tabla |
|---------|--------|------|---------------|-------|
| **Estudiantes** | ✅ Producción | ✅ Completo | `verumax_nexus` | `miembros` |
| **Cursos** | ✅ Producción | ✅ Completo | `verumax_academi` | `cursos` |
| **Inscripciones** | ✅ Producción | ✅ Completo | `verumax_academi` | `inscripciones` |
| **Cohortes** | ✅ Producción | Parcial | `verumax_academi` | `cohortes` |
| **Competencias** | ✅ Producción | Lectura | `verumax_academi` | `competencias` |
| **Trayectoria** | ✅ Producción | Lectura | `verumax_academi` | `trayectoria` |
| **Docentes** | ✅ Producción | ✅ Completo | `verumax_nexus` | `miembros` |
| **Participaciones** | ✅ Producción | ✅ Completo | `verumax_certifi` | `participaciones_docente` |
| **Evaluaciones** | 🚧 En desarrollo | - | `verumax_academi` | `evaluationes` (Probatio) |

### Servicios PSR-4 Implementados

Ubicación: `src/VERUMax/Services/`

| Servicio | Responsabilidad | Estado |
|----------|-----------------|--------|
| `StudentService.php` | Gestión de estudiantes | ✅ Producción |
| `MemberService.php` | Gestión unificada de personas | ✅ Producción |
| `CursoService.php` | Fuente de verdad para cursos | ✅ Producción |
| `InscripcionService.php` | Gestión de inscripciones | ✅ Producción |
| `DatabaseService.php` | Abstracción multi-BD | ✅ Producción |
| `LanguageService.php` | Sistema multiidioma | ✅ Producción |

### Funcionalidades Implementadas

**Panel de Administración** (`certificatum/administrare.php`):
- [x] CRUD completo de estudiantes
- [x] CRUD completo de cursos
- [x] CRUD completo de inscripciones
- [x] CRUD completo de docentes
- [x] CRUD completo de participaciones docentes
- [x] Importación masiva (Excel, CSV, texto)
- [x] Búsqueda y filtrado avanzado
- [x] Gestión de competencias por curso
- [x] Timeline de trayectoria académica

**Archivos clave en producción:**
```
certificatum/
├── administrare.php           ← Panel principal
├── administrare_gestionar.php ← 25+ funciones CRUD
├── administrare_procesador.php← Importación masiva
└── config.php                 ← Conexiones BD
```

---

## ARQUITECTURA DE BASE DE DATOS (IMPLEMENTADA)

### Distribución Multi-Base de Datos

```
verumax_general    → Configuración global, instancias, templates email
verumax_nexus      → Personas (estudiantes, docentes, miembros)
verumax_academi    → Gestión académica (cursos, inscripciones, evaluaciones)
verumax_certifi    → Certificación y validación
```

### Tablas Principales (YA EN PRODUCCIÓN)

```sql
-- verumax_nexus.miembros (Estudiantes y Docentes unificados)
-- Campos: id_miembro, identificador_principal (DNI), nombre, apellido,
--         email, telefono, genero, estado, tipo_miembro, fecha_alta...

-- verumax_academi.cursos
-- Campos: id_curso, id_instancia, codigo_curso, nombre_curso, carga_horaria,
--         descripcion, categoria, tipo_curso, nivel, modalidad, activo...

-- verumax_academi.inscripciones
-- Campos: id_inscripcion, id_miembro, id_curso, id_cohorte, estado,
--         nota_final, asistencia_porcentaje, certificado_emitido...
-- Estados: Preinscrito, Inscrito, En Curso, Finalizado, Aprobado,
--          Desaprobado, Abandonado, Suspendido

-- verumax_academi.cohortes
-- Campos: id_cohorte, id_instancia, id_curso, codigo_cohorte,
--         nombre_cohorte, fecha_inicio, fecha_fin, estado...

-- verumax_academi.competencias
-- Campos: id_competencia, id_curso, competencia, descripcion, categoria, orden

-- verumax_academi.trayectoria
-- Campos: id_evento, id_inscripcion, fecha, tipo_evento, evento, detalle, orden
```

---

## MÓDULO PROBATIO: SISTEMA DE EVALUACIONES

> **Probatio** (del latín "prueba, demostración") es el módulo de evaluaciones de Academicus. Permite crear y administrar exámenes, quizzes y evaluaciones vinculadas a cursos.

### Concepto

Sistema de evaluación digital con metodología flexible que soporta:
- Evaluaciones con múltiples respuestas correctas
- Acceso con DNI (sin login adicional)
- Feedback inmediato pedagógico
- Persistencia de progreso
- Cierre cualitativo opcional
- Auditoría completa de intentos

### Arquitectura de Datos

Las tablas de Probatio viven en `verumax_academi` para mantener coherencia:

```sql
-- verumax_academi.evaluationes
CREATE TABLE evaluationes (
    id_evaluatio INT PRIMARY KEY AUTO_INCREMENT,
    id_instancia INT NOT NULL,              -- FK a instancia (SAJuR, etc.)
    id_curso INT,                           -- FK a cursos (opcional)
    id_cohorte INT,                         -- FK a cohortes (opcional)
    codigo VARCHAR(50) UNIQUE,              -- Ej: 'EVAL-SAJUR-CORR-2025'
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    tipo ENUM('examen', 'quiz', 'encuesta', 'autoevaluacion') DEFAULT 'examen',
    metodologia ENUM('afirmacion', 'tradicional', 'adaptive') DEFAULT 'tradicional',
    -- Configuración
    requiere_aprobacion_previa BOOLEAN DEFAULT FALSE,
    permite_multiples_intentos BOOLEAN DEFAULT TRUE,
    muestra_respuestas_correctas BOOLEAN DEFAULT FALSE,
    requiere_cierre_cualitativo BOOLEAN DEFAULT FALSE,
    texto_cierre_cualitativo TEXT,
    minimo_caracteres_cierre INT DEFAULT 50,
    -- Mensajes personalizados
    mensaje_bienvenida TEXT,
    mensaje_finalizacion TEXT,
    mensaje_error_no_inscripto TEXT,
    -- Estado y fechas
    estado ENUM('borrador', 'activa', 'cerrada', 'archivada') DEFAULT 'borrador',
    fecha_inicio DATETIME,
    fecha_fin DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_instancia (id_instancia),
    INDEX idx_curso (id_curso),
    INDEX idx_estado (estado)
);

-- verumax_academi.quaestiones (Preguntas)
CREATE TABLE quaestiones (
    id_quaestio INT PRIMARY KEY AUTO_INCREMENT,
    id_evaluatio INT NOT NULL,              -- FK a evaluationes
    orden INT NOT NULL,
    tipo ENUM('multiple_choice', 'multiple_answer', 'verdadero_falso', 'abierta') DEFAULT 'multiple_answer',
    enunciado TEXT NOT NULL,
    opciones JSON,                          -- [{"letra":"A","texto":"...","es_correcta":true}, ...]
    explicacion_correcta TEXT,              -- Feedback cuando acierta
    explicacion_incorrecta TEXT,            -- Feedback cuando falla
    puntos INT DEFAULT 1,
    es_obligatoria BOOLEAN DEFAULT TRUE,

    INDEX idx_evaluatio (id_evaluatio),
    FOREIGN KEY (id_evaluatio) REFERENCES evaluationes(id_evaluatio) ON DELETE CASCADE
);

-- verumax_academi.sessiones_evaluatio (Sesiones de estudiantes)
CREATE TABLE sessiones_evaluatio (
    id_sessio INT PRIMARY KEY AUTO_INCREMENT,
    id_evaluatio INT NOT NULL,
    id_miembro INT NOT NULL,                -- FK a nexus.miembros (estudiante)
    id_inscripcion INT,                     -- FK a inscripciones (si aplica)
    -- Progreso
    pregunta_actual INT DEFAULT 1,
    preguntas_completadas INT DEFAULT 0,
    total_preguntas INT,
    progreso_json JSON,                     -- Estado detallado por pregunta
    -- Resultado
    estado ENUM('iniciada', 'en_progreso', 'completada', 'abandonada') DEFAULT 'iniciada',
    puntaje_obtenido DECIMAL(5,2),
    puntaje_maximo DECIMAL(5,2),
    porcentaje DECIMAL(5,2),
    aprobado BOOLEAN,
    reflexion_final TEXT,                   -- Cierre cualitativo
    -- Auditoría
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_ultima_actividad TIMESTAMP,
    fecha_finalizacion TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,

    UNIQUE KEY unique_estudiante_evaluacion (id_evaluatio, id_miembro),
    INDEX idx_evaluatio (id_evaluatio),
    INDEX idx_miembro (id_miembro),
    FOREIGN KEY (id_evaluatio) REFERENCES evaluationes(id_evaluatio),
    FOREIGN KEY (id_miembro) REFERENCES verumax_nexus.miembros(id_miembro)
);

-- verumax_academi.responsa (Respuestas/Intentos)
CREATE TABLE responsa (
    id_responsum INT PRIMARY KEY AUTO_INCREMENT,
    id_sessio INT NOT NULL,
    id_quaestio INT NOT NULL,
    intento_numero INT DEFAULT 1,
    respuestas_seleccionadas JSON,          -- ["A", "C"] para múltiple respuesta
    es_correcta BOOLEAN,
    tiempo_respuesta_segundos INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    INDEX idx_sessio (id_sessio),
    INDEX idx_quaestio (id_quaestio),
    FOREIGN KEY (id_sessio) REFERENCES sessiones_evaluatio(id_sessio) ON DELETE CASCADE,
    FOREIGN KEY (id_quaestio) REFERENCES quaestiones(id_quaestio)
);

-- Vista para estadísticas
CREATE VIEW v_estadisticas_evaluationes AS
SELECT
    e.id_evaluatio,
    e.codigo,
    e.nombre,
    COUNT(DISTINCT s.id_sessio) as total_sesiones,
    COUNT(DISTINCT CASE WHEN s.estado = 'completada' THEN s.id_sessio END) as completadas,
    COUNT(DISTINCT CASE WHEN s.aprobado = 1 THEN s.id_sessio END) as aprobados,
    AVG(CASE WHEN s.estado = 'completada' THEN s.porcentaje END) as promedio_porcentaje,
    AVG(TIMESTAMPDIFF(MINUTE, s.fecha_inicio, s.fecha_finalizacion)) as promedio_minutos
FROM evaluationes e
LEFT JOIN sessiones_evaluatio s ON e.id_evaluatio = s.id_evaluatio
GROUP BY e.id_evaluatio;
```

### Estructura de Archivos Probatio

```
probatio/                              # Motor central de evaluaciones
├── config.php                         # Configuración y conexiones
├── accedere.php                       # Pantalla de acceso (ingreso DNI)
├── respondere.php                     # Formulario de evaluación
├── verificare.php                     # Verificación de respuestas (AJAX)
├── salvare.php                        # Persistencia de progreso
├── resultatum.php                     # Pantalla de resultados
└── api/
    ├── validare_dni.php               # Valida DNI contra inscriptos
    ├── get_quaestio.php               # Obtiene pregunta actual
    ├── submit_responsum.php           # Envía respuesta
    └── save_progress.php              # Guarda progreso parcial

sajur/                                 # Proxy institucional
└── eval-corrientes-2025/
    └── index.php                      # Redirige a probatio con contexto
```

### Flujo de Usuario

```
1. Estudiante accede a sajur.verumax.com/eval-corrientes-2025
                    ↓
2. Ingresa su DNI
                    ↓
3. Sistema valida:
   - DNI existe en verumax_nexus.miembros
   - Estudiante inscripto al curso vinculado (verumax_academi.inscripciones)
   - Evaluación está activa
                    ↓
4. Si hay sesión previa → recupera progreso
   Si no → crea nueva sesión
                    ↓
5. Muestra pregunta actual con opciones
                    ↓
6. Estudiante responde → verificación inmediata
   - Correcta: avanza a siguiente
   - Incorrecta: muestra explicación, permite reintentar
                    ↓
7. Al completar todas las preguntas → cierre cualitativo (si requerido)
                    ↓
8. Pantalla de resultados + opción de descargar certificado (si aprobó)
```

### Implementaciones Planificadas

| Evaluación | Institución | Curso | Estado |
|------------|-------------|-------|--------|
| EVAL-SAJUR-CORR-2025 | SAJuR | Diplomatura JR Corrientes 2025 | 🚧 En desarrollo |

---

## ROADMAP ACTUALIZADO

### FASE 0: Core Académico ✅ COMPLETADO

> Ya implementado en `certificatum/administrare.php`

- [x] CRUD de Estudiantes
- [x] CRUD de Cursos
- [x] CRUD de Inscripciones
- [x] CRUD de Docentes
- [x] CRUD de Participaciones Docentes
- [x] Importación masiva de datos
- [x] Gestión de Cohortes (parcial)
- [x] Competencias por curso
- [x] Trayectoria académica

### FASE 1: Módulo Probatio (Evaluaciones) 🚧 EN PROGRESO

**Objetivo:** Sistema de evaluaciones digitales integrado con inscripciones existentes.

- [ ] Crear tablas en `verumax_academi` (evaluationes, quaestiones, sessiones, responsa)
- [ ] Desarrollar motor de evaluaciones (`probatio/`)
- [ ] Implementar primera evaluación: SAJuR Corrientes 2025
- [ ] Panel de administración de evaluaciones (integrar en administrare.php)
- [ ] Reportes y estadísticas

### FASE 2: Portales de Auto-Servicio

**Objetivo:** Dar autonomía a docentes y estudiantes.

- [ ] **Portal del Docente:**
  - [ ] Ver sus cohortes y lista de alumnos
  - [ ] Cargar notas y asistencia
  - [ ] Ver resultados de evaluaciones de sus cursos
  - [ ] Enviar notificaciones a su cohorte

- [ ] **Portal del Estudiante:**
  - [ ] Ver su progreso académico, cursos y notas
  - [ ] Acceder a evaluaciones pendientes
  - [ ] Descargar certificados
  - [ ] Ver historial de evaluaciones

### FASE 3: Gestión de Formaciones

**Objetivo:** Agrupar cursos en programas más largos (Diplomaturas, Especializaciones).

- [ ] Implementar tabla `formaciones` y pivote `formacion_cursos`
- [ ] UI para crear y gestionar formaciones
- [ ] Certificado de formación completa (cuando se aprueban todos los cursos)

### FASE 4: LMS Ligero

**Objetivo:** Agregar contenidos educativos básicos.

- [ ] Módulos dentro de cursos
- [ ] Subida de materiales (PDFs, links)
- [ ] Calendario académico

### FASE 5: LMS Avanzado (Largo Plazo)

- [ ] Videos embebidos
- [ ] Foros de discusión
- [ ] Integración de pagos (Emporium)

---

## INTEGRACIÓN CON ECOSISTEMA VERUMAX

### Certificatum (Integración Nativa) ✅

**Flujo actual:**
1. Academicus (administrare.php) gestiona estudiantes, cursos, inscripciones
2. Cuando `inscripcion.estado = 'Aprobado'`, Certificatum puede generar certificado
3. Certificatum usa los datos de Academicus para poblar los documentos

**Con Probatio:**
- Al completar evaluación con éxito, puede actualizar automáticamente `inscripcion.estado`
- Trigger opcional: aprobar evaluación → habilitar certificado

### Nexus (CRM/MMS) ✅

**Integración actual:** Los estudiantes y docentes viven en `verumax_nexus.miembros`.
Academicus consume esta tabla como fuente de verdad de personas.

### Identitas / Vitae

El perfil profesional podrá mostrar:
- Cursos aprobados (de Academicus)
- Evaluaciones completadas (de Probatio)
- Certificados validables (de Certificatum)

### Communica (Email Marketing)

Permitirá enviar comunicaciones segmentadas:
- Por cohorte o curso
- Por estado de evaluación (no iniciada, en progreso, completada)
- Recordatorios automáticos

---

## STACK TECNOLÓGICO

* **Backend:** PHP 8+ (tipado estricto)
* **Base de Datos:** MySQL / MariaDB
* **Servicios:** Clases PSR-4 en `src/VERUMax/Services/`
* **Frontend:** Tailwind CSS + Vanilla JS / Alpine.js
* **PDF:** mPDF (documentos HTML) + TCPDF (certificados con imagen)

---

## NOTAS IMPORTANTES PARA DESARROLLO

### Compatibilidad de Datos

> **CRÍTICO:** Las tablas de Probatio deben usar las mismas convenciones que las existentes:
> - `id_instancia` para multi-tenancy (no `id_institucion`)
> - `id_miembro` referencia a `verumax_nexus.miembros` (no crear tabla separada de estudiantes)
> - `id_curso` referencia a `verumax_academi.cursos`
> - `id_inscripcion` referencia a `verumax_academi.inscripciones`

### Nomenclatura Latina

Mantener consistencia con el resto del sistema:
- `evaluationes` (no "evaluaciones")
- `quaestiones` (no "preguntas")
- `sessiones_evaluatio` (no "sesiones")
- `responsa` (no "respuestas")

### Multi-Tenancy

Todas las queries deben filtrar por institución:
```php
// ✅ CORRECTO
$evaluaciones = query("SELECT * FROM evaluationes WHERE id_instancia = ?", [$id_instancia]);

// ❌ INCORRECTO (expone datos de otras instituciones)
$evaluaciones = query("SELECT * FROM evaluationes");
```

---

**Última actualización:** 20 de Diciembre de 2025
**Archivos relacionados:** `PLAN_PROBATIO_SAJUR_CORRIENTES_2025.md`, `certificatum.md`, `CLAUDE.md`
