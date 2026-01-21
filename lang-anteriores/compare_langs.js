const fs = require('fs');

function parseLangFile(content) {
    // Extract the $lang array content
    const langMatch = content.match(/\$lang = \[(.*?)\];/s);
    if (!langMatch) {
        throw new Error("Could not find $lang array");
    }
    
    const langContent = langMatch[1];
    
    // Parse the key-value pairs using regex
    const pairs = [];
    const regex = /'([^']*)'\s*=>\s*'([^']*)'/g;
    let match;
    
    while ((match = regex.exec(langContent)) !== null) {
        const key = match[1];
        const value = match[2];
        pairs.push({ key, value });
    }
    
    // Convert to object
    const langObj = {};
    for (const pair of pairs) {
        langObj[pair.key] = pair.value;
    }
    
    return langObj;
}

function compareLangs(esFile, elFile) {
    const esContent = fs.readFileSync(esFile, 'utf-8');
    const elContent = fs.readFileSync(elFile, 'utf-8');
    
    const esLang = parseLangFile(esContent);
    const elLang = parseLangFile(elContent);
    
    // Find entries that exist in es_AR but not in el_GR
    const faltantes = {};
    for (const [key, value] of Object.entries(esLang)) {
        if (!elLang.hasOwnProperty(key)) {
            faltantes[key] = value;
        }
    }
    
    // Find entries that have different values between the two files
    const diferentes = {};
    for (const [key, esValue] of Object.entries(esLang)) {
        if (elLang.hasOwnProperty(key) && elLang[key] !== esValue) {
            diferentes[key] = {
                'es_AR': esValue,
                'el_GR': elLang[key]
            };
        }
    }
    
    return { faltantes, diferentes };
}

function main() {
    const { faltantes, diferentes } = compareLangs('es_AR.php', 'el_GR.php');
    
    console.log("=== ENTRIES MISSING IN el_GR.php ===\n");
    for (const [key, value] of Object.entries(faltantes)) {
        console.log(`'${key}' => '${value}',`);
    }
    
    console.log("\n=== ENTRIES WITH DIFFERENT VALUES ===\n");
    for (const [key, values] of Object.entries(diferentes)) {
        console.log(`'${key}' => es_AR: '${values['es_AR']}', el_GR: '${values['el_GR']}',`);
    }
}

main();