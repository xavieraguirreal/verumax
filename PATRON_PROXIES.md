# PATRÓN DE PROXIES - ARQUITECTURA VERUMAX

**Fecha:** 16/11/2025
**Versión:** 1.0
**Propósito:** Documentar el patrón de proxies para soluciones multi-tenant con subdominios

---

## 🏗️ CONCEPTO FUNDAMENTAL

### La Regla de Oro

> **LÓGICA COMPARTIDA** en dominio principal (`verumax.com`)
> **PRESENTACIÓN/ACCESO** en subdominios (`cliente.verumax.com`)

---

## 📐 ARQUITECTURA

### Dominio Principal: `verumax.com`

Contiene **TODA la lógica compartida** (motores, clases, templates):

```
/public/
├── identitas/              ← Motor de sitios web (compartido)
│   ├── config.php
│   ├── identitas_engine.php
│   ├── login.php
│   ├── administrare.php
│   └── templates/
│       ├── header.php
│       ├── footer.php
│       ├── home.php
│       └── page.php
│
├── certificatum/           ← Motor de certificados (compartido)
│   ├── config.php
│   ├── cursus.php
│   ├── validar.php
│   └── generar_documento.php
│
├── scripta/               ← Motor de blog (futuro, compartido)
├── nexus/                 ← Motor de CRM (futuro, compartido)
└── ...
```

### Subdominios: `cliente.verumax.com`

Contienen **SOLO archivos proxy** (mínimos, ligeros):

```
sajur.verumax.com → /subdomains/sajur/
└── index.php               ← Proxy a identitas (slug='sajur')

liberte.verumax.com → /subdomains/liberte/
└── index.php               ← Proxy a identitas (slug='liberte')

otroCliente.verumax.com → /subdomains/otrocliente/
└── index.php               ← Proxy a identitas (slug='otrocliente')
```

---

## 📝 PLANTILLA DE PROXY

### Archivo: `{subdominio}/index.php`

```php
<?php
/**
 * PROXY - {Nombre del Cliente}
 *
 * Este archivo es un proxy ligero que delega toda la lógica
 * al motor Identitas compartido en verumax.com/identitas/
 *
 * IMPORTANTE: Este archivo NO contiene lógica de negocio.
 * Solo instancia el motor con el slug del cliente.
 */

// Incluir motor Identitas desde dominio principal
require_once __DIR__ . '/../../public/identitas/config.php';
require_once __DIR__ . '/../../public/identitas/identitas_engine.php';

// Crear instancia del motor con el slug único del cliente
$identitas = new IdentitasEngine('{slug-del-cliente}');

// Manejar envío de formulario de contacto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'enviar') {
    $resultado = $identitas->procesarContacto($_POST);
    if ($resultado['success']) {
        header('Location: ?enviado=1#contacto');
        exit;
    } else {
        header('Location: ?error=envio#contacto');
        exit;
    }
}

// Renderizar página solicitada o homepage
if (isset($_GET['page'])) {
    $identitas->renderPage($_GET['page']);
} else {
    $identitas->renderHome();
}
```

### Ejemplo Real: SAJuR

**Ubicación:** `/subdomains/sajur/index.php`

```php
<?php
require_once __DIR__ . '/../../public/identitas/config.php';
require_once __DIR__ . '/../../public/identitas/identitas_engine.php';

$identitas = new IdentitasEngine('sajur');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'enviar') {
    $resultado = $identitas->procesarContacto($_POST);
    if ($resultado['success']) {
        header('Location: ?enviado=1#contacto');
        exit;
    } else {
        header('Location: ?error=envio#contacto');
        exit;
    }
}

if (isset($_GET['page'])) {
    $identitas->renderPage($_GET['page']);
} else {
    $identitas->renderHome();
}
```

**Eso es todo.** El archivo proxy de SAJuR tiene **34 líneas**.

---

## 🔗 REGLAS DE ENLACES (URLs)

### ❌ NUNCA usar rutas relativas para lógica compartida

```php
// ❌ MAL - Busca en el subdominio actual
<a href="/identitas/login.php">Admin</a>
<a href="/certificatum/cursus.php">Certificados</a>

// Problema: Si estás en sajur.verumax.com, busca:
// https://sajur.verumax.com/identitas/login.php ← 404!
```

### ✅ SIEMPRE usar URLs absolutas al dominio principal

```php
// ✅ BIEN - Apunta al dominio principal donde está la lógica
<a href="https://verumax.com/identitas/login.php">Admin</a>
<a href="https://verumax.com/certificatum/cursus.php?institutio=sajur">Certificados</a>

// Funciona desde cualquier subdominio
// Todos los clientes usan el mismo código
```

### ✅ Anclas (#) dentro del mismo subdominio

```php
// ✅ BIEN - Navegación interna en el subdominio actual
<a href="#inicio">Inicio</a>
<a href="#sobre-nosotros">Sobre Nosotros</a>
<a href="#contacto">Contacto</a>

// Esto funciona con rutas relativas porque es dentro del mismo dominio
```

---

## 🔄 FLUJO DE NAVEGACIÓN

