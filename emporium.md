# EMPORIUM - TIENDA ONLINE PROFESIONAL

**Archivo Landing:** `emporium.php` (pendiente)
**Archivo Aplicación:** PrestaShop (instancia dedicada por cliente)
**Estado:** 📋 Planeado | 🔜 Desarrollo pendiente

---

## 📋 CONCEPTO GENERAL

### Tagline Principal

**"Tu Tienda Online Profesional, Potenciada por VERUMax"**

### Propuesta de Valor

**"Vendé Online sin Complicaciones Técnicas"**

Emporium NO es un catálogo simple (eso es Vitreum). Es una **tienda online completa con carrito, pagos y gestión de pedidos**, basada en PrestaShop (líder mundial en e-commerce), pero ofrecida como **servicio gestionado**.

**¿Qué incluye?**

1. **Instancia PrestaShop Dedicada** (subdominio.verumax.com o dominio propio)
2. **Carrito de Compras Funcional** (stock, variantes, descuentos)
3. **Pasarelas de Pago Preconfiguradas** (Mercado Pago, PayPal, Stripe)
4. **Gestión de Pedidos y Envíos** (estados, tracking, notificaciones)
5. **Integración con Ecosistema VERUMax** (Nexus CRM, Communica, Certificatum)
6. **Módulos Exclusivos VERUMax** (certificados de autenticidad por producto)

---

## 🎯 DIFERENCIADOR CLAVE: E-COMMERCE GESTIONADO

### Filosofía Central

**El problema:** Instalar y mantener una tienda online (PrestaShop, WooCommerce, etc.) es complejo, técnico y costoso.

**La solución:** Ofrecemos PrestaShop como **PaaS (Platform as a Service)**:
- Nosotros instalamos y configuramos todo
- Nosotros mantenemos y actualizamos la plataforma
- Nosotros gestionamos el hosting y la seguridad
- El cliente solo gestiona sus productos y ventas

### Diferencia con Otras Soluciones VERUMax

| Producto | Propósito | Ventas Online |
|----------|-----------|---------------|
| **Vitreum** | Catálogo de productos (exhibición) | ❌ No vende, solo muestra |
| **Emporium** | Tienda online completa | ✅ Carrito, pagos, envíos |
| **Opera** | Portfolio de servicios/proyectos | ❌ No vende productos |
| **Lumen** | Portfolio fotográfico | ❌ Solo exhibe fotos |

---

## 🏗️ ARQUITECTURA TÉCNICA

### Modelo Multi-Instancia (No Multi-Tenant)

Cada cliente obtiene su **propia instancia aislada de PrestaShop**:

```
Cliente A: tienda-artesanias.verumax.com
├── PrestaShop 8.x dedicado
├── Base de datos MySQL propia
├── Archivos aislados
└── Módulos VERUMax instalados

Cliente B: mitienda.com (dominio propio)
├── PrestaShop 8.x dedicado
├── Base de datos MySQL propia
├── Archivos aislados
└── Módulos VERUMax instalados
```

**Ventajas:**
- ✅ Seguridad total (aislamiento completo)
- ✅ Personalización sin límites
- ✅ Escalabilidad individual
- ✅ No hay riesgo de que un cliente afecte a otro

---

### Script de Auto-Instalación

Cuando un cliente contrata Emporium, el sistema ejecuta automáticamente:

**Paso 1: Preparación de Infraestructura**
```bash
# Crear subdominio o configurar dominio propio
# Crear directorio: /var/www/tienda-{cliente_id}/
# Crear base de datos MySQL: emporium_{cliente_id}
# Crear usuario MySQL con permisos
```

**Paso 2: Despliegue de PrestaShop**
```bash
# Descargar última versión estable de PrestaShop
# Descomprimir en directorio del cliente
# Configurar permisos de archivos
# Ejecutar instalador programático de PrestaShop
```

