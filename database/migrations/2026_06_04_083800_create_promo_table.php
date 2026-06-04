<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('promo')) {
            Schema::create('promo', function (Blueprint $table) {
                $table->integer('id_promo')->autoIncrement();
                $table->string('nama_promo', 50);
                $table->enum('tipe_promo', ['Harian', 'Mingguan', 'Bulanan', 'Sekali Pakai']);
                $table->string('deskripsi', 255)->nullable();
                $table->dateTime('start_time')->nullable();
                $table->dateTime('end_time')->nullable();
                $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
                $table->timestamps();
                $table->softDeletes();

                $table->primary('id_promo');
            });
        }

        Schema::table('promo', function (Blueprint $table) {
            if (!Schema::hasColumn('promo', 'nominal_potongan')) {
                $table->integer('nominal_potongan')->default(0)->after('status');
            }
            if (!Schema::hasColumn('promo', 'tipe_potongan')) {
                $table->enum('tipe_potongan', ['persen', 'nominal'])->default('nominal')->after('nominal_potongan');
            }
        });

        Schema::table('pesanan', function (Blueprint $table) {
            if (!Schema::hasColumn('pesanan', 'id_promo')) {
                $table->integer('id_promo')->nullable()->after('kode_struk');
                $table->foreign('id_promo')->references('id_promo')->on('promo')->onDelete('set null');
            }
            if (!Schema::hasColumn('pesanan', 'diskon')) {
                $table->integer('diskon')->default(0)->after('total_harga');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pesanan', function (Blueprint $table) {
            if (Schema::hasColumn('pesanan', 'id_promo')) {
                $table->dropForeign(['id_promo']);
                $table->dropColumn(['id_promo']);
            }
            if (Schema::hasColumn('pesanan', 'diskon')) {
                $table->dropColumn(['diskon']);
            }
        });

        Schema::table('promo', function (Blueprint $table) {
            if (Schema::hasColumn('promo', 'nominal_potongan')) {
                $table->dropColumn(['nominal_potongan']);
            }
            if (Schema::hasColumn('promo', 'tipe_potongan')) {
                $table->dropColumn(['tipe_potongan']);
            }
        });
    }
};
