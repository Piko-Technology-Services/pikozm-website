@extends('layouts.payment')

@section('title', 'Checkout')
@section('wrap-class', 'wrap')

@section('content')
    <h1>Checkout</h1>

    <div class="summary">
        <div class="name">{{ $item['name'] }}</div>
        <div class="desc">{{ $item['description'] }}</div>
        <div class="amount">ZMW {{ number_format($item['amount'], 2) }}</div>
    </div>

    <form action="{{ route('payments.process') }}" method="POST">
        @csrf
        <input type="hidden" name="mode" value="{{ $mode }}">

        @if ($mode === 'catalog')
            <input type="hidden" name="product" value="{{ $product->slug }}">
            @if ($product->isCustomPriced())
                <input type="hidden" name="amount" value="{{ $item['amount'] }}">
            @endif
        @else
            <input type="hidden" name="item_name" value="{{ $item['name'] }}">
            <input type="hidden" name="item_description" value="{{ $item['description'] }}">
            <input type="hidden" name="amount" value="{{ $item['amount'] }}">
        @endif

        <div class="field">
            <label for="name">Full Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" maxlength="120" required autocomplete="name">
            @error('name') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}" required autocomplete="email">
            @error('email') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="phone">Phone Number</label>
            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" maxlength="40" required autocomplete="tel">
            @error('phone') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="notes">Notes (optional)</label>
            <textarea name="notes" id="notes" rows="3" maxlength="2000">{{ old('notes') }}</textarea>
        </div>

        <button type="submit" class="btn block">Proceed to payment</button>
        <a href="{{ route('payments.index') }}" class="btn-link">Cancel and go back</a>
    </form>
@endsection
