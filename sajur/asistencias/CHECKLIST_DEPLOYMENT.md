# ✅ Checklist de Deployment - Sistema de Asistencias SAJUR

## 📋 Pre-Deployment (En Local)

- [ ] Revisar que todos los archivos estén creados:
  - [ ] `config.php`
  - [ ] `asistencia.php`
  - [ ] `gestion_asistencias.php`
  - [ ] `sql_asistencias.sql`
  - [ ] `README.md`
  - [ ] `INSTRUCCIONES.md`

- [ ] Verificar rutas en `config.php`:
  - [ ] Ruta a config de SAJUR: `../appSajur/formacion/config.php`
  - [ ] Ruta a PHPMailer: `../appSajur/formacion/PHPMailer/`
  - [ ] URL base: `https://www.sajur.org/asistencias/`

## 🗄️ Base de Datos

- [ ] Abrir phpMyAdmin
- [ ] Seleccionar base de datos `sajurorg_formac`
- [ ] Ejecutar `sql_asistencias.sql`
- [ ] Verificar que la tabla `asistencias_formaciones` se creó
- [ ] Verificar índices:
  - [ ] `idx_formacion`
  - [ ] `idx_dni_formacion`
  - [ ] `unique_asistencia`
- [ ] Verificar foreign key a `formaciones`

## 📤 Subir Archivos con Filezilla

- [ ] Conectar al servidor remoto
- [ ] Crear carpeta `/asistencias/` si no existe
- [ ] Subir archivos:
  - [ ] `config.php`
  - [ ] `asistencia.php`
  - [ ] `gestion_asistencias.php`
- [ ] Verificar permisos de archivos (644)

## 🧪 Testing en Producción

### Test 1: Gestor Administrativo

- [ ] Acceder a: `https://www.sajur.org/asistencias/gestion_asistencias.php`
- [ ] Verificar que carga sin errores
- [ ] Verificar que se ven las formaciones
- [ ] Copiar un enlace de asistencia
- [ ] Verificar que el enlace se copia correctamente

### Test 2: Formulario Público

- [ ] Abrir el enlace copiado
- [ ] Verificar que se muestra el formulario
- [ ] Verificar datos de la formación:
  - [ ] Nombre correcto
  - [ ] Fecha correcta
  - [ ] Horario correcto
- [ ] Probar validación en tiempo real:
  - [ ] Nombres se convierten a MAYÚSCULAS
  - [ ] Apellidos se convierten a MAYÚSCULAS
  - [ ] DNI solo acepta números y guiones

### Test 3: Registro de Asistencia

**IMPORTANTE: Hacer esto solo si la formación está en ventana de tiempo permitida**

- [ ] Completar formulario con datos de prueba
- [ ] Hacer clic en "Registrar Asistencia"
- [ ] Verificar modal de confirmación
- [ ] Confirmar registro
- [ ] Verificar mensaje de éxito
- [ ] Verificar que llegó email de confirmación

### Test 4: Verificación de Duplicados

- [ ] Intentar registrar el mismo DNI de nuevo
- [ ] Verificar que muestra modal de "Ya registrado"
- [ ] Verificar que muestra los datos previos

### Test 5: Exportación CSV

- [ ] Volver al gestor
- [ ] Ver asistencias de la formación de prueba
- [ ] Hacer clic en "Exportar CSV"
- [ ] Verificar que descarga el archivo
- [ ] Abrir en Excel y verificar codificación UTF-8

## ⚠️ Verificaciones de Seguridad

- [ ] No hay mensajes de error visibles al usuario
- [ ] PDO está usando prepared statements
- [ ] Datos se sanitizan antes de insertar
- [ ] Emails se validan
- [ ] UNIQUE KEY previene duplicados
- [ ] Foreign key mantiene integridad referencial

## 📧 Verificar Email

- [ ] Email de confirmación llega
- [ ] Formato HTML correcto
- [ ] Datos del participante correctos
- [ ] Datos de la formación correctos
- [ ] Remitente: formacion@sajur.org
- [ ] Asunto claro y descriptivo

## 🎨 Verificar Responsive

Probar formulario en:

- [ ] Desktop (1920px)
- [ ] Laptop (1366px)
- [ ] Tablet (768px)
- [ ] Móvil (375px)

## 📊 Datos de Prueba

Después del testing exitoso:

- [ ] Eliminar asistencias de prueba si es necesario:
  ```sql
  DELETE FROM asistencias_formaciones WHERE dni = 'DNI_PRUEBA';
  ```

## 🔄 Post-Deployment

- [ ] Documentar URL del gestor para el equipo
- [ ] Capacitar al equipo sobre cómo generar enlaces
- [ ] Capacitar sobre cómo exportar CSV
- [ ] Agregar enlace al gestor en el panel principal de SAJUR (opcional)

## 📝 Documentación para el Equipo

- [ ] Compartir `INSTRUCCIONES.md`
- [ ] Explicar cómo generar enlaces
- [ ] Explicar cómo ver asistencias
- [ ] Explicar cómo exportar a CSV
- [ ] Explicar ventana de tiempo permitida

## 🎯 Checklist de Funcionalidades

- [ ] ✅ Generador de enlaces funciona
- [ ] ✅ Formulario público funciona
- [ ] ✅ Validaciones funcionan
- [ ] ✅ Prevención de duplicados funciona
- [ ] ✅ Emails se envían
- [ ] ✅ Exportación CSV funciona
- [ ] ✅ Estadísticas se muestran correctamente
- [ ] ✅ Responsive funciona
- [ ] ✅ Verificación de ventana de tiempo funciona

## 🐛 Monitoreo Post-Deployment

**Durante las primeras 24 horas:**

- [ ] Revisar logs de PHP para errores
- [ ] Verificar que los emails están llegando
- [ ] Verificar que no hay problemas de rendimiento
- [ ] Estar disponible para soporte

## 📞 Contactos Importantes

- **Desarrollador:** [Tu contacto]
- **Admin SAJUR:** formacion@sajur.org
- **Soporte Técnico:** [Contacto de soporte]

---

## ✅ Deployment Completado

Fecha: ___/___/___
Hora: ___:___
Por: _______________

**Notas adicionales:**
_________________________________________________
_________________________________________________
_________________________________________________

---

**Sistema:** Gestión de Asistencias SAJUR v1.0.0
**Fecha Creación:** 14/11/2025
