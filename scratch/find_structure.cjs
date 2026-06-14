const fs = require('fs');
const path = require('path');

const docFile = path.join(__dirname, 'extracted_docx', 'word', 'document.xml');
if (fs.existsSync(docFile)) {
    const xml = fs.readFileSync(docFile, 'utf8');
    
    // Find index of "BAB IV" and "BAB V" in the xml
    const indexIV = xml.indexOf('BAB IV');
    const indexV = xml.indexOf('BAB V');
    
    console.log("BAB IV found at:", indexIV);
    console.log("BAB V found at:", indexV);
    
    if (indexIV !== -1 && indexV !== -1) {
        // Let's grab some context before and after
        const beforeIV = xml.substring(indexIV - 500, indexIV + 100);
        const middle = xml.substring(indexIV, indexIV + 500);
        const aroundV = xml.substring(indexV - 500, indexV + 200);
        
        console.log("\n--- AROUND BAB IV ---");
        console.log(beforeIV);
        console.log("\n--- MIDDLE BAB IV ---");
        console.log(middle);
        console.log("\n--- AROUND BAB V ---");
        console.log(aroundV);
    }
} else {
    console.log("File not found");
}
