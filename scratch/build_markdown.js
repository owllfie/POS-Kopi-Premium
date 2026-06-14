const fs = require('fs');
const path = require('path');

const docxContentFile = path.join(__dirname, 'docx_content.txt');
const outputFile = path.join(__dirname, 'pipeline_buffer.md');

const content = fs.readFileSync(docxContentFile, 'utf8');
const lines = content.split('\n');

let out = [];

// Helper to clean XML tags and perform text substitutions
function cleanText(text) {
    // Remove leftover XML tags
    let clean = text.replace(/<[^>]+>/g, '');
    
    // Substitutions
    clean = clean.replace(/23 Februari 2026/g, "12 Juni 2026");
    clean = clean.replace(/Aplikasi Rapor Digital/g, "Sistem POS Premium");
    clean = clean.replace(/Sistem Informasi Rapor Digital/g, "Sistem POS Premium");
    clean = clean.replace(/sistem informasi rapor digital/g, "sistem pos premium");
    clean = clean.replace(/Rapor Digital/g, "Sistem POS Premium");
    clean = clean.replace(/rapor digital/g, "sistem pos");
    clean = clean.replace(/\brapor\b/g, "sistem pos");
    clean = clean.replace(/\bRapor\b/g, "Sistem POS");
    
    return clean.trim();
}

// Write the Cover Page
out.push("# LAPORAN PERANCANGAN SISTEM POS PREMIUM");
out.push("");
out.push("![](scratch/extracted_docx/word/media/image1.png)");
out.push("");
out.push("**Disusun Oleh:**");
out.push("Nickent Fausta  ");
out.push("24161029  ");
out.push("RPL XI  ");
out.push("");
out.push("**REKAYASA PERANGKAT LUNAK**  ");
out.push("**SMK PERMATA HARAPAN**  ");
out.push("**2026**");
out.push("");
out.push("```{=openxml}");
out.push("<w:p><w:r><w:br w:type=\"page\"/></w:r></w:p>");
out.push("```");
out.push("");

