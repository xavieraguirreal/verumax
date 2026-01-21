const fs = require('fs');

function parsePhpLangFile(filePath) {
    const content = fs.readFileSync(filePath, 'utf8');
    
    // Extract the array content between $lang = [ ... ];
    const arrayMatch = content.match(/\$lang\s*=\s*\[(.*?)\];/s);
    if (!arrayMatch) {
        throw new Error("Could not find $lang array in the file");
    }
    
    const arrayContent = arrayMatch[1];
    
    // Find all key-value pairs in the format 'key' => 'value'
    // This regex accounts for potential newlines and comments within the array
    const keyValuePairs = [];
    // Updated regex to handle escaped quotes inside values
    const regex = /'([^']*)'\s*=>\s*'((?:[^'\\]|\\.)*?)'(?:\s*,|$|(?=\s*[\n\r]))/g;
    let match;
    while ((match = regex.exec(arrayContent)) !== null) {
        keyValuePairs.push([match[1], match[2]]);
    }
    
    // Create an object from the key-value pairs
    const result = {};
    keyValuePairs.forEach(([key, value]) => {
        // Handle escaped quotes in the value
        value = value.replace(/\\'/g, "'");
        result[key] = value;
    });
    
    return result;
}

function compareLanguageFiles(esArFile, caEsFile) {
    const esAr = parsePhpLangFile(esArFile);
    const caEs = parsePhpLangFile(caEsFile);
    
    // Find entries that exist in es_AR but not in ca_ES (missing in ca_ES)
    const missingInCaEs = {};
    for (const [key, value] of Object.entries(esAr)) {
        if (!(key in caEs)) {
            missingInCaEs[key] = value;
        }
    }
    
    // Find entries that have different values between the two files
    const differentValues = {};
    const commonKeys = Object.keys(esAr).filter(key => key in caEs);
    for (const key of commonKeys) {
        if (esAr[key] !== caEs[key]) {
            differentValues[key] = {
                'es_AR': esAr[key],
                'ca_ES': caEs[key]
            };
        }
    }
    
    return { missingInCaEs, differentValues };
}

const esArPath = "D:\\\\validarcert\\\\lang\\\\es_AR.php";
const caEsPath = "D:\\\\validarcert\\\\lang\\\\ca_ES.php";

try {
    const { missingInCaEs, differentValues } = compareLanguageFiles(esArPath, caEsPath);
    
    console.log("=== ENTRIES MISSING IN ca_ES.php (exist in es_AR.php but not in ca_ES.php) ===\n");
    if (Object.keys(missingInCaEs).length > 0) {
        for (const [key, value] of Object.entries(missingInCaEs)) {
            console.log(`    '${key}' => '${value}',`);
        }
    } else {
        console.log("No entries are missing in ca_ES.php\n");
    }
    
    console.log("\n=== ENTRIES WITH DIFFERENT VALUES BETWEEN es_AR.php AND ca_ES.php ===\n");
    if (Object.keys(differentValues).length > 0) {
        for (const [key, values] of Object.entries(differentValues)) {
            console.log(`Key: '${key}'`);
            console.log(`es_AR value: '${values['es_AR']}'`);
            console.log(`ca_ES value: '${values['ca_ES']}'`);
            console.log("-".repeat(50));
        }
    } else {
        console.log("No entries have different values between the files\n");
    }
} catch (error) {
    console.error("Error:", error.message);
}