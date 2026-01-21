# ROADMAP - Verumax

## Funcionalidades Planificadas

### 1. Sistema Multi-idioma para Landing Page

**Estado**: Pendiente
**Prioridad**: Media
**Última actualización**: 2025-10-06

#### Descripción

Implementar sistema de traducción multi-idioma únicamente para la página principal (`index.html`) para expandir el servicio a diferentes países de Latinoamérica y España.

#### Estructura Técnica

```
validarcert/
├── index.html → index.php (convertir a PHP)
├── lang/
│   ├── es_AR.php  (Español Argentina)
│   ├── es_UY.php  (Español Uruguay)
│   ├── es_ES.php  (Español España)
│   ├── pt_BR.php  (Portugués Brasil)
│   └── ca_ES.php  (Catalán España - opcional futuro)
```

#### Características a Implementar

1. **Detección automática de idioma**
   
   - Detectar idioma del navegador (`$_SERVER['HTTP_ACCEPT_LANGUAGE']`)
   - Usar como idioma predeterminado al cargar la página
   - Fallback a `es_AR` si el idioma no está disponible

2. **Selector manual de país/idioma**
   
   - Banderas de países en el header de `index.php`
   - Ejemplos: 🇦🇷 Argentina | 🇧🇷 Brasil | 🇺🇾 Uruguay | 🇪🇸 España
   - Cambio instantáneo de contenido al seleccionar

3. **Persistencia de preferencia**
   
   - Guardar selección en cookie
   - Duración: 30 días
   - Al regresar a la página, mostrar idioma previamente seleccionado

4. **Archivos de idioma**
   
   - Formato: `lang/{idioma}_{pais}.php`
   - Contienen array `$lang` con todas las traducciones
   - Estructura ejemplo:
     
     ```php
     <?php
     $lang = [
       'hero_title' => 'Valida tus certificados educativos',
       'hero_subtitle' => 'Plataforma segura y confiable',
       'how_it_works' => 'Cómo funciona',
       'validate_now' => 'Validar ahora',
       'country_selector' => 'Selecciona tu país',
       // ...
     ];
     ```

#### Alcance

**Incluido**:

- ✅ Landing page principal (`index.php`) con selector de idioma
- ✅ Detección automática + cambio manual
- ✅ Persistencia via cookies
- ✅ Soporte para español (AR, UY, ES) y portugués (BR)

**NO Incluido**:

- ❌ Portales de instituciones individuales (mantienen idioma nativo fijo)
- ❌ Panel de validación (`validar.php`, `vista_validacion.php`)
- ❌ Generación de documentos
- ❌ Traducciones en tiempo real via base de datos

#### Notas de Implementación

- Las instituciones (`sajur/`, futuras instituciones) mantendrán su idioma nativo sin selector
- Cada institución tendrá configurado su idioma en `config.php` (ej: `idioma_fijo: 'es'`, `pais: 'AR'`)
- El sistema multi-idioma es **exclusivo** para la landing page de marketing

#### Contenido Regional

Cuando se implemente el sistema multi-idioma, ciertas secciones del sitio solo se mostrarán en regiones específicas:

- **FAQ de Impresión Física** (Argentina solamente):
  - Pregunta: "¿Qué pasa si necesito una versión física del documento?"
  - Respuesta sobre servicio premium de impresión con envío a toda Argentina
  - Elemento HTML marcado con `data-region="AR"`
  - JavaScript del multi-idioma deberá mostrar/ocultar según país seleccionado

---

---

### 2. Perfil para Coaches

**Estado**: Planificado
**Prioridad**: Alta
**Última actualización**: 2025-10-12

#### Descripción

Solución similar a TarjetaDigital pero orientada específicamente a coaches (de vida, ejecutivos, deportivos, nutricionales, etc.) con características especializadas para su industria.

#### Características

- Landing page adaptada con metodología de coaching
- Sección de especialidades y certificaciones
- Calendario de disponibilidad
- Sistema de reserva de sesiones
- Testimonios de clientes/coachees
- Blog/Recursos descargables
- Integración con pagos

---

### 3. PetCard - Tarjeta Digital para Mascotas

**Estado**: Planificado
**Prioridad**: Media
**Última actualización**: 2025-10-12

#### Descripción

Perfil digital completo para mascotas, orientado a dueños responsables que desean mantener toda la información de su mascota organizada y accesible.

