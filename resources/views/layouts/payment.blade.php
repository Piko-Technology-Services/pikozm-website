<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

{{-- Minimal dark layout used by the DPO checkout and result pages. --}}
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>@yield('title') - {{ config('piko.name') }}</title>
    <link rel="icon" href="{{ asset('images/piko-logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Lora:wght@600&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink-900: #101B26; --ink-800: #13212E; --panel: #1C3145;
            --panel-line: #2C4256; --paper: #EDF1F4; --paper-dim: #B6C2CC;
            --copper: #C97A3B; --copper-light: #E0985C; --teal: #4F9C82;
            --danger: #D9754A; --radius-sm: 4px;
        }
        * { box-sizing: border-box; }
        body { margin: 0; background: var(--ink-900); color: var(--paper); font-family: 'IBM Plex Sans', sans-serif; }
        .wrap { max-width: 520px; margin: 60px auto; padding: 0 20px 60px; }
        .wrap.center { max-width: 480px; margin-top: 90px; text-align: center; }
        .wordmark { font-family: 'Lora', serif; font-weight: 600; text-align: center; display: block; margin-bottom: 36px; text-decoration: none; color: var(--paper); }
        h1 { font-family: 'Lora', serif; font-size: 1.45rem; margin: 0 0 18px; }
        h1.ok { color: var(--teal); }
        h1.no { color: var(--danger); }
        p { color: var(--paper-dim); }
        .summary { background: var(--panel); border: 1px solid var(--panel-line); border-radius: var(--radius-sm); padding: 20px 22px; margin-bottom: 28px; }
        .summary .name { font-weight: 600; margin-bottom: 4px; }
        .summary .desc { color: var(--paper-dim); font-size: .9rem; margin-bottom: 12px; }
        .summary .amount { font-family: 'IBM Plex Mono', monospace; font-weight: 600; font-size: 1.3rem; }
        .ref { font-family: 'IBM Plex Mono', monospace; font-size: .85rem; color: var(--paper-dim); }
        .field { margin-bottom: 18px; }
        .field label { display: block; font-size: .85rem; color: var(--paper-dim); margin-bottom: 6px; }
        .field input, .field textarea { width: 100%; background: var(--ink-800); border: 1px solid var(--panel-line); border-radius: var(--radius-sm); color: var(--paper); padding: 11px 12px; font-family: 'IBM Plex Sans', sans-serif; font-size: .95rem; }
        .field input:focus, .field textarea:focus { outline: 2px solid var(--copper-light); outline-offset: 1px; }
        .field .error { color: var(--danger); font-size: .8rem; margin-top: 4px; }
        .btn { display: inline-block; text-align: center; font-weight: 600; font-size: .95rem; padding: 12px 22px; border-radius: var(--radius-sm); border: 1px solid var(--copper); background: var(--copper); color: var(--ink-900); cursor: pointer; text-decoration: none; }
        .btn.block { display: block; width: 100%; }
        .btn:hover { background: var(--copper-light); border-color: var(--copper-light); }
        .btn-link { display: block; text-align: center; margin-top: 12px; color: var(--paper-dim); font-size: .85rem; text-decoration: none; }
        .btn-link:hover { color: var(--paper); }
        .mt { margin-top: 24px; }
    </style>
</head>

<body>
    <div class="@yield('wrap-class', 'wrap center')">
        <a href="{{ route('payments.index') }}" class="wordmark">{{ config('piko.name') }}</a>
        @yield('content')
    </div>
</body>

</html>
