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
        Schema::create('variation_attribute_values', function (Blueprint $table) {

            $table->foreignId('product_variation_id')->constrained('product_variations')->cascadeOnDelete();

            $table->foreignId('attribute_value_id')->constrained('attribute_values')->cascadeOnDelete();

            $table->primary(['product_variation_id','attribute_value_id']);
           
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('variation_attribute_values');
    }
};