<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Setting;
use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name' => 'Administrator',
            'email' => 'admin@example.com',
            'business_name' => 'GST Billing Application',
            'role' => 'admin',
            'trial_started_at' => Carbon::now(),
            'plan' => 'unlimited',
            'trial_days' => 0,
            'active' => true,
            'password' => Hash::make('admin123'),
        ]);

        Setting::create([
            'user_id' => $admin->id,
            'profile_name' => 'GST Billing Application',
            'profile_email' => 'admin@example.com',
            'profile_gstin' => '33AAAAA0000A1Z5',
            'profile_phone' => '9876543210',
            'profile_state' => 'Tamil Nadu',
            'profile_state_code' => '33',
            'profile_address' => '123 Business Street, Chennai, Tamil Nadu',
            'profile_bank_name' => 'State Bank of India',
            'profile_account_no' => '123456789012',
            'profile_branch_name' => 'Main Branch',
            'profile_ifsc' => 'SBIN0001234',
            'profile_declaration' => 'We declare that this invoice shows the actual price of the goods described and that all particulars are true and correct.',
            'profile_signatory' => 'Authorised Signatory',
            'default_tax_rate' => 12.00,
            'invoice_prefix' => 'GST',
            'invoice_start_value' => 1,
            'composition_valid_days' => 30,
        ]);

        Customer::create([
            'user_id' => $admin->id,
            'name' => 'Sample Customer Pvt Ltd',
            'phone' => '9876500000',
            'email' => 'customer@example.com',
            'gstin' => '33BBBBB0000B1Z6',
            'address' => '45 Commercial Road, Chennai',
            'state' => 'Tamil Nadu',
            'state_code' => '33',
            'balance' => 0.00,
        ]);

        Product::create([
            'user_id' => $admin->id,
            'name' => 'Cotton Fabric',
            'hsn_code' => '5208',
            'unit' => 'Meter',
            'price' => 150.00,
            'tax_rate' => 5.00,
            'stock' => 500.00,
        ]);

        Product::create([
            'user_id' => $admin->id,
            'name' => 'Polyester Yarn',
            'hsn_code' => '5402',
            'unit' => 'Kgs',
            'price' => 280.00,
            'tax_rate' => 12.00,
            'stock' => 250.00,
        ]);
    }
}
