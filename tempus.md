# **Plan de Desarrollo: VERUMax Tempus**

Proyecto: VERUMax  
Fecha: 21 de octubre de 2025  
Objetivo: Desarrollar una solución de agendamiento de citas y gestión de calendario, diseñada para ser ultra-sencilla en su uso interno y, a la vez, potente para la reserva de turnos por parte de clientes externos. Debe integrarse nativamente con VERUMax Identitas y calendarios externos (Google, Outlook, etc.).

### **1\. Nombre de la Solución**

* **Nombre Propuesto:** **VERUMax** Tempus  
* **Slogan/Concepto:** "Tu Tiempo, Sincronizado y Verificado."  
* **Justificación:** *Tempus* (del latín "tiempo") es un nombre elegante, corto y universal que describe perfectamente la función principal. El slogan lo conecta con la **sincronización** (Google Calendar) y la **confianza** (VERUMax).

### **2\. Concepto Principal: Una Solución con Dos Caras**

VERUMax Tempus es una solución dual que resuelve dos problemas distintos pero complementarios:

1. **"Quick Add" (Carga Rápida Interna):** Responde a tu pedido. Es una herramienta *privada* en el dashboard del profesional para que pueda agendar un evento o cita en su propio calendario en **menos de 5 segundos**.  
2. **"Booking Page" (Página de Reserva Pública):** Es el módulo que se integra en VERUMax Identitas. Permite que *clientes externos* vean la disponibilidad del profesional (basada en su calendario real) y reserven un turno por sí mismos.

### **3\. Arquitectura Técnica (El Motor de Sincronización)**

* **Conexión** Principal **(Google Calendar API):**  
  * El sistema se basará en la **API de Google Calendar** (vía OAuth 2.0).  
  * En el dashboard de VERUMax, el profesional conectará su cuenta de Google **una sola vez**. A partir de ese momento, Tempus tendrá permiso para leer su disponibilidad (ver espacios libres/ocupados) y para crear nuevos eventos en su nombre.  
* **Integración** Universal (iCal / .ics):  
  * Para la **compatibilidad universal** (Outlook, Apple Calendar, etc.), el sistema **no** intentará conectarse a las APIs de todos ellos.  
  * En su lugar, utilizará el estándar universal:  
    1. **Para Compartir:** Cuando se crea un evento (ya sea interna o externamente), el sistema generará un archivo .ics. Este archivo es la "invitación de calendario" que cualquier aplicación (Outlook, Apple) puede entender e importar.  
    2. **Para Sincronizar (Entrante):** (Avanzado) Permitirá al profesional "pegar" una URL de un calendario externo (ej. de Outlook) en formato .ics para que Tempus pueda leer esa disponibilidad (además de la de Google) al mostrar turnos libres.

### **4\. Funcionalidades Clave (El Dashboard de "Tempus")**

#### **A. "Quick Add": El Agendamiento Sencillo (Tu Solicitud)**

Esta es la herramienta interna del profesional, pensada para la máxima velocidad.

* **Interfaz:** Un widget simple y siempre visible en su dashboard de VERUMax.  
* **Formulario "Ultra-Sencillo":** No será un "cuadro único" (lo cual requiere IA compleja para procesar lenguaje), sino **tres campos directos** que se sienten igual de rápidos:  
  1. **QUÉ:** Un cuadro de texto. (Ej. "Reunión con Dr. Pérez", "Consulta Paciente Marquez").  
  2. **CUÁNDO:** Un selector de fecha.  
  3. **A QUÉ HORA:** Un selector de hora.  
* **Flujo de Trabajo (en segundos):**  
  1. El profesional escribe "Reunión Dr. Pérez", elige la fecha y la hora.  
  2. Hace clic en "Agendar".  
  3. El sistema, usando la API de Google, **crea** el evento instantáneamente **en el Google Calendar** del profesional.  
  4. **Inmediatamente**, la pantalla se actualiza y muestra:  
     * **Mensaje:** "¡Agendado con éxito\!"  
     * **Enlaces para Compartir:**  
       * "Copiar Enlace para Google Calendar" (un enlace gcal)  
       * "Descargar Invitación (.ics)" (para Outlook, Apple, etc.)  
       * "Enviar por WhatsApp" (pre-arma un mensaje con los enlaces)  
* **Modalidad Dual:** El cliente puede hacerlo él mismo (autogestión) o el equipo de VERUMax puede ofrecer un servicio de "secretaría virtual" (servicio asistido).

#### **B. "Booking Page": La Reserva Pública (La Integración con Identitas)**

Esta es la herramienta premium que se vende como parte de VERUMax Identitas

**Configuración (en el Dashboard):**

*   
  * El profesional define sus **reglas de disponibilidad** (ej. "Lunes de 9:00 a 18:00", "Miércoles de 14:00 a 20:00").  
  * Define "Tipos de Cita" (ej. "Primera Consulta \- 1 hora", "Seguimiento \- 30 min").  
* **Vista Pública (en VERUMax Identitas):**  
  * Los clientes visitan el perfil verumax.com/tunombre y ven una nueva pestaña: "Agendar Cita".  
  * Tempus lee en tiempo real el Google Calendar del profesional:  
    1. Toma la disponibilidad (ej. Lunes 9-18).  
    2. Comprueba los eventos ya agendados (ej. "Almuerzo 13-14").  
    3. Muestra al cliente **solo los bloques libres** (ej. Lunes 9:00, 10:00, 11:00, 12:00, 14:00...).  
* **Flujo de Reserva:**  
  1. El cliente elige un tipo de cita, un día y una hora libre.  
  2. Completa sus datos (Nombre, Email, Teléfono).  
  3. Tempus **crea el evento automáticamente** en el Google Calendar del profesional.  
  4. El sistema envía una confirmación por email (vía Communica) tanto al profesional como al cliente, **adjuntando la invitación .ics** para que ambos lo tengan en sus calendarios.

### **5\. Integración con el Ecosistema VERUMax**

* **VERUMax Identitas:** Tempus es el motor de agendamiento del Perfil Pro.  
* **Gestor Nexus:** Cuando un *nuevo* cliente agenda una cita por primera vez, Tempus **crea automáticamente** su ficha en Gestor Nexus. Si el cliente ya existía, Tempus **añade** la cita al **historial** de ese cliente en Nexus.  
* **VERUMax Cognita:** El Agente de IA puede conectarse a Tempus. Un usuario puede chatear y decir: "¿Tienes turno para la próxima semana?". La IA consulta Tempus y ofrece las fechas libres.  
* **VERUMax Custodia:** La cita agendada en Tempus puede aparecer automáticamente en la Historia Clínica del paciente como el punto de partida para una nueva "