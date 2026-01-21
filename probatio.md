# PROBATIO - SISTEMA DE EVALUACIONES

**Carpeta Motor Central:** `probatio/`
**Color Distintivo:** Azul Profundo (#1E3A8A)
**Estado:** En Desarrollo

---

## CONCEPTO GENERAL

### Nombres

**Nombre Técnico (Latin):** Probatio
- **Significado:** Prueba, examen, demostración
- **Raiz:** *probare* (probar, demostrar, verificar)
- **Plural:** Probationes

**Nombre Comercial:** VERUMax Probatio / Sistema de Evaluaciones Verificadas

**Lema:** *"Probatio: Scientia Confirmata"* (Conocimiento Confirmado)

### Propuesta de Valor

> "Transforma la evaluación de un filtro punitivo a una herramienta de aprendizaje: garantiza que todos los certificados representen conocimiento real, sin dejar nadie atras."

### Filosofia

Probatio se aleja del modelo de evaluación tradicional (punitivo) para adoptar un enfoque **formativo**. El objetivo no es "desaprobar" estudiantes, sino asegurar que todos los que obtienen el certificado posean los conocimientos criticos. Esto se logra mediante una metodologia de **refuerzo inmediato** y **obligatoriedad de comprensión**.

---

## METODOLOGIA PEDAGOGICA: "EVALUACIÓN POR AFIRMACIÓN"

### Principios Fundamentales

| Principio | Modelo Tradicional | Modelo Probatio |
|-----------|-------------------|-----------------|
| **Objetivo** | Filtrar/Desaprobar | Garantizar aprendizaje |
| **Intentos** | Limitados (1-3) | Ilimitados |
| **Feedback** | Al final | Inmediato por pregunta |
| **Avance** | Siempre permitido | Solo con respuesta correcta |
| **Resultado** | Nota numerica | Completitud (100%) |
| **Enfoque** | Competencia | Dominio del conocimiento |

### Flujo de Evaluación

```
Estudiante accede con DNI
        |
        v
Pregunta 1 de 10
        |
    Responde
        |
        v
    +--------+
    | ¿Es    |
    |correcta|
    +--------+
     |      |
    SI     NO
     |      |
     v      v
  Avanza   Muestra explicación
  a P2     técnica del error
            |
            v
         Reintenta
         (mismo lugar)
```

### Caracteristicas del Modelo

**1. Retroalimentación Inmediata (Feedback Loop)**
- **Al acertar:** Confirmación visual + permite avanzar
- **Al fallar:** Explicación técnica del error + solicita nuevo intento

**2. Múltiples Respuestas Correctas**
- Evita el acierto por azar
- Exige lectura comprensiva profunda
- Puede haber 1, 2, 3 o mas respuestas correctas simultaneas

**3. Condición de Aprobación: Completitud**
- La aprobación se da al responder correctamente el 100% de las preguntas
- No hay "nota de corte" - el sistema garantiza dominio total
- La institución tiene certeza de que el alumno domina todos los puntos clave

**4. Intentos Auditados**
- Aunque ilimitados, cada intento queda registrado
- Genera estadisticas de dificultad por pregunta
- El estudiante sabe que sus intentos son monitoreados

---

## MODELO DE EVALUACIÓN SAJuR - CORRIENTES 2025

### Estructura del Examen

| Aspecto | Valor |
|---------|-------|
| **Cantidad de Preguntas** | 10 |
| **Opciones por Pregunta** | 5 |
| **Respuestas Correctas** | 1 o mas por pregunta |
| **Tiempo Limite** | Sin limite (persistencia) |
| **Intentos** | Ilimitados (auditados) |
| **Cierre Cualitativo** | 1 pregunta abierta obligatoria |

### Formato de Opciones (5 por pregunta)

```
Pregunta: [Enunciado de la situacion problemática]

[ ] A. Primera opción especifica
[ ] B. Segunda opción especifica
[ ] C. Tercera opción especifica
[ ] D. Todas las anteriores
[ ] E. Ninguna de las anteriores
```

**Reglas de Respuestas Correctas:**
- Si "Todas" es correcta → A, B, C tambien deben ser correctas
- Si "Ninguna" es correcta → A, B, C deben ser incorrectas
- Puede haber combinaciones: A+C correctas, solo B correcta, etc.

### Ejemplo de Pregunta

```
SITUACIÓN 3:

Un adolescente de 16 años causó daños a un vehículo durante
una protesta. La víctima solicita reparación del daño.
En el marco de la Justicia Restaurativa, ¿qué acciones
corresponden?

[ ] A. Convocar un círculo restaurativo con el adolescente,
       la víctima y sus familias
[ ] B. Derivar directamente al sistema penal juvenil
[ ] C. Proponer un acuerdo de reparación que incluya trabajo
       comunitario supervisado
[ ] D. Todas las anteriores
[ ] E. Ninguna de las anteriores

Respuestas correctas: A, C
(B es incorrecta porque la JR busca evitar el sistema penal)
```

---

## EXPERIENCIA DEL USUARIO (UX)

### 1. Acceso Simplificado

```
URL: sajur.verumax.com/eval-corrientes-2025

+------------------------------------------+
|                                          |
|   [Logo SAJuR]                          |
|                                          |
|   EVALUACIÓN FINAL                       |
|   Diplomatura en Justicia Restaurativa   |
|   Corrientes 2025                        |
|                                          |
|   +----------------------------------+   |
|   |  Ingrese su DNI                  |   |
|   |  [____________] [Ingresar]       |   |
|   +----------------------------------+   |
|                                          |
|   * No requiere contraseña               |
|   * Su progreso se guarda automaticamente|
|                                          |
+------------------------------------------+
```

**Validación:**
- DNI debe existir en la base de inscriptos del curso vinculado
- Si no existe → Error: "No esta inscrito en este curso"
- Si existe → Acceso a la evaluación

### 2. Continuidad y Persistencia

```
Estudiante cierra navegador en Pregunta 5
           |
           | (1 hora despues)
           v
Estudiante vuelve a ingresar
           |
           v
Sistema detecta sesión guardada
           |
           v
"Bienvenido/a de vuelta. Continuarás
 desde la Pregunta 5."
           |
           v
Se muestra Pregunta 5 (exactamente donde dejó)
```

**Datos que se persisten:**
- Pregunta actual
- Respuestas ya completadas
- Intentos por pregunta
- Timestamp de inicio

### 3. Interfaz de Pregunta

```
+--------------------------------------------------+
|  [Logo SAJuR]              Pregunta 5 de 10      |
+--------------------------------------------------+
|                                                  |
|  SITUACIÓN 5:                                    |
|                                                  |
|  [Enunciado de la situación problemática         |
|   con suficiente contexto para evitar            |
|   ambiguedades...]                               |
|                                                  |
|  +--------------------------------------------+  |
|  | [ ] A. Primera opción                      |  |
|  +--------------------------------------------+  |
|  | [ ] B. Segunda opción                      |  |
|  +--------------------------------------------+  |
|  | [ ] C. Tercera opción                      |  |
|  +--------------------------------------------+  |
|  | [ ] D. Todas las anteriores                |  |
|  +--------------------------------------------+  |
|  | [ ] E. Ninguna de las anteriores           |  |
|  +--------------------------------------------+  |
|                                                  |
|  [      Verificar Respuesta      ]               |
|                                                  |
|  Intentos en esta pregunta: 2                    |
|                                                  |
+--------------------------------------------------+
|  Progreso: [====----] 40%                        |
+--------------------------------------------------+
```

### 4. Feedback Inmediato

**Respuesta Correcta:**
```
+--------------------------------------------------+
|  ✓ CORRECTO                                     |
|                                                  |
|  Has seleccionado correctamente las opciones     |
|  A y C.                                          |
|                                                  |
|  [Breve refuerzo del concepto]                  |
|                                                  |
|  [      Continuar a Pregunta 6      ]           |
+--------------------------------------------------+
```

**Respuesta Incorrecta:**
```
+--------------------------------------------------+
|  ✗ INCORRECTO                                   |
|                                                  |
|  Tu respuesta no es correcta.                   |
|                                                  |
|  EXPLICACIÓN:                                    |
|  La opción B no corresponde porque en el         |
|  marco de la Justicia Restaurativa, la          |
|  derivación directa al sistema penal se         |
|  considera como último recurso, no como         |
|  primera opción...                              |
|                                                  |
|  Por favor, revise la pregunta y vuelva a       |
|  intentar.                                       |
|                                                  |
|  [      Reintentar      ]                       |
+--------------------------------------------------+
```

### 5. Cierre Cualitativo

Al completar las 10 preguntas:

```
+--------------------------------------------------+
|  ✓ EVALUACIÓN COMPLETADA                        |
|                                                  |
|  Felicitaciones, has respondido correctamente   |
|  todas las preguntas.                           |
|                                                  |
|  PASO FINAL - Reflexión Personal                |
|                                                  |
|  Por favor, comparte una reflexión, consulta    |
|  o comentario sobre el curso:                   |
|                                                  |
|  +--------------------------------------------+ |
|  |                                            | |
|  |                                            | |
|  |                                            | |
|  +--------------------------------------------+ |
|  (Mínimo 50 caracteres)                         |
|                                                  |
|  [      Enviar y Finalizar      ]               |
|                                                  |
+--------------------------------------------------+
```

### 6. Confirmación Final

```
+--------------------------------------------------+
|                                                  |
|        ✓ EVALUACIÓN FINALIZADA                  |
|                                                  |
|  Nombre: Juan Pérez                             |
|  DNI: 25.123.456                                |
|  Curso: Diplomatura en JR - Corrientes 2025    |
|                                                  |
|  Estado: APROBADO                               |
|  Fecha: 19 de Diciembre de 2025                 |
|                                                  |
|  Tu certificado estará disponible en            |
|  las próximas 48 horas.                         |
|                                                  |
|  [   Ver mis certificados en SAJuR   ]          |
|                                                  |
+--------------------------------------------------+
```

---

## ARQUITECTURA TÉCNICA

### Estructura de Carpetas

```
/appVerumax/
|
+-- probatio/                          # Motor central
|   +-- config.php                     # Configuración BD
|   +-- index.php                      # Landing/selector
|   +-- accedere.php                   # Validación DNI + curso
|   +-- respondere.php                 # Formulario de evaluación
|   +-- verificare.php                 # Verificar respuesta (AJAX)
|   +-- salvare.php                    # Guardar progreso (AJAX)
|   +-- resultatum.php                 # Resultado final
|   +-- gratias.php                    # Agradecimiento
|   |
|   +-- administrare/                  # Panel administrativo
|   |   +-- index.php                  # Dashboard
|   |   +-- evaluationes.php           # CRUD evaluaciones
|   |   +-- quaestiones.php            # Editor de preguntas
|   |   +-- responsa.php               # Ver respuestas
|   |   +-- statisticae.php            # Estadísticas
|   |   +-- exportare.php              # Exportar resultados
|   |
|   +-- api/                           # Endpoints AJAX
|   |   +-- validare_dni.php
|   |   +-- get_quaestio.php
|   |   +-- submit_responsum.php
|   |   +-- save_progress.php
|   |
|   +-- templates/                     # Plantillas de UI
|       +-- pregunta.php
|       +-- feedback_correcto.php
|       +-- feedback_incorrecto.php
|
+-- sajur/
|   +-- eval-corrientes-2025/          # Proxy para este examen
|       +-- index.php                  # Redirige con contexto
|       +-- .htaccess
|
+-- src/VERUMax/Services/
    +-- ProbatioService.php            # Lógica de evaluaciones
```

### Base de Datos: verumax_probatio

```sql
-- Tabla principal de evaluaciones
CREATE TABLE evaluationes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_instancia INT NOT NULL,              -- FK a verumax_general.instancias
    codigo VARCHAR(50) UNIQUE NOT NULL,     -- 'EVAL-CORR-2025'
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,

    -- Vinculación con curso (Academicus/Certificatum)
    id_curso INT NULL,                      -- FK a verumax_academi.cursos
    codigo_curso VARCHAR(50) NULL,          -- Alternativa: código del curso

    -- Configuración temporal
    fecha_inicio DATETIME NULL,
    fecha_fin DATETIME NULL,

    -- Configuración de evaluación
    tipo ENUM('examen', 'encuesta', 'autoevaluacion') DEFAULT 'examen',
    requiere_cierre_cualitativo BOOLEAN DEFAULT TRUE,
    texto_cierre_cualitativo TEXT,          -- Pregunta abierta final
    minimo_caracteres_cierre INT DEFAULT 50,

    -- Estados
    estado ENUM('borrador', 'activa', 'pausada', 'cerrada') DEFAULT 'borrador',

    -- Mensajes personalizados
    mensaje_bienvenida TEXT,
    mensaje_finalizacion TEXT,

    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT NULL,

    INDEX idx_instancia (id_instancia),
    INDEX idx_curso (id_curso),
    INDEX idx_estado (estado)
);

-- Preguntas de la evaluación
CREATE TABLE quaestiones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_evaluatio INT NOT NULL,
    orden INT NOT NULL,                     -- Orden de aparición

    -- Contenido
    enunciado TEXT NOT NULL,                -- Situación problemática

    -- Opciones (JSON array)
    -- Formato: [
    --   {"letra": "A", "texto": "...", "es_correcta": true},
    --   {"letra": "B", "texto": "...", "es_correcta": false},
    --   {"letra": "C", "texto": "...", "es_correcta": true},
    --   {"letra": "D", "texto": "Todas las anteriores", "es_correcta": false, "tipo": "todas"},
    --   {"letra": "E", "texto": "Ninguna de las anteriores", "es_correcta": false, "tipo": "ninguna"}
    -- ]
    opciones JSON NOT NULL,

    -- Feedback
    explicacion_correcta TEXT,              -- Se muestra al acertar
    explicacion_incorrecta TEXT NOT NULL,   -- Se muestra al fallar (OBLIGATORIO)

    -- Metadatos
    activa BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_evaluatio) REFERENCES evaluationes(id) ON DELETE CASCADE,
    INDEX idx_evaluatio_orden (id_evaluatio, orden)
);

-- Sesiones de evaluación (un estudiante respondiendo)
CREATE TABLE sessiones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_evaluatio INT NOT NULL,
    dni_estudiante VARCHAR(20) NOT NULL,
    id_instancia INT NOT NULL,

    -- Estado de la sesión
    estado ENUM('en_progreso', 'completada', 'abandonada') DEFAULT 'en_progreso',
    pregunta_actual INT DEFAULT 1,          -- 1-10

    -- Progreso guardado (JSON)
    -- Formato: {
    --   "1": {"completada": true, "intentos": 2},
    --   "2": {"completada": true, "intentos": 1},
    --   "3": {"completada": false, "intentos": 3}
    -- }
    progreso JSON,

    -- Cierre cualitativo
    reflexion_final TEXT NULL,

    -- Timestamps
    fecha_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_ultima_actividad DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_finalizacion DATETIME NULL,

    -- Metadatos
    ip_address VARCHAR(45),
    user_agent TEXT,

    FOREIGN KEY (id_evaluatio) REFERENCES evaluationes(id),
    UNIQUE KEY uk_evaluatio_dni (id_evaluatio, dni_estudiante),
    INDEX idx_estado (estado),
    INDEX idx_dni (dni_estudiante)
);

-- Respuestas individuales (detalle de cada intento)
CREATE TABLE responsa (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_sessio INT NOT NULL,
    id_quaestio INT NOT NULL,

    -- Respuesta dada
    -- Formato: ["A", "C"] o ["B"]
    respuesta_dada JSON NOT NULL,

    -- Resultado
    es_correcta BOOLEAN NOT NULL,
    intento_numero INT NOT NULL,            -- 1, 2, 3, ...

    -- Timestamp
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (id_sessio) REFERENCES sessiones(id) ON DELETE CASCADE,
    FOREIGN KEY (id_quaestio) REFERENCES quaestiones(id),
    INDEX idx_sessio_quaestio (id_sessio, id_quaestio)
);

-- Vista para estadísticas de dificultad
CREATE VIEW v_estadisticas_preguntas AS
SELECT
    q.id AS id_pregunta,
    q.id_evaluatio,
    q.orden,
    COUNT(DISTINCT s.dni_estudiante) AS total_estudiantes,
    AVG(
        JSON_EXTRACT(s.progreso, CONCAT('$."', q.orden, '".intentos'))
    ) AS promedio_intentos,
    SUM(CASE
        WHEN JSON_EXTRACT(s.progreso, CONCAT('$."', q.orden, '".intentos')) > 3
        THEN 1 ELSE 0
    END) AS estudiantes_mas_3_intentos,
    ROUND(
        SUM(CASE
            WHEN JSON_EXTRACT(s.progreso, CONCAT('$."', q.orden, '".intentos')) > 3
            THEN 1 ELSE 0
        END) * 100.0 / COUNT(DISTINCT s.dni_estudiante),
        2
    ) AS porcentaje_dificultad
FROM quaestiones q
LEFT JOIN sessiones s ON q.id_evaluatio = s.id_evaluatio
WHERE s.estado = 'completada'
GROUP BY q.id, q.id_evaluatio, q.orden;
```

### Integración con Verumax Existente

```
+-------------------+      +-------------------+      +-------------------+
|    ACADEMICUS     |      |     PROBATIO      |      |   CERTIFICATUM    |
|  (cursos, est.)   |----->|   (evaluaciones)  |----->|   (certificados)  |
+-------------------+      +-------------------+      +-------------------+
        |                          |                          |
        |   Valida inscripción    |   Libera certificado    |
        |   en el curso           |   al aprobar            |
        |                          |                          |
        +------------+-------------+-------------+------------+
                     |                           |
                     v                           v
           +-------------------+       +-------------------+
           | verumax_general   |       |   verumax_nexus   |
           | (instituciones)   |       |    (miembros)     |
           +-------------------+       +-------------------+
```

**Flujo de integración:**
1. Probatio consulta `verumax_academi.inscripciones` para validar que el DNI esta inscrito en el curso vinculado
2. Al completar la evaluación, Probatio puede:
   - Marcar el estudiante como "apto para certificar" en Academicus
   - Disparar webhook a Certificatum para liberar certificado
   - Actualizar estado en `verumax_nexus.miembros`

---

## MÉTRICAS Y ANALYTICS

### Para la Institución (Dashboard Admin)

**Métricas de Participación:**
- Total de estudiantes que iniciaron
- Total de estudiantes que completaron
- Tasa de completitud (%)
- Tiempo promedio para completar

**Métricas de Dificultad por Pregunta:**
```
Pregunta | Promedio Intentos | >3 Intentos | Dificultad
---------|-------------------|-------------|------------
   1     |       1.2         |     5%      | Facil
   2     |       1.8         |    15%      | Media
   3     |       3.5         |    60%      | Difícil ⚠️
   ...
```

**Insights Automaticos:**
- "El 60% de los estudiantes requirió mas de 3 intentos en la Pregunta 3"
- "Tiempo promedio en la Pregunta 7: 8 minutos (vs 2 min promedio general)"
- "Recomendación: revisar el material sobre [tema de pregunta 3]"

### Datos Cualitativos

**Banco de Reflexiones Finales:**
- Todas las respuestas de cierre cualitativo almacenadas
- Exportables a Excel/CSV
- Analisis de sentimiento (futuro)
- Nube de palabras frecuentes (futuro)

---

## PLANES DE SUSCRIPCIÓN

Probatio se integra al esquema de planes existente de Verumax:

| Plan | Evaluaciones/mes | Preguntas | Features |
|------|------------------|-----------|----------|
| **Basicum** | 1 activa | 10 max | Basico, sin estadisticas |
| **Premium** | 5 activas | 20 max | + Estadisticas, export |
| **Excellens** | 20 activas | Ilimitadas | + Analytics avanzados |
| **Supremus** | Ilimitadas | Ilimitadas | + API, webhooks, white-label |

---

## IMPLEMENTACIÓN INICIAL: SAJUR CORRIENTES 2025

### Configuración Especifica

| Campo | Valor |
|-------|-------|
| **Código** | EVAL-SAJUR-CORR-2025 |
| **Nombre** | Evaluación Final - Diplomatura JR Corrientes 2025 |
| **Curso vinculado** | [ID del curso en verumax_academi] |
| **URL Publica** | sajur.verumax.com/eval-corrientes-2025 |
| **URL Local** | sajur.verumax.local/eval-corrientes-2025 |
| **Preguntas** | 10 |
| **Cierre cualitativo** | Si (obligatorio) |

### Contenido de las 10 Preguntas

> **PENDIENTE:** Recibir las 10 situaciones problemáticas con sus opciones y respuestas correctas del equipo de SAJuR.

**Formato esperado por pregunta:**
```
PREGUNTA N:
Enunciado: [Situación problemática]
Opciones:
  A. [Texto opción A] - Correcta: Si/No
  B. [Texto opción B] - Correcta: Si/No
  C. [Texto opción C] - Correcta: Si/No
  D. Todas las anteriores - Correcta: Si/No
  E. Ninguna de las anteriores - Correcta: Si/No
Explicación error: [Texto que se muestra cuando falla]
```

---

## ROADMAP DE DESARROLLO

### FASE 1: MVP Funcional (Urgente)

- [ ] Crear estructura de carpetas `probatio/`
- [ ] Crear base de datos `verumax_probatio`
- [ ] Desarrollar `accedere.php` (validación DNI)
- [ ] Desarrollar `respondere.php` (formulario de evaluación)
- [ ] Implementar persistencia de progreso
- [ ] Implementar feedback inmediato (correcto/incorrecto)
- [ ] Desarrollar cierre cualitativo
- [ ] Crear proxy `sajur/eval-corrientes-2025/`
- [ ] Cargar las 10 preguntas de SAJuR
- [ ] Testing con usuarios reales

### FASE 2: Panel Admin

- [ ] Dashboard con estadísticas
- [ ] CRUD de evaluaciones
- [ ] Editor de preguntas
- [ ] Visor de respuestas
- [ ] Exportación de datos

### FASE 3: Integraciones

- [ ] Webhook a Certificatum (liberar certificado)
- [ ] Sincronización con Academicus
- [ ] Notificaciones por email
- [ ] API publica

---

## SEGURIDAD

### Validaciones Implementadas

1. **Acceso por DNI:** Solo estudiantes inscriptos en el curso
2. **Sesión única:** Un estudiante = una sesión por evaluación
3. **Persistencia segura:** Progreso en servidor (no localStorage)
4. **Rate limiting:** Prevenir intentos masivos automatizados
5. **CSRF tokens:** En todos los formularios

### Prevención de Trampas

- No se muestran respuestas correctas hasta acertar
- Opciones en orden fijo (no aleatorio) para consistencia pedagogica
- Log de todas las respuestas y tiempos
- Detección de patrones sospechosos (tiempo muy corto entre respuestas)

---

## ARCHIVOS A CREAR

### Primera Iteración (MVP)

```
probatio/
  config.php
  accedere.php
  respondere.php
  verificare.php (AJAX)
  salvare.php (AJAX)
  resultatum.php
  gratias.php

sajur/eval-corrientes-2025/
  index.php
  .htaccess
```

### Base de Datos

```sql
-- Ejecutar en servidor MySQL
CREATE DATABASE IF NOT EXISTS verumax_probatio;
USE verumax_probatio;
-- [Tablas definidas arriba]
```

---

**Última actualización:** 19 de Diciembre de 2025
**Creado por:** Claude Code + Pampa
**Archivos relacionados:** `probatio/`, `sajur/eval-corrientes-2025/`
