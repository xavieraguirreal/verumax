# Plan de Mejoras - Panel de Administración VERUMax

> **Fecha de creación:** 2026-01-10
> **Estado:** En progreso
> **Ambiente:** Producción - Implementar con precaución

---

## Estructura Propuesta del Admin

```
VERUMAX ADMIN
│
├── GENERAL
│   ├── Institución (nombre, logo, redes)
│   ├── Apariencia (colores, favicon)
│   └── Configuración (módulos, firmantes, SEO)
│
├── IDENTITAS
│   ├── Configuración (paleta propia)
│   ├── Páginas (editor WYSIWYG)
│   ├── Templates
│   └── Contactos
│
├── CERTIFICATUM
│   ├── Configuración (branding, textos)
│   ├── Personas (estudiantes + docentes)
│   ├── Cursos
│   ├── Matrículas (inscripciones + asignaciones)
│   └── Evaluaciones
│
└── ACTIVIDAD (ex Emails)
    ├── Dashboard (resumen general)
    ├── Comunicaciones (emails SendGrid)
    ├── Validaciones (logs QR)
    └── Configuración (notificaciones)
```

---

## Fases de Implementación

### MÓDULO: GENERAL

| ID | Fase | Descripción | Complejidad | Riesgo | Estado |
|----|------|-------------|-------------|--------|--------|
| G1 | 1 | Toast notifications | Baja | Bajo | **COMPLETADO** (ya existía) |
| G2 | 2 | Dashboard de estado inicial | Media | Bajo | **COMPLETADO** |
| G3 | 3 | Secciones colapsables | Baja | Bajo | **COMPLETADO** |
| G4 | 4 | Simplificar upload logo | Baja | Bajo | **COMPLETADO** |
| G5 | 5 | Selector estilo logo (dropdown) | Baja | Bajo | **COMPLETADO** |
| G6 | 6 | Presets de colores | Media | Bajo | **COMPLETADO** |
| G7 | 7 | Cards visuales para módulos | Media | Bajo | **COMPLETADO** |
| G8 | 8 | Reorganizar tabs (4→3) + Fix post-save | Alta | Medio | **COMPLETADO** |
| G9 | 9 | Mejoras mobile | Media | Bajo | **COMPLETADO** |

#### G1: Toast Notifications
- Crear función `showToast()` similar a la de Identitas
- Reemplazar mensajes estáticos por toasts flotantes
- Posición: esquina superior derecha
- Auto-ocultar después de 4 segundos

#### G2: Dashboard de Estado Inicial
- Mostrar cards con estado de configuración
- Logo: Configurado / Pendiente
- Favicon: Generado / No generado
- Colores: Personalizados / Defaults
- Módulos activos: X de Y
- Firmantes: Configurados / Pendiente

#### G3: Secciones Colapsables
- "Información Adicional" colapsable (misión, sitio web, email)
- "Redes Sociales" colapsable
- Guardar estado en localStorage

#### G4: Simplificar Upload Logo
- Mostrar solo opción de subir archivo
- Link "¿Tienes URL externa?" para expandir campo URL
- Preview en tiempo real

#### G5: Selector Estilo Logo
- Reemplazar 5 cards por select dropdown
- Preview dinámico al lado

#### G6: Presets de Colores
- Botones de paletas predefinidas
- Preview en tiempo real del header

#### G7: Cards para Módulos
- Reemplazar checkboxes por cards visuales
- Icono + nombre + descripción + toggle

#### G8: Reorganizar Tabs
- Institución: nombre, logo, misión, redes
- Apariencia: colores, favicon, tema
- Configuración: módulos, firmantes, construcción, SEO

#### G9: Mejoras Mobile
- Tabs responsivos (dropdown en móvil)
- Grids adaptativos

---

### MÓDULO: IDENTITAS

| ID | Fase | Descripción | Complejidad | Riesgo | Estado |
|----|------|-------------|-------------|--------|--------|
| I1 | 1 | Restaurar tab Páginas | Baja | Bajo | **COMPLETADO** |
| I2 | 2 | Editor WYSIWYG (TinyMCE/CKEditor) | Media | Medio | **COMPLETADO** (ya existía CKEditor5) |
| I3 | 3 | Enriquecer tab Configuración | Media | Bajo | **COMPLETADO** |
| I4 | 4 | Mejorar gestión de Contactos | Media | Bajo | **COMPLETADO** |
| I5 | 5 | Integrar Templates en módulo | Alta | Medio | **COMPLETADO** (navegación sincronizada) |

#### I1: Restaurar Tab Páginas
- Agregar botón de tab "Páginas" en navegación
- Verificar funcionalidad de edición

#### I2: Editor WYSIWYG
- Integrar TinyMCE o CKEditor via CDN
- Toolbar básica: negrita, cursiva, listas, enlaces

