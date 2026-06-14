# LAPORAN PERANCANGAN SISTEM POS PREMIUM

![](scratch/extracted_docx/word/media/image1.png)

**Disusun Oleh:**
Nickent Fausta  
24161029  
RPL XI  

**REKAYASA PERANGKAT LUNAK**  
**SMK PERMATA HARAPAN**  
**2026**

```{=openxml}
<w:p><w:r><w:br w:type="page"/></w:r></w:p>
```

![](scratch/extracted_docx/word/media/image1.png)

# KATA PENGANTAR

Puji syukur saya panjatkan kepada Tuhan Yang Maha Esa atas segala rahmat dan hidayah-Nya sehingga laporan perancangan yang berjudul “Laporan Perancangan Sistem POS Premium” ini dapat diselesaikan dengan baik dan tepat waktu.

Dalam proses penyusunan perancangan ini, saya memperoleh banyak masukan, dukungan, dan bimbingan dari berbagai pihak. Oleh karena itu, saya ingin menyampaikan terima kasih kepada:

Bapak Miftahul Ilmi, S.Pd., S.Kom., M.Pd.T, selaku pembimbing yang telah memberikan arahan dan bimbingan selama proses perancangan aplikasi.

Teman-teman yang telah memberikan saran dan bantuan dalam berbagai bentuk.

Saya menyadari bahwa laporan ini masih memiliki kekurangan, baik dalam isi maupun penyajian. Oleh karena itu, saya sangat mengharapkan kritik dan saran yang membangun demi penyempurnaan di masa mendatang.

Semoga laporan ini dapat memberikan manfaat serta menjadi referensi bagi pihak-pihak yang membutuhkan, khususnya dalam bidang perancangan aplikasi berbasis teknologi informasi.

Batam, 12 Juni 2026

Nickent Fausta

# BAB I PENDAHULUAN

## 1.1. Latar Belakang

Sistem POS merupakan laporan hasil dari suatu kegiatan yang disusun secara benar. Sistem POS merupakan dokumen yang menjadi penghubung komunikasi antara sekolah dengan orang tua peserta didik. Materi yang dilaporkan dalam hal ini adalah hasil ulangan harian, tugas harian, ujian tengah semester, ujian akhir semester, beserta data-data lain yang diperlukan yang berkaitan dengan sistem pos.

Sistem POS setiap semester adalah sesuatu yang dinantikan oleh setiap siswa di sekolah. Karena itu, sistem pos harus komunikatif, informatif, dan komprehensif (menyeluruh), dalam memberikan gambaran tentang hasil belajar peserta didik. Bagi sekolah, proses menghasilkan sistem pos adalah agenda besar dan rutin di setiap semester. Proses penginputan nilai, perhitungan nilai, hingga penggabungan nilai dari berbagai guru mata pelajaran menjadi proses yang harus presisi dan terkadang memakan waktu.

Sistem POS Premium adalah sebuah sistem aplikasi berbasis web yang di harapkan dapat mengubah pola kerja guru dari pola manual ke pola digital, yang dapat mempermudah guru dalam melakukan penilaian siswa, bahkan sampai ke pencetakan sistem pos dan evaluasi nilai hasil belajar siswa. Sistem POS Premium juga diharapkan dapat memberikan manfaat untuk dunia pendidikan dan dapat memberikan efek positif terhadap dunia pendidikan untuk lebih berkembang dan maju di era digital ini.

## 1.2. Rumusan Masalah

Proses evaluasi hasil belajar merupakan tahap penting dalam siklus pendidikan untuk mengukur pencapaian kompetensi siswa. Namun, dalam praktiknya, pengelolaan data nilai seringkali masih menghadapi kendala teknis, mulai dari risiko kesalahan menginput nilai, lambatnya proses rekap, hingga sulitnya melacak riwayat akademik siswa dari tahun ke tahun.

Sejalan dengan tuntutan digitalisasi pendidikan dan kebutuhan akan data yang terintegrasi, maka diperlukan sebuah sistem informasi Sistem POS Premium yang mampu mengelola hubungan kompleks antara data siswa, guru, rombongan belajar, dan nilai secara akurat. Berdasarkan latar belakang tersebut, maka dapat dirumuskan beberapa permasalahan utama yang menjadi fokus dalam perancangan sistem ini, yaitu:

