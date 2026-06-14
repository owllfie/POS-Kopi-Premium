const fs = require('fs');
const path = require('path');

const docFile = path.join(__dirname, 'extracted_docx', 'word', 'document.xml');
if (fs.existsSync(docFile)) {
    const xml = fs.readFileSync(docFile, 'utf8');
    
    // Find an image paragraph. An image paragraph usually contains <w:drawing>
    const drawPos = xml.indexOf('<w:drawing');
    if (drawPos !== -1) {
        const pStart = xml.lastIndexOf('<w:p ', drawPos);
        const pEnd = xml.indexOf('</w:p>', drawPos) + 6;
        console.log("--- SAMPLE IMAGE PARAGRAPH ---");
        console.log(xml.substring(pStart, pEnd));
    }
    
    // Find a normal text paragraph. Let's find one near line 118 (Latar Belakang)
    const textPos = xml.indexOf('1.1. Latar Belakang');
    if (textPos !== -1) {
        const pStart = xml.lastIndexOf('<w:p ', textPos);
        const pEnd = xml.indexOf('</w:p>', textPos) + 6;
        console.log("\n--- SAMPLE TEXT PARAGRAPH ---");
        console.log(xml.substring(pStart, pEnd));
    }
}
