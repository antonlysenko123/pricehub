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
    Schema::create('price_files', function (Blueprint $table) {
        $table->id();
        $table->foreignId('supplier_id')->constrained();
        $table->string('filename');               // шлях у storage (prices/.../file.xlsx)
        $table->string('original_name')->nullable();
        $table->string('extension', 10)->nullable();
        $table->unsignedBigInteger('rows_count')->default(0);
        $table->timestamp('price_date')->nullable();
        $table->enum('status', ['downloaded', 'parsed', 'failed'])->default('downloaded');
        $table->text('error_message')->nullable();
        $table->timestamps();

        $table->index(['supplier_id', 'status']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('price_files');
    }
};
