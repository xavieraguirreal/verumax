# Política de Retención de Certificados

**Versión:** 1.0
**Última actualización:** Enero 2026

---

## Resumen

Esta política define qué sucede con los certificados emitidos cuando una institución cancela su suscripción o utiliza el plan de pago por uso (Singularis).

**Principio fundamental:** El certificado pertenece al estudiante, no a la institución. La validación debe funcionar siempre.

---

## 1. Actores y Expectativas

| Actor | Rol | Expectativa |
|-------|-----|-------------|
| **Institución** | Cliente que paga | Gestionar y emitir mientras el plan esté activo |
| **Estudiante** | Usuario final | Descargar su PDF y que el QR funcione "para siempre" |
| **Empleador/Tercero** | Verificador | Confirmar autenticidad del certificado en cualquier momento |

---

## 2. Matriz de Acceso por Estado

### Planes de Suscripción (Essentialis, Premium, Excellens, Supremus)

| Elemento | Plan Activo | Cancelado (0-12 meses) | Cancelado (+12 meses) |
|----------|:-----------:|:----------------------:|:---------------------:|
| Panel de gestión | ✓ | ✗ | ✗ |
| Emisión de nuevos certificados | ✓ | ✗ | ✗ |
| Descarga PDF por estudiante | ✓ | ✓ Grace period | ✗ Expirado |
| Validación QR pública | ✓ | ✓ Permanente | ✓ Permanente |
| Página de verificación | ✓ Con branding | ✓ Sin branding (Verumax) | ✓ Sin branding (Verumax) |

### Plan Singularis (Pago por Uso)

| Elemento | Estado |
|----------|--------|
| Descarga PDF por estudiante | ✓ Permanente |
| Validación QR pública | ✓ Permanente |
| Página de verificación | ✓ Sin branding (Verumax) |

---

## 3. Justificación de las Decisiones

### ¿Por qué validación permanente?

- El certificado es propiedad del estudiante, no de la institución
- Si un empleador escanea el QR y falla, Verumax pierde credibilidad
- Costo de almacenamiento de metadata es marginal (~KB por certificado)
- **Diferenciador competitivo:** "Validación garantizada de por vida"

### ¿Por qué 12 meses de grace period para descarga?

- Da tiempo razonable al estudiante para descargar su PDF
- Incentiva a la institución a reactivar su cuenta
- Permite enviar notificaciones antes de la expiración
- Reduce costos de almacenamiento de PDFs pesados a largo plazo

### ¿Por qué sin branding post-cancelación?

- **Incentivo comercial:** Si la institución quiere su marca visible, debe mantener el plan activo
- **Simplicidad técnica:** No hay que "congelar" logos o colores
- **Promoción de Verumax:** Cada verificación muestra el portal oficial
- **Claridad legal:** Evita que instituciones inactivas sigan "usando" la plataforma visualmente

### ¿Por qué Singularis es permanente pero sin branding?

- El pago de $2 cubre el certificado específico, no el branding
- No hay relación recurrente que justifique personalización
- Simplifica la propuesta: "Pagás por el certificado, no por la marca"

---

## 4. Experiencia del Usuario

### Estudiante con plan activo

```
┌─────────────────────────────────────────────────────┐
│  [LOGO INSTITUCIÓN]                                 │
│                                                     │
│  CERTIFICADO VÁLIDO                                 │
│  ───────────────────                                │
│  Nombre: Juan Pérez                                 │
│  Curso: Justicia Restaurativa                       │
│  Fecha: 15/01/2026                                  │
│                                                     │
│  [Descargar PDF]                                    │
│                                                     │
│  Emitido por: SAJuR                                 │
│  Verificado por: verumax.com                        │
└─────────────────────────────────────────────────────┘
```

### Estudiante con plan cancelado o Singularis

```
┌─────────────────────────────────────────────────────┐
│  [LOGO VERUMAX]                                     │
│                                                     │
│  CERTIFICADO VÁLIDO                                 │
│  ───────────────────                                │
│  Nombre: Juan Pérez                                 │
│  Curso: Justicia Restaurativa                       │
│  Fecha: 15/01/2026                                  │
│                                                     │
│  [Descargar PDF]  ← Solo si está en grace period    │
│                     o es Singularis                 │
│                                                     │
│  Emitido por: SAJuR                                 │
│  Verificado por: verumax.com                        │
└─────────────────────────────────────────────────────┘
```

### Estudiante con descarga expirada (+12 meses)

```
┌─────────────────────────────────────────────────────┐
│  [LOGO VERUMAX]                                     │
│                                                     │
│  CERTIFICADO VÁLIDO                                 │
│  ───────────────────                                │
│  Nombre: Juan Pérez                                 │
│  Curso: Justicia Restaurativa                       │
│  Fecha: 15/01/2026                                  │
│                                                     │
│  ⚠ La descarga del PDF ya no está disponible.      │
│    Contactá a la institución emisora.              │
│                                                     │
│  Emitido por: SAJuR                                 │
│  Verificado por: verumax.com                        │
└─────────────────────────────────────────────────────┘
```

---

## 5. Implementación Técnica

### Estructura de datos

