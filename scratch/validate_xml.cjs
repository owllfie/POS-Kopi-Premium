const fs = require('fs');
const path = require('path');
const { DOMParser } = require('xmldom');

const xmlPath = path.join(__dirname, 'extracted_docx', 'word', 'document.xml');
const relsPath = path.join(__dirname, 'extracted_docx', 'word', '_rels', 'document.xml.rels');

function validateFile(filePath, label) {
    if (fs.existsSync(filePath)) {
        const xml = fs.readFileSync(filePath, 'utf8');
        console.log(`Parsing ${label}...`);
        
        let errors = [];
        const parser = new DOMParser({
            errorHandler: {
                warning: (msg) => console.warn('Warning:', msg),
                error: (msg) => {
                    console.error('Error:', msg);
                    errors.push(msg);
                },
                fatalError: (msg) => {
                    console.error('Fatal Error:', msg);
                    errors.push(msg);
                }
            }
        });

        try {
            const doc = parser.parseFromString(xml, 'text/xml');
            console.log(`${label} Parsing finished.`);
            if (errors.length > 0) {
                console.log(`Found ${errors.length} parsing errors in ${label}!`);
            } else {
                console.log(`${label} is completely valid!`);
            }
        } catch (e) {
            console.error(`Exception during parsing ${label}:`, e.message);
        }
    } else {
        console.error(`${label} not found at ${filePath}`);
    }
}

validateFile(xmlPath, "document.xml");
validateFile(relsPath, "document.xml.rels");
