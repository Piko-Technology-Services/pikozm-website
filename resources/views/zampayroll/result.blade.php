@extends('layouts.zampayroll')

@section('title', match ($outcome) { 'paid' => 'Payment received – ZamPayroll', 'cancelled' => 'Payment cancelled – ZamPayroll', default => 'Payment status – ZamPayroll' })

@section('content')
    <section class="zp-wrap zp-result zp-result--{{ $outcome }}">
        @if ($outcome === 'paid')
            <div class="zp-confetti" aria-hidden="true"></div>
            <div class="zp-badge zp-badge--ok" aria-hidden="true">✓</div>
            <h1>Payment received</h1>
            <p class="zp-lede">Welcome to ZamPayroll {{ $subscription->plan_name }}. A confirmation is on its way to {{ $subscription->email }}.</p>
        @elseif ($outcome === 'cancelled')
            <div class="zp-badge zp-badge--warn" aria-hidden="true">–</div>
            <h1>Payment cancelled</h1>
            <p class="zp-lede">{{ $message }}</p>
        @elseif ($outcome === 'awaiting')
            <div class="zp-badge zp-badge--warn" aria-hidden="true">…</div>
            <h1>Payment still processing</h1>
            <p class="zp-lede">DPO hasn’t confirmed the payment yet. Refresh this page in a minute, or contact us with your reference.</p>
        @else
            <div class="zp-badge zp-badge--fail" aria-hidden="true">!</div>
            <h1>Payment didn’t go through</h1>
            <p class="zp-lede">{{ $message }}</p>
        @endif

        @if ($subscription)
            <dl class="zp-receipt">
                <div><dt>Plan</dt><dd>{{ $subscription->plan_name }} · {{ $subscription->billingLabel() }}</dd></div>
                <div><dt>Amount</dt><dd>{{ $subscription->formattedAmount() }}</dd></div>
                <div><dt>Method</dt><dd>{{ $subscription->methodLabel() }}</dd></div>
                <div><dt>Reference</dt><dd>{{ $subscription->reference }}</dd></div>
            </dl>
        @endif

        <div class="zp-result__actions">
            @if ($outcome === 'paid')
                <a class="zp-btn" href="{{ config('zampayroll.login_url') }}">Sign in to ZamPayroll</a>
            @elseif ($subscription)
                <a class="zp-btn" href="{{ route('zampayroll.checkout', ['plan' => $subscription->plan, 'billing' => $subscription->billing_cycle]) }}">Choose a payment method again</a>
                <a class="zp-btn zp-btn--ghost" href="{{ route('zampayroll.pricing') }}">See all plans</a>
            @else
                <a class="zp-btn" href="{{ route('zampayroll.pricing') }}">See all plans</a>
            @endif
        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('zampayroll/confetti.js') }}?v=1"></script>
@endpush