```sql
-- Nuevos campos en tabla certificados
ALTER TABLE certificados ADD COLUMN estado_acceso ENUM(
    'activo',           -- Plan activo, todo disponible
    'grace_period',     -- Plan cancelado, 12 meses restantes
    'solo_validacion',  -- Pasó grace period, solo QR funciona
    'singularis'        -- Pago único, permanente sin branding
) DEFAULT 'activo';

ALTER TABLE certificados ADD COLUMN fecha_cancelacion_plan DATE NULL;
ALTER TABLE certificados ADD COLUMN fecha_expira_descarga DATE NULL;
```

### Lógica de acceso (PHP)

```php
/**
 * Determina si el estudiante puede descargar el PDF
 */
function puedeDescargar($certificado) {
    switch ($certificado['estado_acceso']) {
        case 'activo':
        case 'singularis':
            return true;
        case 'grace_period':
            return strtotime($certificado['fecha_expira_descarga']) > time();
        case 'solo_validacion':
        default:
            return false;
    }
}

/**
 * Determina si se muestra branding de la institución
 */
function mostrarBranding($certificado) {
    return $certificado['estado_acceso'] === 'activo';
}

/**
 * La validación QR SIEMPRE funciona
 */
function puedeValidar($certificado) {
    return true;
}
```

### Proceso automático (Cron diario)

```sql
-- 1. Detectar instituciones recién canceladas y marcar sus certificados
UPDATE certificados c
INNER JOIN instituciones i ON c.id_instancia = i.id
SET
    c.estado_acceso = 'grace_period',
    c.fecha_cancelacion_plan = CURDATE(),
    c.fecha_expira_descarga = DATE_ADD(CURDATE(), INTERVAL 12 MONTH)
WHERE i.estado_suscripcion = 'cancelado'
  AND c.estado_acceso = 'activo';

-- 2. Marcar certificados cuyo grace period expiró
UPDATE certificados
SET estado_acceso = 'solo_validacion'
WHERE estado_acceso = 'grace_period'
  AND fecha_expira_descarga < CURDATE();
```

### Notificaciones automáticas

| Momento | Destinatario | Mensaje |
|---------|--------------|---------|
| Cancelación | Institución | "Tus X certificados seguirán siendo válidos. Descarga disponible 12 meses." |
| Cancelación | Estudiantes | "Tu certificado sigue siendo válido. Descargá tu PDF antes del [fecha]." |
| 30 días antes | Estudiantes | "Recordatorio: La descarga de tu certificado expira el [fecha]." |
| 7 días antes | Estudiantes | "Última oportunidad: Descargá tu PDF antes del [fecha]." |
| Expiración | Estudiantes | "La descarga expiró. Tu certificado sigue siendo verificable por QR." |

---

## 6. Comunicación Pública

### FAQ en landing page

> **¿Qué pasa con mis certificados si cancelo la suscripción?**
>
> Todos los certificados emitidos mantienen su **validación QR activa de por vida**. Cualquier persona podrá verificar su autenticidad escaneando el código.
>
> Tus estudiantes tendrán **12 meses adicionales** para descargar sus PDFs después de la cancelación.
>
> *Nota: La página de verificación mostrará el portal de Verumax en lugar del branding de tu institución.*

> **¿Y si uso el plan Singularis (pago por uso)?**
>
> Cada certificado pagado incluye **descarga permanente** y **validación QR de por vida**. La página de verificación muestra el portal de Verumax.

### Email de cancelación

```
Asunto: Tu suscripción a Certificatum ha sido cancelada

Hola [nombre_institucion],

Tu suscripción ha sido cancelada. Esto es lo que sucede con tus certificados:

✓ Los [X] certificados emitidos SIGUEN SIENDO VÁLIDOS
✓ La validación por QR funcionará DE POR VIDA
✓ Tus estudiantes pueden descargar PDFs hasta el [fecha_expiracion]

Nota: A partir de ahora, la página de verificación mostrará el portal
de Verumax en lugar del branding de tu institución.

¿Querés recuperar el branding? Reactivá tu cuenta en cualquier momento.

[Botón: Reactivar suscripción]
```

---

## 7. Resumen Visual

```
PLAN ACTIVO
├── Panel gestión ───────── ✓
├── Emitir certificados ─── ✓
├── Descarga PDF ────────── ✓
├── Validación QR ───────── ✓
└── Branding ────────────── ✓ INSTITUCIÓN

CANCELADO (0-12 meses)
├── Panel gestión ───────── ✗
├── Emitir certificados ─── ✗
├── Descarga PDF ────────── ✓ (grace period)
├── Validación QR ───────── ✓
└── Branding ────────────── ✗ → VERUMAX

CANCELADO (+12 meses)
├── Panel gestión ───────── ✗
├── Emitir certificados ─── ✗
├── Descarga PDF ────────── ✗
├── Validación QR ───────── ✓
└── Branding ────────────── ✗ → VERUMAX

SINGULARIS
├── Panel gestión ───────── N/A
├── Emitir certificados ─── Pago por uso
├── Descarga PDF ────────── ✓ (permanente)
├── Validación QR ───────── ✓
└── Branding ────────────── ✗ → VERUMAX
```

---

## Historial de Cambios

| Fecha | Versión | Cambio |
|-------|---------|--------|
| 2026-01 | 1.0 | Documento inicial |
