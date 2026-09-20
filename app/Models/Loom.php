<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loom extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'worker_id',
        'entry_date',
        'details',
        'type',
        'qty_in',
        'qty_out',
        'notes',
    ];

    protected $casts = [
        'entry_date' => 'date',
        'qty_in' => 'decimal:3',
        'qty_out' => 'decimal:3',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
