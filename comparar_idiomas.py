#!/usr/bin/env python3
# Script para comparar archivos de idioma es_AR.php y es_ES.php

import re

def extract_entries(file_content):
    """
    Extrae las entradas de idioma de un archivo PHP
    """
    entries = {}
    
    # Patrón para encontrar entradas de tipo 'clave' => 'valor'
    pattern = r"'([^']*)'\s*=>\s*'([^']*)'"
    matches = re.findall(pattern, file_content)
    
    for key, value in matches:
        entries[key] = value
    
    # También buscamos con comillas dobles
    pattern_dq = r'"([^"]*)"\s*=>\s*"([^"]*)"'
    matches_dq = re.findall(pattern_dq, file_content)
    
    for key, value in matches_dq:
        entries[key] = value
    
    return entries

def main():
    # Leer el contenido de ambos archivos
    with open('lang/es_AR.php', 'r', encoding='utf-8') as f:
        es_ar_content = f.read()
    
    with open('lang/es_ES.php', 'r', encoding='utf-8') as f:
        es_es_content = f.read()

    es_ar_entries = extract_entries(es_ar_content)
    es_es_entries = extract_entries(es_es_content)

    # 1. Lista de entradas que están en es_AR.php pero faltan completamente en es_ES.php
    faltantes_es_es = {}
    for clave, valor in es_ar_entries.items():
        if clave not in es_es_entries:
            faltantes_es_es[clave] = valor

    # 2. Lista de entradas que existen en ambos archivos pero tienen valores diferentes
    diferentes = {}
    for clave, valor_ar in es_ar_entries.items():
        if clave in es_es_entries and es_es_entries[clave] != valor_ar:
            diferentes[clave] = {
                'es_AR': valor_ar,
                'es_ES': es_es_entries[clave]
            }

    # 3. Adaptaciones lingüísticas necesarias entre español argentino y español de España
    adaptaciones_linguisticas = {
        'voseo': {
            'title': 'Voseo vs Tuteo',
            'description': 'El español argentino usa "vos" en lugar de "tú", mientras que el español de España usa "tú".',
            'examples': {
                'es_AR': [
                    'Contanos sobre tu necesidad...',
                    'Validar Ahora',
                    'Podés cargar estudiantes de dos formas...'
                ],
                'es_ES': [
                    'Contáctenos sobre su necesidad...',
                    'Validar Ahora',
                    'Puedes cargar estudiantes de dos formas...'
                ]
            }
        },
        'terminos_argentinos': {
            'title': 'Términos específicos del español argentino',
            'description': 'Términos que son propios del español argentino y deben adaptarse al español de España.',
            'examples': {
                'es_AR': [
                    'DNI',
                    'CBU/CVU',
                    'Peso argentino',
                    'Garantías y Autenticidad'
                ],
                'es_ES': [
                    'DNI o NIF/NIE',
                    'IBAN',
                    'Euro',
                    'Garantías y Autenticidad'
                ]
            }
        },
        'expresiones_culturales': {
            'title': 'Expresiones culturales argentinas',
            'description': 'Expresiones o ejemplos específicos de la cultura argentina que deben adaptarse al contexto español.',
            'examples': {
                'es_AR': [
                    'Sociedad Argentina de Justicia Restaurativa',
                    'Cooperativa de Trabajo Liberté',
                    'Emisión en 24hs',
                    'Comenzar a emitir mañana'
                ],
                'es_ES': [
                    'Organización Española de Justicia Restaurativa',
                    'Cooperativa de Trabajo Liberté',
                    'Implementación en 24h',
                    'Comenzar a emitir mañana'
                ]
            }
        },
        'vocabulario_diferente': {
            'title': 'Vocabulario diferente entre variantes',
            'description': 'Términos que se usan de forma diferente entre el español argentino y español de España.',
            'examples': {
                'es_AR': [
                    'emisión (en lugar de expedición)',
                    'estudiante (más común que alumno en ciertos contextos)',
                    'tarjeta de contacto digital'
                ],
                'es_ES': [
                    'expedición (más común en contextos oficiales)',
                    'alumno',
                    'tarjeta de visita digital'
                ]
            }
        }
    }

    # Imprimir los resultados
    print("=== ENTRADAS FALTANTES EN es_ES.php ===")
    if not faltantes_es_es:
        print("No hay entradas que falten en es_ES.php.")
    else:
        for clave, valor in faltantes_es_es.items():
            print(f"'{clave}' => '{valor}'")

    print("\n=== ENTRADAS CON VALORES DIFERENTES ===")
    if not diferentes:
        print("No hay entradas con valores diferentes.")
    else:
        for clave, valores in diferentes.items():
            print(f"'{clave}':")
            print(f"  es_AR: '{valores['es_AR']}'")
            print(f"  es_ES: '{valores['es_ES']}'")

    print("\n=== ADAPTACIONES LINGÜÍSTICAS NECESARIAS ===")
    for categoria, info in adaptaciones_linguisticas.items():
        print(f"\n{info['title']}:")
        print(f"{info['description']}")
        print("Ejemplos:")
        print(f"  Español Argentino: {', '.join(info['examples']['es_AR'])}")
        print(f"  Español España: {', '.join(info['examples']['es_ES'])}")

if __name__ == '__main__':
    main()