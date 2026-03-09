<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE product_prices MODIFY product_name TEXT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE product_prices MODIFY product_name VARCHAR(255) NULL");
    }
};