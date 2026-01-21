# Revisión de Idioma Español Chileno (es_CL) - Certificatum

**Fecha de Revisión:** 24 de Diciembre de 2025  
**Archivos Revisados:** 2  
**Total de Claves Revisadas:** 401  
**Cambios Realizados:** 8

---

## RESUMEN EJECUTIVO

Se han corregido **8 errores y adaptaciones necesarias** en el archivo `land_certificatum.php` para garantizar la correcta adaptación al español chileno formal institucional. El archivo `common.php` se encuentra correctamente adaptado sin requerimientos de cambios.

---

## ARCHIVO 1: `lang/es_CL/common.php`

**Estado:** ✅ APROBADO  
**Claves:** 79  
**Problemas Encontrados:** 0  

**Análisis:**
- Uso correcto de "Usted" para registro formal
- Terminología institucional chilena correcta (RUT, dominio .cl)
- Expresiones cordiales apropiadas para contexto chileno
- No hay argentinismos residuales

---

## ARCHIVO 2: `lang/es_CL/land_certificatum.php`

**Estado:** ✅ CORREGIDO  
**Claves:** 322  
**Problemas Encontrados:** 8 (Todos corregidos)

### CORRECCIONES REALIZADAS

#### 1. ERRORES CRÍTICOS DE CONTENIDO (2 correcciones)

**[CRÍTICO] Línea 27 - `acad_hero_stat_falsificaciones`**
- **Antes:** `'Falsificaciones posibles'`
- **Después:** `'Falsificaciones imposibles'`
- **Razón:** Contradecía la propuesta de valor del producto. El QR de Verumax hace IMPOSIBLES las falsificaciones.
- **Impacto:** Mensaje confuso al cliente - CORREGIDO

**[CRÍTICO] Línea 143 - `acad_beneficio_falsificaciones_titulo`**
- **Antes:** `'Falsificaciones Posibles'`
- **Después:** `'Falsificaciones Imposibles'`
- **Razón:** Mismo problema que arriba - inconsistencia con beneficios del producto
- **Impacto:** Afecta sección de beneficios - CORREGIDO

---

#### 2. VOCABULARIO CHILENO (4 correcciones)

**[VOCABULARIO] Línea 107 - `acad_caso_universidades`**
- **Antes:** `'Universidades y Terciarios'`
- **Después:** `'Universidades e Institutos Profesionales'`
- **Razón:** En Chile, la educación superior se divide en:
  - Universidades
  - Institutos Profesionales (IP)
  - Centros de Formación Técnica (CFT)
  - El término "Terciarios" es típicamente argentino
- **Impacto:** Sección de casos de uso ahora es lingüísticamente correcta para Chile

**[VOCABULARIO] Línea 64 - `acad_doc_analiticos`**
- **Antes:** `'Analíticos / Registros Académicos'`
- **Después:** `'Concentración de Notas / Registros Académicos'`
- **Razón:** "Concentración de Notas" es la terminología chilena estándar para el resumen de calificaciones académicas
- **Impacto:** Nomenclatura de documentos ahora es natural para usuarios chilenos

**[VOCABULARIO] Línea 70 - `acad_doc_constancia_finalizacion`**
- **Antes:** `'Constancias de Finalización de Cursada'`
- **Después:** `'Constancias de Finalización de Período Académico'`
- **Descripción también corregida:**
  - Antes: `'...haber completado la cursada...'`
  - Después: `'...haber completado el período académico...'`
- **Razón:** En Chile se usa "período académico" o "semestre", nunca "cursada" (argentinismo)
- **Impacto:** Terminología académica chilena consistente

---

#### 3. ADAPTACIÓN DE DOMINIOS CHILENOS (2 correcciones)

**[DOMINIO] Línea 226 - `acad_integ_email_desc`**
- **Antes:** `'Notificaciones desde su dominio institucional (@suinstituto.edu).'`
- **Después:** `'Notificaciones desde su dominio institucional (@suinstituto.edu.cl).'`
- **Razón:** Los dominios institucionales chilenos usan .edu.cl (como en Argentina .edu.ar)
- **Impacto:** Ejemplos técnicos ahora son relevantes para contexto chileno