// 25 POS Pages for Bab 4
const bab4Pages = [
    { title: "4.1. Halaman Login", desc: "Halaman ini digunakan oleh seluruh pengguna sistem (Kasir, Chef, Manager, Admin, Superadmin) untuk melakukan otentikasi menggunakan email dan kata sandi.", file: "login.png" },
    { title: "4.2. Halaman Dashboard Superadmin", desc: "Dashboard utama untuk Superadmin yang menyajikan grafik tren pendapatan, data metrik operasional (total transaksi, pendapatan hari ini, meja aktif, menu tersedia), serta daftar log aktivitas audit sistem terbaru secara real-time.", file: "dashboard_superadmin.png" },
    { title: "4.3. Halaman Dashboard Manager", desc: "Dashboard operasional untuk Manager. Berfokus pada analisis kinerja keuangan restoran, menampilkan grafik tren penjualan harian/mingguan/bulanan, serta rekapitulasi pendapatan berjalan.", file: "dashboard_manager.png" },
    { title: "4.4. Halaman Dashboard Kasir", desc: "Dashboard operasional untuk Kasir. Halaman ini berfungsi sebagai laporan kas live untuk memantau waktu shift aktif kasir, total pendapatan tunai (cash) versus QRIS, serta riwayat transaksi kasir yang sedang bertugas.", file: "dashboard_kasir.png" },
    { title: "4.5. Halaman Menu Pelanggan (QR Code)", desc: "Menu publik yang diakses pelanggan setelah melakukan pemindaian QR code meja. Pelanggan dapat memilih kategori menu, menambahkan item ke keranjang belanja, memberikan catatan khusus, dan mengirim pesanan.", file: "halaman_menu_customer.png" },
    { title: "4.6. Daftar Pesanan Dapur (Chef View)", desc: "Halaman antrean dapur untuk Chef untuk melihat pesanan yang perlu dimasak, mengubah status pesanan dari menunggu menjadi sedang dimasak, dan menyelesaikannya.", file: "daftar_pesanan_chef.png" },
    { title: "4.7. Daftar Pesanan Kasir (Kasir View)", desc: "Antrean pesanan aktif restoran untuk kasir, yang menampilkan status meja dan tombol cepat untuk menuju halaman pembayaran.", file: "daftar_pesanan_kasir.png" },
    { title: "4.8. Halaman Pembayaran", desc: "Form kasir untuk memproses pembayaran meja, memilih metode cash atau QRIS, menghitung nominal kembalian, dan mencetak struk transaksi.", file: "halaman_pembayaran.png" },
    { title: "4.9. Laporan Keuangan", desc: "Ringkasan keuangan restoran yang dapat difilter harian/mingguan/bulanan, mencakup pencatatan buku besar pemasukan dan pengeluaran kas operasional restoran.", file: "laporan.png" },
    { title: "4.10. Riwayat Transaksi", desc: "Catatan riwayat seluruh transaksi pembayaran yang berhasil diselesaikan, lengkap dengan pencarian nomor struk, filter kasir, serta fitur soft-delete/trash.", file: "riwayat_transaksi.png" },
    { title: "4.11. Kelola Users", desc: "Halaman administrasi pengguna sistem untuk menambahkan, mengubah, atau menonaktifkan akun kasir, chef, manager, dan admin.", file: "kelola_users.png" },
    { title: "4.12. Kelola Menu", desc: "Modul CRUD data menu makanan/minuman, harga, foto, kategori, dan tombol cepat status ketersediaan menu.", file: "kelola_menu.png" },
    { title: "4.13. Kelola Kategori", desc: "Pengelolaan kategori menu restoran (Makanan, Minuman, Snack, dll) untuk mempermudah penyajian menu pelanggan.", file: "kelola_kategori.png" },
    { title: "4.14. Kelola Meja", desc: "Pengelolaan nomor meja restoran beserta pembuatan otomatis (regenerasi) token unik QR Code.", file: "kelola_meja.png" },
    { title: "4.15. Kelola Shift", desc: "Rekapitulasi waktu kerja dan rekonsiliasi kas kasir pada akhir shift untuk mendeteksi adanya selisih uang tunai.", file: "kelola_shift.png" },
    { title: "4.16. Kelola Akses", desc: "Modul matriks perizinan bagi Superadmin untuk menonaktifkan atau mengaktifkan hak akses modul bagi role tertentu.", file: "kelola_akses.png" },
    { title: "4.17. Log Aktivitas", desc: "Riwayat audit sistem yang mencatat setiap aksi penting pengguna beserta detail waktu, IP address, dan jenis aktivitas.", file: "log_aktivitas.png" },
    { title: "4.18. Web Setting", desc: "Pengaturan nama restoran, logo restoran, persentase pajak transaksi, dan footer struk fisik.", file: "web_setting.png" },
    { title: "4.19. Backup Database", desc: "Fasilitas Superadmin untuk mengekspor database SQL ke dalam file backup untuk keamanan data.", file: "backup_database.png" },
    { title: "4.20. Kelola Karyawan", desc: "Database biodata lengkap karyawan yang terintegrasi dengan jabatan dan slip gaji.", file: "kelola_karyawan.png" },
    { title: "4.21. Kelola Jabatan", desc: "Pengaturan jenjang karier karyawan restoran beserta konfigurasi gaji pokok dan tunjangan.", file: "kelola_jabatan.png" },
    { title: "4.22. Kelola Slip Gaji", desc: "Penerbitan dan pencatatan riwayat slip gaji bulanan karyawan berdasarkan akumulasi kehadiran.", file: "kelola_slip_gaji.png" },
    { title: "4.23. Kelola Bahan & Alat (Inventaris)", desc: "Manajemen inventaris bahan baku dapur dan aset alat makan untuk memantau stok minimum.", file: "kelola_bahan_alat.png" },
    { title: "4.24. Kelola Properti", desc: "Manajemen data properti dan aset fisik berharga milik restoran.", file: "kelola_properti.png" },
    { title: "4.25. Face Scan Attendance", desc: "Halaman absensi mandiri karyawan menggunakan sistem pemindaian wajah kamera lokal restoran.", file: "face_scan.png" }
];

let inBab4 = false;
let currentP = 0;

