<?php

namespace App\Http\Controllers;

use App\Models\ClientPayment;
use App\Services\LencoPay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * PikoPay – free-form client payments via Lenco (formerly payments.html,
 * backed by app.pikozm.com/api/payments/initiate and /processing).
 */
class PikoPayController extends Controller
{
    public function __construct(private readonly LencoPay $lenco)
    {
    }

    public function index(): View
    {
        return view('pikopay.index', ['currencies' => config('lenco.currencies.pikopay')]);
    }

    public function initiate(Request $request): JsonResponse
    {
        if (! $this->lenco->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'Online payments are not available right now. Please contact us.'], 503);
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'purpose' => ['required', 'string', 'max:190'],
            'amount' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'currency' => ['required', Rule::in(config('lenco.currencies.pikopay'))],
        ]);

        $payment = ClientPayment::create($data + [
            'reference' => ClientPayment::generateReference(ClientPayment::REFERENCE_PREFIX),
            'status' => ClientPayment::STATUS_PENDING,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'public_key' => $this->lenco->publicKey(),
                'reference' => $payment->reference,
                'channels' => $this->lenco->channels(),
                'payment' => $payment->only(['full_name', 'email', 'amount', 'currency']),
            ],
        ], 201);
    }

    public function verify(Request $request): JsonResponse
    {
        $request->validate(['reference' => ['required', 'string', 'max:100']]);

        $payment = ClientPayment::where('reference', $request->string('reference'))->firstOrFail();

        if ($result = $this->lenco->collectionStatus($payment->reference)) {
            $payment->applyGatewayStatus($result['status'], $result['data']);
        } elseif ($payment->status === ClientPayment::STATUS_PENDING) {
            $payment->update(['status' => ClientPayment::STATUS_PROCESSING]);
        }

        return response()->json(['success' => true, 'status' => $payment->status]);
    }
}
