@extends('layouts.zampayroll')

@section('title', "Pay for ZamPayroll {$plan['name']}")
@section('body-class', 'zp-body--checkout')

@php
    $otherBilling = $billing === 'annual' ? 'monthly' : 'annual';
    $old = fn ($k, $d = '') => old($k, $d);
@endphp

@section('content')
    <div class="zp-wrap zp-checkout"
        data-plan="{{ $plan['key'] }}"
        data-billing="{{ $billing }}"
        data-max-employees="{{ $plan['max_employees'] }}"
        data-plan-name="{{ $plan['name'] }}"
        data-mobile-url="{{ route('zampayroll.mobile.start') }}"
        data-timeout="{{ $timeout }}"
        data-login-url="{{ config('zampayroll.login_url') }}"
        data-pricing-url="{{ route('zampayroll.pricing') }}">

        <ol class="zp-stepper" data-stepper aria-label="Payment progress">
            <li class="is-done"><a href="{{ route('zampayroll.pricing', ['billing' => $billing]) }}">Choose plan</a></li>
            <li class="is-current" aria-current="step">Your details</li>
            <li>Payment</li>
            <li>Confirmation</li>
        </ol>

        <div class="zp-checkout__grid">
            <form class="zp-checkout__main" id="checkoutForm" method="POST"
                action="{{ route('zampayroll.checkout.store', ['plan' => $plan['key']]) }}">
                @csrf
                <input type="hidden" name="billing" value="{{ $billing }}">

                <h1>Pay for {{ $plan['name'] }}</h1>
                <p class="zp-lede">Enter your details, choose how to pay, and your plan is activated as soon as the payment is confirmed. <a href="{{ route('zampayroll.pricing', ['billing' => $billing]) }}">Change plan</a></p>

                @if ($errors->any())
                    <div class="zp-alert zp-alert--error" role="alert">
                        <strong>Check the highlighted details.</strong>
                        <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif

                {{-- Step 1 --}}
                <fieldset class="zp-step" id="step-details">
                    <legend><span class="zp-step__num">2</span> Your organisation</legend>

                    <div class="zp-fields">
                        <label class="zp-field zp-field--wide">
                            <span>Company name</span>
                            <input name="company_name" value="{{ $old('company_name') }}" required maxlength="160" autocomplete="organization">
                        </label>
                        <label class="zp-field">
                            <span>Your name</span>
                            <input name="contact_name" value="{{ $old('contact_name') }}" required maxlength="120" autocomplete="name">
                        </label>
                        <label class="zp-field">
                            <span>Work email</span>
                            <input type="email" name="email" value="{{ $old('email') }}" required autocomplete="email">
                            <small>Your receipt and sign-in details go here.</small>
                        </label>
                        <label class="zp-field">
                            <span>Phone</span>
                            <input type="tel" name="phone" value="{{ $old('phone') }}" required maxlength="40" autocomplete="tel" placeholder="097 123 4567">
                        </label>
                        <label class="zp-field">
                            <span>Active employees</span>
                            <input type="number" name="employees" value="{{ $old('employees') }}" required min="1" inputmode="numeric" data-employees>
                            <small data-employees-hint>{{ $plan['name'] }} covers {{ $plan['employees'] }}.</small>
                        </label>
                    </div>
                </fieldset>

                {{-- Step 2 --}}
                <fieldset class="zp-step" id="step-method">
                    <legend><span class="zp-step__num">3</span> How would you like to pay?</legend>

                    <div class="zp-methods" role="radiogroup">
                        <label class="zp-method">
                            <input type="radio" name="payment_method" value="mobile_money" @checked($old('payment_method', 'mobile_money') === 'mobile_money')>
                            <span class="zp-method__body">
                                <span class="zp-method__title">Mobile money</span>
                                <span class="zp-method__sub">Airtel Money, MTN MoMo or Zamtel Kwacha</span>
                                <span class="zp-method__next">We send a payment prompt to your phone. Approve it with your PIN and this page confirms the payment straight away. You don’t leave this page.</span>
                            </span>
                        </label>
                        <label class="zp-method">
                            <input type="radio" name="payment_method" value="card" @checked($old('payment_method') === 'card')>
                            <span class="zp-method__body">
                                <span class="zp-method__title">Card</span>
                                <span class="zp-method__sub">Visa or Mastercard</span>
                                <span class="zp-method__next">You’ll go to DPO Pay’s secure checkout to enter your card. When you finish, DPO brings you back here with your confirmation. Your card details never touch our servers.</span>
                            </span>
                        </label>
                        <label class="zp-method">
                            <input type="radio" name="payment_method" value="manual" @checked($old('payment_method') === 'manual')>
                            <span class="zp-method__body">
                                <span class="zp-method__title">Manual payment</span>
                                <span class="zp-method__sub">Bank transfer or invoice</span>
                                <span class="zp-method__next">We’ll note your order and take you to ZamPayroll sign-in, where you can arrange an invoice or bank transfer. Our billing team will also be in touch.</span>
                            </span>
                        </label>
                    </div>
                </fieldset>

                {{-- Step 3: depends on the method --}}
                <div class="zp-pay" data-panel="card" hidden>
                    <button type="submit" class="zp-btn zp-btn--block">Pay K{{ number_format($amount, 2) }} by card</button>
                    <p class="zp-fine">Secured by DPO Pay · PCI DSS certified. You’ll be redirected to complete payment.</p>
                </div>

                <div class="zp-pay" data-panel="manual" hidden>
                    <button type="submit" class="zp-btn zp-btn--block zp-btn--dark">Continue to ZamPayroll sign-in</button>
                    <p class="zp-fine">Your subscription activates once your manual payment is received.</p>
                </div>

                <div class="zp-pay zp-momo" data-panel="mobile_money" aria-live="polite">
                    {{-- Choose network + number --}}
                    <div class="zp-momo__screen" data-screen="enter">
                        <h2 class="zp-momo__title">Pay with mobile money</h2>
                        <div class="zp-networks" role="radiogroup" aria-label="Mobile network">
                            @foreach ($networks as $key => $network)
                                <label class="zp-network zp-network--{{ $key }}">
                                    <input type="radio" name="network" value="{{ $key }}" @checked($loop->first)>
                                    <span>{{ $network['label'] }}</span>
                                </label>
                            @endforeach
                        </div>
                        <label class="zp-field zp-field--momo">
                            <span>Mobile money number</span>
                            <input type="tel" name="mobile_number" inputmode="tel" autocomplete="tel" placeholder="097 123 4567" maxlength="16" data-momo-number>
                            <small data-network-hint>We’ll pick the network from your number.</small>
                        </label>
                        <button type="button" class="zp-btn zp-btn--block" data-momo-send>Send payment prompt</button>
                        <p class="zp-fine">Make sure your wallet has at least K{{ number_format($amount, 2) }} plus any network fee.</p>
                    </div>

                    {{-- Waiting for approval --}}
                    <div class="zp-momo__screen" data-screen="waiting" hidden>
                        <div class="zp-phone" aria-hidden="true">
                            <div class="zp-phone__ring"></div>
                            <div class="zp-phone__body"><span>PIN</span><i></i><i></i><i></i><i></i></div>
                        </div>
                        <h2 class="zp-momo__title">Check your phone</h2>
                        <p class="zp-momo__lead">We sent a prompt to <strong data-momo-display></strong>. Enter your mobile money PIN to approve K{{ number_format($amount, 2) }}.</p>
                        <ol class="zp-progress">
                            <li class="is-done">Prompt sent</li>
                            <li class="is-active">Approve with your PIN</li>
                            <li>Payment confirmed</li>
                        </ol>
                        <p class="zp-momo__instructions" data-momo-instructions hidden></p>
                        <p class="zp-countdown">Waiting for approval · <span data-countdown>3:00</span></p>
                        <div class="zp-momo__actions">
                            <button type="button" class="zp-btn zp-btn--ghost" data-momo-resend>Send a new prompt</button>
                            <button type="button" class="zp-linkbtn" data-momo-change>Use a different number</button>
                        </div>
                    </div>

                    {{-- Timed out --}}
                    <div class="zp-momo__screen" data-screen="timeout" hidden>
                        <h2 class="zp-momo__title">Still waiting for your approval</h2>
                        <p class="zp-momo__lead">We haven’t received the approval yet. If you already entered your PIN, check again. If the prompt never arrived or expired, send a new one.</p>
                        <div class="zp-momo__actions">
                            <button type="button" class="zp-btn" data-momo-check>Check again</button>
                            <button type="button" class="zp-btn zp-btn--ghost" data-momo-resend>Send a new prompt</button>
                            <button type="button" class="zp-linkbtn" data-momo-change>Use a different number</button>
                        </div>
                    </div>

                    {{-- Failed --}}
                    <div class="zp-momo__screen" data-screen="failed" hidden>
                        <div class="zp-badge zp-badge--fail" aria-hidden="true">!</div>
                        <h2 class="zp-momo__title">Payment didn’t go through</h2>
                        <p class="zp-momo__lead" data-fail-message>The payment was not completed.</p>
                        <ul class="zp-tips">
                            <li>Check your wallet balance covers the amount and any fee.</li>
                            <li>Approve the prompt within about a minute of it arriving.</li>
                            <li>Make sure the number is registered for mobile money.</li>
                        </ul>
                        <div class="zp-momo__actions">
                            <button type="button" class="zp-btn" data-momo-resend>Try again</button>
                            <button type="button" class="zp-btn zp-btn--ghost" data-momo-change>Change number or network</button>
                            <button type="button" class="zp-linkbtn" data-use-card>Pay by card instead</button>
                        </div>
                        <p class="zp-fine" data-attempt></p>
                    </div>

                    {{-- Paid --}}
                    <div class="zp-momo__screen zp-momo__screen--paid" data-screen="paid" hidden>
                        <div class="zp-confetti" aria-hidden="true"></div>
                        <div class="zp-badge zp-badge--ok" aria-hidden="true">✓</div>
                        <h2 class="zp-momo__title">Payment received</h2>
                        <p class="zp-momo__lead">Welcome to ZamPayroll {{ $plan['name'] }}. A confirmation is on its way to your email.</p>
                        <dl class="zp-receipt">
                            <div><dt>Plan</dt><dd data-r="plan"></dd></div>
                            <div><dt>Amount</dt><dd data-r="amount"></dd></div>
                            <div><dt>Paid</dt><dd data-r="paid_at"></dd></div>
                            <div><dt>Reference</dt><dd data-r="reference"></dd></div>
                        </dl>
                        <a class="zp-btn zp-btn--block" href="{{ config('zampayroll.login_url') }}">Sign in to ZamPayroll</a>
                    </div>
                </div>
            </form>

            <aside class="zp-summary" aria-label="Order summary">
                <div class="zp-summary__card">
                    <div class="zp-summary__flag" aria-hidden="true"><span></span><span></span><span></span></div>
                    <p class="zp-summary__plan">{{ $plan['name'] }}</p>
                    <p class="zp-summary__band">{{ $plan['employees'] }}</p>

                    <div class="zp-summary__price">
                        <span>K{{ number_format($amount, 2) }}</span>
                        <small>{{ $billing === 'annual' ? 'per year' : 'per month' }}</small>
                    </div>

                    <a class="zp-summary__switch" href="{{ route('zampayroll.checkout', ['plan' => $plan['key'], 'billing' => $otherBilling]) }}">
                        @if ($billing === 'monthly')
                            Switch to annual: K{{ number_format($plan['annual']) }}/year, 2 months free
                        @else
                            Switch to monthly: K{{ number_format($plan['monthly']) }}/month
                        @endif
                    </a>

                    <dl class="zp-summary__lines">
                        <div><dt>Billing</dt><dd>{{ $billing === 'annual' ? 'Annual' : 'Monthly' }}</dd></div>
                        <div><dt>Currency</dt><dd>Zambian Kwacha (ZMW)</dd></div>
                        <div class="zp-summary__total"><dt>Due today</dt><dd>K{{ number_format($amount, 2) }}</dd></div>
                    </dl>

                    <p class="zp-summary__included">{{ $plan['detail'] }}</p>
                    <p class="zp-summary__ref" data-reference hidden>Reference <strong></strong></p>
                </div>
                <p class="zp-summary__help">Questions about billing? Email <a href="mailto:{{ config('zampayroll.admin_email') }}">{{ config('zampayroll.admin_email') }}</a>.</p>
            </aside>
        </div>
    </div>
@endsection

@push('scripts')
    <script>window.ZP_NETWORKS = @json(collect($networks)->map(fn ($n) => ['label' => $n['label'], 'prefixes' => $n['prefixes']]));</script>
    <script src="{{ asset('zampayroll/confetti.js') }}?v=1"></script>
    <script src="{{ asset('zampayroll/checkout.js') }}?v=1"></script>
@endpush
