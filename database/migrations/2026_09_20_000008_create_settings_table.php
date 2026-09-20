<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('profile_name')->nullable();
            $table->string('profile_email')->nullable();
            $table->string('profile_gstin')->nullable();
            $table->string('profile_phone')->nullable();
            $table->string('profile_state')->nullable();
            $table->string('profile_state_code', 10)->nullable();
            $table->text('profile_address')->nullable();
            $table->string('profile_bank_name')->nullable();
            $table->string('profile_account_no')->nullable();
            $table->string('profile_branch_name')->nullable();
            $table->string('profile_ifsc')->nullable();
            $table->text('profile_declaration')->nullable();
            $table->string('profile_signatory')->nullable();
            $table->longText('profile_signature_data')->nullable();
            $table->decimal('default_tax_rate', 5, 2)->default(12.00);
            $table->string('invoice_prefix')->default('GST');
            $table->integer('invoice_start_value')->default(1);
            $table->integer('composition_valid_days')->default(30);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
