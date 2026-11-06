<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coupons', function (Blueprint $table) {
            $table->enum('type', ['fixed', 'percent', 'free_shipping'])->change();
        });
    }

    public function down(): void
    {
        DB::table('coupons')->where('type', 'free_shipping')->delete();

        Schema::table('coupons', function (Blueprint $table) {
            $table->enum('type', ['fixed', 'percent'])->change();
        });
    }
};
