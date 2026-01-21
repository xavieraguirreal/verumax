# PLAN DE TRABAJO: Probatio - SAJuR Corrientes 2025

**Proyecto:** Módulo Probatio (Evaluaciones) - Parte de Academicus
**Cliente:** SAJuR - Sociedad Argentina de Justicia Restaurativa
**Evaluación:** Diplomatura en Justicia Restaurativa - Corrientes 2025
**Estado:** Planificado
**Fecha inicio plan:** 19 de Diciembre de 2025

> **NOTA ARQUITECTÓNICA:** Probatio es el módulo de evaluaciones de **Academicus**.
> Las tablas viven en `verumax_academi` y usan las mismas convenciones del sistema.
> Ver `academicus.md` para contexto completo.

---

## RESUMEN EJECUTIVO

Implementar un sistema de evaluación digital con metodología "Evaluación por Afirmación" donde:

- 10 preguntas con múltiples respuestas correctas
- Acceso con DNI (sin login)
- Se puede avanzar aunque responda incorrectamente
- Feedback inmediato con explicación del error o ampliando la respuesta correcta
- Persistencia del progreso
- Cierre cualitativo obligatorio
- Intentos ilimitados pero auditados

**URLs de acceso:**

- Producción: `sajur.verumax.com/eval-corrientes-2025`
- Local: `sajur.verumax.local/eval-corrientes-2025`

---

## FASE 1: PREPARACIÓN (Previo al desarrollo)

### 1.1 Contenido de las Preguntas

**Estado:** [ ] Pendiente
**Responsable:** Equipo SAJuR

Obtener las 10 situaciones problemáticas con el siguiente formato:

```
PREGUNTA N:
Enunciado: [Situación problemática detallada]

Opciones:
  A. [Texto opción A] → Correcta: Si/No
  B. [Texto opción B] → Correcta: Si/No
  C. [Texto opción C] → Correcta: Si/No
  D. Todas las anteriores → Correcta: Si/No
  E. Ninguna de las anteriores → Correcta: Si/No

Explicación del error:
[Texto pedagógico que se muestra cuando el estudiante falla,
explicando por qué la respuesta es incorrecta y reforzando
el concepto correcto o la ampliación en caso de responder correctamente]
```

### 1.2 Identificar Curso Vinculado

**Estado:** [ ] Pendiente

- [ ] Obtener el `id_curso` o `codigo_curso` de la Diplomatura en verumax_academi
- [ ] Verificar que los estudiantes inscriptos estén cargados en la base de datos
- [ ] Confirmar campo de DNI para validación

### 1.3 Textos Personalizados

**Estado:** [ ] Pendiente

Definir los siguientes textos:

| Elemento                    | Texto                                                                      |
| --------------------------- | -------------------------------------------------------------------------- |
| Mensaje de bienvenida       | [Por definir]                                                              |
| Pregunta cierre cualitativo | "Comparte una reflexión, consulta o comentario sobre el curso:" (sugerido) |
| Mensaje de finalización     | [Por definir]                                                              |
| Mínimo caracteres reflexión | 50 (sugerido)                                                              |

---

## FASE 2: INFRAESTRUCTURA (Backend)

### 2.1 Base de Datos

**Estado:** [ ] Pendiente
**Tiempo estimado:** 30 min

> **IMPORTANTE:** Las tablas se crean en `verumax_academi` (NO crear BD separada).
> Esto asegura compatibilidad con Academicus y evita migraciones futuras.

- [ ] Crear tabla `verumax_academi.evaluationes`
- [ ] Crear tabla `verumax_academi.quaestiones`
- [ ] Crear tabla `verumax_academi.sessiones_evaluatio`
- [ ] Crear tabla `verumax_academi.responsa`
- [ ] Crear vista `verumax_academi.v_estadisticas_evaluationes`
- [ ] Configurar conexión en `probatio/config.php` (usar misma conexión de academi)

**Archivo SQL a ejecutar:** Ver sección "MÓDULO PROBATIO" en `academicus.md`

### 2.2 Estructura de Carpetas

**Estado:** [ ] Pendiente
**Tiempo estimado:** 15 min

```
Crear:
  probatio/
    config.php
    accedere.php
    respondere.php
    verificare.php
    salvare.php
    resultatum.php
    gratias.php
    api/
      validare_dni.php
      get_quaestio.php
      submit_responsum.php
      save_progress.php

  sajur/
    eval-corrientes-2025/
      index.php
      .htaccess
```

