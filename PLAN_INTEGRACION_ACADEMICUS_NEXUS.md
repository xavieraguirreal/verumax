# PLAN DE INTEGRACIÓN: ACADEMICUS + NEXUS + CERTIFICATUM

**Estado:** Aprobado (v2)
**Fecha:** 2025-11-24
**Objetivo:** Evolucionar la arquitectura de gestión académica hacia un ecosistema modular y escalable, presentando una **experiencia de usuario unificada dentro de un Panel de Control centralizado en `Certificatum`**.

---

## 1\. Visión Estratégica y Arquitectura Final (v2)

La arquitectura backend se mantiene desacoplada, pero la experiencia de usuario se consolida. Usaremos el modelo **"Anfitrión y Satélites"**, donde `Certificatum` es el centro de la experiencia.

* **`Certificatum` (El Anfitrión):** Será el **panel de control principal** y la "puerta de entrada" para el cliente. Su interfaz integrará visualmente las funcionalidades de los otros módulos. Es el producto principal que el cliente compra y usa.
* **`Academicus` (Satélite Académico):** Funciona "sin cabeza" (headless). Provee toda la lógica y datos para la gestión de cursos, cohortes y formaciones a través de su `AcademicusService.php`, pero su interfaz es renderizada *dentro* del panel de `Certificatum`.
* **`Nexus` (Satélite de Personas):** El sistema maestro para gestionar personas (estudiantes, docentes). Al igual que `Academicus`, no tiene interfaz propia para el cliente, sino que expone su funcionalidad a través de `NexusService.php` para ser usada en la sección "Estudiantes" del panel de `Certificatum`.

### Modelo de Comunicación: API Interna (Service Classes)

La comunicación se mantiene a través de "Service Classes" de PHP. Cada módulo tiene su "puerta de entrada" lógica:

* `nexus/NexusService.php`
* `academicus/AcademicusService.php`
* `certificatum/CertificatumService.php`

Esta comunicación es interna, directa y de alto rendimiento.

!\[Diagrama de Arquitectura](https://i.imgur.com/2Xqg2rU.png)
*(Diagrama actualizado: Certificatum es el Host UI)*

---

## 2\. Plan de Implementación por Fases (Revisado)

Este plan está diseñado para reutilizar el código existente y evolucionar el panel actual de `Certificatum`.

### **FASE 0: Refactorización Interna (El Gran Desacople)**

**Duración Estimada:** 1-2 semanas
**Objetivo:** Separar la lógica de negocio del panel actual de `Certificatum`. **Esta fase es idéntica al plan anterior y sigue siendo el primer paso crucial.**

1. **Crear `nexus/NexusService.php`:** Mover toda la lógica de gestión de `estudiantes` desde los archivos `administrare\_\*.php` a esta nueva clase (`NexusService::getEstudiantes()`, `NexusService::crearEstudiante()`, `NexusService::actualizarEstudiante()`, etc.).
2. **Crear `academicus/AcademicusService.php`:** Mover toda la lógica de `cursos` e `inscripciones` a esta nueva clase (`AcademicusService::getCursos()`, `AcademicusService::crearCurso()`, etc.).
3. **Refactorizar `certificatum/administrare.php`:** Modificar el panel actual para que, en lugar de llamar a sus funciones locales, llame a los nuevos métodos estáticos de `NexusService` y `AcademicusService`.

   * **Resultado:** Al final de esta fase, el panel de `Certificatum` se ve y funciona igual, pero su código interno ya está desacoplado y listo para la siguiente fase.

---

### **FASE 1: Creación del Panel Unificado de `Certificatum`**

**Duración Estimada:** 2-3 semanas
**Objetivo:** Evolucionar el panel `certificatum/administrare.php` en una interfaz unificada.

1. **Rediseñar la Navegación del Panel:**

   * **Acción:** Modificar la estructura de `certificatum/administrare.php` (o crear una copia `panel\_unificado.php`) para tener una navegación principal clara.
   * **Ejemplo de Menú:** `Dashboard | Certificados | Gestión Académica | Plantillas`.

2. **Crear la Sección "Gestión Académica":**

   * Dentro de esta nueva sección, crear las sub-pestañas: `Cursos`, `Cohortes`, `Estudiantes`, `Docentes`.
   * **Reutilizar Código:** Copiar y adaptar masivamente los componentes de UI (tablas, formularios) del viejo panel.
   * La pestaña **"Cursos"** llamará a los métodos de `AcademicusService`.
   * La pestaña **"Estudiantes"** llamará a los métodos de `NexusService`.
   * El cliente percibe todo como una única herramienta integrada.

3. **Escribir Script de Migración de Datos:**

   * Crear un script `migracion\_nexus\_academicus.php`.
   * **Acción:** Leerá los datos de las tablas del viejo sistema y los insertará en las nuevas tablas de `Nexus` y `Academicus`. Este paso es idéntico al plan anterior.

4. **Lanzamiento:**

   * Una vez que el nuevo panel unificado esté funcional, se convierte en el panel de administración principal para todos los clientes.

---

### **FASE 2: Consolidación de la Lógica de Certificación**

**Duración Estimada:** 1 semana
**Objetivo:** Asegurar que la lógica de emisión y mapeo de certificados funcione fluidamente desde el nuevo panel.

1. **Crear `certificatum/CertificatumService.php`:**

   * **Acción:** Formalizar la lógica de gestión de plantillas y reglas de emisión en esta clase de servicio.

2. **Integrar la Gestión de Plantillas:**

   * La sección "Plantillas" del panel unificado permitirá al usuario gestionar los diseños de sus certificados.
   * Se creará una nueva interfaz para el **mapeo de reglas**, donde el cliente podrá asociar un `Curso` (de `AcademicusService`) con una `Plantilla` (de `CertificatumService`).
   * El proceso de "Emisión Masiva" consultará estas reglas y llamará a `AcademicusService` para obtener la lista de alumnos aprobados.

---

## 3\. Conclusión de la Estrategia

Este plan revisado pone al producto estrella, **`Certificatum`**, en el centro de la experiencia del cliente, lo cual es una decisión de producto más sólida. Mantiene todos los beneficios de una arquitectura de backend limpia y desacoplada, mientras resuelve la preocupación de una experiencia de usuario fragmentada. El camino de implementación sigue siendo claro, pragmático y reutiliza el trabajo ya hecho.

