<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\DeliverySheetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSalesController;
use App\Http\Controllers\SettingController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showAuth'])->name('login');
Route::post('/api/signup', [AuthController::class, 'signUp'])->name('signup');
Route::post('/api/signin', [AuthController::class, 'signIn'])->name('signin');
Route::post('/api/logout', [AuthController::class, 'signOut'])->name('logout');
Route::get('/api/me', [AuthController::class, 'me'])->name('me');

// OTP & Forgot Password Routes
Route::post('/api/verify-otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');
Route::post('/api/resend-otp', [AuthController::class, 'resendOtp'])->name('otp.resend');
Route::post('/api/forgot-password', [AuthController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('/reset-password', [AuthController::class, 'showResetForm'])->name('password.reset');
Route::post('/api/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Authenticated Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/api/dashboard/metrics', [DashboardController::class, 'getMetrics'])->name('dashboard.metrics');

    // Billing
    Route::get('/billing', [BillingController::class, 'create'])->name('billing');
    Route::get('/api/billing/next-number', [BillingController::class, 'getNextInvoiceNumber'])->name('billing.next-number');
    Route::post('/api/invoices', [BillingController::class, 'store'])->name('invoices.store');

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/{id}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{id}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::delete('/api/invoices/{id}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');

    // Delivery Sheets
    Route::get('/delivery-sheets', [DeliverySheetController::class, 'index'])->name('delivery-sheets.index');
    Route::get('/api/delivery-sheets/next-number', [DeliverySheetController::class, 'getNextSheetNumber'])->name('delivery-sheets.next-number');
    Route::post('/api/delivery-sheets', [DeliverySheetController::class, 'store'])->name('delivery-sheets.store');
    Route::get('/delivery-sheets/{id}/print', [DeliverySheetController::class, 'print'])->name('delivery-sheets.print');
    Route::delete('/api/delivery-sheets/{id}', [DeliverySheetController::class, 'destroy'])->name('delivery-sheets.destroy');

    // Payments
    Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
    Route::post('/api/payments', [PaymentController::class, 'store'])->name('payments.store');
    Route::delete('/api/payments/{id}', [PaymentController::class, 'destroy'])->name('payments.destroy');

    // Customers
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/api/customers', [CustomerController::class, 'index'])->name('api.customers.index');
    Route::post('/api/customers', [CustomerController::class, 'store'])->name('customers.store');
    Route::put('/api/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/api/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Products
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/api/products', [ProductController::class, 'index'])->name('api.products.index');
    Route::post('/api/products', [ProductController::class, 'store'])->name('products.store');
    Route::put('/api/products/{id}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/api/products/{id}', [ProductController::class, 'destroy'])->name('products.destroy');

    // Product Sales Report
    Route::get('/product-sales', [ProductSalesController::class, 'index'])->name('product-sales.index');

    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/api/settings', [SettingController::class, 'update'])->name('settings.update');

    // Team users (Owner only)
    Route::post('/api/team-user', [AuthController::class, 'createTeamUser'])->name('team-user.create');
    Route::put('/api/team-user/{id}', [AuthController::class, 'updateTeamUser'])->name('team-user.update');
    Route::delete('/api/team-user/{id}', [AuthController::class, 'deleteTeamUser'])->name('team-user.destroy');
});
