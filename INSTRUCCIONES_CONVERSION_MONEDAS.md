# Sistema de Conversión de Monedas - Instrucciones

## ✅ Implementación Completada

Se ha implementado un sistema completo de conversión de monedas que muestra los precios en **Pesos Argentinos (ARS)** y **Dólares (USD)** para usuarios de Argentina.

## 📂 Archivos Creados/Modificados

### Archivos Nuevos:
1. **`includes/currency_converter.php`** - Sistema de conversión con cache
2. **`cache/`** - Directorio para almacenar tasas de cambio
3. **`cache/.gitignore`** - Evita subir cache al repositorio
4. **`cache/README.md`** - Documentación del sistema

### Archivos Modificados:
1. **`identitas.php`** - Ahora muestra precios duales (ARS + USD)
2. **`lang/es_AR.php`** - Traducciones actualizadas

## 🎯 Cómo Funciona

### Para usuarios de Argentina (es_AR):
- **Se muestra**: AR$ 14.850 / Anual
  <br>($ 9 USD)

### Para otros países:
- **Se muestra**: $ 9 USD / Anual

## 🔧 API Utilizada

**ExchangeRate-API (Versión Open - GRATIS)**
- URL: https://open.er-api.com/v6/latest/USD
- Sin límite de requests
- Actualización automática cada 24 horas
- Cache local para optimizar rendimiento

### Opcional: API Key Personalizada

Si querés más velocidad y confiabilidad, podés obtener una API key gratis en:
https://www.exchangerate-api.com (1,500 requests/mes gratis)

Para usarla:
1. Registrate en https://www.exchangerate-api.com
2. Obtené tu API key
3. Abrí `includes/currency_converter.php`
4. Reemplazá la línea 24:
   ```php
   $api_url = 'https://open.er-api.com/v6/latest/USD';
   ```
   Por:
   ```php
   $api_url = 'https://v6.exchangerate-api.com/v6/TU_API_KEY_AQUI/latest/USD';
   ```

## 💰 Tasas de Fallback

Si la API falla, el sistema usa tasas predefinidas:
- **ARS**: 1,650 pesos por dólar
- **CLP**: 950 pesos chilenos
- **EUR**: 0.92 euros
- **BRL**: 5.50 reales

### Actualizar Tasas de Fallback:
1. Abrí `includes/currency_converter.php`
2. Buscá la función `get_fallback_rates()` (línea 68)
3. Actualizá los valores:
   ```php
   'ARS' => 1650.00,  // Actualizar con tasa actual
   ```

## 🌍 Monedas Soportadas

El sistema soporta automáticamente:
- 🇦🇷 **Argentina** → Peso Argentino (ARS)
- 🇨🇱 **Chile** → Peso Chileno (CLP)
- 🇺🇾 **Uruguay** → Peso Uruguayo (UYU)
- 🇧🇷 **Brasil** → Real (BRL)
- 🇲🇽 **México** → Peso Mexicano (MXN)
- 🇪🇸 **España/Cataluña/Euskadi** → Euro (EUR)
- 🇵🇹 **Portugal** → Euro (EUR)
- 🇬🇷 **Grecia** → Euro (EUR)
- 🇺🇸 **USA** → Dólar (USD)

## 📊 Ejemplo Visual

### Plan Basicum - Argentina (es_AR):
```
AR$ 14.850 / Anual
($ 9 USD)
```

### Plan Premium - Argentina (es_AR):
```
AR$ 31.350 / Anual
($ 19 USD)
```

### Plan Basicum - USA (en_US):
```
$ 9 USD / Anual
```

## 🔍 Verificar que Funciona

1. Abrí tu navegador en modo incógnito
2. Visitá: http://localhost/identitas.php?lang=es_AR
3. Scrolleá hasta la sección de planes
4. Deberías ver precios en ARS + USD

## 🐛 Solución de Problemas

### Error: "Call to undefined function display_price()"
**Solución**: Verificá que `identitas.php` tenga en la línea 8:
```php
require_once 'includes/currency_converter.php';
```

### Los precios se muestran en USD en lugar de ARS
**Posibles causas**:
1. El idioma no es `es_AR`
2. La API está caída → revisa tasas de fallback
3. Error de permisos en directorio `cache/`

### La conversión parece incorrecta
1. Verificá las tasas de fallback en `currency_converter.php`
2. Eliminá `cache/exchange_rates.json` para forzar actualización
3. Verificá que la API responde: https://open.er-api.com/v6/latest/USD

## 📝 Mantenimiento

### Cada 2-3 meses:
1. Actualizá las tasas de fallback en `currency_converter.php`
2. Fuentes confiables:
   - https://dolarhoy.com (Argentina)
   - https://www.xe.com (internacional)

### Verificación de permisos:
El directorio `cache/` debe tener permisos de escritura para que PHP pueda guardar el cache.

## 🎨 Personalización

### Cambiar formato de precios:
Editá la función `format_price()` en `currency_converter.php` (línea 97)

### Agregar nueva moneda:
1. Agregá el código de moneda en `$lang_to_currency` (línea 143)
2. Agregá el símbolo en `$symbols` (línea 101)
3. Agregá tasa de fallback (línea 68)

## ✨ Características Implementadas

✅ Conversión automática de USD a ARS
✅ Cache de 24 horas para optimizar rendimiento
✅ Tasas de fallback si la API falla
✅ Soporte para múltiples monedas
✅ Formato correcto según país (AR$ 1.000 vs $ 1,000.00)
✅ Actualización automática diaria
✅ Sistema robusto con manejo de errores

## 📞 Soporte

Si tenés problemas:
1. Verificá los logs de PHP
2. Revisá que el directorio `cache/` tenga permisos
3. Comprobá que la API responda: https://open.er-api.com/v6/latest/USD

---

**Implementado por**: Claude Code
**Fecha**: 24/10/2025
**Versión**: 1.0
