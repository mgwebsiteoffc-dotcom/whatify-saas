<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetCurrentCompany
{
    public function handle(Request $request, Closure $next): Response
    {
        $companyId = $request->session()->get('current_company_id');
        $company   = $companyId ? Company::find($companyId) : null;

        if (! $company && $request->has('shop')) {
            $company = Company::where('shopify_domain', $request->query('shop'))->first();
            if ($company) {
                $request->session()->put('current_company_id', $company->id);
            }
        }

        if ($company) {
            // Share with all views
            view()->share('currentCompany', $company);
        }

        return $next($request);
    }
}
