<?php

namespace App\Jobs;

use App\Models\Company;
use App\Models\Order;
use App\Services\WhatifyService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RunCodAutomation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Company $company;
    public Order $order;

    public function __construct(Company $company, Order $order)
    {
        $this->company = $company;
        $this->order   = $order;
    }

    public function handle(): void
    {
        // Find active COD automation
        $automation = $this->company->automations()
            ->where('trigger', 'cod_order')
            ->where('is_active', true)
            ->first();

        if (! $automation || ! $this->order->customer_phone || ! $this->company->whatify_token) {
            return;
        }

        $flow  = $automation->flow_definition ?? [];
        $steps = $flow['steps'] ?? [];

        if (! is_array($steps) || count($steps) === 0) {
            return;
        }

        $whatsapp = new WhatifyService($this->company);

        foreach ($steps as $step) {
            $type   = $step['type']   ?? null;
            $config = $step['config'] ?? [];

            switch ($type) {
                case 'simple_text':
                    $text = $this->renderVariables($config['text'] ?? '', $this->order);
                    if ($text !== '') {
                        $whatsapp->sendText($this->order->customer_phone, $text);
                    }
                    break;

case 'media':
    $mediaUrl  = $config['media_url']  ?? '';
    $mediaType = $config['media_type'] ?? 'image';
    if ($mediaUrl !== '') {
        $caption = $this->renderVariables($config['caption'] ?? '', $this->order);
        $whatsapp->sendMedia(
            $this->order->customer_phone,
            $mediaType,
            $mediaUrl,
            $caption
        );
    }
    break;

case 'buttons':
    $templateName = $config['template_name'] ?? null;
    if (! $templateName) {
        break;
    }
    $languageCode = $config['language_code'] ?? 'en';
    $components   = $this->buildTemplateComponents($config, $this->order);
    $whatsapp->sendTemplate(
        $this->order->customer_phone,
        $templateName,
        $languageCode,
        $components
    );
    break;
                case 'delay':
                    // Day 3: ignore real delay, keep it simple & immediate
                    // Later we'll schedule follow-up jobs with ->delay()
                    break;

                default:
                    // ignore unknown types for now
                    break;
            }
        }
    }

    protected function renderVariables(string $text, Order $order): string
    {
        return str_replace(
            ['{{ customer_name }}', '{{ order_total }}', '{{ order_id }}'],
            [$order->customer_name ?? '', $order->total_price, $order->shopify_order_id],
            $text
        );
    }

protected function buildTemplateComponents(array $config, Order $order): array
{
    $components = [];

    if (! empty($config['body'])) {
        $components[] = [
            'type'       => 'body',
            'parameters' => [
                ['type' => 'text', 'text' => $this->renderVariables($config['body'], $order)],
            ],
        ];
    }

    if (! empty($config['buttons']) && is_array($config['buttons'])) {
        foreach ($config['buttons'] as $index => $btn) {
            $components[] = [
                'type'      => 'button',
                'subType'   => 'quick_reply',
                'index'     => $index,
                'parameters'=> [
                    ['type' => 'payload', 'payload' => $btn['payload'] ?? ''],
                ],
            ];
        }
    }

    return $components;
}

}
