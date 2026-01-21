# Guía Completa: Crear Nueva Institución en VERUMax

## Checklist Rápido

- [ ] 1. Base de datos: Crear instancia
- [ ] 2. Carpeta: Copiar template y configurar
- [ ] 3. Hosting: Crear subdominio
- [ ] 4. SendGrid: Configurar email
- [ ] 5. Admin: Crear usuario administrador
- [ ] 6. Branding: Logo, colores, firma

---

## 1. Base de Datos

### 1.1 Crear instancia en `verumax_general.instancias`

```sql
INSERT INTO instancias (
    slug,
    nombre,
    nombre_completo,
    idioma_default,
    idiomas_habilitados,
    color_primario,
    color_secundario,
    color_acento,
    logo_url,
    logo_estilo,
    firmante_nombre,
    firmante_cargo,
    email_contacto,
    activo,
    modulos,
    created_at
) VALUES (
    '{{SLUG}}',                          -- ej: 'pampaformacion'
    '{{NOMBRE_CORTO}}',                  -- ej: 'Pampa Formación'
    '{{NOMBRE_COMPLETO}}',               -- ej: 'Instituto Pampa Formación S.A.'
    'es_AR',                             -- idioma por defecto
    'es_AR,pt_BR',                       -- idiomas habilitados
    '#1E40AF',                           -- color primario (hex)
    '#1E3A8A',                           -- color secundario (hex)
    '#3B82F6',                           -- color acento (hex)
    NULL,                                -- se completa después con el logo
    'rectangular',                       -- rectangular, circular, rectangular-rounded
    '{{NOMBRE_FIRMANTE}}',               -- ej: 'Juan Pérez'
    '{{CARGO_FIRMANTE}}',                -- ej: 'Director General'
    '{{EMAIL_CONTACTO}}',                -- ej: 'contacto@pampaformacion.com'
    1,                                   -- activo
    '{"certificatum":true,"scripta":false,"nexus":false}',
    NOW()
);
```

### 1.2 Obtener el ID de la instancia creada

```sql
SELECT id_instancia FROM instancias WHERE slug = '{{SLUG}}';
-- Anotar este ID para los siguientes pasos
```

### 1.3 Crear usuario administrador en `verumax_general.admins`

```sql
INSERT INTO admins (
    id_instancia,
    usuario,
    password_hash,
    nombre,
    email,
    activo,
    created_at
) VALUES (
    {{ID_INSTANCIA}},                    -- ID obtenido arriba
    '{{USUARIO_ADMIN}}',                 -- ej: 'admin_pampa'
    '$2y$10$...',                        -- hash generado con password_hash()
    '{{NOMBRE_ADMIN}}',                  -- ej: 'Administrador'
    '{{EMAIL_ADMIN}}',                   -- ej: 'admin@pampaformacion.com'
    1,
    NOW()
);
```

**Generar hash de contraseña:**
```php
<?php
echo password_hash('contraseña_temporal_123', PASSWORD_DEFAULT);
?>
```

---

## 2. Estructura de Carpetas

### 2.1 Copiar template

```bash
# En servidor, copiar la carpeta template
cp -r _template_institucion/ {{SLUG}}/

# O en Windows
xcopy _template_institucion {{SLUG}} /E /I
```

### 2.2 Reemplazar placeholders

En todos los archivos PHP de la carpeta, reemplazar:
- `{{SLUG}}` → slug de la institución (ej: `pampaformacion`)
- `{{NOMBRE_INSTITUCION}}` → nombre completo

**Archivos a modificar:**
- `creare.php`
- `creare_pdf.php`
- `cursus.php`
- `tabularium.php`
- `validare.php`
- `verificatio.php`

### 2.3 Crear subcarpetas necesarias

```bash
mkdir {{SLUG}}/assets
mkdir {{SLUG}}/assets/images
mkdir {{SLUG}}/certificatum
```

### 2.4 Crear archivos adicionales en /certificatum

Copiar de otra institución y modificar el slug:
- `certificatum/index.php`
- `certificatum/creare.php`
- `certificatum/cursus.php`
- `certificatum/tabularium.php`

---

## 3. Configuración de Hosting

### 3.1 Crear subdominio en cPanel/Plesk

1. Acceder al panel de hosting
2. Ir a "Subdominios" o "Subdomains"
3. Crear: `{{SLUG}}.verumax.com`
4. Document Root: `/public_html/{{SLUG}}`

### 3.2 Configurar SSL (Let's Encrypt)

1. Ir a "SSL/TLS" o "Let's Encrypt"
2. Emitir certificado para `{{SLUG}}.verumax.com`

### 3.3 Subir archivos

Subir toda la carpeta `{{SLUG}}/` a `/public_html/{{SLUG}}/`

---

## 4. Configuración de SendGrid

### 4.1 Crear Sender Identity

