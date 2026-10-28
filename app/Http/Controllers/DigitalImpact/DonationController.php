<?php

namespace App\Http\Controllers\DigitalImpact;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Services\LencoPay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * "Support the Cause" donations via Lenco – previously
 * app.pikozm.com/api/support/initiate and /api/support/processing.
 */
class DonationController extends Controller
{
    public function __construct(private readonly LencoPay $lenco)
    {
    }

    public function initiate(Request $request): JsonResponse
    {
        if (! $this->lenco->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'Online payments are not available right now. Please contact us.'], 503);
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'focus_area' => ['nullable', Rule::in(array_keys(Donation::FOCUS_AREAS))],
            'amount' => ['required', 'numeric', 'min:1', 'max:1000000'],
            'currency' => ['required', Rule::in(config('lenco.currencies.donations'))],
            'message' => ['nullable', 'string', 'max:5000'],
        ]);

        $donation = Donation::create($data + [
            'focus_area' => $data['focus_area'] ?? 'general',
            'reference' => Donation::generateReference(Donation::REFERENCE_PREFIX),
            'status' => Donation::STATUS_PENDING,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'public_key' => $this->lenco->publicKey(),
                'reference' => $donation->reference,
                'channels' => $this->lenco->channels(),
                'donation' => $donation->only(['name', 'email', 'amount', 'currency']),
            ],
        ], 201);
    }

    /**
     * Called by the browser after the Lenco widget reports success.
     * The status is confirmed with Lenco server-side – the browser is not trusted.
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate(['reference' => ['required', 'string', 'max:100']]);

        $donation = Donation::where('reference', $request->string('reference'))->firstOrFail();

        if ($result = $this->lenco->collectionStatus($donation->reference)) {
            $donation->applyGatewayStatus($result['status'], $result['data']);
        } elseif ($donation->status === Donation::STATUS_PENDING) {
            $donation->update(['status' => Donation::STATUS_PROCESSING]);
        }

        return response()->json(['success' => true, 'status' => $donation->status]);
    }
}
