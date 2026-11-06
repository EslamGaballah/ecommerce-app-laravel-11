<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedBigInteger('views_count')->default(0)->index();
            $table->text('description')->nullable()->change();
        });

        Schema::table('orders', function (Blueprint $table) {
            // Already included in "discount"; kept apart so an order can show where its discount came from.
            $table->decimal('bundle_discount', 10, 2)->default(0);
        });

        Schema::create('bundles', function (Blueprint $table) {
            $table->id();
            $table->string('name_ar');
            $table->string('name_en');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            // When on, three different products count as 3 but three of one product count as 1.
            $table->boolean('distinct_products')->default(false);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();
        });

        Schema::create('bundle_product', function (Blueprint $table) {
            $table->foreignId('bundle_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['bundle_id', 'product_id']);
        });

        Schema::create('bundle_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bundle_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('min_quantity');
            $table->string('discount_type', 10);
            $table->decimal('discount_value', 10, 2);
            $table->unique(['bundle_id', 'min_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bundle_tiers');
        Schema::dropIfExists('bundle_product');
        Schema::dropIfExists('bundles');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('bundle_discount');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['is_featured', 'views_count']);
        });
    }
};
