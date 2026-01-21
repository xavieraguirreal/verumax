# Plan: Reestructuración de Fechas en Certificados

**Estado:** Pendiente de implementación
**Fecha de creación:** 3 de Enero de 2026

---

## Resumen del Cambio

Modificar la lógica de fechas en certificados para que:
1. **Fecha del certificado** = fecha de expedición (cuando quedó disponible)
2. **Texto del certificado** mencione cuándo se dictó el curso:
   - Día único: "dictado el {fecha}"
   - Rango: "dictado del {fecha_inicio} al {fecha_fin}"

## Principio de Estabilidad

**Prioridad: QUE NO FALLE**
- Los certificados pueden regenerarse con nuevo formato
- Los datos existentes en BD no se modifican
- Templates existentes deben seguir funcionando (sin errores)
- `{{fecha}}` sigue disponible como variable

---

## Fase 1: Variables de Plantilla

### Nuevas Variables

| Variable | Descripción | Fuente |
|----------|-------------|--------|
| `{{fecha_expedicion}}` | Fecha en que el certificado quedó disponible | `InstitutionService::calcularDisponibilidadCertificado()` |
| `{{fecha_curso}}` | Fecha formateada del curso (único o rango) | Calculada dinámicamente |
| `{{fecha_inicio}}` | Fecha de inicio del curso | `curso.fecha_inicio` |
| `{{fecha_fin}}` | Fecha de fin del curso | `curso.fecha_fin` o `inscripcion.fecha_finalizacion` |

---

## Fase 2: Archivos a Modificar

### 2.1 `certificatum/creare.php` (líneas 454-617)

**Cambios:**
1. Agregar función para determinar si curso es de día único o rango:
```php
function esCursoDiaUnico($fecha_inicio, $fecha_fin) {
    if (!$fecha_inicio || !$fecha_fin) return null;
    return $fecha_inicio === $fecha_fin;
}
```

2. Calcular `$fecha_expedicion`:
```php
$fecha_expedicion = InstitutionService::calcularDisponibilidadCertificado(
    $inscripcion['estado'],
    $inscripcion['fecha_finalizacion'],
    $instance_config
);
```

3. Generar `$fecha_curso` formateada:
```php
$es_dia_unico = esCursoDiaUnico($curso['fecha_inicio'], $curso['fecha_fin']);
if ($es_dia_unico === true) {
    $fecha_curso = $t('dictado_el', ['fecha' => LanguageService::formatDate($curso['fecha_inicio'])]);
} elseif ($es_dia_unico === false) {
    $fecha_curso = $t('dictado_del_al', [
        'fecha_inicio' => LanguageService::formatDate($curso['fecha_inicio']),
        'fecha_fin' => LanguageService::formatDate($curso['fecha_fin'])
    ]);
} else {
    $fecha_curso = '';
}
```

4. Agregar variables al array `$variables` (línea ~561):
```php
$variables = [
    // ... existentes ...
    'fecha_expedicion' => LanguageService::formatDate($fecha_expedicion),
    'fecha_curso' => $fecha_curso,
    'fecha_inicio' => $curso['fecha_inicio'] ? LanguageService::formatDate($curso['fecha_inicio']) : '',
    'fecha_fin' => $curso['fecha_fin'] ? LanguageService::formatDate($curso['fecha_fin']) : '',
];
```

### 2.2 `certificatum/creare_pdf_tcpdf.php`

Agregar las mismas variables al array de datos para PDF con imagen de fondo.

### 2.3 `certificatum/creare_content.php`

Asegurar que las nuevas variables estén disponibles para el contenido HTML (mPDF).

---

## Fase 3: Traducciones

### 3.1 `lang/es_AR/certificatum.php`

```php
// Fechas de curso
'dictado_el' => 'dictado el {fecha}',
'dictado_del_al' => 'dictado del {fecha_inicio} al {fecha_fin}',

// Párrafos actualizados
'template.parrafo_aprobacion_v2' => 'Se certifica que **{{nombre_completo}}** con documento **{{dni}}** aprobó el curso **{{nombre_curso}}** {{fecha_curso}}, con carga horaria de {{carga_horaria}}.',

'template.parrafo_docente_v2' => 'Se certifica que **{{nombre_completo}}** con documento **{{dni}}** se desempeñó como {{rol}} en el curso **{{nombre_curso}}** {{fecha_curso}}, transmitiendo sus conocimientos con alto nivel de competencia.',
```

### 3.2 `lang/pt_BR/certificatum.php`

```php
// Datas do curso
'dictado_el' => 'realizado em {fecha}',
'dictado_del_al' => 'realizado de {fecha_inicio} a {fecha_fin}',

// Parágrafos atualizados
'template.parrafo_aprobacion_v2' => 'Certifica-se que **{{nombre_completo}}** com documento **{{dni}}** concluiu o curso **{{nombre_curso}}** {{fecha_curso}}, com carga horária de {{carga_horaria}}.',

'template.parrafo_docente_v2' => 'Certifica-se que **{{nombre_completo}}** com documento **{{dni}}** atuou como {{rol}} no curso **{{nombre_curso}}** {{fecha_curso}}, transmitindo seus conhecimentos com alto nível de competência.',
```

---

## Fase 4: Editor de Templates

### `tools/template-editor/index.html`

**Agregar al selector de variables dinámicas:**
```html
<option value="{{fecha_expedicion}}">Fecha de Expedición</option>
<option value="{{fecha_curso}}">Período del Curso (automático)</option>
<option value="{{fecha_inicio}}">Fecha Inicio del Curso</option>
<option value="{{fecha_fin}}">Fecha Fin del Curso</option>
```

**Agregar párrafos predefinidos:**
```html
<option value="certificatum.template.parrafo_aprobacion_v2">Párrafo Aprobación (con período)</option>
<option value="certificatum.template.parrafo_docente_v2">Párrafo Docente (con período)</option>
```

---

## Fase 5: Testing y Deploy

### Verificación Pre-Deploy

1. Probar certificado de estudiante (curso día único)
2. Probar certificado de estudiante (curso con rango)
3. Probar certificado de docente
4. Verificar que `{{fecha}}` no genere error
5. Verificar ambos idiomas (es_AR, pt_BR)

### Deploy

Subir todos los archivos modificados juntos.

---

## Archivos a Modificar (Resumen)

```
certificatum/creare.php
certificatum/creare_pdf_tcpdf.php
certificatum/creare_content.php
lang/es_AR/certificatum.php
lang/pt_BR/certificatum.php
tools/template-editor/index.html
```

**SQL:** Ninguno requerido.
