<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo', function (Blueprint $table) {
            if (!Schema::hasColumn('promo', 'menu_ids')) {
                $table->text('menu_ids')->nullable()->after('tipe_potongan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('promo', function (Blueprint $table) {
            if (Schema::hasColumn('promo', 'menu_ids')) {
                $table->dropColumn(['menu_ids']);
            }
        });
    }
};