Bagaimana meminimalkan waktu yang dibutuhkan guru dalam merekap nilai harian, nilai tengah semester, dan nilai akhir semester secara manual yang memakan waktu lama?

Bagaimana menyediakan platform yang memudahkan wali murid dan siswa dalam memantau hasil belajar secara transparan dan real-time tanpa harus menunggu buku sistem pos fisik?

## 1.3. Tujuan

Tujuan utama dari sistem pos premium adalah untuk memudahkan guru dalam menginput nilai serta memudahkan wali murid yang tidak sempat untuk datang ke sekolah untuk mengambil sistem pos anaknya.

## 1.4. Manfaat Sistem

Sistem Sistem POS Premium dikembangkan menggunakan platform web–based dengan pertimbangan agar konten yang disajikan dapat dengan mudah diakses oleh para guru. Pemakai sistem ini meliputi administrator sistem, guru, wali kelas, dan siswa.

Melalui Sistem POS Premium siswa diharapkan dapat melihat hasil belajarnya hanya dengan mengakses halaman tertentu yang diinformasikan pihak sekolah. Dengan demikian laporan hasil belajar tidak lagi dalam bentuk hard copy atau sistem pos konvensional.

```{=openxml}
<w:p><w:r><w:br w:type="page"/></w:r></w:p>
```

# BAB II KAJIAN PUSTAKA

Sistem atau tata adalah suatu kesatuan yang terdiri atas komponen atau elemen yang dihubungkan bersama untuk memudahkan aliran informasi, materi, atau energi untuk mencapai suatu tujuan. Istilah ini sering digunakan untuk menggambarkan suatu set entitas yang berinteraksi, di mana suatu model matematika sering kali bisa dibuat.

Informasi atau embaran adalah pesan (ucapan atau ekspresi) atau kumpulan pesan yang terdiri dari order sekuens dari simbol, atau makna yang dapat ditafsirkan dari pesan atau kumpulan pesan. Informasi bisa dikatakan sebagai pengetahuan yang didapatkan dari pembelajaran, pengalaman, atau instruksi. Informasi telah digunakan untuk seluruh segi kehidupan manusia secara individual, kelompok maupun organisasi.

Sistem Informasi adalah serangkaian komponen yang bekerja bersama untuk mengumpulkan, mengelola, menyimpan, memproses, dan menyebarkan informasi yang diperlukan untuk mendukung pengambilan keputusan dalam suatu organisasi atau entitas. Komponen utamanya terdiri dari, data, teknologi, proses, dan orang- orang.

DFD (Data Flow Diagram) adalah representasi grafik dari sebuah sistem, yang menggambarkan komponen-komponen sebuah sistem, aliran-aliran data di mana komponen-komponen tersebut, dan asal, tujuan, dan penyimpanan dari data tersebut.

DFD dapat dilakukan untuk dua hal utama, yaitu untuk membuat dokumentasi dari sistem informasi yang ada, atau untuk menyusun dokumentasi untuk sistem informasi yang baru.

ERD (Entity Relationship Diagram) adalah suatu model untuk menjelaskan hubungan antardata dalam basis data berdasarkan objek-objek dasar data yang mempunyai hubungan antar relasi. ERD berfungsi untuk memodelkan struktur data

dan hubungan antar data, untuk menggambarkannya digunakan beberapa notasi dan simbol.

Konteks Diagram adalah diagram yang terdiri dari suatu proses dan menggambarkan ruang lingkup suatu sistem. Diagram konteks merupakan level tertinggi dari DFD yang menggambarkan seluruh input ke dalam sistem atau output dari sistem yang memberi gambaran tentang keseluruhan sistem.

Use Case Diagram menggambarkan fungsionalitas yang diharapkan dari sebuah sistem, yang merepresentasikan sebuah interaksi antara aktor dengan sistem.

Seorang aktor adalah sebuah entitas manusia atau mesin yang berinteraksi dengan sistem untuk melakukan pekerjaan tertentu.

