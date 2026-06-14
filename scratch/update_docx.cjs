const fs = require('fs');
const path = require('path');

const docxDir = path.join(__dirname, 'extracted_docx');
const docFile = path.join(docxDir, 'word', 'document.xml');
const relsFile = path.join(docxDir, 'word', '_rels', 'document.xml.rels');
const mediaDir = path.join(docxDir, 'word', 'media');
const screenshotsDir = path.join(__dirname, 'screenshots');

// 1. Copy screenshots and map them
const coreMappings = [
    { from: 'login.png', to: 'image28.png' },
    { from: 'dashboard_superadmin.png', to: 'image29.png' },
    { from: 'kelola_meja.png', to: 'image30.png' },
    { from: 'halaman_menu_customer.png', to: 'image31.png' },
    { from: 'kelola_akses.png', to: 'image32.png' },
    { from: 'log_aktivitas.png', to: 'image33.png' },
    { from: 'web_setting.png', to: 'image34.png' },
    { from: 'laporan.png', to: 'image35.png' },
    { from: 'daftar_pesanan_chef.png', to: 'image36.png' },
    { from: 'kelola_users.png', to: 'image37.png' },
    { from: 'kelola_karyawan.png', to: 'image38.png' },
    { from: 'backup_database.png', to: 'image39.png' }
];

console.log("Copying core screenshots...");
for (const map of coreMappings) {
    const src = path.join(screenshotsDir, map.from);
    const dest = path.join(mediaDir, map.to);
    if (fs.existsSync(src)) {
        fs.copyFileSync(src, dest);
        console.log(`  Copied ${map.from} -> ${map.to}`);
    } else {
        console.warn(`  Warning: Source ${map.from} not found!`);
    }
}

// We will use strictly sequential rId numbers starting from 100
const newScreenshots = [
    { from: 'dashboard_manager.png', to: 'image_new1.png', rId: 'rId101' },
    { from: 'dashboard_kasir.png', to: 'image_new2.png', rId: 'rId102' },
    { from: 'daftar_pesanan_kasir.png', to: 'image_new3.png', rId: 'rId103' },
    { from: 'halaman_pembayaran.png', to: 'image_new4.png', rId: 'rId104' },
    { from: 'riwayat_transaksi.png', to: 'image_new5.png', rId: 'rId105' },
    { from: 'kelola_menu.png', to: 'image_new6.png', rId: 'rId106' },
    { from: 'kelola_kategori.png', to: 'image_new7.png', rId: 'rId107' },
    { from: 'kelola_shift.png', to: 'image_new8.png', rId: 'rId108' },
    { from: 'kelola_jabatan.png', to: 'image_new9.png', rId: 'rId109' },
    { from: 'kelola_slip_gaji.png', to: 'image_new10.png', rId: 'rId110' },
    { from: 'kelola_bahan_alat.png', to: 'image_new11.png', rId: 'rId111' },
    { from: 'kelola_properti.png', to: 'image_new12.png', rId: 'rId112' },
    { from: 'face_scan.png', to: 'image_new13.png', rId: 'rId113' }
];

console.log("Copying additional screenshots...");
for (const ns of newScreenshots) {
    const src = path.join(screenshotsDir, ns.from);
    const dest = path.join(mediaDir, ns.to);
    if (fs.existsSync(src)) {
        fs.copyFileSync(src, dest);
        console.log(`  Copied ${ns.from} -> ${ns.to}`);
    } else {
        console.warn(`  Warning: Source ${ns.from} not found!`);
    }
}

// 2. Register additional image relations in word/_rels/document.xml.rels
console.log("Registering relations...");
if (fs.existsSync(relsFile)) {
    let relsXml = fs.readFileSync(relsFile, 'utf8');
    
    const insertPos = relsXml.lastIndexOf('</Relationships>');
    if (insertPos !== -1) {
        let newRels = '';
        for (const ns of newScreenshots) {
            if (!relsXml.includes(`Id="${ns.rId}"`)) {
                newRels += `<Relationship Id="${ns.rId}" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/${ns.to}"/>`;
            }
        }
        relsXml = relsXml.substring(0, insertPos) + newRels + relsXml.substring(insertPos);
        fs.writeFileSync(relsFile, relsXml, 'utf8');
        console.log("  Successfully registered new relationships in document.xml.rels");
    }
}

