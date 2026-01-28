# Plan: Nueva Landing Principal de Verumax

**Fecha:** 2026-01-28
**Estado:** En planificación

---

## Resumen

Rediseño completo de la landing principal de Verumax con enfoque en **filosofía de programación** (origen en C, pasión por código, conocimiento profundo) y **banners por solución** que derivan a sub-landings específicas.

---

## Estrategia de Desarrollo

| Archivo | Propósito | Estado |
|---------|-----------|--------|
| `maintenance.php` | Página actual visible en verumax.com | ✅ Mantener activo |
| `landing.php` | Nueva landing en desarrollo | 🔨 **CREAR** |
| `index.php` | Landing actual (backup) | 📦 Mantener como referencia |
| `desarrollo/landing.php` | Landing anti-CMS (sitios web) | 🔨 **CREAR** |

**Flujo:**
1. Desarrollar en `landing.php`
2. Probar accediendo a `verumax.com/landing.php`
3. Cuando esté lista, renombrar a `index.php`

---

## Estructura de la Nueva Landing (`landing.php`)

### 1. HERO - Filosofía de Código
**Mensaje central:** "Del lenguaje C a la nube"

Contenido:
- "Nacimos programando en C. Conocemos el código desde adentro."
- "Programar no es nuestro trabajo, es nuestra pasión."
- Mencionar lenguajes: PHP, Python, JavaScript, Go, Rust
- Animación sutil de código o terminal

### 2. SECCIÓN - Por qué Código Propio
Cards con ventajas:
- Sin límites de plantillas
- Escalabilidad real
- Seguridad controlada
- Propiedad total del código
- "Sabemos cómo están programados los CMS por dentro"

### 3. BANNERS DE SOLUCIONES (4-5 banners rotativos o grid)

| # | Banner | Título | Gancho | Destino |
|---|--------|--------|--------|---------|
| 1 | **Certificatum** | Certificados Digitales | "Diplomas verificables con QR infalsificable" | `/certificatum/` |
| 2 | **Credencialis** | Credenciales de Membresía | "Carnets digitales para tu organización" | `/credencialis/` |
| 3 | **Desarrollo Web** | Sitios Sin Límites | "¿Aún usás WordPress? Nosotros programamos desde cero" | `/desarrollo/` |
| 4 | **Hosting** | Hosting Optimizado | "Servidores configurados por desarrolladores" | `/hosting/` (futuro) |
| 5 | **LMS/Educación** | Plataformas Educativas | "Tu aula virtual a medida" | `/edumax/` (futuro) |

### 4. SECCIÓN - Tecnologías que Dominamos
Grid visual con logos/iconos:
- **Lenguajes:** C, PHP, Python, JavaScript, TypeScript, Go
- **Frameworks:** Laravel, React, Vue, Node.js
- **Bases de datos:** MySQL, PostgreSQL, MongoDB
- **Cloud:** AWS, Google Cloud, DigitalOcean

### 5. SECCIÓN - Casos de Éxito
Reutilizar casos existentes (SAJuR, Liberté, etc.)

### 6. SECCIÓN - Contacto/CTA
Formulario de contacto + WhatsApp

---

## Landing Anti-CMS (`desarrollo/landing.php`)

### Hero
**Título:** "¿Tu sitio web tiene techo?"
**Subtítulo:** "WordPress, Joomla, Wix... todos tienen límites. Nosotros no."

### Secciones:
1. **Problema:** Limitaciones de los CMS (plugins, velocidad, seguridad, dependencia)
2. **Solución:** Desarrollo a medida desde cero
3. **Servicios:**
   - Sitios web institucionales
   - Plataformas educativas (LMS)
   - E-commerce a medida
   - Aplicaciones web
4. **Comparativa:** CMS vs Código Propio (tabla)
5. **Proceso de trabajo:** Cómo desarrollamos
6. **CTA:** Contacto/presupuesto

---

## Archivos a Crear/Modificar

### Crear:
- [ ] `landing.php` - Nueva landing principal
- [ ] `lang/es_AR/land_verumax.php` - Traducciones nueva landing
- [ ] `lang/pt_BR/land_verumax.php` - Traducciones PT-BR
- [ ] `desarrollo/index.php` - Router para landing desarrollo
- [ ] `desarrollo/landing.php` - Landing anti-CMS
- [ ] `lang/es_AR/land_desarrollo.php` - Traducciones desarrollo
- [ ] `lang/pt_BR/land_desarrollo.php` - Traducciones PT-BR

### Mantener sin cambios:
- `maintenance.php` (sigue siendo la página pública)
- `index.php` (backup/referencia)

---

## Textos Clave (Slogans)

### Filosofía:
- "Nacimos en el mundo del lenguaje C"
- "Programar no es nuestro trabajo, es nuestra pasión"
- "Conocemos el código desde adentro del capó"
- "Donde otros ponen plugins, nosotros escribimos código"
- "Del código C a la nube"

### Anti-CMS:
- "¿Aún tenés tu sitio en WordPress?"
- "Con nosotros podés tener eso y mucho más"
- "Programamos desde cero, sin límites"
- "¿Sabías que si usás un CMS tenés techo?"
- "No por ser código propio es más costoso"
- "Tu negocio es único. Tu software también debería serlo"

---

## Paleta de Colores Propuesta

Mantener la actual de Verumax:
- **Dorado:** #D4AF37 (principal)
- **Negro:** #0a0a0a (fondo)
- **Verde metálico:** #2E7D32 (acento)

Para sección de código/terminal:
- **Verde terminal:** #00ff00 o #4ade80
- **Fondo terminal:** #1a1a2e

---

## Verificación

1. [ ] Acceder a `verumax.com/landing.php` para ver la nueva landing
2. [ ] Probar responsive (mobile, tablet, desktop)
3. [ ] Verificar que los banners llevan a las sub-landings correctas
4. [ ] Probar cambio de idioma (es_AR ↔ pt_BR)
5. [ ] Cuando esté aprobada: renombrar `landing.php` → `index.php`

---

## Orden de Implementación

| Fase | Descripción | Estado |
|------|-------------|--------|
| 1 | Crear `landing.php` con hero + sección filosofía | ⬜ Pendiente |
| 2 | Agregar banners de soluciones | ⬜ Pendiente |
| 3 | Crear archivos de traducción (es_AR, pt_BR) | ⬜ Pendiente |
| 4 | Crear `desarrollo/landing.php` (landing anti-CMS) | ⬜ Pendiente |
| 5 | Pulir diseño y animaciones | ⬜ Pendiente |
| 6 | Revisar y aprobar → Reemplazar index.php | ⬜ Pendiente |

---

## Notas Adicionales

- La landing actual `index.php` tiene 1,923 líneas y buen SEO, podemos reutilizar la estructura base
- Sistema de caché ya implementado (1 hora)
- Multi-idioma ya funciona con 14 idiomas
- Credencialis casi lista, Certificatum ya existe
