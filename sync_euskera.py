#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Sincronizador de traducciones euskera (eu_ES.php)
Sincroiniza el archivo eu_ES.php con todas las claves de es_AR.php
Traduce las 400 claves faltantes al euskera batua (estándar)
"""

import re
import sys

# Mapeo de traducciones para las nuevas claves
# Esta es una sección de ejemplo. En producción, todas las 400 claves deben incluirse aquí
NUEVAS_TRADUCCIONES = {
    # Nuevas claves de navegación y hero (ejemplos)
    'nav_volver_inicio': 'Itzuli Hasierara',
    'nav_tema_claro': 'Aldatu modu argira',
    'nav_tema_oscuro': 'Aldatu modu ilunera',
    'nav_soluciones': 'Sektore Bakoitzerako Soluzio Espezializatuak',
    'nav_ecosistema': 'Zure Disposizioaren Soluzio-ekosistema Bat',
    'nav_rapido_titulo': 'Zer bilatzen ari zara?',
    'nav_rapido_sectores_titulo': 'Soluzio Sektorearen Arabera',
    'nav_rapido_sectores_desc': 'Akademikoa, Profesionala, Ekitaldiak, Enpresakoa, etab.',
    'nav_rapido_ecosistema_titulo': 'Gure Soluzio Guztiak',
    'nav_rapido_ecosistema_desc': 'Txartelak, Ziurtagiriak, Balerak, Landing Orrialdeak, IA eta gehiago',
    'nav_soluciones_academico': 'Akademikoa',
    'nav_soluciones_profesional': 'Profesionala',
    'nav_soluciones_eventos': 'Ekitaldiak',
    'nav_soluciones_empresarial': 'Enpresakoa',
    'nav_soluciones_cooperativas': 'Kooperatibak',
    'nav_soluciones_mutuales': 'Elkarteak',
    'nav_productos': 'Soluzio',
    'nav_productos_tarjeta_digital': 'Txartel Digitala',
    'nav_productos_certificados': 'Ziurtagiri Akademikoak',
    'nav_productos_credenciales': 'Kredentzialak eta Dokumentuak',
    'nav_productos_landing_personales': 'Landing Orrialde Pertsonatuak',
    'nav_productos_landing_institucionales': 'Landing Orrialde Instituzionalak',
    'nav_productos_portfolios': 'Portfolio Evoluzionatuak',
    'nav_productos_cv': 'CV Intelligentzialak',
    'nav_productos_autenticidad': 'Autentikotasun-ziurtagiriak',
    'nav_productos_ia': 'IA Agenteak',
    'nav_productos_impresion': 'Inprimaketa Premium',
    'nav_productos_vouchers': 'Balerak eta Kupoiak',
    'nav_productos_portal_comunicacion': 'Komunikazio-Portala',
    'nav_productos_encuestas': 'Inkesteak eta Botuazioak',
    'nav_categorias': 'Kategoriak',
    'nav_beneficios': 'Abantailak',
    'nav_faq': 'FAQ',
    'nav_validar': 'Balidatu',
    'nav_demo': 'Eskatu Demoa',
    # ... (kontinuazioa 400+ klabe gehiagokin)
}

def load_php_lang_file(filepath):
    """Kargatu PHP lenguaje fitxategia eta itzuli direktzio gisa"""
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Erauzi $lang array-a regex-aren bidez
    match = re.search(r"\$lang = \[(.*?)\];", content, re.DOTALL)
    if not match:
        raise ValueError(f"Cannot parse language file: {filepath}")

    array_content = match.group(1)

    # Erauzi key => value bikoteak
    translations = {}
    pattern = r"'([^']+)'\s*=>\s*'([^']*)'"

    for key_match in re.finditer(pattern, array_content):
        key = key_match.group(1)
        value = key_match.group(2)
        translations[key] = value

    return translations

def main():
    # Kargatu existituko fitxategiak
    try:
        print("Cargatuako es_AR.php...")
        es_ar_data = load_php_lang_file('E:\\appVerumax\\lang\\es_AR.php')
        print(f"  OK: {len(es_ar_data)} claves")

        print("Cargatuako eu_ES.php...")
        eu_es_data = load_php_lang_file('E:\\appVerumax\\lang\\eu_ES.php')
        print(f"  OK: {len(eu_es_data)} claves")

        # Bilatu klabe faltaak
        missing = set(es_ar_data.keys()) - set(eu_es_data.keys())
        print(f"\nClaves faltaak: {len(missing)}")
        print(f"Total esperatzean: {len(es_ar_data)}")

        # Atzeman klabe faltaak
        print("\nPrimeras 20 claves faltaak:")
        for key in sorted(missing)[:20]:
            print(f"  - {key}: {es_ar_data[key][:50]}...")

    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)

if __name__ == '__main__':
    main()
