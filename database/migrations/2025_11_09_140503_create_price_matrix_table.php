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
    Schema::create('price_matrix', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('product_id');   // ID нашого товару в іншій базі
        $table->foreignId('supplier_id')->constrained();

        $table->string('supplier_sku')->nullable();
        $table->string('manufacturer_sku')->nullable();
        $table->string('manufacturer_name')->nullable();
        $table->string('barcode')->nullable();

        $table->foreignId('last_price_row_id')->nullable()->constrained('price_rows');
        $table->decimal('last_price', 12, 2)->nullable();
        $table->decimal('last_rrp', 12, 2)->nullable();
        $table->timestamp('last_price_updated_at')->nullable();
        $table->timestamps();

        $table->index(['product_id', 'supplier_id']);
        $table->index(['supplier_id', 'supplier_sku']);
        $table->index(['supplier_id', 'manufacturer_sku']);
        $table->index(['supplier_id', 'barcode']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_matrix');
    }
};
