# **Plan de Desarrollo: VERUMax Servitium**

Proyecto: VERUMax  
Fecha: 21 de octubre de 2025  
Objetivo: Desarrollar una solución de "Catálogo de Servicios" diseñada para profesionales que venden servicios intangibles. Debe ser un módulo que se integre en VERUMax Identitas para presentar una oferta de servicios estructurada y profesional.

### **1\. Nombre de la Solución**

* **Nombre Propuesto:** **VERUMax Servitium**  
* **Slogan/Concepto:** "Tu Expertise, Estructurado y Ofrecido Profesionalmente."  
* **Justificación:** *Servitium* (del latín "servicio" o "deber")"tiun nombre profesionalteersal que exactamente la función. El slogan se enfoca en **estructurar** el conocimiento (el *expertise*) en productos de servicio claros, listos para ser ofrecidos.

### **2\. Concepto Principal**

**VERUMax Servitium** es la solución para el profesional que vende su conocimiento y tiempo. A diferencia de Opera (que muestra *proyectos pasados*) o Emporium (que vende *productos con stock*), Servitium es un **"Menú de Servicios Profesionales"** enfocado en la *oferta actual*. Permite a un consultor, coach, abogado o terapeuta definir claramente sus servicios, detallar qué incluyen (entregables, metodología) y establecer un llamado a la acción claro para cada uno.

**Público Objetivo:**

Consultores (Marketing, Finanzas, Estrategia).

1. Coaches (Ejecutivos, de Vida, Fitness).**c**a.  
   Profesionales independientes (Abogados, Contadores).-Agencias Pequeñas (Diseño, Software) que venden paquetes de servicios.

### **3\. Arquitectura Técnica (El Motor de Servicios)int**

**Integración Modular:** Servitium es una  **solución** que se activa y se integra en VERUMax Identitas. Cuando está activo, añade una pestaña/sección principal de "Servicios" a la landing page del usuario.

1. **Base de Datos (Multi-Tenant):** Nuevas tablas (aisladas por id\_usuario / id\_organizacion):  
   * servitium\_servicios: id\_servicio, id\_usuario, titulo\_servicio, descripcion\_corta, descripcion\_larga\_html, icono, texto\_cta (ej. "Agendar Consulta"), enlace\_cta (ej. link a Tempus).  
   * servitium\_caracteristicas: id\_caracteristica, id\_servicio, texto\_caracteristica (para los "bullet points").**:**  
   1. 

   **Vistas Públicas:**

   1. **Vista de Listado (.../servicios):** Una página que muestra todos los servicios del profesional en formato de "fichas" o "tarjetas".  
   2. **Vista de Detalle (.../servicios/nombre-servicio):** Una página dedicada a cada servicio, con su descripción detallada, lista de características y el botón de accuna **4\. Funcionalidades Clave (El Dashboard de "Servitium")t**  
      Esta es la interfaz de **autogestión** para el cliente, que aparecerá como una nueva sección en su panel de VERUMax.  
      **i"Mis Servicios":** Listado de todos los servicios creados, con opción de reordenarlos.  
   3. **"Añadir Nuevo Servicio":** Un formulario simple y enfocadota emp texto:e  
      * **Icono del Servicio:** (Selector de iconos de Lucide, ej. briefcase, bar-chart,

n la Historia Clínica del paciente como el punto de partida para una nueva "