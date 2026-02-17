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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();

            $table->decimal('price', 8, 2)->default(0);
            $table->decimal('compare_price', 8, 2)->nullable();
            $table->unsignedSmallInteger('stock')->default(0);
            $table->string('sku')->unique();
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
