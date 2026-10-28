<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\View\View;

/**
 * The /payments dashboard (formerly payments/index.php).
 */
class CatalogController extends Controller
{
    public function __invoke(): View
    {
        $products = Product::active()->get();

        return view('payments.index', [
            'subscriptions' => $products->where('category', Product::CATEGORY_SUBSCRIPTION),
            'oneTime' => $products->where('category', Product::CATEGORY_ONE_TIME),
        ]);
    }
}
