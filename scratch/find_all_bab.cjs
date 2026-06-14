const fs = require('fs');
const path = require('path');

const docFile = path.join(__dirname, 'extracted_docx', 'word', 'document.xml');
if (fs.existsSync(docFile)) {
    const xml = fs.readFileSync(docFile, 'utf8');
    
    // Find all occurrences of BAB IV
    let pos = 0;
    console.log("--- OCCURRENCES OF BAB IV ---");
    while (true) {
        pos = xml.indexOf('BAB IV', pos);
        if (pos === -1) break;
        console.log(`Found at ${pos}:`);
        console.log(xml.substring(pos - 200, pos + 200));
        pos += 6;
    }
    
    // Find all occurrences of BAB V
    pos = 0;
    console.log("\n--- OCCURRENCES OF BAB V ---");
    while (true) {
        pos = xml.indexOf('BAB V', pos);
        if (pos === -1) break;
        console.log(`Found at ${pos}:`);
        console.log(xml.substring(pos - 200, pos + 200));
        pos += 5;
    }
}
