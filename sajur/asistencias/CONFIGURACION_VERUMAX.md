# ⚙️ Configuración para Dominio VERUMAX

## 🌐 Arquitectura del Sistema

Este sistema tiene una arquitectura **multi-dominio**:

```
┌─────────────────────────────────────────────────┐
│           DOMINIO VERUMAX                       │
│   https://www.verumax.com.ar/asistencias/      │
│                                                 │
│   📁 Archivos PHP:                             │
│   - config.php                                  │
│   - asistencia.php                              │
│   - gestion_asistencias.php                     │
│   - PHPMailer/ (carpeta completa)              │
│                                                 │
│   ⬇️ SE CONECTA A ⬇️                           │
└─────────────────────────────────────────────────┘
                    │
                    │ Conexión Remota
                    ▼
┌─────────────────────────────────────────────────┐
│           DOMINIO SAJUR                         │
│   https://www.sajur.org                         │
│                                                 │
│   🗄️ Base de Datos MySQL:                      │
│   - Nombre: sajurorg_formac                     │
│   - Usuario: sajurorg_formac                    │
│   - Tablas: formaciones, asistencias_formaciones│
│                                                 │
│   📧 Servidor SMTP:                             │
│   - Host: vps-5361869-x.dattaweb.com            │
│   - Email: formacion@sajur.org                  │
└─────────────────────────────────────────────────┘
```

## 📝 Pasos de Configuración

### 1️⃣ Base de Datos (Ejecutar en SAJUR)

**Dónde:** phpMyAdmin de SAJUR
**Base de datos:** `sajurorg_formac`

```sql
-- Copiar y ejecutar el contenido de sql_asistencias.sql
```

Este script creará la tabla `asistencias_formaciones` en la base de datos de SAJUR.

### 2️⃣ Archivos PHP (Subir a VERUMAX)

**Dónde:** Servidor de VERUMAX
**Ruta:** La que corresponda en tu dominio (ej: `/public_html/asistencias/`)

**Archivos a subir:**
```
📁 /asistencias/
├── config.php
├── asistencia.php
├── gestion_asistencias.php
└── PHPMailer/
    ├── src/
    │   ├── Exception.php
    │   ├── PHPMailer.php
    │   └── SMTP.php
    └── ... (resto de archivos de PHPMailer)
```

### 3️⃣ Configurar URL en config.php

**IMPORTANTE:** Edita `config.php` y cambia esta línea según tu dominio real:

```php
define('ASISTENCIAS_BASE_URL', 'https://www.verumax.com.ar/asistencias/');
```

**Opciones comunes:**
- Si está en raíz: `https://www.verumax.com.ar/`
- Si está en subcarpeta: `https://www.verumax.com.ar/asistencias/`
- Si es subdominio: `https://asistencias.verumax.com.ar/`

### 4️⃣ Verificar Conexión a Base de Datos

**IMPORTANTE:** El servidor VERUMAX debe poder conectarse a la base de datos de SAJUR.

#### Opción A: Si ambos están en el mismo servidor

Usar `localhost` (ya configurado):
```php
define('DB_HOST', 'localhost');
```

#### Opción B: Si están en servidores diferentes

Necesitas:
1. **IP o dominio del servidor de SAJUR**
2. **Acceso remoto habilitado** en MySQL de SAJUR
3. **Permisos del usuario** para conexión remota

Cambiar en `config.php`:
```php
define('DB_HOST', 'IP_O_DOMINIO_DE_SAJUR');  // Ej: '192.168.1.100' o 'mysql.sajur.org'
```

Y ejecutar en phpMyAdmin de SAJUR:
```sql
GRANT ALL PRIVILEGES ON sajurorg_formac.*
TO 'sajurorg_formac'@'IP_DE_VERUMAX'
IDENTIFIED BY 'zYg*HZg0xA';

FLUSH PRIVILEGES;
```

## 🔐 Credenciales Configuradas

### Base de Datos (SAJUR)
```
Host: localhost (o IP si es remoto)
Usuario: sajurorg_formac
Password: zYg*HZg0xA
Base de datos: sajurorg_formac
```

### Email (SAJUR)
```
Host: vps-5361869-x.dattaweb.com
Puerto: 465
Usuario: formacion@sajur.org
Password: 37Dq**T6fY
From: formacion@sajur.org
From Name: SAJUR - Formación
```

