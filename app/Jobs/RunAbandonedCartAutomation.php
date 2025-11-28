<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\AbandonedCheckout;
use App\Services\WhatifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunAbandonedCartAutomation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Company $company;
    public AbandonedCheckout $cart;

    public function __construct(Company $company, AbandonedCheckout $cart)
    {
        $this->company = $company;
        $this->cart    = $cart;
    }

    public function handle(): void
    {
        $automation = $this->company->automations()
            ->where('trigger', 'abandoned_cart')
            ->where('is_active', true)
            ->first();

        if (! $automation || ! $this->cart->customer_phone || ! $this->company->whatify_token) {
            return;
        }

        $flow  = $automation->flow_definition ?? [];
        $steps = $flow['steps'] ?? [];

        if (! is_array($steps) || count($steps) === 0) {
            return;
        }

        $whatsapp = new WhatifyService($this->company);

        foreach ($steps as $step) {
            $type   = $step['type'] ?? null;
            $config = $step['config'] ?? [];

            switch ($type) {
                case 'simple_text':
                    $text = $this->renderVariables($config['text'] ?? '', $this->cart);
                    if ($text !== '') {
                        $whatsapp->sendText($this->cart->customer_phone, $text);
                    }
                    break;
                case 'media':
                    $mediaUrl  = $config['media_url']  ?? '';
                    $mediaType = $config['media_type'] ?? 'image';
                    if ($mediaUrl !== '') {
                        $caption = $this->renderVariables($config['caption'] ?? '', $this->cart);
                        $whatsapp->sendMedia(
                            $this->cart->customer_phone,
                            $mediaType,
                            $mediaUrl,
                            $caption
                        );
                    }
                    break;
                // You can add buttons/delay as needed, same as COD job above
            }
        }
    }

    protected function renderVariables(string $text, AbandonedCheckout $cart): string
    {
        return str_replace(
            ['{{ customer_email }}', '{{ cart_total }}', '{{ abandoned_at }}'],
            [$cart->customer_email ?? '', $cart->total_price, $cart->abandoned_at->format('Y-m-d H:i')],
            $text
        );
    }
}
