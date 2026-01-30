# CLAUDE_AGENTS.md

Configuración de agentes especializados para Claude Code en VERUMax.

---

## help-manual-auditor

**Propósito:** Auditar la cobertura de ayuda en el panel administrativo y mantener actualizado el manual de usuario para clientes (administradores de instituciones).

### Contexto del Sistema de Ayuda

El panel admin de VERUMax tiene un sistema de ayuda contextual implementado en cada módulo:

```
admin/modulos/
├── general.php      ← Configuración institucional
├── certificatum.php ← Gestión de certificados
├── actividad.php    ← Monitoreo de actividad
├── email_stats.php  ← Estadísticas de emails
└── identitas.php    ← Presencia digital (EXCLUIDO por ahora)
```

### Implementación Técnica de la Ayuda

Cada módulo implementa:

1. **Panel lateral deslizante** - Se abre con F1 o botón flotante
2. **Objeto `contenidoAyuda`** - Diccionario JS con secciones de ayuda
3. **Función `mostrarSeccionAyuda(seccion)`** - Muestra contenido específico
4. **Función `togglePanelAyuda()`** - Abre/cierra el panel
5. **Footer con recursos globales** - FAQ, Glosario, Errores (siempre visible)

Estructura del objeto `contenidoAyuda`:
```javascript
const contenidoAyuda = {
    'seccion-id': {
        titulo: 'Título de la sección',
        contenido: `<div class="...">HTML del contenido</div>`
    },
    'guias': {
        titulo: 'Guías de Inicio',
        contenido: `...`,
        tutoriales: [
            {
                id: 'tutorial-id',
                titulo: 'Nombre del tutorial',
                pasos: [
                    { titulo: 'Paso 1', descripcion: '...' },
                    // ...más pasos
                ]
            }
        ]
    }
};
```

### Módulos y Secciones Actuales

#### General (general.php)
- **Secciones:** bienvenida, institucional, apariencia, sistema, guias
- **Recursos:** faq, glosario, errores-comunes
- **Tutoriales:** subir-logo, configurar-colores, activar-modulos, configurar-firmantes, config-inicial

#### Certificatum (certificatum.php)
- **Secciones:** configuracion, estudiantes, docentes, cursos, inscripciones, asignaciones, evaluaciones, logs, bienvenida, dashboard, personas, matriculas
- **Recursos:** faq-certificatum, glosario-certificatum, errores-certificatum
- **Tutoriales:** dashboard, agregar-estudiante, agregar-docente, crear-curso, inscribir-estudiante, asignar-docente, crear-evaluacion, notificar-estudiantes, configurar-certificados, importar-datos, exportar-datos
- **Guías especiales:** primer-certificado, importar-masivo, formas-inscribir, enviar-email, certificados-docentes

#### Actividad (actividad.php)
- **Secciones:** dashboard, comunicaciones, validaciones-qr, notificaciones, bienvenida, interpretar-metricas, solucionar-emails
- **Recursos:** faq-actividad, glosario-actividad, errores-actividad
- **Tutoriales:** ver-comunicaciones, ver-validaciones, configurar-notificaciones, usar-dashboard

#### Email Stats (email_stats.php)
- **Secciones:** estadisticas, webhook, notificaciones (básico)
- **Estado:** Ayuda mínima, considerar expandir

### Tareas del Agente

1. **Auditar cobertura:**
   - Verificar que cada tab/funcionalidad tenga ayuda correspondiente
   - Identificar secciones sin documentar
   - Revisar consistencia de estilo entre módulos

2. **Mantener manual:**
   - Actualizar `docs/manual_usuario.md` basado en contenidoAyuda
   - Agregar capturas de pantalla si es necesario
   - Mantener índice actualizado

3. **Sincronizar:**
   - Si se agrega ayuda nueva → actualizar manual
   - Si se modifica funcionalidad → actualizar ayuda y manual

### Archivo del Manual

