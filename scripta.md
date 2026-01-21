Plan de Desarrollo: VERUMax Scripta



Proyecto: VERUMax

Fecha: 21 de octubre de 2025

Objetivo: Desarrollar una solución de blogging sencilla y amigable, diseñada para integrarse como un módulo opcional dentro de las landing pages (VERUMax Identitas), ofreciendo tanto una herramienta de autogestión como un servicio de configuración y carga por parte del equipo de VERUMax.



1\. Nombre de la Solución



Nombre Propuesto: VERUMax Scripta



Slogan/Concepto: "Tus Artículos. Tu Legado Digital Verificado."



Justificación: Scripta (del latín "escritos") es un nombre elegante y profesional que describe perfectamente el contenido (artículos, publicaciones, newsletters). El slogan lo conecta con el pilar de la marca (Verificada) y el beneficio principal para el cliente (generar Autoridad).



2\. Concepto Principal



VERUMax Scripta es un módulo de "Content Marketing" ligero e integrado. No busca competir con plataformas complejas como WordPress, sino ofrecer una herramienta sencilla y amigable para que los profesionales e instituciones puedan:



Publicar contenido de valor (artículos, noticias, estudios de caso) directamente en su perfil/portal VERUMax.



Posicionarse como expertos en su sector.



Mejorar el SEO de su landing page.



Nutrir a su comunidad (clientes, estudiantes, socios) con información relevante.



Su principal fortaleza es la integración total: un artículo publicado se conecta automáticamente con el perfil del autor (Identitas), puede ser enviado a la lista de contactos (Communica) y puede alimentar al Agente de IA (Cognita).



3\. Arquitectura Técnica (El Motor del Blog)



Integración Modular: Scripta se diseñará como un módulo que se "activa" en el plan de un cliente. Si está activo, una nueva pestaña o sección ("Blog" o "Publicaciones") aparecerá en su landing page Identitas y un nuevo gestor en su dashboard.



Base de Datos (Multi-Tenant): Se crearán nuevas tablas, todas aisladas por id\_organizacion (o id\_usuario):



scripta\_posts: post\_id, id\_organizacion, titulo, slug (para la URL amigable), contenido\_html, imagen\_portada, estado ('publicado', 'borrador'), fecha\_publicacion.



scripta\_categorias: categoria\_id, id\_organizacion, nombre\_categoria.



post\_categorias\_pivot: post\_id, categoria\_id.



Aislamiento de Datos: Un cliente (ej. SAJuR) solo podrá ver y gestionar sus propios artículos y categorías.



Páginas Públicas: El sistema generará dinámicamente dos tipos de vistas en el frontend del cliente:



Vista de Listado (Blog Home): tunombre.verumax.com/blog - Muestra todos los artículos publicados.



Vista de Artículo (Single Post): tunombre.verumax.com/blog/\[slug-del-articulo] - Muestra el artículo individual.



4\. Funcionalidades Clave (El Dashboard de "Scripta")



Esta es la interfaz de autogestión para el cliente. Ofrecerá una Modalidad Dual de Publicación:



"Todos los Artículos": Un listado simple que muestra Título, Estado (Publicado/Borrador), Fecha. Acciones: Editar, Borrar, Ver.



"Categorías": Un gestor simple para crear, renombrar y eliminar las categorías del blog.



El cliente tendrá dos formas claras de crear contenido:



A. Flujo 1: "Publicación Rápida" (La Vía Express)



Interfaz: El usuario hace clic en "Publicación Rápida". Se abre una interfaz minimalista con un único cuadro de texto (<textarea>).



Instrucciones: El placeholder indicará: "Pega tu artículo aquí. La primera línea será el título. El resto será el cuerpo del artículo. Se publicará en texto plano."



Lógica de Backend:



Al guardar, el sistema toma todo el texto.



La primera línea se extrae y se guarda en el campo titulo de la base de datos.



El resto del texto se procesa para convertir saltos de línea (\\n) en párrafos (<p>) o saltos de línea (<br>) y se guarda en contenido\_html.



El sistema auto-genera el slug (desde el título) y un extracto (primeros 150 caracteres del cuerpo) para los meta tags.



Los campos imagen\_portada y categorias se omiten (quedan en null).



Feedback Inmediato: Inmediatamente después de guardar, el sistema no redirige a la lista, sino a una pantalla de "¡Publicado con Éxito!" que muestra:



El enlace permanente al artículo.



Botón de "Copiar Enlace".



Botones para compartir directamente en WhatsApp, LinkedIn y Twitter.



B. Flujo 2: "Artículo Completo" (El Editor Avanzado)



Interfaz: El usuario hace clic en "Artículo Completo". Se abre el editor que ya habíamos planificado.



Editor de Texto Amigable (WYSIWYG): Un editor visual limpio. Funciones: Título, Párrafo, Subtítulos (H2, H3), Negrita, Cursiva, Enlaces, Listas, Citas, Inserción de imágenes.



Panel de Ajustes (Barra Lateral):



Control de Estado: "Guardar Borrador" o "Publicar".



URL Amigable (Slug): Editable.



Imagen de Portada: Campo para subir la imagen destacada.



Categorías: Checkboxes para seleccionar categorías.



Extracto: Campo de texto para el resumen (meta descripción).



5\. Modalidad Dual: "Autogestión" vs. "Servicio Asistido"



Opción 1: Autogestión (Incluido en Planes):



El cliente con conocimientos digitales accede al "Dashboard de Scripta" y tiene acceso a ambos flujos de publicación (Rápida y Completa).



Planes: Se puede incluir en Identitas Plus (con "Publicación Rápida" y un límite de 10 artículos) y en VERUMax Praxis (con ambos editores y artículos ilimitados).



Opción 2: Servicio Asistido (Equipo Humano VERUMax):



El Problema: El cliente no tiene tiempo o ganas de escribir.



La Solución: Se vende como un "Plan de Contenidos" mensual.



Flujo de Trabajo:



El cliente envía el contenido en bruto (Word, audio, ideas).



Nuestro equipo humano siempre utilizará el "Editor Completo" (Flujo 2) para garantizar un servicio premium: formatear el texto, optimizarlo para SEO, seleccionar una imagen de portada y publicarlo.



Valor: El cliente obtiene todos los beneficios de una estrategia de marketing de contenidos sin dedicarle tiempo a la parte técnica.



6\. Integración con el Ecosistema VERUMax



Scripta se convierte en un potente motor de contenido para los demás servicios:



VERUMax Identitas: La landing page se actualiza automáticamente con "Últimos Artículos", transformándola de estática a dinámica y mejorando el SEO.



VERUMax Vitae: El usuario puede seleccionar sus mejores artículos de Scripta para añadirlos a su CV Inteligente en una sección de "Publicaciones".



VERUMax Communica: Sinergia perfecta. Al publicar un nuevo artículo, el cliente recibe una sugerencia para "Notificar a mis suscriptores", abriendo Communica con un borrador de newsletter.



VERUMax Cognita: El contenido de todos los artículos publicados se convierte automáticamente en parte de la base de conocimiento del Agente de IA, permitiéndole responder preguntas basadas en las publicaciones del profesional.



Este plan añade una poderosa herramienta de marketing y autoridad a tu suite, reforzando el valor de VERUMax Identitas y creando múltiples puntos de sinergia con el resto del ecosistema.

