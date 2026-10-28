<?php

namespace App\Http\Controllers\Zampayroll;

use App\Http\Controllers\Controller;
use App\Models\ZampayrollSubscription;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Shared helpers for the ZamPayroll subscription controllers.
 */
abstract class ZampayrollController extends Controller
{
    /** Plans that can be bought online (have a monthly price above zero). */
    protected function payablePlans(): array
    {
        return array_filter(config('zampayroll.plans'), fn ($p) => ($p['monthly'] ?? 0) > 0);
    }

    protected function planOrFail(string $key): array
    {
        $plans = $this->payablePlans();
        abort_unless(isset($plans[$key]), 404);

        return $plans[$key] + ['key' => $key];
    }

    protected function billing(?string $value): string
    {
        return $value === 'annual' ? 'annual' : 'monthly';
    }

    protected function amountFor(array $plan, string $billing): float
    {
        return (float) $plan[$billing];
    }

    /**
     * Validate the customer details step. Employees may be below the plan's band
     * (paying for headroom is fine) but not above it.
     */
    protected function validateCustomer(Request $request, array $plan, array $extra = []): array
    {
        $validator = validator($request->all(), [
            'company_name' => ['required', 'string', 'max:160'],
            'contact_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:40'],
            'employees' => ['required', 'integer', 'min:1', 'max:100000'],
            'billing' => ['nullable', Rule::in(['monthly', 'annual'])],
        ] + $extra, [], [
            'company_name' => 'company name',
            'contact_name' => 'your name',
            'employees' => 'number of employees',
        ]);

        $validator->after(function (Validator $v) use ($request, $plan) {
            $employees = (int) $request->input('employees');
            if ($plan['max_employees'] && $employees > $plan['max_employees']) {
                $better = $this->planForHeadcount($employees);
                $v->errors()->add('employees', "{$plan['name']} covers up to {$plan['max_employees']} active employees. For {$employees} employees, choose {$better['name']}.");
            }
        });

        return $validator->validate();
    }

    protected function planForHeadcount(int $employees): array
    {
        foreach (config('zampayroll.plans') as $key => $plan) {
            if ($employees >= $plan['min_employees'] && ($plan['max_employees'] === null || $employees <= $plan['max_employees'])) {
                return $plan + ['key' => $key];
            }
        }

        return config('zampayroll.plans.enterprise') + ['key' => 'enterprise'];
    }

    /** References created in this browser session – only these can be polled or retried. */
    protected function rememberReference(ZampayrollSubscription $subscription): void
    {
        $refs = session('zampayroll.references', []);
        $refs[] = $subscription->reference;
        session(['zampayroll.references' => array_values(array_unique(array_slice($refs, -20)))]);
    }

    protected function ownsReference(string $reference): bool
    {
        return in_array($reference, session('zampayroll.references', []), true);
    }

    /** Customer fields that pre-fill DPO's hosted checkout. */
    protected function dpoCustomer(ZampayrollSubscription $s): array
    {
        [$first, $last] = array_pad(explode(' ', trim($s->contact_name), 2), 2, '');

        return [
            'first_name' => $first,
            'last_name' => $last,
            'email' => $s->email,
            'phone' => preg_replace('/\D+/', '', $s->phone),
            'dial_code' => '260',
            'country' => 'Zambia',
        ];
    }

    protected function serviceDescription(ZampayrollSubscription $s): string
    {
        // Plain ASCII only: unusual characters in the body can trip DPO's firewall.
        $company = trim(preg_replace('/\s+/', ' ', preg_replace('/[^A-Za-z0-9 .,&()\'-]+/', ' ', $s->company_name)));

        return "ZamPayroll {$s->plan_name} subscription ({$s->billingLabel()}) - {$company}";
    }
}
