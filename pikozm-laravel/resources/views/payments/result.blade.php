@extends('layouts.payment')

@section('title', 'Payment Status')

@section('content')
    <h1 class="{{ $verified ? 'ok' : 'no' }}">{{ $title }}</h1>
    <p>{{ $message }}</p>

    @if ($verified)
        <p>Thank you — we'll be in touch shortly with next steps or access details.</p>
    @else
        <p>If you believe this is a mistake, please contact us at
            <a href="mailto:{{ config('piko.email') }}" style="color:inherit">{{ config('piko.email') }}</a>
            and we'll help sort it out.</p>
    @endif

    @if ($order)
        <p class="ref">Payment reference: {{ $order->company_ref }}</p>
    @endif

    <a href="{{ route('payments.index') }}" class="btn mt">Back to payments</a>
@endsection