#### I3: Enriquecer Configuración
- Agregar opciones específicas de Identitas
- ~~Idioma del sitio~~ → **NOTA:** Configuración de idiomas movida a módulo GENERAL (afecta toda la plataforma)
- Zona horaria, formato de fechas (pendiente)

#### I4: Mejorar Contactos
- Botón "Responder" (mailto:)
- Botón "Archivar"
- Botón "Eliminar" con confirmación
- Filtros: Todos | No leídos | Archivados
- Búsqueda

#### I5: Integrar Templates
- Evaluar carga como tab interno vs módulo separado

---

### MÓDULO: CERTIFICATUM

| ID | Fase | Descripción | Complejidad | Riesgo | Estado |
|----|------|-------------|-------------|--------|--------|
| C1 | 1 | Reorganizar tabs (9→5) | Alta | Medio | **COMPLETADO** |
| C2 | 2 | Paginación en tablas | Media | Bajo | **COMPLETADO** |
| C3 | 3 | Filtros avanzados | Media | Bajo | **COMPLETADO** |
| C4 | 4 | Acciones masivas | Alta | Medio | **COMPLETADO** |
| C5 | 5 | Ayuda como panel lateral | Media | Bajo | **COMPLETADO** |
| C6 | 6 | Dashboard con métricas | Media | Bajo | **COMPLETADO** |
| C7 | 7 | Exportar Excel/CSV | Media | Bajo | **COMPLETADO** |
| C8 | 8 | Wizard de importación | Alta | Medio | **COMPLETADO** |
| C9 | 9 | Toast notifications | Baja | Bajo | **COMPLETADO** (ya existía) |

#### C1: Reorganizar Tabs
Nueva estructura:
- Configuración (ex Diseño)
- Personas (Estudiantes + Docentes con filtro)
- Cursos
- Matrículas (Inscripciones + Asignaciones con sub-tabs)
- Evaluaciones
- Actividad → Mover a módulo separado

#### C2: Paginación
- 25 registros por página
- Navegación: Primera | Anterior | 1 2 3 | Siguiente | Última
- "Mostrando X-Y de Z registros"

#### C3: Filtros Avanzados
- Panel colapsable arriba de tablas
- Filtros por estado, fecha, curso

#### C4: Acciones Masivas
- Checkbox en primera columna
- Seleccionar todos
- Barra de acciones: Cambiar estado, Enviar email, Exportar, Eliminar

#### C5: Ayuda Panel Lateral
- Botón flotante "?"
- Panel lateral con contenido contextual
- Búsqueda interna

#### C6: Dashboard Métricas
- Cards clickeables con totales
- Total estudiantes, cursos, certificados emitidos

#### C7: Exportar
- Botón junto a Importar
- CSV y Excel
- Respeta filtros aplicados

#### C8: Wizard Importación
- Paso 1: Seleccionar archivo
- Paso 2: Mapear columnas
- Paso 3: Validar
- Paso 4: Confirmar

#### C9: Toast Notifications
- Mismo sistema que General e Identitas

---

### MÓDULO: ACTIVIDAD (ex Emails)

| ID | Fase | Descripción | Complejidad | Riesgo | Estado |
|----|------|-------------|-------------|--------|--------|
| E1 | 1 | Renombrar módulo | Baja | Bajo | **COMPLETADO** |
| E2 | 2 | Agregar tab Validaciones QR | Media | Bajo | **COMPLETADO** |
| E3 | 3 | Dashboard unificado | Media | Bajo | **COMPLETADO** |
| E4 | 4 | Paginación y búsqueda | Media | Bajo | **COMPLETADO** |
| E5 | 5 | Exportar reportes | Media | Bajo | **COMPLETADO** |

#### E1: Renombrar Módulo
- email_stats.php → actividad.php
- Actualizar navegación
- Cambiar icono a "activity"

#### E2: Tab Validaciones QR
- Migrar desde Certificatum
- Sub-tabs: Comunicaciones | Validaciones | Configuración

#### E3: Dashboard Unificado
- Métricas combinadas
- Mini-gráfico de actividad

#### E4: Paginación y Búsqueda
- 25 por página
- Búsqueda por email/código
- Filtros por tipo y estado

#### E5: Exportar Reportes
- CSV con filtros
- Reporte PDF resumido

---

## Orden de Implementación Sugerido

### Sprint 1: Feedback Visual (Bajo riesgo)
1. G1 - Toast notifications General
2. C9 - Toast notifications Certificatum
3. I1 - Restaurar tab Páginas

### Sprint 2: Mejoras de Usabilidad
4. G3 - Secciones colapsables
5. G4 - Simplificar upload logo
6. G5 - Selector estilo logo
7. G2 - Dashboard de estado

### Sprint 3: Datos y Navegación
8. C2 - Paginación Certificatum
9. E4 - Paginación Actividad
10. C3 - Filtros avanzados

