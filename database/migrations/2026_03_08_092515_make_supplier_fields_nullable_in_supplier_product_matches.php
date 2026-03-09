<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE supplier_product_matches
            MODIFY supplier_name VARCHAR(255) NULL,
            MODIFY supplier_sku VARCHAR(255) NULL
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE supplier_product_matches
            MODIFY supplier_name VARCHAR(255) NOT NULL,
            MODIFY supplier_sku VARCHAR(255) NOT NULL
        ");
    }
};