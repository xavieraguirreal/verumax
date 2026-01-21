# Sistema Multi-idioma VERUMax - Estado de Implementación

**Última actualización**: 2025-12-11

## ✅ Arquitectura Implementada

### LanguageService (Núcleo del sistema)
- **Ubicación**: `src/VERUMax/Services/LanguageService.php`
- **Funcionalidades**:
  - Detección automática de idioma desde navegador
  - Cambio manual via `?lang=xx_XX`
  - Persistencia con cookies y sesión (30 días)
  - Carga de traducciones desde archivos PHP
  - Carga de traducciones de contenido desde BD (`instance_translations`)
  - Soporte para parámetros en traducciones

### Idiomas Disponibles
```php
'es_AR' => 'Español (Argentina)' 🇦🇷
'pt_BR' => 'Português (Brasil)' 🇧🇷
'en_US' => 'English (US)' 🇺🇸  // Preparado, no activo
```

---

## ✅ Módulos con Soporte Multi-idioma

### 1. Identitas (100%)
**Selector de idioma**: ✅ Dropdown en header con banderas

**Archivos de traducción**:
- `lang/es_AR/identitas.php` - Español Argentina
- `lang/pt_BR/identitas.php` - Português Brasil

**Elementos traducidos**:
- ✅ Menú de navegación (dinámico desde slugs de páginas)
- ✅ Botón Administración
- ✅ Hero section (bienvenida, subtítulo, CTAs)
- ✅ Sección Certificados (título, descripción, formulario, features)
- ✅ Sección Sobre Nosotros (título)
- ✅ Sección Servicios (título)
- ✅ Sección Contacto (título, formulario, mensajes)
- ✅ Footer (enlaces, servicios, derechos)
- ✅ Redes sociales (título)

**Bloques traducidos** (via BD + archivos):
- ✅ `intro_historia.php` - Título y texto
- ✅ `timeline_vertical.php` - Título
- ✅ `mision_vision.php` - Títulos Misión/Visión/Valores
- ✅ `mision_centrada.php` - Título
- ✅ `mision_con_stats.php` - Título
- ✅ `servicios_header.php` - Título
- ✅ `contacto_info.php` - Título

### 2. Certificatum (100%)
**Integración**: Recibe idioma via parámetro `&lang=` desde Identitas

**Archivos de traducción**:
- `lang/es_AR/certificatum.php` - Español Argentina
- `lang/pt_BR/certificatum.php` - Português Brasil

**Elementos traducidos**:
- ✅ Títulos de página
- ✅ Formulario de búsqueda (labels, placeholders, botones)
- ✅ Mensajes de error
- ✅ Tabla de cursos y certificados
- ✅ Tipos de documentos
- ✅ Competencias (dinámicas via clave generada)
- ✅ Botones de descarga
- ✅ Features (Verificables, 24/7, Descarga)
- ✅ Template integrado (`templates/integrado.php`)

**Páginas traducidas**:
- ✅ `cursus.php` - Selección de cursos
- ✅ `tabularium.php` - Lista de certificados
- ✅ `creare.php` - Visualización de certificado
- ✅ `verificatio.php` - Validación de certificado

### 3. Templates Compartidos (100%)
- ✅ `templates/shared/header.php` - Navegación y botón Admin
- ✅ Archivos comunes en `lang/*/common.php`

---

## 📦 Traducciones en Base de Datos

### Tabla: `verumax_general.instance_translations`
```sql
id_instancia | campo                      | idioma | contenido
-------------|----------------------------|--------|------------------
1            | mision                     | pt_BR  | A Sociedade Argentina...
1            | certificatum_cta_texto     | pt_BR  | Entrar com meu documento
1            | certificatum_descripcion   | pt_BR  | Acesse seus certificados...
1            | intro_historia_titulo      | pt_BR  | Nossa História
1            | intro_historia_texto       | pt_BR  | <p>A Sociedade Argentina...
1            | timeline_titulo            | pt_BR  | Evolução e Conquistas...
```

**Uso**: `LanguageService::getContent($idInstancia, $campo, $idioma, $fallback)`

---

## 🔄 Flujo de Traducciones

### Textos de interfaz (UI)
```
1. Usuario cambia idioma → Cookie guardada
2. LanguageService::init() detecta idioma
3. LanguageService::get('modulo.clave') busca en archivo PHP
4. Si no existe, retorna fallback
```

### Contenido dinámico (BD)
```
1. Bloque necesita contenido traducido
2. LanguageService::getContent($id, 'campo', null, $default)
3. Busca en instance_translations para idioma actual
4. Si no existe, retorna $default (contenido original)
```

---

## 📁 Estructura de Archivos de Idioma

```
lang/
├── es_AR/
│   ├── common.php      # Textos comunes (nav, footer, etc)
│   ├── identitas.php   # Textos de Identitas
│   └── certificatum.php # Textos de Certificatum
├── pt_BR/
│   ├── common.php
│   ├── identitas.php
│   └── certificatum.php
└── en_US/              # Preparado para futuro
    ├── common.php
    ├── identitas.php
    └── certificatum.php
```

---

## 🎯 Configuración por Instancia

### Tabla: `verumax_general.instances`
```sql
slug   | idioma_default | idiomas_habilitados
-------|----------------|--------------------
sajur  | es_AR          | es_AR,pt_BR
```

---

## ⚠️ Contenido NO Traducido (requiere BD)

Los siguientes contenidos están en español en la BD y necesitan traducciones manuales:

1. **Eventos del Timeline** - JSON en `identitas_contenido_bloques`
2. **Servicios individuales** - JSON en bloques
3. **Equipo/Miembros** - JSON en bloques
4. **Publicaciones** - JSON en bloques
5. **Áreas de investigación** - JSON en bloques

Para traducir, agregar registros en `instance_translations` con formato:
- `bloque_{tipo}_{campo}` para campos específicos de bloques

---

## 🚀 Próximos Pasos Sugeridos

### Prioridad Alta
- [ ] Agregar inglés (en_US) para expansión internacional
- [ ] Crear interfaz admin para gestionar traducciones

### Prioridad Media
- [ ] Traducir contenido de bloques de Identitas
- [ ] Agregar traducciones para módulo Admin

### Prioridad Baja
- [ ] Sistema de traducciones automáticas via API
- [ ] Exportar/importar traducciones (CSV/JSON)

---

## 📝 Cómo Agregar Nuevo Idioma

1. Crear carpeta `lang/{codigo}/`
2. Copiar archivos de `lang/es_AR/` como base
3. Traducir cada clave
4. Agregar idioma a `LanguageService::AVAILABLE_LANGUAGES`
5. Agregar a `idiomas_habilitados` de cada instancia en BD
6. Agregar traducciones de contenido en `instance_translations`

---

## ✅ Historial de Cambios

### 2025-12-11
- Implementado sistema completo en Identitas
- Implementado sistema completo en Certificatum
- Creados archivos es_AR y pt_BR para ambos módulos
- Agregadas traducciones de contenido en BD para SAJuR
- Corregido encoding UTF-8 en traducciones
- Modificado TemplateService para pasar idInstancia a bloques

### 2025-10-07
- Implementación inicial en index.php (landing page)
- Creados archivos es_AR, pt_BR, el_GR para landing