### 2.3 Archivo de Configuración

**Estado:** [ ] Pendiente
**Tiempo estimado:** 20 min

`probatio/config.php`:

- [ ] Conexión a `verumax_academi` (evaluaciones + inscripciones + cursos)
- [ ] Conexión a `verumax_nexus` (miembros/estudiantes)
- [ ] Constantes de configuración
- [ ] Funciones helper (reutilizar de `certificatum/config.php`)

> **Nota:** Usar `id_instancia` para multi-tenancy, `id_miembro` para estudiantes (NO crear IDs propios)

---

## FASE 3: DESARROLLO CORE

### 3.1 Validación de Acceso (accedere.php)

**Estado:** [ ] Pendiente
**Tiempo estimado:** 1-2 horas

Funcionalidades:

- [ ] Formulario de ingreso con DNI
- [ ] Validar DNI contra inscriptos del curso vinculado
- [ ] Crear o recuperar sesión existente
- [ ] Redirigir a pregunta actual (persistencia)
- [ ] Manejo de errores (DNI no inscripto, evaluación cerrada, etc.)

**Endpoint API:** `api/validare_dni.php`

### 3.2 Formulario de Evaluación (respondere.php)

**Estado:** [ ] Pendiente
**Tiempo estimado:** 3-4 horas

Funcionalidades:

- [ ] Mostrar pregunta actual con opciones (checkboxes)
- [ ] Botón "Verificar Respuesta"
- [ ] Mostrar feedback inmediato (correcto/incorrecto)
- [ ] Bloquear avance hasta respuesta correcta
- [ ] Barra de progreso
- [ ] Contador de intentos por pregunta
- [ ] Auto-guardado de progreso

**Diseño:**

- [ ] Responsive (mobile, tablet, desktop)
- [ ] Branding SAJuR (colores, logo)
- [ ] Accesibilidad básica

### 3.3 Verificación de Respuesta (verificare.php)

**Estado:** [ ] Pendiente
**Tiempo estimado:** 1-2 horas

Endpoint AJAX que:

- [ ] Recibe: id_sesion, id_pregunta, respuestas_seleccionadas[]
- [ ] Compara con respuestas correctas
- [ ] Registra intento en tabla `responsa`
- [ ] Actualiza progreso en `sessiones`
- [ ] Retorna: es_correcta, explicacion, puede_avanzar

### 3.4 Persistencia de Progreso (salvare.php)

**Estado:** [ ] Pendiente
**Tiempo estimado:** 1 hora

- [ ] Auto-save cada N segundos
- [ ] Save on page unload (beforeunload)
- [ ] Guardar pregunta_actual y progreso JSON
- [ ] Actualizar fecha_ultima_actividad

### 3.5 Cierre Cualitativo

**Estado:** [ ] Pendiente
**Tiempo estimado:** 1 hora

- [ ] Mostrar después de pregunta 10 completada
- [ ] Textarea con mínimo de caracteres
- [ ] Validación client-side y server-side
- [ ] Guardar en campo reflexion_final

### 3.6 Resultado Final (resultatum.php)

**Estado:** [ ] Pendiente
**Tiempo estimado:** 1 hora

- [ ] Mostrar confirmación de aprobación
- [ ] Datos del estudiante y curso
- [ ] Fecha de finalización
- [ ] Link a certificados (si aplica)
- [ ] Mensaje de cierre personalizado

---

## FASE 4: PROXY INSTITUCIONAL

### 4.1 Carpeta SAJuR

**Estado:** [ ] Pendiente
**Tiempo estimado:** 30 min

`sajur/eval-corrientes-2025/index.php`:

- [ ] Detectar contexto (institución, evaluación)
- [ ] Redirigir a probatio/accedere.php con parámetros
- [ ] Mantener branding SAJuR

`sajur/eval-corrientes-2025/.htaccess`:

- [ ] Configurar URL amigables si es necesario

---

## FASE 5: CARGA DE DATOS

### 5.1 Crear Evaluación en BD

**Estado:** [ ] Pendiente
**Tiempo estimado:** 15 min

