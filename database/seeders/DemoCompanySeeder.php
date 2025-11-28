<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Company;

class DemoCompanySeeder extends Seeder
{
    public function run(): void
    {
        if (! Company::first()) {
            Company::create([
                'name'                => 'Demo Shopify Store',
                'shopify_domain'      => 'demo-store.myshopify.com',
                'shopify_access_token'=> 'demo-token',
                'whatify_token'       => null,
                'business_phone'      => null,
                'is_active'           => true,
            ]);
        }
    }
}
