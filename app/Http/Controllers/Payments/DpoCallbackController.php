<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DpoPay;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Where DPO Pay sends the customer back to.
 * Replaces payments/payment-success.php and payments/payment-cancel.php.
 */
class DpoCallbackController extends Controller
{
    public function success(Request $request, DpoPay $dpo): View
    {
        $token = $this->tokenFrom($request);
        $order = $this->findOrder($request, $token);
        $token ??= $order?->dpo_token;

        $verified = false;
        $message = 'We could not find a payment reference to verify. If you completed a payment, please contact us with your details.';

        if ($token) {
            $result = $dpo->verifyToken($token);
            $verified = $result['success'];
            $message = $result['message'] ?: ($verified ? 'Payment confirmed successfully.' : 'Payment was not completed.');

            $order?->update([
                'status' => $verified ? Order::STATUS_PAID : Order::STATUS_UNPAID,
                'verify_message' => $message,
                'verified_at' => now(),
            ]);
        }

        return view('payments.result', [
            'verified' => $verified,
            'title' => $verified ? 'Payment successful' : 'Payment not completed',
            'message' => $message,
            'order' => $order,
        ]);
    }

    public function cancel(Request $request): View
    {
        $order = $this->findOrder($request, $this->tokenFrom($request));

        if ($order && ! $order->isPaid()) {
            $order->update(['status' => Order::STATUS_CANCELLED]);
        }

        return view('payments.cancel', ['order' => $order]);
    }

    /** DPO has used several parameter names over time. */
    private function tokenFrom(Request $request): ?string
    {
        foreach (['TransactionToken', 'TransID', 'TransToken', 'ID'] as $key) {
            if (filled($request->query($key))) {
                return (string) $request->query($key);
            }
        }

        return null;
    }

    private function findOrder(Request $request, ?string $token): ?Order
    {
        if ($token && $order = Order::where('dpo_token', $token)->first()) {
            return $order;
        }

        $ref = $request->query('CompanyRef');

        return $ref ? Order::where('company_ref', $ref)->first() : null;
    }
}