```sql
-- Insertar en verumax_academi.evaluationes
INSERT INTO verumax_academi.evaluationes (
    id_instancia,
    id_curso,
    codigo,
    nombre,
    descripcion,
    tipo,
    metodologia,
    requiere_cierre_cualitativo,
    texto_cierre_cualitativo,
    mensaje_bienvenida,
    mensaje_finalizacion,
    estado
) VALUES (
    1,  -- SAJuR (verificar id_instancia correcto)
    [ID_CURSO],  -- FK a verumax_academi.cursos
    'EVAL-SAJUR-CORR-2025',
    'Evaluación Final - Diplomatura JR Corrientes 2025',
    'Evaluación de conocimientos para certificación',
    'examen',
    'afirmacion',  -- Metodología: no avanza hasta responder bien
    TRUE,
    'Comparte una reflexión, consulta o comentario sobre el curso:',
    '[MENSAJE BIENVENIDA]',
    '[MENSAJE FINALIZACION]',
    'activa'
);
```

### 5.2 Cargar las 10 Preguntas

**Estado:** [ ] Pendiente
**Tiempo estimado:** 1 hora (depende de tener el contenido)

Por cada pregunta:

```sql
-- Insertar en verumax_academi.quaestiones
INSERT INTO verumax_academi.quaestiones (
    id_evaluatio,
    orden,
    tipo,
    enunciado,
    opciones,
    explicacion_incorrecta,
    es_obligatoria
) VALUES (
    [ID_EVALUATIO],  -- FK a evaluationes
    1,               -- Orden de la pregunta
    'multiple_answer',  -- Puede tener múltiples respuestas correctas
    '[ENUNCIADO DE LA SITUACIÓN PROBLEMÁTICA]',
    '[{"letra":"A","texto":"Opción A...","es_correcta":true},
      {"letra":"B","texto":"Opción B...","es_correcta":false},
      {"letra":"C","texto":"Opción C...","es_correcta":true},
      {"letra":"D","texto":"Todas las anteriores","es_correcta":false},
      {"letra":"E","texto":"Ninguna de las anteriores","es_correcta":false}]',
    '[EXPLICACIÓN PEDAGÓGICA DEL ERROR - Se muestra cuando responde mal]',
    TRUE
);
```

---

## FASE 6: TESTING

### 6.1 Testing Funcional

**Estado:** [ ] Pendiente
**Tiempo estimado:** 2 horas

- [ ] Acceso con DNI válido
- [ ] Acceso con DNI no inscripto (error esperado)
- [ ] Respuesta correcta → avanza
- [ ] Respuesta incorrecta → muestra explicación, no avanza
- [ ] Múltiples respuestas correctas funcionan
- [ ] Persistencia: cerrar y reabrir mantiene progreso
- [ ] Cierre cualitativo obligatorio
- [ ] Finalización exitosa

### 6.2 Testing de Edge Cases

**Estado:** [ ] Pendiente
**Tiempo estimado:** 1 hora

- [ ] Conexión lenta/intermitente
- [ ] Múltiples pestañas abiertas
- [ ] Navegador sin JavaScript (graceful degradation)
- [ ] Dispositivos móviles (iOS Safari, Android Chrome)
- [ ] Intentos de manipulación (DevTools)

### 6.3 Testing con Usuario Real

**Estado:** [ ] Pendiente
**Tiempo estimado:** 1 hora

- [ ] Un estudiante real completa la evaluación
- [ ] Feedback sobre UX
- [ ] Ajustes finales

---

## FASE 7: DEPLOYMENT

### 7.1 Subir a Producción

**Estado:** [ ] Pendiente

- [ ] Crear base de datos en servidor producción
- [ ] Subir archivos a sajur.verumax.com
- [ ] Configurar conexiones de BD
- [ ] Verificar URLs y rutas
- [ ] Probar acceso público

### 7.2 Comunicar a SAJuR

**Estado:** [ ] Pendiente

- [ ] URL de acceso para estudiantes
- [ ] Instrucciones de uso
- [ ] Panel de seguimiento (si hay)

---

## FASE 8: PANEL DE ADMINISTRACIÓN DE EVALUACIONES

> **Ubicación:** Nueva pestaña "Evaluaciones" en `sajur.verumax.com/admin/` (o `certificatum/administrare.php`)
> **Objetivo:** Permitir al cliente crear, configurar y gestionar evaluaciones sin intervención técnica.

### 8.1 CRUD de Evaluaciones

**Estado:** [ ] Pendiente
**Tiempo estimado:** 3-4 horas

**Listado de evaluaciones:**
- [ ] Tabla con: código, nombre, curso vinculado, estado, fecha inicio/fin, completadas/total
- [ ] Filtros: por estado (borrador, activa, cerrada), por curso
- [ ] Acciones rápidas: activar/desactivar, duplicar, eliminar

