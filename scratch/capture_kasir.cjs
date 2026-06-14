const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');

const browserPath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const outputPath = path.join(__dirname, 'screenshots', 'dashboard_kasir.png');

async function run() {
    console.log("Launching Edge to capture Kasir Dashboard...");
    const browser = await puppeteer.launch({
        executablePath: browserPath,
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });

    try {
        console.log("Simulating role 4 (Kasir)...");
        await page.goto('http://127.0.0.1:8000/simulate-role/4', { waitUntil: 'domcontentloaded' });
        await new Promise(r => setTimeout(r, 2000));

        console.log("Navigating to dashboard...");
        await page.goto('http://127.0.0.1:8000/dashboard', { waitUntil: 'domcontentloaded' });
        
        // Wait 3 seconds for rendering
        console.log("Waiting 3 seconds...");
        await new Promise(r => setTimeout(r, 3000));

        await page.screenshot({ path: outputPath });
        console.log(`Saved screenshot to ${outputPath}`);
    } catch (err) {
        console.error("Error:", err.message);
    }

    await browser.close();
}

run();