**Paso 3: Instalación de Módulos VERUMax**
```bash
# Instalar módulos exclusivos:
# - verumax_nexus_sync.zip
# - verumax_certifica.zip
# - verumax_communica.zip
# - verumax_cognita.zip (solo Supremus)
```

**Paso 4: Configuración Inicial**
```bash
# Aplicar tema VERUMax base
# Configurar pasarelas de pago por región
# Vincular con cuenta Identitas del cliente
# Enviar credenciales de acceso
```

---

## 🔌 MÓDULOS EXCLUSIVOS VERUMAX

### 1. verumax_nexus_sync.zip

**Funcionalidad:** Sincronización automática con CRM Nexus

**Flujo:**
```
Cliente compra en Emporium
    ↓
PrestaShop genera pedido
    ↓
Módulo captura datos del comprador
    ↓
Envía via API a Nexus CRM
    ↓
Comprador se añade automáticamente a contactos del cliente
```

**Beneficio:** Base de clientes unificada en todo VERUMax

---

### 2. verumax_certifica.zip

**Funcionalidad:** Certificados de Autenticidad por Producto

**Uso típico:** Artistas, artesanos, productos premium

**Panel Admin:**
```
Editar Producto > Pestaña "Certificado VERUMax"
├── [ ] Incluir Certificado de Autenticidad
├── Institución emisora: [Selector]
├── Tipo de certificado: [Selector]
└── Datos adicionales: [Textarea]
```

**Vista Pública:**
```
Ficha de Producto
├── Fotos del producto
├── Descripción
├── Precio
└── 🎓 Certificado de Autenticidad Verificado
    ├── Sello de institución
    ├── Código QR único
    └── [Ver Certificado Completo]
```

**Ejemplo:** Artista vende cuadro original con certificado que valida autoría y fecha de creación.

---

### 3. verumax_communica.zip

**Funcionalidad:** Emails Transaccionales via VERUMax Communica

**Reemplaza emails nativos de PrestaShop:**
- Confirmación de pedido
- Cambio de estado de pedido
- Notificación de envío
- Factura generada
- Solicitud de review

**Ventajas:**
- ✅ Mayor entregabilidad (SendGrid)
- ✅ Branding consistente con Identitas
- ✅ Trackeo de apertura/clics
- ✅ No cae en spam

---

### 4. verumax_cognita.zip (Solo Supremus)

**Funcionalidad:** Chatbot IA entrenado con catálogo

**Entrenamiento automático:**
- Lee todos los productos de PrestaShop
- Aprende descripciones, precios, stock
- Conoce políticas de envío y devoluciones

**Responde consultas:**
- "¿Tenés talle M en la remera azul?"
- "¿Cuánto sale el envío a Córdoba?"
- "¿Qué diferencia hay entre el modelo A y B?"
- "¿Aceptan Mercado Pago?"

**Interfaz:** Widget flotante en la tienda (estilo WhatsApp)

---

## 🛒 FUNCIONALIDADES DE PRESTASHOP

### Panel de Administración Completo

**Gestión de Catálogo:**
- Productos simples y con variantes (talle, color)
- Categorías multinivel
- Atributos personalizados
- Imágenes múltiples por producto
- SEO por producto

**Gestión de Pedidos:**
- Estados personalizables
- Impresión de facturas
- Etiquetas de envío
- Tracking de envíos
- Historial completo

**Gestión de Clientes:**
- Base de datos de compradores
- Historial de compras
- Direcciones guardadas
- Grupos de clientes (VIP, mayoristas)

**Estadísticas e Informes:**
- Ventas por período
- Productos más vendidos
- Tasa de conversión
- Carritos abandonados
- Reportes exportables

**Marketing:**
- Códigos de descuento
- Cupones
- Reglas de precios
- Cross-selling
- Up-selling

---

## 💳 PASARELAS DE PAGO PRECONFIGURADAS

### Por Región

