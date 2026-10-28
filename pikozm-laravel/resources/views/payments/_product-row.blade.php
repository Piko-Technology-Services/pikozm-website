{{-- One row on the /payments price list. Expects $product and $buttonLabel. --}}
<div class="price-item row align-items-center" data-aos="fade-up">
    <div class="col-lg-8 col-12">
        <h5 class="mb-1">{{ $product->name }}</h5>
        <p class="text-secondary mb-1">{{ $product->description }}</p>
        @if ($product->note)
            <p class="item-note mb-0">{{ $product->note }}</p>
        @endif
    </div>
    <div class="col-lg-4 col-12 text-lg-end mt-3 mt-lg-0">
        @if ($product->isCustomPriced())
            <form action="{{ route('payments.checkout.product', $product) }}" method="GET"
                class="d-flex justify-content-lg-end align-items-center gap-2">
                <input type="number" name="amount" min="1" step="0.01" class="form-control form-control-sm"
                    style="width:130px;" placeholder="Amount (ZMW)" required>
                <button type="submit" class="btn btn-dark btn-sm">{{ $buttonLabel }}</button>
            </form>
        @else
            <div class="price-tag mb-2">
                ZMW {{ number_format((float) $product->price, 2) }}
                @if ($product->cycleLabel())
                    <span class="cycle">{{ $product->cycleLabel() }}</span>
                @endif
            </div>
            <a href="{{ route('payments.checkout.product', $product) }}" class="btn btn-dark btn-sm px-4">{{ $buttonLabel }}</a>
        @endif
    </div>
</div>
