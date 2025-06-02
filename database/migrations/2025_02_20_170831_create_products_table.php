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
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            $table->string('name');
            $table->text('description');
            $table->string('slug')->unique();
            $table->decimal('price', 8, 2)->default(0);
            $table->decimal('compare_price', 8, 2)->nullable();
            // $table->unsignedSmallInteger('quantity')->default(0);
            // $table->json('options')->nullable();
            $table->float('rating',1, 1)->default(0);
            $table->boolean('featured')->default(0);
            $table->enum('status',['active', 'draft', 'archived'])->default('active');
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
