<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('supplier_product_matches', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('supplier_sku');

            $table->unsignedBigInteger('product_id');

            $table->timestamps();

            $table->unique(['supplier_id', 'supplier_sku']);

            $table->index(['product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('supplier_product_matches');
    }
};