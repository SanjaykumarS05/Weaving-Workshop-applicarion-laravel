<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliverySheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sheet_number',
        'sheet_date',
        'driver_name',
        'vehicle_number',
        'notes',
    ];

    protected $casts = [
        'sheet_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(DeliverySheetItem::class);
    }
}
