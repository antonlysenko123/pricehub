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
    Schema::create('price_changes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('price_matrix_id')->constrained('price_matrix');
        $table->decimal('old_price', 12, 2)->nullable();
        $table->decimal('new_price', 12, 2);
        $table->timestamp('changed_at');
        $table->timestamps();

        $table->index(['changed_at']);
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_changes');
    }
};