**Formulario crear/editar evaluación:**
- [ ] Campos básicos:
  - Código único (auto-generado o manual)
  - Nombre de la evaluación
  - Descripción
  - Curso vinculado (dropdown de cursos activos)
  - Cohorte específica (opcional)
- [ ] Configuración de metodología:
  - Tipo: examen, quiz, encuesta, autoevaluación
  - Metodología: afirmación (no avanza sin acertar), tradicional (siempre avanza), adaptive
  - Permite múltiples intentos: Sí/No
  - Muestra respuestas correctas al finalizar: Sí/No
- [ ] Cierre cualitativo:
  - Requiere reflexión final: Sí/No
  - Texto de la pregunta de cierre
  - Mínimo de caracteres
- [ ] Mensajes personalizados:
  - Mensaje de bienvenida (HTML permitido)
  - Mensaje de finalización
  - Mensaje para DNI no inscripto
- [ ] Estado y disponibilidad:
  - Estado: borrador, activa, cerrada
  - Fecha de inicio (opcional)
  - Fecha de fin (opcional)

### 8.2 CRUD de Preguntas (Quaestiones)

**Estado:** [ ] Pendiente
**Tiempo estimado:** 4-5 horas

**Listado de preguntas por evaluación:**
- [ ] Vista ordenable (drag & drop para reordenar)
- [ ] Mostrar: orden, tipo, preview del enunciado, cantidad de opciones
- [ ] Acciones: editar, duplicar, eliminar, mover arriba/abajo

**Formulario crear/editar pregunta:**
- [ ] Tipo de pregunta (selector):
  - `multiple_choice` - Una sola respuesta correcta (radio buttons)
  - `multiple_answer` - Múltiples respuestas correctas (checkboxes)
  - `verdadero_falso` - Solo V/F
  - `abierta` - Texto libre (sin validación automática)
- [ ] Enunciado:
  - Editor de texto enriquecido (negrita, cursiva, listas)
  - Soporte para imágenes (opcional)
- [ ] Opciones de respuesta (para tipos choice):
  - Agregar/quitar opciones dinámicamente
  - Por cada opción: letra, texto, checkbox "es correcta"
  - Mínimo 2 opciones, máximo 6
- [ ] Feedback pedagógico:
  - Explicación cuando responde CORRECTAMENTE (ampliación)
  - Explicación cuando responde INCORRECTAMENTE (refuerzo)
- [ ] Configuración:
  - Puntos (default 1)
  - Es obligatoria: Sí/No

**Preview de pregunta:**
- [ ] Vista previa de cómo se verá la pregunta para el estudiante
- [ ] Modo "probar" para verificar que funciona

### 8.3 Vinculación con Cursos

**Estado:** [ ] Pendiente
**Tiempo estimado:** 1 hora

- [ ] Dropdown de cursos desde `verumax_academi.cursos` (filtrado por institución)
- [ ] Opción de vincular a cohorte específica (opcional)
- [ ] Mostrar cantidad de inscriptos que podrán acceder
- [ ] Validación: no permitir activar evaluación sin curso vinculado

### 8.4 Dashboard de Estadísticas

**Estado:** [ ] Futuro (post-lanzamiento)
**Tiempo estimado:** 2-3 horas

- [ ] Métricas generales:
  - Total que iniciaron vs completaron
  - Tasa de finalización (%)
  - Tiempo promedio de completación
- [ ] Métricas por pregunta:
  - Promedio de intentos por pregunta
  - Preguntas más difíciles (mayor cantidad de intentos)
  - Distribución de respuestas por opción
- [ ] Gráficos visuales (Chart.js o similar)

### 8.5 Visor de Respuestas

**Estado:** [ ] Futuro (post-lanzamiento)
**Tiempo estimado:** 2 horas

- [ ] Lista de estudiantes que completaron
- [ ] Por estudiante: fecha inicio, fecha fin, tiempo total, puntaje
- [ ] Detalle de intentos por pregunta
- [ ] Visor de reflexiones finales (con búsqueda)

### 8.6 Exportación

**Estado:** [ ] Futuro (post-lanzamiento)
**Tiempo estimado:** 1 hora

- [ ] Export CSV de resultados (estudiante, puntaje, fecha, aprobado)
- [ ] Export de reflexiones finales
- [ ] Reporte de dificultad por pregunta

---

## DEPENDENCIAS Y BLOQUEOS

