<?php

namespace App\Http\Controllers\Zampayroll;

use App\Models\ZampayrollSubscription;
use App\Services\DpoPay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Checkout for a ZamPayroll plan: customer details, then card (DPO hosted page),
 * mobile money (in-page, see MobileMoneyController) or manual payment.
 */
class CheckoutController extends ZampayrollController
{
    public function show(Request $request, string $plan): View
    {
        $plan = $this->planOrFail($plan);
        $billing = $this->billing($request->query('billing'));

        return view('zampayroll.checkout', [
            'plan' => $plan,
            'billing' => $billing,
            'amount' => $this->amountFor($plan, $billing),
            'networks' => config('zampayroll.mobile_networks'),
            'timeout' => config('zampayroll.mobile_money_timeout_seconds'),
        ]);
    }

    /** Card and manual payments (mobile money is handled in-page via JSON). */
    public function store(Request $request, string $plan, DpoPay $dpo): RedirectResponse|Response
    {
        $plan = $this->planOrFail($plan);
        $data = $this->validateCustomer($request, $plan, [
            'payment_method' => ['required', Rule::in([ZampayrollSubscription::METHOD_CARD, ZampayrollSubscription::METHOD_MANUAL, ZampayrollSubscription::METHOD_MOBILE])],
        ]);

        if ($data['payment_method'] === ZampayrollSubscription::METHOD_MOBILE) {
            // Only reachable without JavaScript; the mobile money steps run in the page.
            return back()->withInput()->withErrors(['payment_method' => 'Mobile money runs inside this page and needs JavaScript. Turn it on, or pay by card.']);
        }

        $billing = $this->billing($data['billing'] ?? null);

        $subscription = ZampayrollSubscription::create([
            'reference' => ZampayrollSubscription::generateReference(),
            'plan' => $plan['key'],
            'plan_name' => $plan['name'],
            'billing_cycle' => $billing,
            'amount' => $this->amountFor($plan, $billing),
            'currency' => config('zampayroll.currency'),
            'company_name' => $data['company_name'],
            'contact_name' => $data['contact_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'employees' => $data['employees'],
            'payment_method' => $data['payment_method'],
            'status' => ZampayrollSubscription::STATUS_PENDING,
        ]);
        $this->rememberReference($subscription);

        if ($subscription->payment_method === ZampayrollSubscription::METHOD_MANUAL) {
            $subscription->update(['status' => ZampayrollSubscription::STATUS_MANUAL]);
            $subscription->notifyAdmin(ZampayrollSubscription::STATUS_MANUAL);

            return redirect()->away(config('zampayroll.login_url'));
        }

        // Card → DPO hosted checkout
        $subscription->update(['attempts' => 1]);

        $result = $dpo->createToken([
            'amount' => $subscription->amount,
            'company_ref' => $subscription->reference,
            'service_type' => config('zampayroll.dpo_service_type'),
            'service_description' => $this->serviceDescription($subscription),
            'redirect_url' => $dpo->callbackUrl('zampayroll.payment.complete'),
            'back_url' => $dpo->callbackUrl('zampayroll.payment.cancelled'),
            'customer' => $this->dpoCustomer($subscription),
        ]);

        if (! $result['success']) {
            $subscription->markFailed('Could not start card payment: '.($result['message'] ?? 'Unknown error'));

            return response()->view('zampayroll.result', [
                'subscription' => $subscription,
                'outcome' => 'failed',
                // The technical reason goes to the admin email; the customer gets a plain next step.
                'message' => 'We couldn’t open the secure card page just now. Please try again in a few minutes, or pay with mobile money.',
            ], 502);
        }

        $subscription->update([
            'status' => ZampayrollSubscription::STATUS_AWAITING,
            'dpo_token' => $result['token'],
            'dpo_trans_ref' => $result['trans_ref'] ?? null,
        ]);

        return redirect()->away($dpo->paymentUrl($result['token']));
    }
}