#### Características Principales

**Información Básica:**

- Datos de la mascota (nombre, raza, edad, peso)
- Galería de fotos y videos
- Microchip / ID de identificación
- Datos del dueño y contactos de emergencia

**Salud y Cuidados:**

- 📅 Libreta de vacunación digital
  - Historial completo de vacunas
  - Recordatorios automáticos
  - Certificados descargables
- 🏥 Historial médico completo
  - Visitas veterinarias
  - Diagnósticos y tratamientos
  - Medicación actual
  - Alergias y condiciones
- 📊 Control de peso y medidas con gráficos

**Características de Seguridad:**

- Código QR para collar/placa de identificación
- Acceso rápido a información de contacto
- Notificación si mascota perdida
- Perfil público/privado configurable

#### Planes

- **Basicum** ($9.99/mes): 1 mascota, perfil básico, galería 50 fotos
- **Premium** ($19.99/mes): 3 mascotas, historial completo, recordatorios
- **Pro** ($29.99/mes): Mascotas ilimitadas, análisis, integración veterinarias
- **Elite** ($49.99/mes): Todo + consultas online, seguro, GPS

#### Casos de Uso

1. Dueño mantiene info de salud actualizada
2. Mascota perdida: quien la encuentra escanea QR y contacta
3. Visita veterinaria: muestra historial completo
4. Viajes: certificados siempre disponibles
5. Múltiples mascotas: gestión centralizada

---

---

## Tareas Técnicas / Refactoring

### 1. Unificar tablas de instancias

**Estado**: Pendiente
**Prioridad**: Alta
**Última actualización**: 2025-12-22

#### Problema Actual

Existen dos tablas de instancias en bases de datos diferentes, causando confusión y duplicación:

| Tabla                 | Base de datos     | Usada por                     |
| --------------------- | ----------------- | ----------------------------- |
| `identitas_instances` | `verumax_identi`  | Login admin, módulo identitas |
| `instances`           | `verumax_general` | Certificatum, servicios PSR-4 |

#### Solución Propuesta

1. **Migrar datos** de `verumax_identi.identitas_instances` a `verumax_general.instances`
2. **Actualizar archivos PHP** que usan `identitas_instances`:
   - `admin/login.php`
   - `admin/modulos/identitas_templates.php`
   - `admin/debug_sobre_nosotros.php`
   - `admin/debug_templates.php`
   - `identitas/administrare.php`
   - `identitas/login.php`
   - `identitas/test_password.php`
3. **Eliminar tabla** `verumax_identi.identitas_instances`

#### Pasos de Implementación

```sql
-- 1. Verificar que los datos de SAJuR existen en verumax_general.instances
SELECT * FROM verumax_general.instances WHERE slug = 'sajur';

-- 2. Si no existe, insertar desde identitas_instances
INSERT INTO verumax_general.instances (slug, nombre, ...)
SELECT slug, nombre, ...
FROM verumax_identi.identitas_instances
WHERE slug = 'sajur';

-- 3. Actualizar archivos PHP (cambiar identitas_instances por verumax_general.instances)

-- 4. Eliminar tabla vieja
DROP TABLE verumax_identi.identitas_instances;
```

#### Archivos a Modificar

Cambiar `FROM identitas_instances` por `FROM verumax_general.instances`:

```php
// Antes
$stmt = $pdo->query("SELECT * FROM identitas_instances WHERE slug = 'sajur'");

// Después
$stmt = $pdo->query("SELECT * FROM verumax_general.instances WHERE slug = 'sajur'");
```

#### Notas

- Asegurar que `verumax_general.instances` tenga todos los campos necesarios (admin_usuario, admin_password, etc.)
- Verificar que las conexiones en cada archivo tengan acceso a `verumax_general`

---

---

### 2. Programa Beta VERUMax

**Estado**: Planificado
**Prioridad**: Alta
**Última actualización**: 2025-12-26

#### Descripción

Sistema para ofrecer acceso 100% bonificado a instituciones que participen en el programa Beta, a cambio de retroalimentación y sugerencias de mejora. Permite control granular para sacar soluciones de Beta independientemente.

#### Características Principales

**Indicador Visual Beta:**

- Badge "Beta v2.0-beta" visible en header y footer
- Mostrado dinámicamente según estado de cada solución
- Desaparece automáticamente cuando la solución sale de Beta

**Control por Solución:**