Activity Diagram menggambarkan berbagai alir aktivitas dalam sistem yang sedang dirancang, bagaimana masing-masing alir berawal, decision yang mungkin terjadi, dan bagaimana mereka berakhir. Activity diagram juga dapat menggambarkan proses pararel yang mungkin terjadi pada beberapa eksekusi.

Sequence Diagram menggambarkan interaksi antar objek di dalam dan di sekitar sistem (termasuk pengguna, display, dan sebagainya) berupa message yan digambarkan terhadap waktu. Biasa digunakan untuk menggambarkan scenario atau rangkaian langkah-langkah yang dilakukan sebagai respon dari sebuah event untuk menghasilkan output tertentu.

Normalisasi Database adalah, satu teknik yang berdasarkan logika desain di dalam sebuah basis data itu sendiri. Yang mana, basis data tersebut akan mengelompokkan berbagai atribut dari berbagai entitas di dalam suatu relasi yang ada.

```{=openxml}
<w:p><w:r><w:br w:type="page"/></w:r></w:p>
```

# BAB III PERANCANGAN

## 3.1. DFD Level 0(Context Diagram)

Diagram konteks terdiri dari suatu proses dan menggambarkan ruang lingkup suatu sistem. Dalam sistem pos premium, diagram ini terdiri dari simbol kotak entitas eksternal berisi nama entitas seperti super admin, admin, guru, wali kelas, kepala sekolah dan siswa, menggambarkan asal atau tujuan data di luar sistem. Simbol aliran data yang mewakili perpindahan data antar entitas kepada sistem.

![](scratch/extracted_docx/word/media/image2.png)

Gambar 3.  1 Konteks Diagram

Pada DFD Level 0 diatas, dijelaskan alur yang dilakukan oleh berbagai macam user. Misalnya pada Super Admin, bisa memiliki full access pada web dan database, admin bisa memiliki akses penuh pada web, kepala sekolah bertugas menginput siswa baru ke sistem, guru dan wali menginput nilai.

## 3.2. DFD Level 1

DFD level 1 menggambarkan proses-proses yang ada pada sistem pos premium. Proses tersebut meliputi proses input data siswa, input nilai siswa dan laporan data nilai siswa. Di level ini proses yang dibuat masih belum detail, oleh karena itu setiap proses dalam level ini masih harus di dekomposisi ke dalam level 2.

![](scratch/extracted_docx/word/media/image3.png)

Gambar 3.  2 DFD Level 1

DFD Level 1 diatas menggambarkan proses yang terjadi di sistem dengan lebih spesifik. Diagram diatas menggambarkan 2 alur, yaitu kelola data nilai dan juga data master. Data master meliputi data user, data mapel, data rombel, dan data kelas.

## 3.3. DFD Level 2

![](scratch/extracted_docx/word/media/image4.png)

Gambar 3.  3 DFD Level 2

DFD level 2 merupakan turunan dari DFD level 1. Pada level 2 ini, proses-proses tersebut dibuat secara rinci. Meliputi berbagai peran dalam sistem sistem pos seperti, guru, wali kelas, super admin, admin dan siswa.

DFD Level 2 diatas, digambarkan peran-peran user dengan lebih spesifik. Siswa dapat melihat sistem pos, guru dapat menginput nilai, wali kelas dapat menginput nilai dan catatan. Kepala sekolah menginput user baru, admin dan super admin memiliki full access.

## 3.4. Use Case

Diagram ini menggambarkan fungsionalitas yang diharapkan dari sebuah sistem. Use Case merepresentasikan sebuah interaksi antara aktor dengan sistem. Use Case menjelaskan peran-peran dan hak apa saja yang bisa diakses oleh peran tertentu.

![](scratch/extracted_docx/word/media/image5.png)

Gambar 3.  4 Use Case

Use Case diatas menggambarkan apa saja yang bisa dilakukan oleh user pada sistem sistem pos dari login hingga logout.

## 3.5. Activity Diagram

Activity diagram menggambarkan berbagai alir aktivitas dalam sistem yang sedang dirancang, bagaimana masing-masing alir berawal, decision yang mungkin terjadi dan bagaimana mereka berakhir.

