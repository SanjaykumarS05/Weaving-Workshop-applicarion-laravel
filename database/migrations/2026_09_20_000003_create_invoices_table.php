<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('invoice_number')->index();
            $table->date('invoice_date');
            $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
            $table->string('customer_name');
            $table->string('customer_phone')->nullable();
            $table->text('customer_address')->nullable();
            $table->string('customer_gstin')->nullable();
            $table->string('customer_state')->nullable();
            $table->string('customer_state_code', 10)->nullable();
            $table->string('supply_type', 10)->default('intra'); // intra, inter, none
            $table->string('e_way_bill_no')->nullable();
            $table->string('delivery_note')->nullable();
            $table->string('reference_no_date')->nullable();
            $table->string('other_references')->nullable();
            $table->string('buyers_order_no')->nullable();
            $table->date('buyers_order_date')->nullable();
            $table->string('dispatch_doc_no')->nullable();
            $table->date('delivery_note_date')->nullable();
            $table->string('dispatched_through')->nullable();
            $table->string('destination')->nullable();
            $table->text('terms_of_delivery')->nullable();
            $table->decimal('subtotal', 15, 2)->default(0.00);
            $table->decimal('taxable_amount', 15, 2)->default(0.00);
            $table->decimal('cgst_amount', 15, 2)->default(0.00);
            $table->decimal('sgst_amount', 15, 2)->default(0.00);
            $table->decimal('igst_amount', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('round_off', 8, 2)->default(0.00);
            $table->decimal('grand_total', 15, 2)->default(0.00);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->string('status', 20)->default('unpaid'); // paid, partial, unpaid
            $table->string('payment_mode')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
