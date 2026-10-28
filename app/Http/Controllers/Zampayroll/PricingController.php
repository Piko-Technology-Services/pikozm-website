<?php

namespace App\Http\Controllers\Zampayroll;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PricingController extends ZampayrollController
{
    public function __invoke(Request $request): View
    {
        return view('zampayroll.pricing', [
            'plans' => config('zampayroll.plans'),
            'billing' => $this->billing($request->query('billing')),
        ]);
    }
}