for (let i = 0; i < lines.length; i++) {
    let line = lines[i].trim();
    if (!line) continue;
    
    // Parse paragraph marker
    let pMatch = line.match(/^\[P (\d+)\]\s*([\s\S]*)$/);
    let imgMatch = line.match(/^\[IMAGE: ([^\]]+)\]/);
    
    if (pMatch) {
        currentP = parseInt(pMatch[1]);
    }
    
    // Skip TOC / List of Figures range (paragraphs 34 to 114)
    if (currentP >= 34 && currentP < 115) {
        continue;
    }
    
    if (imgMatch) {
        let imgPath = imgMatch[1].split(' (rel:')[0].trim();
        // Skip Bab 4 images as they are generated programmatically
        if (inBab4) continue;
        
        // Map images to correct local paths
        out.push(`![](scratch/extracted_docx/word/${imgPath})`);
        out.push("");
        continue;
    }
    
    if (!pMatch) continue;
    
    let text = pMatch[2].trim();
    
    // Skip original Cover Page paragraphs
    if (currentP < 20) {
        continue;
    }
    
    // Skip original Bab 4 content (paragraphs between BAB IV and BAB V)
    if (text.includes("BAB IV")) {
        inBab4 = true;
        out.push("# BAB IV TAMPILAN APLIKASI");
        out.push("");
        
        // Write the 25 POS Pages with descriptions and image markdown links
        for (const page of bab4Pages) {
            out.push(`## ${page.title}`);
            out.push("");
            out.push(page.desc);
            out.push("");
            out.push(`![](scratch/screenshots/${page.file})`);
            out.push("");
            out.push(`*Gambar 4. ${page.title.split('. ')[0].split('4.')[1]} Halaman ${page.title.split('. ')[1]}*`);
            out.push("");
        }
        
        out.push("```{=openxml}");
        out.push("<w:p><w:r><w:br w:type=\"page\"/></w:r></w:p>");
        out.push("```");
        out.push("");
        continue;
    }
    
    if (inBab4 && text.includes("BAB V")) {
        inBab4 = false;
    }
    
    if (inBab4) {
        continue;
    }
    
    // Handle headings
    if (text === "KATA PENGANTAR") {
        out.push("# KATA PENGANTAR");
        out.push("");
        continue;
    }
    if (text === "BAB I" && lines[i+1] && lines[i+1].includes("PENDAHULUAN")) {
        out.push("# BAB I PENDAHULUAN");
        out.push("");
        i++; // skip next line
        continue;
    }
    if (text === "BAB II" && lines[i+1] && lines[i+1].includes("KAJIAN PUSTAKA")) {
        out.push("```{=openxml}");
        out.push("<w:p><w:r><w:br w:type=\"page\"/></w:r></w:p>");
        out.push("```");
        out.push("");
        out.push("# BAB II KAJIAN PUSTAKA");
        out.push("");
        i++; // skip next line
        continue;
    }
    if (text === "BAB III" && lines[i+1] && lines[i+1].includes("PERANCANGAN")) {
        out.push("```{=openxml}");
        out.push("<w:p><w:r><w:br w:type=\"page\"/></w:r></w:p>");
        out.push("```");
        out.push("");
        out.push("# BAB III PERANCANGAN");
        out.push("");
        i++; // skip next line
        continue;
    }
    if (text === "BAB V" && lines[i+1] && lines[i+1].includes("KESIMPULAN")) {
        out.push("# BAB V KESIMPULAN");
        out.push("");
        i++; // skip next line
        continue;
    }
    if (text === "DAFTAR PUSTAKA") {
        out.push("```{=openxml}");
        out.push("<w:p><w:r><w:br w:type=\"page\"/></w:r></w:p>");
        out.push("```");
        out.push("");
        out.push("# DAFTAR PUSTAKA");
        out.push("");
        continue;
    }
    
    // Match sub-headings (e.g. 1.1., 3.1.2., etc)
    let headingMatch = text.match(/^([1-5]\.\d+\.?\d*?\.)\s+(.*)$/);
    if (headingMatch) {
        let level = headingMatch[1].split('.').filter(Boolean).length;
        let prefix = '#'.repeat(level); // Level 1 is #, Level 2 is ##, etc.
        out.push(`${prefix} ${headingMatch[1]} ${cleanText(headingMatch[2])}`);
        out.push("");
        continue;
    }
    
    // Normal text
    let cleaned = cleanText(text);
    if (cleaned) {
        // Class diagram description override
        if (cleaned.includes("Class diagram diatas menggambarkan primary key dan foreign key")) {
            cleaned = "Class diagram diatas menggambarkan primary key dan foreign key antar tabel yang dibutuhkan oleh sistem POS. Terdapat 18 tabel utama (termasuk role, users, shift, menu, kategori, meja, pesanan, detail_pesanan, karyawan, absensi, jabatan, slip_gaji, bahan_alat, promo, keuangan_transaksi, dan log audit) yang saling terhubung.";
        }
        out.push(cleaned);
        out.push("");
    }
}

fs.writeFileSync(outputFile, out.join('\n'), 'utf8');
console.log("Successfully generated pipeline_buffer.md");