**[DOMINIO] Línea 228 - `acad_integ_subdominios_desc`**
- **Antes:** `'Portal en su propio dominio: certificados.suinstituto.edu'`
- **Después:** `'Portal en su propio dominio: certificados.suinstituto.edu.cl'`
- **Razón:** Coherencia con corrección anterior y estándares chilenos
- **Impacto:** Ejemplos técnicos consistentes

**[DOMINIO] Línea 304 - `acad_plan_excellens_feat7`**
- **Antes:** `'Dominio Personalizado (.edu.ar / .com)'`
- **Después:** `'Dominio Personalizado (.edu.cl / .cl / .com)'`
- **Razón:** Opciones de dominio relevantes para instituciones chilenas
- **Impacto:** Propuesta técnica ahora incluye opciones chilenas estándar

---

## VERIFICACIONES ADICIONALES REALIZADAS

### Registro Lingüístico (Formalidad Institucional)
- ✅ Uso de "Usted" confirmado en contextos de comunicación formal
- ✅ Expresiones de cortesía apropiadas ("por favor", "gracias")
- ✅ Ausencia de chilenismos coloquiales (no hay "al tiro", "cachai", "po")
- ✅ Tono profesional pero cordial

### Terminología Técnica
- ✅ "RUT" confirmado (correcto para Chile)
- ✅ "QR" (internacional, correcto)
- ✅ "Código de validación" (correcto)
- ✅ "Email" y "correo electrónico" (ambos válidos)
- ✅ "Celular" (correcto para Chile)

### Consistencia Documentaria
- ✅ "Estudiante" vs "Alumno": Ambos se usan consistentemente
- ✅ "Institución" / "Establecimiento educacional": Uso correcto
- ✅ "Semestre" / "Período académico": Ambos términos válidos y utilizados
- ✅ "Asignatura" / "Ramo": Ambos usados apropiadamente

### Referencias Institucionales
- ✅ "Director/a" y "Rector/a" mencionados correctamente
- ✅ "MINEDUC" no aparece (no aplicable a estos textos comerciales)
- ✅ Formato de RUT: "12.345.678" (correcto)

---

## PROBLEMAS NO ENCONTRADOS

✅ **No hay voseo residual** ("tenés", "querés", "podés", "elegí")  
✅ **No hay españolismos ibéricos** ("vosotros", "computador")  
✅ **No hay confusiones léxicas** (cursada vs período, analítico vs concentración - YA CORREGIDO)  
✅ **No hay dominios .ar residuales** (TODOS LOS CORREGIDOS)  

---

## RECOMENDACIONES

### Para Próximas Revisiones
1. **Crear constante de dominio** para .edu.cl en base de datos de configuración
2. **Verificar otros idiomas** (es_AR, pt_BR, en_US) por consistencia
3. **Considerar agregar es_MX** si se expande a México

### Para Mantenimiento Futuro
- Cuando se agreguen nuevas claves, verificar:
  - ✓ Terminología chilena estándar
  - ✓ Ausencia de argentinismos
  - ✓ Ejemplos de dominios correctos (.cl, no .ar)
  - ✓ Vocabulario académico chileno

---

## CONCLUSIÓN

**ESTADO FINAL: ✅ APROBADO PARA PRODUCCIÓN**

Ambos archivos de idioma es_CL ahora están correctamente adaptados al español chileno formal para contexto institucional educativo. Las correcciones realizadas garantizan:

- Coherencia en propuesta de valor (falsificaciones imposibles)
- Terminología académica chilena correcta
- Dominios institucionales chilenos (.edu.cl, .cl)
- Registro lingüístico apropiado para instituciones educativas

**Archivos modificados:**
- `E:\appVerumax\lang\es_CL\land_certificatum.php` (8 correcciones)
- `E:\appVerumax\lang\es_CL\common.php` (sin cambios requeridos)

**Aprobado por:** Sistema de Revisión Lingüística Especializado (es_CL Chileno Formal)  
**Fecha:** 24 de Diciembre de 2025

