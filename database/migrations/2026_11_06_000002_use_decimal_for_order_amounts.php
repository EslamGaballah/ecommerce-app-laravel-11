<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('shipping', 12, 2)->default(0)->change();
            $table->decimal('tax', 12, 2)->default(0)->change();
            $table->decimal('discount', 12, 2)->default(0)->change();
            $table->decimal('total', 12, 2)->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->float('shipping')->default(0)->change();
            $table->float('tax')->default(0)->change();
            $table->float('discount')->default(0)->change();
            $table->float('total')->default(0)->change();
        });
    }
};
