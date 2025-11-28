<?php

use Illuminate\Support\Facades\Route;
use App\Models\Company;
use App\Http\Controllers\WhatsappSettingsController;
use App\Http\Controllers\AutomationController;
use App\Services\WhatifyService; /*test route*/
use App\Models\Order;
use App\Jobs\RunCodAutomation;
use App\Models\AbandonedCheckout;
use App\Jobs\RunAbandonedCartAutomation;
use Illuminate\Http\Request;
use App\Support\PhoneHelper;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\WebhookController;


Route::get('/', function () {
    return view('marketing.home');
});

Route::get('/app', function (\Illuminate\Http\Request $request) {
    $company = $request->session()->get('current_company_id')
        ? \App\Models\Company::find($request->session()->get('current_company_id'))
        : \App\Models\Company::first();

    return view('dashboard', ['company' => $company]);
});

// Settings + automations for first company (later will be per-store auth)
Route::prefix('companies/{company}')->group(function () {
    Route::get('whatsapp-settings', [WhatsappSettingsController::class, 'edit'])->name('whatsapp.settings.edit');
    Route::post('whatsapp-settings', [WhatsappSettingsController::class, 'update'])->name('whatsapp.settings.update');
    Route::post('whatsapp-settings/test', [WhatsappSettingsController::class, 'test'])->name('whatsapp.settings.test');

    Route::get('automations', [AutomationController::class, 'index'])->name('automations.index');
    Route::get('automations/create', [AutomationController::class, 'create'])->name('automations.create');
    Route::post('automations', [AutomationController::class, 'store'])->name('automations.store');
        Route::get('automations/{automation}/edit', [AutomationController::class, 'edit'])->name('automations.edit');
    Route::post('automations/{automation}', [AutomationController::class, 'update'])->name('automations.update');
});

Route::get('/dev/test-whatsapp', function (Request $request) {
    $company = Company::first();
    if (! $company || ! $company->whatify_token) {
        return 'Set Whatify token first.';
    }

    // Use ?phone= query or fall back to company business_phone
    $phone = $request->query('phone', $company->business_phone);

    $service = new WhatifyService($company);
    $result  = $service->sendText($phone, 'Dev test from Laravel Whatify app.');

    return $result;
});

Route::get('/dev/create-demo-cod', function () {
    $company = Company::first();
    if (! $company) {
        return 'No company. Run seeder first.';
    }

    $order = $company->orders()->create([
        'shopify_order_id' => 'DEMO-'.now()->format('YmdHis'),
        'customer_name'    => 'Demo Customer',
        'customer_phone'   => $company->business_phone ?: '+919569283474',
        'customer_email'   => 'demo@example.com',
        'total_price'      => 999,
        'financial_status' => 'pending',
        'payment_method'   => 'COD',
        'cod_status'       => 'pending',
        'line_items'       => [
            ['name' => 'Demo product', 'qty' => 1],
        ],
    ]);

    return "Demo COD order created: {$order->shopify_order_id}. Now hit /dev/run-cod-automation to send WhatsApp.";
});

Route::get('/dev/run-cod-automation', function () {
    $company = Company::first();
    if (! $company) {
        return 'No company.';
    }

    $order = $company->orders()
        ->where('payment_method', 'COD')
        ->latest()
        ->first();

    if (! $order) {
        return 'No COD order found. Visit /dev/create-demo-cod first.';
    }

    RunCodAutomation::dispatch($company, $order);

    return "RunCodAutomation dispatched for order {$order->shopify_order_id}. Ensure queue worker is running.";
});

Route::get('/dev/create-demo-abandoned-cart', function () {
    $company = \App\Models\Company::first();
    if (! $company) {
        return 'No company, run seeder first.';
    }

    $cart = $company->abandonedCheckouts()->create([
        'shopify_checkout_id' => 'CART-'.now()->format('YmdHis'),
        'customer_phone'      => $company->business_phone ?: '+919569283474',
        'customer_email'      => 'cart-demo@example.com',
        'total_price'         => 249,
        'line_items'          => [
            ['name' => 'Demo cart product', 'qty' => 1],
        ],
        'abandoned_at'        => now(),
        'recovery_status'     => 'pending',
    ]);

    return "Demo cart created: {$cart->shopify_checkout_id}. Now hit /dev/run-abandoned-cart-automation";
});

Route::get('/dev/run-abandoned-cart-automation', function () {
    $company = Company::first();
    if (! $company) {
        return 'No company.';
    }
    $cart = $company->abandonedCheckouts()
        ->where('recovery_status', 'pending')
        ->latest()
        ->first();

    if (! $cart) {
        return 'No abandoned cart found. Visit /dev/create-demo-abandoned-cart first.';
    }

    RunAbandonedCartAutomation::dispatch($company, $cart);

    return "RunAbandonedCartAutomation dispatched for cart {$cart->shopify_checkout_id}. Ensure queue worker is running.";
});


// Shopify OAuth
Route::get('/shopify/install', [ShopifyController::class, 'install']);
Route::get('/shopify/callback', [ShopifyController::class, 'callback']);


// Webhooks (must be POST, no CSRF)
Route::post('/webhooks/orders-create', [WebhookController::class, 'ordersCreate'])
    ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);