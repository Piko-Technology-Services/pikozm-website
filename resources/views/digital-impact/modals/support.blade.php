@include('digital-impact.modals._modal-open', [
    'id' => 'supportModal',
    'title' => 'Apply for Digital Support',
    'intro' => 'NGOs, non-profits, and social projects can request digital solutions and technical assistance. Fill in your details and our team will get in touch.',
])

<form id="supportForm" class="js-ajax-form" action="{{ route('digital-impact.support-requests.store') }}"
    data-success-title="Success" data-success-text="Your support request has been sent.">
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'building', 'name' => 'organization_name', 'id' => 'orgName', 'placeholder' => 'Organization / Project Name'])
        </div>
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'person-fill', 'name' => 'contact_person', 'id' => 'contactPerson', 'placeholder' => 'Contact Person'])
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'envelope-fill', 'name' => 'email', 'id' => 'supportEmail', 'type' => 'email', 'placeholder' => 'Email Address'])
        </div>
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'telephone-fill', 'name' => 'phone', 'id' => 'supportPhone', 'type' => 'tel', 'placeholder' => 'e.g. +260 123 456 789'])
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'geo-alt-fill', 'name' => 'country', 'id' => 'supportCountry', 'placeholder' => 'Country'])
        </div>
        <div class="col-md-6">
            @include('digital-impact.modals._select', ['icon' => 'diagram-3-fill', 'name' => 'organization_type', 'id' => 'supportOrgType', 'placeholder' => 'Select Organization Type', 'options' => $supportOrgTypes])
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            @include('digital-impact.modals._textarea', ['icon' => 'laptop', 'name' => 'support_needs', 'id' => 'supportNeeds', 'required' => true, 'placeholder' => 'Describe the digital support you need (e.g., Website, Donor Management, Training...)'])
        </div>
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'globe', 'name' => 'website', 'id' => 'supportWebsite', 'type' => 'url', 'required' => false, 'placeholder' => 'Organization Website (optional)'])
        </div>
    </div>

    <div class="mb-3">
        @include('digital-impact.modals._textarea', ['name' => 'message', 'id' => 'supportMessage', 'placeholder' => 'Additional information, questions, or comments (optional)'])
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">Apply for Support</button>
    <p class="small mb-3 text-primary mt-2">
        Our team will review your application and contact you for next steps.
    </p>
</form>

@include('digital-impact.modals._modal-close')
