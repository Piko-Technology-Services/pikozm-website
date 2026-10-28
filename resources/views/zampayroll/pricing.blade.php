@extends('layouts.zampayroll')

@section('title', 'Pay for ZamPayroll – choose your plan')

@php
    $paid = array_filter($plans, fn ($p) => ($p['monthly'] ?? 0) > 0);
@endphp

@section('content')
    <div class="zp-wrap zp-pay-page">
        <header class="zp-pay-page__head">
            <h1>Pay for your ZamPayroll subscription</h1>
            <p class="zp-lede">Choose your plan, then pay by mobile money, card or invoice. Prices are in Zambian Kwacha.</p>
        </header>

        {{-- How to pay: a real sequence, so it is numbered --}}
        <ol class="zp-howto" aria-label="How to pay">
            <li class="is-current">
                <strong>Choose your plan</strong>
                <span>Pick the plan for your number of active employees, billed monthly or annually.</span>
            </li>
            <li>
                <strong>Enter your details</strong>
                <span>Company name, your name and the email for your receipt.</span>
            </li>
            <li>
                <strong>Choose how to pay</strong>
                <span>Mobile money prompt on your phone, Visa or Mastercard, or a manual invoice.</span>
            </li>
            <li>
                <strong>Get confirmed</strong>
                <span>Confirmation appears on screen and by email, and your plan is activated.</span>
            </li>
        </ol>

        <section class="zp-plans" aria-labelledby="plans-title">
            <div class="zp-plans__head">
                <h2 id="plans-title">Choose your plan</h2>
                <div class="zp-toggle" role="radiogroup" aria-label="Billing period">
                    <a role="radio" href="?billing=monthly" data-billing="monthly" aria-checked="{{ $billing === 'monthly' ? 'true' : 'false' }}">Monthly</a>
                    <a role="radio" href="?billing=annual" data-billing="annual" aria-checked="{{ $billing === 'annual' ? 'true' : 'false' }}">Annually <small>2 months free</small></a>
                </div>
            </div>

            <div class="zp-plan-grid" data-billing="{{ $billing }}">
                @foreach ($paid as $key => $plan)
                    <article @class(['zp-plan', 'zp-plan--popular' => $plan['popular'] ?? false]) aria-labelledby="plan-{{ $key }}">
                        <div class="zp-plan__top">
                            <h3 id="plan-{{ $key }}">{{ $plan['name'] }}</h3>
                            @isset($plan['tag'])<p class="zp-plan__tag">{{ $plan['tag'] }}</p>@endisset
                        </div>
                        <p class="zp-plan__band">{{ $plan['employees'] }}</p>

                        <div class="zp-plan__price">
                            <p class="zp-price" data-show="monthly"><span>K{{ number_format($plan['monthly']) }}</span> / month</p>
                            <p class="zp-price" data-show="annual"><span>K{{ number_format($plan['annual']) }}</span> / year</p>
                            <p class="zp-plan__alt" data-show="monthly">or K{{ number_format($plan['annual']) }} a year · 2 months free</p>
                            <p class="zp-plan__alt" data-show="annual">Save K{{ number_format($plan['monthly'] * 12 - $plan['annual']) }} vs monthly</p>
                        </div>

                        <p class="zp-plan__summary">{{ $plan['summary'] }}</p>

                        <a class="zp-btn zp-btn--block {{ ($plan['popular'] ?? false) ? 'zp-btn--orange' : '' }}"
                            href="{{ route('zampayroll.checkout', ['plan' => $key, 'billing' => $billing]) }}"
                            data-checkout="{{ route('zampayroll.checkout', ['plan' => $key]) }}"
                            aria-label="Pay for the {{ $plan['name'] }} plan">Pay for this plan</a>
                    </article>
                @endforeach
            </div>

            {{-- Plans that are not paid online --}}
            <div class="zp-other-plans">
                <p>
                    <strong>{{ $plans['free']['name'] }}</strong> · {{ $plans['free']['employees'] }} ·
                    K0 for 2 months, then a paid plan from month three.
                    <a href="{{ $plans['free']['cta']['url'] }}">Start free</a>
                </p>
                <p>
                    <strong>{{ $plans['enterprise']['name'] }}</strong> · {{ $plans['enterprise']['employees'] }} ·
                    Tailored pricing, agreed with our team.
                    <a href="{{ $plans['enterprise']['cta']['url'] }}">Talk to our team</a>
                </p>
            </div>

            <p class="zp-plans__note">Every paid plan includes the complete ZamPayroll platform and standard support. You only pay more as your workforce grows.</p>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        // Switch monthly/annual prices in place and keep the checkout links in sync.
        (function () {
            const grid = document.querySelector('.zp-plan-grid');
            document.querySelectorAll('.zp-toggle [data-billing]').forEach(function (opt) {
                opt.addEventListener('click', function (e) {
                    e.preventDefault();
                    const billing = opt.dataset.billing;
                    grid.dataset.billing = billing;
                    document.querySelectorAll('.zp-toggle [data-billing]').forEach(o => o.setAttribute('aria-checked', String(o === opt)));
                    document.querySelectorAll('[data-checkout]').forEach(a => a.href = a.dataset.checkout + '?billing=' + billing);
                    history.replaceState(null, '', '?billing=' + billing);
                });
            });
        })();
    </script>
@endpush
