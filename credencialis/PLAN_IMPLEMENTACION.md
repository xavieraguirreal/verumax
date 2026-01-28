# Plan: Credencialis - Módulo Admin (Panel de Gestión)

## Estado General
**Última actualización:** 2026-01-28

> **Nota:** El plan de la landing page está en `PLAN_LANDING.md`

---

## Módulo Admin (Panel de Gestión)

### Principio Clave
**Los miembros son compartidos en Nexus.** Una persona puede ser estudiante Y socio. Este módulo NO duplica la gestión de personas - solo gestiona datos de credencial para personas existentes.

---

## Archivos a Crear/Modificar

### 1. CREAR: `admin/modulos/credencialis.php` (~800 líneas)
Módulo principal con:

**Tabs:**
- **Socios**: Lista miembros con filtro (todos/con credencial/sin credencial), búsqueda, tabla con acciones
- **Configuración**: Subir template JPG, textos (banner/footer), opciones (mostrar foto, vigencia)

**Acciones POST:**
- `asignar_credencial` - Asignar datos de credencial a miembro existente
- `quitar_credencial` - Limpiar datos de credencial
- `asignar_masivo` - Carga masiva desde CSV
- `guardar_config` - Guardar credencial_config JSON
- `subir_template` - Upload de JPG

**Modales:**
- Asignar/Editar credencial (campos: numero_asociado, tipo_asociado, categoria_servicio, fecha_ingreso, foto)
- Confirmar quitar credencial
- Asignación masiva (textarea CSV)

### 2. MODIFICAR: `admin/index.php`
- Agregar tab "Credencialis" en navegación (línea ~185)
- Agregar acciones AJAX al whitelist (línea ~44)

### 3. MODIFICAR: `src/VERUMax/Services/MemberService.php`
- Agregar método `getConCredencial($id_instancia, $filtro, $buscar)` para listar miembros con estado de credencial

### 4. CREAR: Directorios de uploads
```
uploads/credenciales/templates/   # JPG templates
uploads/credenciales/fotos/       # Fotos de socios
```

---

## Flujo de Usuario

```
Admin entra a Credencialis
    ↓
Ve dashboard: Total miembros | Con credencial | Sin credencial
    ↓
Tab Socios:
    - Filtra por estado de credencial
    - Busca por DNI/nombre/N° asociado
    - Click "+" para asignar credencial a persona sin credencial
    - Click "editar" para modificar datos de credencial
    - Click "X" para quitar credencial
    - "Asignación Masiva" para cargar CSV
    ↓
Tab Configuración:
    - Sube template JPG (opcional)
    - Configura texto superior/inferior
    - Activa/desactiva foto de socio
    - Define vigencia en meses
    - Guarda configuración
```

---

## Campos de Credencial (ya existen en miembros)

| Campo | Tipo | Descripción |
|-------|------|-------------|
| numero_asociado | VARCHAR(50) | N° de socio (ej: "11023") |
| tipo_asociado | VARCHAR(50) | TITULAR, ADHERENTE, INST., etc. |
| nombre_entidad | VARCHAR(200) | Entidad si pertenece a una |
| categoria_servicio | VARCHAR(100) | BÁSICO, PREMIUM, VIP |
| fecha_ingreso | DATE | Fecha de alta como socio |
| foto_url | VARCHAR(500) | URL de foto del socio |

---

## Orden de Implementación

- [ ] 1. Crear directorios de uploads
- [ ] 2. Agregar método `getConCredencial()` a MemberService
- [ ] 3. Crear `admin/modulos/credencialis.php` completo
- [ ] 4. Modificar `admin/index.php` para agregar tab
- [ ] 5. Probar flujo completo
- [ ] 6. Push a producción

---

## Verificación

1. Entrar a `https://sajur.verumax.com/admin/?modulo=credencialis`
2. Ver lista de miembros con Jorge Aguilar (ya tiene credencial)
3. Filtrar por "Sin credencial" - ver miembros pendientes
4. Asignar credencial a un miembro sin credencial
5. Verificar que aparece en `https://sajur.verumax.com/credencialis/`
6. Probar subir template JPG en Configuración
7. Probar asignación masiva con CSV

---

## Archivos de Referencia (patrones a seguir)

- `admin/modulos/certificatum.php` - Estructura de tabs, modales, acciones
- `certificatum/templates/credencial.php` - Template de credencial
- `src/VERUMax/Services/MemberService.php` - Métodos existentes

---

## Notas de Producción

⚠️ **IMPORTANTE**: No modificar archivos de Certificatum existentes. Los clientes actuales no deben verse afectados.

- Seguir mismo estilo visual que Certificatum
- Usar mismos patrones de código
- Módulo 100% independiente
