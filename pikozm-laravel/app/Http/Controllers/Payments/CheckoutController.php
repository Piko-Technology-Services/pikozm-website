<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Services\DpoPay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Collect customer details, create the order and hand over to DPO Pay.
 * Replaces payments/checkout.php and payments/process-payment.php.
 */
class CheckoutController extends Controller
{
    /** GET /payments/checkout/{product} – a catalog item. */
    public function product(Request $request, Product $product): View|RedirectResponse
    {
        abort_unless($product->is_active, 404);

        $amount = (float) $product->price;

        if ($product->isCustomPriced()) {
            $amount = (float) $request->query('amount', 0);

            if ($amount <= 0) {
                return redirect()->route('payments.index');
            }
        }

        return view('payments.checkout', [
            'mode' => 'catalog',
            'product' => $product,
            'item' => [
                'name' => $product->name,
                'description' => $product->description,
                'amount' => $amount,
            ],
        ]);
    }

    /** POST /payments/checkout – the "Paying for something else?" panel. */
    public function custom(Request $request): View
    {
        $data = $request->validate([
            'item_name' => ['required', 'string', 'max:120'],
            'item_description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['required', 'numeric', 'min:1', 'max:10000000'],
        ]);

        return view('payments.checkout', [
            'mode' => 'custom',
            'product' => null,
            'item' => [
                'name' => $data['item_name'],
                'description' => ($data['item_description'] ?? null) ?: 'Custom payment',
                'amount' => (float) $data['amount'],
            ],
        ]);
    }

    /**
     * GET /payments/checkout – nothing to show without a POSTed item (e.g. after a
     * validation error on a custom payment); go back to the catalog with errors kept.
     */
    public function restart(): RedirectResponse
    {
        session()->reflash();

        return redirect()->to(route('payments.index').'#custom-payment');
    }

    /** POST /payments/checkout/process – create the DPO token and redirect. */
    public function process(Request $request, DpoPay $dpo): RedirectResponse|Response
    {
        $data = $request->validate([
            'mode' => ['required', Rule::in(['catalog', 'custom'])],
            'product' => ['required_if:mode,catalog', 'nullable', 'string'],
            'item_name' => ['required_if:mode,custom', 'nullable', 'string', 'max:120'],
            'item_description' => ['nullable', 'string', 'max:1000'],
            'amount' => ['nullable', 'numeric', 'min:1', 'max:10000000'],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($data['mode'] === 'catalog') {
            $product = Product::active()->where('slug', $data['product'])->firstOrFail();
            // Never trust a posted price for fixed-price items.
            $amount = $product->isCustomPriced() ? (float) ($data['amount'] ?? 0) : (float) $product->price;
            $productName = $product->name;
            $description = $product->description;
            $serviceType = $product->service_type;
        } else {
            $product = null;
            $amount = (float) ($data['amount'] ?? 0);
            $productName = $data['item_name'];
            $description = $data['item_description'] ?? null;
            $serviceType = (string) config('dpo.default_service_type');
        }

        if ($amount <= 0) {
            return $this->failure('Invalid payment amount.', 422);
        }

        $order = Order::create([
            'company_ref' => Order::generateCompanyRef(),
            'product_id' => $product?->id,
            'product_name' => $productName,
            'description' => $description,
            'amount' => $amount,
            'currency' => config('dpo.currency'),
            'service_type' => $serviceType,
            'customer_name' => $data['name'],
            'customer_email' => $data['email'],
            'customer_phone' => $data['phone'],
            'notes' => $data['notes'] ?? null,
            'status' => Order::STATUS_PENDING,
        ]);

        $result = $dpo->createToken([
            'amount' => $amount,
            'company_ref' => $order->company_ref,
            'service_type' => $serviceType,
            'service_description' => $productName,
        ]);

        if (! $result['success']) {
            $order->markFailed($result['message'] ?? 'Unknown error');

            return $this->failure(
                'We could not start your payment right now: '.($result['message'] ?? 'Unknown error').'. Please try again shortly or contact us.',
                502,
            );
        }

        $order->markTokenCreated($result['token'], $result['trans_ref'] ?? null);

        return redirect()->away($dpo->paymentUrl($result['token']));
    }

    private function failure(string $message, int $status): Response
    {
        return response()->view('payments.result', [
            'verified' => false,
            'title' => 'Payment could not be started',
            'message' => $message,
            'order' => null,
        ], $status);
    }
}
