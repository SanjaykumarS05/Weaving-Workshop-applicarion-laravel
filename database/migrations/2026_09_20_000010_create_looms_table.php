<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('looms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('entry_date');
            $table->string('details')->nullable(); // e.g. 4000 E 2 Warp, 170 x 1.90
            $table->enum('type', ['in', 'out'])->default('in'); // in = Warp In, out = Cloth Out
            $table->decimal('qty_in', 12, 3)->default(0); // Warp In meters
            $table->decimal('qty_out', 12, 3)->default(0); // Cloth Out meters
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('looms');
    }
};
