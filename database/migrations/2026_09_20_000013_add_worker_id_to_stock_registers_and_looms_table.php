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
        Schema::table('stock_registers', function (Blueprint $table) {
            $table->foreignId('worker_id')->nullable()->after('user_id')->constrained('workers')->nullOnDelete();
        });

        Schema::table('looms', function (Blueprint $table) {
            $table->foreignId('worker_id')->nullable()->after('user_id')->constrained('workers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_registers', function (Blueprint $table) {
            $table->dropForeign(['worker_id']);
            $table->dropColumn('worker_id');
        });

        Schema::table('looms', function (Blueprint $table) {
            $table->dropForeign(['worker_id']);
            $table->dropColumn('worker_id');
        });
    }
};