![](scratch/extracted_docx/word/media/image6.png)

Gambar 3.  5 Activity Diagram

Activity diagram diatas menggambarkan alur sistem dari berbagai perspeksi. Diperlihatkan keenam role dengan alur yang berbeda-beda.

## 3.6. Sequence Diagram

Sequence diagram merupakan diagram yang menjelaskan interaksi objek berdasarkan urutan waktu. Sequence dapat menggambarkan urutan atau tahapan yang harus dilakukan untuk dapat menghasilkan sesuatu secara bertahap.

![](scratch/extracted_docx/word/media/image7.png)

Gambar 3.  6 Sequence Diagram

Sequence Diagram diatas menggambarkan alur tiap user dari login hingga logout, secara kronologis.

## 3.7. Class Diagram

![](scratch/extracted_docx/word/media/image8.png)

Gambar 3.  7 Class Diagram

Class diagram merupakan suatu diagram yang digunakan untuk menampilkan kelas-kelas berupa pake-paket untuk memenuhi salah satu kebutuhan paket yang akan digunakan nantinya.

Class diagram diatas menggambarkan primary key dan foreign key antar tabel yang dibutuhkan oleh sistem POS. Terdapat 18 tabel utama (termasuk role, users, shift, menu, kategori, meja, pesanan, detail_pesanan, karyawan, absensi, jabatan, slip_gaji, bahan_alat, promo, keuangan_transaksi, dan log audit) yang saling terhubung.

## 3.8. ERD(Entity Relationship Diagram)

Entity Relationship Diagram adalah diagram yang digunakan untuk perancangan suatu database dan menunjukan relasi antar objek atau entitas beserta atribut-atributnya secara detail.

![](scratch/extracted_docx/word/media/image9.png)

Gambar 3.  8 ERD

ERD diatas menggambarkan relasi antar tabel yang dibutuhkan untuk sistem pos premium.

## 3.9. Normalisasi Data

Normalisasi adalah proses dalam desain basis data yang bertujuan untuk mengorganisasi data dalam tabel secara efisien. Tujuan utama dari normalisasi adalah untuk mengurangi redundansi data dan meningkatkan integritas data dengan memastikan bahwa data disimpan dengan cara yang konsisten dan terstruktur.

0NF

0NF adalah bentuk tabel yang belum dinormalisasi. Ini biasanya merupakan data mentah yang baru dikumpulkan dari dokumen fisik (seperti struk belanja atau formulir pendaftaran).

![](scratch/extracted_docx/word/media/image10.png)

Gambar 3.  9 0NF(1)

![](scratch/extracted_docx/word/media/image11.png)

Gambar 3.  10 0NF(2)

1NF

Sebuah tabel dikatakan memenuhi 1NF jika setiap kolom hanya berisi satu nilai tunggal (atomik) dan tidak ada grup yang berulang.

![](scratch/extracted_docx/word/media/image12.png)

Gambar 3.  11 1NF(1)

![](scratch/extracted_docx/word/media/image13.png)

Gambar 3.  12 1NF(2)

2NF

Sebuah tabel memenuhi 2NF jika sudah memenuhi syarat 1NF dan semua atribut yang bukan kunci (non-key) harus bergantung sepenuhnya pada Primary Key.

![](scratch/extracted_docx/word/media/image14.png)

Gambar 3.  13 2NF(1)

![](scratch/extracted_docx/word/media/image15.png)

Gambar 3.  14 2NF(2)

![](scratch/extracted_docx/word/media/image16.png)

Gambar 3.  15 2NF(3)

4.           3NF

3NF (Third Normal Form) adalah tahap normalisasi di mana sebuah tabel harus sudah memenuhi kriteria 2NF dan semua atribut yang bukan kunci (non-key) tidak boleh memiliki ketergantungan transitif terhadap kunci utama (Primary Key).

![](scratch/extracted_docx/word/media/image17.png)

Gambar 3.  16 3NF(1)

![](scratch/extracted_docx/word/media/image18.png)

Gambar 3.  17 3NF(2)

![](scratch/extracted_docx/word/media/image19.png)

