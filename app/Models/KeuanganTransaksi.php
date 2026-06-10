<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KeuanganTransaksi extends Model
{
    use SoftDeletes;

    protected $table = 'keuangan_transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'tanggal',
        'kode_akun',
        'deskripsi',
        'metode',
        'debit',
        'kredit',
        'id_user',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'debit' => 'integer',
        'kredit' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    // Helper to get Account Name
    public function getNamaAkunAttribute()
    {
        $coa = [
            // Revenue
            4100 => 'Penjualan Makanan (Dine-in, Takeaway)',
            4200 => 'Penjualan Minuman',
            4300 => 'Penjualan Produk Kemasan / Merchandise',
            4400 => 'Pendapatan Kemitraan (Aggregator)',
            4500 => 'Pendapatan Lain-lain',
            // HPP/COGS
            5100 => 'HPP Bahan Baku Makanan',
            5200 => 'HPP Bahan Baku Minuman',
            5300 => 'HPP Kemasan & Konsumabel',
            // OPEX
            6100 => 'OPEX - Biaya Karyawan',
            6101 => 'OPEX - Gaji Pokok Karyawan',
            6102 => 'OPEX - Insentif / Lembur Karyawan',
            6103 => 'OPEX - THR & BPJS Karyawan',
            6200 => 'OPEX - Utilitas & Tempat',
            6201 => 'OPEX - Sewa Bulanan Tempat',
            6202 => 'OPEX - Listrik Toko',
            6203 => 'OPEX - Air Toko',
            6204 => 'OPEX - Gas LPG',
            6205 => 'OPEX - Internet / Wi-Fi',
            6300 => 'OPEX - Pemasaran & Promosi',
            6301 => 'OPEX - Digital Ads',
            6302 => 'OPEX - Influencer / Endorsement',
            6303 => 'OPEX - Cetak Menu & Banner',
            6304 => 'OPEX - Diskon & Promo',
            6305 => 'OPEX - Komisi Aplikasi Ojol (15-20%)',
            6400 => 'OPEX - Pemeliharaan & Kebersihan',
            6401 => 'OPEX - Servis Alat Dapur/Chiller/Mesin Kopi',
            6402 => 'OPEX - Servis Gedung / AC',
            6403 => 'OPEX - Bahan Kimia / Sabun Cuci',
            6404 => 'OPEX - Alat Makan Pecah (Breakage / Spoilage)',
            6500 => 'OPEX - Administrasi & Legalitas',
            6501 => 'OPEX - Sampah & Keamanan Lingkungan',
            6502 => 'OPEX - Pajak PB1/PBJT (10%)',
            6503 => 'OPEX - Sertifikasi Halal / NIB',
            6504 => 'OPEX - Biaya Admin Bank / QRIS',
            6505 => 'OPEX - Selisih Kas Toko (Shortage)',
        ];

        return $coa[$this->kode_akun] ?? 'Akun Tidak Dikenal (' . $this->kode_akun . ')';
    }

    // Helper to get Account Category
    public function getKategoriAkunAttribute()
    {
        $code = $this->kode_akun;
        if ($code >= 4000 && $code < 5000) {
            return 'Pendapatan';
        } elseif ($code >= 5000 && $code < 6000) {
            return 'HPP';
        } elseif ($code >= 6000 && $code < 7000) {
            return 'Beban Operasional (OPEX)';
        }
        return 'Lain-lain';
    }
}
