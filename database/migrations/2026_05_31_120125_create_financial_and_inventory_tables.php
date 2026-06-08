<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';

        // 1. keuangan_transaksi (Financial Ledger Transactions)
        Schema::create('keuangan_transaksi', function (Blueprint $table) use ($isSqlite) {
            $table->integer('id_transaksi')->autoIncrement();
            $table->date('tanggal');
            $table->integer('kode_akun'); // e.g. 4100, 5100, 6100
            $table->string('deskripsi', 255);
            $table->enum('metode', ['Tunai', 'Transfer', 'Potong']);
            $table->integer('debit')->default(0);
            $table->integer('kredit')->default(0);
            $table->integer('id_user')->nullable(); // Who recorded it
            $table->timestamps();
            $table->softDeletes();

            if (!$isSqlite) $table->primary('id_transaksi');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('set null');
        });

        // 2. bahan_alat (Materials & Tools Inventory)
        Schema::create('bahan_alat', function (Blueprint $table) use ($isSqlite) {
            $table->integer('id_item')->autoIncrement();
            $table->string('nama_item', 255);
            $table->enum('tipe', ['bahan', 'alat']); // Bahan Baku vs Peralatan
            $table->string('kategori', 255); // e.g. Makanan, Minuman, Kopi, Kebersihan, Dapur
            $table->decimal('stok', 10, 2)->default(0);
            $table->string('satuan', 50); // e.g. kg, liter, pcs, gram, unit
            $table->integer('harga_estimasi')->default(0);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            if (!$isSqlite) $table->primary('id_item');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keuangan_transaksi');
        Schema::dropIfExists('bahan_alat');
    }
};
