<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliverySheetItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'delivery_sheet_id',
        'invoice_id',
        'status',
    ];

    public function deliverySheet()
    {
        return $this->belongsTo(DeliverySheet::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