- Cada solución (Certificatum, Identitas, Lumen) tiene su propio estado Beta
- Posibilidad de sacar una solución de Beta sin afectar las demás
- Ejemplo: Certificatum puede salir de Beta mientras Identitas sigue en Beta

**Gestión de Instituciones:**

- Invitación manual desde panel admin (sin landing pública)
- Sin límite de cupo
- Selección de qué soluciones Beta habilitar por institución

**Sistema de Feedback Simple:**

- Formulario: tipo (bug/sugerencia/mejora/otro), título, descripción
- Accesible solo para instituciones con `es_beta = 1`
- Panel admin para revisar y responder feedback

#### Modelo de Datos

```sql
-- Estado Beta por solución
CREATE TABLE beta_solutions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    solucion VARCHAR(50) UNIQUE NOT NULL,  -- certificatum, identitas, lumen
    nombre_display VARCHAR(100) NOT NULL,
    es_beta TINYINT(1) DEFAULT 1,
    version_beta VARCHAR(20) DEFAULT '1.0',
    fecha_salida_beta DATE NULL,
    orden INT DEFAULT 0
);

-- Instituciones participantes
CREATE TABLE beta_instances (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_instancia INT NOT NULL UNIQUE,
    estado ENUM('activo', 'pausado', 'egresado') DEFAULT 'activo',
    fecha_inscripcion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    soluciones_beta_json JSON,  -- {"certificatum": true, "identitas": true}
    contacto_nombre VARCHAR(200),
    contacto_email VARCHAR(255),
    notas_admin TEXT,
    FOREIGN KEY (id_instancia) REFERENCES instances(id_instancia)
);

-- Feedback simple
CREATE TABLE beta_feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_instancia INT NOT NULL,
    solucion VARCHAR(50) NOT NULL,
    tipo ENUM('bug', 'sugerencia', 'mejora', 'otro') DEFAULT 'sugerencia',
    titulo VARCHAR(255) NOT NULL,
    descripcion TEXT NOT NULL,
    estado ENUM('nuevo', 'revisado', 'resuelto') DEFAULT 'nuevo',
    respuesta_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_instancia) REFERENCES instances(id_instancia)
);

-- Agregar a tabla instances
ALTER TABLE instances ADD COLUMN es_beta TINYINT(1) DEFAULT 0;
ALTER TABLE instances ADD COLUMN beta_desde TIMESTAMP NULL;
```

#### Archivos a Crear

| Archivo                                      | Descripción                          |
| -------------------------------------------- | ------------------------------------ |
| `src/VERUMax/Services/BetaService.php`       | Servicio con lógica de negocio       |
| `templates/shared/components/beta_badge.php` | Componente visual del badge          |
| `admin/modulos/beta.php`                     | Panel administrativo completo        |
| `beta/feedback.php`                          | Formulario de feedback para clientes |

#### Archivos a Modificar

| Archivo                       | Cambio                           |
| ----------------------------- | -------------------------------- |
| `templates/shared/header.php` | Mostrar badge Beta               |
| `templates/shared/footer.php` | Indicador Beta + link feedback   |
| `admin/index.php`             | Agregar tab "Beta" en navegación |

#### Panel Admin - Secciones

1. **Dashboard**: Instituciones activas, feedback pendiente, soluciones en Beta
2. **Instituciones Beta**: Agregar/quitar instituciones, seleccionar soluciones
3. **Soluciones**: Toggle Beta por solución, editar versión, "Sacar de Beta"
4. **Feedback**: Lista de feedback, filtros, marcar resuelto, responder

#### Datos Iniciales

```sql
INSERT INTO beta_solutions (solucion, nombre_display, es_beta, version_beta, orden) VALUES
('certificatum', 'Certificatum', 1, '2.0-beta', 1),
('identitas', 'Identitas', 1, '2.0-beta', 2),
('lumen', 'Lumen', 1, '1.0-beta', 3);
```

#### Implementación Segura (No afecta producción)

**Impacto en instituciones existentes (ej: SAJuR): NINGUNO**

El plan es 100% aditivo y no invasivo:

| Tipo de cambio           | Impacto en producción                            |
| ------------------------ | ------------------------------------------------ |
| Tablas nuevas (`beta_*`) | Ninguno - no modifican tablas existentes         |
| Columnas en `instances`  | Ninguno - `es_beta DEFAULT 0`, `beta_desde NULL` |
| BetaService.php          | Ninguno - servicio nuevo independiente           |
| Módulo admin beta.php    | Ninguno - módulo nuevo                           |
| Cambios en header/footer | Condicionales - solo muestran si `es_beta = 1`   |

