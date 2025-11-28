<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\WhatsappAutomation;
use Illuminate\Http\Request;

class AutomationController extends Controller
{
    public function index(Company $company)
    {
        $automations = $company->automations()->latest()->get();

        return view('automations.index', compact('company', 'automations'));
    }

   public function create(Company $company)
{
    $automation = null;
    return view('automations.builder', compact('company', 'automation'));
}

public function store(Request $request, Company $company)
{
    $data = $request->validate([
        'name'       => 'required|string|max:255',
        'trigger'    => 'required|string|in:cod_order,abandoned_cart,order_fulfilled',
        'definition' => 'required|string', // JSON from builder
    ]);

    $definition = json_decode($data['definition'], true);

    WhatsappAutomation::create([
        'company_id'      => $company->id,
        'name'            => $data['name'],
        'trigger'         => $data['trigger'],
        'is_active'       => true,
        'flow_definition' => $definition,
    ]);

    return redirect()->route('automations.index', $company)->with('message', 'Automation created.');
}

public function edit(Company $company, WhatsappAutomation $automation)
{
    return view('automations.builder', compact('company', 'automation'));
}

public function update(Request $request, Company $company, WhatsappAutomation $automation)
{
    $data = $request->validate([
        'name'       => 'required|string|max:255',
        'trigger'    => 'required|string|in:cod_order,abandoned_cart,order_fulfilled',
        'definition' => 'required|string',
    ]);

    $definition = json_decode($data['definition'], true);

    $automation->update([
        'name'            => $data['name'],
        'trigger'         => $data['trigger'],
        'flow_definition' => $definition,
    ]);

    return redirect()->route('automations.index', $company)->with('message', 'Automation updated.');
}
}