### Caso 1: Usuario accede al sitio del cliente

```
1. Usuario abre: https://sajur.verumax.com/
   ↓
2. Servidor ejecuta: /subdomains/sajur/index.php
   ↓
3. Proxy instancia: IdentitasEngine('sajur')
   ↓
4. Motor busca en BD: identitas_instances WHERE slug='sajur'
   ↓
5. Motor renderiza: /public/identitas/templates/home.php
   ↓
6. Usuario ve: Sitio de SAJuR con su branding
```

### Caso 2: Usuario hace clic en "Admin"

```
1. Click en botón "Admin" (en sajur.verumax.com)
   ↓
2. Redirige a: https://verumax.com/identitas/login.php
   ↓
3. Muestra login multi-instancia
   ↓
4. Usuario ingresa: admin@sajur / password
   ↓
5. Sistema valida en BD: identitas_instances WHERE admin_usuario='admin@sajur'
   ↓
6. Sesión creada: $_SESSION['admin_identitas']['slug'] = 'sajur'
   ↓
7. Redirige a: https://verumax.com/identitas/administrare.php
   ↓
8. Panel muestra solo datos de SAJuR (filtrado por slug)
```

### Caso 3: Admin hace clic en "Ver sitio"

```
1. En panel admin (verumax.com/identitas/administrare.php)
   ↓
2. Click en "Ver sitio"
   ↓
3. Código lee slug de sesión: $slug = $_SESSION['admin_identitas']['slug']
   ↓
4. Construye URL: "https://{$slug}.verumax.com/"
   ↓
5. Redirige a: https://sajur.verumax.com/
   ↓
6. Vuelve al sitio del cliente
```

### Caso 4: Usuario hace clic en "Certificados"

```
1. En sajur.verumax.com, click en "Portal de Certificados"
   ↓
2. Redirige a: https://verumax.com/certificatum/cursus.php?institutio=sajur
   ↓
3. Motor Certificatum (compartido) busca: WHERE institucion='sajur'
   ↓
4. Muestra certificados de SAJuR
```

---

## 📦 AGREGAR UN NUEVO CLIENTE

### Pasos para agregar "Liberté"

#### 1. Crear entrada en BD

```sql
INSERT INTO identitas_instances (
    slug, nombre, nombre_completo, color_primario,
    admin_usuario, admin_password, admin_email,
    configuracion, plan
) VALUES (
    'liberte',                      -- slug único
    'Liberté',
    'Liberté - Escuela de Formación',
    '#8B4513',
    'admin@liberte',                -- usuario login
    '$2y$10$...',                   -- password hasheado
    'contacto@liberte.com',
    JSON_OBJECT(
        'sitio_web_oficial', 'https://liberte.com',
        'email_contacto', 'contacto@liberte.com',
        'mision', 'Formación profesional...'
    ),
    'basicum'
);
```

#### 2. Crear páginas predefinidas

```sql
SET @liberte_id = LAST_INSERT_ID();

INSERT INTO identitas_paginas (id_instancia, slug, titulo, contenido, orden) VALUES
(@liberte_id, 'inicio', 'Inicio', '<h1>Bienvenido a Liberté</h1>', 0),
(@liberte_id, 'sobre-nosotros', 'Sobre Nosotros', '<h2>Nuestra Historia</h2>', 1),
(@liberte_id, 'servicios', 'Servicios', '<h2>Servicios</h2>', 2),
(@liberte_id, 'contacto', 'Contacto', '<h2>Contacto</h2>', 3);
```

#### 3. Crear subdominio en hosting

Configurar DNS:
```
liberte.verumax.com → /subdomains/liberte/
```

#### 4. Crear archivo proxy

**Archivo:** `/subdomains/liberte/index.php`

```php
<?php
require_once __DIR__ . '/../../public/identitas/config.php';
require_once __DIR__ . '/../../public/identitas/identitas_engine.php';

$identitas = new IdentitasEngine('liberte'); // ← Solo cambiar el slug

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'enviar') {
    $resultado = $identitas->procesarContacto($_POST);
    if ($resultado['success']) {
        header('Location: ?enviado=1#contacto');
        exit;
    } else {
        header('Location: ?error=envio#contacto');
        exit;
    }
}

if (isset($_GET['page'])) {
    $identitas->renderPage($_GET['page']);
} else {
    $identitas->renderHome();
}
```

#### 5. ¡Listo!

- Sitio: `https://liberte.verumax.com/`
- Admin: `https://verumax.com/identitas/login.php`
- Usuario: `admin@liberte`
- Password: (el que hasheaste)

**NO necesitas duplicar código ni templates. Todo está compartido.**

---

## 🎯 VENTAJAS DE ESTE PATRÓN

### ✅ Mantenimiento centralizado

- Cambio en template = afecta a todos los clientes
- Bug fix en motor = se arregla para todos
- Nueva feature = disponible para todos

### ✅ Escalabilidad

- Agregar cliente = 1 archivo proxy (34 líneas)
- Sin duplicación de código
- Performance: todos usan el mismo código cacheado

### ✅ Multi-tenant real

