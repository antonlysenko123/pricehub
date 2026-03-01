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
    Schema::create('price_rows', function (Blueprint $table) {
        $table->id();
        $table->foreignId('price_file_id')->constrained();

        $table->string('supplier_sku')->nullable();      // код у постачальника
        $table->string('manufacturer_sku')->nullable();  // модель виробника
        $table->string('manufacturer_name')->nullable(); // бренд
        $table->string('barcode')->nullable();
        $table->string('name')->nullable();
        $table->string('category')->nullable();

        $table->decimal('price', 12, 2)->nullable();     // фактична ціна постачальника
        $table->decimal('rrp', 12, 2)->nullable();       // РРЦ

        $table->integer('quantity')->nullable();
        $table->string('availability_status')->nullable(); // in_stock / out_of_stock / ...

        $table->json('raw')->nullable();                 // повний сирий рядок
        $table->timestamps();

        $table->index(['price_file_id']);
        $table->index(['supplier_sku']);
        $table->index(['manufacturer_sku']);
        $table->index(['barcode']);
        $table->index(['manufacturer_name']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_rows');
    }
};
