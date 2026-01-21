# IDENTITAS \- TARJETA DIGITAL PROFESIONAL

**Archivo Landing:** `identitas.php` **Archivo Aplicación:** `identitas_app.php` (pendiente desarrollo) **Color Distintivo:** Gold (\#D4AF37) **Estado:** ✅ Landing completa | 🔜 App en desarrollo

---

## 📋 CONCEPTO GENERAL

### Tagline Principal

**"Tu Presencia Digital Configurada por Nuestro Equipo Humano"**

### Propuesta de Valor

**"Todo lo que Necesitás en un Solo Producto"**

Identitas NO es solo una tarjeta digital. Es un **ecosistema completo de presencia digital profesional** que combina:

1. **Tarjeta Física Digital** (JPG compartible con QR infalsificable)  
2. **Sitio Web Profesional Completo** (tunombre.verumax.com)  
3. **Gestor de Clientes CRM**  
4. **Email Marketing Integrado**  
5. **Recepcionista Virtual IA** (Especializado en tu negocio)  
6. **Sistema de Agendamiento** (Citas automáticas)  
7. **Generador de CV Inteligente** (PDF \+ Web, desde Premium)  
8. **Blog Profesional Scripta** (Incluido en todos los planes)

---

## 🎯 DIFERENCIADOR CLAVE: EQUIPO HUMANO

### Filosofía Central

El usuario NO necesita conocimientos técnicos. Nuestro equipo humano se encarga de TODO.

### Modelo Dual de Acceso

#### 🧑‍💼 Opción 1: Equipo Humano (Para el 80% de usuarios)

- Enviás tu información por email/WhatsApp  
- Nuestro equipo configura TODO por vos  
- Diseñamos tu sitio web personalizado  
- Cargamos tu información inicial  
- Configuramos todas las integraciones  
- Soporte permanente 24/7

#### 🖥️ Opción 2: Dashboard de Autoadministración (Para el 20% técnico)

- Acceso completo al panel de control  
- Modificaciones en tiempo real  
- Control total de contenido  
- Autonomía completa  
- Soporte disponible cuando lo necesites

**Mensaje clave:** *"Vos elegís cómo trabajar. Si preferís delegar, nosotros lo hacemos. Si preferís control total, tenés el dashboard."*

---

## 💰 PRICING Y PLANES

### Configuración Actual

**Archivo:** `includes/pricing_config.php`

$PRICING \= \[

    'basicum' \=\> 18,      // USD/año

    'premium' \=\> 38,      // USD/año

    'excellens' \=\> 98,    // USD/año

    'supremus' \=\> 198     // USD/año

\];

$ALTA\_PRICE\_USD \= 25;         // Setup fee (bonificado en promo)

$DISCOUNT\_PERCENTAGE \= 50;    // Descuento actual: 50% OFF

**Modelo de Negocio:**

- ✅ Pago único anual (no renovación mensual)  
- ✅ Conversión automática USD → Moneda local (ARS, CLP, BRL)  
- ✅ Alta bonificada en promo de lanzamiento  
- ✅ Sin costos ocultos

### 🔥 PROMO ACTUAL: 50% DE DESCUENTO

**Decisión pendiente:**

- [ ] ¿Es promo temporal o permanente?  
- [ ] Si es temporal: ¿Cuándo finaliza? ¿Cuál será el precio final?  
- [ ] Si es permanente: ¿Mostrar precio directo sin tachado?

---

## 🎨 COMPONENTES DE IDENTITAS (Detallado)

### 1\. Tarjeta Física Digital (JPG)

**Especificaciones Técnicas:**

- Formato: JPG alta resolución (300 DPI)  
- Tamaño: Estándar tarjeta de presentación (8.5 x 5.5 cm)  
- QR Code: Infalsificable con verificación criptográfica  
- Diseño: Personalizado con branding del cliente

**Uso:**

- ✅ Compartir por WhatsApp (envío directo de imagen)  
- ✅ Email (adjunto)  
- ✅ LinkedIn (imagen de perfil o post)  
- ✅ Impresión en papel de alta calidad  
- ✅ Impresión en tarjetas PVC

**Ventaja vs competencia:** No necesita app especial para ver. Es una imagen que cualquier persona puede abrir.

---

### 2\. Sitio Web Profesional

**URL Personalizada:**

- Formato: `tunombre.verumax.com` (Basicum, Premium, Excellens)  
- Formato: `tunombre.com` (Supremus \- dominio propio incluido)

**Secciones del Sitio:**

#### Home / Hero

- Video de presentación personal (Premium)  
- Foto profesional  
- Título y especialidad  
- CTA principal (Contactar / Agendar Cita)  
- Stats/Logros destacados

#### Sobre Mí / Nosotros

- Biografía profesional  
- Historia del negocio  
- Misión y valores  
- Equipo (Supremus: subdominios por miembro)

#### Servicios (Premium)

- Lista detallada de servicios  
- Descripción, precios (opcional)  
- Galería por servicio  
- CTA por servicio

#### Contacto

- Formulario de contacto  
- Mapa de ubicación (Google Maps)  
- Horarios de atención  
- Redes sociales  
- WhatsApp directo

---

## 📱 CASOS DE USO POR INDUSTRIA

### Abogados / Estudios Jurídicos

**Plan recomendado:** Premium o Excellens

**Uso principal:**

- Tarjeta digital para networking en tribunales/eventos  
- Sitio web con áreas de práctica  
- Blog con artículos legales (SEO para captación)  
- CRM para gestionar casos  
- Recepcionista IA que responde consultas básicas legales  
- Sistema de agendamiento para consultas iniciales

**ROI:** Un solo caso nuevo puede pagar el plan del año completo.

---

### Arquitectos / Diseñadores

**Plan recomendado:** Premium

**Uso principal:**

- Portfolio visual con 100 imágenes de proyectos  
- Video de presentación del estudio  
- Formulario de contacto para presupuestos  
- Blog con tendencias de diseño  
- QR en tarjeta para mostrar portfolio al instante

**ROI:** Impresión profesional aumenta tasa de cierre.

---

### Contadores / Estudios Contables

**Plan recomendado:** Excellens (para estudios con varios contadores)

**Uso principal:**

- Subdominios para cada contador del estudio  
- CRM con 1,000+ clientes  
- Email marketing para recordatorios (vencimientos, fechas clave)  
- Recepcionista IA con respuestas sobre servicios y precios  
- Sistema de agendamiento para reuniones

**ROI:** Automatización de recordatorios reduce carga administrativa.

---

### Coaches / Consultores

**Plan recomendado:** Premium

**Uso principal:**

- Video de presentación personal (genera confianza)  
- CV con certificaciones verificadas  
- Blog con contenido de valor (captación)  
- Sistema de agendamiento para sesiones  
- Email marketing para nurturing de leads

**ROI:** Posicionamiento como experto aumenta valor percibido y precio por sesión.

---

### Médicos / Profesionales de Salud

**Plan recomendado:** Premium

**Uso principal:**

- Información de especialidades y prestaciones  
- Sistema de turnos online (reduce llamadas telefónicas)  
- Recepcionista IA para consultas frecuentes  
- Blog con tips de salud  
- Integración con obras sociales (opcional)

**ROI:** Reducción de tiempo administrativo \+ captación de pacientes nuevos.

---

### Agencias / Estudios con Equipo

**Plan recomendado:** Excellens o Supremus

**Uso principal:**

- Múltiples páginas (por servicio, por industria)  
- Subdominios para cada miembro del equipo (Supremus)  
- Portfolio corporativo extenso  
- CRM compartido con roles y permisos  
- Analíticas para medir conversión

**ROI:** Imagen corporativa profesional aumenta ticket promedio.

---

### Franquicias / Multi-Sucursal

**Plan recomendado:** Supremus

**Uso principal:**

- Dominio corporativo propio (marca.com)  
- Subdominio por sucursal (sucursal1.marca.com)  
- Sistema de roles (admin central \+ gerentes locales)  
- CRM unificado de toda la red  
- Reportes consolidados

**ROI:** Unificación de marca \+ control centralizado.

---

**Integraciones:**

- API de Exchange Rates (conversión de moneda)  
- Google Calendar API  
- Email SMTP (envío de emails)  
- WhatsApp Business API (opcional)

---

## 🚀 DESARROLLO DE identitas\_app.php

### Funcionalidades Mínimas (MVP)

**\[ \] Dashboard Principal**

- Resumen de métricas (visitas, leads, citas)  
- Accesos rápidos a secciones

**\[ \] Gestión de Perfil**

- Editar información personal  
- Subir foto de perfil  
- Video de presentación (Premium+)  
- Actualizar redes sociales

**\[ \] Gestión de Servicios**

- Agregar/Editar/Eliminar servicios  
- Descripción, precio, duración  
- Galería por servicio

**\[ \] Estadísticas**

- Gráficos de visitas  
- Escaneos de QR (Premium+)  
- Conversiones  
- Exportar reportes (Excellens+)

---

## 💡 IDEAS Y MEJORAS FUTURAS

### Monetización Adicional

**\[ \] Add-ons (Complementos pagos):**

- Capacidad extra de contactos CRM  
- Emails extra  
- Consultas IA extra  
- Dominio personalizado para planes inferiores  
- Diseño custom premium

**\[ \] Servicio Asistido Identitas:** Ofrecer servicio humano mensual:

- Actualización mensual de contenido  
- Gestión de email marketing  
- Community manager de redes sociales  
- Paquete completo

**Fortaleza principal:** Propuesta de "Equipo Humano" es un diferenciador MUY fuerte vs competencia.

**Riesgo principal:** Pricing extremadamente bajo puede ser insostenible si los costos operativos (equipo humano, infraestructura, IA) son altos.

**Recomendación:** Validar costos reales y ajustar pricing si es necesario ANTES de lanzar oficialmente.

---