- Cada cliente con su subdominio
- Datos aislados por slug
- Branding personalizado desde BD

### ✅ Seguridad

- Lógica en un solo lugar (más fácil de auditar)
- No hay código duplicado con posibles vulnerabilidades diferentes
- Admin multi-instancia con sesiones separadas

---

## ⚠️ ERRORES COMUNES A EVITAR

### ❌ Error 1: Duplicar lógica en subdominios

```php
// ❌ MAL - No hagas esto
/subdomains/sajur/
├── identitas_engine.php    ← NO duplicar
├── templates/              ← NO duplicar
└── index.php
```

**Corrección:** Solo `index.php` en subdominio, todo lo demás en `/public/`.

### ❌ Error 2: Rutas relativas para lógica compartida

```php
// ❌ MAL
<a href="/identitas/login.php">Admin</a>

// ✅ BIEN
<a href="https://verumax.com/identitas/login.php">Admin</a>
```

### ❌ Error 3: Hardcodear subdominios

```php
// ❌ MAL
<a href="https://sajur.verumax.com/">Ver sitio</a>

// ✅ BIEN - Dinámico basado en slug
<a href="https://<?php echo $slug; ?>.verumax.com/">Ver sitio</a>
```

### ❌ Error 4: Mezclar datos de clientes

```php
// ❌ MAL - Sin filtro
SELECT * FROM identitas_paginas;

// ✅ BIEN - Filtrado por instancia
SELECT * FROM identitas_paginas WHERE id_instancia = :id_instancia;
```

---

## 📚 CHECKLIST PARA NUEVAS SOLUCIONES

Cuando crees una nueva solución (Scripta, Nexus, Lumen, etc.):

### ✅ Motor compartido

- [ ] Crear carpeta en `/public/{nombre-solucion}/`
- [ ] Crear clase motor (ej: `ScriptaEngine`)
- [ ] Configuración en `config.php`
- [ ] Templates en `/templates/`

### ✅ Base de datos

- [ ] Tabla de instancias o campo `id_instancia` en tablas principales
- [ ] Filtrado por cliente en todas las queries

### ✅ Integración con Identitas

- [ ] Agregar columna `modulo_{nombre}` en `identitas_instances`
- [ ] Actualizar `getModulosActivos()` en `identitas_engine.php`
- [ ] Agregar enlace en templates si módulo activo

### ✅ Enlaces

- [ ] URLs absolutas a `https://verumax.com/{solucion}/`
- [ ] Parámetro `?slug=` o `?institutio=` para identificar cliente
- [ ] NO usar rutas relativas

### ✅ Sin proxies necesarios

Los subdominios NO necesitan proxies para soluciones adicionales.
Solo necesitan el proxy de Identitas (index.php).

**Ejemplo:** Scripta (blog)

```php
// En template de Identitas:
<?php if ($modulos_activos['scripta']): ?>
    <a href="https://verumax.com/scripta/blog.php?slug=<?php echo $slug; ?>">
        Blog
    </a>
<?php endif; ?>
```

---

## 🗺️ MAPA COMPLETO

```
USUARIO EN NAVEGADOR
        ↓
┌───────────────────────────────────────┐
│ https://sajur.verumax.com/            │ ← Punto de entrada (subdominio)
└───────────────────────────────────────┘
        ↓
┌───────────────────────────────────────┐
│ /subdomains/sajur/index.php           │ ← Proxy ligero (34 líneas)
│ new IdentitasEngine('sajur')          │
└───────────────────────────────────────┘
        ↓
┌───────────────────────────────────────┐
│ /public/identitas/identitas_engine.php│ ← Motor compartido
│ - Busca config en BD (slug='sajur')  │
│ - Renderiza templates                 │
└───────────────────────────────────────┘
        ↓
┌───────────────────────────────────────┐
│ /public/identitas/templates/home.php  │ ← Template compartido
│ - Muestra branding de SAJuR          │
│ - Enlaces a Admin y Certificatum      │
└───────────────────────────────────────┘
        ↓ (usuario hace click en "Admin")
┌───────────────────────────────────────┐
│ https://verumax.com/identitas/login.php│ ← Admin compartido
└───────────────────────────────────────┘
```

---

## 📄 RESUMEN EJECUTIVO

| Concepto | Ubicación | Tipo |
|----------|-----------|------|
| **Motores** | `verumax.com/identitas/`, `verumax.com/certificatum/` | Compartido |
| **Templates** | `verumax.com/identitas/templates/` | Compartido |
| **Admin** | `verumax.com/identitas/administrare.php` | Compartido (multi-instancia) |
| **Base de datos** | `verumax_identi`, `verumax_certifi` | Compartida (filtrada por slug) |
| **Proxies** | `cliente.verumax.com/index.php` | Por cliente (34 líneas) |
| **Branding** | Base de datos (`identitas_instances`) | Por cliente (dinámico) |

---

**Patrón:** Un motor compartido, múltiples clientes con proxies ligeros.

**Beneficio:** Cambio en 1 lugar = actualización para todos los clientes.

---

*Este documento es la guía de referencia para mantener la arquitectura consistente en todo el ecosistema VERUMax.*
