### **Plan de Desarrollo: La Solución "Contactus"**

#### **1. Nombre y Concepto**

*   **Nombre Propuesto:** **Contactus**
*   **Concepto:** "Tu Puente de Comunicación Digital"
*   **Misión Inicial:** Proveer un sistema de formulario de contacto robusto y personalizable como solución base (Plan Essentia), sentando las bases para futuras expansiones.

#### **2. Propuesta de Valor del Plan "Essentia"**

Contactus, en su versión Essentia, ofrece un sistema de gestión de formularios de contacto que combina simplicidad con un control administrativo esencial. El objetivo es permitir a cada cliente adaptar el formulario a sus necesidades sin complejidad técnica.

*   **Funcionalidad:** Un constructor de formularios y una bandeja de entrada para gestionar las consultas.
*   **Panel de Administración:** El cliente obtiene acceso a un panel de control para personalizar su formulario.
*   **Gestión de Campos Clásicos:**
    *   **Campos Incluidos:** Se proveerá una lista de campos clásicos predefinidos (Ej: Nombre, Apellido, Email, Teléfono, Empresa, Asunto, Mensaje).
    *   **Activar/Desactivar:** El administrador podrá elegir qué campos de la lista desea mostrar en su formulario.
    *   **Personalizar Etiquetas:** Podrá cambiar el nombre visible de cada campo (Ej: cambiar "Empresa" por "Institución").
    *   **Marcar como Requerido:** Podrá definir qué campos son obligatorios para el envío.
*   **Gestión de Respuestas:**
    *   **Auto-Respuesta Configurable:** El administrador podrá escribir el texto del email de respuesta automática que se envía al usuario tras el contacto.
    *   **Notificaciones:** Se podrá definir una dirección de email principal para recibir las notificaciones de nuevas consultas.
*   **Bandeja de Entrada:** Todas las consultas se guardan en una bandeja de entrada dentro del panel, permitiendo:
    *   Ver y leer todos los mensajes recibidos de forma centralizada.
    *   Tener un registro histórico de todas las interacciones.

#### **3. Arquitectura Técnica Simplificada**

La solución seguirá siendo multi-tenant e integrada en `Identitas`.

*   **Tabla `contactus_config_instancia`:**
    *   `id` (PK)
    *   `id_instancia` (FK a `identitas_instancias`, única)
    *   `email_notificacion`: (Texto) El email que recibe los avisos.
    *   `autorespuesta_asunto`: (Texto) Asunto del email de respuesta.
    *   `autorespuesta_cuerpo`: (Texto) Cuerpo del email de respuesta.

*   **Tabla `contactus_campos_instancia`:**
    *   `id` (PK)
    *   `id_instancia` (FK)
    *   `nombre_sistema`: (Texto, Ej: `nombre`, `telefono`) El nombre fijo del campo.
    *   `etiqueta_personalizada`: (Texto, Ej: "Nombre y Apellido") El nombre que define el admin.
    *   `esta_activo`: (Booleano, default: `true`)
    *   `es_requerido`: (Booleano, default: `false`)
    *   `orden`: (Entero) Para definir la posición en el formulario.

*   **Tabla `contactus_entradas` (Bandeja de Entrada):**
    *   `id` (PK)
    *   `id_instancia` (FK)
    *   `fecha_creacion`: (Timestamp)
    *   `datos_formulario_json`: (JSON) Almacena todos los campos y valores enviados en la consulta. Ej: `{"Nombre": "Juan Pérez", "Email": "juan@test.com", ...}`.
    *   `leido`: (Booleano, default: `false`)

#### **4. El Panel de Administración "Essentia"**

El cliente tendrá una nueva sección en su dashboard llamada "Contactus" con dos apartados.

1.  **Bandeja de Entrada:** La vista principal. Una tabla simple con las consultas recibidas, mostrando remitente, asunto (si existe) y fecha. Se podrá hacer clic para ver el mensaje completo.

2.  **Configuración del Formulario:** Una única página de ajustes donde el administrador podrá:
    *   **Gestionar Campos:** Verá una lista de todos los campos clásicos. Para cada uno, podrá:
        *   Activar o desactivar su visibilidad con un *checkbox*.
        *   Editar su etiqueta en una caja de texto.
        *   Marcar si es requerido con otro *checkbox*.
    *   **Configurar Respuesta:** En la misma página, habrá campos de texto para definir el asunto y el cuerpo del email de auto-respuesta.

#### **5. Integración con el Ecosistema Verumax**

*   El motor `IdentitasEngine` renderizará el formulario público, generando el HTML dinámicamente según la configuración de `Contactus` para esa instancia.
*   El archivo `contactus.php` se convertirá en el punto de entrada que utiliza el motor para renderizar el formulario y procesar los envíos.
