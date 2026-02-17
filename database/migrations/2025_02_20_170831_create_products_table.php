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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('slug')->unique();
            $table->string('status')->default('draft');
            $table->string('product_type')->default('simple');


            $table->decimal('rating_avg', 3, 2)->default(0);
            $table->unsignedSmallInteger('rating_count')->default(0);

            // Single Product Only
            $table->decimal('price', 8, 2)->nullable();
            $table->decimal('compare_price', 8, 2)->nullable();
            $table->unsignedSmallInteger('stock')->nullable();
            $table->string('sku')->nullable();

            
            $table->boolean('featured')->default(0);

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->timestamps();

            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
