const fs = require('fs');
const path = require('path');

const relsFile = path.join(__dirname, 'extracted_docx', 'word', '_rels', 'document.xml.rels');
if (fs.existsSync(relsFile)) {
    const xml = fs.readFileSync(relsFile, 'utf8');
    const matches = xml.matchAll(/Id="rId(\d+)"/g);
    let maxId = 0;
    for (const match of matches) {
        const id = parseInt(match[1], 10);
        if (id > maxId) {
            maxId = id;
        }
    }
    console.log("Maximum rId found in rels:", maxId);
} else {
    console.log("rels file not found");
}
