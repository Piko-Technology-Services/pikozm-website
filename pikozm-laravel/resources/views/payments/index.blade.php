@extends('layouts.app')

@section('title', 'Payments - Piko Technology Services')
@section('description', 'Pay for Piko Technology Services software subscriptions, website development, and design services securely through DPO Pay.')

@push('styles')
    <style>
        .price-item { border-bottom: 1px solid #e9ecef; padding: 22px 0; }
        .price-item:last-child { border-bottom: none; }
        .price-tag { font-size: 1.25rem; font-weight: 700; white-space: nowrap; }
        .price-tag .cycle { font-size: .85rem; font-weight: 400; color: #6c757d; }
        .item-note { font-size: .85rem; color: #6c757d; font-style: italic; }
        .policy-box { background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 25px 28px; }
        .policy-box h5 { font-size: 1rem; font-weight: 700; margin-bottom: 8px; }
        .policy-box p { font-size: .9rem; color: #6c757d; margin-bottom: 18px; }
        .policy-box p:last-child { margin-bottom: 0; }
    </style>
@endpush

@section('content')
    <section class="section-padding pb-0" id="home">
        <div class="container">
            <div class="row">
                <div class="col-12 mt-5 pt-5">
                    <h2 class="mb-3 text-center" data-aos="fade-up">Pay Online, Quickly &amp; Securely</h2>
                </div>
                <div class="col-lg-8 col-12 mx-auto text-center">
                    <p data-aos="fade-up" data-aos-delay="300">
                        Select a subscription or service below, or enter the details of a payment that isn't
                        listed. All payments are processed through <strong>DPO Pay's</strong> secure checkout,
                        which supports Visa and Mastercard, mobile money, and bank transfers. All prices are
                        shown in Zambian Kwacha (ZMW).
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding" id="payments-products">
        <div class="container">

            @if ($subscriptions->isNotEmpty())
                <div class="row mb-5">
                    <div class="col-12">
                        <h3 class="mb-2" data-aos="fade-up">Subscriptions</h3>
                        <p class="text-secondary mb-4" data-aos="fade-up">Recurring plans, billed each period.</p>
                    </div>
                    <div class="col-12">
                        @foreach ($subscriptions as $product)
                            @include('payments._product-row', ['buttonLabel' => 'Subscribe'])
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($oneTime->isNotEmpty())
                <div class="row">
                    <div class="col-12">
                        <h3 class="mb-2" data-aos="fade-up">Services &amp; Projects</h3>
                        <p class="text-secondary mb-4" data-aos="fade-up">One-time payments for a specific piece of work.</p>
                    </div>
                    <div class="col-12">
                        @foreach ($oneTime as $product)
                            @include('payments._product-row', ['buttonLabel' => 'Pay Now'])
                        @endforeach
                    </div>
                </div>
            @endif

        </div>
    </section>

    <section class="contact section-padding pt-0" id="custom-payment">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 col-12 mx-auto">

                    <h3 class="mb-3 text-center" data-aos="fade-up">Paying For Something Else?</h3>
                    <p class="text-center text-secondary mb-4" data-aos="fade-up">
                        If you're settling a quotation, an invoice, or a project down payment, enter the details
                        and the amount agreed with us.
                    </p>

                    @include('partials.form-errors')

                    <form class="contact-form" role="form" data-aos="fade-up" action="{{ route('payments.checkout.custom') }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12 my-2">
                                <label for="item_name" class="form-label">What's this payment for? <sup class="text-danger">*</sup></label>
                                <input type="text" name="item_name" id="item_name" class="form-control" value="{{ old('item_name') }}"
                                    placeholder="e.g. Quotation PTS-QUO-279, project down payment" maxlength="120" required>
                            </div>

                            <div class="col-12 my-4">
                                <label for="item_description" class="form-label">Description (optional)</label>
                                <textarea name="item_description" id="item_description" rows="4" class="form-control" maxlength="1000"
                                    placeholder="Any detail that helps us match this payment to your quotation or invoice.">{{ old('item_description') }}</textarea>
                            </div>

                            <div class="col-lg-6 col-12">
                                <label for="custom_amount" class="form-label">Amount (ZMW) <sup class="text-danger">*</sup></label>
                                <input type="number" name="amount" id="custom_amount" class="form-control" value="{{ old('amount') }}"
                                    min="1" step="0.01" placeholder="0.00" required>
                            </div>

                            <div class="col-lg-5 col-12 mx-auto mt-5">
                                <button type="submit" class="form-control">Continue to Payment</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>

    <section class="section-padding pt-0" id="payment-terms">
        <div class="container">
            <div class="row">
                <div class="col-lg-9 col-12 mx-auto">
                    <div class="policy-box" data-aos="fade-up">
                        <h4 class="mb-4">Delivery, Refund &amp; Cancellation Terms</h4>

                        <h5>Delivery of services</h5>
                        <p>
                            Software subscriptions and hosting are activated within 1–2 business days of a
                            confirmed payment, and access details are sent to the email address provided at
                            checkout. Design, development, and printing projects commence on receipt of the
                            agreed down payment, with delivery timelines confirmed in writing in your quotation.
                            As set out in our standard quotation terms, project work begins once a down payment
                            of 75% of the total has been received.
                        </p>

                        <h5>Cancellations</h5>
                        <p>
                            Subscriptions may be cancelled at any time before the next renewal date by
                            contacting us at {{ config('piko.email') }}. Project work may be cancelled in writing before
                            work has commenced for a full refund of any amount paid.
                        </p>

                        <h5>Refunds</h5>
                        <p>
                            Where a service has not yet been delivered or work has not yet commenced, payments
                            are refunded in full. Where work is already in progress, refunds are calculated
                            against the work completed to date. Approved refunds are returned to the original
                            payment method within 14 business days. To request a refund, contact us at
                            {{ config('piko.email') }} with your payment reference.
                        </p>

                        <h5>Need help with a payment?</h5>
                        <p class="mb-0">
                            Email <a href="mailto:{{ config('piko.email') }}">{{ config('piko.email') }}</a> or call/WhatsApp
                            <a href="tel:+260962893773">+260 962 893 773</a>. Full
                            <a href="{{ asset(config('piko.legal.terms')) }}" target="_blank" rel="noopener">Terms and Conditions</a>,
                            <a href="{{ asset(config('piko.legal.privacy')) }}" target="_blank" rel="noopener">Privacy Policy</a>, and
                            <a href="{{ asset(config('piko.legal.refunds')) }}" target="_blank" rel="noopener">Refund and Cancellation Policy</a>
                            are available to read in full.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
