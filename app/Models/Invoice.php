<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'invoice_number',
        'invoice_date',
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'customer_gstin',
        'customer_state',
        'customer_state_code',
        'supply_type',
        'e_way_bill_no',
        'delivery_note',
        'reference_no_date',
        'other_references',
        'buyers_order_no',
        'buyers_order_date',
        'dispatch_doc_no',
        'delivery_note_date',
        'dispatched_through',
        'destination',
        'terms_of_delivery',
        'subtotal',
        'taxable_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'discount_amount',
        'round_off',
        'grand_total',
        'paid_amount',
        'status',
        'payment_mode',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'buyers_order_date' => 'date',
        'delivery_note_date' => 'date',
        'subtotal' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'round_off' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getPendingAmountAttribute()
    {
        return max(0, $this->grand_total - $this->paid_amount);
    }
}
