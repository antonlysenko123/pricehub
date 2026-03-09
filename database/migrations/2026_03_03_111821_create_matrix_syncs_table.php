<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matrix_syncs', function (Blueprint $table) {
            $table->id();
            $table->string('status')->nullable(); // running / done / failed
            $table->integer('progress')->default(0);
            $table->integer('total')->default(0);
            $table->integer('processed')->default(0);
            $table->string('current_action')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matrix_syncs');
    }
};