Gambar 3.  18 3NF(3)

![](scratch/extracted_docx/word/media/image20.png)

Gambar 3.  19 3NF(4)

![](scratch/extracted_docx/word/media/image21.png)

Gambar 3.  20 3NF(5)

![](scratch/extracted_docx/word/media/image22.png)

Gambar 3.  21 3NF(6)

![](scratch/extracted_docx/word/media/image23.png)

Gambar 3.  22 3NF(7)

![](scratch/extracted_docx/word/media/image24.png)

Gambar 3.  23 3NF(8)

![](scratch/extracted_docx/word/media/image25.png)

Gambar 3.  24 3NF(9)

![](scratch/extracted_docx/word/media/image26.png)

Gambar 3.  25 3NF(10)

![](scratch/extracted_docx/word/media/image27.png)

Gambar 3.  26 3NF(11)

# BAB IV TAMPILAN APLIKASI

## 4.1. Halaman Login

Halaman ini digunakan oleh seluruh pengguna sistem (Kasir, Chef, Manager, Admin, Superadmin) untuk melakukan otentikasi menggunakan email dan kata sandi.

![](scratch/screenshots/login.png)

*Gambar 4. 1 Halaman Halaman Login*

## 4.2. Halaman Dashboard Superadmin

Dashboard utama untuk Superadmin yang menyajikan grafik tren pendapatan, data metrik operasional (total transaksi, pendapatan hari ini, meja aktif, menu tersedia), serta daftar log aktivitas audit sistem terbaru secara real-time.

![](scratch/screenshots/dashboard_superadmin.png)

*Gambar 4. 2 Halaman Halaman Dashboard Superadmin*

## 4.3. Halaman Dashboard Manager

Dashboard operasional untuk Manager. Berfokus pada analisis kinerja keuangan restoran, menampilkan grafik tren penjualan harian/mingguan/bulanan, serta rekapitulasi pendapatan berjalan.

![](scratch/screenshots/dashboard_manager.png)

*Gambar 4. 3 Halaman Halaman Dashboard Manager*

## 4.4. Halaman Dashboard Kasir

Dashboard operasional untuk Kasir. Halaman ini berfungsi sebagai laporan kas live untuk memantau waktu shift aktif kasir, total pendapatan tunai (cash) versus QRIS, serta riwayat transaksi kasir yang sedang bertugas.

![](scratch/screenshots/dashboard_kasir.png)

*Gambar 4. 4 Halaman Halaman Dashboard Kasir*

## 4.5. Halaman Menu Pelanggan (QR Code)

Menu publik yang diakses pelanggan setelah melakukan pemindaian QR code meja. Pelanggan dapat memilih kategori menu, menambahkan item ke keranjang belanja, memberikan catatan khusus, dan mengirim pesanan.

![](scratch/screenshots/halaman_menu_customer.png)

*Gambar 4. 5 Halaman Halaman Menu Pelanggan (QR Code)*

## 4.6. Daftar Pesanan Dapur (Chef View)

Halaman antrean dapur untuk Chef untuk melihat pesanan yang perlu dimasak, mengubah status pesanan dari menunggu menjadi sedang dimasak, dan menyelesaikannya.

![](scratch/screenshots/daftar_pesanan_chef.png)

*Gambar 4. 6 Halaman Daftar Pesanan Dapur (Chef View)*

## 4.7. Daftar Pesanan Kasir (Kasir View)

Antrean pesanan aktif restoran untuk kasir, yang menampilkan status meja dan tombol cepat untuk menuju halaman pembayaran.

![](scratch/screenshots/daftar_pesanan_kasir.png)

*Gambar 4. 7 Halaman Daftar Pesanan Kasir (Kasir View)*

## 4.8. Halaman Pembayaran

Form kasir untuk memproses pembayaran meja, memilih metode cash atau QRIS, menghitung nominal kembalian, dan mencetak struk transaksi.

![](scratch/screenshots/halaman_pembayaran.png)

*Gambar 4. 8 Halaman Halaman Pembayaran*

## 4.9. Laporan Keuangan

