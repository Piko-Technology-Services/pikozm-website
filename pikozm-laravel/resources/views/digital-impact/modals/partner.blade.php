@include('digital-impact.modals._modal-open', [
    'id' => 'partnerModal',
    'title' => 'Partner with Piko Digital Impact',
    'intro' => 'Join us as a partner to drive digital transformation for social good. We welcome CSR, project funding, resource support, and other partnership models.',
])

<form id="partnerForm" class="js-ajax-form" action="{{ route('digital-impact.partners.store') }}"
    data-success-title="Submitted" data-success-text="Your partnership request has been received.">
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'person-fill', 'name' => 'organization_name', 'id' => 'partnerName', 'placeholder' => 'Organization / Full Name'])
        </div>
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'envelope-fill', 'name' => 'email', 'id' => 'partnerEmail', 'type' => 'email', 'placeholder' => 'Email Address'])
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-12">
            @include('digital-impact.modals._input', ['icon' => 'person-badge-fill', 'name' => 'contact_person', 'id' => 'partnerContactPerson', 'placeholder' => 'Contact Person Fullnames'])
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'telephone-fill', 'name' => 'phone', 'id' => 'partnerPhone', 'type' => 'tel', 'placeholder' => 'e.g. +260 123 456 789'])
        </div>
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'geo-alt-fill', 'name' => 'country', 'id' => 'partnerCountry', 'placeholder' => 'Country'])
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            @include('digital-impact.modals._select', ['icon' => 'diagram-3-fill', 'name' => 'partnership_type', 'id' => 'partnerType', 'placeholder' => 'Select Partnership Type', 'options' => $partnershipTypes])
        </div>
        <div class="col-md-6">
            @include('digital-impact.modals._select', ['icon' => 'building', 'name' => 'organization_type', 'id' => 'partnerOrgType', 'placeholder' => 'Select Organization Type', 'options' => $partnerOrgTypes])
        </div>
    </div>

    <div class="mb-3">
        @include('digital-impact.modals._textarea', ['name' => 'message', 'id' => 'partnerMessage', 'placeholder' => 'Tell us about your partnership interest, proposal, or any questions.'])
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">Submit Partnership</button>
    <p class="small mb-3 text-primary mt-2">
        We value every partnership. Our team will review your proposal and get in touch to discuss collaboration opportunities.
    </p>
</form>

@include('digital-impact.modals._modal-close')
