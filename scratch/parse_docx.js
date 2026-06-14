const fs = require('fs');
const path = require('path');

const docxDir = path.join(__dirname, 'extracted_docx');
const relsFile = path.join(docxDir, 'word', '_rels', 'document.xml.rels');
const docFile = path.join(docxDir, 'word', 'document.xml');

// 1. Read relations
let rels = {};
if (fs.existsSync(relsFile)) {
    const relsXml = fs.readFileSync(relsFile, 'utf8');
    const relMatches = relsXml.matchAll(/Id="([^"]+)"[^>]+Target="([^"]+)"/g);
    for (const match of relMatches) {
        rels[match[1]] = match[2];
    }
}

console.log("Found Relationships:", Object.keys(rels).length);

// 2. Read document xml
if (fs.existsSync(docFile)) {
    const docXml = fs.readFileSync(docFile, 'utf8');
    
    // We want to extract text and image locations.
    // Let's parse the XML in a simple way or line-by-line.
    // A quick way is to use a simple sax-like parser or regex.
    // Let's write a quick regex-based extractor.
    
    // We can extract all paragraphs <w:p>
    // inside each paragraph, we look for text <w:t> and drawings <w:drawing>
    
    const pRegex = /<w:p(?:\s+[^>]*)*>([\s\S]*?)<\/w:p>/g;
    const tRegex = /<w:t(?:\s+[^>]*)*>([\s\S]*?)<\/w:t>/g;
    const rIdRegex = /r:embed="([^"]+)"/g;
    
    let paragraphs = [];
    let match;
    let pCount = 0;
    
    // Let's walk through the document XML using index
    let lastIndex = 0;
    let out = [];
    
    // Let's do a simple tag-based parser to maintain order of text and images
    let pos = 0;
    while(pos < docXml.length) {
        let pStart = docXml.indexOf('<w:p', pos);
        if (pStart === -1) break;
        let pEnd = docXml.indexOf('</w:p>', pStart);
        if (pEnd === -1) break;
        
        let pContent = docXml.substring(pStart, pEnd + 6);
        pos = pEnd + 6;
        
        pCount++;
        
        // Extract all text in this paragraph
        let textParts = [];
        let pPos = 0;
        let imagesInP = [];
        
        // Find all text runs and drawings inside this paragraph
        // We can do this by scanning for tags in order
        let subPos = 0;
        while(subPos < pContent.length) {
            let nextT = pContent.indexOf('<w:t', subPos);
            let nextDrawing = pContent.indexOf('<w:drawing', subPos);
            
            if (nextT === -1 && nextDrawing === -1) break;
            
            if (nextDrawing === -1 || (nextT !== -1 && nextT < nextDrawing)) {
                // Process text
                let tEnd = pContent.indexOf('</w:t>', nextT);
                if (tEnd === -1) {
                    subPos = nextT + 4;
                    continue;
                }
                let tOpenEnd = pContent.indexOf('>', nextT);
                let text = pContent.substring(tOpenEnd + 1, tEnd);
                // remove XML entities
                text = text.replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&').replace(/&quot;/g, '"');
                textParts.push(text);
                subPos = tEnd + 6;
            } else {
                // Process drawing
                let dEnd = pContent.indexOf('</w:drawing>', nextDrawing);
                if (dEnd === -1) {
                    subPos = nextDrawing + 10;
                    continue;
                }
                let drawingContent = pContent.substring(nextDrawing, dEnd + 12);
                let embedMatch = drawingContent.match(/r:embed="([^"]+)"/);
                if (embedMatch) {
                    let rId = embedMatch[1];
                    let imgPath = rels[rId] || 'unknown';
                    imagesInP.push({ rId, imgPath });
                }
                subPos = dEnd + 12;
            }
        }
        
        let pText = textParts.join('');
        out.push({
            index: pCount,
            text: pText,
            images: imagesInP
        });
    }
    
    // Save to a text file for inspection
    let formattedOut = out.map(p => {
        let prefix = `[P ${p.index}] `;
        let imgStr = p.images.map(img => `\n[IMAGE: ${img.imgPath} (rel: ${img.rId})]`).join('');
        return prefix + p.text + imgStr;
    }).join('\n');
    
    fs.writeFileSync(path.join(__dirname, 'docx_content.txt'), formattedOut, 'utf8');
    console.log(`Parsed ${pCount} paragraphs. Saved to docx_content.txt`);
} else {
    console.error("document.xml not found!");
}
