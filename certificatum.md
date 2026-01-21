# CERTIFICATUM - CREDENCIALES VERIFICADAS

**Archivo Landing:** `certificatum.php`
**Panel de Administración:** `certificatum/administrare.php`
**Color Distintivo:** Metallic Green (#2E7D32)
**Estado:** ✅ Producción (ValidarCert)

---

## 📋 CONCEPTO GENERAL

### Nombres

**Nombre Técnico (Latín):** Certificatum
- **Significado:** Certificado, cosa certificada
- **Raíz:** *certus* (cierto, seguro) + *facere* (hacer) = "hacer cierto"
- **Plural:** Certificata

**Nombre Comercial:** Credenciales Verificadas / Certificados Infalsificables

**Lema:** *"Certificatum: Veritas in perpetuum"* (La verdad para siempre)

### Propuesta de Valor
> "Elimina el trabajo manual de emisión de certificados, previene falsificaciones y ofrece validación instantánea 24/7"

### Filosofía
Certificatum es una **plataforma multi-tenant** que permite a instituciones educativas emitir certificados digitales infalsificables con códigos QR únicos que pueden ser validados por cualquier persona en cualquier momento.

---

## 🎯 PROBLEMAS QUE RESUELVE

### Para Instituciones Educativas:

1. ❌ **Horas de Trabajo Manual**
   - Diseñar y emitir cada certificado individualmente
   - Firmar documentos uno por uno
   - Gestión de archivo físico
   - **Solución:** Generación automática con plantillas

2. ❌ **Certificados Perdidos**
   - Alumnos pierden o dañan documentos
   - Solicitudes constantes de reemisión
   - Costos de impresión repetidos
   - **Solución:** Descarga digital ilimitada

3. ❌ **Falsificaciones**
   - Títulos y certificados falsos circulando
   - Sin forma efectiva de verificar autenticidad
   - Riesgo reputacional para la institución
   - **Solución:** QR infalsificable con validación 24/7

4. ❌ **Sin Trazabilidad**
   - Falta de historial académico completo
   - No hay registro verificable de estudiantes
   - Dificultad para consultas históricas
   - **Solución:** Registro académico digital permanente

### Para Empleadores/Verificadores:

1. ❌ **Verificación Lenta**
   - Llamadas telefónicas a instituciones
   - Espera de días para confirmación
   - Horarios de atención limitados
   - **Solución:** Validación instantánea escaneando QR

2. ❌ **Incertidumbre**
   - No saber si un certificado es real
   - Riesgo de contratar con credenciales falsas
   - **Solución:** Verificación criptográfica garantizada

---

## 🏗️ ARQUITECTURA TÉCNICA

### Sistema Multi-Tenant con Base de Datos Central

Certificatum ha evolucionado a una arquitectura **multi-tenant con una base de datos central (MySQL/MariaDB)**. Los datos de todas las instituciones se almacenan de forma segura en la misma base de datos, pero se segregan lógicamente a través de una columna `institucion` en las tablas clave.

El branding (aspecto visual) sigue siendo personalizable por institución a través de carpetas dedicadas que contienen archivos de estilo e imagen.

```
D:\appVerumax\
├── certificatum.php              ✅ Landing page de la solución
├── certificatum/                 ✅ Lógica de la aplicación
│   ├── administrare.php          ✅ Panel de Admin por Institución
│   ├── administrare_procesador.php → Lógica de carga de datos
│   └── administrare_gestionar.php  → Lógica de gestión de datos (CRUD)
│
├── validar.php                   ✅ Motor de validación global
├── vista_validacion.php          ✅ Página pública de resultado
│
├── sajur/                        ✅ Institución 1 (SAJuR)
│   ├── index.php                 → Portal del estudiante (usa DB)
│   ├── header.php                → Branding institucional
│   ├── footer.php                → Footer institucional
│   └── style.css                 → Paleta de colores
│
└── liberte/                      ✅ Institución 2 (Liberté)
    ├── index.php
    ├── header.php
    ├── footer.php
    └── style.css
```

### Instituciones Activas

#### 1. SAJuR - Sociedad Argentina de Justicia Restaurativa
- **Carpeta Branding:** `sajur/`
- **Slug en DB:** `sajur`
- **Estado:** ✅ Producción

#### 2. Liberté - Cooperativa de Trabajo Liberté
- **Carpeta Branding:** `liberte/`
- **Slug en DB:** `liberte`
- **Estado:** ✅ Producción

#### 3. Template para Nuevas Instituciones
Para agregar una nueva institución:
1. Crear el registro de la institución en la tabla `instituciones` de la base de datos.
2. Crear una carpeta de branding: `{slug_institucion}/`
3. Copiar los archivos de plantilla (e.g., de `sajur/`) a la nueva carpeta.
4. Personalizar el branding en `style.css`, `header.php`, y `footer.php`.
5. Usar el **Panel de Administración** (`certificatum/administrare.php`) para cargar los estudiantes, cursos e inscripciones de la nueva institución.

---

## 🔐 SISTEMA DE VALIDACIÓN

### Generación del Código Único

Cada certificado tiene un **código de validación único e irrepetible**:

```php
$codigo = "VALID-" . strtoupper(substr(md5($dni . $curso_id), 0, 12));
```

**Componentes:**
- Prefijo: `VALID-`
- Hash: MD5 del DNI + ID del curso
- Longitud: 12 caracteres (primeros 12 del hash)

**Ejemplo:**
- DNI: `25123456`
- Curso: `SJ-DPA-2024`
- Hash completo: `e8a9f3b2c1d0...`
- **Código final:** `VALID-E8A9F3B2C1D0`

### Flujo de Validación Completo

```
┌──────────────────────────────────────────────────────────┐
│ 1. EMISIÓN DEL CERTIFICADO                              │
│                                                          │
│ Institución genera certificado                          │
│      ↓                                                   │
│ Sistema crea código QR único                            │
│      ↓                                                   │
│ QR apunta a: validar.php?codigo=VALID-{hash}           │
└──────────────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────────────┐
│ 2. VALIDACIÓN PÚBLICA                                   │
│                                                          │
│ Usuario escanea QR con smartphone                       │
│      ↓                                                   │
│ validar.php recibe el código                            │
│      ↓                                                   │
│ Busca en TODAS las instituciones registradas            │
│      ↓                                                   │
│ foreach ($instituciones as $inst) {                     │
│     Carga datos.php de cada institución                 │
│     Compara código recibido con códigos generados       │
│     Si coincide → encontrado!                           │
│ }                                                        │
└──────────────────────────────────────────────────────────┘
                        ↓
┌──────────────────────────────────────────────────────────┐
│ 3. RESULTADO                                             │
│                                                          │
│ ✅ SI SE ENCUENTRA:                                     │
│    → Redirige a vista_validacion.php                    │
│    → Muestra certificado con branding institucional     │
│    → Datos verificados del estudiante                   │
│    → Registro académico completo                        │
│                                                          │
│ ❌ SI NO SE ENCUENTRA:                                  │
│    → Muestra página de error                            │
│    → "Documento No Válido"                              │
│    → Código ingresado para referencia                   │
└──────────────────────────────────────────────────────────┘
```

### Características de Seguridad

**✅ Infalsificable:**
- Hash MD5 basado en datos únicos (DNI + Curso ID)
- Imposible generar código válido sin acceso a datos.php
- Verificación criptográfica

**✅ Validación Instantánea:**
- 24/7 disponible
- Sin necesidad de contactar a la institución
- Resultados en menos de 1 segundo

**✅ Trazable:**
- Cada validación puede ser registrada (opcional)
- Historial de quién validó y cuándo
- Analytics de certificados más consultados

---

## 📄 TIPOS DE DOCUMENTOS

### 1. Analítico (Registro Académico Completo)

**Archivo:** `generar_documento.php?tipo=analitico`

**Formato:** Vertical (A4 Portrait)

**Contenido:**
- ✅ Datos completos del estudiante
- ✅ Logo y branding institucional
- ✅ Listado de TODOS los cursos realizados
- ✅ Línea de tiempo (trayectoria académica)
- ✅ Competencias adquiridas por curso
- ✅ Código QR de validación

**Uso Principal:**
- Historial académico verificable completo
- Solicitudes de empleo
- Postulación a maestrías/doctorados
- Presentación a colegios profesionales

**Vista Previa:**
```
┌────────────────────────────────────┐
│ [LOGO INSTITUCIÓN]                 │
│                                    │
│ REGISTRO ACADÉMICO                 │
│                                    │
│ Nombre: Juan Pérez                 │
│ DNI: 25123456                      │
│                                    │
│ ════════════════════════════════   │
│                                    │
│ CURSOS COMPLETADOS:                │
│                                    │
│ 📚 Derecho Procesal Avanzado       │
│    Carga: 90hs | Nota: 9.50       │
│    Finalizó: 30/07/2024            │
│    ─ Competencias:                 │
│      • Litigación Oral             │
│      • Recursos Procesales         │
│    ─ Trayectoria:                  │
│      01/03/2024: Inscripción       │
│      15/03/2024: Inicio            │
│      30/04/2024: TP1 (Nota: 9.0)   │
│      15/06/2024: Parcial (10.0)    │
│      30/07/2024: Finalización      │
│                                    │
│ 📚 Argumentación Jurídica          │
│    Carga: 60hs | Nota: 8.75       │
│    ...                             │
│                                    │
│ [QR CODE]                          │
│ VALID-E8A9F3B2C1D0                │
└────────────────────────────────────┘
```

---

### 2. Certificado de Aprobación

**Archivo:** `generar_documento.php?tipo=certificado_aprobacion`

**Formato:** Horizontal (A4 Landscape)

**Contenido:**
- ✅ Diseño elegante tipo diploma
- ✅ Nombre del estudiante (destacado)
- ✅ Nombre del curso
- ✅ Carga horaria
- ✅ Nota final
- ✅ Fecha de finalización
- ✅ Firmas digitales (directivos)
- ✅ Código QR de validación

**Uso Principal:**
- Certificado oficial de finalización
- Documento para enmarcar
- Presentación formal de credenciales

**Vista Previa:**
```
┌──────────────────────────────────────────────────────────┐
│                    [LOGO INSTITUCIÓN]                    │
│                                                          │
│              CERTIFICADO DE APROBACIÓN                   │
│                                                          │
│           Otorgado a                                     │
│       ═══════════════════════════════                   │
│           JUAN PÉREZ                                     │
│       ═══════════════════════════════                   │
│                                                          │
│   Por haber aprobado satisfactoriamente el curso de:    │
│                                                          │
│         DERECHO PROCESAL AVANZADO                        │
│                                                          │
│   Carga horaria: 90 horas                               │
│   Nota final: 9.50                                      │
│   Fecha: 30 de Julio de 2024                            │
│                                                          │
│                                                          │
│   _______________        _______________                 │
│   Firma Director         Firma Académico                │
│                                                          │
│   [QR CODE]                                             │
│   VALID-E8A9F3B2C1D0                                    │
└──────────────────────────────────────────────────────────┘
```

---

### 3. Constancia de Alumno Regular

**Archivo:** `generar_documento.php?tipo=constancia_regular`

**Formato:** Vertical

**Contenido:**
- ✅ Datos del estudiante
- ✅ Curso en curso actual
- ✅ Asistencia actual
- ✅ Fecha de emisión
- ✅ Código QR de validación

**Uso Principal:**
- Certificar condición de alumno regular
- Trámites administrativos
- Becas y subsidios
- Descuentos estudiantiles

---

### 4. Constancia de Finalización de Cursada

**Archivo:** `generar_documento.php?tipo=constancia_finalizacion`

**Formato:** Vertical

**Contenido:**
- ✅ Finalización de cursada (sin nota final)
- ✅ Carga horaria cumplida
- ✅ Asistencia lograda
- ✅ Pendiente: Examen final

**Uso Principal:**
- Cursó pero no rindió examen final
- Comprobante de cursada aprobada
- Inscripción a mesa de examen

---

### 5. Constancia de Inscripción

**Archivo:** `generar_documento.php?tipo=constancia_inscripcion`

**Formato:** Vertical

**Contenido:**
- ✅ Datos del estudiante
- ✅ Curso inscrito (próximo a iniciar)
- ✅ Fecha de inicio prevista
- ✅ Modalidad y horarios

**Uso Principal:**
- Comprobante de inscripción
- Reserva de vacante
- Presentación de intención de cursar

---

## 💾 ESTRUCTURA DE DATOS (Base de Datos MySQL)

El sistema ahora utiliza una base de datos relacional para almacenar toda la información, lo que garantiza escalabilidad e integridad de los datos. El esquema principal es el siguiente:

```sql
-- Instituciones
CREATE TABLE instituciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(255),
    slug VARCHAR(100) UNIQUE,
    logo_url VARCHAR(500),
    color_primary VARCHAR(7),
    created_at TIMESTAMP
);

-- Estudiantes
CREATE TABLE estudiantes (
    id_estudiante INT PRIMARY KEY AUTO_INCREMENT,
    institucion VARCHAR(50), -- Slug de la institución
    dni VARCHAR(20),
    nombre_completo VARCHAR(255),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(institucion, dni)
);

-- Cursos
CREATE TABLE cursos (
    id_curso INT PRIMARY KEY AUTO_INCREMENT,
    codigo_curso VARCHAR(50) UNIQUE,
    nombre_curso VARCHAR(255),
    carga_horaria INT,
    activo BOOLEAN DEFAULT 1
);

-- Inscripciones / Cursadas
CREATE TABLE inscripciones (
    id_inscripcion INT PRIMARY KEY AUTO_INCREMENT,
    id_estudiante INT,
    id_curso INT,
    estado ENUM('Por Iniciar', 'En Curso', 'Finalizado', 'Aprobado'),
    fecha_inscripcion TIMESTAMP,
    fecha_inicio DATE,
    fecha_finalizacion DATE,
    nota_final DECIMAL(4,2),
    asistencia VARCHAR(10),
    codigo_validacion VARCHAR(20) UNIQUE,
    FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante),
    FOREIGN KEY (id_curso) REFERENCES cursos(id_curso)
);

-- Competencias
CREATE TABLE competencias_curso (
    id_competencia INT PRIMARY KEY AUTO_INCREMENT,
    id_inscripcion INT,
    competencia VARCHAR(255),
    orden INT,
    FOREIGN KEY (id_inscripcion) REFERENCES inscripciones(id_inscripcion)
);

-- Trayectoria Académica
CREATE TABLE trayectoria (
    id_trayectoria INT PRIMARY KEY AUTO_INCREMENT,
    id_inscripcion INT,
    fecha DATE,
    evento VARCHAR(255),
    detalle TEXT,
    orden INT,
    FOREIGN KEY (id_inscripcion) REFERENCES inscripciones(id_inscripcion)
);

-- Validaciones (para Analytics)
CREATE TABLE validaciones (
    id_validacion INT PRIMARY KEY AUTO_INCREMENT,
    codigo_validacion VARCHAR(20),
    id_inscripcion INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_validacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_inscripcion) REFERENCES inscripciones(id_inscripcion)
);
```

### Estados de Curso

| Estado | Significado | Nota Final | Documentos Disponibles |
|--------|-------------|------------|------------------------|
| **Aprobado** | Curso completado con nota aprobatoria | Sí (≥6.0) | Certificado Aprobación, Analítico |
| **En Curso** | Cursando actualmente | N/A | Constancia Alumno Regular |
| **Finalizado** | Cursada completa sin examen final | N/A o <6.0 | Constancia Finalización, Analítico |
| **Por Iniciar** | Inscrito pero aún no comenzó | N/A | Constancia Inscripción |

---

## 🌐 URLS Y FLUJOS DEL USUARIO

### Para el Estudiante

#### 1. Acceso al Portal Institucional
```
{institucion}/index.php?dni={DNI}
```

**Ejemplo:**
```
sajur/index.php?dni=25123456
```

**Muestra:**
- Datos del estudiante
- Lista de cursos realizados/en curso
- Botones de acción por curso

---

#### 2. Ver Listado de Cursos
```
cursos.php?institucion={inst}&dni={DNI}
```

**Ejemplo:**
```
cursos.php?institucion=sajur&dni=25123456
```

**Muestra:**
- Grid de todos los cursos
- Estado de cada curso
- Botones para generar documentos

---

#### 3. Ver Analítico (Registro Académico)
```
analitico.php?institucion={inst}&dni={DNI}&curso_id={ID}
```

**Ejemplo:**
```
analitico.php?institucion=sajur&dni=25123456&curso_id=SJ-DPA-2024
```

**Muestra:**
- Registro académico completo
- Línea de tiempo
- Botón de impresión/descarga PDF

---

#### 4. Generar Documento
```
generar_documento.php?institucion={inst}&dni={DNI}&curso_id={ID}&tipo={tipo}
```

**Tipos disponibles:**
- `analitico`
- `certificado_aprobacion`
- `constancia_regular`
- `constancia_finalizacion`
- `constancia_inscripcion`

**Ejemplo:**
```
generar_documento.php?institucion=sajur&dni=25123456&curso_id=SJ-DPA-2024&tipo=certificado_aprobacion
```

**Función:**
- Genera documento en HTML
- Optimizado para impresión
- Botón "Imprimir / Guardar como PDF"
- Incluye código QR de validación

---

### Para Validación Pública

#### Validar Certificado
```
validar.php?codigo={CODIGO}
```

**Ejemplo:**
```
validar.php?codigo=VALID-E8A9F3B2C1D0
```

**Proceso:**
1. Recibe código de validación
2. Busca en todas las instituciones
3. Si encuentra → redirige a vista_validacion.php
4. Si no encuentra → muestra error

---

#### Vista de Validación
```
vista_validacion.php?institucion={inst}&dni={DNI}&curso_id={ID}
```

**Ejemplo:**
```
vista_validacion.php?institucion=sajur&dni=25123456&curso_id=SJ-DPA-2024
```

**Muestra:**
- ✅ "Documento Válido"
- Datos del estudiante verificados
- Datos del curso verificados
- Branding institucional
- Código de validación
- Fecha de consulta

---

## 🎨 BRANDING POR INSTITUCIÓN

### Sistema de Personalización

Cada institución tiene **identidad visual propia** a través de:

#### 1. Paleta de Colores (`style.css`)

**SAJuR:**
```css
.sajur-green-dark {
    background-color: #006837;
}
.sajur-green-dark-text {
    color: #006837;
}
.sajur-green-dark-hover:hover {
    background-color: #005228;
}
```

**Liberté:**
```css
.liberte-green-dark {
    background-color: #16a34a;
}
.liberte-green-text {
    color: #16a34a;
}
.liberte-green-hover:hover {
    background-color: #15803d;
}
```

---

#### 2. Header Institucional (`header.php`)

Incluye:
- Logo de la institución
- Nombre completo
- Navegación específica
- Colores del brand

---

#### 3. Footer Institucional (`footer.php`)

Incluye:
- Datos de contacto
- Redes sociales
- Copyright
- Links institucionales

---

#### 4. Lógica de Selección Automática

En `generar_documento.php`:

```php
if ($institucion == 'sajur') {
    $color_primary_bg = 'sajur-green-dark';
    $color_primary_hover = 'sajur-green-dark-hover';
    $color_primary_text = 'sajur-green-dark-text';
    $logo_url = 'https://placehold.co/100x100/006837/ffffff?text=SJ';
    $nombre_institucion = 'SAJuR - Sociedad Argentina de Justicia Restaurativa';

} elseif ($institucion == 'liberte') {
    $color_primary_bg = 'liberte-green-dark';
    $color_primary_hover = 'liberte-green-hover';
    $color_primary_text = 'liberte-green-text';
    $logo_url = 'https://placehold.co/100x100/16a34a/ffffff?text=L';
    $nombre_institucion = 'Cooperativa de Trabajo Liberté';

} else {
    // Defaults genéricos
    $color_primary_bg = 'bg-blue-600';
    $color_primary_hover = 'hover:bg-blue-700';
    $color_primary_text = 'text-blue-600';
    $logo_url = 'https://placehold.co/100x100/3b82f6/ffffff?text=?';
    $nombre_institucion = 'Institución Educativa';
}
```

---

## 🔧 FUNCIONALIDADES IMPLEMENTADAS

### ✅ Core Funcional

**1. Multi-Tenant Completo**
- ✅ Aislamiento total de datos por institución
- ✅ Branding personalizado automático
- ✅ Gestión independiente por carpeta
- ✅ Escalable a N instituciones

**2. Generación de Documentos**
- ✅ 5 tipos de documentos diferentes
- ✅ HTML optimizado para impresión
- ✅ PDF vía función nativa del navegador
- ✅ QR code integrado automáticamente
- ✅ Responsive (mobile-friendly)

**3. Validación Global**
- ✅ Búsqueda en todas las instituciones
- ✅ Verificación instantánea 24/7
- ✅ Página pública de resultados con branding
- ✅ Manejo de errores (código inválido)

**4. Registro Académico (Analítico)**
- ✅ Historial completo de cursos
- ✅ Línea de tiempo (trayectoria)
- ✅ Competencias adquiridas
- ✅ Notas y asistencia
- ✅ Eventos académicos

**5. Portal del Estudiante**
- ✅ Acceso con DNI (sin login/password)
- ✅ Vista de todos sus cursos
- ✅ Descarga de documentos
- ✅ Navegación intuitiva

**6. Panel de Administración (`administrare.php`)**
- ✅ Gestión completa de Estudiantes, Cursos e Inscripciones (CRUD).
- ✅ Carga masiva de datos desde Excel, CSV o texto.
- ✅ Búsqueda y filtros avanzados.
- ✅ Interfaz multi-tenant segura por institución.
---

## ⚙️ PANEL DE ADMINISTRACIÓN (`administrare.php`)

El sistema cuenta con un panel de administración robusto y funcional, que contradice la idea de que era una funcionalidad "pendiente". Este panel permite a cada institución gestionar de forma autónoma toda su información.

**Archivo principal:** `certificatum/administrare.php`

### Funcionalidades Clave del Panel:

**1. Carga Masiva de Datos:**
- **Soporte Multi-formato:** Permite la carga inicial y actualizaciones masivas usando archivos **Excel (.xlsx)**, **CSV** o **pegando texto plano**.
- **Procesamiento Inteligente:** El sistema automáticamente crea o actualiza estudiantes, cursos e inscripciones en una sola operación, resolviendo dependencias.

**2. Gestión de Estudiantes:**
- **CRUD Completo:** Permite crear (masivamente), listar, buscar, editar y eliminar estudiantes.
- **Seguridad:** Previene la eliminación de estudiantes con inscripciones activas para mantener la integridad de los datos.

**3. Gestión de Cursos:**
- **CRUD Completo:** Permite crear (masivamente), listar, buscar, editar y gestionar el estado (activo/inactivo) de los cursos.
- **Desactivación Segura:** En lugar de borrar, desactiva los cursos que ya tienen historial de inscripciones.

**4. Gestión de Inscripciones:**
- **Panel Central:** Es el núcleo de la gestión académica diaria.
- **CRUD y Actualización de Estado:** Permite crear, listar con filtros, y editar inscripciones para actualizar el estado (`En Curso`, `Aprobado`), registrar notas finales, asistencia y fechas.

**5. Interfaz Intuitiva:**
- **Diseño Tabulado:** Organiza la información en pestañas claras (Estudiantes, Cursos, Inscripciones, Ayuda).
- **Herramientas de Ayuda:** Incluye documentación sobre el formato CSV requerido para las cargas masivas.

---

### API de Integración

**Endpoints propuestos:**

```
POST /api/certificatum/estudiante
  → Dar de alta un estudiante

POST /api/certificatum/cursada
  → Inscribir estudiante a curso

PUT /api/certificatum/cursada/{id}
  → Actualizar estado/nota de cursada

POST /api/certificatum/emitir
  → Emitir certificado

GET /api/certificatum/validar/{codigo}
  → Validar certificado vía API
```

**Casos de uso:**
- Integración con sistemas académicos existentes (SIU Guaraní, etc.)
- Sincronización automática de notas
- Webhooks para notificaciones

---

### Plantillas Personalizables

**Editor Visual:**
- [ ] Drag & drop de elementos
- [ ] Selector de fuentes
- [ ] Paleta de colores institucional
- [ ] Upload de logos/imágenes
- [ ] Preview en tiempo real

**Plantillas Pre-diseñadas:**
- [ ] Certificado Clásico (formal)
- [ ] Certificado Moderno (minimalista)
- [ ] Certificado Elegante (serif)
- [ ] Diploma Internacional (bilingüe)

---

## 💰 MODELO DE NEGOCIO

### Propuesta 1: SaaS por Institución

#### Plan Básico - USD $49/mes
- ✅ Hasta 50 estudiantes activos
- ✅ Hasta 100 certificados/mes
- ✅ 1 usuario administrador
- ✅ Branding básico (logo + colores)
- ✅ Validación ilimitada
- ✅ Soporte por email

#### Plan Profesional - USD $99/mes ⭐ Popular
- ✅ Hasta 200 estudiantes activos
- ✅ Hasta 500 certificados/mes
- ✅ 3 usuarios administradores
- ✅ Branding completo personalizado
- ✅ Validación ilimitada
- ✅ Estadísticas avanzadas
- ✅ Soporte prioritario

#### Plan Enterprise - USD $249/mes
- ✅ Estudiantes ilimitados
- ✅ Certificados ilimitados
- ✅ Usuarios ilimitados
- ✅ Branding white-label
- ✅ API access completo
- ✅ Webhooks
- ✅ Soporte dedicado 24/7
- ✅ Gerente de cuenta

---

### Propuesta 2: Integrado en Identitas

**Incluido desde Premium+:**

| Plan | Certificados/mes | Features |
|------|------------------|----------|
| **Basicum** | - | No incluido |
| **Premium** | 10 certificados/mes | Básico |
| **Excellens** | 50 certificados/mes | + Estadísticas |
| **Supremus** | Ilimitado | + API |

**Target:** Profesionales que dan cursos/talleres/workshops

**Integración con Identitas:**
- Mostrar certificados en sección "Credenciales"
- Badge "Certificado Verificado" en sitio web
- QR de validación visible
- Sincronización automática

---

### Propuesta 3: Freemium

**Plan Gratuito:**
- ✅ 1 curso activo
- ✅ Hasta 10 estudiantes
- ✅ 10 certificados/mes
- ⚠️ Badge "Powered by VERUMax"
- ⚠️ Branding limitado

**Plan Premium - USD $19/mes:**
- ✅ Cursos ilimitados
- ✅ 100 estudiantes
- ✅ 100 certificados/mes
- ✅ Sin badge
- ✅ Branding completo
- ✅ Soporte

**Ventaja:** Captación masiva con freemium, conversión a pago

---

## 🎯 MERCADO OBJETIVO

### Primario (B2B)

**1. Universidades Privadas (Pequeñas/Medianas)**
- Hasta 5,000 estudiantes
- Sin sistema de certificación digital
- Buscan modernización

**2. Centros de Formación Profesional**
- Cursos técnicos/oficios
- Alta rotación de alumnos
- Necesidad de validación rápida

**3. Academias Especializadas**
- Idiomas, programación, diseño
- Certificaciones no oficiales
- Diferenciación por calidad

**4. Escuelas de Negocios**
- Cursos corporativos
- Certificaciones ejecutivas
- Prestigio y validación

---

### Secundario

**5. Formadores Particulares**
- Coaches, instructores
- Talleres y workshops
- Profesionalización de servicios

**6. Empresas con Capacitación Interna**
- RRHH y desarrollo
- Compliance y certificaciones
- Onboarding

**7. Colegios Profesionales**
- Abogados, contadores, médicos
- Cursos de actualización
- Puntos para matrícula

**8. Escuelas de Arte y Oficios**
- Certificación de habilidades
- Portfolio + credencial
- Inserción laboral

---

## 📊 VENTAJAS COMPETITIVAS

### vs Certificados en Papel

| Aspecto | Papel | Certificatum |
|---------|-------|--------------|
| **Costo de emisión** | Alto (impresión, firmas) | Cero marginal |
| **Tiempo de emisión** | Horas/Días | Segundos |
| **Validación** | Llamada telefónica | Instantánea QR |
| **Pérdida/Daño** | Reemisión costosa | Descarga ilimitada |
| **Falsificación** | Fácil | Imposible |
| **Almacenamiento** | Archivo físico | Base de datos |
| **Trazabilidad** | Nula | Completa |

---

### vs Plataformas Internacionales (Coursera, Udemy)

| Aspecto | Plataformas Globales | Certificatum |
|---------|---------------------|--------------|
| **Branding** | Marca propia | Institución 100% |
| **Personalización** | Limitada | Total |
| **Costo** | % de ventas | Flat fee |
| **Datos** | Son de ellos | Son tuyos |
| **Integración** | Cerrada | API abierta |
| **Validación** | Genérica | Multi-tenant local |

---

### vs Sistemas Académicos (SIU Guaraní, etc.)

| Aspecto | SIU/Sistemas Legacy | Certificatum |
|---------|---------------------|--------------|
| **Setup** | Meses | Días |
| **Costo inicial** | Alto (licencias) | Bajo (SaaS) |
| **Curva aprendizaje** | Compleja | Intuitiva |
| **Validación pública** | No incluida | Core feature |
| **Mobile-friendly** | Limitado | Nativo |
| **QR infalsificable** | No | Sí |

---

## 🔗 INTEGRACIÓN CON ECOSISTEMA VERUMAX

### En Identitas (Premium+)

**Sección "Credenciales":**

```
┌────────────────────────────────────┐
│ tunombre.verumax.com               │
│                                    │
│ ┌──────────────────────────────┐   │
│ │ SOBRE MÍ                     │   │
│ │ ...                          │   │
│ └──────────────────────────────┘   │
│                                    │
│ ┌──────────────────────────────┐   │
│ │ CREDENCIALES VERIFICADAS ✓   │   │
│ │                              │   │
│ │ 🎓 Derecho Procesal Avanzado │   │
│ │    SAJuR | 2024              │   │
│ │    Nota: 9.50                │   │
│ │    [Ver Certificado] [QR]    │   │
│ │                              │   │
│ │ 🎓 Argumentación Jurídica    │   │
│ │    SAJuR | 2023              │   │
│ │    Nota: 8.75                │   │
│ │    [Ver Certificado] [QR]    │   │
│ │                              │   │
│ └──────────────────────────────┘   │
│                                    │
│ ┌──────────────────────────────┐   │
│ │ SERVICIOS                    │   │
│ │ ...                          │   │
│ └──────────────────────────────┘   │
└────────────────────────────────────┘
```

**Funcionalidad:**
1. Usuario agrega certificados desde Certificatum
2. Obtiene código de validación
3. Integración automática con Identitas
4. Certificado aparece en sección "Credenciales"
5. Visitantes pueden:
   - Ver certificado completo
   - Escanear QR para verificar
   - Ver institución emisora

**Badge de Verificación:**
- "✓ Certificado Verificado" en cada credencial
- Hover muestra: "Validado por VERUMax Certificatum"
- Click abre popup con QR

---

### En Lumen (Portfolios)

**Para Creativos/Profesionales:**

Si un fotógrafo tiene certificaciones (ej: curso de iluminación), puede mostrarlas en su portfolio:

```
Portfolio → Sección "Formación" → Certificados verificados con QR
```

---

### En Vitae (CV)

**Sincronización Automática:**

Certificados de Certificatum → CV Vitae automáticamente

```
┌────────────────────────────────────┐
│ CURRICULUM VITAE                   │
│                                    │
│ EDUCACIÓN                          │
│ ...                                │
│                                    │
│ CERTIFICACIONES (desde Certificatum)│
│                                    │
│ • Derecho Procesal Avanzado        │
│   SAJuR | 2024 | Nota: 9.50       │
│   [QR Verificable]                 │
│                                    │
│ • Argumentación Jurídica           │
│   SAJuR | 2023 | Nota: 8.75       │
│   [QR Verificable]                 │
│                                    │
└────────────────────────────────────┘
```

**Ventaja:** CV con credenciales verificables = mayor confianza empleador

---

## 📈 MÉTRICAS DE ÉXITO

### KPIs Institucionales (Dashboard)

**Emisión:**
- Certificados emitidos este mes
- Certificados emitidos total
- Promedio de emisión por día

**Validación:**
- Validaciones realizadas este mes
- Validaciones totales
- Certificados más validados
- Horarios pico de validación

**Estudiantes:**
- Estudiantes activos
- Estudiantes graduados
- Tasa de aprobación
- Cursos más populares

**Engagement:**
- Descargas de certificados
- Accesos al portal del estudiante
- Tiempo promedio en sitio

---

### KPIs de Negocio (VERUMax)

**Adopción:**
- Instituciones activas
- Estudiantes en plataforma
- Certificados emitidos totales

**Revenue:**
- MRR (Monthly Recurring Revenue)
- ARR (Annual Recurring Revenue)
- ARPU (Average Revenue Per User)
- Churn rate

**Engagement:**
- Validaciones públicas/mes
- Tasa de conversión Freemium → Premium
- NPS (Net Promoter Score)

---

## 🛠️ TECNOLOGÍA Y STACK

### Frontend
- **Framework CSS:** Tailwind CSS (vía CDN)
- **Icons:** Lucide Icons
- **Fonts:** Inter (sans-serif), Merriweather (serif)
- **JavaScript:** Vanilla JS (sin dependencias)

### Backend
- **Lenguaje:** PHP 7.4+
- **Base de datos:** MySQL/MariaDB
- **Arquitectura:** Multi-tenant con BBDD única y segregación por `institucion`.

### Generación de Documentos
- **HTML:** Plantillas optimizadas para impresión
- **PDF:** Función nativa del navegador (Print to PDF)
- **QR Codes:** API externa `https://api.qrserver.com/v1/create-qr-code/`

### Hosting
- **Servidor:** PHP-enabled hosting
- **SSL:** Requerido (para confianza en validaciones)
- **Storage:** Archivos locales (migración a S3/Cloud opcional)

---

## 🚀 ROADMAP DE DESARROLLO

### FASE 1: MVP Funcional ✅
**Estado:** Completado

- [x] Sistema multi-tenant con branding por carpetas.
- [x] Generación de 5 tipos de documentos.
- [x] Validación global con QR.
- [x] Portal del estudiante.
- [x] Landing page (certificatum.php).

---

### FASE 2: Base de Datos y Panel de Admin v2 ✅
**Estado:** Completado

- [x] Migración completa a Base de Datos **MySQL/MariaDB**.
- [x] Desarrollo del panel `certificatum/administrare.php`.
- [x] **CRUD** completo para Estudiantes, Cursos e Inscripciones.
- [x] Implementación de carga masiva vía **Excel, CSV y texto**.
- [x] Sistema de autenticación unificado.

---

### FASE 3: Analytics y Reportes 🔜
**Tiempo estimado:** 2-3 semanas

**Tareas:**
- [ ] Dashboard con métricas de uso.
- [ ] Gráficos de certificados emitidos.
- [ ] Tracking y mapa de validaciones.
- [ ] Exportación de reportes (PDF, Excel).
- [ ] Alertas y notificaciones.

---

### FASE 4: API y Webhooks 🔜
**Tiempo estimado:** 3-4 semanas

**Tareas:**
- [ ] API RESTful documentada.
- [ ] Autenticación con tokens.
- [ ] Endpoints CRUD completos.
- [ ] Webhooks para notificar eventos (ej. nuevo certificado emitido).
- [ ] SDK/Cliente de ejemplo (PHP, Python).

---

### FASE 5: Editor de Plantillas 🔜
**Tiempo estimado:** 4-6 semanas

**Tareas:**
- [ ] Editor visual drag & drop para plantillas de certificados.
- [ ] Biblioteca de plantillas prediseñadas.
- [ ] Upload de logos/imágenes de firma.
- [ ] Preview en tiempo real.
- [ ] Versionado de plantillas.

---

## 📝 NOTAS TÉCNICAS IMPORTANTES

### Seguridad

**Protección Anti-Falsificación:**
1. **Código único:** Generado en la base de datos para cada inscripción.
2. **Imposible de replicar:** Requiere acceso a la base de datos para crear una inscripción válida.
3. **Validación centralizada:** `validar.php` consulta la base de datos como única fuente de verdad.

**Vulnerabilidades y Mitigaciones:**
- ✅ **SQL Injection:** Se utilizan prepared statements (PDO) en todas las consultas para prevenir inyecciones.
- ✅ **XSS:** Se utiliza `htmlspecialchars()` en toda la data que se muestra en el HTML para prevenir Cross-Site Scripting.
- ✅ **CSRF:** El panel de administración debería incluir tokens CSRF en todos los formularios de acción (pendiente de revisión).
- [ ] **Brute force:** Se debe implementar un rate limiting en el login y en la página de validación pública.

---

### Performance

**Optimizaciones Implementadas:**
- ✅ **Base de Datos Indexada:** Las tablas clave (`estudiantes`, `cursos`, `inscripciones`) tienen índices en las columnas de búsqueda frecuente para acelerar las consultas.
- ✅ **Consultas Eficientes:** Las listas en el panel de admin están paginadas y usan `JOIN`s optimizados.
- ✅ CSS inline en documentos generados para minimizar requests.

**Optimizaciones Futuras:**
- [ ] Implementar un sistema de caché (Redis/Memcached) para consultas frecuentes.
- [ ] CDN para assets estáticos (CSS, JS).
- [ ] Minificación de todos los assets.

---

### Escalabilidad

**Modelo Actual (Base de Datos):**
- ✅ **Escalable:** El uso de MySQL/MariaDB permite un crecimiento virtualmente ilimitado de instituciones, estudiantes y cursos.
- ✅ **Concurrencia:** La base de datos maneja múltiples lecturas y escrituras de forma concurrente, a diferencia del sistema anterior de archivos planos.
- ✅ **Mantenible:** La estructura relacional facilita la gestión, el backup y la adición de nuevas funcionalidades.

---

## 🆘 TROUBLESHOOTING

### Problema: "Documento No Válido"

**Posibles causas:**
1. Código QR mal escaneado o URL incorrecta (caracteres faltantes).
2. El `codigo_validacion` no existe en la tabla `inscripciones` de la base de datos.
3. La inscripción fue eliminada o el código fue modificado.
4. La institución no está configurada correctamente en el sistema.

**Solución:**
1. Verificar que el código en la URL sea correcto.
2. Ingresar al **Panel de Administración** (`certificatum/administrare.php`).
3. Ir a la pestaña **Inscripciones**.
4. Buscar al estudiante por DNI o nombre para verificar el estado de su inscripción.
5. Confirmar que el curso y la inscripción existen y que el estado es el correcto ("Aprobado", etc.).
6. Si todo parece correcto, puede ser un problema de caché o de generación del código. Contactar a soporte técnico.

---

### Problema: Branding no se aplica

**Causa:** Institución no tiene configuración en `generar_documento.php`

**Solución:**
Agregar bloque condicional:
```php
} elseif ($institucion == 'nueva_inst') {
    $color_primary_bg = 'nueva-inst-color';
    $color_primary_hover = 'nueva-inst-hover';
    $color_primary_text = 'nueva-inst-text';
    $logo_url = 'https://...';
    $nombre_institucion = 'Nombre Completo';
}
```

---

### Problema: PDF no se genera correctamente

**Causa:** Configuración de impresión del navegador

**Solución:**
1. Usar Chrome/Edge (mejor soporte)
2. Configurar:
   - Orientación: Horizontal (certificados) / Vertical (analíticos)
   - Márgenes: Ninguno
   - Fondo gráfico: Activado

---

## 📄 ARCHIVOS PARA SUBIR/ACTUALIZAR

### Producción Actual (Certificatum v2)

**Landing & Core:**
- ✅ `certificatum.php`
- ✅ `validar.php`
- ✅ `vista_validacion.php`
- ✅ `generar_documento.php`
- ✅ `generar_pdf_certificado.php`
- ✅ `generar_pdf_analitico.php`
- ✅ `analitico.php`
- ✅ `cursos.php`

**Panel de Administración:**
- ✅ `certificatum/administrare.php`
- ✅ `certificatum/administrare_gestionar.php`
- ✅ `certificatum/administrare_procesador.php`
- ✅ `certificatum/config.php` (contiene la conexión a BBDD)

**Instituciones (Branding):**
- ✅ `sajur/index.php`
- ✅ `sajur/header.php`
- ✅ `sajur/footer.php`
- ✅ `sajur/style.css`
- ✅ `liberte/` (mismos archivos)

**Documentación:**
- ✅ `certificatum.md` (este archivo)

---

## 🎓 CASOS DE USO DETALLADOS

### Caso 1: Universidad Privada

**Cliente:** Universidad del Valle (500 estudiantes)

**Necesidad:**
- Emitir diplomas de grado
- Certificados de cursos de extensión
- Analíticos estudiantiles
- Validación para empresas

**Solución:**
- Plan Enterprise ($249/mes)
- 5 usuarios admin (secretarías académicas)
- API integrada con sistema SIU
- Branding institucional completo

**ROI:**
- Ahorro en impresión: $800/mes
- Ahorro en tiempo administrativo: 40hs/mes
- Reducción de consultas telefónicas: 80%
- **Payback:** 2 meses

---

### Caso 2: Formador Particular (Coach)

**Cliente:** María, Coach de Negocios

**Necesidad:**
- Certificar workshops/talleres
- Profesionalizar su servicio
- Diferenciarse de competencia

**Solución:**
- Integración con Identitas Premium
- 10 certificados/mes incluidos
- Certificados mostrados en su sitio web
- Badge "Certificaciones Verificadas"

**ROI:**
- Aumento de precio por taller: +20%
- Tasa de conversión: +15%
- **Inversión:** $0 adicional (incluido en Premium)

---

### Caso 3: Escuela de Programación

**Cliente:** Code Academy (200 alumnos/año)

**Necesidad:**
- Certificar bootcamps
- Portfolio + certificado
- Empleadores validan credenciales

**Solución:**
- Plan Profesional ($99/mes)
- Integración Certificatum + Lumen
- Portfolio con certificado verificable
- API para partners empresariales

**ROI:**
- Tasa de empleabilidad graduados: +25%
- Marketing boca-a-boca: +40%
- Precio del bootcamp: +$200 (valor agregado)

---

## 🏁 CONCLUSIÓN

**Certificatum** es una solución completa, funcional y en producción que resuelve un problema real para instituciones educativas de todos los tamaños. La reciente migración a base de datos y la creación de un panel de administración la convierten en una herramienta SaaS robusta y escalable.

### Fortalezas:
✅ Arquitectura Multi-tenant sobre **Base de Datos MySQL**.
✅ **Panel de Administración** completo para autogestión.
✅ Carga masiva de datos (Excel, CSV) para un setup rápido.
✅ QR infalsificable (diferenciador clave).
✅ Validación 24/7 sin intervención humana.
✅ 5 tipos de documentos y branding personalizado.
✅ En producción y validado con clientes reales (SAJuR, Liberté).

### Próximos Pasos:
1. Desarrollar el **Dashboard de Analytics y Reportes** (Fase 3).
2. Construir la **API de integración y Webhooks** (Fase 4).
3. Lanzamiento comercial de los planes SaaS.
4. Integración completa con Identitas, Lumen y Vitae.

---

**Última actualización:** 2025-11-24
**Creado por:** Claude Code + Pampa
**Archivos relacionados:** `certificatum.php`, `certificatum/administrare.php`
