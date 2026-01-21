# Guía de Configuración de Entorno Local

Esta guía te ayudará a configurar tu entorno local de desarrollo para trabajar independientemente del servidor remoto.

## Requisitos Previos

- ✅ XAMPP instalado (C:\xampp)
- ✅ Apache y PHP funcionando
- ⚠️ MySQL por configurar

---

## Paso 1: Iniciar MySQL en XAMPP

1. Abre **XAMPP Control Panel** (`C:\xampp\xampp-control.exe`)
2. Haz clic en **Start** junto a "MySQL"
3. Verifica que el módulo MySQL cambie a color verde
4. Si el puerto 3306 está ocupado, detén otros servicios MySQL

---

## Paso 2: Crear Bases de Datos Locales

### Opción A: Usando el script automatizado (Recomendado)

1. Abre tu navegador
2. Ve a: `http://localhost/setup_local_databases.php`
3. El script creará automáticamente las 3 bases de datos vacías:
   - `verumax_general`
   - `verumax_certifi`
   - `verumax_identi`

### Opción B: Manualmente en phpMyAdmin

1. Abre `http://localhost/phpmyadmin`
2. Haz clic en "Nueva" para crear base de datos
3. Crea estas 3 bases de datos:
   - Nombre: `verumax_general`, Cotejamiento: `utf8mb4_unicode_ci`
   - Nombre: `verumax_certifi`, Cotejamiento: `utf8mb4_unicode_ci`
   - Nombre: `verumax_identi`, Cotejamiento: `utf8mb4_unicode_ci`

---

## Paso 3: Exportar Datos desde Servidor Remoto

Accede a tu **phpMyAdmin remoto** y exporta cada base de datos:

### Para cada base de datos (verumax_general, verumax_certifi, verumax_identi):

1. Selecciona la base de datos en el panel izquierdo
2. Haz clic en la pestaña **"Exportar"**
3. Selecciona:
   - **Método:** Rápido
   - **Formato:** SQL
4. Haz clic en **"Continuar"**
5. Guarda el archivo (ej: `verumax_general.sql`)

**Deberías tener 3 archivos:**
- `verumax_general.sql`
- `verumax_certifi.sql`
- `verumax_identi.sql`

---

## Paso 4: Importar Datos en Local

Accede a tu **phpMyAdmin local** (`http://localhost/phpmyadmin`):

### Para cada archivo SQL:

1. Selecciona la base de datos correspondiente en el panel izquierdo
2. Haz clic en la pestaña **"Importar"**
3. Haz clic en **"Seleccionar archivo"** y elige el archivo .sql correspondiente
4. Haz clic en **"Continuar"**
5. Espera a que termine la importación

**Importante:** Asegúrate de importar cada archivo a su base de datos correspondiente:
- `verumax_general.sql` → base de datos `verumax_general`
- `verumax_certifi.sql` → base de datos `verumax_certifi`
- `verumax_identi.sql` → base de datos `verumax_identi`

---

## Paso 5: Configurar Archivo de Entorno

Elige qué archivo de configuración usar:

### Para trabajar en LOCAL:
```bash
# En la raíz del proyecto (E:\appVerumax)
# Renombrar .env.local a .env
```

Esto configurará las credenciales locales:
- Host: `localhost`
- Usuario: `root`
- Contraseña: *(vacía)*

### Para trabajar en REMOTO:
```bash
# Renombrar .env.remoto a .env
```

---

## Paso 6: Verificar Configuración

Ejecuta el script de prueba completo:

1. Abre tu navegador
2. Ve a: `http://localhost/test_local_complete.php`
3. Verifica que todos los tests pasen ✅

El script verificará:
- ✅ Archivo .env configurado
- ✅ MySQL corriendo
- ✅ Conexión a las 3 bases de datos
- ✅ Existencia de tablas principales
- ✅ Cantidad de registros

---

## Archivos Importantes

### Archivos de Configuración Creados

| Archivo | Descripción |
|---------|-------------|
| `.env.local` | Credenciales para entorno local (XAMPP) |
| `.env.remoto` | Credenciales para servidor remoto |
| `env_loader.php` | Cargador de configuración que lee .env |
| `config_local.php` | Configuración global local |
| `general/config_local.php` | Configuración local de verumax_general |

### Scripts de Utilidad

| Script | Propósito |
|--------|-----------|
| `setup_local_databases.php` | Crea las 3 bases de datos vacías |
| `test_mysql_local.php` | Test básico de MySQL |
| `test_local_complete.php` | Test completo del entorno |

---

## Flujo de Trabajo Recomendado

### Desarrollo Local
1. Asegúrate de que `.env` apunte a local (renombrar `.env.local` a `.env`)
2. Inicia MySQL desde XAMPP Control Panel
3. Trabaja normalmente en tu código
4. Prueba todo localmente

### Subir a Producción
1. Haz backup de tus cambios locales
2. Sube los archivos modificados via FileZilla
3. En el servidor remoto, asegúrate de que `.env` apunte a remoto (o usa los config.php originales)
4. **NUNCA** subir el archivo `.env.local` al servidor

---

## Solución de Problemas

### Error: "MySQL no está corriendo"
- Abre XAMPP Control Panel
- Inicia el módulo MySQL
- Si falla, verifica que el puerto 3306 no esté ocupado

### Error: "Base de datos no existe"
- Ejecuta `setup_local_databases.php`
- Verifica que las 3 bases de datos existen en phpMyAdmin local

### Error: "Tabla no existe"
- Asegúrate de haber importado los archivos .sql correctamente
- Verifica que importaste cada archivo a su base de datos correspondiente

### Error: "Access denied for user 'root'"
- Verifica que `.env` tenga las credenciales correctas
- En XAMPP local, el password de root suele estar vacío

---

## Cambiar entre Local y Remoto

### Trabajar en LOCAL:
```bash
# 1. Renombrar archivo
ren .env.remoto .env.backup
ren .env.local .env

# 2. Iniciar MySQL en XAMPP
# 3. ¡Listo para trabajar!
```

### Volver a REMOTO:
```bash
# 1. Renombrar archivo
ren .env .env.local.backup
ren .env.remoto .env

# 2. Subir archivos modificados via FileZilla
```

---

## Ventajas del Entorno Local

✅ **Velocidad**: No dependes de la conexión a internet
✅ **Seguridad**: Datos sensibles no viajan por la red
✅ **Pruebas**: Experimenta sin afectar producción
✅ **Backup**: Tienes copia local de las bases de datos
✅ **SQL**: Puedes hacer queries directamente en phpMyAdmin local

---

## Notas Importantes

⚠️ **NUNCA** subir archivos `.env*` al servidor remoto
⚠️ **SIEMPRE** hacer backup antes de modificar bases de datos
⚠️ **VERIFICAR** qué archivo .env está activo antes de trabajar
⚠️ **SINCRONIZAR** bases de datos periódicamente (exportar/importar)

---

## Contacto y Soporte

Si tienes problemas con la configuración:
1. Ejecuta `test_local_complete.php` y revisa los errores
2. Verifica que MySQL esté corriendo en XAMPP
3. Asegúrate de haber importado las 3 bases de datos correctamente
