# 📋 Instrucciones Rápidas - Sistema de Asistencias SAJUR

## 🚀 Pasos para Implementar

### 1️⃣ Ejecutar SQL en phpMyAdmin

1. Abre phpMyAdmin
2. Selecciona la base de datos `sajurorg_formac`
3. Ve a la pestaña "SQL"
4. Abre el archivo `sql_asistencias.sql`
5. Copia TODO el contenido
6. Pégalo en phpMyAdmin
7. Haz clic en "Continuar"

✅ **Verificación:** Deberías ver la tabla `asistencias_formaciones` en la lista de tablas.

### 2️⃣ Subir Archivos con Filezilla

Sube estos archivos a la carpeta `/asistencias/` en el servidor remoto:

```
📁 /asistencias/
├── config.php
├── asistencia.php
└── gestion_asistencias.php
```

**Ruta completa en remoto:** `https://www.sajur.org/asistencias/`

### 3️⃣ Verificar Funcionamiento

#### Probar el Gestor Administrativo

1. Accede a: `https://www.sajur.org/asistencias/gestion_asistencias.php`
2. Deberías ver la lista de formaciones
3. Copia un enlace de asistencia

#### Probar el Formulario Público

1. Pega el enlace copiado en el navegador
2. Verifica que se muestre el formulario
3. Prueba registrar una asistencia de prueba

---

## 📝 Cómo Usar el Sistema

### Para el Administrador

#### Generar Enlace de Asistencia

1. Ve a `gestion_asistencias.php`
2. Busca la formación deseada
3. **HAZ CLIC** en el recuadro morado con el enlace
4. El enlace se copiará automáticamente
5. Compártelo con los participantes (email, WhatsApp, etc.)

#### Ver Quiénes Asistieron

1. En `gestion_asistencias.php`
2. Haz clic en **"Ver Asistencias"** de la formación
3. Verás la lista completa de participantes

#### Exportar a Excel

1. En la vista de asistencias
2. Haz clic en **"Exportar CSV"**
3. El archivo se descargará automáticamente
4. Ábrelo con Excel

### Para el Participante

1. Recibe el enlace de asistencia
2. Lo abre en su navegador
3. Completa el formulario:
   - Nombres (se guardan en MAYÚSCULAS)
   - Apellidos (se guardan en MAYÚSCULAS)
   - DNI (sin puntos ni espacios)
   - Email
4. Hace clic en "Registrar Asistencia"
5. Confirma sus datos en el modal
6. Recibe email de confirmación

---

## ⏰ Ventana de Tiempo

Los participantes pueden registrar asistencia:

- **DESDE:** La hora de inicio de la formación
- **HASTA:** 1 hora después de la hora de fin

### Ejemplo

Si la formación es de **10:00 a 12:00**:
- ✅ Pueden registrarse desde las **10:00**
- ✅ Hasta las **13:00** (1 hora después del fin)
- ❌ Antes de las 10:00 → mensaje de "aún no disponible"
- ❌ Después de las 13:00 → mensaje de "plazo finalizado"

---

## 🎯 Características Especiales

### Prevención de Duplicados

- Un DNI solo puede registrarse UNA vez por formación
- Si intenta registrarse de nuevo, verá un modal con sus datos previos

### Validación en Tiempo Real

- Nombres y apellidos: Solo letras y espacios
- DNI: Solo números y guiones
- Email: Formato válido automático

### Email de Confirmación

Cada participante recibe un email con:
- Sus datos registrados
- Detalles de la formación
- Fecha y hora de registro
- Contacto para correcciones

---

## 📊 Reportes y Estadísticas

### En el Gestor Verás

- **Total de asistencias** por cada formación
- **Fecha y hora** de la formación
- **Modalidad** (Presencial/Virtual/Híbrida)
- **Estado** (Programada/En Curso/Finalizada)
- Lista completa de participantes

### CSV Exportado Incluye

- Nombres
- Apellidos
- DNI
- Email
- Fecha de registro
- Hora de registro

---

## 🔗 Enlaces Importantes

### Producción

- **Gestor:** `https://www.sajur.org/asistencias/gestion_asistencias.php`
- **Formulario:** `https://www.sajur.org/asistencias/asistencia.php?formacion=CODIGO`

### Local (para desarrollo)

- **Gestor:** `D:\appCooperativa\appAsistenciaSajur\gestion_asistencias.php`
- **Formulario:** `D:\appCooperativa\appAsistenciaSajur\asistencia.php?formacion=CODIGO`

---

## ⚠️ Problemas Comunes

### "No se especificó el código de la formación"

**Causa:** Falta el parámetro `?formacion=CODIGO` en la URL

**Solución:** Siempre comparte el enlace completo generado por el gestor

### "El plazo para registrar asistencia ha finalizado"

**Causa:** Ya pasó más de 1 hora desde el fin de la formación

**Solución:** Si necesitas extender el plazo, contacta al desarrollador

### "Ya registraste tu asistencia"

**Causa:** El DNI ya está registrado para esa formación

**Solución:** Normal. Cada DNI solo puede registrarse una vez.

### Email no llega

**Pasos:**
1. Verificar carpeta de SPAM
2. Verificar que el email esté escrito correctamente
3. Esperar unos minutos (puede haber delay)

---

## 📞 Soporte

**Email:** formacion@sajur.org

**Para reportar:**
- Descripción del problema
- Qué formación
- Captura de pantalla si es posible

---

## 🎓 Integración Futura

Este sistema está preparado para:
- Generación automática de certificados
- Estadísticas avanzadas
- Reportes personalizados
- Integración con otros sistemas SAJUR

---

**Última actualización:** 14/11/2025
**Versión:** 1.0.0