Ringkasan keuangan restoran yang dapat difilter harian/mingguan/bulanan, mencakup pencatatan buku besar pemasukan dan pengeluaran kas operasional restoran.

![](scratch/screenshots/laporan.png)

*Gambar 4. 9 Halaman Laporan Keuangan*

## 4.10. Riwayat Transaksi

Catatan riwayat seluruh transaksi pembayaran yang berhasil diselesaikan, lengkap dengan pencarian nomor struk, filter kasir, serta fitur soft-delete/trash.

![](scratch/screenshots/riwayat_transaksi.png)

*Gambar 4. 10 Halaman Riwayat Transaksi*

## 4.11. Kelola Users

Halaman administrasi pengguna sistem untuk menambahkan, mengubah, atau menonaktifkan akun kasir, chef, manager, dan admin.

![](scratch/screenshots/kelola_users.png)

*Gambar 4. 11 Halaman Kelola Users*

## 4.12. Kelola Menu

Modul CRUD data menu makanan/minuman, harga, foto, kategori, dan tombol cepat status ketersediaan menu.

![](scratch/screenshots/kelola_menu.png)

*Gambar 4. 12 Halaman Kelola Menu*

## 4.13. Kelola Kategori

Pengelolaan kategori menu restoran (Makanan, Minuman, Snack, dll) untuk mempermudah penyajian menu pelanggan.

![](scratch/screenshots/kelola_kategori.png)

*Gambar 4. 13 Halaman Kelola Kategori*

## 4.14. Kelola Meja

Pengelolaan nomor meja restoran beserta pembuatan otomatis (regenerasi) token unik QR Code.

![](scratch/screenshots/kelola_meja.png)

*Gambar 4. 14 Halaman Kelola Meja*

## 4.15. Kelola Shift

Rekapitulasi waktu kerja dan rekonsiliasi kas kasir pada akhir shift untuk mendeteksi adanya selisih uang tunai.

![](scratch/screenshots/kelola_shift.png)

*Gambar 4. 15 Halaman Kelola Shift*

## 4.16. Kelola Akses

Modul matriks perizinan bagi Superadmin untuk menonaktifkan atau mengaktifkan hak akses modul bagi role tertentu.

![](scratch/screenshots/kelola_akses.png)

*Gambar 4. 16 Halaman Kelola Akses*

## 4.17. Log Aktivitas

Riwayat audit sistem yang mencatat setiap aksi penting pengguna beserta detail waktu, IP address, dan jenis aktivitas.

![](scratch/screenshots/log_aktivitas.png)

*Gambar 4. 17 Halaman Log Aktivitas*

## 4.18. Web Setting

Pengaturan nama restoran, logo restoran, persentase pajak transaksi, dan footer struk fisik.

![](scratch/screenshots/web_setting.png)

*Gambar 4. 18 Halaman Web Setting*

## 4.19. Backup Database

Fasilitas Superadmin untuk mengekspor database SQL ke dalam file backup untuk keamanan data.

![](scratch/screenshots/backup_database.png)

*Gambar 4. 19 Halaman Backup Database*

## 4.20. Kelola Karyawan

Database biodata lengkap karyawan yang terintegrasi dengan jabatan dan slip gaji.

![](scratch/screenshots/kelola_karyawan.png)

*Gambar 4. 20 Halaman Kelola Karyawan*

## 4.21. Kelola Jabatan

Pengaturan jenjang karier karyawan restoran beserta konfigurasi gaji pokok dan tunjangan.

![](scratch/screenshots/kelola_jabatan.png)

*Gambar 4. 21 Halaman Kelola Jabatan*

## 4.22. Kelola Slip Gaji

Penerbitan dan pencatatan riwayat slip gaji bulanan karyawan berdasarkan akumulasi kehadiran.

![](scratch/screenshots/kelola_slip_gaji.png)

*Gambar 4. 22 Halaman Kelola Slip Gaji*

## 4.23. Kelola Bahan & Alat (Inventaris)

Manajemen inventaris bahan baku dapur dan aset alat makan untuk memantau stok minimum.

![](scratch/screenshots/kelola_bahan_alat.png)

*Gambar 4. 23 Halaman Kelola Bahan & Alat (Inventaris)*

