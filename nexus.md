### **Plan de Desarrollo: La Solución "Gestor Nexus"**

#### **1. Nombre de la Solución**

* **Nombre Propuesto:** **Gestor Nexus**
* **Slogan/Concepto:** "Tu Centro de Gestión de Comunidades"
* **¿Por qué "Nexus"?** La palabra latina *nexus* significa "conexión" o "vínculo". Este nombre posiciona a la herramienta no como un simple listado de clientes, sino como el **núcleo central que conecta a los miembros de una organización** y desde donde se gestionan todas sus interacciones y credenciales.

#### **2. Concepto Principal**

El "Gestor Nexus" no es un CRM (Customer Relationship Management) genérico. Es un **MMS (Member Management System)** flexible, diseñado para que cada organización (institución académica, mutual, estudio de abogados, etc.) pueda adaptarlo a su terminología y necesidades específicas sin necesidad de programación.

El objetivo es ofrecer un panel de control donde cada cliente de Verumax pueda administrar su base de datos de personas (sean estudiantes, socios, pacientes o clientes) de una forma centralizada, segura y totalmente integrada con el resto de los servicios de la plataforma.

#### **3. Arquitectura Técnica (El "Motor")**

El programador debe entender que esta es una **arquitectura multi-tenant (multi-inquilino)**.

* **Base de Datos Única:** Existirá una única tabla principal en nuestra base de datos, por ejemplo, `miembros`. La columna más importante de esta tabla será `id\_organizacion`. Cada vez que SAJuR cargue un estudiante, ese registro tendrá su `id\_organizacion`. Cuando una mutual cargue un socio, tendrá un `id\_organizacion` diferente.
* **Aislamiento de Datos:** La lógica de la aplicación debe garantizar que, cuando un cliente inicie sesión en su dashboard, todas las consultas a la base de datos (`SELECT`, `UPDATE`, `DELETE`) lleven **obligatoriamente** una cláusula `WHERE id\_organizacion = \[su\_id]`. Esto asegura que una institución **jamás** pueda ver los datos de otra.

**Configuración de Campos Flexibles:** Para lograr la personalización que pides, crearemos una tabla de configuración, por ejemplo, `configuracion\_organizacion`. Esta tabla guardará las preferencias de cada cliente en un campo de tipo `JSON`. Por ejemplo, para SAJuR, podría guardar algo como:  
JSON  
{  
"campos\_activos": \["nombre", "apellido", "dni", "email", "campo\_personalizado\_1"],  
"etiquetas": {  
"dni": "DNI",  
"campo\_personalizado\_1": "Número de Legajo"  
},  
"campos\_requeridos": \["nombre", "apellido", "dni"]  
}

* 

#### **4. Campos de la Base de Datos (Los "Ladrillos")**

> ✅ **IMPLEMENTADO: 2025-12-06**
> Base de datos: `verumax_nexus`
> Tabla principal: `miembros`

La tabla `miembros` tiene un conjunto de campos estándar y campos personalizables.

```sql
CREATE TABLE miembros (
    id_miembro INT PRIMARY KEY AUTO_INCREMENT,
    id_instancia INT NOT NULL,  -- FK a verumax_identitas.instancias

    -- Identificación
    identificador_principal VARCHAR(50) NOT NULL,  -- DNI, CUIT, Pasaporte, etc.
    tipo_identificador ENUM('DNI', 'CUIT', 'CUIL', 'Pasaporte', 'Otro') DEFAULT 'DNI',

    -- Datos personales
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    nombre_completo VARCHAR(200) GENERATED ALWAYS AS (CONCAT(nombre, ' ', apellido)) STORED,
    email VARCHAR(150),
    telefono VARCHAR(30),
    fecha_nacimiento DATE,
    genero ENUM('M', 'F', 'Otro', 'No especifica') DEFAULT 'No especifica',

    -- Domicilio
    domicilio_calle VARCHAR(200),
    domicilio_numero VARCHAR(20),
    domicilio_piso VARCHAR(10),
    domicilio_depto VARCHAR(10),
    domicilio_ciudad VARCHAR(100),
    domicilio_provincia VARCHAR(100),
    domicilio_codigo_postal VARCHAR(20),
    domicilio_pais VARCHAR(100) DEFAULT 'Argentina',

    -- Estado y tipo
    estado ENUM('Activo', 'Inactivo', 'Suspendido', 'Pendiente') DEFAULT 'Activo',
    tipo_miembro ENUM('Estudiante', 'Docente', 'Socio', 'Cliente', 'Otro') DEFAULT 'Estudiante',

    -- Campos personalizables (para flexibilidad)
    campo_texto_1 VARCHAR(255),
    campo_texto_2 VARCHAR(255),
    campo_texto_3 VARCHAR(255),
    campo_numero_1 DECIMAL(10,2),
    campo_numero_2 DECIMAL(10,2),
    campo_fecha_1 DATE,
    campo_fecha_2 DATE,
    campo_booleano_1 BOOLEAN DEFAULT FALSE,

    -- Metadata
    fecha_alta TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    notas TEXT,

    -- Índices
    UNIQUE KEY uk_instancia_identificador (id_instancia, identificador_principal),
    INDEX idx_instancia (id_instancia),
    INDEX idx_estado (estado),
    INDEX idx_nombre (apellido, nombre),
    INDEX idx_email (email)
);
```

**Tabla de Configuración por Organización:**

