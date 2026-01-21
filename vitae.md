# **Plan de Desarrollo: VERUMax Vitae**

Proyecto: VERUMax  
Fecha: 21 de octubre de 2025  
Objetivo: Desarrollar una solución especializada que permita a los usuarios de VERUMax crear y compartir una "Hoja de Vida Digital Verificada", optimizada para postulaciones laborales y presentaciones profesionales.

### **1\. Nombre de la Solución**

* **Nombre Propuesto:** **VERUMax Vitae**  
* **Slogan/Concepto:** "Tu CV, Potenciado y Verificado."  
* **Justificación:** *Vitae* (del latín "vida", como en *Curriculum Vitae*) conecta directamente con el propósito del producto. El slogan enfatiza la **mejora** sobre un CV tradicional ("Potenciado") y el **diferencial clave** de la marca ("Verificado").

### **2\. Concepto Principal**

**VERUMax Vitae** no es un simple creador de CVs online. Es una herramienta que transforma el currículum estático en una **Hoja de Vida Digital Interactiva y Verificable**. Su propósito es servir como una "carta de presentación" dinámica y un resumen profesional conciso, diseñado específicamente para ser compartido en procesos de selección o networking formal.

**Público Objetivo:** Personas buscando empleo, estudiantes, recién graduados, profesionales que necesitan un resumen rápido de su perfil para conferencias o colaboraciones.

### **3\. Arquitectura y Producto**

* **Integración Modular:** no es un producto que se venda por separado. Es un **módulo premium** que se activa y se integra directamente en `VERUMax Identitas`. Cuando está activo, añade una pestaña principal de "CV"  a la landing page del usuario  
* **Integración:** Se alimenta de los datos cargados por el usuario en su panel de VERUMax (datos personales, experiencia, educación) y, crucialmente, de las credenciales verificadas asociadas a su cuenta a través de VERUMax Certifica.  
* **Generador en el Panel de Control:**  
  * El usuario accederá a una nueva sección "VERUMax Vitae".  
  * Podrá **crear múltiples versiones** de su Hoja de Vida, adaptadas a diferentes postulaciones (ej. "CV para puesto de Marketing", "CV para rol Académico").  
  * Una interfaz intuitiva permitirá **seleccionar qué secciones** del perfil general (experiencia, educación, proyectos del portfolio) y **qué credenciales verificadas específicas** quiere incluir en cada versión.  
  * Posibilidad de añadir un **"Objetivo Profesional"** o **"Resumen Ejecutivo"** personalizado para cada versión.

### **4\. Funcionalidades Clave de la "Hoja de Vida Digital"**

La página web generada (ej. verumax.com/cv/usuario/nombre-cv) tendrá:

* **Diseño Profesional y Conciso:**  
  * Plantillas limpias, elegantes y optimizadas para lectura rápida en pantalla. Enfoque en la legibilidad y la jerarquía de la información.  
* **Contenido Esencial:**  
  * **Encabezado:** Foto (opcional), Nombre Completo, Título Profesional / Objetivo. Datos de contacto clave (Email, Teléfono, LinkedIn).  
  * **Resumen/Perfil Profesional:** El texto personalizado por el usuario para esa versión.  
  * **Experiencia Laboral:** Listado conciso.  
  * **Educación:** Títulos y certificaciones principales.  
  * **Habilidades Clave:** Listado o nube de tags.  
* **Sección Destacada: "Credenciales Verificadas por VERUMax":**  
  * **El Gran Diferencial:** Un apartado visualmente atractivo que muestra los **logos y nombres de las credenciales más relevantes** (seleccionadas por el usuario) que ha obtenido a través de instituciones asociadas a VERUMax.  
  * **Interactividad:** Cada credencial mostrada tendrá un botón o enlace de **"Verificar Autenticidad"** que llevará directamente a la vista\_validacion.php correspondiente, probando instantáneamente la validez del logro ante el reclutador.  
* **Enlace Opcional al Portfolio Completo:**  
  * Un botón discreto como "Ver Portfolio Detallado" o "Conocer Más" que enlaza a la landing page completa de "Perfiles Pro" del usuario (si la tiene activa).  
* **QR Code Asociado:**  
  * Cada Hoja de Vida generada tendrá su propio QR único que enlaza a su URL. Ideal para añadir al CV en papel, firma de email o compartir rápidamente.  
* **(Premium) PDF Interactivo Descargable:**  
  * Opción de descargar una versión en PDF de alta calidad, donde los enlaces de verificación de las credenciales son clicables.

### **Sinergia con el Ecosistema VERUMax**

* **Identitas:** Vitae se nutre de los datos de Identitas y actúa como un "embudo" o "resumen ejecutivo" del mismo.  
* **Certifica:** Es la fuente de las credenciales verificadas, el corazón del valor diferencial de Vitae.  
* **Nexus / Académico / Artistas:** Las instituciones que usan VERUMax indirectamente potencian el valor de