**Argentina:**
- ✅ Mercado Pago (tarjetas, efectivo, cuotas)
- ✅ Transferencia bancaria
- ✅ Efectivo contra entrega

**Internacional:**
- ✅ PayPal
- ✅ Stripe
- ✅ Transferencia SWIFT

**Configuración:** Nuestro equipo configura las pasarelas según la ubicación del cliente.

---

## 🧑‍💼 MODELO DUAL DE ACCESO

### Opción 1: Autogestión (Usuario Experimenta)

**Acceso completo al panel de PrestaShop:**
- Dashboard con estadísticas
- Carga de productos (manual o CSV)
- Gestión de pedidos
- Configuración de envíos
- Diseño de tienda (temas)

**Ideal para:**
- E-commerces establecidos
- Usuarios con experiencia en PrestaShop/WooCommerce
- Equipos con recursos técnicos

---

### Opción 2: Servicio Asistido por Equipo Humano

**"Tienda Llave en Mano"**

**El problema:** El cliente tiene productos excelentes pero:
- No sabe usar PrestaShop
- No tiene tiempo para gestionar
- No quiere lidiar con aspectos técnicos

**La solución (servicio premium):**

#### Paso 1: Configuración Inicial (incluida)
- Diseño de tienda con branding del cliente
- Personalización de colores y logo
- Configuración de pasarelas de pago
- Setup de métodos de envío
- Políticas de devolución

#### Paso 2: Carga de Catálogo Inicial
- Cliente envía: fotos, descripciones, precios
- Nuestro equipo:
  - Optimiza imágenes (recorte, compresión)
  - Redacta/mejora descripciones SEO
  - Carga productos en PrestaShop
  - Organiza en categorías

**Pricing:**
- Hasta 20 productos: Incluido en setup
- 21-50 productos: +$100 USD
- 51-100 productos: +$200 USD
- +100 productos: Cotización personalizada

#### Paso 3: Gestión Mensual (opcional)
**Servicio adicional recurrente:**
- Actualización de stock
- Procesamiento de pedidos
- Respuesta a consultas de clientes
- Actualizaciones de productos/precios
- Reportes mensuales

**Pricing mensual:**
- Básico (hasta 50 pedidos/mes): $150 USD/mes
- Estándar (hasta 200 pedidos/mes): $300 USD/mes
- Premium (pedidos ilimitados): $500 USD/mes

---

## 💼 PÚBLICO OBJETIVO

### Comercios / Tiendas

**Ejemplos:**
- Ropa y accesorios
- Productos de belleza
- Electrónica
- Librería
- Juguetería

**Necesidad:** Vender online con gestión completa de stock y envíos

---

### Artesanos / Makers

**Ejemplos:**
- Cerámica artesanal
- Joyería hecha a mano
- Productos de cuero
- Decoración del hogar
- Alimentos gourmet

**Necesidad:** Tienda online con certificados de autenticidad

---

### Artistas Visuales

**Ejemplos:**
- Pinturas originales
- Esculturas
- Ilustraciones
- Fotografía de arte
- Arte digital (NFTs físicos)

**Necesidad:** Vender obra con certificado que valida autenticidad

**Integración con Lumen:**
- Portfolio en Lumen (exhibición)
- Tienda en Emporium (ventas de originales/copias)

---

### Emprendedores / Startups

**Ejemplos:**
- Productos propios (marca blanca)
- Importadores
- Dropshipping
- Productos digitales + físicos

**Necesidad:** E-commerce escalable y profesional sin inversión técnica

---

### Instituciones Educativas

**Ejemplos:**
- Venta de uniformes
- Material didáctico
- Merchandising institucional
- Certificados impresos

**Necesidad:** Tienda institucional con certificados VERUMax integrados

---

## 🔗 INTEGRACIONES CON ECOSISTEMA VERUMAX

