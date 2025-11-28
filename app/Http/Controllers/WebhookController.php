<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessOrderWebhook;
use App\Models\Company;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function ordersCreate(Request $request)
    {
        if (! $this->verifyHmac($request)) {
            return response('Invalid HMAC', 401);
        }

        $shopDomain = $request->header('X-Shopify-Shop-Domain');
        $company    = Company::where('shopify_domain', $shopDomain)->first();

        if (! $company) {
            return response('Company not found', 404);
        }

        $payload = $request->json()->all();

        ProcessOrderWebhook::dispatch($company, $payload);

        return response('OK', 200);
    }

    protected function verifyHmac(Request $request): bool
    {
        $hmacHeader = $request->header('X-Shopify-Hmac-Sha256');
        $data       = $request->getContent();

        $calculated = base64_encode(hash_hmac('sha256', $data, config('shopify.secret'), true));

        return hash_equals($hmacHeader, $calculated);
    }
}