### Sprint 4: Reorganización Mayor
11. E1 - Renombrar a Actividad
12. E2 - Mover Validaciones a Actividad
13. C1 - Reorganizar tabs Certificatum

### Sprint 5: Features Avanzados
14. C4 - Acciones masivas
15. C7 - Exportar
16. I2 - Editor WYSIWYG

---

## Notas de Implementación

### Antes de cada cambio:
1. Crear backup del archivo en `backup/YYYY-MM-DD/HHMM-archivo.php`
2. Probar en entorno local si es posible
3. Implementar cambio mínimo
4. Verificar en producción
5. Documentar qué probar

### Rollback:
Si algo falla, restaurar desde backup inmediatamente.

### Testing:
- Probar en Chrome, Firefox, Safari
- Probar en móvil
- Verificar que formularios guardan correctamente
- Verificar mensajes de éxito/error

---

## Historial de Cambios

| Fecha | Fase | Descripción | Resultado |
|-------|------|-------------|-----------|
| 2026-01-10 | - | Plan creado | OK |
| 2026-01-10 | G1 | Toast notifications General | Ya existía - OK |
| 2026-01-10 | I1 | Restaurar tab Páginas Identitas | Implementado - OK |
| 2026-01-10 | - | Fix CKEditor sync con detección cambios | Implementado - OK |
| 2026-01-10 | - | Fix redeclaración funciones identitas/config.php | Implementado - OK |
| 2026-01-10 | G3 | Secciones colapsables en General (Info Adicional + Redes Sociales) | Implementado - OK |
| 2026-01-10 | G4 | Simplificar upload logo (preview integrado, URL expandible) | Implementado - OK |
| 2026-01-11 | G5 | Selector estilo logo como dropdown con preview dinámico | Implementado - OK |
| 2026-01-11 | G6 | Presets de colores (botones visuales + preview header) | Implementado - OK |
| 2026-01-11 | G7 | Cards visuales para módulos (colores dinámicos al toggle) | Implementado - OK |
| 2026-01-11 | G2 | Dashboard de estado inicial (5 indicadores) | Implementado - OK |
| 2026-01-11 | G8 | Reorganizar tabs 4→3 (Institución, Apariencia, Configuración) | Implementado - OK |
| 2026-01-11 | G8 | Fix: Actualizar todas las referencias de tabs antiguos | Implementado - OK |
| 2026-01-11 | G9 | Mejoras mobile (tabs responsive, CSS adaptativo) | Implementado - OK |
| 2026-01-11 | I3 | Configuración de idiomas inicialmente en Identitas | Implementado - OK |
| 2026-01-11 | I4 | Gestión de contactos mejorada (filtros, búsqueda, eliminar) | Implementado - OK |
| 2026-01-11 | - | Refactor: Mover config idiomas de Identitas a General (afecta toda la plataforma) | Implementado - OK |
| 2026-01-11 | I5 | Integrar Templates: navegación sincronizada entre tabs | Implementado - OK |
| 2026-01-11 | C9 | Toast notifications Certificatum (ya existía) | Ya existía - OK |
| 2026-01-11 | C2 | Paginación en tablas Estudiantes y Cursos (25 items/página) | Implementado - OK |
| 2026-01-11 | C6 | Dashboard con métricas clickeables (6 cards resumen) | Implementado - OK |
| 2026-01-11 | C3 | Filtros avanzados Estudiantes y Cursos (estado, tipo, ordenamiento) | Implementado - OK |
| 2026-01-11 | C7 | Exportar CSV estudiantes y cursos (respeta filtros aplicados) | Implementado - OK |
| 2026-01-12 | C7 | Exportar CSV docentes, inscripciones y asignaciones (completo) | Implementado - OK |
| 2026-01-12 | C5 | Panel de ayuda lateral contextual con búsqueda | Implementado - OK |
| 2026-01-12 | C5 | Panel de ayuda extendido a General e Identitas + fix detección tab | Implementado - OK |
| 2026-01-12 | E1-E3,E5 | Módulo ACTIVIDAD unificado: Dashboard, Comunicaciones, Validaciones QR, Config + Exportar CSV | Implementado - OK |
| 2026-01-12 | E4 | Paginación (25/pág) + búsqueda + filtros en Comunicaciones, Validaciones y Accesos | Implementado - OK |
| 2026-01-12 | C1 | Reorganizar tabs Certificatum (9→5): Configuración, Personas (sub-tabs), Cursos, Matrículas (sub-tabs), Evaluaciones | Implementado - OK |
| 2026-01-13 | C4 | Acciones masivas: checkboxes en todas las tablas, barra de selección, cambio de estado, envío email, exportar seleccionados, eliminar | Implementado - OK |
| 2026-01-13 | C8 | Wizard de Importación: 4 pasos (archivo, mapeo columnas, validación, importar), auto-detección columnas, soporte estudiantes/docentes/inscripciones | Implementado - OK |