**Con `es_beta = 1` (institución participa en Beta):**

| Afectado               | Qué cambia                                      |
| ---------------------- | ----------------------------------------------- |
| Header                 | Muestra badge "Beta v2.0-beta" (solo visual)    |
| Footer                 | Muestra indicador + link feedback (solo visual) |
| Certificados           | **NO** - generación intacta                     |
| Evaluaciones           | **NO** - lógica intacta                         |
| Validación QR          | **NO** - sin cambios                            |
| Analíticos/Constancias | **NO** - sin cambios                            |

**Orden de implementación recomendado:**

1. Crear tablas nuevas (sin tocar nada visual)
2. Crear BetaService.php
3. Agregar módulo admin/modulos/beta.php
4. Agregar condicionales en header/footer
5. Crear beta/feedback.php

Cada fase puede desplegarse independientemente sin afectar el funcionamiento actual.

---

### 2.1 Modo Test VERUMax (Pruebas Internas)

**Estado**: Planificado
**Prioridad**: Alta (después de Super Admin)
**Última actualización**: 2026-01-13

#### Descripción

Modo especial para que el equipo interno de VERUMax pueda simular ser un cliente real, probando todas las funcionalidades sin generar documentos válidos. Los certificados y documentos generados tendrán marcas de agua indicando que no tienen valor oficial.

#### Diferencia con Beta

| Aspecto | Modo Test | Modo Beta |
|---------|-----------|-----------|
| **Usuarios** | Solo equipo interno VERUMax | Instituciones reales externas |
| **Propósito** | Probar funcionalidades, demos | Validar producto con usuarios reales |
| **Documentos** | Watermark "SIN VALOR OFICIAL" | Válidos y funcionales |
| **Datos** | Ficticios, pre-cargados | Reales de la institución |
| **Validación QR** | Muestra "DOCUMENTO DE PRUEBA" | Validación normal |
| **Emails** | No se envían o van a correo interno | Se envían normalmente |
| **Duración** | Permanente (para demos/testing) | Temporal (hasta salir de Beta) |

#### Características del Modo Test

**1. Watermarks en Documentos:**

```
┌─────────────────────────────────────────────┐
│                                             │
│    ╔═══════════════════════════════════╗    │
│    ║   DOCUMENTO DE PRUEBA             ║    │
│    ║   SIN VALOR OFICIAL               ║    │
│    ║   SOLO PARA DEMOSTRACIÓN          ║    │
│    ╚═══════════════════════════════════╝    │
│                                             │
│         [Contenido del certificado]         │
│                                             │
└─────────────────────────────────────────────┘
```

