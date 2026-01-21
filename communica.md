# **Plan de Desarrollo: VERUMax Communica**

Proyecto: VERUMax  
Fecha: 21 de octubre de 2025  
Objetivo: Desarrollar una solución de email marketing integrada que permita a los clientes de VERUMax enviar comunicaciones masivas y segmentadas a los contactos gestionados en el "Gestor Nexus", utilizando SendGrid como proveedor de envío.

### **1. Nombre de la Solución**

* **Nombre Propuesto:** **VERUMax Communica**
* **Slogan/Concepto:** "Conecta con tu Comunidad."
* **Justificación:** *Communica* (del latín "comunicar") es directo y claro. El slogan enfatiza la **integración** ("Directo desde VERUMax") y el **propósito** ("Conecta con tu Comunidad"), sea esta de estudiantes, socios, clientes, etc.

### **2. Concepto Principal**

**VERUMax Communica** no pretende ser un reemplazo completo de plataformas especializadas como Mailchimp, sino una **herramienta de comunicación esencial y optimizada** para los usuarios de VERUMax. Su principal fortaleza reside en la **integración nativa con Gestor Nexus**, permitiendo una segmentación precisa y el envío de mensajes relevantes basados en los datos ya gestionados en la plataforma.

El objetivo es ofrecer una solución amigable y eficiente para que las instituciones y profesionales puedan mantener informada a su base de contactos, enviar newsletters, anunciar novedades o promocionar eventos, sin tener que exportar e importar listas constantemente entre diferentes sistemas.

### **3. Arquitectura Técnica (El Motor de Envío)**

* **Integración con Gestor Nexus:** Communica debe poder leer las listas de miembros directamente desde Nexus. La clave es poder **filtrar y segmentar** esa lista basándose en los campos estándar y personalizados que cada cliente haya definido (ej. "Enviar solo a Socios Activos", "Enviar a Estudiantes del Curso X", "Enviar a Clientes de Mar del Plata").
* **Servicio Externo de Envío (ESP - SendGrid):**

  * **Proveedor Elegido:** **SendGrid**.
  * **Integración:** Se utilizará la **API oficial de SendGrid** y su librería PHP para realizar todos los envíos de email.
  * **Ventajas:**

    * **Alta Entregabilidad:** Aprovecha la infraestructura y reputación de SendGrid para maximizar la llegada a la bandeja de entrada.
    * **Tracking Completo:** Permite rastrear tasas de apertura, clics, bajas y rebotes.
    * **Gestión de Reputación:** SendGrid maneja la reputación de las IPs de envío.
    * **Escalabilidad:** Permite empezar con planes gratuitos o económicos y escalar según la necesidad.

  * **Autenticación de Remitentes:** Se debe implementar un proceso en el dashboard del cliente para guiarlo en la **autenticación de su dominio o subdominio** (ej: sajur.verumax.com, juanperez.verumax.com) dentro de SendGrid (configuración de registros SPF, DKIM). Esto es **crucial** para poder enviar emails *desde* su dirección personalizada. Todos los envíos desde diferentes dominios autenticados compartirán los límites del plan de la cuenta principal de VERUMax en SendGrid.

* **Base de Datos:** Se necesitarán nuevas tablas para almacenar:

  * campañas\_email (asunto, contenido\_html, contenido\_texto, fecha\_envio, estado, id\_organizacion, id\_segmento, sendgrid\_campaign\_id\*).
  * listas\_segmentadas (id\_organizacion, nombre\_segmento, definicion\_filtros\_json).
  * plantillas\_email (id\_organizacion, nombre\_plantilla, contenido\_html, sendgrid\_template\_id\*).
  * estadisticas\_email (id\_campaña, id\_miembro, evento: 'enviado', 'abierto', 'clic', 'baja', 'rebotado', timestamp, url\_clicada\*). *\* Campos opcionales dependiendo de la profundidad de la integración con SendGrid Webhooks.*

* **Aislamiento Multi-Tenant:** Todas las operaciones deben estar estrictamente aisladas por id\_organizacion.

### **4. Funcionalidades Clave (El Dashboard de Communica)**

* **Gestor de Listas (Integrado con Nexus):**

  * Visualización y Segmentación dinámica de miembros de Nexus.

* **Creador de Campañas:**

  * Configuración (Asunto, Remitente verificado).
  * Selección de Segmentos de Nexus.
  * **Diseño del Email:** Editor WYSIWYG, Selector de Plantillas (propias o pre-diseñadas), Personalización con etiquetas (\[Nombre]).
  * Envío inmediato, Programación (requiere Cron Job) y Email de Prueba.

* **Gestor de Plantillas:** Crear y guardar diseños reutilizables.
* **Dashboard de Estadísticas:**

  * **Visión General:** Resumen de campañas con tasas de apertura, clics, bajas y rebotes (datos obtenidos vía API de SendGrid o Webhooks).
  * **Detalle por Campaña:** Listado de destinatarios y su estado (abrió, hizo clic, etc.). Exportación de listas.**Ecosistema VERUMax**

* **Desde Nexus:** En la ficha de un miembro, podría haber un historial de las campañas de email que se le han enviado.
* **Desde Otros Módulos:** Se podrían disparar emails automáticos desde otros servicios. Por ejemplo, cuando se emite un certificado ("VERUMax Certifica"), que se envíe automáticamente un email de notificación usando una plantilla de Communica.

Este plan añade una capacidad de comunicación fundamental a tu suite, haciendo que **VERUMax Praxis** sea una solución aún más indispensable para tus clientes al permitirles no solo gestionar, sino también **interactuar activamente** con su comunidad.

