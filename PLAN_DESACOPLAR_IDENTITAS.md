# Plan: Desacoplar Identitas del Core VERUMax

**Fecha inicio:** 2026-01-04
**Fecha fin:** 2026-01-05
**Estado:** ✅ COMPLETADO

---

## Objetivo

Reestructurar la arquitectura para que:
- **VERUMax** sea el core/plataforma principal
- **Identitas** sea un módulo opcional (landing pages)
- **Certificatum** sea un módulo opcional (certificados)

Esto permitirá crear nuevos clientes sin depender de módulos que no usen.

---

## Arquitectura Actual (Problema)

```
sajur/index.php ──────┐
sajur/header.php ─────┼──→ identitas/config.php ──→ getInstanceConfig()
sajur/footer.php ─────┘

Problema: getInstanceConfig() está en identitas/ pero es función CORE
```

---

## Arquitectura Propuesta

```
                     ┌─────────────────────────────────────┐
                     │         verumax/config.php          │
                     │  (core: getInstanceConfig, etc.)    │
                     └─────────────────────────────────────┘
                                      ↑
           ┌──────────────────────────┼──────────────────────────┐
           │                          │                          │
    sajur/*.php               identitas/              certificatum/
    (usa core)                (módulo opcional)       (módulo opcional)
```

---

## Fases del Plan

### ✅ Fase 1: Preparación (COMPLETADA 2026-01-04)
- [x] Crear carpeta `verumax/`
- [x] Crear `verumax/config.php` con funciones core:
  - `getInstanceConfig()` - obtiene config de institución
  - `getLogoClasses()` - genera clases CSS para logos
- [x] Crear `verumax/test_config.php` para verificar funcionamiento
- [x] Subir al servidor y probar - **TODO OK**

---

### ✅ Fase 2: Migrar header.php (COMPLETADA 2026-01-05)
- [x] Backup de `sajur/header.php` → `backup/2026-01-05/1737-header.php`
- [x] Cambiar `require identitas/config.php` → `require verumax/config.php`
- [x] Probar que el header funciona

---

### ✅ Fase 3: Migrar footer.php (COMPLETADA 2026-01-05)
- [x] Backup de `sajur/footer.php` → `backup/2026-01-05/1737-footer.php`
- [x] Mismo cambio que header
- [x] Probar

---

### ✅ Fase 4: Evaluar index.php (COMPLETADA 2026-01-05)
- [x] Analizar dependencias de `sajur/index.php`
- [x] Backup → `backup/2026-01-05/1737-index.php`
- [x] Migrar línea 13 (`require identitas/config.php` → `require verumax/config.php`)
- [x] Mantener `IdentitasEngine` (líneas 97-100) - es uso legítimo del módulo cuando está activo
- [x] Actualizar documentación del archivo (versión 3.0)

**Resultado:** index.php ya no depende de identitas/config.php para getInstanceConfig(), pero sigue usando IdentitasEngine cuando el módulo está activo (comportamiento correcto).

---

### ✅ Fase 5: Limpieza (COMPLETADA 2026-01-05)
- [ ] Eliminar `verumax/test_config.php` del servidor (pendiente confirmación usuario)
- [x] Documentar cambios en este archivo

---

## Checkpoint de Seguridad

```
Fase completada → Probar en producción → ¿Funciona?
                                            │
                        ┌───────────────────┴───────────────────┐
                        ↓                                       ↓
                       SÍ → Siguiente fase                    NO → Rollback
```

---

## Notas Importantes

1. **Certificatum NO se ve afectado** - ya usa `InstitutionService::getConfig()` directo
2. **Identitas sigue funcionando** - no se modifica `identitas/config.php`
3. **Rollback fácil** - si algo falla, solo revertir el archivo modificado

---

## Resultado Final

✅ **Plan completado exitosamente**

La carpeta `sajur/` ya no depende de `identitas/config.php` para funciones core.
Ahora usa `verumax/config.php` que contiene las funciones compartidas.

**Dependencias actuales de sajur/:**
- `verumax/config.php` → Core (getInstanceConfig, getLogoClasses)
- `identitas/identitas_engine.php` → Solo si módulo Identitas está activo (uso legítimo)
- `certificatum/templates/solo.php` → Solo si módulo Certificatum activo sin Identitas

**Para crear un nuevo cliente sin Identitas:**
1. Copiar estructura de `sajur/`
2. Los archivos ya usan `verumax/config.php` (no hay dependencia de Identitas)
3. Si el cliente no usa landing pages, `identitas_engine.php` nunca se carga

---

## Archivos Relacionados

| Archivo | Rol | Estado |
|---------|-----|--------|
| `verumax/config.php` | Config core VERUMax | ✅ Activo |
| `verumax/test_config.php` | Test de config | 🗑️ Eliminar del servidor |
| `identitas/config.php` | Config módulo Identitas | ⚠️ Duplicado (mantener por ahora) |
| `certificatum/config.php` | Config módulo Certificatum | ✅ Independiente |
| `sajur/header.php` | Wrapper header | ✅ Migrado |
| `sajur/footer.php` | Wrapper footer | ✅ Migrado |
| `sajur/index.php` | Punto de entrada | ✅ Migrado |

## Backups Creados

```
backup/2026-01-05/
├── 1737-header.php
├── 1737-footer.php
└── 1737-index.php
```
