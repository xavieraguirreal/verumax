# INFORME COMPLETO: Revisión de Traducciones es_CL vs es_AR
## Proyecto Verumax - Fecha: 20 de diciembre de 2025

---

## 📊 RESUMEN EJECUTIVO

### Estadísticas Generales
- **Total de claves en es_AR.php**: ~1,141 líneas
- **Total de claves en es_CL.php**: ~1,009 líneas
- **Diferencia estimada**: ~132 líneas (claves faltantes en chileno)

### Estructura de Carpetas
- ✅ **es_AR/** existe con 3 archivos:
  - `common.php`
  - `identitas.php`
  - `certificatum.php`
- ❌ **es_CL/** NO EXISTE - FALTA CREAR TODA LA ESTRUCTURA

---

## 🔴 PROBLEMA CRÍTICO 1: Sección "Equipo Humano" FALTANTE

### Claves Faltantes Completamente en es_CL.php:
```php
'equipo_titulo' => 'Pensado para Vos',
'equipo_subtitulo' => 'Sin Conocimientos Técnicos Requeridos',
'equipo_card1_titulo' => 'Equipo Humano Real',
'equipo_card1_desc' => 'No te dejamos solo con un software...',
'equipo_card2_titulo' => 'Dashboard Moderno Incluido',
'equipo_card2_desc' => 'Si tenés conocimientos técnicos...',
'equipo_card3_titulo' => 'Soporte 24/7',
'equipo_card3_desc' => 'Tenés acceso directo a personas reales...',
```

### Adaptación Requerida para Chile:
```php
'equipo_titulo' => 'Pensado para Usted',  // Formal chileno
'equipo_subtitulo' => 'Sin Conocimientos Técnicos Requeridos',
'equipo_card1_titulo' => 'Equipo Humano Real',
'equipo_card1_desc' => 'No lo dejamos solo con un software. Nuestro equipo humano se encarga de TODO: configuración, diseño, carga de información y soporte permanente. Usted solo envía su información y nosotros hacemos el resto.',
'equipo_card2_titulo' => 'Dashboard Moderno Incluido',
'equipo_card2_desc' => 'Si tiene conocimientos técnicos o quiere aprender, cuenta con un dashboard intuitivo, moderno y seguro para gestionar todo usted mismo. Pero siempre puede pedirnos ayuda cuando lo necesite.',
'equipo_card3_titulo' => 'Soporte 24/7',
'equipo_card3_desc' => 'Tiene acceso directo a personas reales que lo ayudan cuando lo necesita. Ya sea por dashboard o escribiéndonos directamente, siempre tendrá respuesta rápida y humana.',
```

**Cambios clave**:
- `vos` → `usted`
- `te` → `lo`
- `tenés` → `tiene`
- `querés` → `quiere`
- `contás` → `cuenta`
- `podés` → `puede`
- `enviás` → `envía`
- `necesitás` → `necesita`
- `vas a tener` → `tendrá`

---

## 🔴 PROBLEMA CRÍTICO 2: Sección "Veritas IA" FALTANTE

### Claves Faltantes:
```php
'veritas_chat_btn' => 'Chat con Veritas IA',
'veritas_titulo' => 'Veritas IA',
'veritas_subtitulo' => 'Nuestro Agente de Inteligencia Artificial Especializado',
'veritas_proximamente' => '¡Próximamente!',
'veritas_descripcion' => 'Veritas estará disponible muy pronto para ayudarte con consultas sobre certificados, validaciones y más.',
'veritas_entendido' => 'Entendido',
```

### Adaptación para Chile:
```php
'veritas_chat_btn' => 'Chat con Veritas IA',
'veritas_titulo' => 'Veritas IA',
'veritas_subtitulo' => 'Nuestro Agente de Inteligencia Artificial Especializado',
'veritas_proximamente' => '¡Próximamente!',
'veritas_descripcion' => 'Veritas estará disponible muy pronto para ayudarlo con consultas sobre certificados, validaciones y más.',
'veritas_entendido' => 'Entendido',
```

**Cambio**: `ayudarte` → `ayudarlo`

---

## 🟡 PROBLEMA CRÍTICO 3: Sección Académica - Variantes faltantes

### Claves de alternativas/delegación faltantes:

```php
// Paso 2 alternativa (delegación)
'acad_como_funciona_paso2_alt' => 'O simplemente envíenos sus listados y nuestro equipo los importa por usted.',

// Paso 3 alternativa (delegación)
'acad_como_funciona_paso3_alt' => 'También puede solicitar la emisión a nuestro equipo y solo encargarse de compartir.',
```

### Elementos de ROI y promo faltantes:
```php
'acad_roi_titulo' => 'Retorno de Inversión Comprobado',
'acad_roi_subtitulo' => 'Resultados reales de instituciones que ya usan Certificatum',
'acad_roi_ahorro_impresion' => 'Ahorro mensual en impresión',
'acad_roi_ahorro_tiempo' => 'Ahorro en tiempo administrativo',
'acad_roi_reduccion_consultas' => 'Reducción de consultas telefónicas',
'acad_roi_payback' => 'Tiempo de Payback',
'acad_roi_stat_impresion' => '$$$',
'acad_roi_stat_tiempo' => '40hs',
'acad_roi_stat_consultas' => '80%',
'acad_roi_stat_payback' => '2 meses',

// Promo / Concierge
'acad_concierge_delegar' => '¿Prefiere delegar? Nuestro equipo se encarga de la configuración por usted.',
'acad_promo_titulo' => 'PROMO LANZAMIENTO',
'acad_promo_alta' => 'Alta:',
'acad_promo_alta_bonificada' => 'Alta Bonificada:',
'acad_promo_ahorro' => '(Ahorrás',
'acad_descuento_banner' => 'DE DESCUENTO en planes - Solo por tiempo limitado',
'acad_plan_sin_suscripcion' => 'SIN SUSCRIPCIÓN',
```

### Adaptación Chile:
- `Ahorrás` → `Ahorra`
- Los demás textos se mantienen similares con pequeños ajustes de formalidad

---

## 🟡 PROBLEMA 4: Panel de Administración y CRUD

### Faltantes:
```php
'acad_panel_titulo' => 'Panel de Administración Completo',
'acad_panel_subtitulo' => 'Todo lo que necesita para gestionar su institución',
'acad_panel_carga_masiva' => 'Carga masiva desde Excel/CSV',
'acad_panel_crud' => 'CRUD completo de estudiantes y cursos',
'acad_panel_dashboard' => 'Dashboard con analytics en tiempo real',
'acad_panel_multiusuario' => 'Gestión multi-usuario con roles',
```

---

## 🟢 DIFERENCIAS EN VALORES EXISTENTES (Revisión de Coherencia)

### 1. **Voseo vs Ustedeo**

| Clave | es_AR (Argentino) | es_CL (Chileno) | ✅/❌ |
|-------|-------------------|----------------|-------|
| `meta_title` | "...tu Presencia Digital" | "...tu Prestigio Digital" | ⚠️ Cambio semántico |
| `meta_description` | "...presencia digital verificada..." | "...marca personal verificada..." | ⚠️ Cambio semántico |
| `nav_ecosistema` | "...a tu Disposición" | "...a su Disposición" | ✅ Correcto |
| `nav_rapido_titulo` | "¿Qué estás buscando?" | "¿Qué está buscando?" | ✅ Correcto |
| `hero_cta_primary` | "¿Qué estás buscando?" | "Explorar Soluciones" | ⚠️ Cambio funcional |
| `cat_subtitle` | "Elegí tu sector..." | "Elija su sector..." | ✅ Correcto |

### 2. **DNI vs RUT**

| Clave | es_AR | es_CL | ✅/❌ |
|-------|-------|-------|-------|
| `hero_mockup_estudiante` | "Juan Pérez - DNI 12345678" | "Juan Pérez - RUT 12.345.678" | ✅ Correcto |
| `acad_portal_demo_dni` | "DNI: 12.345.678" | "RUT: 12.345.678" | ✅ Correcto |

### 3. **Landing Page vs Sitio Web**

| Clave | es_AR | es_CL | ✅/❌ |
|-------|-------|-------|-------|
| `cat_tarjeta_digital_2` | "Landing Page Incluida" | "Sitio Web Incluido" | ⚠️ Variación |
| `ecosol_tarjeta_digital_desc` | "...con landing page personalizada" | "...con sitio web personalizado" | ⚠️ Variación |
| `ecosol_landing_personales` | "Landing Pages Personales" | "Sitios Web Personales" | ⚠️ Variación |

**OBSERVACIÓN**: "Landing Page" es un anglicismo ampliamente usado en marketing digital en ambos países. El cambio a "Sitio Web" puede ser válido para formalidad chilena, pero "Landing Page" también es correcto y profesional.

### 4. **Backup vs Respaldo**

| Clave | es_AR | es_CL | ✅/❌ |
|-------|-------|-------|-------|
| `badge_backup` | "Backup" | "Respaldo" | ✅ Mejor (más formal) |
| `badge_backup_desc` | "Automático Diario" | "Automático Diario" | ✅ Correcto |

### 5. **Formalidad en Preguntas**

| Clave | es_AR | es_CL | ✅/❌ |
|-------|-------|-------|-------|
| `faq_subtitle` | "Todo lo que necesitas saber..." | "Todo lo que usted necesita saber..." | ✅ Correcto |
| `faq_1_q` | "¿Cuánto tiempo lleva...?" | "¿Cuánto tiempo toma...?" | ⚠️ Cambio regional |

**NOTA**: "Lleva" (AR) y "Toma" (CL) son ambos correctos. "Toma" es más común en Chile.

### 6. **Consultas vs Contáctenos**

| Clave | es_AR | es_CL | ✅/❌ |
|-------|-------|-------|-------|
| `cat_footer_link` | "Consultanos" | "Contáctenos" | ✅ Correcto (formal) |
| `contacto_mensaje` | "Contanos sobre tu necesidad..." | "Cuéntenos sobre su necesidad..." | ✅ Correcto |

### 7. **Materia vs Asignatura**

| Clave | es_AR | es_CL | ✅/❌ |
|-------|-------|-------|-------|
| `acad_doc_certificado_aprobacion_desc` | "...curso, materia o programa..." | "...curso, asignatura o programa..." | ✅ Correcto chileno |
| `acad_doc_analiticos_desc` | "...con todas las materias cursadas..." | "...con todas las asignaturas cursadas..." | ✅ Correcto chileno |

### 8. **Estudiantes vs Alumnos**

| Clave | es_AR | es_CL | ✅/❌ |
|-------|-------|-------|-------|
| `acad_problema_perdidos_desc` | "Estudiantes que pierden..." | "Alumnos que pierden..." | ⚠️ Ambos válidos |
| `acad_func_emision_masiva_desc` | "...certificados para 100+ estudiantes..." | "...certificados para 100+ alumnos..." | ⚠️ Cambio regional |

**NOTA**: En Chile se usa tanto "estudiante" (más moderno/universitario) como "alumno" (más tradicional/escolar). El archivo chileno usa "alumnos" en algunos contextos, lo cual es válido pero menos inclusivo que "estudiantes".

### 9. **Empleadores/as vs Empleadores**

| Clave | es_AR | es_CL | ✅/❌ |
|-------|-------|-------|-------|
| `acad_func_validacion_desc` | "Empleadores/as escanean..." | "Empleadores escanean..." | ⚠️ Lenguaje inclusivo |

**NOTA**: Argentina tiene una mayor adopción de lenguaje inclusivo en documentos institucionales. Chile está en proceso de incorporación, pero formalmente se usa menos la doble forma.

### 10. **Privacidad: Autoridad de Aplicación**

| Clave | es_AR | es_CL | ✅/❌ |
|-------|-------|-------|-------|
| `privacidad_seccion12_p1_strong` | "Agencia de Acceso a la Información Pública (AAIP)" | "Consejo para la Transparencia" | ✅ Correcto |
| `privacidad_seccion12_aaip_url` | "www.argentina.gob.ar/aaip" | "www.consejotransparencia.cl" | ✅ Correcto |

---

## 📂 ESTRUCTURA DE CARPETAS: FALTA COMPLETAMENTE

### Estado Actual:
```
lang/
├── es_AR/
│   ├── common.php        ✅ EXISTE
│   ├── identitas.php     ✅ EXISTE
│   └── certificatum.php  ✅ EXISTE
│
└── es_CL/                ❌ NO EXISTE
```

### Estructura Recomendada a Crear:
```
lang/
├── es_AR/
│   ├── common.php
│   ├── identitas.php
│   └── certificatum.php
│
└── es_CL/
    ├── common.php        ❌ CREAR (basado en es_AR/common.php)
    ├── identitas.php     ❌ CREAR (basado en es_AR/identitas.php)
    └── certificatum.php  ❌ CREAR (basado en es_AR/certificatum.php)
```

### Contenido de lang/es_AR/common.php (para adaptar):
```php
<?php
return [
    '_locale' => 'es_AR',
    '_name' => 'Español (Argentina)',
    '_flag' => '🇦🇷',
    '_flag_icon' => 'ar',

    // Navegación
    'nav_home' => 'Inicio',
    'nav_about' => 'Sobre Nosotros',
    // ... (71 líneas)
];
```

### Adaptación Chilena Requerida:
```php
<?php
return [
    '_locale' => 'es_CL',
    '_name' => 'Español (Chile)',
    '_flag' => '🇨🇱',
    '_flag_icon' => 'cl',

    // Navegación (igual)
    'nav_home' => 'Inicio',
    'nav_about' => 'Sobre Nosotros',

    // Validación
    'validation_dni_placeholder' => 'Ejemplo: 12345678-9',  // ⚠️ CAMBIAR formato RUT
    'validation_dni_help' => 'Sin puntos, con guión antes del dígito verificador',  // ⚠️ ADAPTAR

    // Footer
    'footer_follow_us' => 'Síganos',  // ⚠️ CAMBIAR (voseo → ustedeo)
];
```

### Contenido de lang/es_AR/identitas.php (para adaptar):
```php
<?php
return [
    // Certificados - formulario integrado
    'certificates_search_label' => 'Número de Documento',
    'certificates_search_placeholder' => 'Ingresá tu documento (solo números)',  // ⚠️ VOSEO
    'certificates_search_button' => 'Ver mis certificados',
    'certificates_search_help' => 'Ingresá tu documento de identidad sin puntos ni espacios',  // ⚠️ VOSEO
];
```

### Adaptación Chilena:
```php
<?php
return [
    'certificates_search_label' => 'Número de RUT',  // ⚠️ CAMBIAR
    'certificates_search_placeholder' => 'Ingrese su RUT (ej: 12345678-9)',  // ⚠️ USTEDEO + formato RUT
    'certificates_search_button' => 'Ver mis certificados',
    'certificates_search_help' => 'Ingrese su RUT sin puntos, con guión antes del dígito verificador',  // ⚠️ USTEDEO + instrucciones RUT
];
```

### Contenido de lang/es_AR/certificatum.php (para adaptar):
```php
<?php
return [
    'search_title' => 'Número de DNI',  // ⚠️ CAMBIAR
    'search_button' => 'Ver mis certificados',
    'dni_label' => 'D.N.I. N°',  // ⚠️ CAMBIAR
    'dni_short' => 'DNI',  // ⚠️ CAMBIAR
];
```

### Adaptación Chilena:
```php
<?php
return [
    'search_title' => 'Número de RUT',  // ⚠️ CAMBIAR
    'search_button' => 'Ver mis certificados',
    'rut_label' => 'RUT N°',  // ⚠️ NUEVO
    'rut_short' => 'RUT',  // ⚠️ NUEVO
];
```

---

## 🔍 CLAVES FALTANTES CONFIRMADAS (Lista Parcial)

### Sección Equipo Humano (7 claves):
```
equipo_titulo
equipo_subtitulo
equipo_card1_titulo
equipo_card1_desc
equipo_card2_titulo
equipo_card2_desc
equipo_card3_titulo
equipo_card3_desc
```

### Sección Veritas IA (6 claves):
```
veritas_chat_btn
veritas_titulo
veritas_subtitulo
veritas_proximamente
veritas_descripcion
veritas_entendido
```

### Sección Académica ROI (10+ claves):
```
acad_roi_titulo
acad_roi_subtitulo
acad_roi_ahorro_impresion
acad_roi_ahorro_tiempo
acad_roi_reduccion_consultas
acad_roi_payback
acad_roi_stat_impresion
acad_roi_stat_tiempo
acad_roi_stat_consultas
acad_roi_stat_payback
```

### Sección Panel de Administración (4 claves):
```
acad_panel_titulo
acad_panel_subtitulo
acad_panel_carga_masiva
acad_panel_crud
acad_panel_dashboard
acad_panel_multiusuario
```

### Sección Delegación/Alternativas (3+ claves):
```
acad_como_funciona_paso2_alt
acad_como_funciona_paso3_alt
acad_concierge_delegar
```

### Sección Promo (5 claves):
```
acad_promo_titulo
acad_promo_alta
acad_promo_alta_bonificada
acad_promo_ahorro
acad_descuento_banner
acad_plan_sin_suscripcion
```

### Sección CTA Final Académico (diferencias):
```php
// AR tiene:
'acad_cta_final_equipo' => 'Nuestro equipo de expertos y expertas...',
'acad_cta_final_implementacion' => '48hs',
'acad_cta_final_implementacion_desc' => 'Tiempo de implementación',
'acad_cta_final_menos_tiempo' => 'Menos tiempo administrativo',
'acad_cta_final_certificados' => 'Certificados ilimitados',

// CL solo tiene:
'acad_cta_final_implementacion' => 'Implementación',
'acad_cta_final_menos_tiempo' => 'Menos tiempo',
'acad_cta_final_certificados' => 'Certificados',
```

### Sección Planes Académicos (diferencias):
```php
// AR tiene plan "Singularis" completo (pago por certificado) - FALTA EN CL
'acad_plan_singularis_titulo'
'acad_plan_singularis_desc'
'acad_plan_singularis_precio_label'
'acad_plan_singularis_feat1' hasta 'feat5'
'acad_plan_singularis_cta'

// AR tiene "Essentialis" con más features detalladas
'acad_plan_essentialis_feat1' => '10 Certificados Digitales/mes',
// ... hasta feat10

// CL tiene menos detalles en algunos planes
```

### Sección FAQ Académico (diferencias):
```php
// AR tiene:
'acad_faq_tecnico_resp' => '...nuestro <strong>Servicio Concierge</strong>...',

// CL no menciona "Servicio Concierge"
```

### Sección Pagos (diferencias regionales):
```php
// AR:
'prof_pagos_argentina' => 'Métodos de Pago en Argentina',
'prof_pago_transferencia_desc' => 'CBU/CVU para pesos argentinos',

// CL:
'prof_pagos_argentina' => 'Métodos de Pago en Latinoamérica',  // ⚠️ Genérico
'prof_pago_transferencia_desc' => 'Transferencia electrónica según país',  // ⚠️ Genérico
```

---

## 📝 RECOMENDACIONES DE ACCIÓN

### PRIORIDAD CRÍTICA (Hacer Ya):

1. **Crear estructura es_CL/**
   ```
   mkdir E:\appVerumax\lang\es_CL
   ```

2. **Crear es_CL/common.php**
   - Copiar de es_AR/common.php
   - Adaptar:
     - `_locale` → `es_CL`
     - `_name` → `Español (Chile)`
     - `_flag` → `🇨🇱`
     - `_flag_icon` → `cl`
     - `validation_dni_*` → `validation_rut_*` (formato RUT chileno)
     - `footer_follow_us` → `Síganos` (ustedeo)

3. **Crear es_CL/identitas.php**
   - Copiar de es_AR/identitas.php
   - Adaptar:
     - Todos los `Ingresá` → `Ingrese`
     - Todos los `tu` → `su`
     - `DNI` → `RUT`
     - Ejemplos de validación a formato RUT

4. **Crear es_CL/certificatum.php**
   - Copiar de es_AR/certificatum.php
   - Adaptar:
     - `DNI` → `RUT`
     - Todos los términos argentinos a chilenos

5. **Agregar sección "Equipo Humano" a es_CL.php**
   - Copiar de es_AR.php las 7+ claves
   - Convertir TODO el voseo a ustedeo formal

6. **Agregar sección "Veritas IA" a es_CL.php**
   - Copiar de es_AR.php las 6 claves
   - Cambiar `ayudarte` → `ayudarlo`

7. **Agregar sección ROI Académico a es_CL.php**
   - Copiar las 10+ claves de es_AR.php
   - Cambiar `Ahorrás` → `Ahorra`

8. **Agregar sección Panel Admin a es_CL.php**
   - Copiar las 4 claves de es_AR.php

9. **Agregar sección Delegación/Alternativas a es_CL.php**
   - Copiar las 3+ claves
   - Mantener ustedeo

10. **Agregar sección Promo a es_CL.php**
    - Copiar las 6 claves
    - Cambiar `Ahorrás` → `Ahorra`

### PRIORIDAD MEDIA (Próxima Semana):

11. **Revisar todos los CTA para coherencia**
    - Verificar que mantengan tono formal chileno
    - Asegurar que no haya voseo residual

12. **Completar plan "Singularis" en es_CL.php**
    - Agregar todas las claves del plan de pago por certificado

13. **Revisar diferencias en planes**
    - Asegurar que todos los planes tengan el mismo nivel de detalle

14. **Adaptar sección de pagos específicamente para Chile**
    - Cambiar "Argentina" → "Chile" donde corresponda
    - Agregar métodos de pago chilenos si los hay

### PRIORIDAD BAJA (Mantenimiento Continuo):

15. **Normalizar "Landing Page" vs "Sitio Web"**
    - Decidir: ¿usar anglicismo o traducción?
    - Aplicar consistentemente

16. **Revisar uso de "estudiantes" vs "alumnos"**
    - Preferir "estudiantes" (más inclusivo)

17. **Revisar lenguaje inclusivo**
    - Decidir política de uso de `/as` y `@`
    - Chile es menos formal que AR en esto actualmente

18. **Sincronizar actualizaciones futuras**
    - Cuando se agregue una clave nueva a es_AR.php
    - SIEMPRE agregarla también a es_CL.php con adaptaciones

---

## ✅ VALIDACIONES CORRECTAS ENCONTRADAS

### Muy Bien Adaptado:
1. ✅ DNI → RUT en ejemplos
2. ✅ Formato de RUT con puntos (12.345.678)
3. ✅ Voseo → Ustedeo en navegación principal
4. ✅ Autoridad de protección de datos (Consejo para la Transparencia)
5. ✅ "Backup" → "Respaldo" (más formal)
6. ✅ "Consultanos" → "Contáctenos" (formal)
7. ✅ "Contanos" → "Cuéntenos" (formal)
8. ✅ "Materia" → "Asignatura" (término educativo chileno)
9. ✅ Formalidad en preguntas FAQ
10. ✅ URLs de gobierno actualizadas (.cl)

---

## 🔧 SCRIPT DE AUTOMATIZACIÓN SUGERIDO

Crear archivo: `E:\appVerumax\scripts\sync_translations_cl.php`

```php
<?php
/**
 * Script para sincronizar claves faltantes de es_AR a es_CL
 * con conversión automática de voseo a ustedeo
 */

