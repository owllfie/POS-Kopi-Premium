# Standar Akuntansi Restoran (Ground Truth untuk Agen AI)

## 1. Klasifikasi Akun (Chart of Accounts)
### 4000 - Pendapatan (Revenue)
* 4100: Penjualan Makanan (Dine-in, Takeaway)
* 4200: Penjualan Minuman
* 4300: Penjualan Produk Kemasan / Merchandise
* 4400: Pendapatan Kemitraan (Subsidi promo/cashback aggregator)
* 4500: Pendapatan Lain-lain (Penjualan minyak jelantah, sewa space)

### 5000 - Harga Pokok Penjualan (HPP / COGS)
* 5100: HPP Bahan Baku Makanan (Daging, sayur, bumbu, dll)
* 5200: HPP Bahan Baku Minuman (Kopi, sirup, susu, es batu, dll)
* 5300: HPP Kemasan & Konsumabel (Box, cup, sedotan, kantong, tisu)

### 6000 - Pengeluaran Operasional (OPEX)
* 6100: Biaya Karyawan (6101: Gaji Pokok, 6102: Insentif/Lembur, 6103: THR & BPJS)
* 6200: Utilitas & Tempat (6201: Sewa Bulanan, 6202: Listrik, 6203: Air, 6204: Gas LPG, 6205: Internet/Wi-Fi)
* 6300: Pemasaran & Promosi (6301: Digital Ads, 6302: Influencer, 6303: Cetak Menu/Banner, 6304: Diskon, 6305: Komisi Aplikasi Ojol 15-20%)
* 6400: Pemeliharaan & Kebersihan (6401: Servis Alat Dapur/Chiller/Mesin Kopi, 6402: Servis Gedung/AC, 6403: Bahan Kimia/Sabun Cuci, 6404: Alat Makan Pecah/Breakage)
* 6500: Administrasi & Legalitas (6501: Sampah/Keamanan, 6502: Pajak PB1/PBJT 10%, 6503: Sertifikasi Halal/NIB, 6504: Admin Bank/QRIS)

## 2. Logika Kalkulasi Laporan Keuangan
### 2.1 Laba Rugi (Profit & Loss)
* Pendapatan Bersih = Total Pendapatan (4000) - Diskon Langsung
* Laba Kotor = Pendapatan Bersih - Total HPP (5000)
* EBITDA = Laba Kotor - Total OPEX (6000)
* Laba Bersih = EBITDA - Penyusutan Aset - Pajak Penghasilan

### 2.2 Neraca (Balance Sheet)
* Aset Lancar: Petty Cash, Saldo Bank, Saldo Ojol Terendap, Persediaan Bahan Baku (Stock Opname)
* Aset Tetap: Renovasi, Mesin Kopi, Kulkas, Meja, Kursi (Dikurangi Akumulasi Penyusutan)
* Kewajiban: Utang Dagang Supplier, Utang Gaji, Titipan Pajak PB1 Belum Setor
* Modal: Modal Awal + Laba Ditahan

### 2.3 Arus Kas (Cash Flow)
* Ops: Kas masuk konsumen - kas keluar belanja harian & gaji
* Investasi: Kas keluar beli mesin/alat dapur baru
* Pendanaan: Prive / suntikan modal baru

### 2.4 Perubahan Modal & CALK
* Modal Akhir = Modal Awal + Laba Bersih - Prive

## 3. Format Buku Kas Harian (Ledger)
| Tanggal | Akun | Deskripsi | Metode | Debit | Kredit | Saldo |
| :--- | :--- | :--- | :--- | :--- | :--- | :--- |
| 2026-05-01 | 4100 | Bill #001-#045 (Dine-in) | Tunai | 3500000 | 0 | 3500000 |
| 2026-05-01 | 6305 | Komisi Aplikasi Ojol 20% | Potong | 0 | 700000 | 2800000 |
| 2026-05-02 | 5100 | Belanja Daging & Ayam | Transfer | 0 | 1500000 | 1300000 |
| 2026-05-02 | 6204 | Isi Gas LPG 12kg (2 tabung)| Tunai | 0 | 420000 | 880000 |

## 4. Aturan Validasi Finansial untuk Agen AI
1. Komisi Ojol (6305) wajib dicatat sebagai beban marketing, bukan pemotong langsung nilai penjualan kotor (Gross Sales).
2. Rumus HPP Riil: Persediaan Awal + Pembelian - Persediaan Akhir. Selisih bahan busuk/rusak masuk ke akun Spoilage/Waste.
3. Amortisasi Biaya Sewa: Biaya sewa tahunan wajib dibagi 12 bulan secara proporsional ke laporan laba rugi bulanan.
4. Titipan Pajak Restoran PB1/PBJT 10% dari konsumen berstatus Utang Lancar di Neraca, bukan pendapatan restoran.