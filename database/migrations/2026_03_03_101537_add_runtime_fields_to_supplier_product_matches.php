<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE supplier_product_matches 
            ADD current_price DECIMAL(12,2) NULL,
            ADD current_quantity INT NULL,
            ADD current_availability VARCHAR(50) NULL,
            ADD price_row_id BIGINT UNSIGNED NULL,
            ADD last_synced_at DATETIME NULL
        ");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE supplier_product_matches 
            DROP current_price,
            DROP current_quantity,
            DROP current_availability,
            DROP price_row_id,
            DROP last_synced_at
        ");
    }
};