1. Ir a [SendGrid](https://app.sendgrid.com/) → Settings → Sender Authentication
2. Verificar dominio o crear Single Sender:
   - From Email: `noreply@{{SLUG}}.verumax.com` o `certificados@verumax.com`
   - From Name: `{{NOMBRE_CORTO}}`
   - Reply To: `{{EMAIL_CONTACTO}}`

### 4.2 Crear API Key (si no existe una global)

1. Settings → API Keys → Create API Key
2. Nombre: `VERUMax - {{NOMBRE_CORTO}}`
3. Permisos: "Restricted Access" → Mail Send: Full Access
4. Guardar la key en `config.php`

### 4.3 Configurar templates de email

En `verumax_general.email_templates`, insertar templates para la institución:

```sql
-- Template de bienvenida
INSERT INTO email_templates (
    id_instancia,
    tipo,
    asunto,
    cuerpo_html,
    activo
) VALUES (
    {{ID_INSTANCIA}},
    'bienvenida',
    'Bienvenido/a a {{NOMBRE_CORTO}}',
    '<html>...</html>',
    1
);

-- Template de certificado disponible
INSERT INTO email_templates (
    id_instancia,
    tipo,
    asunto,
    cuerpo_html,
    activo
) VALUES (
    {{ID_INSTANCIA}},
    'certificado_disponible',
    'Tu certificado está disponible - {{NOMBRE_CORTO}}',
    '<html>...</html>',
    1
);
```

### 4.4 Verificar configuración en config.php

```php
// En config.php verificar que existan:
define('SENDGRID_API_KEY', 'SG.xxxxx...');
define('SENDGRID_FROM_EMAIL', 'certificados@verumax.com');
define('SENDGRID_FROM_NAME', 'VERUMax Certificados');
```

---

## 5. Branding

### 5.1 Subir logo

1. Formato: PNG con fondo transparente
2. Tamaño recomendado: 400x100px (rectangular) o 200x200px (cuadrado)
3. Ubicación: `{{SLUG}}/assets/images/logo.png`
4. Actualizar BD:

```sql
UPDATE instancias
SET logo_url = '/{{SLUG}}/assets/images/logo.png'
WHERE slug = '{{SLUG}}';
```

### 5.2 Subir firma digital

1. Formato: PNG con fondo transparente
2. Tamaño: ~300x150px
3. Ubicación: `assets/images/firmas/{{SLUG}}_firma.png`

### 5.3 Configurar colores

Actualizar en BD si es necesario:

```sql
UPDATE instancias SET
    color_primario = '#1E40AF',
    color_secundario = '#1E3A8A',
    color_acento = '#3B82F6'
WHERE slug = '{{SLUG}}';
```

### 5.4 Personalizar style.css (opcional)

Crear `{{SLUG}}/style.css` con estilos específicos si necesario.

---

## 6. Templates de Certificados

### 6.1 Asignar templates globales

Los templates globales (Moderno, Minimalista, etc.) ya estarán disponibles automáticamente.

### 6.2 Crear template exclusivo (opcional)

Si la institución necesita un template propio:

1. Crear en Template Manager (`tools/template-manager/`)
2. Asignar institución en el campo correspondiente
3. Subir imagen de fondo si es necesario

```sql
INSERT INTO certificatum_templates (
    slug,
    nombre,
    descripcion,
    tipo_generador,
    orientacion,
    activo,
    institucion,
    orden
) VALUES (
    '{{SLUG}}-clasico',
    '{{NOMBRE_CORTO}} Clásico',
    'Template exclusivo de {{NOMBRE_CORTO}}',
    'mpdf',
    'landscape',
    1,
    '{{SLUG}}',
    10
);
```

---

## 7. Testing

### 7.1 Verificar acceso al admin

1. Ir a `https://{{SLUG}}.verumax.com/admin/`
2. Login con credenciales creadas
3. Verificar que carguen todos los módulos

### 7.2 Crear datos de prueba

1. Crear un estudiante de prueba
2. Crear un curso de prueba
3. Inscribir estudiante al curso
4. Generar certificado de prueba

### 7.3 URLs a probar

```
https://{{SLUG}}.verumax.com/                              # Landing
https://{{SLUG}}.verumax.com/admin/                        # Admin
https://{{SLUG}}.verumax.com/cursus.php?documentum=XXX     # Lista cursos
https://{{SLUG}}.verumax.com/creare.php?...                # Ver certificado
https://{{SLUG}}.verumax.com/validare.php?codigo=XXX      # Validar QR
```

---

## 8. Troubleshooting

### Error 404 en archivos PHP

- Verificar que los archivos proxy existen en la carpeta raíz
- Verificar que el slug en los archivos coincide con el de la BD

### "Institución no encontrada"

- Verificar que existe registro en `instancias` con el slug correcto
- Verificar que `activo = 1`

### Certificados no generan

- Verificar que existe al menos un template activo
- Verificar que el estudiante tiene inscripciones activas
- Revisar logs de PHP en el servidor

### Emails no llegan

- Verificar API key de SendGrid
- Verificar que el sender está verificado
- Revisar Activity en SendGrid para ver si hubo bounces

---

## Resumen de Archivos a Crear/Modificar

```
{{SLUG}}/
├── creare.php           ← Proxy generación documentos
├── creare_pdf.php       ← Proxy generación PDFs
├── cursus.php           ← Proxy lista de cursos
├── tabularium.php       ← Proxy trayectoria académica
├── validare.php         ← Proxy validación códigos
├── verificatio.php      ← Proxy vista pública
├── index.php            ← Landing page (opcional)
├── style.css            ← Estilos custom (opcional)
├── assets/
│   └── images/
│       └── logo.png     ← Logo institución
└── certificatum/
    ├── index.php        ← Redirect a cursus
    ├── creare.php       ← Proxy alternativo
    ├── cursus.php       ← Proxy alternativo
    └── tabularium.php   ← Proxy alternativo
```

---

**Última actualización:** Enero 2026