## 4.24. Kelola Properti

Manajemen data properti dan aset fisik berharga milik restoran.

![](scratch/screenshots/kelola_properti.png)

*Gambar 4. 24 Halaman Kelola Properti*

## 4.25. Face Scan Attendance

Halaman absensi mandiri karyawan menggunakan sistem pemindaian wajah kamera lokal restoran.

![](scratch/screenshots/face_scan.png)

*Gambar 4. 25 Halaman Face Scan Attendance*

```{=openxml}
<w:p><w:r><w:br w:type="page"/></w:r></w:p>
```

# BAB V KESIMPULAN

## 5.1. Kesimpulan

Implementasi sistem Sistem POS Premium merupakan langkah strategis dalam memodernisasi administrasi pendidikan dari pola manual menuju digital. Berdasarkan latar belakang dan rumusan masalah yang ada, dapat disimpulkan bahwa:

Optimalisasi Waktu dan Efisiensi Kerja Guru: Sistem Sistem POS Premium mampu menjawab tantangan manajemen waktu dengan mengotomatisasi proses rekapitulasi nilai (harian, tengah semester, dan akhir semester). Integrasi data yang presisi dalam sistem berbasis web ini menghilangkan prosedur perhitungan manual yang rumit, sehingga meminimalkan risiko kesalahan input dan mempercepat penyelesaian laporan hasil belajar.

Peningkatan Aksesibilitas dan Transparansi: Sistem ini menyediakan platform yang memungkinkan wali murid dan siswa untuk memantau hasil belajar secara real-time dan transparan. Dengan akses daring, kendala geografis dan waktu—seperti kewajiban orang tua untuk hadir secara fisik ke sekolah—dapat teratasi, sehingga laporan perkembangan akademik dapat diterima dengan lebih cepat dan fleksibel.

Digitalisasi Pendidikan yang Terintegrasi: Penggunaan platform web-based menciptakan ekosistem data yang terpadu antara administrator, guru, dan siswa. Hal ini tidak hanya menggantikan peran sistem pos konvensional (hard copy), tetapi juga mendukung tuntutan digitalisasi pendidikan yang menuntut keakuratan serta kemudahan pelacakan riwayat akademik siswa secara berkelanjutan.

```{=openxml}
<w:p><w:r><w:br w:type="page"/></w:r></w:p>
```

# DAFTAR PUSTAKA

Fathansyah. (2018). Sistem Basis Data. Bandung: Informatika. (Pustaka utama untuk definisi ERD dan Normalisasi).

Haviluddin. (2011). Memahami Penggunaan UML (Unified Modelling Language). Jurnal Informatika Mulawarman, 6(1). (Referensi untuk Use Case, Activity, dan Sequence Diagram).

Hutahaean, J. (2015). Konsep Sistem Informasi. Yogyakarta: Deepublish. (Referensi untuk definisi Sistem, Informasi, dan komponen SI).

Jogiyanto, H.M. (2017). Analisis dan Desain Sistem Informasi: Pendekatan Terstruktur Teori dan Praktik Aplikasi Bisnis. Yogyakarta: Andi Offset. (Pustaka kunci untuk DFD dan Konteks Diagram).

Mulyani, S. (2016). Metode Analisis dan Perancangan Sistem. Bandung: Abdi Sistematika. (Referensi untuk pemodelan grafik dan alur informasi).

Pressman, R. S. (2015). Software Engineering: A Practitioner's Approach. New York: McGraw-Hill. (Referensi standar internasional untuk siklus hidup pengembangan sistem).

Rosa, A. S., & Shalahuddin, M. (2018). Rekayasa Perangkat Lunak Terstruktur dan Berorientasi Objek. Bandung: Informatika. (Referensi komprehensif untuk UML dan implementasi class diagram).

Sutabri, T. (2012). Konsep Sistem Informasi. Yogyakarta: Andi Offset. (Referensi untuk pengertian elemen sistem dan aliran data).

Tyoso, J. S. P. (2016). Sistem Informasi Manajemen. Yogyakarta: Deepublish. (Referensi untuk penggunaan informasi dalam organisasi).