### Con Identitas (Core)
- Emporium se vincula desde el sitio Identitas
- Botón "Tienda Online" en menú principal
- Widget "Productos Destacados" en home de Identitas
- Hereda branding (colores, logo, fuentes)

### Con Nexus (CRM)
- Compradores se añaden automáticamente a contactos
- Segmentación: "Clientes que compraron"
- Historial de compras visible en CRM
- Sincronización bidireccional

### Con Communica (Email Marketing)
- Emails transaccionales de pedidos
- Campañas a compradores (cross-sell, up-sell)
- Recuperación de carritos abandonados
- Newsletters de productos nuevos

### Con Certificatum (Credenciales)
- Productos con certificados de autenticidad
- QR en certificado apunta a ficha del producto
- Validación pública de certificados
- Ideal para arte, joyería, productos premium

### Con Vitreum (Catálogo)
**Flujo típico:**
1. Cliente Premium tiene Vitreum (catálogo integrado en Identitas)
2. Negocio crece, necesita vender online
3. Upgrade a Emporium (tienda completa)
4. Opción: mantener Vitreum como "Productos Destacados" y Emporium como "Tienda Completa"

---

## 💰 MODELO DE NEGOCIO

### Inclusión en Planes Identitas

**Emporium NO está incluido en ningún plan base de Identitas.**

Es un **add-on premium** que se contrata por separado:

| Plan Emporium | Productos | Pedidos/Mes | Precio Anual |
|---------------|-----------|-------------|--------------|
| **Tienda Básica** | Hasta 100 | Hasta 50 | $300 USD/año |
| **Tienda Estándar** | Hasta 500 | Hasta 200 | $600 USD/año |
| **Tienda Premium** | Ilimitado | Ilimitado | $1,200 USD/año |

**Incluye:**
- Instancia PrestaShop dedicada
- Hosting y mantenimiento
- Módulos VERUMax base (Nexus, Communica, Certifica)
- Pasarelas de pago configuradas
- Soporte técnico
- Actualizaciones automáticas

**Add-ons opcionales:**
- Módulo Cognita (chatbot IA): +$200 USD/año
- Dominio propio: +$50 USD/año
- Servicio gestión mensual: desde $150 USD/mes
- Migración desde otra plataforma: desde $300 USD (único)

---

### Setup Fee

**Alta de Emporium:** $200 USD (pago único)

**Incluye:**
- Instalación de PrestaShop
- Configuración de pasarelas
- Setup de envíos por región
- Diseño básico con branding del cliente
- Carga de hasta 20 productos
- Capacitación (1 hora)

**Bonificable:** Si el cliente contrata servicio "Tienda Llave en Mano", el setup fee se bonifica.

---

## 📊 MÉTRICAS DE ÉXITO (Fase 1)

- ✅ Auto-instalación de PrestaShop < 5 minutos
- ✅ Tiempo de setup completo < 24 horas
- ✅ 95%+ de uptime (disponibilidad)
- ✅ Sincronización con Nexus en tiempo real
- ✅ 10+ tiendas activas en primer trimestre
- ✅ Tasa de conversión promedio > 2%

---

## 🔧 STACK TECNOLÓGICO

**E-commerce Core:**
- PrestaShop 8.x (última versión estable)
- MySQL 8.0 (base de datos por instancia)
- PHP 8.1+ (servidor)
- Apache/Nginx (web server)

**Módulos VERUMax:**
- PHP 8.1+ (desarrollo de módulos)
- API REST para integraciones
- Webhooks para sincronización

**Pasarelas de Pago:**
- SDK Mercado Pago
- PayPal REST API
- Stripe API

