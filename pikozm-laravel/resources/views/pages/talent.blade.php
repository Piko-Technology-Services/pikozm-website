@extends('layouts.app')

@section('title', 'Join Our Team – Piko Tech Services')
@section('description', 'Join Piko Tech Services\' Talent Program – empowering future developers, designers, and creators.')

@push('meta')
    <meta property="og:title" content="Join Our Team – Piko Tech Services">
    <meta property="og:description" content="Remote & On-site Opportunities. Web Devs, Designers, Marketers, and more. Register now!">
    <meta property="og:image" content="{{ asset('images/talent-banner.png') }}">
    <meta property="og:url" content="{{ route('talent.create') }}">
    <meta property="og:type" content="website">
@endpush

@section('content')
    <section class="section-padding bg-light" id="join-talent">
        <div class="container">
            <div class="row justify-content-center text-center mb-5">
                <div class="col-lg-10">
                    <img src="{{ asset('images/talent-banner.png') }}" class="img-fluid rounded shadow-sm mb-4" alt="Join Our Team Banner">
                    <h2 class="mb-3">Join Our Team</h2>
                    <p class="text-muted">
                        At <strong>Piko Tech Services</strong>, we’re opening doors for aspiring developers,
                        designers, marketers, and creatives. Whether you're a student, self-learner, or just
                        passionate about digital innovation – let’s build together!
                    </p>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    @include('partials.form-errors')

                    <form action="{{ route('talent.store') }}" method="POST" target="_blank" class="bg-white p-4 rounded shadow-sm">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="full_name" class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="full_name" name="full_name" value="{{ old('full_name') }}" maxlength="120" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone / WhatsApp <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control" id="phone" name="phone" value="{{ old('phone') }}" maxlength="40" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email (optional)</label>
                                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="skill" class="form-label">Main Interest/Skill</label>
                                <select class="form-select" id="skill" name="skill" required>
                                    <option value="">-- Select --</option>
                                    @foreach ($skills as $skill)
                                        <option value="{{ $skill }}" @selected(old('skill') === $skill)>{{ $skill }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12 mb-3">
                                <label for="message" class="form-label">Tell us more about yourself</label>
                                <textarea class="form-control" name="message" id="message" rows="4" placeholder="What do you hope to learn or contribute?">{{ old('message') }}</textarea>
                            </div>

                            <div class="col-12 text-center mt-4">
                                <button type="submit" class="btn btn-primary px-4">Join</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection
