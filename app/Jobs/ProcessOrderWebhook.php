<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Order;
use App\Support\PhoneHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessOrderWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Company $company;
    public array $payload;

    public function __construct(Company $company, array $payload)
    {
        $this->company = $company;
        $this->payload = $payload;
    }

    public function handle(): void
    {
        $orderData = $this->payload;

        $shopifyId = $orderData['id'];

        $customer  = $orderData['customer'] ?? [];
        $billing   = $orderData['billing_address'] ?? [];

        $rawPhone = $customer['phone'] ?? $billing['phone'] ?? null;
        $phone    = \App\Support\PhoneHelper::normalize($rawPhone, 'IN');

        $paymentGatewayNames = $orderData['gateway'] ?? ($orderData['payment_gateway_names'][0] ?? null);
        $paymentMethod = $paymentGatewayNames === 'Cash on Delivery (COD)' || stripos($paymentGatewayNames, 'cod') !== false
            ? 'COD'
            : 'prepaid';

        $order = Order::updateOrCreate(
            ['shopify_order_id' => $shopifyId],
            [
                'company_id'      => $this->company->id,
                'customer_name'   => trim(($customer['first_name'] ?? '').' '.($customer['last_name'] ?? '')),
                'customer_phone'  => $phone,
                'customer_email'  => $customer['email'] ?? null,
                'total_price'     => $orderData['total_price'] ?? 0,
                'financial_status'=> $orderData['financial_status'] ?? 'pending',
                'payment_method'  => $paymentMethod,
                'cod_status'      => 'pending',
                'line_items'      => $orderData['line_items'] ?? [],
            ]
        );

        if ($order->payment_method === 'COD') {
            \App\Jobs\RunCodAutomation::dispatch($this->company, $order);
        }
    }
}