// Helper to generate strict 8-character hex IDs
function generateHexId() {
    return Math.floor((1 + Math.random()) * 0x100000000).toString(16).substring(1).toUpperCase();
}

// 3. Edit document.xml content
console.log("Modifying document.xml...");
if (fs.existsSync(docFile)) {
    let docXml = fs.readFileSync(docFile, 'utf8');
    
    // Replace text inside <w:t> tags for names and dates
    docXml = docXml.replace(/<w:t>([\s\S]*?)<\/w:t>/g, (match, text) => {
        let newText = text;
        
        // Date replacement
        newText = newText.replace(/23 Februari 2026/g, "12 Juni 2026");
        
        // Application name replacement
        newText = newText.replace(/Aplikasi Rapor Digital/g, "Sistem Point of Sales (POS) Premium");
        newText = newText.replace(/Sistem Informasi Rapor Digital/g, "Sistem POS Premium");
        newText = newText.replace(/sistem informasi rapor digital/g, "sistem pos premium");
        newText = newText.replace(/Rapor Digital/g, "Sistem POS Premium");
        newText = newText.replace(/rapor digital/g, "sistem pos");
        newText = newText.replace(/\brapor\b/g, "sistem pos");
        newText = newText.replace(/\bRapor\b/g, "Sistem POS");
        
        return `<w:t>${newText}</w:t>`;
    });

    // Update explanations in Bab 3
    docXml = docXml.replace(/Class diagram diatas menggambarkan primary key dan foreign key antar tabel yang dibutuhkan oleh sistem aplikasi rapor digital\. Terdapat 11 tabel dan semuanya saling terhubung\./g, 
        "Class diagram diatas menggambarkan primary key dan foreign key antar tabel yang dibutuhkan oleh sistem POS. Terdapat 18 tabel utama (termasuk role, users, shift, menu, kategori, meja, pesanan, detail_pesanan, karyawan, absensi, jabatan, slip_gaji, bahan_alat, promo, keuangan_transaksi, dan log audit) yang saling terhubung.");
    
    docXml = docXml.replace(/ERD diatas menggambarkan relasi antar tabel yang dibutuhkan untuk sistem informasi rapor digital\./g,
        "ERD diatas menggambarkan relasi antar tabel yang dibutuhkan untuk sistem POS.");

    // 4. Rebuild the XML content of Bab IV
    let indexT_IV = docXml.indexOf('<w:t>BAB IV</w:t>', 200000);
    let pStart_IV = docXml.lastIndexOf('<w:p ', indexT_IV);
    let pEnd_IV = docXml.indexOf('</w:p>', indexT_IV) + 6;

    let indexT_V = docXml.indexOf('<w:t>BAB V</w:t>', 200000);
    let pStart_V = docXml.lastIndexOf('<w:p ', indexT_V);
    
    console.log(`  BAB IV Heading Paragraph boundaries: [${pStart_IV}, ${pEnd_IV}]`);
    console.log(`  BAB V Heading Paragraph starts at: ${pStart_V}`);

    const bab4Pages = [
        { title: "4.1. Halaman Login", desc: "Halaman ini digunakan oleh seluruh pengguna sistem (Kasir, Chef, Manager, Admin, Superadmin) untuk melakukan otentikasi menggunakan email dan kata sandi.", rId: "rId49" },
        { title: "4.2. Halaman Dashboard Superadmin", desc: "Dashboard utama untuk Superadmin yang menyajikan grafik tren pendapatan, data metrik operasional (total transaksi, pendapatan hari ini, meja aktif, menu tersedia), serta daftar log aktivitas audit sistem terbaru secara real-time.", rId: "rId50" },
        { title: "4.3. Halaman Dashboard Manager", desc: "Dashboard operasional untuk Manager. Berfokus pada analisis kinerja keuangan restoran, menampilkan grafik tren penjualan harian/mingguan/bulanan, serta rekapitulasi pendapatan berjalan.", rId: "rId101" },
        { title: "4.4. Halaman Dashboard Kasir", desc: "Dashboard operasional untuk Kasir. Halaman ini berfungsi sebagai laporan kas live untuk memantau waktu shift aktif kasir, total pendapatan tunai (cash) versus QRIS, serta riwayat transaksi kasir yang sedang bertugas.", rId: "rId102" },
        { title: "4.5. Halaman Menu Pelanggan (QR Code)", desc: "Menu publik yang diakses pelanggan setelah melakukan pemindaian QR code meja. Pelanggan dapat memilih kategori menu, menambahkan item ke keranjang belanja, memberikan catatan khusus, dan mengirim pesanan.", rId: "rId54" },
        { title: "4.6. Daftar Pesanan Dapur (Chef View)", desc: "Halaman antrean dapur untuk Chef untuk melihat pesanan yang perlu dimasak, mengubah status pesanan dari menunggu menjadi sedang dimasak, dan menyelesaikannya.", rId: "rId59" },
        { title: "4.7. Daftar Pesanan Kasir (Kasir View)", desc: "Antrean pesanan aktif restoran untuk kasir, yang menampilkan status meja dan tombol cepat untuk menuju halaman pembayaran.", rId: "rId103" },
        { title: "4.8. Halaman Pembayaran", desc: "Form kasir untuk memproses pembayaran meja, memilih metode cash atau QRIS, menghitung nominal kembalian, dan mencetak struk transaksi.", rId: "rId104" },
        { title: "4.9. Laporan Keuangan", desc: "Ringkasan keuangan restoran yang dapat difilter harian/mingguan/bulanan, mencakup pencatatan buku besar pemasukan dan pengeluaran kas operasional restoran.", rId: "rId58" },
        { title: "4.10. Riwayat Transaksi", desc: "Catatan riwayat seluruh transaksi pembayaran yang berhasil diselesaikan, lengkap dengan pencarian nomor struk, filter kasir, serta fitur soft-delete/trash.", rId: "rId105" },
        { title: "4.11. Kelola Users", desc: "Halaman administrasi pengguna sistem untuk menambahkan, mengubah, atau menonaktifkan akun kasir, chef, manager, dan admin.", rId: "rId60" },
        { title: "4.12. Kelola Menu", desc: "Modul CRUD data menu makanan/minuman, harga, foto, kategori, dan tombol cepat status ketersediaan menu.", rId: "rId106" },
        { title: "4.13. Kelola Kategori", desc: "Pengelolaan kategori menu restoran (Makanan, Minuman, Snack, dll) untuk mempermudah penyajian menu pelanggan.", rId: "rId107" },
        { title: "4.14. Kelola Meja", desc: "Pengelolaan nomor meja restoran beserta pembuatan otomatis (regenerasi) token unik QR Code.", rId: "rId30" },
        { title: "4.15. Kelola Shift", desc: "Rekapitulasi waktu kerja dan rekonsiliasi kas kasir pada akhir shift untuk mendeteksi adanya selisih uang tunai.", rId: "rId108" },
        { title: "4.16. Kelola Akses", desc: "Modul matriks perizinan bagi Superadmin untuk menonaktifkan atau mengaktifkan hak akses modul bagi role tertentu.", rId: "rId55" },
        { title: "4.17. Log Aktivitas", desc: "Riwayat audit sistem yang mencatat setiap aksi penting pengguna beserta detail waktu, IP address, dan jenis aktivitas.", rId: "rId56" },
        { title: "4.18. Web Setting", desc: "Pengaturan nama restoran, logo restoran, persentase pajak transaksi, dan footer struk fisik.", rId: "rId57" },
        { title: "4.19. Backup Database", desc: "Fasilitas Superadmin untuk mengekspor database SQL ke dalam file backup untuk keamanan data.", rId: "rId62" },
        { title: "4.20. Kelola Karyawan", desc: "Database biodata lengkap karyawan yang terintegrasi dengan jabatan dan slip gaji.", rId: "rId61" },
        { title: "4.21. Kelola Jabatan", desc: "Pengaturan jenjang karier karyawan restoran beserta konfigurasi gaji pokok dan tunjangan.", rId: "rId109" },
        { title: "4.22. Kelola Slip Gaji", desc: "Penerbitan dan pencatatan riwayat slip gaji bulanan karyawan berdasarkan akumulasi kehadiran.", rId: "rId110" },
        { title: "4.23. Kelola Bahan & Alat (Inventaris)", desc: "Manajemen inventaris bahan baku dapur dan aset alat makan untuk memantau stok minimum.", rId: "rId111" },
        { title: "4.24. Kelola Properti", desc: "Manajemen data properti dan aset fisik berharga milik restoran.", rId: "rId112" },
        { title: "4.25. Face Scan Attendance", desc: "Halaman absensi mandiri karyawan menggunakan sistem pemindaian wajah kamera lokal restoran.", rId: "rId113" }
    ];

    let newBab4Xml = '';
    const pStyleSubheading = `<w:pPr><w:pStyle w:val="Heading2"/><w:spacing w:line="360" w:lineRule="auto"/><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:b/><w:bCs/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr>`;
    const pStyleText = `<w:pPr><w:spacing w:line="360" w:lineRule="auto"/><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr>`;
    const pStyleImage = `<w:pPr><w:spacing w:line="360" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr>`;
    
    let picCounter = 1000;
    
    for (const page of bab4Pages) {
        const paraId1 = generateHexId();
        const textId1 = generateHexId();
        const paraId2 = generateHexId();
        const textId2 = generateHexId();
        const paraId3 = generateHexId();
        const textId3 = generateHexId();
        const paraId4 = generateHexId();
        const textId4 = generateHexId();
        const anchorId = generateHexId();
        const editId = generateHexId();

        // 1. Add Heading 2
        newBab4Xml += `<w:p w14:paraId="${paraId1}" w14:textId="${textId1}" w:rsidR="00EF6930" w:rsidRDefault="00EF6930" w:rsidP="00EF6930">${pStyleSubheading}<w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:b/><w:bCs/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>${page.title}</w:t></w:r></w:p>`;
        
        // 2. Add description text
        newBab4Xml += `<w:p w14:paraId="${paraId2}" w14:textId="${textId2}" w:rsidR="00EF6930" w:rsidRDefault="00EF6930" w:rsidP="00EF6930">${pStyleText}<w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>${page.desc}</w:t></w:r></w:p>`;
        
        // 3. Add drawing image
        const docId = picCounter++;
        const drawingXml = `<w:p w14:paraId="${paraId3}" w14:textId="${textId3}" w:rsidR="00B27CF7" w:rsidRDefault="00B27CF7" w:rsidP="00F87659">${pStyleImage}<w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:noProof/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:drawing><wp:inline distT="0" distB="0" distL="0" distR="0" wp14:anchorId="${anchorId}" wp14:editId="${editId}"><wp:extent cx="5715000" cy="3571875"/><wp:effectExtent l="0" t="0" r="0" b="0"/><wp:docPr id="${docId}" name="Picture ${docId}"/><wp:cNvGraphicFramePr><a:graphicFrameLocks xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" noChangeAspect="1"/></wp:cNvGraphicFramePr><a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"><pic:nvPicPr><pic:cNvPr id="${docId}" name="Picture ${docId}"/><pic:cNvPicPr/></pic:nvPicPr><pic:blipFill><a:blip r:embed="${page.rId}" cstate="print"><a:extLst><a:ext uri="{28A0092B-C50C-407E-A947-70E740481C1C}"><a14:useLocalDpi xmlns:a14="http://schemas.microsoft.com/office/drawing/2010/main" val="0"/></a:ext></a:extLst></a:blip><a:stretch><a:fillRect/></a:stretch></pic:blipFill><pic:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="5715000" cy="3571875"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></pic:spPr></pic:pic></a:graphicData></a:graphic></wp:inline></w:drawing></w:r></w:p>`;
        newBab4Xml += drawingXml;
        
        // 4. Add caption text under the image
        newBab4Xml += `<w:p w14:paraId="${paraId4}" w14:textId="${textId4}" w:rsidR="00EF6930" w:rsidRDefault="00EF6930" w:rsidP="00EF6930"><w:pPr><w:pStyle w:val="Caption"/><w:spacing w:line="360" w:lineRule="auto"/><w:jc w:val="center"/><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:b/><w:bCs/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr></w:pPr><w:r><w:rPr><w:rFonts w:ascii="Times New Roman" w:hAnsi="Times New Roman" w:cs="Times New Roman"/><w:b/><w:bCs/><w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr><w:t>Gambar 4. ${picCounter - 1000} Halaman ${page.title.split('. ')[1]}</w:t></w:r></w:p>`;
    }

    const updatedXml = docXml.substring(0, pEnd_IV) + newBab4Xml + docXml.substring(pStart_V);
    fs.writeFileSync(docFile, updatedXml, 'utf8');
    console.log("  Successfully wrote new Bab IV content to document.xml");
} else {
    console.error("document.xml not found!");
}

console.log("Finished update script!");
