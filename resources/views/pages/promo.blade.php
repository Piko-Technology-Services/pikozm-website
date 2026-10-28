@extends('layouts.app')

@section('title', 'Register for Promo – Piko Tech Services')
@section('description', 'Register for Piko Tech Services Promos and boost your brand with our packages.')

@push('meta')
    <meta property="og:title" content="🔥 Business Booster Packages at Piko Tech Services">
    <meta property="og:description" content="Get your business looking professional with branding, profiles, and websites – packages from just {{ $packages->first()?->formattedPrice() }}.">
    <meta property="og:image" content="{{ asset('images/promo-banner.png') }}">
    <meta property="og:url" content="{{ route('promo.index') }}">
    <meta property="og:type" content="website">
@endpush

@section('content')
    <div class="container">
        <div class="row justify-content-center text-center mb-5 mt-5">
            <div class="col-lg-10">
                <h2 class="mb-3">🔥 Choose Your Promo Package 🔥</h2>
                <p class="text-secondary">
                    We’re helping businesses grow with our <strong>Business Booster Packages</strong>. Select your fit:
                </p>
            </div>
        </div>

        <div class="row text-center mb-5 p-4">
            @foreach ($packages as $package)
                <div class="col-md-4 mb-4">
                    <div @class(['card h-100 border-0 shadow-sm', 'bg-light border-top border-3 border-primary' => $package->is_featured])>
                        <div class="card-body px-4 py-5">
                            <h5 class="fw-bold mb-3">{{ $package->name }}</h5>
                            <h6 class="text-primary fw-semibold mb-2">{{ $package->formattedPrice() }}</h6>
                            <p class="text-muted small mb-4">{{ $package->tagline }}</p>
                            <ul class="list-unstyled text-start small">
                                @foreach ($package->features as $feature)
                                    <li @class(['mb-2' => ! $loop->last])>• {{ $feature }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <section class="contact section-padding" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 col-12 mx-auto">
                    <h2 class="mb-4 text-center" data-aos="fade-up">Register for a Promo Package</h2>

                    @include('partials.form-errors')

                    <form action="{{ route('promo.store') }}" method="POST" target="_blank" class="contact-form bg-white p-4 rounded shadow-sm" role="form" data-aos="fade-up">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="full_name" id="full_name" class="form-control" placeholder="Full name" value="{{ old('full_name') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone / WhatsApp <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" id="phone" class="form-control" placeholder="Phone number" value="{{ old('phone') }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Email address" value="{{ old('email') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="business_name" class="form-label">Business Name</label>
                                <input type="text" name="business_name" id="business_name" class="form-control" placeholder="Business name" value="{{ old('business_name') }}">
                            </div>

                            <div class="col-12 mb-3">
                                <label for="promo_package_id" class="form-label">Select Promo Package <span class="text-danger">*</span></label>
                                <select class="form-select" id="promo_package_id" name="promo_package_id" required>
                                    <option value="">-- Choose Package --</option>
                                    @foreach ($packages as $package)
                                        <option value="{{ $package->id }}" @selected((int) old('promo_package_id') === $package->id)>{{ $package->label() }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="message" class="form-label">Anything else you'd like us to know?</label>
                                <textarea name="message" rows="4" class="form-control" id="message" placeholder="Optional message...">{{ old('message') }}</textarea>
                            </div>

                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary px-4">Register</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
