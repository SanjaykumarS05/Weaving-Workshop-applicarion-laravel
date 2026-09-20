<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'business_name',
        'role',
        'trial_started_at',
        'plan',
        'trial_days',
        'active',
        'email_otp',
        'otp_expires_at',
        'is_verified',
        'password',
        'allowed_navs',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'trial_started_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'password' => 'hashed',
            'active' => 'boolean',
            'is_verified' => 'boolean',
            'trial_days' => 'integer',
            'allowed_navs' => 'array',
        ];
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function deliverySheets()
    {
        return $this->hasMany(DeliverySheet::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function setting()
    {
        return $this->hasOne(Setting::class);
    }

    public function getTrialStatusAttribute()
    {
        return [
            'active' => true,
            'unlimited' => true,
            'daysLeft' => null,
            'startedAt' => $this->trial_started_at ? $this->trial_started_at->toIso8601String() : null,
            'endsAt' => null
        ];
    }
}
