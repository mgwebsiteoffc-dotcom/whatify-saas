<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Services\WhatifyService;
use Illuminate\Http\Request;

class WhatsappSettingsController extends Controller
{
    public function edit(Company $company)
    {
        return view('settings.whatsapp', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $data = $request->validate([
            'whatify_token'  => 'required|string',
            'business_phone' => 'required|string',
        ]);

        $company->update($data);

        return back()->with('message', 'WhatsApp settings saved.');
    }

  public function test(Request $request, Company $company)
{
    $data = $request->validate([
        'test_phone' => 'required|string',
    ]);

    if (! $company->whatify_token) {
        return back()->with('error', 'Save Whatify token first.');
    }

    $service = new WhatifyService($company);
    $result  = $service->sendText($data['test_phone'], 'Test from WhatsApp Whatify settings.');

    if (($result['data']['success'] ?? false) === true) {
        return back()->with('message', 'Test message sent successfully.');
    }

    // Show exact error from Whatify
    $msg = $result['data']['error']['message'] ?? json_encode($result);
    return back()->with('error', 'Test failed: '.$msg);
}

}
