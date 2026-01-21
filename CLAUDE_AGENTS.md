# CLAUDE_AGENTS.md

Configuración de agentes especializados para Claude Code en VERUMax.

---

## help-manual-auditor

**Propósito:** Auditar la cobertura de ayuda en el panel administrativo y mantener actualizado el manual de usuario para clientes (administradores de instituciones).

### Contexto del Sistema de Ayuda

El panel admin de VERUMax tiene un sistema de ayuda contextual implementado en cada módulo:

```
admin/modulos/
├── general.php      ← Configuración institucional
├── certificatum.php ← Gestión de certificados
├── actividad.php    ← Monitoreo de actividad
├── email_stats.php  ← Estadísticas de emails
└── identitas.php    ← Presencia digital (EXCLUIDO por ahora)
```

### Implementación Técnica de la Ayuda

Cada módulo implementa:

1. **Panel lateral deslizante** - Se abre con F1 o botón flotante
2. **Objeto `contenidoAyuda`** - Diccionario JS con secciones de ayuda
3. **Función `mostrarSeccionAyuda(seccion)`** - Muestra contenido específico
4. **Función `togglePanelAyuda()`** - Abre/cierra el panel
5. **Footer con recursos globales** - FAQ, Glosario, Errores (siempre visible)

Estructura del objeto `contenidoAyuda`:
```javascript
const contenidoAyuda = {
    'seccion-id': {
        titulo: 'Título de la sección',
        contenido: `<div class="...">HTML del contenido</div>`
    },
    'guias': {
        titulo: 'Guías de Inicio',
        contenido: `...`,
        tutoriales: [
            {
                id: 'tutorial-id',
                titulo: 'Nombre del tutorial',
                pasos: [
                    { titulo: 'Paso 1', descripcion: '...' },
                    // ...más pasos
                ]
            }
        ]
    }
};
```

### Módulos y Secciones Actuales

#### General (general.php)
- **Secciones:** bienvenida, institucional, apariencia, sistema, guias
- **Recursos:** faq, glosario, errores-comunes
- **Tutoriales:** subir-logo, configurar-colores, activar-modulos, configurar-firmantes, config-inicial

#### Certificatum (certificatum.php)
- **Secciones:** configuracion, estudiantes, docentes, cursos, inscripciones, asignaciones, evaluaciones, logs, bienvenida, dashboard, personas, matriculas
- **Recursos:** faq-certificatum, glosario-certificatum, errores-certificatum
- **Tutoriales:** dashboard, agregar-estudiante, agregar-docente, crear-curso, inscribir-estudiante, asignar-docente, crear-evaluacion, notificar-estudiantes, configurar-certificados, importar-datos, exportar-datos
- **Guías especiales:** primer-certificado, importar-masivo, formas-inscribir, enviar-email, certificados-docentes

#### Actividad (actividad.php)
- **Secciones:** dashboard, comunicaciones, validaciones-qr, notificaciones, bienvenida, interpretar-metricas, solucionar-emails
- **Recursos:** faq-actividad, glosario-actividad, errores-actividad
- **Tutoriales:** ver-comunicaciones, ver-validaciones, configurar-notificaciones, usar-dashboard

#### Email Stats (email_stats.php)
- **Secciones:** estadisticas, webhook, notificaciones (básico)
- **Estado:** Ayuda mínima, considerar expandir

### Tareas del Agente

1. **Auditar cobertura:**
   - Verificar que cada tab/funcionalidad tenga ayuda correspondiente
   - Identificar secciones sin documentar
   - Revisar consistencia de estilo entre módulos

2. **Mantener manual:**
   - Actualizar `docs/manual_usuario.md` basado en contenidoAyuda
   - Agregar capturas de pantalla si es necesario
   - Mantener índice actualizado

3. **Sincronizar:**
   - Si se agrega ayuda nueva → actualizar manual
   - Si se modifica funcionalidad → actualizar ayuda y manual

### Archivo del Manual

**Ubicación:** `docs/manual_usuario.md`
**Vista:** `admin/manual.php`
**PDF:** Generado con mPDF desde el MD

### Checklist de Auditoría

```markdown
## General
- [ ] Información institucional (nombre, descripción, contacto)
- [ ] Logo y favicon
- [ ] Colores y paletas
- [ ] Configuración de firmantes
- [ ] Módulos activos
- [ ] Modo construcción
- [ ] Robots/SEO

## Certificatum
- [ ] Dashboard y métricas
- [ ] CRUD Estudiantes
- [ ] CRUD Docentes
- [ ] CRUD Cursos
- [ ] Inscripciones y estados
- [ ] Asignaciones docentes
- [ ] Sistema de evaluaciones
- [ ] Importación masiva
- [ ] Exportación de datos
- [ ] Envío de emails
- [ ] Logs de actividad

## Actividad
- [ ] Dashboard de actividad
- [ ] Comunicaciones (emails enviados)
- [ ] Validaciones QR
- [ ] Configuración de notificaciones
- [ ] Interpretación de métricas
- [ ] Solución de problemas de emails
```

### Cómo Invocar el Agente

Usar el Task tool con:
```
subagent_type: "help-manual-auditor"
prompt: "Descripción de la tarea específica"
```

**Ejemplos de uso:**
- "Auditar cobertura de ayuda en módulo Certificatum"
- "Actualizar manual con nueva funcionalidad de evaluaciones"
- "Verificar consistencia de estilo en FAQ de todos los módulos"
- "Generar reporte de secciones sin documentar"

---

## Notas para el Desarrollo

- El manual es para **clientes/administradores de instituciones**, no usuarios finales
- Excluir módulo Identitas por ahora
- Mantener lenguaje formal pero accesible
- Usar capturas de pantalla cuando ayuden a la comprensión
- El manual debe poder convertirse a PDF profesional
