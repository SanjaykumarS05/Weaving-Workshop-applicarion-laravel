<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'worker_id',
        'entry_date',
        'item_name',
        'type',
        'details',
        'qty_in',
        'qty_out',
        'balance_qty',
        'conversion_notes',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'qty_in' => 'decimal:3',
            'qty_out' => 'decimal:3',
            'balance_qty' => 'decimal:3',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
