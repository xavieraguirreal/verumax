import re
import os

def load_lang_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    # Extract the $lang array using regex
    pattern = r'\$lang = \[(.*?)\];'
    matches = re.search(pattern, content, re.DOTALL)
    
    if not matches:
        raise ValueError(f"Could not find $lang array in {filepath}")
    
    lang_content = matches.group(1)
    
    # Parse the key-value pairs
    lang_dict = {}
    
    # Match 'key' => 'value' patterns
    pair_pattern = r"'([^']*)'\s*=>\s*'([^']*)'"
    matches = re.findall(pair_pattern, lang_content)
    
    for key, value in matches:
        lang_dict[key] = value
    
    return lang_dict

def compare_langs(es_file, el_file):
    es_lang = load_lang_file(es_file)
    el_lang = load_lang_file(el_file)
    
    # Find entries that exist in es_AR but not in el_GR
    faltantes = {}
    for key, value in es_lang.items():
        if key not in el_lang:
            faltantes[key] = value
    
    # Find entries that have different values between the two files
    diferentes = {}
    for key, es_value in es_lang.items():
        if key in el_lang and el_lang[key] != es_value:
            diferentes[key] = {
                'es_AR': es_value,
                'el_GR': el_lang[key]
            }
    
    return faltantes, diferentes

def main():
    es_file = 'es_AR.php'
    el_file = 'el_GR.php'
    
    faltantes, diferentes = compare_langs(es_file, el_file)
    
    print("=== ENTRIES MISSING IN el_GR.php ===\n")
    for key, value in faltantes.items():
        print(f"'{key}' => '{value}',")
    
    print("\n=== ENTRIES WITH DIFFERENT VALUES ===\n")
    for key, values in diferentes.items():
        print(f"'{key}' => es_AR: '{values['es_AR']}', el_GR: '{values['el_GR']}',")

if __name__ == "__main__":
    main()