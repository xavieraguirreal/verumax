# Configuración de Webhooks de SendGrid - Verumax

## Resumen

Este documento describe cómo configurar los webhooks de SendGrid para habilitar el tracking de emails (aperturas, clicks, rebotes) en Verumax.

**Estado actual del sistema:**
- Tracking de opens/clicks: ✅ Configurado en código
- Webhook endpoint: ✅ Creado (`/api/sendgrid/webhook.php`)
- Tablas de BD: ⚠️ Pendiente ejecutar migración
- Configuración en SendGrid: ⚠️ Pendiente configurar

---

## Paso 1: Ejecutar Migración SQL

Ejecutar en la base de datos `verumax_general` el archivo:

```
database/migrations/012_sendgrid_webhook_tracking.sql
```

Este script crea:
- Tabla `sendgrid_webhook_events` para eventos raw
- Columnas de notificación en `email_config`
- Tabla `email_notification_history`
- Vista `v_email_stats_daily`

---

## Paso 2: Configurar Webhook en SendGrid

### 2.1 Acceder a SendGrid

1. Ir a [https://app.sendgrid.com](https://app.sendgrid.com)
2. Iniciar sesión con la cuenta de Verumax

### 2.2 Configurar Event Webhook

1. Ir a **Settings** → **Mail Settings**
2. Buscar **Event Webhook**
3. Hacer clic en **Edit**

### 2.3 Configurar URL y Eventos

**HTTP Post URL:**
```
https://verumax.com/api/sendgrid/webhook.php
```

**Eventos a seleccionar:**
- ✅ Processed
- ✅ Delivered
- ✅ Open
- ✅ Click
- ✅ Bounce
- ✅ Dropped
- ✅ Spam Report
- ✅ Unsubscribe
- ⬜ Deferred (opcional, genera mucho volumen)
- ⬜ Group Unsubscribe (opcional)
- ⬜ Group Resubscribe (opcional)

### 2.4 Habilitar Firma de Seguridad (Recomendado)

1. En la misma pantalla, buscar **Signed Event Webhook Requests**
2. Activar el toggle
3. Copiar la **Public Key** que aparece
4. Guardar la clave en el servidor (ver Paso 3)

### 2.5 Guardar Configuración

1. Hacer clic en **Save**
2. Verificar que el estado cambie a **Enabled**

---

## Paso 3: Configurar Clave Pública (Seguridad)

Para verificar que los webhooks vienen realmente de SendGrid:

1. Crear archivo `config/sendgrid_webhook_public_key.pem` con la clave pública copiada de SendGrid

2. El contenido debe verse así:
```
-----BEGIN PUBLIC KEY-----
MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE...
(clave de SendGrid)
-----END PUBLIC KEY-----
```

**Nota:** Si no se configura la clave, el webhook aceptará todas las peticiones (menos seguro pero funcional para testing).

---

## Paso 4: Verificar Funcionamiento

### 4.1 Revisar Logs

Los logs de webhook se guardan en:
```
logs/sendgrid/webhook_YYYY-MM-DD.log
```

Ejemplo de log exitoso:
```
[2026-01-01 12:00:00] [INFO] Webhook request recibido
[2026-01-01 12:00:00] [INFO] Eventos recibidos: 5
[2026-01-01 12:00:00] [INFO] Procesamiento completado | Data: {"total":5,"processed":5,"errors":0}
```

### 4.2 Verificar en Dashboard

1. Acceder al panel de administración de la institución
2. Ir a la pestaña **Emails**
3. Verificar que:
   - Aparezca "Webhook activo" en verde
   - Se muestren estadísticas de opens/clicks
   - La tabla "Últimos Eventos de Webhook" muestre datos

### 4.3 Test Manual

Para probar, enviar un email de prueba y luego:
1. Abrir el email
2. Hacer clic en algún enlace
3. Esperar 1-2 minutos
4. Verificar en el dashboard que se registró el open/click

---

## Troubleshooting

### Error: "Firma inválida"

**Causa:** La clave pública no coincide o está mal configurada

**Solución:**
1. Verificar que la clave en `config/sendgrid_webhook_public_key.pem` sea correcta
2. Regenerar la clave en SendGrid si es necesario
3. Asegurar que el archivo tenga formato PEM correcto

### Error: "Error conectando a BD"

**Causa:** El webhook no puede conectar a la base de datos

**Solución:**
1. Verificar que el archivo `.env` esté correctamente configurado
2. Revisar permisos de archivo
3. Los eventos se guardan en `logs/sendgrid/events_backup_*.json` para reprocesar después

### Los eventos no aparecen en el dashboard

**Posibles causas:**
1. La migración SQL no se ejecutó
2. El webhook no está configurado en SendGrid
3. El campo `instancia` no coincide con el slug de la institución

**Verificar:**
```sql
SELECT COUNT(*) FROM sendgrid_webhook_events;
SELECT DISTINCT instancia FROM sendgrid_webhook_events;
```

### Alto volumen de eventos

**Recomendación:** Configurar limpieza automática de eventos > 90 días

```sql
DELETE FROM sendgrid_webhook_events
WHERE received_at < DATE_SUB(NOW(), INTERVAL 90 DAY);
```

---

## Arquitectura

```
SendGrid                         Verumax
   │
   │  POST /api/sendgrid/webhook.php
   │  (eventos: open, click, bounce, etc.)
   │
   ▼
┌─────────────────────────────────────────────┐
│  api/sendgrid/webhook.php                   │
│  - Verificar firma (opcional)               │
│  - Parsear JSON                             │
│  - Guardar en sendgrid_webhook_events       │
│  - Actualizar email_logs                    │
│  - Responder 200 OK                         │
└─────────────────────────────────────────────┘
   │
   ▼
┌─────────────────────────────────────────────┐
│  Base de datos: verumax_general             │
│  - sendgrid_webhook_events (eventos raw)    │
│  - email_logs (estados actualizados)        │
└─────────────────────────────────────────────┘
   │
   ▼
┌─────────────────────────────────────────────┐
│  admin/modulos/email_stats.php              │
│  - Mostrar estadísticas                     │
│  - Configurar notificaciones                │
│  - Ver eventos en tiempo real               │
└─────────────────────────────────────────────┘
```

---

## Archivos Relacionados

| Archivo | Descripción |
|---------|-------------|
| `api/sendgrid/webhook.php` | Endpoint que recibe webhooks |
| `admin/modulos/email_stats.php` | Dashboard de estadísticas |
| `database/migrations/012_sendgrid_webhook_tracking.sql` | Migración SQL |
| `logs/sendgrid/` | Logs de webhooks |
| `config/sendgrid_webhook_public_key.pem` | Clave pública (seguridad) |
| `src/VERUMax/Services/EmailService.php` | Servicio de envío |

---

## Contacto

Para problemas con la configuración, revisar:
1. Logs en `logs/sendgrid/`
2. Panel de SendGrid → Activity
3. Base de datos tabla `sendgrid_webhook_events`

**Última actualización:** 2026-01-01
