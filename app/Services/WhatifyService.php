<?php

namespace App\Services;

use App\Models\Company;
use Illuminate\Support\Facades\Http;

class WhatifyService
{
    protected string $baseUrl = 'https://whatify.in/api';
    protected Company $company;

    public function __construct(Company $company)
    {
        $this->company = $company;
    }

    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer '.$this->company->whatify_token,
            'Content-Type'  => 'application/json',
        ];
    }

public function sendText(string $phone, string $message): array
{
    $response = Http::withHeaders($this->headers())
        ->post($this->baseUrl.'/send', [
            'phone'   => $phone,
            'message' => $message,
        ]);

    \Log::info('Whatify sendText', [
        'phone'  => $phone,
        'status' => $response->status(),
        'body'   => $response->json(),
    ]);

    return $response->json();
}

    public function sendMedia(string $phone, string $mediaType, string $mediaUrl, ?string $caption = null, ?string $filename = null): array
    {
        $payload = [
            'phone'     => $phone,
            'mediaType' => $mediaType,   // image, video, document
            'mediaUrl'  => $mediaUrl,
        ];

        if ($caption) {
            $payload['caption'] = $caption;
        }
        if ($filename) {
            $payload['filename'] = $filename;
        }

        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl.'/send/media', $payload);

        return $response->json();
    }

    public function sendTemplate(string $phone, string $templateName, string $languageCode, array $components): array
    {
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl.'/send/template', [
                'phone'    => $phone,
                'template' => [
                    'name'     => $templateName,
                    'language' => ['code' => $languageCode],
                ],
                'components' => $components,
            ]);

        return $response->json();
    }
}
