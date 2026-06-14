const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');

const browserPath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const screenshotDir = path.join(__dirname, 'screenshots');

if (!fs.existsSync(screenshotDir)) {
    fs.mkdirSync(screenshotDir, { recursive: true });
}

// Map page names to URLs, and the role ID to simulate before visiting
const pages = [
    { name: 'login', url: 'http://127.0.0.1:8000/login', role: null },
    
    // Superadmin pages (Role 1)
    { name: 'dashboard_superadmin', url: 'http://127.0.0.1:8000/dashboard', role: 1 },
    { name: 'kelola_users', url: 'http://127.0.0.1:8000/users', role: 1 },
    { name: 'kelola_menu', url: 'http://127.0.0.1:8000/menu', role: 1 },
    { name: 'kelola_kategori', url: 'http://127.0.0.1:8000/kategori', role: 1 },
    { name: 'kelola_meja', url: 'http://127.0.0.1:8000/meja', role: 1 },
    { name: 'kelola_shift', url: 'http://127.0.0.1:8000/shift', role: 1 },
    { name: 'kelola_akses', url: 'http://127.0.0.1:8000/akses', role: 1 },
    { name: 'log_aktivitas', url: 'http://127.0.0.1:8000/log', role: 1 },
    { name: 'web_setting', url: 'http://127.0.0.1:8000/setting', role: 1 },
    { name: 'backup_database', url: 'http://127.0.0.1:8000/backup', role: 1 },
    { name: 'kelola_karyawan', url: 'http://127.0.0.1:8000/karyawan', role: 1 },
    { name: 'kelola_jabatan', url: 'http://127.0.0.1:8000/jabatan', role: 1 },
    { name: 'kelola_slip_gaji', url: 'http://127.0.0.1:8000/slip-gaji', role: 1 },
    { name: 'kelola_bahan_alat', url: 'http://127.0.0.1:8000/bahan-alat', role: 1 },
    { name: 'kelola_properti', url: 'http://127.0.0.1:8000/properti', role: 1 },
    { name: 'face_scan', url: 'http://127.0.0.1:8000/face-scan', role: 1 },
    
    // Manager pages (Role 3)
    { name: 'dashboard_manager', url: 'http://127.0.0.1:8000/dashboard', role: 3 },
    { name: 'laporan', url: 'http://127.0.0.1:8000/laporan', role: 3 },
    { name: 'riwayat_transaksi', url: 'http://127.0.0.1:8000/transaksi', role: 3 },
    
    // Kasir pages (Role 4)
    { name: 'dashboard_kasir', url: 'http://127.0.0.1:8000/dashboard', role: 4 },
    { name: 'daftar_pesanan_kasir', url: 'http://127.0.0.1:8000/pesanan', role: 4 },
    { name: 'halaman_pembayaran', url: 'http://127.0.0.1:8000/pesanan/1/bayar', role: 4 },
    
    // Chef pages (Role 5)
    { name: 'daftar_pesanan_chef', url: 'http://127.0.0.1:8000/pesanan', role: 5 },
    
    // Customer Menu Page (QR)
    { name: 'halaman_menu_customer', url: 'http://127.0.0.1:8000/menu/table-1-033bb0c7', role: null }
];

async function run() {
    console.log("Launching Edge...");
    const browser = await puppeteer.launch({
        executablePath: browserPath,
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    await page.setViewport({ width: 1280, height: 800 });

    for (const p of pages) {
        console.log(`Processing page: ${p.name}`);
        
        try {
            if (p.role !== null) {
                // Simulate role first
                console.log(`  Simulating role ${p.role}...`);
                await page.goto(`http://127.0.0.1:8000/simulate-role/${p.role}`, { waitUntil: 'networkidle0' });
            } else {
                // Clear cookies to simulate guest/logged out
                console.log('  Clearing cookies...');
                const client = await page.target().createCDPSession();
                await client.send('Network.clearBrowserCookies');
            }
            
            console.log(`  Navigating to ${p.url}...`);
            await page.goto(p.url, { waitUntil: 'networkidle2', timeout: 30000 });
            
            // Wait 1 second for any animations or transitions to complete
            await new Promise(r => setTimeout(r, 1000));
            
            const screenshotPath = path.join(screenshotDir, `${p.name}.png`);
            await page.screenshot({ path: screenshotPath });
            console.log(`  Screenshot saved to ${screenshotPath}`);
        } catch (err) {
            console.error(`  Error processing page ${p.name}:`, err.message);
        }
    }

    await browser.close();
    console.log("Finished all screenshots!");
}

run();
