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
        // 1. role
        Schema::create('role', function (Blueprint $table) {
            $table->integer('id_role')->autoIncrement();
            $table->string('role', 50);
            $table->primary('id_role');
        });

        // 2. users
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id_user')->autoIncrement();
            $table->string('username', 255);
            $table->string('email', 255)->unique();
            $table->string('password', 80);
            $table->integer('id_role');
            $table->timestamps();
            $table->softDeletes();
            
            $table->primary('id_user');
            $table->foreign('id_role')->references('id_role')->on('role')->onDelete('cascade');
        });

        // 3. aksess
        Schema::create('aksess', function (Blueprint $table) {
            $table->integer('id_akses')->autoIncrement();
            $table->integer('id_role');
            $table->string('modul', 255);
            $table->enum('allowed', ['0', '1']);
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id_akses');
            $table->foreign('id_role')->references('id_role')->on('role')->onDelete('cascade');
        });

        // 4. shift
        Schema::create('shift', function (Blueprint $table) {
            $table->integer('id_shift')->autoIncrement();
            $table->integer('id_user');
            $table->timestamp('jam_mulai')->useCurrent();
            $table->timestamp('jam_selesai')->nullable();
            $table->integer('cash_masuk')->default(0);
            $table->integer('qris_masuk')->default(0);
            $table->integer('total_masuk')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id_shift');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        // 5. kategori
        Schema::create('kategori', function (Blueprint $table) {
            $table->integer('id_kategori')->autoIncrement();
            $table->string('kategori', 50);
            $table->timestamps();
            $table->softDeletes(); // Consider adding for consistency as suggested in database_detail.md

            $table->primary('id_kategori');
        });

        // 6. menu
        Schema::create('menu', function (Blueprint $table) {
            $table->integer('id_menu')->autoIncrement();
            $table->string('nama_menu', 255);
            $table->integer('id_kategori');
            $table->string('foto', 255)->nullable();
            $table->integer('harga');
            $table->enum('status', ['habis', 'tersedia'])->default('tersedia');
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id_menu');
            $table->foreign('id_kategori')->references('id_kategori')->on('kategori')->onDelete('cascade');
        });

        // 7. meja
        Schema::create('meja', function (Blueprint $table) {
            $table->integer('id_meja')->autoIncrement();
            $table->integer('nomor_meja');
            $table->string('qrcode_token', 255)->unique();
            $table->enum('status', ['kosong', 'terisi'])->default('kosong');
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id_meja');
        });

        // 8. pesanan
        Schema::create('pesanan', function (Blueprint $table) {
            $table->integer('id_pesanan')->autoIncrement();
            $table->string('kode_struk', 255)->unique();
            $table->integer('id_meja');
            $table->enum('metode_pembayaran', ['cash', 'qris']);
            $table->integer('total_harga');
            $table->integer('pajak');
            $table->integer('total_bayar');
            $table->integer('id_user');
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id_pesanan');
            $table->foreign('id_meja')->references('id_meja')->on('meja')->onDelete('cascade');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        // 9. detail_pesanan
        Schema::create('detail_pesanan', function (Blueprint $table) {
            $table->integer('id_detail')->autoIncrement();
            $table->integer('id_pesanan')->nullable(); // Can be null in draft state before payment confirmation
            $table->integer('id_menu');
            $table->integer('jumlah');
            $table->integer('harga_satuan');
            $table->integer('subtotal');
            $table->string('catatan', 255)->nullable();
            $table->enum('status', ['menunggu', 'dimasak', 'selesai'])->default('menunggu');
            $table->timestamps();
            $table->softDeletes();

            // Temporary field to track orders that are guest drafts for a table before they are paid/confirmed
            $table->integer('id_meja_temp')->nullable(); 

            $table->primary('id_detail');
            $table->foreign('id_pesanan')->references('id_pesanan')->on('pesanan')->onDelete('cascade');
            $table->foreign('id_menu')->references('id_menu')->on('menu')->onDelete('cascade');
        });

        // 10. activity_log
        Schema::create('activity_log', function (Blueprint $table) {
            $table->integer('id_log')->autoIncrement();
            $table->integer('id_user')->nullable(); // nullable to support guest actions
            $table->string('aktivitas', 255);
            $table->string('detail_aktivitas', 255)->nullable();
            $table->string('ip_address', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id_log');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('cascade');
        });

        // 11. history_update
        Schema::create('history_update', function (Blueprint $table) {
            $table->integer('id_update')->autoIncrement();
            $table->string('table', 255);
            $table->integer('record_id');
            $table->text('data_lama')->nullable();
            $table->text('data_baru')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->primary('id_update');
        });

        // Laravel Default Tables Support
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->integer('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('history_update');
        Schema::dropIfExists('activity_log');
        Schema::dropIfExists('detail_pesanan');
        Schema::dropIfExists('pesanan');
        Schema::dropIfExists('meja');
        Schema::dropIfExists('menu');
        Schema::dropIfExists('kategori');
        Schema::dropIfExists('shift');
        Schema::dropIfExists('aksess');
        Schema::dropIfExists('users');
        Schema::dropIfExists('role');
    }
};
