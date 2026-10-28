<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * 301-redirects URLs from the old static site (index.html, payments/*.php …)
 * to their Laravel routes, preserving the query string – important for the
 * DPO callback URLs that may still be registered with DPO.
 */
class LegacyRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $route = $request->route()->defaults['to'];

        // payments/checkout.php?product=slug[&amount=x]
        if ($route === 'payments.checkout.product') {
            if (! $request->filled('product')) {
                return redirect()->route('payments.index', status: 301);
            }

            return redirect()->route($route, ['product' => $request->query('product')] + $request->only('amount'), 301);
        }

        $query = $request->getQueryString();

        return redirect()->to(route($route).($query ? '?'.$query : ''), 301);
    }
}
