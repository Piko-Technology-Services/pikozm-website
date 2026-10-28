<?php

namespace App\Http\Controllers\Zampayroll;

use App\Models\ZampayrollSubscription;
use App\Services\DpoPay;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Where DPO's hosted card checkout sends the customer back to.
 */
class PaymentReturnController extends ZampayrollController
{
    public function complete(Request $request, DpoPay $dpo): View
    {
        $subscription = $this->find($request);

        if (! $subscription) {
            return view('zampayroll.result', [
                'subscription' => null,
                'outcome' => 'unknown',
                'message' => 'We couldn’t match this payment to a ZamPayroll subscription. If money left your account, email '.config('zampayroll.admin_email').' with your bank reference.',
            ]);
        }

        if (! $subscription->isPaid() && $subscription->dpo_token) {
            $result = $dpo->verifyToken($subscription->dpo_token);
            $subscription->applyDpoResult($result['result_code'] ?: 'ERR', $result['message'] ?: null);
        }

        return view('zampayroll.result', [
            'subscription' => $subscription->fresh(),
            'outcome' => $subscription->isPaid() ? 'paid' : ($subscription->status === ZampayrollSubscription::STATUS_AWAITING ? 'awaiting' : 'failed'),
            'message' => $subscription->last_message,
        ]);
    }

    public function cancelled(Request $request): View
    {
        $subscription = $this->find($request);

        if ($subscription && ! $subscription->isPaid()) {
            $subscription->applyDpoResult('904', 'You cancelled the payment on the secure card page.');
        }

        return view('zampayroll.result', [
            'subscription' => $subscription,
            'outcome' => $subscription?->isPaid() ? 'paid' : 'cancelled',
            'message' => 'No money was taken. You can choose a payment method again.',
        ]);
    }

    private function find(Request $request): ?ZampayrollSubscription
    {
        foreach (['TransactionToken', 'TransID', 'TransToken', 'ID'] as $key) {
            if ($token = $request->query($key)) {
                if ($s = ZampayrollSubscription::where('dpo_token', $token)->first()) {
                    return $s;
                }
            }
        }

        $ref = $request->query('CompanyRef');

        return $ref ? ZampayrollSubscription::where('reference', $ref)->first() : null;
    }
}
