@php
    $links = [
        ['label' => 'Home', 'url' => route('home'), 'active' => request()->routeIs('home')],
        ['label' => 'Services', 'url' => route('home').'#services', 'active' => false],
        ['label' => 'Payments', 'url' => route('payments.index'), 'active' => request()->routeIs('payments.*')],
        ['label' => 'Digital Impact', 'url' => route('digital-impact'), 'active' => request()->routeIs('digital-impact*')],
        ['label' => 'Academy', 'url' => route('home').'#', 'active' => false],
        ['label' => 'Talent', 'url' => route('talent.create'), 'active' => request()->routeIs('talent.*')],
        ['label' => 'Let’s Connect', 'url' => route('home').'#contact', 'active' => false],
    ];
@endphp

<nav class="navbar navbar-expand-lg bg-light shadow-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="{{ route('home') }}">
            <img src="{{ asset('images/piko-logo.png') }}" alt="{{ config('piko.name') }} Logo" width="70" height="70"
                class="d-inline-block align-text-top">
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mx-auto fw-semibold">
                @foreach ($links as $link)
                    <li class="nav-item">
                        <a @class(['nav-link', 'active text-primary' => $link['active']]) href="{{ $link['url'] }}"
                            @if ($link['active']) aria-current="page" @endif>{{ $link['label'] }}</a>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</nav>
