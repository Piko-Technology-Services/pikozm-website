<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

{{-- PikoPay keeps its own stand-alone look (formerly payments.html). --}}
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>PikoPay | Secure Digital Payments for Social Impact</title>

    <meta name="description" content="PikoPay is a secure digital payment platform by Piko Technology Services, powering transparent payments for businesses, and digital initiatives across Africa.">
    <meta name="keywords" content="PikoPay, Piko Digital Impact Programme, PDIP, secure online payments Africa, social impact payments, digital donations, fintech for NGOs, Zambia fintech, African digital payments">
    <meta name="author" content="{{ config('piko.name') }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ route('pikopay.index') }}">
    <link rel="icon" type="image/png" href="{{ asset('images/piko-logo.png') }}">

    <meta property="og:title" content="PikoPay | Secure Payments for Digital & Social Impact">
    <meta property="og:description" content="Powering transparent, secure payments for digital projects, and impact-driven organisations across Africa.">
    <meta property="og:image" content="{{ asset('images/piko-logo.png') }}">
    <meta property="og:url" content="{{ route('pikopay.index') }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="PikoPay">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="PikoPay | Secure Payments for Social Impact in Africa">
    <meta name="twitter:description" content="A secure payment platform for digital projects across Africa.">
    <meta name="twitter:image" content="{{ asset('images/piko-logo.png') }}">
    <meta name="twitter:site" content="@@pikotechnology">

    <meta name="theme-color" content="#03629A">

    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">

    <style>
        body { background: #f6f8fb; font-family: "Inter", system-ui, -apple-system, BlinkMacSystemFont; color: #1f2937; }
        .hero { background: #03629A; color: #fff; padding: 5rem 1rem; }
        .hero h1 { font-weight: 700; }
        .hero p { max-width: 600px; opacity: 0.95; }
        .impact-card { border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08); }
        .impact-icon { width: 56px; height: 56px; border-radius: 14px; background: rgba(13, 110, 253, 0.1); display: flex; align-items: center; justify-content: center; color: #03629A; font-size: 1.5rem; }
        .cta-btn { padding: 0.9rem 1.4rem; font-size: 1.05rem; border-radius: 12px; }
        footer { color: #6b7280; }
    </style>
</head>

<body>

    <section class="hero text-center">
        <div class="container">
            <h1>PikoPay - Secure Payments</h1>
            <p class="mx-auto mt-3">
                Secure, transparent, and seamless payments that power real digital impact.
                Trusted by clients across Africa and beyond.
            </p>
            <button class="btn btn-light btn-lg mt-4 cta-btn" data-bs-toggle="modal" data-bs-target="#paymentModal">
                Make a Payment
            </button>
        </div>
    </section>

    <section class="py-5">
        <div class="container">
            <div class="row g-4">
                @foreach ([
                    ['icon' => 'shield-check', 'title' => 'Secure Transactions', 'text' => 'Payments are encrypted and securely processed via Lenco.'],
                    ['icon' => 'lightning-charge', 'title' => 'Fast & Reliable', 'text' => 'Instant initiation with real-time payment confirmation.'],
                    ['icon' => 'graph-up-arrow', 'title' => 'Built for Impact', 'text' => 'Every payment supports sustainable digital solutions.'],
                ] as $prop)
                    <div class="col-md-4">
                        <div class="card impact-card h-100 p-4">
                            <div class="impact-icon mb-3"><i class="bi bi-{{ $prop['icon'] }}"></i></div>
                            <h5>{{ $prop['title'] }}</h5>
                            <p class="text-muted">{{ $prop['text'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4">
                <div class="modal-header border-0">
                    <h5 class="modal-title" id="paymentModalLabel">PikoPay - Secure Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <form id="clientPaymentForm" action="{{ route('pikopay.initiate') }}" data-verify-url="{{ route('pikopay.verify') }}">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-person-fill"></i></span>
                                <input type="text" class="form-control" name="full_name" id="clientFullName" placeholder="Client or Company Name" maxlength="120" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-envelope-fill"></i></span>
                                <input type="email" class="form-control" name="email" id="clientEmail" placeholder="Email Address" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="bi bi-journal-text"></i></span>
                                <input type="text" class="form-control" name="purpose" id="paymentPurpose" placeholder="Purpose of Payment" maxlength="190" required>
                            </div>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <span class="input-group-text bg-white"><i class="bi bi-cash-stack"></i></span>
                                    <input type="number" class="form-control" name="amount" id="paymentAmount" placeholder="Amount" min="1" step="0.01" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="currency" id="paymentCurrency">
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency }}" @selected($currency === 'USD')>{{ $currency }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg">Proceed to Secure Payment</button>

                        <p class="small text-muted text-center mt-3">Powered by Lenco • Safe & Secure</p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <footer class="py-4 text-center">
        <small>
            <p class="copyright-text mb-2">
                &copy; {{ now()->year }} {{ config('piko.name') }}<br><br>
                @include('partials.legal-links')
            </p>
            <a href="{{ route('home') }}">pikozm.com</a> · <a href="{{ route('payments.index') }}">Pay for a service</a>
        </small>
    </footer>

    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <script src="https://pay.lenco.co/js/v1/inline.js"></script>
    <script src="{{ asset('js/pages/forms.js') }}"></script>
    <script src="{{ asset('js/pages/pikopay.js') }}"></script>
</body>

</html>
