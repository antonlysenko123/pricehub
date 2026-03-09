<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('product_id')->index();

            $table->string('supplier_name');
            $table->string('supplier_sku');

            $table->decimal('price', 12, 2)->nullable();
            $table->integer('quantity')->nullable();
            $table->string('availability_status')->nullable();

            $table->unsignedBigInteger('price_row_id')->nullable();

            $table->timestamps();

            $table->unique(['product_id', 'supplier_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};