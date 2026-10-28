@extends('layouts.app')

@section('title', 'Piko Digital Impact Programme (PDIP) | Technology for Humanitarian Impact in Africa')
@section('description', 'The Piko Digital Impact Programme (PDIP) is a social impact initiative by Piko Technology Services delivering free and subsidized digital solutions to NGOs, humanitarian organizations, and government social projects across Africa.')
@section('keywords', 'Piko Digital Impact Programme, PDIP, NGO technology support, humanitarian digital solutions, nonprofit IT support, CSR technology projects, donor management systems, monitoring and evaluation tools, social impact technology Africa, digital transformation NGOs')

@push('meta')
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ route('digital-impact') }}">
    <meta property="og:title" content="Piko Digital Impact Programme">
    <meta property="og:description" content="Driving digital transformation for NGOs, communities, and social initiatives across regions.">
    <meta property="og:image" content="{{ asset('images/cover.png') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:url" content="{{ route('digital-impact') }}">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Piko Digital Impact Programme | Technology for Social Good in Africa">
    <meta name="twitter:description" content="A social impact programme delivering digital infrastructure, systems, and training to NGOs and humanitarian projects across Africa.">
    <meta name="twitter:image" content="{{ asset('images/piko-logo.png') }}">
    <meta name="twitter:site" content="@@pikotechnology">
    <meta name="twitter:creator" content="@@pikotechnology">
@endpush

@section('content')
    <section class="section-padding" id="digital-impact">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-11 col-12">

                    <div class="text-center mt-5 mb-5 py-3">
                        <h2 class="text-primary mb-3" data-aos="fade-up">Piko Digital Impact Programme</h2>
                        <p class="lead" data-aos="fade-up" data-aos-delay="200">
                            The Piko Digital Impact Programme (PDIP) is a social impact initiative supporting NGOs,
                            humanitarian organizations, and government projects with essential digital solutions.
                        </p>
                    </div>

                    {{-- Who this is for --}}
                    <div class="mb-5 mt-5 py-5">
                        <h5 class="fw-bold text-center mb-4 mt-5" data-aos="fade-up">Who This Is For</h5>
                        <div class="row g-4 justify-content-center" data-aos="fade-up" data-aos-delay="100">
                            @foreach ([
                                ['icon' => 'building', 'label' => 'Registered NGOs & Non-Profits'],
                                ['icon' => 'people', 'label' => 'Community & Humanitarian Projects'],
                                ['icon' => 'heart', 'label' => 'Faith-based & Development Organizations'],
                                ['icon' => 'house', 'label' => 'Government Social Initiatives'],
                            ] as $audience)
                                <div class="col-md-6 col-lg-3">
                                    <div class="card shadow-sm text-center p-4 card-hover h-100">
                                        <i class="bi bi-{{ $audience['icon'] }} display-4 mb-3 text-primary"></i>
                                        <h6 class="fw-bold text-primary">{{ $audience['label'] }}</h6>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Call to action --}}
                    <section class="section-padding bg-primary text-white mb-5" id="digital-cta" style="border-radius: 3%">
                        <div class="container text-center" data-aos="fade-up">
                            <h2 class="mb-5 text-light">Join the Digital Impact Movement</h2>
                            <p class="lead mb-5 text-light">
                                Together, we can empower NGOs, communities, and young tech talent to create measurable change.
                            </p>
                            <div class="d-flex justify-content-center flex-wrap gap-4 my-5">
                                <a href="#donateModal" class="btn btn-outline-light btn-xl" data-bs-toggle="modal">
                                    <i class="bi bi-heart-fill me-2 text-light"></i> Sponsor a Project
                                </a>
                                <a href="#volunteerModal" class="btn btn-outline-light btn-xl" data-bs-toggle="modal">
                                    <i class="bi bi-people-fill me-2 text-light"></i> Join the Team
                                </a>
                            </div>
                            <p class="mt-3 small text-light">
                                Be part of a growing network of innovators, donors and organizations that are transforming
                                social impact across Africa through technology.
                            </p>
                        </div>
                    </section>

                    {{-- Get involved --}}
                    <section class="section-padding" id="get-involved">
                        <div class="container">
                            <div class="text-center mb-5">
                                <h2 class="text-primary mb-3" data-aos="fade-up">Get Involved</h2>
                                <p class="lead" data-aos="fade-up" data-aos-delay="100">
                                    Support our mission by donating, partnering, or applying for digital support.
                                </p>
                            </div>
                            <div class="row g-4 justify-content-center">
                                @foreach ([
                                    ['icon' => 'cash-stack', 'title' => 'Support the Initiative', 'text' => 'Contribute any amount to fund NGO digital systems, hosting, and training.', 'modal' => 'donateModal', 'cta' => 'Support Now'],
                                    ['icon' => 'bounding-box', 'title' => 'Become a Partner', 'text' => 'Support us via CSR, project funding, long-term partnerships, or resource support.', 'modal' => 'partnerModal', 'cta' => 'Partner Now'],
                                    ['icon' => 'person-lines-fill', 'title' => 'Be Part of the Team', 'text' => 'Join our team and contribute your skills to help NGOs and communities digitally.', 'modal' => 'volunteerModal', 'cta' => 'Sign Up'],
                                    ['icon' => 'laptop-fill', 'title' => 'Apply for Support', 'text' => 'If you are an NGO or social project, request digital solutions and technical assistance.', 'modal' => 'supportModal', 'cta' => 'Apply Now'],
                                ] as $card)
                                    <div class="col-md-6 col-lg-3">
                                        <div class="card shadow-sm text-center p-4 card-hover h-100">
                                            <i class="bi bi-{{ $card['icon'] }} display-4 mb-3 text-primary"></i>
                                            <h6 class="fw-bold text-primary">{{ $card['title'] }}</h6>
                                            <p class="small text-muted">{{ $card['text'] }}</p>
                                            <button class="btn btn-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#{{ $card['modal'] }}">
                                                {{ $card['cta'] }}
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>

                    @include('digital-impact.modals.donate')
                    @include('digital-impact.modals.partner')
                    @include('digital-impact.modals.volunteer')
                    @include('digital-impact.modals.support')

                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>
    <script src="https://pay.lenco.co/js/v1/inline.js"></script>
    <script src="{{ asset('js/pages/forms.js') }}"></script>
    <script src="{{ asset('js/pages/digital-impact.js') }}"></script>
@endpush