- Watermark diagonal semi-transparente en PDFs
- Texto: "DOCUMENTO DE PRUEBA - SIN VALOR OFICIAL"
- Color: Rojo semi-transparente (#FF0000, opacity 15%)
- Aplicado a: Certificados, Analíticos, Constancias

**2. Indicadores Visuales en Admin:**

- Banner fijo superior: "⚠️ MODO TEST - Los documentos generados no tienen validez"
- Color de fondo del header: Amarillo/Naranja de advertencia
- Badge "TEST" junto al nombre de la institución

**3. Validación QR Especial:**

Cuando alguien escanea un QR de documento test:

```
┌─────────────────────────────────────────────┐
│  ⚠️ DOCUMENTO DE DEMOSTRACIÓN              │
│                                             │
│  Este documento fue generado en modo TEST   │
│  y NO tiene validez oficial.                │
│                                             │
│  Es solo para propósitos de demostración    │
│  del sistema VERUMax.                       │
│                                             │
│  Código: TEST-XXXX-XXXX                     │
└─────────────────────────────────────────────┘
```

**4. Emails en Modo Test:**

Opciones configurables:
- ❌ No enviar emails (solo simular)
- ✅ Enviar a correo interno de prueba (ej: test@verumax.com)
- ✅ Enviar normalmente pero con prefijo "[TEST]" en asunto

**5. Datos de Prueba Pre-cargados:**

Al crear instancia en modo Test, cargar automáticamente:
- 5 estudiantes ficticios (Juan Test, María Demo, etc.)
- 3 cursos de ejemplo
- 2 docentes de prueba
- 1 evaluación de muestra
- Inscripciones y notas de ejemplo

#### Modelo de Datos

```sql
-- Agregar a tabla instances
ALTER TABLE instances ADD COLUMN es_test TINYINT(1) DEFAULT 0;
ALTER TABLE instances ADD COLUMN test_email_destino VARCHAR(255) NULL;
ALTER TABLE instances ADD COLUMN test_enviar_emails ENUM('no', 'interno', 'normal_prefijo') DEFAULT 'no';

-- Prefijo para códigos de validación en modo test
-- Normal: CERT-ABCD1234
-- Test:   TEST-ABCD1234
```

#### Archivos a Modificar

| Archivo | Cambio |
|---------|--------|
| `certificatum/creare_pdf.php` | Agregar watermark si `es_test = 1` |
| `certificatum/creare_pdf_tcpdf.php` | Agregar watermark si `es_test = 1` |
| `certificatum/verificatio.php` | Mostrar mensaje de documento test |
| `certificatum/validare.php` | Detectar códigos TEST-* |
| `src/VERUMax/Services/EmailService.php` | Respetar config de emails test |
| `src/VERUMax/Services/CertificateService.php` | Generar códigos con prefijo TEST- |
| `admin/includes/header.php` | Mostrar banner de modo test |

#### Archivos a Crear

| Archivo | Descripción |
|---------|-------------|
| `src/VERUMax/Services/TestModeService.php` | Lógica de modo test |
| `src/VERUMax/Services/TestDataSeeder.php` | Generador de datos ficticios |

#### Casos de Uso

1. **Demo a cliente potencial**: Mostrar todas las funcionalidades sin crear datos reales
2. **Testing de nuevas features**: Probar cambios antes de aplicar a producción
3. **Capacitación**: Entrenar nuevos miembros del equipo
4. **Screenshots/Videos**: Material de marketing sin datos sensibles
5. **QA**: Pruebas de regresión antes de deploys

#### Implementación Segura

**Impacto en producción: NINGUNO**

- Clientes existentes tienen `es_test = 0` por defecto
- Los cambios en generación de PDF son condicionales
- La validación QR detecta el prefijo TEST- automáticamente

---

### 3. Notificaciones Automáticas de Estadísticas de Email

**Estado**: UI implementada, Backend pendiente
**Prioridad**: Media
**Última actualización**: 2026-01-02

#### Descripción

Sistema de envío automático de reportes de estadísticas de email a los administradores de instituciones. La interfaz de configuración ya existe en el panel admin (pestaña Emails → Notificaciones), pero falta el cron job que envíe los reportes.

#### Estado Actual

**Implementado:**

- ✅ UI de configuración en `admin/modulos/email_stats.php`
- ✅ Guardado de preferencias en `email_config` (notificar_estadisticas, notificar_email, notificar_frecuencia, notificar_rebotes_alta)
- ✅ Columnas en BD para configuración

**Pendiente:**

- ❌ Script cron `cron/enviar_reportes_email.php`
- ❌ Template de email para reportes
- ❌ Lógica de frecuencia (diario/semanal/mensual)
- ❌ Alertas por tasa de rebote alta (>5%)

#### Archivos a Crear

| Archivo                          | Descripción                           |
| -------------------------------- | ------------------------------------- |
| `cron/enviar_reportes_email.php` | Script principal del cron             |
| `email_templates` (INSERT)       | Template `reporte_estadisticas_email` |

#### Lógica del Cron

```php
// Pseudocódigo
1. Obtener instancias con notificar_estadisticas = 1
2. Para cada instancia:
   a. Verificar si toca enviar según frecuencia y última notificación
   b. Calcular estadísticas del período
   c. Si notificar_rebotes_alta = 1 y tasa > 5%, agregar alerta
   d. Renderizar template con estadísticas
   e. Enviar email usando EmailService
   f. Registrar en email_notification_history
```

#### Configuración del Cron (servidor)

```bash
# Ejecutar diariamente a las 8:00 AM
0 8 * * * php /path/to/cron/enviar_reportes_email.php
```

---

## Funcionalidades Futuras (Sin Planificar)

- API REST para validación de certificados
- Integración con blockchain para certificados
- App móvil (Android/iOS)
- Sistema de firma digital avanzada
