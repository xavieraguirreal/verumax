# Instrucciones de Migración a MySQL - CERTIFICATUM

## Sistema de Credenciales Verificadas (VERUMax)

**Fecha:** 14/11/2025
**Versión:** 1.0.0

---

## 📋 Resumen de Cambios

Se ha migrado el sistema CERTIFICATUM de arrays PHP (`datos.php`) a base de datos MySQL, manteniendo la arquitectura multi-tenant.

### Archivos Modificados:

✅ `cursos.php` - Consulta MySQL en lugar de array
✅ `analitico.php` - Consulta MySQL en lugar de array
✅ `generar_documento.php` - Consulta MySQL + códigos de validación en BD
✅ `sajur/index.php` - Sin cambios (solo interfaz)

### Archivos Nuevos Creados:

📄 `certificatum/migracion.sql` - Estructura de tablas MySQL
📄 `certificatum/config.php` - Configuración y funciones de BD
📄 `certificatum/migrar_datos.php` - Script de migración de datos
📄 `certificatum/INSTRUCCIONES_MIGRACION.md` - Este archivo

### Backups Creados:

💾 `backup/2025-11-14/2031-cursos.php`
💾 `backup/2025-11-14/2031-analitico.php`
💾 `backup/2025-11-14/2031-generar_documento.php`
💾 `backup/2025-11-14/2031-sajur-index.php`
💾 `backup/2025-11-14/2031-sajur-datos.php`

---

## 🚀 Pasos para Deployment

### **PASO 1: Ejecutar el SQL en phpMyAdmin**

1. Conectarse a phpMyAdmin en el servidor remoto
2. Seleccionar la base de datos: `verumax_certifi`
3. Ir a la pestaña "SQL"
4. Copiar y pegar el contenido completo del archivo: **`certificatum/migracion.sql`**
5. Hacer clic en "Ejecutar"
6. Verificar que se crearon las tablas:
   - `estudiantes`
   - `cursos`
   - `inscripciones`
   - `competencias_curso`
   - `trayectoria`
   - `codigos_validacion`

**Verificación:**
```sql
SHOW TABLES LIKE '%estudiantes%';
SHOW TABLES LIKE '%cursos%';
```

Deberías ver todas las tablas creadas.

---

### **PASO 2: Migrar los Datos**

#### **Opción A: Ejecutar script localmente (Recomendado)**

1. Abrir terminal en la carpeta del proyecto
2. Ejecutar:
   ```bash
   php certificatum/migrar_datos.php
   ```
3. Verificar el output - debe decir "MIGRACIÓN COMPLETADA EXITOSAMENTE"
4. Revisar las estadísticas mostradas (estudiantes, cursos, inscripciones, etc.)

#### **Opción B: Ejecutar script en el servidor**

1. Subir `certificatum/migrar_datos.php` al servidor con FileZilla
2. Navegar en el navegador a: `https://www.verumax.com/certificatum/migrar_datos.php`
3. Verificar el output en pantalla
4. **IMPORTANTE:** Eliminar el archivo `migrar_datos.php` del servidor después de ejecutarlo

**Verificación en phpMyAdmin:**
```sql
SELECT COUNT(*) FROM estudiantes;
SELECT COUNT(*) FROM cursos;
SELECT COUNT(*) FROM inscripciones;
```

Debería mostrar:
- 3 estudiantes (Alejandro Rodriguez, Sofía Gómez, Martín Lopez)
- 4 cursos aproximadamente
- 4+ inscripciones

---

### **PASO 3: Subir Archivos al Servidor con FileZilla**

Subir la carpeta completa de CERTIFICATUM:

**Carpeta a subir:**
1. ✅ **Carpeta completa: `certificatum/`** → al servidor
   - Incluye: config.php, cursos.php, analitico.php, generar_documento.php, validar.php, etc.

**Archivos en raíz (instituciones):**
2. ✅ `sajur/index.php` (modificado - actualizar si cambió)
3. ✅ `certificatum.php` (landing page - si existe)

**NO subir:**
- ❌ `certificatum/migrar_datos.php` (solo ejecutar una vez, luego eliminar)
- ❌ `certificatum/migracion.sql` (ya ejecutado en phpMyAdmin)
- ❌ `backup/*` (mantener solo localmente)

---

### **PASO 4: Probar el Sistema**

#### **Test 1: Portal de Estudiante**

1. Navegar a: `https://www.verumax.com/sajur/`
2. Ingresar DNI de prueba: `25123456`
3. El formulario enviará a: `https://www.verumax.com/certificatum/cursos.php`
4. **Resultado esperado:** Lista de 2 cursos (Derecho Procesal Avanzado, Argumentación Jurídica)

#### **Test 2: Ver Trayectoria**

1. Hacer clic en "Ver Trayectoria Completa" de un curso
2. Verificar que muestra la línea de tiempo con eventos
3. **Resultado esperado:** Timeline con eventos (Inscripción, Inicio, Finalización, etc.)

#### **Test 3: Generar Certificado**

1. En un curso "Aprobado", hacer clic en "Certificado"
2. Verificar que se genera correctamente
3. **Resultado esperado:** Certificado en PDF con QR válido

#### **Test 4: Código de Validación**

1. Tomar nota del código QR del certificado generado (ej: `VALID-xxxxxxxxxxxx`)
2. URL del QR debe ser: `https://www.verumax.com/certificatum/validar.php?codigo=VALID-xxx`
3. Verificar en phpMyAdmin que se guardó en `codigos_validacion`:
   ```sql
   SELECT * FROM codigos_validacion WHERE codigo_validacion = 'VALID-xxxxxxxxxxxx';
   ```
