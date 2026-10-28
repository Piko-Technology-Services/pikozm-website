@include('digital-impact.modals._modal-open', [
    'id' => 'donateModal',
    'title' => 'Support the Cause',
    'intro' => 'Every contribution directly funds technology solutions with measurable social impact.',
])

<form id="donateForm" action="{{ route('digital-impact.donations.initiate') }}" data-verify-url="{{ route('digital-impact.donations.verify') }}">
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'person-fill', 'name' => 'name', 'id' => 'donorName', 'placeholder' => 'Name of Sponsor / Organization'])
        </div>
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'envelope-fill', 'name' => 'email', 'id' => 'donorEmail', 'type' => 'email', 'placeholder' => 'Email Address'])
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-diagram-3-fill"></i></span>
                <select class="form-select" name="focus_area" id="donorProject">
                    @foreach ($focusAreas as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text bg-white"><i class="bi bi-cash-stack ms-2"></i></span>
                <input type="number" class="form-control" name="amount" id="donorAmount" placeholder="Amount" min="1" step="0.01" required>
                <select class="form-select w-auto" name="currency" id="donorCurrency">
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency }}" @selected($loop->first)>{{ $currency }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="mb-3">
        @include('digital-impact.modals._textarea', ['name' => 'message', 'id' => 'donorMessage', 'placeholder' => 'Tell us something we should be reminded of...'])
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">Support Digital Impact</button>

    <p class="small mb-3 text-primary mt-2">
        Your contribution supports sustainable digital systems for NGOs and social projects, enabling them to operate
        more transparently, efficiently, and at scale.
    </p>
</form>

@include('digital-impact.modals._modal-close')
