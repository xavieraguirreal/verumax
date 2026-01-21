import re
import os

def extract_lang_dict(file_path):
    """Extrae el diccionario de traducciones de un archivo PHP"""
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Buscar el inicio del array $lang
    start_match = re.search(r'\$lang\s*=\s*\[', content)
    if not start_match:
        return {}
    
    # Extraer todo el contenido del array
    start_pos = start_match.end()
    bracket_count = 1
    end_pos = start_pos
    
    for i in range(start_pos, len(content)):
        if content[i] == '[':
            bracket_count += 1
        elif content[i] == ']':
            bracket_count -= 1
            if bracket_count == 0:
                end_pos = i
                break
    
    array_content = content[start_pos:end_pos]
    
    # Parsear las entradas del array
    entries = {}
    # Expresión regular para encontrar pares clave => valor
    pattern = r"'([^']*)'\s*=>\s*'(.*?)(?<!\\)',"
    matches = re.findall(pattern, array_content, re.DOTALL)
    
    for key, value in matches:
        # Des-escapar comillas simples
        value = value.replace("\\'", "'")
        entries[key] = value
    
    return entries

# Rutas de los archivos
es_ar_path = 'lang/es_AR.php'
es_uy_path = 'lang/es_UY.php'

# Extraer diccionarios
lang_ar = extract_lang_dict(es_ar_path)
lang_uy = extract_lang_dict(es_uy_path)

# Encontrar entradas faltantes en es_UY
faltantes = {}
diferentes = {}
extra_uy = {}

for key, value in lang_ar.items():
    if key not in lang_uy:
        faltantes[key] = value
    elif lang_uy[key] != value:
        diferentes[key] = {
            'ar': value,
            'uy': lang_uy[key]
        }

for key, value in lang_uy.items():
    if key not in lang_ar:
        extra_uy[key] = value

print("=== ENTRADAS FALTANTES EN es_UY.php ===")
print(f"Cantidad: {len(faltantes)}\n")

for key, value in faltantes.items():
    print(f"'{key}' => '{value}',")

print("\n=== ENTRADAS CON VALORES DIFERENTES ===")
print(f"Cantidad: {len(diferentes)}\n")

for key, values in diferentes.items():
    print(f"Clave: {key}")
    print(f"  AR: {values['ar']}")
    print(f"  UY: {values['uy']}")
    print()

print("\n=== ENTRADAS EXTRAS EN es_UY.php ===")
print(f"Cantidad: {len(extra_uy)}\n")

for key, value in extra_uy.items():
    print(f"'{key}' => '{value}',")