**Ubicación:** `docs/manual_usuario.md`
**Vista:** `admin/manual.php`
**PDF:** Generado con mPDF desde el MD

### Checklist de Auditoría

```markdown
## General
- [ ] Información institucional (nombre, descripción, contacto)
- [ ] Logo y favicon
- [ ] Colores y paletas
- [ ] Configuración de firmantes
- [ ] Módulos activos
- [ ] Modo construcción
- [ ] Robots/SEO

## Certificatum
- [ ] Dashboard y métricas
- [ ] CRUD Estudiantes
- [ ] CRUD Docentes
- [ ] CRUD Cursos
- [ ] Inscripciones y estados
- [ ] Asignaciones docentes
- [ ] Sistema de evaluaciones
- [ ] Importación masiva
- [ ] Exportación de datos
- [ ] Envío de emails
- [ ] Logs de actividad

## Actividad
- [ ] Dashboard de actividad
- [ ] Comunicaciones (emails enviados)
- [ ] Validaciones QR
- [ ] Configuración de notificaciones
- [ ] Interpretación de métricas
- [ ] Solución de problemas de emails
```

### Cómo Invocar el Agente

Usar el Task tool con:
```
subagent_type: "help-manual-auditor"
prompt: "Descripción de la tarea específica"
```

**Ejemplos de uso:**
- "Auditar cobertura de ayuda en módulo Certificatum"
- "Actualizar manual con nueva funcionalidad de evaluaciones"
- "Verificar consistencia de estilo en FAQ de todos los módulos"
- "Generar reporte de secciones sin documentar"

---

## verumax-translator

**Propósito:** Traducir archivos de idioma de VERUMax desde español argentino (es_AR) a todos los idiomas regionales soportados, manteniendo lenguaje formal e inclusivo binario.

### Idiomas Soportados

| Código | Idioma | Región | Notas |
|--------|--------|--------|-------|
| **es_AR** | Español | Argentina | **FUENTE** - Base para todas las traducciones |
| es_BO | Español | Bolivia | Variante latinoamericana, similar a AR |
| es_CL | Español | Chile | Variante latinoamericana |
| es_EC | Español | Ecuador | Variante latinoamericana |
| es_ES | Español | España | Variante peninsular (vosotros, ordenador, móvil) |
| es_PY | Español | Paraguay | Variante latinoamericana, similar a AR |
| es_UY | Español | Uruguay | Variante latinoamericana, muy similar a AR |
| pt_BR | Português | Brasil | Formal (você), adaptaciones culturales |
| pt_PT | Português | Portugal | Formal, diferencias léxicas con BR |
| en_US | English | Estados Unidos | Gender-neutral, formal |
| ca_ES | Català | Catalunya | Formal académico |
| eu_ES | Euskara | País Vasco | Formal académico |
| el_GR | Ελληνικά | Grecia | Formal |

### Criterios de Traducción

#### 1. Lenguaje Formal (usted/você/you)
- **Español:** Usar "usted" siempre, nunca tuteo ni voseo
- **Portugués BR:** Usar "você" (formal en Brasil)
- **Portugués PT:** Usar "você" o formas impersonales
- **Inglés:** Formal pero accesible, evitar contracciones en textos principales

#### 2. Lenguaje Binario Inclusivo
Aplicar formato `palabra/a` o `palabra/as` según contexto:

| Español | Portugués | Inglés |
|---------|-----------|--------|
| socio/a | associado/a | member |
| asociado/a | associado/a | associate |
| usuario/a | usuário/a | user |
| estudiante | estudante | student |
| docente | docente | instructor |
| profesional | profissional | professional |
| lector/a | leitor/a | reader |
| nuestros/as | nossos/as | our |

**Nota:** En inglés, muchas palabras son naturalmente neutras. Evitar "he/she", preferir "they" o reformular.

#### 3. Variaciones Regionales del Español

