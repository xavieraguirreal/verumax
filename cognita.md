# **Plan de Desarrollo: VERUMax Cognita**

Proyecto: VERUMax  
Fecha: 21 de octubre de 2025  
Objetivo: Desarrollar un servicio premium de Asistentes Virtuales (Agentes de IA) que puedan ser entrenados con el conocimiento específico de cada cliente (institución o profesional) para ofrecer respuestas precisas y seguras 24/7.

### **1\. Nombre de la Solución**

* **Nombre Propuesto:** **VERUMax Cognita**  
* **Slogan/Concepto:** "Tu Conocimiento, Tu Experto Digital."  
* **Justificación:** *Cognita* (del latín *cognitus*, "conocido" o "sabido") posiciona el servicio como una herramienta de inteligencia y conocimiento. La combinación con **VERUMax** refuerza la idea de que es un conocimiento "verdadero" y "máximo", alineado con la marca principal.

### **2\. Concepto Principal**

**VERUMax Cognita** es una solución de "Asistente como Servicio" (AaaS). No es un chatbot genérico que se conecta a internet. Es un **Agente de IA especializado** que opera dentro de un ecosistema cerrado de información, garantizando que sus respuestas se basen **exclusivamente** en la documentación proporcionada por el cliente.

El objetivo es transformar el conocimiento estático de una organización o profesional (PDFs, documentos, FAQs) en un experto digital interactivo, eliminando la posibilidad de "alucinaciones" (respuestas inventadas) y asegurando que cada interacción sea precisa y alineada con la marca.

### **3\. Arquitectura Técnica (El Motor de IA)**

La tecnología subyacente será **Retrieval-Augmented Generation (RAG)**. El programador debe implementar el siguiente flujo:

1. **Ingesta de Conocimiento:**  
   * Se debe desarrollar una interfaz en el panel de control de cada cliente donde puedan subir su base de conocimiento.  
   * Formatos soportados inicialmente: .pdf, .txt, .docx.  
   * El sistema debe almacenar estos archivos de forma segura, asociándolos al id\_organizacion o id\_usuario.  
2. **Procesamiento y Vectorización:**  
   * Cuando se sube un nuevo documento (o se actualiza la base de conocimiento), un proceso en segundo plano debe:  
     * Extraer el texto de los documentos.  
     * Dividir el texto en fragmentos manejables (chunks).  
     * Convertir cada fragmento en un "embedding" numérico usando un modelo de lenguaje.  
     * Almacenar estos vectores en una **base de datos vectorial** (ej. Pinecone, ChromaDB, o similar), etiquetando cada vector con el id\_organizacion al que pertenece. Este es el "cerebro digital privado" del cliente.  
3. **Flujo de Consulta (La Interacción del Usuario):**  
   * Cuando un usuario final escribe una pregunta en el chatbot...  
   * **Paso A (Búsqueda):** La pregunta del usuario se convierte en un vector. El sistema busca en la base de datos vectorial los fragmentos de texto más similares semánticamente, **filtrando obligatoriamente por el id\_organizacion** del portal donde está el chatbot.  
   * **Paso B (Aumentación):** Los fragmentos de texto recuperados se inyectan en el "prompt" (la instrucción) que se le enviará al modelo de lenguaje principal (LLM, como la API de Gemini).  
   * **Paso C (Generación):** Se le instruye al LLM: "Basándote *única y exclusivamente* en el siguiente contexto \[fragmentos recuperados\], responde a la siguiente pregunta: \[pregunta del usuario\]".  
   * **Paso D (Respuesta):** El LLM genera una respuesta coherente y natural, que se muestra al usuario.

### **4\. Funcionalidades Clave (El Panel de Control del Cliente)**

El cliente debe tener un control total sobre su Agente de IA. Su dashboard debe incluir:

* **Gestor de Base de Conocimiento:**  
  * Una interfaz para subir, listar y eliminar los documentos que forman el "cerebro" de la IA.  
  * Un botón visible de "Re-entrenar Agente" que dispare el proceso de vectorización para los nuevos documentos.  
* **Personalización del Chatbot:**  
  * Un editor para configurar la apariencia de la ventana de chat: color principal, logo en la cabecera del chat.  
  * Campos para personalizar los mensajes automáticos: "Mensaje de Bienvenida", "Mensaje de espera", "Mensaje cuando no encuentra respuesta".  
* **Dashboard de Analíticas:**  
  * Un panel que muestre estadísticas de uso:  
    * Número de conversaciones por día/semana/mes.  
    * "Top 5 de preguntas más frecuentes".  
    * **"Consultas no resueltas":** Un listado de las preguntas que la IA no pudo responder. Esto es oro puro para que el cliente identifique vacíos en su base de conocimiento.  
* **Historial de Conversaciones:**  
  * Un registro de todas las interacciones que los usuarios han tenido con el chatbot, para fines de auditoría y mejora continua.  
* **Instrucciones de Instalación:**  
  * Una sección simple que provea el snippet de código JavaScript que el cliente debe copiar y pegar en su portal para que el chatbot aparezca.

### **5\. Casos de Uso y Aplicación**

El plan debe contemplar los dos productos finales que se venderán:

* **Para Instituciones ("Asistente Académico 24/7"):**  
  * Se integra en las landing pages sectoriales (academico.php, mutuales.php, etc.).  
  * Responde a consultas sobre programas, fechas, requisitos, reglamentos, etc.  
  * Libera al personal administrativo de tareas repetitivas.  
* **Para Profesionales ("Recepcionista Virtual Inteligente"):**  
  * Se integra en la página de perfiles.php (el Perfil Pro).  
  * Responde a consultas de potenciales clientes sobre servicios, honorarios, disponibilidad, y puede guiar hacia un sistema de agendamiento.  
  * Funciona como un filtro de clientes y un asistente de ventas 24/7.

