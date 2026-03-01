<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('price_files', function (Blueprint $table) {
            $table->unsignedTinyInteger('progress')->default(0)->after('rows_count');
            $table->string('current_action', 50)->nullable()->after('progress');
        });
    }

    public function down(): void
    {
        Schema::table('price_files', function (Blueprint $table) {
            $table->dropColumn(['progress', 'current_action']);
        });
    }
};
