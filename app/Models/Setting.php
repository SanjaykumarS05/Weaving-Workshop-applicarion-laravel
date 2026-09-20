<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'profile_name',
        'profile_email',
        'profile_gstin',
        'profile_phone',
        'profile_state',
        'profile_state_code',
        'profile_address',
        'profile_bank_name',
        'profile_account_no',
        'profile_branch_name',
        'profile_ifsc',
        'profile_declaration',
        'profile_signatory',
        'profile_signature_data',
        'default_tax_rate',
        'invoice_prefix',
        'invoice_start_value',
        'composition_valid_days',
    ];

    protected $casts = [
        'default_tax_rate' => 'decimal:2',
        'invoice_start_value' => 'integer',
        'composition_valid_days' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
