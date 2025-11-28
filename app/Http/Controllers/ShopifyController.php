<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ShopifyController extends Controller
{
    public function install(Request $request)
    {
        $shop = $request->query('shop');
        if (! $shop) {
            abort(400, 'Missing shop parameter.');
        }

        $apiKey   = config('shopify.api_key');
        $scopes   = config('shopify.scopes');
        $redirect = config('shopify.app_url').'/shopify/callback';
        $state    = bin2hex(random_bytes(8));

        $request->session()->put('shopify_oauth_state', $state);
        $request->session()->put('shopify_oauth_shop', $shop);

        $installUrl = "https://{$shop}/admin/oauth/authorize?".http_build_query([
            'client_id'    => $apiKey,
            'scope'        => $scopes,
            'redirect_uri' => $redirect,
            'state'        => $state,
        ]);

        return redirect()->away($installUrl);
    }

    public function callback(Request $request)
    {
        $shop  = $request->query('shop');
        $code  = $request->query('code');
        $hmac  = $request->query('hmac');
        $state = $request->query('state');

        if (! $shop || ! $code || ! $hmac || ! $state) {
            abort(400, 'Missing OAuth parameters.');
        }

        if ($state !== $request->session()->pull('shopify_oauth_state')) {
            abort(400, 'Invalid state.');
        }

        // Verify HMAC
        $query = $request->query();
        unset($query['hmac']);
        ksort($query);

        $data      = urldecode(http_build_query($query));
        $calculated= hash_hmac('sha256', $data, config('shopify.secret'));

        if (! hash_equals($hmac, $calculated)) {
            abort(400, 'HMAC validation failed.');
        }

        // Exchange code for access token
        $tokenResp = Http::post("https://{$shop}/admin/oauth/access_token", [
            'client_id'     => config('shopify.api_key'),
            'client_secret' => config('shopify.secret'),
            'code'          => $code,
        ])->json();

        $accessToken = $tokenResp['access_token'] ?? null;
        if (! $accessToken) {
            abort(500, 'Failed to get access token.');
        }

        // Upsert company (tenant)
        $company = Company::updateOrCreate(
            ['shopify_domain' => $shop],
            [
                'name'                 => $shop,
                'shopify_access_token' => $accessToken,
                'is_active'            => true,
            ]
        );

        // Register webhooks
        $this->registerWebhooks($company);

        // Set current company in session
        $request->session()->put('current_company_id', $company->id);

        return redirect('/app');
    }

    protected function registerWebhooks(Company $company): void
    {
        $token      = $company->shopify_access_token;
        $shop       = $company->shopify_domain;
        $apiVersion = config('shopify.api_version');
        $baseUrl    = "https://{$shop}/admin/api/{$apiVersion}";

        $addressBase = config('shopify.app_url');

        $webhooks = [
            [
                'topic'   => 'orders/create',
                'address' => $addressBase.'/webhooks/orders-create',
            ],
        ];

        foreach ($webhooks as $wh) {
            Http::withHeaders([
                'X-Shopify-Access-Token' => $token,
            ])->post("{$baseUrl}/webhooks.json", [
                'webhook' => [
                    'topic'   => $wh['topic'],
                    'address' => $wh['address'],
                    'format'  => 'json',
                ],
            ]);
        }
    }
}