4. **Resultado esperado:** 1 registro con los datos del certificado

---

### **PASO 5: Verificar Logs de Errores (Opcional)**

Si algo falla, revisar los logs de PHP en el servidor:

1. Conectarse via FTP o cPanel
2. Buscar archivo: `error_log` o `php_error_log`
3. Revisar errores recientes relacionados con MySQL

---

## 🔧 Configuración de Base de Datos

El archivo `config_certificados.php` usa estas credenciales:

```php
define('CERT_DB_HOST', 'localhost');
define('CERT_DB_USER', 'verumax_certifi');
define('CERT_DB_PASSWORD', '/hPfiYd6xH');
define('CERT_DB_NAME', 'verumax_certifi');
```

**IMPORTANTE:** Si las credenciales cambian, editar `config_certificados.php` en el servidor.

---

## 📊 Estructura de Base de Datos

### Tabla `estudiantes`
- `id_estudiante` - ID único
- `institucion` - Código institución ('sajur', 'liberte', etc.)
- `dni` - DNI del estudiante
- `nombre_completo` - Nombre completo

### Tabla `cursos`
- `id_curso` - ID único
- `institucion` - Código institución
- `codigo_curso` - Código único del curso ('SJ-DPA-2024')
- `nombre_curso` - Nombre del curso
- `carga_horaria` - Horas del curso

### Tabla `inscripciones`
- `id_inscripcion` - ID único
- `id_estudiante` - Referencia al estudiante
- `id_curso` - Referencia al curso
- `estado` - 'Aprobado', 'En Curso', 'Por Iniciar', etc.
- `fecha_finalizacion` - Fecha de finalización
- `nota_final` - Nota (decimal)
- `asistencia` - Porcentaje de asistencia

### Tabla `competencias_curso`
- Competencias adquiridas por estudiante en cada curso

### Tabla `trayectoria`
- Eventos del timeline académico (Inscripción, Exámenes, etc.)

### Tabla `codigos_validacion`
- Códigos QR generados para certificados
- Tracking de consultas de validación

---

## 🔄 Agregar Nuevos Estudiantes

### Desde SQL (phpMyAdmin):

```sql
-- 1. Insertar estudiante
INSERT INTO estudiantes (institucion, dni, nombre_completo)
VALUES ('sajur', '12345678', 'JUAN PEREZ');

-- 2. Insertar curso (si no existe)
INSERT INTO cursos (institucion, codigo_curso, nombre_curso, carga_horaria)
VALUES ('sajur', 'SJ-NUEVO-2025', 'Curso Nuevo', 80);

-- 3. Inscribir al estudiante
INSERT INTO inscripciones (id_estudiante, id_curso, estado, fecha_finalizacion, nota_final, asistencia)
VALUES (
    (SELECT id_estudiante FROM estudiantes WHERE dni = '12345678' AND institucion = 'sajur'),
    (SELECT id_curso FROM cursos WHERE codigo_curso = 'SJ-NUEVO-2025'),
    'Aprobado', '2025-12-15', 8.50, '95%'
);
```

---

## 🛡️ Rollback (Si algo falla)

Si necesitas volver al sistema anterior:

1. **Restaurar archivos desde backup:**
   ```bash
   copy backup/2025-11-14/2031-cursos.php cursos.php
   copy backup/2025-11-14/2031-analitico.php analitico.php
   copy backup/2025-11-14/2031-generar_documento.php generar_documento.php
   ```

2. **En el servidor:** Subir los archivos del backup con FileZilla

3. **Eliminar del servidor:**
   - `config_certificados.php`

4. **En phpMyAdmin (opcional):** Eliminar las tablas creadas:
   ```sql
   DROP TABLE IF EXISTS codigos_validacion;
   DROP TABLE IF EXISTS trayectoria;
   DROP TABLE IF EXISTS competencias_curso;
   DROP TABLE IF EXISTS inscripciones;
   DROP TABLE IF EXISTS cursos;
   DROP TABLE IF EXISTS estudiantes;
   ```

---

## ✅ Checklist de Deployment

- [ ] Ejecutar `sql_certificados_multitenant.sql` en phpMyAdmin
- [ ] Verificar que se crearon 6 tablas
- [ ] Ejecutar `migrar_datos_a_mysql.php`
- [ ] Verificar migración exitosa (3 estudiantes, 4+ cursos)
- [ ] Subir `config_certificados.php` al servidor
- [ ] Subir `cursos.php` (modificado) al servidor
- [ ] Subir `analitico.php` (modificado) al servidor
- [ ] Subir `generar_documento.php` (modificado) al servidor
- [ ] Probar portal con DNI 25123456
- [ ] Probar generación de certificado
- [ ] Verificar código de validación en BD
- [ ] Revisar logs de errores
- [ ] **Eliminar `migrar_datos_a_mysql.php` del servidor**

---

## 📞 Soporte

Si tienes problemas durante la migración:

1. Revisar logs de PHP en el servidor
2. Verificar credenciales de BD en `config_certificados.php`
3. Consultar el archivo de backup si necesitas revertir cambios

---

**Migración preparada por:** Claude Code
**Fecha:** 14/11/2025
**Versión del sistema:** CERTIFICATUM 1.0 Multi-Tenant (VERUMax)
