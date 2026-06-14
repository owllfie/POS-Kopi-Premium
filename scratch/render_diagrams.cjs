const puppeteer = require('puppeteer-core');
const fs = require('fs');
const path = require('path');

const browserPath = 'C:\\Program Files (x86)\\Microsoft\\Edge\\Application\\msedge.exe';
const outputDir = path.join(__dirname, 'extracted_docx', 'word', 'media');

if (!fs.existsSync(outputDir)) {
    fs.mkdirSync(outputDir, { recursive: true });
}

const htmlContent = `
<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: 'Times New Roman', Times, serif;
            background-color: white;
            padding: 20px;
            margin: 0;
        }
        .diagram-container {
            width: 1000px;
            margin-bottom: 50px;
            padding: 20px;
            border: 1px solid #ccc;
            background: white;
            display: inline-block;
        }
        .diagram-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }
    </style>
    <script type="module">
        import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
        mermaid.initialize({
            startOnLoad: true,
            theme: 'default',
            flowchart: { useMaxWidth: false, htmlLabels: true },
            sequence: { useMaxWidth: false },
            er: { useMaxWidth: false }
        });
    </script>
</head>
<body>

    <!-- Gambar 3.1 Context Diagram (image2.png) -->
    <div id="container-context" class="diagram-container">
        <div class="diagram-title">Gambar 3.1 Konteks Diagram (DFD Level 0)</div>
        <pre class="mermaid">
        graph TD
            Customer[Pelanggan / Guest]
            Kasir[Kasir]
            Chef[Chef / Dapur]
            Manager[Manager]
            Admin[Admin / Superadmin]
            
            System((Sistem POS Restoran Premium))
            
            Customer -- "Pilih menu & Submit pesanan (QR Code)" --> System
            System -- "Konfirmasi & Detail pesanan" --> Customer
            
            Kasir -- "Kelola transaksi, Mulai/End shift, Catat nominal cash/QRIS" --> System
            System -- "Data pesanan meja, Hitung kembalian, Print struk pembayaran" --> Kasir
            
            Chef -- "Update status masak (Menunggu/Dimasak/Selesai)" --> System
            System -- "Daftar antrean pesanan dapur" --> Chef
            
            Manager -- "Filter & Unduh laporan keuangan harian/mingguan/bulanan" --> System
            System -- "Visualisasi grafik tren pendapatan & riwayat transaksi" --> Manager
            
            Admin -- "Kelola data master (Menu, Kategori, Meja, User, Karyawan, Jabatan, Promo, Bahan & Alat)" --> System
            System -- "Informasi database, Log aktivitas audit, Backup database SQL" --> Admin
        </pre>
    </div>

    <!-- Gambar 3.2 DFD Level 1 (image3.png) -->
    <div id="container-dfd1" class="diagram-container">
        <div class="diagram-title">Gambar 3.2 DFD Level 1</div>
        <pre class="mermaid">
        graph TD
            Customer[Pelanggan / Guest]
            Kasir[Kasir]
            Chef[Chef]
            Manager[Manager]
            Admin[Admin]
            
            P1((1.0 Kelola Data Master))
            P2((2.0 Transaksi & Pesanan))
            P3((3.0 Operasional & SDM))
            P4((4.0 Keuangan & Audit))
            
            D1[(Data Master DB)]
            D2[(Transaksi DB)]
            D3[(SDM & Inventaris DB)]
            D4[(Audit DB)]
            
            Admin -- "Input menu, meja, user, promo" --> P1
            P1 -- "Simpan/Update" --> D1
            
            Customer -- "Pesan via QR" --> P2
            Kasir -- "Proses Bayar & Shift" --> P2
            Chef -- "Update Status Masak" --> P2
            P2 -- "Catat order/bayar/shift" --> D2
            
            Admin -- "Kelola Karyawan, Jabatan, Absen, Slip Gaji, Stok Bahan" --> P3
            P3 -- "Simpan SDM & Bahan" --> D3
            
            Manager -- "Lihat Laporan & Transaksi" --> P4
            P4 -- "Ambil riwayat/log" --> D2
            P4 -- "Ambil log aktivitas" --> D4
            D2 -- "Data transaksi" --> P4
            D4 -- "Data log" --> P4
            P4 -- "Tampilkan Grafik & Log" --> Manager
        </pre>
    </div>

    <!-- Gambar 3.3 DFD Level 2 (image4.png) -->
    <div id="container-dfd2" class="diagram-container">
        <div class="diagram-title">Gambar 3.3 DFD Level 2</div>
        <pre class="mermaid">
        graph TD
            Customer[Pelanggan]
            Kasir[Kasir]
            Chef[Chef]
            
            P21((2.1 Buat Pesanan QR))
            P22((2.2 Kelola Antrean Dapur))
            P23((2.3 Pembayaran & Struk))
            P24((2.4 Pengelolaan Shift))
            
            D_Meja[(meja)]
            D_Pesanan[(pesanan & detail_pesanan)]
            D_Shift[(shift)]
            
            Customer -- "Scan QR & Input Order" --> P21
            P21 -- "Update status terisi" --> D_Meja
            P21 -- "Simpan detail pesanan draft" --> D_Pesanan
            
            Chef -- "Ambil daftar masak & update status" --> P22
            P22 -- "Update status masak" --> D_Pesanan
            
            Kasir -- "Pilih metode cash/QRIS, input bayar" --> P23
            P23 -- "Simpan pesanan lunas, update meja kosong" --> D_Pesanan
            P23 -- "Update status kosong" --> D_Meja
            P23 -- "Print struk" --> Kasir
            
            Kasir -- "Input saldo awal & akhir cash/QRIS" --> P24
            P24 -- "Simpan data shift kasir" --> D_Shift
        </pre>
    </div>

    <!-- Gambar 3.4 Use Case (image5.png) -->
    <div id="container-usecase" class="diagram-container">
        <div class="diagram-title">Gambar 3.4 Use Case Diagram</div>
        <pre class="mermaid">
        leftToRightDirection
        actor Customer as "Customer (Guest)"
        actor Kasir as "Kasir"
        actor Chef as "Chef"
        actor Manager as "Manager"
        actor Admin as "Admin / Superadmin"
        
        rectangle "Sistem POS Restoran" {
            usecase UC_Menu as "Lihat Menu & Pesan (QR)"
            usecase UC_Login as "Login Akun"
            usecase UC_Dashboard as "Lihat Dashboard"
            usecase UC_Kitchen as "Kelola Antrean Dapur"
            usecase UC_Pay as "Proses Pembayaran & Struk"
            usecase UC_Shift as "Kelola Shift Kasir"
            usecase UC_Report as "Lihat Laporan & Riwayat"
            usecase UC_Master as "Kelola Data Master (Menu/Meja/User)"
            usecase UC_HR as "Kelola SDM (Karyawan/Absen/Gaji)"
            usecase UC_Inventory as "Kelola Bahan, Alat & Properti"
            usecase UC_Akses as "Kelola Hak Akses (Superadmin)"
            usecase UC_Backup as "Backup Database (Superadmin)"
        }
        
        Customer --> UC_Menu
        Kasir --> UC_Login
        Kasir --> UC_Pay
        Kasir --> UC_Shift
        Chef --> UC_Login
        Chef --> UC_Kitchen
        Manager --> UC_Login
        Manager --> UC_Dashboard
        Manager --> UC_Report
        Admin --> UC_Login
        Admin --> UC_Dashboard
        Admin --> UC_Master
        Admin --> UC_HR
        Admin --> UC_Inventory
        Admin --> UC_Akses
        Admin --> UC_Backup
        
        UC_Pay .> UC_Login : include
        UC_Shift .> UC_Login : include
        UC_Kitchen .> UC_Login : include
        UC_Report .> UC_Login : include
        UC_Master .> UC_Login : include
        UC_HR .> UC_Login : include
        UC_Inventory .> UC_Login : include
        UC_Akses .> UC_Login : include
        UC_Backup .> UC_Login : include
        
        style UC_Menu fill:#f9f,stroke:#333,stroke-width:2px
        style UC_Pay fill:#bbf,stroke:#333,stroke-width:2px
        style UC_Master fill:#bfb,stroke:#333,stroke-width:2px
        style UC_Login fill:#ffb,stroke:#333,stroke-width:2px
        style UC_HR fill:#fbb,stroke:#333,stroke-width:2px
        style UC_Inventory fill:#fbf,stroke:#333,stroke-width:2px
        style UC_Report fill:#bff,stroke:#333,stroke-width:2px
        style UC_Akses fill:#bbb,stroke:#333,stroke-width:2px
        style UC_Backup fill:#bbb,stroke:#333,stroke-width:2px
        style UC_Kitchen fill:#f99,stroke:#333,stroke-width:2px
        style UC_Dashboard fill:#9f9,stroke:#333,stroke-width:2px
        style UC_Shift fill:#99f,stroke:#333,stroke-width:2px
        
        </pre>
    </div>

    <!-- Gambar 3.5 Activity Diagram (image6.png) -->
    <div id="container-activity" class="diagram-container">
        <div class="diagram-title">Gambar 3.5 Activity Diagram</div>
        <pre class="mermaid">
        stateDiagram-v2
            [*] --> Scan_QR
            Scan_QR --> Lihat_Menu
            Lihat_Menu --> Tambah_Keranjang
            Tambah_Keranjang --> Submit_Pesanan
            Submit_Pesanan --> Antrean_Dapur_Menunggu
            
            state Antrean_Dapur_Menunggu {
                [*] --> Chef_Mulai_Masak
                Chef_Mulai_Masak --> Dimasak
                Dimasak --> Chef_Selesai_Masak
                Chef_Selesai_Masak --> Selesai_Saji
            }
            
            Selesai_Saji --> Kasir_Pilih_Pembayaran
            Kasir_Pilih_Pembayaran --> Proses_Bayar_Cash : Cash
            Kasir_Pilih_Pembayaran --> Proses_Bayar_QRIS : QRIS
            
            Proses_Bayar_Cash --> Konfirmasi_Bayar
            Proses_Bayar_QRIS --> Konfirmasi_Bayar
            
            Konfirmasi_Bayar --> Cetak_Struk
            Cetak_Struk --> Kosongkan_Meja
            Kosongkan_Meja --> [*]
        </pre>
    </div>

    <!-- Gambar 3.6 Sequence Diagram (image7.png) -->
    <div id="container-sequence" class="diagram-container">
        <div class="diagram-title">Gambar 3.6 Sequence Diagram</div>
        <pre class="mermaid">
        sequenceDiagram
            actor Pelanggan as Pelanggan (Guest)
            actor Kasir as Kasir
            actor Chef as Chef
            participant Sistem as Sistem POS
            database DB as Database (MySQL)
            
            Pelanggan->>Sistem: Scan QR Code Table
            Sistem->>DB: Cek Token Meja
            DB-->>Sistem: Token Valid
            Sistem-->>Pelanggan: Tampilkan Menu Table 1
            
            Pelanggan->>Sistem: Pilih Menu & Kirim Pesanan
            Sistem->>DB: Simpan detail_pesanan (status: menunggu)
            Sistem->>Chef: Notifikasi Pesanan Baru
            
            Chef->>Sistem: Klik 'Mulai Masak'
            Sistem->>DB: Update detail_pesanan (status: dimasak)
            Chef->>Sistem: Klik 'Selesai'
            Sistem->>DB: Update detail_pesanan (status: selesai)
            Sistem->>Kasir: Notifikasi Pesanan Selesai Masak
            
            Kasir->>Sistem: Buka Halaman Bayar (Meja 1)
            Sistem->>DB: Tarik total_harga & pajak
            DB-->>Sistem: Data total nominal
            Sistem-->>Kasir: Tampilkan Total & Form Pembayaran
            
            Kasir->>Sistem: Konfirmasi Pembayaran (Cash/QRIS)
            Sistem->>DB: Simpan pesanan, update meja status=kosong
            Sistem-->>Kasir: Transaksi Sukses & Print Struk
        </pre>
    </div>

    <!-- Gambar 3.7 Class Diagram (image8.png) -->
    <div id="container-class" class="diagram-container" style="width: 1200px;">
        <div class="diagram-title">Gambar 3.7 Class Diagram</div>
        <pre class="mermaid">
        classDiagram
            class role {
                +int id_role PK
                +varchar role
            }
            class users {
                +int id_user PK
                +varchar username
                +varchar email
                +varchar password
                +int id_role FK
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            class aksess {
                +int id_akses PK
                +int id_role FK
                +varchar modul
                +enum allowed
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            class shift {
                +int id_shift PK
                +int id_user FK
                +timestamp jam_mulai
                +timestamp jam_selesai
                +int cash_masuk
                +int qris_masuk
                +int total_masuk
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            class kategori {
                +int id_kategori PK
                +varchar kategori
            }
            class menu {
                +int id_menu PK
                +varchar nama_menu
                +int id_kategori FK
                +varchar foto
                +int harga
                +enum status
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            class meja {
                +int id_meja PK
                +int nomor_meja
                +varchar qrcode_token
                +enum status
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            class pesanan {
                +int id_pesanan PK
                +varchar kode_struk
                +int id_promo FK
                +int id_meja FK
                +enum metode_pembayaran
                +int total_harga
                +int diskon
                +int pajak
                +int total_bayar
                +int id_user FK
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            class detail_pesanan {
                +int id_detail PK
                +int id_pesanan FK
                +int id_menu FK
                +int jumlah
                +int harga_satuan
                +int subtotal
                +varchar catatan
                +enum status
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            class activity_log {
                +int id_log PK
                +int id_user FK
                +varchar aktivitas
                +varchar detail_aktivitas
                +varchar ip_address
                +timestamp created_at
                +timestamp deleted_at
            }
            class history_update {
                +int id_update PK
                +varchar table
                +int record_id
                +text data_lama
                +text data_baru
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            class karyawan {
                +int id_karyawan PK
                +varchar nama
                +varchar alamat
                +varchar no_hp
                +int id_jabatan FK
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            class absensi {
                +int id_absensi PK
                +int id_karyawan FK
                +date tanggal
                +time jam_masuk
                +time jam_keluar
                +varchar status
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            class jabatan {
                +int id_jabatan PK
                +varchar nama_jabatan
                +int gaji_pokok
                +int tunjangan
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            class slip_gaji {
                +int id_slip PK
                +int id_karyawan FK
                +varchar bulan
                +varchar tahun
                +int gaji_bersih
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            class bahan_alat {
                +int id_item PK
                +varchar nama_item
                +varchar kategori
                +int stok
                +varchar satuan
                +enum status
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            class promo {
                +int id_promo PK
                +varchar kode_promo
                +int diskon
                +enum status
                +timestamp created_at
                +timestamp updated_at
                +timestamp deleted_at
            }
            
            role "1" --> "0..*" users : has
            role "1" --> "0..*" aksess : sets
            users "1" --> "0..*" shift : opens
            users "1" --> "0..*" pesanan : handles
            users "1" --> "0..*" activity_log : triggers
            kategori "1" --> "0..*" menu : categorizes
            menu "1" --> "0..*" detail_pesanan : ordered_in
            meja "1" --> "0..*" pesanan : registers
            pesanan "1" --> "1..*" detail_pesanan : contains
            promo "1" --> "0..*" pesanan : applies
            karyawan "1" --> "0..*" absensi : logs
            karyawan "1" --> "0..*" slip_gaji : receives
            jabatan "1" --> "0..*" karyawan : assigns
        </pre>
    </div>

    <!-- Gambar 3.8 ERD (image9.png) -->
    <div id="container-erd" class="diagram-container" style="width: 1200px;">
        <div class="diagram-title">Gambar 3.8 Entity Relationship Diagram (ERD)</div>
        <pre class="mermaid">
        erDiagram
            role ||--o{ users : "has"
            role ||--o{ aksess : "sets"
            users ||--o{ shift : "opens"
            users ||--o{ pesanan : "handles"
            users ||--o{ activity_log : "triggers"
            kategori ||--o{ menu : "categorizes"
            menu ||--o{ detail_pesanan : "ordered_in"
            meja ||--o{ pesanan : "registers"
            pesanan ||--{ detail_pesanan : "contains"
            promo ||--o{ pesanan : "applies"
            karyawan ||--o{ absensi : "logs"
            karyawan ||--o{ slip_gaji : "receives"
            jabatan ||--o{ karyawan : "assigns"
            
            role {
                int id_role PK
                varchar role
            }
            users {
                int id_user PK
                varchar username
                varchar email
                varchar password
                int id_role FK
            }
            aksess {
                int id_akses PK
                int id_role FK
                varchar modul
                enum allowed
            }
            shift {
                int id_shift PK
                int id_user FK
                timestamp jam_mulai
                timestamp jam_selesai
                int cash_masuk
                int qris_masuk
                int total_masuk
            }
            kategori {
                int id_kategori PK
                varchar kategori
            }
            menu {
                int id_menu PK
                varchar nama_menu
                int id_kategori FK
                int harga
                enum status
            }
            meja {
                int id_meja PK
                int nomor_meja
                varchar qrcode_token
                enum status
            }
            pesanan {
                int id_pesanan PK
                varchar kode_struk
                int id_promo FK
                int id_meja FK
                enum metode_pembayaran
                int total_harga
                int diskon
                int pajak
                int total_bayar
                int id_user FK
            }
            detail_pesanan {
                int id_detail PK
                int id_pesanan FK
                int id_menu FK
                int jumlah
                int harga_satuan
                int subtotal
                varchar catatan
                enum status
            }
            karyawan {
                int id_karyawan PK
                varchar nama
                varchar alamat
                varchar no_hp
                int id_jabatan FK
            }
            absensi {
                int id_absensi PK
                int id_karyawan FK
                date tanggal
                time jam_masuk
                time jam_keluar
                varchar status
            }
            jabatan {
                int id_jabatan PK
                varchar nama_jabatan
                int gaji_pokok
                int tunjangan
            }
            slip_gaji {
                int id_slip PK
                int id_karyawan FK
                varchar bulan
                varchar tahun
                int gaji_bersih
            }
            bahan_alat {
                int id_item PK
                varchar nama_item
                varchar kategori
                int stok
                varchar satuan
                enum status
            }
            promo {
                int id_promo PK
                varchar kode_promo
                int diskon
                enum status
            }
        </pre>
    </div>

</body>
</html>
`;