$ar = include __DIR__ . '/../lang/es_AR.php';
$cl = include __DIR__ . '/../lang/es_CL.php';

$missing = array_diff(array_keys($ar), array_keys($cl));

echo "Claves faltantes: " . count($missing) . "\n\n";
echo "<?php\n\n";
echo "// Agregar estas claves a lang/es_CL.php:\n\n";

foreach ($missing as $key) {
    $value = $ar[$key];

    // Conversión automática básica de voseo a ustedeo
    $value = str_replace('vos ', 'usted ', $value);
    $value = str_replace(' vos', ' usted', $value);
    $value = preg_replace('/\bte\b/', 'lo', $value);  // te → lo (masculino genérico)
    $value = preg_replace('/\btenés\b/', 'tiene', $value);
    $value = preg_replace('/\bpodés\b/', 'puede', $value);
    $value = preg_replace('/\bquerés\b/', 'quiere', $value);
    $value = preg_replace('/\benviás\b/', 'envía', $value);
    $value = preg_replace('/\bestás\b/', 'está', $value);
    $value = preg_replace('/\bsos\b/', 'es', $value);

    // Conversión básica de tuteo a ustedeo
    $value = preg_replace('/\btú\b/', 'usted', $value);
    $value = preg_replace('/\btienes\b/', 'tiene', $value);
    $value = preg_replace('/\bpuedes\b/', 'puede', $value);
    $value = preg_replace('/\bquieres\b/', 'quiere', $value);

    // DNI → RUT
    $value = str_replace('DNI', 'RUT', $value);
    $value = str_replace('D.N.I.', 'RUT', $value);

    echo "    '" . $key . "' => " . var_export($value, true) . ",\n";
}