| Tarea                      | Bloqueada por                        |
| -------------------------- | ------------------------------------ |
| Crear tablas en BD         | Nada (puede empezar ya)              |
| Cargar preguntas via SQL   | Contenido de SAJuR + tablas creadas  |
| Validar inscriptos         | ID del curso en BD                   |
| Motor de evaluaciones      | Tablas creadas                       |
| Testing funcional          | Motor completado + preguntas cargadas|
| Deployment                 | Testing aprobado                     |
| Panel Admin CRUD           | Tablas creadas (independiente del motor) |
| Panel Admin Estadísticas   | Motor en producción + datos reales   |

**Nota:** El Panel Admin CRUD puede desarrollarse en paralelo con el motor de evaluaciones.

---

## ESTIMACIÓN DE TIEMPO TOTAL

| Fase                          | Tiempo           | Prioridad |
| ----------------------------- | ---------------- | --------- |
| Preparación                   | Depende de SAJuR | -         |
| Infraestructura (BD + config) | ~1 hora          | Alta      |
| Desarrollo Core (motor eval)  | ~8-10 horas      | Alta      |
| Proxy Institucional           | ~30 min          | Alta      |
| Carga de Datos (primera eval) | ~1-2 horas       | Alta      |
| Testing                       | ~4 horas         | Alta      |
| Deployment                    | ~1 hora          | Alta      |
| **Panel Admin - CRUD**        | **~8-9 horas**   | Media     |
| Panel Admin - Estadísticas    | ~5-6 horas       | Baja      |
| **TOTAL MVP (sin panel)**     | **~16-18 horas** |           |
| **TOTAL CON PANEL CRUD**      | **~24-27 horas** |           |
| **TOTAL COMPLETO**            | **~30-33 horas** |           |

### Estrategia de Entrega

**Opción A - MVP Rápido (para SAJuR Corrientes 2025):**
1. Desarrollar motor de evaluaciones (Fases 2-7)
2. Cargar preguntas manualmente via SQL
3. Panel Admin en siguiente iteración

**Opción B - Producto Completo:**
1. Desarrollar Panel Admin primero (Fase 8.1-8.3)
2. SAJuR carga sus propias preguntas desde la UI
3. Más tiempo inicial, pero reutilizable para otras instituciones

---

## CHECKLIST RÁPIDO PARA RETOMAR

Cuando retomes el proyecto, verificar:

**Infraestructura:**
1. [ ] ¿Existen las tablas en `verumax_academi`? (evaluationes, quaestiones, sessiones_evaluatio, responsa)
2. [ ] ¿Existe la carpeta `probatio/` con los archivos base?
3. [ ] ¿Está configurada la conexión en `probatio/config.php`?

**Datos:**
4. [ ] ¿Tengo el `id_curso` de la Diplomatura JR Corrientes?
5. [ ] ¿Tengo las 10 preguntas con sus respuestas y feedback?
6. [ ] ¿Está creada la evaluación en BD? (EVAL-SAJUR-CORR-2025)
7. [ ] ¿Están cargadas las preguntas en `quaestiones`?

**Funcionalidad:**
8. [ ] ¿Funciona el acceso con DNI?
9. [ ] ¿Funciona la persistencia de progreso?
10. [ ] ¿Funciona el cierre cualitativo?
11. [ ] ¿Pasó el testing?

**Panel Admin (si aplica):**
12. [ ] ¿Existe pestaña "Evaluaciones" en administrare.php?
13. [ ] ¿Funciona CRUD de evaluaciones?
14. [ ] ¿Funciona CRUD de preguntas?

---

## NOTAS Y DECISIONES

- **Orden de opciones:** Fijo (no aleatorio) para consistencia pedagógica
- **Tiempo límite:** Sin límite, permitir pausas
- **Intentos:** Ilimitados, pero auditados
- **Feedback:** Obligatorio explicar el error, no solo decir "incorrecto"

---

**Próxima acción:** Obtener las 10 preguntas de SAJuR

---

## REFERENCIAS

- **Documentación completa de Probatio:** `academicus.md` (sección "MÓDULO PROBATIO")
- **Estructura de tablas SQL:** `academicus.md` (sección "Arquitectura de Datos")
- **Servicios existentes a reutilizar:** `src/VERUMax/Services/` (StudentService, InscripcionService)
- **Panel de administración:** `certificatum/administrare.php` (futuro: agregar tab de Evaluaciones)

**Última actualización:** 20 de Diciembre de 2025
