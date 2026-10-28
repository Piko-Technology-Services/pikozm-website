<?php

namespace App\Http\Controllers\Zampayroll;

use App\Models\ZampayrollSubscription;
use App\Services\DpoPay;
use App\Support\ZambianPhone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * In-page mobile money: send a payment prompt to the customer's phone through
 * DPO (ChargeTokenMobile), then poll until it is approved, declined or expires.
 * A retry creates a fresh DPO token on the same subscription.
 */
class MobileMoneyController extends ZampayrollController
{
    public function start(Request $request, DpoPay $dpo): JsonResponse
    {
        $request->validate(['plan' => ['required', Rule::in(array_keys($this->payablePlans()))]]);
        $plan = $this->planOrFail($request->input('plan'));

        $data = $this->validateCustomer($request, $plan, [
            'network' => ['required', Rule::in(array_keys(config('zampayroll.mobile_networks')))],
            'mobile_number' => ['required', 'string', 'max:20'],
            'reference' => ['nullable', 'string', 'max:40'],
        ]);

        $msisdn = ZambianPhone::normalize($data['mobile_number']);
        if (! $msisdn) {
            throw ValidationException::withMessages(['mobile_number' => 'Enter a Zambian mobile number, for example 097 123 4567.']);
        }

        $billing = $this->billing($data['billing'] ?? null);
        $subscription = $this->subscriptionFor($data, $plan, $billing);

        if ($subscription->isPaid()) {
            return response()->json($this->payload($subscription, 'paid'));
        }

        $subscription->fill([
            'payment_method' => ZampayrollSubscription::METHOD_MOBILE,
            'mobile_network' => $data['network'],
            'mobile_number' => $msisdn,
            'attempts' => $subscription->attempts + 1,
            'status' => ZampayrollSubscription::STATUS_PENDING,
            'last_result_code' => null,
            'last_message' => null,
        ])->save();

        // 1. Fresh DPO token for every attempt
        $token = $dpo->createToken([
            'amount' => $subscription->amount,
            'company_ref' => $subscription->reference,
            'service_type' => config('zampayroll.dpo_service_type'),
            'service_description' => $this->serviceDescription($subscription),
            'redirect_url' => route('zampayroll.payment.complete'),
            'back_url' => route('zampayroll.payment.cancelled'),
            'customer' => $this->dpoCustomer($subscription),
        ]);

        if (! $token['success']) {
            return $this->failed(
                $subscription,
                'Could not start mobile money payment: '.($token['message'] ?? 'unknown error'),
                customerMessage: 'We couldn’t connect to the payment service just now. Please try again in a few minutes, or pay by card.',
            );
        }

        $subscription->update(['dpo_token' => $token['token'], 'dpo_trans_ref' => $token['trans_ref'] ?? null]);

        // 2. Match the chosen network to DPO's mobile options for this transaction
        $network = config("zampayroll.mobile_networks.{$data['network']}");
        $mno = $network['dpo_name'];
        $country = config('zampayroll.mobile_country');

        $options = $dpo->mobilePaymentOptions($token['token']);
        if ($options !== []) {
            $match = collect($options)->first(fn ($o) => str_contains(strtolower($o['name']), strtolower($network['dpo_name']))
                && ($o['country'] === '' || str_contains(strtolower($o['country']), $country)));

            if (! $match) {
                return $this->failed($subscription, "{$network['label']} isn’t available for this payment right now. Choose another network or pay by card.");
            }

            $mno = $match['name'];
            $country = $match['country'] ?: $country;
        }

        // 3. Send the prompt to the phone
        $charge = $dpo->chargeMobile($token['token'], $msisdn, $mno, $country);

        if (! $charge['success']) {
            return $this->failed($subscription, $charge['message'] ?: 'The payment prompt could not be sent to your phone.', $charge['code']);
        }

        $subscription->update([
            'status' => ZampayrollSubscription::STATUS_AWAITING,
            'last_result_code' => $charge['code'],
            'last_message' => 'Payment prompt sent to '.ZambianPhone::display($msisdn).'.',
        ]);

        return response()->json($this->payload($subscription, 'awaiting', [
            'instructions' => $charge['instructions'],
        ]));
    }

    /** Polled by the browser every few seconds while the customer approves on their phone. */
    public function status(ZampayrollSubscription $subscription, DpoPay $dpo): JsonResponse
    {
        abort_unless($this->ownsReference($subscription->reference), 404);

        if (! $subscription->isPaid() && $subscription->dpo_token
            && $subscription->status === ZampayrollSubscription::STATUS_AWAITING) {
            $result = $dpo->verifyToken($subscription->dpo_token);

            if ($result['result_code'] !== '') {
                $subscription->applyDpoResult($result['result_code'], $result['message'] ?: null);
            }
        }

        $state = match ($subscription->status) {
            ZampayrollSubscription::STATUS_PAID => 'paid',
            ZampayrollSubscription::STATUS_AWAITING, ZampayrollSubscription::STATUS_PENDING => 'awaiting',
            default => 'failed',
        };

        return response()->json($this->payload($subscription, $state));
    }

    private function subscriptionFor(array $data, array $plan, string $billing): ZampayrollSubscription
    {
        $details = [
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
        ];

        $reference = $data['reference'] ?? null;
        if ($reference && $this->ownsReference($reference)
            && $existing = ZampayrollSubscription::where('reference', $reference)->first()) {
            if (! $existing->isPaid()) {
                $existing->fill($details);
            }

            return $existing;
        }

        $subscription = new ZampayrollSubscription($details + [
            'reference' => ZampayrollSubscription::generateReference(),
            'payment_method' => ZampayrollSubscription::METHOD_MOBILE,
            'status' => ZampayrollSubscription::STATUS_PENDING,
        ]);
        $subscription->save();
        $this->rememberReference($subscription);

        return $subscription;
    }

    /** $message is recorded and emailed to the admin; $customerMessage (if given) is what the customer sees. */
    private function failed(ZampayrollSubscription $subscription, string $message, ?string $code = null, ?string $customerMessage = null): JsonResponse
    {
        $subscription->markFailed($message, $code);

        $payload = $this->payload($subscription, 'failed');
        if ($customerMessage) {
            $payload['message'] = $customerMessage;
        }

        return response()->json($payload, 422);
    }

    private function payload(ZampayrollSubscription $s, string $state, array $extra = []): array
    {
        return [
            'state' => $state,
            'reference' => $s->reference,
            'message' => $s->last_message,
            'code' => $s->last_result_code,
            'attempt' => $s->attempts,
            'status_url' => route('zampayroll.mobile.status', $s),
            'receipt' => $state === 'paid' ? [
                'plan' => $s->plan_name.' · '.$s->billingLabel(),
                'amount' => $s->formattedAmount(),
                'paid_at' => $s->paid_at?->timezone('Africa/Lusaka')->format('j M Y, H:i'),
                'login_url' => config('zampayroll.login_url'),
            ] : null,
        ] + $extra;
    }
}
