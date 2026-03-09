<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE product_prices DROP INDEX product_prices_product_id_supplier_name_unique");
        DB::statement("ALTER TABLE product_prices ADD UNIQUE supplier_sku_supplier_unique (supplier_name, supplier_sku)");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE product_prices DROP INDEX supplier_sku_supplier_unique");
        DB::statement("ALTER TABLE product_prices ADD UNIQUE product_prices_product_id_supplier_name_unique (product_id, supplier_name)");
    }
};