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

    public function canAccessNav(?string $navKey): bool
    {
        if (empty($navKey)) {
            return true;
        }
        if ($this->id === 1 || is_null($this->allowed_navs) || empty($this->allowed_navs)) {
            return true;
        }
        return in_array($navKey, $this->allowed_navs, true);
    }

    public static function getNavOrder(): array
    {
        return [
            'dashboard' => 'dashboard',
            'billing' => 'billing',
            'invoices' => 'invoices.index',
            'delivery-sheets' => 'delivery-sheets.index',
            'payments' => 'payments.index',
            'customers' => 'customers.index',
            'products' => 'products.index',
            'product-sales' => 'product-sales.index',
            'stock-register' => 'stock-register.index',
            'looms' => 'looms.index',
            'workers' => 'workers.index',
            'borrows' => 'borrows.index',
            'settings' => 'settings.index',
        ];
    }

    public function getFirstAvailableNavRoute(): string
    {
        $navMap = self::getNavOrder();
        foreach ($navMap as $navKey => $routeName) {
            if ($this->canAccessNav($navKey)) {
                return $routeName;
            }
        }
        return 'dashboard';
    }

    public function getFirstAvailableNavUrl(): string
    {
        return route($this->getFirstAvailableNavRoute());
    }
}
