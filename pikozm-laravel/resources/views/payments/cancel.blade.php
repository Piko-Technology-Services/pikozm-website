@extends('layouts.payment')

@section('title', 'Payment Cancelled')

@section('content')
    <h1>Payment cancelled</h1>
    <p>Your payment was not completed. No charges were made.</p>

    @if ($order)
        <p class="ref">Reference: {{ $order->company_ref }}</p>
    @endif

    <a href="{{ route('payments.index') }}" class="btn mt">Back to payments</a>
@endsection