fs.writeFileSync(path.join(__dirname, 'diagrams.html'), htmlContent, 'utf8');
console.log("Wrote diagrams.html");

const diagramIds = [
    { id: 'container-context', file: 'image2.png' },
    { id: 'container-dfd1', file: 'image3.png' },
    { id: 'container-dfd2', file: 'image4.png' },
    { id: 'container-usecase', file: 'image5.png' },
    { id: 'container-activity', file: 'image6.png' },
    { id: 'container-sequence', file: 'image7.png' },
    { id: 'container-class', file: 'image8.png' },
    { id: 'container-erd', file: 'image9.png' }
];

async function run() {
    console.log("Launching Edge to render diagrams...");
    const browser = await puppeteer.launch({
        executablePath: browserPath,
        headless: true,
        args: ['--no-sandbox', '--disable-setuid-sandbox']
    });

    const page = await browser.newPage();
    // Open local file
    const filePath = 'file://' + path.join(__dirname, 'diagrams.html').replace(/\\/g, '/');
    console.log(`Opening ${filePath}...`);
    await page.goto(filePath, { waitUntil: 'networkidle0' });

    // Wait a bit for mermaid to render all diagrams
    console.log("Waiting for Mermaid to render...");
    await new Promise(r => setTimeout(r, 5000));

    for (const d of diagramIds) {
        console.log(`Capturing ${d.id} -> ${d.file}`);
        try {
            const element = await page.$(`#${d.id}`);
            if (element) {
                const outputPath = path.join(outputDir, d.file);
                await element.screenshot({ path: outputPath });
                console.log(`  Saved to ${outputPath}`);
            } else {
                console.error(`  Element #${d.id} not found!`);
            }
        } catch (err) {
            console.error(`  Error capturing ${d.id}:`, err.message);
        }
    }

    await browser.close();
    console.log("Finished rendering diagrams!");
}

run();
