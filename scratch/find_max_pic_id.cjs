const fs = require('fs');
const path = require('path');

const docFile = path.join(__dirname, 'extracted_docx', 'word', 'document.xml');
if (fs.existsSync(docFile)) {
    const xml = fs.readFileSync(docFile, 'utf8');
    const matches = xml.matchAll(/<wp:docPr[^>]+id="(\d+)"/g);
    let maxId = 0;
    for (const match of matches) {
        const id = parseInt(match[1], 10);
        if (id > maxId) {
            maxId = id;
        }
    }
    console.log("Maximum wp:docPr id found in document.xml:", maxId);
} else {
    console.log("document.xml file not found");
}