```sql
CREATE TABLE configuracion_nexus (
    id_config INT PRIMARY KEY AUTO_INCREMENT,
    id_instancia INT NOT NULL UNIQUE,
    etiqueta_miembro VARCHAR(50) DEFAULT 'Miembro',  -- Estudiante, Socio, etc.
    etiqueta_identificador VARCHAR(50) DEFAULT 'DNI',
    campos_activos JSON,
    campos_requeridos JSON,
    etiquetas_personalizadas JSON,
    color_primario VARCHAR(7) DEFAULT '#0F52BA',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_modificacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

#### **5. El Dashboard de Configuración del Cliente (La "Cabina de Control")**

Esta es la sección donde el administrador de cada institución personaliza su "Gestor Nexus". Debe ser una página amigable con una interfaz como la siguiente:

* **Título:** "Configurar Ficha de Miembro"
* **Interfaz:** Una lista de todos los campos disponibles (estándar y personalizables). Al lado de cada campo, habrá tres controles:

  1. **Checkbox "Activar":** Para decidir si quiere usar ese campo o no.
  2. **Campo de Texto "Etiqueta":** Aquí es donde personaliza el nombre. Por ejemplo, para `identificador\_principal`, un cliente puede escribir "DNI" y otro "N° de Socio". Para `campo\_texto\_1`, una academia puede escribir "N° de Legajo" y una mutual "N° de Afiliado".
  3. **Checkbox "Requerido":** Para marcar si ese campo es obligatorio al dar de alta a un nuevo miembro.

#### **6. La Interfaz de Gestión (El "Día a Día")**

Esta es la pantalla principal del "Gestor Nexus" donde el cliente trabaja con sus miembros.

* **Vista de Listado:**

  * Una tabla que muestra a todos los miembros. **Las columnas de esta tabla se generan dinámicamente** basándose en los campos que el cliente activó en su configuración.
  * Funciones de **búsqueda y filtrado potentes** en la parte superior.
  * Un botón destacado: **"Añadir Nuevo \[Miembro/Socio/Estudiante]"** (la etiqueta cambia según la configuración).
  * Acciones por cada fila: Editar, Eliminar, Ver Ficha Completa.

* **Formulario de Alta/Edición:**

  * Este formulario también **se genera dinámicamente**. Solo mostrará los campos que el cliente activó.
  * Las etiquetas de cada campo serán las que el cliente personalizó.
  * Validará que los campos marcados como "Requeridos" no estén vacíos.

#### **7. Integración con el Ecosistema Verumax**

El verdadero poder del "Gestor Nexus" es cómo se conecta con tus otros servicios.

* **Desde el Gestor hacia afuera:** En la ficha de cada miembro, habrá botones de acción directa como:

  * "Emitir Certificado" (abre el módulo académico con los datos del miembro ya cargados).
  * "Ver Credencial Digital" (muestra el carnet de socio).
  * "Acceder al Portal del Estudiante".

* **Desde afuera hacia el Gestor:**

  * Cuando se use el módulo de "Emisión Masiva de Certificados", se podrá seleccionar directamente un grupo de estudiantes desde el "Gestor Nexus" en lugar de tener que subir un Excel.
  * El estado ("Activo"/"Inactivo") de un socio en el Gestor podría habilitar o deshabilitar automáticamente su acceso al portal de beneficios de la mutual.

Con este plan, no solo ofreces un CRM simple, sino una solución de gestión de comunidades **profundamente integrada y personalizable**, lo que la convierte en una herramienta indispensable y de altísimo valor para tus clientes.

---

#### **8. Implementación Técnica (Estado Actual)**

> ✅ **IMPLEMENTADO: 2025-12-06**

**Servicio PHP:** `src/Services/MemberService.php`

```php
use VERUMax\Services\MemberService;

// Obtener todos los miembros de una instancia
$miembros = MemberService::getAll($id_instancia, ['buscar' => 'juan']);

// Obtener un miembro por ID
$miembro = MemberService::getById($id_miembro);

// Crear un nuevo miembro
$resultado = MemberService::crear([
    'id_instancia' => 1,
    'identificador_principal' => '25123456',
    'nombre' => 'Juan',
    'apellido' => 'Pérez',
    'email' => 'juan@email.com',
    'tipo_miembro' => 'Estudiante'
]);

// Actualizar
$resultado = MemberService::actualizar($id_miembro, [
    'email' => 'nuevo@email.com',
    'telefono' => '1155554444'
]);

// Eliminar
$resultado = MemberService::eliminar($id_miembro);

// Importar desde CSV
$stats = MemberService::importarDesdeTexto($id_instancia, $contenido_csv, 'Estudiante');
```

**Conexión de Base de Datos:**

```php
// En env_loader.php
DatabaseService::configure('nexus', [
    'host' => env('NEXUS_DB_HOST', 'localhost'),
    'user' => env('NEXUS_DB_USER', 'root'),
    'password' => env('NEXUS_DB_PASS', ''),
    'database' => env('NEXUS_DB_NAME', 'verumax_nexus'),
]);

// Uso
$pdo = DatabaseService::get('nexus');
```

**Integración con Certificatum:**

Certificatum ahora lee de `verumax_nexus.miembros` en lugar de su tabla local `estudiantes`:
- `obtenerEstudiantes()` → llama a `MemberService::getConInscripciones()`
- `crearEstudiante()` → llama a `MemberService::crear()`
- `actualizarEstudiante()` → llama a `MemberService::actualizar()`
- `eliminarEstudiante()` → llama a `MemberService::eliminar()`

---

**Última actualización:** 2025-12-06
