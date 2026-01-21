# Fotos de Prueba para Lumen - FotosJuan

## 📸 Fuentes de Fotos Libres de Derechos

Para poblar el portfolio de prueba de FotosJuan, puedes descargar fotos profesionales gratuitas de estas fuentes:

### **1. Unsplash** (Recomendado)
🔗 https://unsplash.com/

**Categorías para FotosJuan:**
- **Bodas:** https://unsplash.com/s/photos/wedding
- **Eventos Corporativos:** https://unsplash.com/s/photos/corporate-event
- **Retratos:** https://unsplash.com/s/photos/portrait
- **Sesiones Familiares:** https://unsplash.com/s/photos/family-portrait

**Licencia:** Completamente gratis, sin atribución requerida

---

### **2. Pexels**
🔗 https://www.pexels.com/

**Búsquedas sugeridas:**
- Wedding photography
- Business conference
- Professional portrait
- Family photos outdoor

**Licencia:** Gratis para uso comercial y personal

---

### **3. Pixabay**
🔗 https://pixabay.com/

**Ventaja:** Gran variedad, también tiene vectores e ilustraciones

---

## 📁 Estructura de Carpetas Actual

```
lumen/
└── uploads/
    └── fotosjuan/
        ├── bodas/           (Crear manualmente)
        ├── eventos/         (Crear manualmente)
        ├── retratos/        (Crear manualmente)
        └── familiar/        (Crear manualmente)
```

---

## 🎯 Fotos Recomendadas para Descargar

### **Bodas (3-5 fotos)**
Buscar en Unsplash:
- "wedding ceremony"
- "bride and groom portrait"
- "wedding reception"
- "wedding dance"

**Sugerencias específicas:**
- 1 foto de ceremonia (iglesia o jardín)
- 1 foto de novios (retrato romántico)
- 1 foto de recepción/fiesta
- 1 foto de detalles (anillos, decoración)

### **Eventos Corporativos (2-3 fotos)**
Buscar:
- "business conference"
- "corporate event"
- "conference speaker"
- "networking event"

### **Retratos (2-3 fotos)**
Buscar:
- "professional headshot"
- "corporate portrait"
- "linkedin profile photo"

### **Sesiones Familiares (2-3 fotos)**
Buscar:
- "family outdoor"
- "family park"
- "happy family portrait"

---

## 🚀 Proceso Manual (Por ahora)

### **Paso 1: Descargar fotos**
1. Ve a Unsplash/Pexels
2. Busca las categorías mencionadas
3. Descarga en **alta resolución**
4. Renombra con formato descriptivo:
   ```
   boda_ceremonia_001.jpg
   boda_novios_002.jpg
   evento_conferencia_001.jpg
   retrato_corporativo_001.jpg
   familia_parque_001.jpg
   ```

### **Paso 2: Crear subcarpetas**
```bash
mkdir lumen/uploads/fotosjuan/bodas
mkdir lumen/uploads/fotosjuan/eventos
mkdir lumen/uploads/fotosjuan/retratos
mkdir lumen/uploads/fotosjuan/familiar
```

### **Paso 3: Copiar fotos**
Coloca las fotos descargadas en sus respectivas carpetas:
```
lumen/uploads/fotosjuan/bodas/boda_ceremonia_001.jpg
lumen/uploads/fotosjuan/bodas/boda_novios_002.jpg
lumen/uploads/fotosjuan/eventos/evento_conferencia_001.jpg
...
```

### **Paso 4: Actualizar lumen_datos.php**
Cambiar los nombres de archivos en `lumen_datos.php` para que coincidan:
```php
'archivo_original' => 'boda_ceremonia_001.jpg',
```

---

## 🔮 Futuro (Fase 2)

Cuando implementemos el **Dashboard de Lumen**, el fotógrafo podrá:
- Subir fotos directamente desde el navegador
- Arrastrar y soltar carpetas completas
- El sistema procesará automáticamente (conversión a WebP, thumbnails, etc.)

---

## 📌 Enlaces Rápidos de Búsqueda

### Unsplash Collections Curadas:
- **Bodas:** https://unsplash.com/collections/1478655/wedding-photography
- **Retratos:** https://unsplash.com/collections/895527/portraits
- **Corporativo:** https://unsplash.com/collections/3657850/business

### Pexels Collections:
- **Bodas:** https://www.pexels.com/search/wedding/
- **Familia:** https://www.pexels.com/search/family/

---

## ⚠️ Importante

- **Tamaño recomendado:** Mínimo 1920x1080px (Full HD)
- **Formato:** JPG (el sistema convertirá a WebP automáticamente en el futuro)
- **Peso:** No importa el tamaño, Lumen lo optimizará
- **Orientación:** Mezcla de horizontal y vertical para portfolio dinámico

---

## 🎨 Paleta Visual Sugerida

Para mantener coherencia visual en el portfolio de prueba:
- **Bodas:** Tonos cálidos, románticos
- **Corporativo:** Tonos fríos, profesionales (azules, grises)
- **Retratos:** Fondos neutros, iluminación profesional
- **Familia:** Naturales, exteriores, luz natural

---

**Última actualización:** 12 de Octubre, 2025