| Concepto | Argentina | España | Chile/Ecuador/etc. |
|----------|-----------|--------|-------------------|
| Piscina | pileta | piscina | piscina |
| Teléfono móvil | celular | móvil | celular |
| Computadora | computadora | ordenador | computador/a |
| Correo electrónico | mail/email | correo | correo/email |
| Acera | vereda | acera | vereda/acera |
| Apartamento | departamento | piso | departamento |
| Coche | auto | coche | auto |
| Factura | factura | factura | factura |
| DNI | DNI | DNI/NIF | cédula/RUT |

#### 4. Variaciones Portugués BR vs PT

| Concepto | Brasil | Portugal |
|----------|--------|----------|
| Celular | celular | telemóvel |
| Archivo | arquivo | ficheiro |
| Tren | trem | comboio |
| Desayuno | café da manhã | pequeno-almoço |
| Autobús | ônibus | autocarro |
| CPF (documento) | CPF | NIF |

### Estructura de Archivos

```
lang/
├── es_AR/           ← FUENTE (siempre completo)
│   ├── common.php
│   ├── land_certificatum.php
│   ├── land_credencialis.php
│   ├── land_verumax.php
│   └── land_fabricatum.php
├── es_BO/           ← Destino
├── es_CL/
├── es_EC/
├── es_ES/
├── es_PY/
├── es_UY/
├── pt_BR/
├── pt_PT/
├── en_US/
├── ca_ES/
├── eu_ES/
└── el_GR/
```

### Proceso de Traducción

1. **Leer archivo fuente** (`es_AR/{modulo}.php`)
2. **Verificar si existe destino** (`{idioma}/{modulo}.php`)
3. **Si no existe:** Crear traducción completa
4. **Si existe:** Comparar claves y agregar faltantes
5. **Aplicar criterios:** Formal, inclusivo, regional
6. **Validar sintaxis PHP:** Asegurar que el array sea válido

### Formato de Archivo de Idioma

```php
<?php
/**
 * {Idioma} - {código} - {Módulo}
 * {Descripción del módulo}
 * Lenguaje formal para público amplio, binario inclusivo
 * @version 1.0.0
 */

return [
    'clave_ejemplo' => 'Texto traducido',
    'clave_con_variable' => 'Hola, :nombre',
    // ...
];
```

### Tareas del Agente

1. **Traducir módulo completo:**
   ```
   Traducir land_credencialis.php de es_AR a es_ES
   ```

2. **Traducir a múltiples idiomas:**
   ```
   Traducir land_credencialis.php de es_AR a todos los idiomas faltantes
   ```

3. **Sincronizar claves faltantes:**
   ```
   Sincronizar claves de land_certificatum.php en todos los idiomas
   ```

4. **Auditar traducciones:**
   ```
   Verificar consistencia de lenguaje inclusivo en pt_BR
   ```

### Cómo Invocar el Agente

```
Task tool con:
subagent_type: "verumax-translator"
prompt: "Traducir land_credencialis.php de es_AR a [idioma(s) destino]"
```

**Ejemplos:**
- "Traducir land_credencialis.php a es_ES, es_CL, es_UY"
- "Crear pt_PT/land_verumax.php desde es_AR"
- "Sincronizar todas las claves faltantes en en_US/land_fabricatum.php"
- "Auditar lenguaje inclusivo en todos los archivos pt_BR"

### Checklist de Calidad

```markdown
## Por cada traducción verificar:
- [ ] Lenguaje formal (usted/você/you)
- [ ] Lenguaje inclusivo binario aplicado
- [ ] Variaciones regionales correctas
- [ ] Sin errores de sintaxis PHP
- [ ] Todas las claves del original presentes
- [ ] Variables (:nombre, :fecha) preservadas
- [ ] HTML interno preservado (<span>, <strong>, etc.)
```

---

## Notas para el Desarrollo

- El manual es para **clientes/administradores de instituciones**, no usuarios finales
- Excluir módulo Identitas por ahora
- Mantener lenguaje formal pero accesible
- Usar capturas de pantalla cuando ayuden a la comprensión
- El manual debe poder convertirse a PDF profesional
