# Plan: Credencialis - Landing Page

## Estado General
**Última actualización:** 2026-01-28

> **Nota:** El plan del módulo admin está en `PLAN_IMPLEMENTACION.md`

---

## Archivos de Idioma
- [x] `lang/es_AR/land_credencialis.php` - Español Argentina (base)
- [x] `lang/pt_BR/land_credencialis.php` - Portugués Brasil

---

## Landing Page

### Archivo Principal
- [x] `credencialis/landing.php` - Landing page de marketing (color Teal #0891b2)
- [x] `credencialis/index.php` - Pure landing (incluye landing.php, sin lógica de institutio)
- [x] `sajur/credencialis/index.php` - Proxy de institución (usa templates/solo.php)

### Secciones Implementadas
- [x] Header con navegación y selector de idioma (ES/PT)
- [x] Hero section con propuesta de valor
- [x] Sección "Problemas que resuelve" (6 problemas)
- [x] Sección "Cómo funciona" (3 pasos simples)
- [x] Sección "Tipos de organizaciones" (8 tipos)
- [x] Sección "Funcionalidades" (6 features)
- [x] Sección "Beneficios" (4 beneficios)
- [x] Sección "Casos de éxito" (SAJuR)
- [x] Sección "FAQ" (6 preguntas)
- [x] Sección "Planes y precios" (3 planes)
- [x] Sección "Contacto/Demo" (formulario)
- [x] Footer

---

## Recursos Adicionales
- [ ] Imagen OG para redes sociales (`og-image-credencialis.png`)

---

## Referencia de Diseño
- Seguir estructura de `certificatum/index.php`
- Usar Tailwind CSS
- Colores institucionales dinámicos
- Responsive design

---

## Verificación
1. Acceder a `https://verumax.com/credencialis/`
2. Probar selector de idioma (ES/PT)
3. Verificar todas las secciones
4. Probar formulario de contacto
5. Verificar responsive en móvil
