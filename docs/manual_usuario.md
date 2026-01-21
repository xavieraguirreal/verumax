# Manual de Usuario - VERUMax

**Versión:** 1.0
**Última actualización:** Enero 2026
**Audiencia:** Administradores de instituciones

---

## Tabla de Contenidos

1. [Introducción](#introducción)
2. [Acceso al Panel](#acceso-al-panel)
3. [Módulo General](#módulo-general)
   - [Información Institucional](#información-institucional)
   - [Apariencia y Branding](#apariencia-y-branding)
   - [Configuración del Sistema](#configuración-del-sistema)
4. [Módulo Certificatum](#módulo-certificatum)
   - [Dashboard](#dashboard-certificatum)
   - [Gestión de Estudiantes](#gestión-de-estudiantes)
   - [Gestión de Docentes](#gestión-de-docentes)
   - [Gestión de Cursos](#gestión-de-cursos)
   - [Inscripciones](#inscripciones)
   - [Asignaciones de Docentes](#asignaciones-de-docentes)
   - [Evaluaciones](#evaluaciones)
   - [Importación y Exportación](#importación-y-exportación)
   - [Envío de Certificados](#envío-de-certificados)
5. [Módulo Actividad](#módulo-actividad)
   - [Dashboard de Actividad](#dashboard-de-actividad)
   - [Comunicaciones](#comunicaciones)
   - [Validaciones QR](#validaciones-qr)
   - [Configuración de Notificaciones](#configuración-de-notificaciones)
6. [Tutoriales Interactivos](#tutoriales-interactivos)
   - [Flujo Completo del Certificado](#flujo-completo-del-certificado)
   - [Agregar un Estudiante](#demo-agregar-estudiante)
   - [Configurar Branding](#demo-configurar-branding)
   - [Configurar Plantilla y Firma](#demo-configurar-plantilla)
7. [Preguntas Frecuentes](#preguntas-frecuentes)
8. [Glosario](#glosario)
9. [Solución de Problemas](#solución-de-problemas)

---

## Introducción

VERUMax es una plataforma de gestión de certificados académicos que permite a instituciones educativas:

- Gestionar estudiantes, docentes y cursos
- Generar certificados digitales con validación QR
- Emitir analíticos académicos y constancias
- Enviar documentos por email de forma automática
- Monitorear la actividad y validaciones

Este manual está dirigido a administradores de instituciones que utilizan el panel de administración de VERUMax.

### Requisitos del Sistema

- Navegador web moderno (Chrome, Firefox, Edge, Safari)
- Conexión a internet estable
- Credenciales de acceso proporcionadas por VERUMax

---

## Acceso al Panel

### Iniciar Sesión

1. Ingrese a la URL de su panel administrativo (ej: `suinstitucion.verumax.com/admin`)
2. Introduzca su email y contraseña
3. Haga clic en "Iniciar Sesión"

### Navegación Principal

El panel cuenta con un menú lateral con los siguientes módulos:

| Módulo | Descripción |
|--------|-------------|
| **General** | Configuración de la institución, branding y sistema |
| **Certificatum** | Gestión de estudiantes, cursos y certificados |
| **Actividad** | Monitoreo de emails y validaciones QR |

### Centro de Ayuda

Cada módulo incluye un Centro de Ayuda integrado:

- **Botón flotante (esquina inferior derecha):** Abre el panel de ayuda
- **Tecla F1:** Atajo de teclado para abrir/cerrar la ayuda
- **Recursos siempre visibles:** FAQ, Glosario y Errores Comunes en el footer del panel

---

## Módulo General

El módulo General permite configurar los aspectos fundamentales de su institución.

### Información Institucional

Configure los datos básicos de su institución:

| Campo | Descripción |
|-------|-------------|
| **Nombre** | Nombre corto de la institución (ej: "SAJuR") |
| **Nombre completo** | Denominación oficial completa |
| **Descripción** | Breve descripción de la institución |
| **Email de contacto** | Email para comunicaciones |
| **Teléfono** | Número de contacto |
| **Dirección** | Ubicación física |
| **Sitio web** | URL del sitio institucional |

**Importante:** Estos datos aparecerán en los certificados y comunicaciones oficiales.

### Apariencia y Branding

#### Logo Institucional

1. Haga clic en "Cambiar logo"
2. Seleccione una imagen (PNG o JPG, máximo 2MB)
3. Elija el estilo de visualización:
   - **Cuadrado:** Para logos cuadrados o circulares
   - **Rectangular:** Para logos horizontales
   - **Rectangular redondeado:** Rectangular con bordes suaves
4. Guarde los cambios

**Recomendaciones:**
- Resolución mínima: 200x200 píxeles
- Fondo transparente (PNG) preferido
- Formato horizontal funciona mejor en certificados

#### Favicon

El favicon es el ícono que aparece en la pestaña del navegador:

1. Suba una imagen cuadrada (idealmente 32x32 o 64x64 px)
2. Use formato PNG o ICO

#### Colores Institucionales

Configure los colores que se aplicarán a certificados y documentos:

| Color | Uso |
|-------|-----|
| **Primario** | Color principal de la marca |
| **Secundario** | Color complementario |
| **Acento** | Detalles y resaltados |

**Paletas predefinidas:** Seleccione una paleta preconfigurada o personalice los colores individualmente.

### Configuración del Sistema

#### Módulos Activos

Active o desactive módulos según sus necesidades:

- **Certificatum:** Gestión de certificados (recomendado siempre activo)
- **Identitas:** Presencia digital y sitio web
- **Actividad:** Monitoreo de comunicaciones

#### Firmantes

Configure quién firma los certificados:

1. **Nombre del firmante:** Nombre completo con título
2. **Cargo:** Puesto o función
3. **Firma digital:** Imagen de la firma (PNG con fondo transparente)

**Múltiples firmantes:** Puede configurar hasta 2 firmantes por certificado.

#### Modo Construcción

Cuando está activo:
- Los visitantes ven una página de "Sitio en construcción"
- Solo administradores pueden acceder normalmente
- Útil durante configuración inicial

#### Indexación (Robots)

Controle si los buscadores pueden indexar su sitio:
- **Permitir indexación:** El sitio aparecerá en Google
- **No indexar:** Oculto de buscadores (recomendado durante desarrollo)

---

## Módulo Certificatum

Certificatum es el corazón de VERUMax, donde gestiona personas, cursos y emite certificados.

### Dashboard Certificatum

El dashboard muestra un resumen de su institución:

| Métrica | Descripción |
|---------|-------------|
| **Total Estudiantes** | Cantidad de estudiantes registrados |
| **Total Docentes** | Cantidad de docentes registrados |
| **Cursos Activos** | Cursos en estado activo |
| **Inscripciones** | Total de inscripciones |
| **Certificados Emitidos** | Documentos generados |

**Cards interactivas:** Haga clic en cualquier card para ir directamente a esa sección.

### Gestión de Estudiantes

#### Agregar Estudiante

1. Vaya a **Personas > Estudiantes**
2. Clic en **"Agregar"**
3. Complete los campos:
   - **DNI/Documento:** Número de identificación (único)
   - **Nombre completo:** Nombre y apellido
   - **Email:** Correo electrónico
   - **Género:** Masculino/Femenino/Otro (para textos con género)
   - **Teléfono:** (opcional)
4. Clic en **"Guardar"**

#### Editar Estudiante

1. Ubique al estudiante en la lista
2. Clic en el ícono de edición (lápiz)
3. Modifique los datos necesarios
4. Guarde los cambios

#### Eliminar Estudiante

**Advertencia:** Al eliminar un estudiante se eliminan también sus inscripciones.

1. Clic en el ícono de eliminar (papelera)
2. Confirme la acción

### Gestión de Docentes

Similar a estudiantes, con campos adicionales:

| Campo | Descripción |
|-------|-------------|
| **Rol por defecto** | Docente, Instructor, Facilitador, etc. |
| **Especialidad** | Área de expertise |
| **Biografía** | Descripción breve |

### Gestión de Cursos

#### Crear Curso

1. Vaya a **Cursos**
2. Clic en **"Agregar"**
3. Complete:

| Campo | Descripción | Ejemplo |
|-------|-------------|---------|
| **Código** | Identificador único | SA-CUR-2025-001 |
| **Nombre** | Título del curso | Introducción a la Mediación |
| **Descripción** | Detalle del contenido | (opcional) |
| **Tipo** | Curso, Taller, Seminario, etc. | Curso |
| **Modalidad** | Virtual, Presencial, Híbrido | Virtual |
| **Carga horaria** | Duración en horas | 40 |
| **Fecha inicio** | Cuándo comienza | 01/03/2025 |
| **Fecha fin** | Cuándo termina | 30/04/2025 |
| **Estado** | Activo, Finalizado, Cancelado | Activo |

4. Clic en **"Guardar"**

#### Estados de Curso

| Estado | Significado |
|--------|-------------|
| **Borrador** | En preparación, no visible |
| **Activo** | En curso, permite inscripciones |
| **Finalizado** | Completado, permite certificados |
| **Cancelado** | No se dictó |

### Inscripciones

#### Inscribir Estudiante

1. Vaya a **Matrículas > Inscripciones**
2. Clic en **"Agregar"**
3. Seleccione el estudiante
4. Seleccione el curso
5. Configure:
   - **Estado:** Inscripto, Cursando, Aprobado, etc.
   - **Nota final:** (si aplica)
   - **Asistencia:** Porcentaje
6. Guarde

#### Estados de Inscripción

| Estado | Documentos Disponibles |
|--------|----------------------|
| **Inscripto** | Constancia de Inscripción |
| **Cursando** | Constancia de Alumno Regular |
| **Aprobado** | Certificado de Aprobación, Analítico |
| **Desaprobado** | Constancia de Participación |
| **Baja** | Ninguno |

### Asignaciones de Docentes

Vincule docentes a cursos con roles específicos:

1. Vaya a **Matrículas > Asignaciones**
2. Clic en **"Agregar"**
3. Seleccione docente y curso
4. Elija el rol:
   - Docente
   - Instructor
   - Facilitador
   - Tutor
   - Coordinador
   - Orador
   - Conferencista
5. Configure el estado:
   - **Asignado:** Curso no iniciado
   - **En curso:** Dictando actualmente
   - **Completado:** Finalizó su participación

**Importante:** Solo con estado "Completado" el docente puede descargar su certificado.

### Evaluaciones

El sistema de evaluaciones permite crear exámenes en línea.

#### Crear Evaluación

1. Vaya a **Evaluaciones**
2. Clic en **"Nueva Evaluación"**
3. Configure:
   - **Nombre:** Título de la evaluación
   - **Curso:** A qué curso pertenece
   - **Tipo:** Parcial, Final, Recuperatorio
   - **Duración:** Tiempo límite en minutos
   - **Intentos:** Cantidad de intentos permitidos
   - **Nota mínima:** Para aprobar
   - **Aleatorizar:** Mezclar preguntas
4. Agregue preguntas:
   - Opción múltiple
   - Verdadero/Falso
   - Respuesta corta
5. Active la evaluación
6. Comparta el enlace con estudiantes

### Importación y Exportación

#### Importar Datos Masivamente

Para cargar muchos registros a la vez:

1. Prepare un archivo CSV o Excel con los datos
2. Vaya a la sección correspondiente (Estudiantes, Docentes, etc.)
3. Clic en **"Importar"**
4. Seleccione el archivo o pegue los datos
5. Mapee las columnas del archivo con los campos del sistema
6. Valide los datos (el sistema muestra errores)
7. Ejecute la importación

**Formato recomendado para estudiantes:**
```
DNI,Nombre,Apellido,Email,Genero
12345678,Juan,Pérez,juan@email.com,Masculino
23456789,María,García,maria@email.com,Femenino
```

#### Exportar Datos

1. Aplique filtros si desea exportar solo algunos registros
2. Clic en **"Exportar"**
3. Elija formato (CSV o Excel)
4. Descargue el archivo

### Envío de Certificados

#### Enviar a un Estudiante

1. En la lista de inscripciones, ubique al estudiante
2. Clic en el ícono de email
3. Confirme el envío

#### Envío Masivo

1. Seleccione múltiples estudiantes con los checkboxes
2. Clic en **"Acciones Masivas"**
3. Elija **"Enviar certificados por email"**
4. Confirme

**El email incluye:**
- Saludo personalizado
- Enlace para descargar el certificado
- Código QR de validación

---

## Módulo Actividad

Monitoree las comunicaciones y validaciones de su institución.

### Dashboard de Actividad

Muestra métricas en tiempo real:

| Métrica | Descripción |
|---------|-------------|
| **Emails enviados** | Total de correos enviados |
| **Tasa de apertura** | % de emails abiertos |
| **Tasa de rebote** | % de emails no entregados |
| **Validaciones QR** | Cantidad de verificaciones |

### Comunicaciones

Lista de todos los emails enviados:

| Columna | Descripción |
|---------|-------------|
| **Destinatario** | A quién se envió |
| **Asunto** | Título del email |
| **Estado** | Enviado, Entregado, Abierto, Rebotado |
| **Fecha** | Cuándo se envió |

#### Estados de Email

| Estado | Significado | Acción |
|--------|-------------|--------|
| **Enviado** | Salió del sistema | Esperar |
| **Entregado** | Llegó al servidor destino | OK |
| **Abierto** | El destinatario lo abrió | OK |
| **Click** | Hizo clic en un enlace | OK |
| **Rebotado** | No se pudo entregar | Verificar email |
| **Spam** | Marcado como spam | Revisar contenido |

### Validaciones QR

Registro de cada vez que alguien escanea un código QR:

| Dato | Descripción |
|------|-------------|
| **Código** | Identificador del certificado |
| **Fecha/Hora** | Cuándo se validó |
| **IP** | Desde dónde se validó |
| **Ubicación** | País/ciudad aproximada |
| **Resultado** | Válido o Inválido |

**Uso:** Permite detectar intentos de validación de certificados falsos.

### Configuración de Notificaciones

Configure alertas automáticas:

- **Resumen diario:** Email con actividad del día
- **Resumen semanal:** Reporte semanal
- **Alertas de rebote:** Aviso inmediato de emails fallidos
- **Alertas de seguridad:** Notificación de validaciones sospechosas

---

## Tutoriales Interactivos

VERUMax incluye tutoriales interactivos que simulan el panel real para que pueda aprender cada proceso paso a paso. Estos demos funcionan en cualquier dispositivo (computadora, tablet o celular).

### Cómo funcionan los demos

- **Modo automático:** El demo avanza solo, mostrando cada paso con animaciones
- **Navegación manual:** Use las flechas del teclado o botones para avanzar a su ritmo
- **Globos explicativos:** Aparecen mensajes contextuales con tips y aclaraciones
- **Responsive:** Funcionan perfectamente en dispositivos móviles (deslice para navegar)

### Flujo Completo del Certificado

Este tutorial completo muestra todo el proceso desde registrar un estudiante hasta que recibe su certificado por email:

1. Agregar un nuevo estudiante
2. Crear o seleccionar un curso
3. Inscribir al estudiante en el curso
4. Registrar la aprobación con calificación
5. Enviar el certificado por email
6. Ver cómo el estudiante recibe y accede a su certificado

**[Ver Demo: Flujo Completo del Certificado](../admin/demos/demo_flujo_completo.html)**

### Demo: Agregar Estudiante

Aprenda a registrar un nuevo estudiante en el sistema paso a paso:

- Navegar a la sección de Personas
- Abrir el formulario de nuevo estudiante
- Completar los datos requeridos (DNI, nombre, apellido)
- Guardar y verificar el registro

**[Ver Demo: Agregar Estudiante](../admin/demos/demo_agregar_estudiante.html)**

### Demo: Configurar Branding

Personalice la apariencia de su institución:

- Subir el logo institucional
- Configurar los colores primarios y secundarios
- Previsualizar los cambios en tiempo real

**[Ver Demo: Configurar Branding](demos/demo_configurar_branding.html)** *(Próximamente)*

### Demo: Configurar Plantilla y Firma

Configure las plantillas de certificados:

- Seleccionar el diseño de plantilla
- Subir la firma digital del responsable
- Configurar el cargo y nombre del firmante
- Previsualizar el certificado final

**[Ver Demo: Configurar Plantilla](demos/demo_configurar_plantilla.html)** *(Próximamente)*

---

## Preguntas Frecuentes

### Configuración General

**¿Cómo cambio el logo de mi institución?**
Vaya a General > Apariencia > Logo y suba una nueva imagen.

**¿Puedo tener múltiples firmantes en los certificados?**
Sí, puede configurar hasta 2 firmantes en General > Sistema > Firmantes.

**¿Qué pasa si activo el modo construcción?**
Los visitantes verán una página de "Sitio en construcción", pero usted podrá acceder normalmente.

### Certificados

**¿Por qué un estudiante no puede descargar su certificado?**
Verifique que la inscripción esté en estado "Aprobado" y que el curso esté "Finalizado".

**¿Puedo generar certificados para docentes?**
Sí, los docentes pueden recibir certificados de participación cuando su asignación está en estado "Completado".

**¿Cómo se valida un certificado?**
Cada certificado tiene un código QR único. Al escanearlo, lleva a una página de verificación que confirma la autenticidad.

**¿Qué tipos de documentos puedo generar?**

| Documento | Para quién | Requisito |
|-----------|------------|-----------|
| Certificado de Aprobación | Estudiantes | Estado: Aprobado |
| Analítico Académico | Estudiantes | Estado: Aprobado |
| Constancia de Inscripción | Estudiantes | Cualquier estado |
| Constancia de Alumno Regular | Estudiantes | Estado: Cursando |
| Certificado de Participación | Docentes | Estado: Completado |
| Constancia de Asignación | Docentes | Estado: Asignado/En curso |

### Emails

**¿Por qué algunos emails rebotan?**
Posibles causas: email incorrecto, buzón lleno, servidor destino rechaza. Verifique la dirección y reintente.

**¿Puedo reenviar un email?**
Sí, desde la lista de comunicaciones puede reenviar cualquier email.

**¿Cómo evito que mis emails vayan a spam?**
VERUMax usa SendGrid con configuración SPF/DKIM. Si persiste el problema, contacte soporte.

### Importación

**¿Qué formato debe tener el archivo de importación?**
CSV o Excel con encabezados. La primera fila debe contener los nombres de las columnas.

**¿Qué pasa si hay errores en la importación?**
El sistema valida los datos antes de importar y muestra los errores encontrados. Puede corregir y reintentar.

**¿Se pueden importar inscripciones?**
Sí, puede importar inscripciones indicando DNI del estudiante, código del curso y estado.

---

## Glosario

| Término | Definición |
|---------|------------|
| **Analítico** | Documento detallado con trayectoria académica completa |
| **Asignación** | Vínculo entre un docente y un curso |
| **Certificado** | Documento oficial que acredita aprobación |
| **Código QR** | Código de barras bidimensional para validación |
| **Constancia** | Documento que certifica un estado o situación |
| **Carga horaria** | Duración del curso en horas |
| **DNI** | Documento Nacional de Identidad (identificador único) |
| **Firmante** | Persona autorizada para firmar certificados |
| **Hard bounce** | Email rechazado permanentemente (dirección no existe) |
| **Inscripción** | Registro de un estudiante en un curso |
| **Instancia** | Institución dentro del sistema multi-tenant |
| **Matrícula** | Conjunto de inscripciones y asignaciones |
| **Modalidad** | Formato del curso (virtual, presencial, híbrido) |
| **Módulo** | Sección funcional del sistema |
| **Multi-tenant** | Sistema que aloja múltiples instituciones |
| **Rol** | Función del docente (instructor, facilitador, etc.) |
| **Soft bounce** | Email rechazado temporalmente (buzón lleno) |
| **Tasa de apertura** | Porcentaje de emails abiertos |
| **Tasa de rebote** | Porcentaje de emails no entregados |
| **Validación** | Verificación de autenticidad de un certificado |
| **Webhook** | Notificación automática de eventos de email |

---

## Solución de Problemas

### El logo no se ve correctamente

**Síntoma:** El logo aparece cortado o distorsionado.

**Solución:**
1. Verifique que el estilo seleccionado coincida con la forma del logo
2. Use una imagen de al menos 200x200 píxeles
3. Pruebe con formato PNG con fondo transparente

### Los emails no llegan

**Síntoma:** Los destinatarios no reciben los emails.

**Soluciones:**
1. Verifique que el email del destinatario sea correcto
2. Pida al destinatario que revise la carpeta de spam
3. Revise el estado del email en Actividad > Comunicaciones
4. Si está rebotado, corrija el email y reenvíe

### No puedo generar un certificado

**Síntoma:** El botón de descargar certificado no aparece o da error.

**Verificar:**
1. El estudiante tiene inscripción en estado "Aprobado"
2. El curso está en estado "Finalizado"
3. Los datos del estudiante están completos (nombre, DNI)

### La importación falla

**Síntoma:** Error al importar archivo CSV.

**Soluciones:**
1. Verifique que el archivo tenga encabezados en la primera fila
2. Use codificación UTF-8 para caracteres especiales
3. No deje celdas vacías en campos requeridos
4. Verifique que los DNI no estén duplicados

### El certificado muestra datos incorrectos

**Síntoma:** Nombre, fecha u otros datos aparecen mal.

**Solución:**
1. Edite los datos del estudiante o curso según corresponda
2. Regenere el certificado (los cambios se reflejan automáticamente)

### No puedo acceder al panel

**Síntoma:** Error al iniciar sesión.

**Soluciones:**
1. Verifique que el email y contraseña sean correctos
2. Use "Olvidé mi contraseña" si es necesario
3. Limpie caché y cookies del navegador
4. Contacte soporte si el problema persiste

---

## Contacto y Soporte

Para asistencia técnica:

- **Email:** soporte@verumax.com
- **Documentación:** Este manual y el Centro de Ayuda integrado
- **Horario:** Lunes a Viernes, 9:00 a 18:00 (Argentina)

---

*Manual generado automáticamente desde el sistema de ayuda de VERUMax.*
*Versión 1.0 - Enero 2026*
