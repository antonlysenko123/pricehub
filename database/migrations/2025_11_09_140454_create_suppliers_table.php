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
    Schema::create('suppliers', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('code')->unique();        // короткий код постачальника (itlink, brain...)
        $table->string('type')->default('http'); // http, ftp, gdrive
        $table->string('source_url')->nullable();// базове посилання/шлях
        $table->json('config')->nullable();      // конфіг формату прайсу
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
