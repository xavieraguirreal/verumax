# Implementación de CKEditor 5 en VERUMax

## Comparación de Editores

### Editor Actual (administrare.php)
```html
<textarea name="contenido" rows="10"
          class="w-full px-4 py-2 border border-gray-300 rounded-lg">
</textarea>
```
❌ Sin formato visual
❌ Usuario debe escribir HTML manualmente
❌ Propenso a errores de sintaxis

### CKEditor 5 (gestion_mensajes_tempo.php)
✅ WYSIWYG (What You See Is What You Get)
✅ **No requiere API key**
✅ CDN gratuito
✅ Fácil implementación
✅ Soporte en español
✅ Toolbar personalizable

---

## Implementación en VERUMax

### 1. Incluir CDN en el HTML (en `<head>`)

```html
<!-- CKEditor 5 Classic -->
<script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
```

### 2. Textarea HTML

```html
<textarea name="contenido" id="contenido_editor">
    <?php echo htmlspecialchars($pagina['contenido']); ?>
</textarea>
```

### 3. Inicializar el Editor (JavaScript al final del `<body>`)

```javascript
<script>
let editorInstance;
ClassicEditor
    .create(document.querySelector('#contenido_editor'), {
        toolbar: {
            items: [
                'heading', '|',
                'bold', 'italic', 'underline', '|',
                'link', '|',
                'bulletedList', 'numberedList', '|',
                'indent', 'outdent', '|',
                'blockQuote', '|',
                'undo', 'redo'
            ]
        },
        language: 'es',
        placeholder: 'Escriba aquí el contenido de la página...'
    })
    .then(editor => {
        editorInstance = editor;
        console.log('CKEditor cargado correctamente');
    })
    .catch(error => {
        console.error('Error al cargar CKEditor:', error);
    });
</script>
```

### 4. CSS Opcional (altura mínima del editor)

```css
<style>
    .ck-editor__editable {
        min-height: 300px;
    }
</style>
```

---

## Toolbar Items Disponibles

### Básicos
- `heading` - Encabezados (H1, H2, H3, etc.)
- `bold` - Negrita
- `italic` - Cursiva
- `underline` - Subrayado
- `strikethrough` - Tachado

### Formato
- `bulletedList` - Lista con viñetas
- `numberedList` - Lista numerada
- `indent` / `outdent` - Aumentar/reducir sangría
- `blockQuote` - Cita de bloque
- `code` - Código inline
- `codeBlock` - Bloque de código

### Enlaces y Multimedia
- `link` - Insertar enlace
- `imageUpload` - Subir imagen (requiere backend)
- `mediaEmbed` - Insertar video (YouTube, Vimeo, etc.)

### Otros
- `horizontalLine` - Línea horizontal
- `table` - Insertar tabla
- `undo` / `redo` - Deshacer/rehacer

---

## Configuración Recomendada para VERUMax

### Admin Panel (Contenido Completo)
```javascript
toolbar: {
    items: [
        'heading', '|',
        'bold', 'italic', 'underline', '|',
        'link', 'bulletedList', 'numberedList', '|',
        'indent', 'outdent', '|',
        'blockQuote', 'horizontalLine', '|',
        'undo', 'redo'
    ]
}
```

### Mensajes (Contenido Simple)
```javascript
toolbar: {
    items: [
        'bold', 'italic', 'underline', '|',
        'link', '|',
        'bulletedList', 'numberedList', '|',
        'undo', 'redo'
    ]
}
```

---

## Ventajas sobre TinyMCE o Quill

| Característica | CKEditor 5 | TinyMCE | Quill |
|----------------|------------|---------|-------|
| **API Key** | ❌ No requiere | ⚠️ Limitado sin key | ❌ No requiere |
| **CDN Gratuito** | ✅ Sí | ⚠️ Limitado | ✅ Sí |
| **Español** | ✅ Nativo | ✅ Nativo | ⚠️ Requiere config |
| **Peso** | ~200KB | ~400KB | ~100KB |
| **Facilidad** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ | ⭐⭐⭐⭐ |
| **Personalización** | ⭐⭐⭐⭐⭐ | ⭐⭐⭐⭐⭐ | ⭐⭐⭐ |

---

## Archivos a Modificar en VERUMax

### 1. `identitas/administrare.php`
- Reemplazar `<textarea>` simple por CKEditor 5
- Para edición de páginas (Sobre Nosotros, Servicios, Contacto)

### 2. `admin/modulos/general.php` (opcional)
- Si se quieren editar descripciones o contenidos HTML

### 3. Cualquier formulario que edite HTML
- Buscar con `grep -r "textarea.*contenido" --include="*.php"`

---

## Ejemplo Completo

```html
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editor de Contenido</title>
    <script src="https://cdn.ckeditor.com/ckeditor5/40.1.0/classic/ckeditor.js"></script>
    <style>
        .ck-editor__editable {
            min-height: 400px;
        }
    </style>
</head>
<body>
    <form method="POST" action="">
        <label>Contenido de la Página:</label>
        <textarea name="contenido" id="contenido_editor">
            <h2>Sobre Nosotros</h2>
            <p>Contenido inicial...</p>
        </textarea>

        <button type="submit">Guardar</button>
    </form>

    <script>
    ClassicEditor
        .create(document.querySelector('#contenido_editor'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', '|', 'bulletedList', 'numberedList', '|', 'undo', 'redo'],
            language: 'es'
        })
        .then(editor => {
            console.log('Editor listo');
        })
        .catch(error => {
            console.error(error);
        });
    </script>
</body>
</html>
```

---

## Enlaces Útiles

- **Documentación**: https://ckeditor.com/docs/ckeditor5/latest/
- **Builder Online**: https://ckeditor.com/ckeditor-5/online-builder/
- **Demos**: https://ckeditor.com/ckeditor-5/demo/

---

## Implementado en:

- ✅ `gestion_mensajes_tempo.php` - Sistema de mensajes a estudiantes
- ✅ `identitas/administrare.php` - Panel de administración de Identitas (2025-11-22)
- ✅ `admin/modulos/identitas.php` - Módulo Identitas en Admin VERUMax (2025-11-22) - **Reemplazó TinyMCE**

---

**Última actualización**: 2025-11-22
