<!doctype html>
<html lang="en">

{{-- ZamPayroll payments – a focused payment area, styled as part of zampayroll.com --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0A3B22">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'ZamPayroll payments')</title>
    <meta name="description" content="Pay for your ZamPayroll subscription by mobile money, card or invoice.">
    <link rel="icon" href="{{ config('zampayroll.logo_url') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,500;12..96,700;12..96,800&family=Public+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('zampayroll/zampayroll.css') }}?v=2" rel="stylesheet">
    @stack('head')
</head>

<body class="@yield('body-class')">
    <a class="skip" href="#main">Skip to content</a>

    <header class="zp-header">
        <div class="zp-flagline" aria-hidden="true"><span></span><span></span><span></span><span></span></div>
        <div class="zp-wrap zp-header__inner">
            <a class="zp-logo" href="{{ route('zampayroll.pricing') }}" aria-label="ZamPayroll payments">
                <img src="{{ config('zampayroll.logo_url') }}" alt="" width="36" height="36" onerror="this.remove()">
                <span><b>Zam</b>Payroll</span>
            </a>
            <span class="zp-header__section">Payments</span>
            <div class="zp-header__actions">
                <a class="zp-link zp-link--muted" href="mailto:{{ config('zampayroll.admin_email') }}">Billing help</a>
                <a class="zp-link" href="{{ config('zampayroll.login_url') }}">Sign in</a>
            </div>
        </div>
    </header>

    <main id="main">
        @yield('content')
    </main>

    <footer class="zp-footer">
        <div class="zp-wrap zp-footer__inner">
            <p>Card payments are processed securely by DPO Pay. Mobile money supports Airtel, MTN and Zamtel.</p>
            <p class="zp-footer__links">
                <a href="{{ asset(config('piko.legal.terms')) }}" target="_blank" rel="noopener">Terms</a>
                <a href="{{ asset(config('piko.legal.privacy')) }}" target="_blank" rel="noopener">Privacy</a>
                <a href="{{ asset(config('piko.legal.refunds')) }}" target="_blank" rel="noopener">Refunds</a>
                <a href="{{ config('zampayroll.site_url') }}">zampayroll.com</a>
            </p>
        </div>
    </footer>

    @stack('scripts')
</body>

</html>