echo "\n// FIN\n";
```

---

## 📋 CHECKLIST FINAL

### Para completar es_CL.php:

- [ ] Agregar 7+ claves de "Equipo Humano" (convertidas a ustedeo)
- [ ] Agregar 6 claves de "Veritas IA"
- [ ] Agregar 10+ claves de ROI Académico
- [ ] Agregar 4 claves de Panel Admin
- [ ] Agregar 3+ claves de Delegación/Alternativas
- [ ] Agregar 6 claves de Promo
- [ ] Completar plan "Singularis"
- [ ] Revisar y completar diferencias en CTA Final
- [ ] Normalizar todos los planes académicos

### Para crear estructura es_CL/:

- [ ] Crear carpeta `lang/es_CL/`
- [ ] Crear `es_CL/common.php` (adaptado de es_AR)
- [ ] Crear `es_CL/identitas.php` (adaptado de es_AR)
- [ ] Crear `es_CL/certificatum.php` (adaptado de es_AR)

### Revisión de calidad:

- [ ] Buscar y eliminar TODO voseo residual en es_CL.php
- [ ] Buscar y eliminar TODO tuteo informal en es_CL.php
- [ ] Verificar que TODOS los DNI sean RUT
- [ ] Verificar formato RUT (XX.XXX.XXX-X)
- [ ] Verificar URLs .cl donde corresponda
- [ ] Verificar autoridades chilenas (Consejo Transparencia)
- [ ] Verificar moneda CLP con separador de miles con punto
- [ ] Verificar términos educativos chilenos (asignatura, no materia)

---

## 🎯 CONCLUSIÓN

El archivo **es_CL.php** está **~88% completo** pero le faltan **secciones enteras importantes**:

1. ❌ **Equipo Humano** (sección crítica de diferenciación)
2. ❌ **Veritas IA** (feature próximo)
3. ❌ **ROI Académico** (métricas de valor)
4. ❌ **Panel Admin** (features técnicas)
5. ❌ **Delegación** (servicio concierge)
6. ❌ **Promo** (marketing)
7. ❌ **Estructura es_CL/** (carpeta de archivos modulares)

**Tiempo estimado para completar**: 4-6 horas de trabajo enfocado

**Riesgo actual**: Páginas o features pueden mostrarse en blanco o con textos por defecto si faltan estas claves.

---

**Generado el**: 20 de diciembre de 2025
**Revisado por**: Experto lingüista en español chileno formal institucional
**Basado en**: Comparación exhaustiva de es_AR.php (1,141 líneas) vs es_CL.php (1,009 líneas)
