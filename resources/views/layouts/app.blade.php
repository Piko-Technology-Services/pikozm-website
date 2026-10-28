<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('piko.name'))</title>

    <meta name="description" content="@yield('description', 'Piko Technology Services offers smart business solutions including Business Development, HR & Finance Systems, Web Development, Training, Design & Printing for Zambian SMEs and Enterprises.')">
    <meta name="keywords" content="@yield('keywords', 'Piko Technology Services, Business Development, Information & (Human Resource) HR Systems, Accounting Software, Web Development, Graphic Design, Software Development, Printing, Training, Zambia')">
    <meta name="author" content="{{ config('piko.name') }}">
    @stack('meta')

    <link rel="icon" href="{{ asset('images/piko-logo.png') }}">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@100;300;400;700;900&display=swap" rel="stylesheet">

    {{-- TemplateMo 567 Nomad Force | https://templatemo.com/tm-567-nomad-force --}}
    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('css/magnific-popup.css') }}" rel="stylesheet">
    <link href="{{ asset('css/aos.css') }}" rel="stylesheet">
    <link href="{{ asset('css/templatemo-nomad-force.css') }}" rel="stylesheet">
    @stack('styles')

    @stack('head')
</head>

<body>

    <main>
        {{-- Full-bleed content that sits above the sticky navbar (e.g. the home hero video) --}}
        @yield('hero')

        @include('partials.navbar')

        @yield('content')
    </main>

    @include('partials.footer')

    <script src="{{ asset('js/jquery.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('js/jquery.sticky.js') }}"></script>
    <script src="{{ asset('js/aos.js') }}"></script>
    <script src="{{ asset('js/jquery.magnific-popup.min.js') }}"></script>
    <script src="{{ asset('js/magnific-popup-options.js') }}"></script>
    <script src="{{ asset('js/scrollspy.min.js') }}"></script>
    <script src="{{ asset('js/custom.js') }}"></script>
    @stack('scripts')

</body>

</html>
