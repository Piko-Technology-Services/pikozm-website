@extends('layouts.app')

@section('title', 'Piko Technology Services - Business Development, Information Systems, Software & Creative Solutions')

@push('meta')
    <meta property="og:title" content="Piko Technology Services - Smart Solutions for Business Growth">
    <meta property="og:description" content="We provide expert services in Business Information Systems, Web Development, Custom Software, Training, Design, and Printing.">
    <meta property="og:image" content="{{ asset('images/piko-logo.png') }}">
    <meta property="og:url" content="{{ route('home') }}">
    <meta property="og:type" content="website">
@endpush

@push('head')
    @if (config('piko.adsense_client'))
        <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client={{ config('piko.adsense_client') }}" crossorigin="anonymous"></script>
    @endif
@endpush

@section('hero')
    <section class="hero" id="hero">
        <div class="heroText"></div>
        <div class="videoWrapper">
            <video id="heroVideo" autoplay loop muted playsinline class="custom-video">
                <source src="{{ asset('videos/hero.mp4') }}" type="video/mp4">
                Your browser does not support the video tag.
            </video>
        </div>
    </section>
@endsection

@section('content')
    <section class="section-padding pb-0" id="about">
        <div class="container mb-5 pb-lg-5">
            <div class="row">
                <div class="col-12 mt-5 py-5">
                    <h2 class="mb-3 text-center" data-aos="fade-up">Let’s Build Smarter Businesses Together</h2>
                </div>

                {{-- Partners & platforms (horizontal slider) --}}
                <div class="mb-5">
                    <div class="sponsors-slider d-flex align-items-center overflow-hidden" data-aos="fade-up" data-aos-delay="100">
                        <div class="d-flex flex-nowrap animate-slider">
                            @for ($i = 0; $i < 4; $i++)
                                @foreach ($partnerLogos as $logo)
                                    <img src="{{ asset($logo['src']) }}" alt="{{ $logo['alt'] }}" class="mx-4" style="height:{{ $logo['height'] }}px;">
                                @endforeach
                            @endfor
                        </div>
                    </div>
                </div>

                <div class="row justify-content-center align-items-center">
                    <div class="col-lg-6 col-12 mt-3 mb-lg-5">
                        <p class="me-4" data-aos="fade-up" data-aos-delay="300">
                            <strong>Piko Technology Services</strong> empowers entrepreneurs, SMEs, and enterprises to
                            scale and thrive through:
                        </p>
                        <p data-aos="fade-up" data-aos-delay="500">
                            We also nurture the next generation of African tech innovators by offering:
                        </p>
                    </div>

                    <div class="col-lg-6 col-12 mt-lg-3 mb-lg-5">
                        <ul class="me-4" data-aos="fade-up" data-aos-delay="500">
                            <li class="text-secondary">Branding, Graphic Design & Web Development</li>
                            <li class="text-secondary">Custom Software & Business Information Systems</li>
                            <li class="text-secondary">Large Format Printing & Visual Media</li>
                            <li class="text-secondary">Business Development & Strategic Consultancy</li>
                            <li class="text-secondary">Professional Training in Tech & Creative Design</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-3 col-12 p-0">
                    <img src="{{ asset('images/people/team.jpg') }}" class="img-fluid about-image" alt="Piko Technology Services Team">
                </div>

                <div class="col-lg-3 col-12 bg-dark">
                    <div class="d-flex flex-column flex-wrap justify-content-center h-100 py-5 px-4 pt-lg-4 pb-lg-0">
                        <h3 class="text-white mb-3" data-aos="fade-up">We’re consultants, creators & coders.</h3>
                        <p class="text-secondary-white-color" data-aos="fade-up">Driven by purpose, built for impact.</p>
                        <div class="mt-3 custom-links">
                            <a href="#services" class="text-white custom-link" data-aos="zoom-in" data-aos-delay="100">Explore Services</a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-12 p-0">
                    <section id="myCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
                        <div class="carousel-inner">
                            <div class="carousel-item active">
                                <img src="{{ asset('images/people/team-2.jpg') }}" class="img-fluid team-image" alt="">
                                <div class="team-thumb bg-success">
                                    <h3 class="text-white mb-0">Coming Soon</h3>
                                    <p class="text-secondary-white-color mb-0">Team Expansion</p>
                                </div>
                            </div>
                        </div>

                        <button class="carousel-control-prev" type="button" data-bs-target="#myCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#myCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                    </section>
                </div>
            </div>
        </div>
    </section>

    <section class="section-padding" id="services">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="mb-5 text-center" data-aos="fade-up">Our Services</h2>
                </div>

                @foreach (array_chunk($services, (int) ceil(count($services) / 2)) as $column)
                    <div class="col-lg-6 col-12">
                        @foreach ($column as $service)
                            <div class="portfolio-thumb mb-5" data-aos="fade-up">
                                <a href="{{ asset($service['image']) }}" class="image-popup">
                                    <img src="{{ asset($service['image']) }}" class="img-fluid portfolio-image" alt="{{ $service['title'] }}">
                                </a>
                                <div class="portfolio-info">
                                    <h4 class="portfolio-title mb-0">{{ $service['title'] }}</h4>
                                    <p class="text-secondary">{{ $service['description'] }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="contact section-padding" id="contact">
        <div class="container">
            <div class="row">
                <div class="col-lg-7 col-12 mx-auto">

                    <h2 class="mb-4 text-center" data-aos="fade-up">Don't be shy, get in touch</h2>

                    @include('partials.form-errors')

                    {{-- Saved to the database, then handed off to WhatsApp in a new tab. --}}
                    <form action="{{ route('contact.store') }}" method="POST" target="_blank" class="contact-form" role="form" data-aos="fade-up">
                        @csrf

                        <div class="row">
                            <div class="col-lg-6 col-6">
                                <label for="name" class="form-label">Name <sup class="text-danger">*</sup></label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Full name" value="{{ old('name') }}" maxlength="120" required>
                            </div>

                            <div class="col-lg-6 col-6">
                                <label for="email" class="form-label">Email <sup class="text-danger">*</sup></label>
                                <input type="email" name="email" id="email" class="form-control" placeholder="Email address" value="{{ old('email') }}" required>
                            </div>

                            <div class="col-12 my-4">
                                <label for="message" class="form-label">How can we help?</label>
                                <textarea name="message" rows="6" class="form-control" id="message" placeholder="Tell us about your needs or project..." required>{{ old('message') }}</textarea>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Services <sup class="text-danger">*</sup></label>
                            </div>

                            @foreach ($contactServices as $service)
                                <div class="col-lg-4 col-12 my-2 my-lg-0">
                                    <div class="form-check">
                                        <input type="checkbox" id="service{{ $loop->index }}" name="services[]" class="form-check-input" value="{{ $service }}"
                                            @checked(in_array($service, old('services', [])))>
                                        <label class="form-check-label" for="service{{ $loop->index }}">{{ $service }}</label>
                                    </div>
                                </div>
                            @endforeach

                            <div class="col-lg-5 col-12 mx-auto mt-5">
                                <button type="submit" class="form-control">Send via WhatsApp</button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </section>

    <section class="google-map">
        <iframe src="https://maps.google.com/maps?q=Ndola,+Zambia&t=&z=13&ie=UTF8&iwloc=&output=embed"
            class="map-iframe" width="100%" height="400" style="border:0;" allowfullscreen="" loading="lazy"
            title="Map of Ndola, Zambia"></iframe>
    </section>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const video = document.getElementById('heroVideo');
            if (video) {
                video.playbackRate = 1.5;
            }
        });
    </script>
@endpush