## 🧪 Testing

### 1. Probar Conexión a Base de Datos

Crea un archivo `test_conexion.php` temporal:

```php
<?php
require_once 'config.php';

try {
    $conn = getDBConnection();
    echo "✅ Conexión exitosa a la base de datos SAJUR!<br>";

    // Probar consulta
    $stmt = $conn->query("SELECT COUNT(*) as total FROM formaciones");
    $result = $stmt->fetch();
    echo "✅ Formaciones encontradas: " . $result['total'] . "<br>";

    $stmt = $conn->query("SELECT COUNT(*) as total FROM asistencias_formaciones");
    $result = $stmt->fetch();
    echo "✅ Asistencias encontradas: " . $result['total'] . "<br>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
```

Accede a: `https://www.verumax.com.ar/asistencias/test_conexion.php`

**SI TODO ESTÁ BIEN:** Deberías ver conexión exitosa y conteos.

**ELIMINA** este archivo después del test por seguridad.

### 2. Probar Gestor

Accede a: `https://www.verumax.com.ar/asistencias/gestion_asistencias.php`

Deberías ver:
- ✅ Lista de formaciones de SAJUR
- ✅ Enlaces generados correctamente
- ✅ Poder ver asistencias

### 3. Probar Formulario

Copia un enlace del gestor y ábrelo.

Deberías ver:
- ✅ Datos de la formación correctos
- ✅ Formulario funcional
- ✅ (Si estás en ventana de tiempo) Poder registrar asistencia

## ⚠️ Problemas Comunes

### Error: "Access denied for user"

**Causa:** El usuario no tiene permisos de conexión remota.

**Solución:** Ejecutar el GRANT en phpMyAdmin de SAJUR (ver sección 4️⃣)

### Error: "Unknown database"

**Causa:** La base de datos no existe o el nombre es incorrecto.

**Solución:** Verificar que la base `sajurorg_formac` existe en SAJUR.

### Error: "Table 'asistencias_formaciones' doesn't exist"

**Causa:** No se ejecutó el SQL.

**Solución:** Ejecutar `sql_asistencias.sql` en phpMyAdmin de SAJUR.

### Error: "Connection timeout"

**Causa:** Firewall bloqueando conexión o MySQL no acepta conexiones remotas.

**Solución:**
1. Verificar firewall del servidor de SAJUR
2. Verificar que MySQL está configurado para aceptar conexiones remotas
3. En `/etc/mysql/my.cnf` (servidor SAJUR), verificar:
   ```
   bind-address = 0.0.0.0
   ```

### Email no llega

**Causa:** Configuración SMTP incorrecta o servidor bloqueando.

**Solución:**
1. Verificar que las credenciales sean correctas
2. Verificar que el servidor VERUMAX pueda conectar al puerto 465
3. Revisar logs de PHP para errores de PHPMailer

## 📊 URLs Finales del Sistema

Después de configurar todo:

- **Gestor Administrativo:**
  ```
  https://www.verumax.com.ar/asistencias/gestion_asistencias.php
  ```

- **Formulario Público:**
  ```
  https://www.verumax.com.ar/asistencias/asistencia.php?formacion=CODIGO
  ```

## 🔒 Seguridad

### Archivos que NO debes subir a VERUMAX:
- ❌ `README.md` (solo para desarrollo)
- ❌ `INSTRUCCIONES.md` (solo para desarrollo)
- ❌ `CONFIGURACION_VERUMAX.md` (este archivo)
- ❌ `CHECKLIST_DEPLOYMENT.md` (solo para desarrollo)
- ❌ `test_conexion.php` (si lo creaste, eliminarlo después del test)

### Archivos que SÍ debes subir:
- ✅ `config.php`
- ✅ `asistencia.php`
- ✅ `gestion_asistencias.php`
- ✅ `PHPMailer/` (carpeta completa)

## 📞 Soporte

Si tienes problemas con:
- **Configuración de VERUMAX:** Contacta a tu proveedor de hosting
- **Base de datos SAJUR:** Accede a phpMyAdmin de SAJUR
- **Emails:** Verifica credenciales SMTP en config.php

---

**Sistema:** Gestión de Asistencias SAJUR
**Versión:** 1.0.0
**Última actualización:** 14/11/2025
