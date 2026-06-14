const fs = require('fs');
const path = require('path');

const docFile = path.join(__dirname, 'extracted_docx', 'word', 'document.xml');
if (fs.existsSync(docFile)) {
    const xml = fs.readFileSync(docFile, 'utf8');
    
    let indexT_IV = xml.indexOf('<w:t>BAB IV</w:t>', 200000);
    let pStart_IV = xml.lastIndexOf('<w:p', indexT_IV);
    let pEnd_IV = xml.indexOf('</w:p>', indexT_IV) + 6;

    let indexT_V = xml.indexOf('<w:t>BAB V</w:t>', 200000);
    let pStart_V = xml.lastIndexOf('<w:p', indexT_V);
    
    console.log("BAB IV heading paragraph start:", pStart_IV);
    console.log("BAB IV heading paragraph end:", pEnd_IV);
    console.log("BAB V heading paragraph start:", pStart_V);
    
    // Let's print the BAB IV paragraph and the start of BAB V paragraph
    console.log("\n--- BAB IV Heading Paragraph ---");
    console.log(xml.substring(pStart_IV, pEnd_IV));
    console.log("\n--- BAB V Heading Paragraph Start ---");
    console.log(xml.substring(pStart_V, pStart_V + 200));
}