**Hosting:**
- VPS dedicado o cloud (AWS/DigitalOcean)
- SSL incluido (Let's Encrypt o comercial)
- CDN para imágenes (opcional)

---

## 🚀 ROADMAP DE IMPLEMENTACIÓN

### FASE 1: Infraestructura (2-3 meses)
**Objetivo:** Sistema de auto-instalación funcionando

**Tareas:**
1. [ ] Script de auto-instalación de PrestaShop
2. [ ] Sistema de gestión de instancias
3. [ ] Panel de control VERUMax para monitoreo
4. [ ] Configuración de servidor(es)
5. [ ] Automatización de backups
6. [ ] Testing de instalación/desinstalación

---

### FASE 2: Módulos VERUMax (2 meses)
**Objetivo:** Integración completa con ecosistema

**Tareas:**
1. [ ] Desarrollo módulo verumax_nexus_sync
2. [ ] Desarrollo módulo verumax_certifica
3. [ ] Desarrollo módulo verumax_communica
4. [ ] Testing de módulos
5. [ ] Documentación técnica
6. [ ] Publicación en marketplace PrestaShop (opcional)

---

### FASE 3: Servicio Asistido (1-2 meses post-MVP)
**Objetivo:** Ofrecer servicio "Tienda Llave en Mano"

**Tareas:**
1. [ ] Protocolo de configuración inicial
2. [ ] Plantillas de diseño predefinidas
3. [ ] Sistema de carga masiva de productos
4. [ ] Capacitación de equipo de soporte
5. [ ] Pricing del servicio gestionado

---

### FASE 4: Mejoras Avanzadas (3-6 meses post-lanzamiento)

**Características adicionales:**
1. [ ] Módulo verumax_cognita (chatbot IA)
2. [ ] Marketplace de temas VERUMax para PrestaShop
3. [ ] Integración con marketplaces (Mercado Libre)
4. [ ] Sistema de afiliados
5. [ ] App móvil (PWA) para administración

---

## 🎯 DIFERENCIADORES vs COMPETENCIA

| Característica | Tiendanube/Shopify | Emporium |
|----------------|-------------------|----------|
| **Software** | Propietario cerrado | ✅ PrestaShop open source |
| **Personalización** | Limitada | ✅ Total (código abierto) |
| **Integración con CRM** | Parcial | ✅ Total con Nexus |
| **Certificados por producto** | No | ✅ Módulo Certifica |
| **Email marketing integrado** | Add-on externo | ✅ Communica incluido |
| **Chatbot IA especializado** | Add-on caro | ✅ Cognita (plan Premium) |
| **Equipo humano gestiona** | No | ✅ Servicio "Llave en Mano" |
| **Comisión por venta** | 1-3% | ✅ 0% (solo plan anual) |

---

## 📝 NOTAS IMPORTANTES

### Diferencia con Vitreum
- **Vitreum:** Catálogo de productos sin ventas (integrado en Identitas, sin costo adicional según plan)
- **Emporium:** Tienda completa con carrito, pagos, pedidos (add-on premium independiente)

### Migración Vitreum → Emporium
**Flujo recomendado:**
1. Cliente inicia con Vitreum (catálogo simple)
2. Negocio crece, necesita vender online
3. Upgrade a Emporium
4. Importación automática de productos desde Vitreum a PrestaShop

### Ventajas de PrestaShop vs Desarrollo Propio
- ✅ Software maduro y probado (15+ años)
- ✅ Comunidad enorme (documentación, módulos, soporte)
- ✅ Actualizaciones de seguridad constantes
- ✅ Certificación PCI-DSS para pagos
- ✅ Miles de módulos disponibles
- ✅ Multi-idioma y multi-moneda nativo
- ✅ SEO optimizado out-of-the-box

### Soporte
**Niveles de soporte:**
- Tienda Básica: Email (48hs)
- Tienda Estándar: Email (24hs) + Chat
- Tienda Premium: Email/Chat/WhatsApp (12hs) + Prioritario

---

**Fecha de creación:** 21 de octubre, 2025
**Última actualización:** 8 de noviembre, 2025
**Estado:** 📋 Planeado - Desarrollo pendiente
