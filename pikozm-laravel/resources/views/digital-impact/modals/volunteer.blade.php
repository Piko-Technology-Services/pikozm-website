@include('digital-impact.modals._modal-open', [
    'id' => 'volunteerModal',
    'title' => 'Be Part of the Team',
    'intro' => 'Become part of our mission! Fill in your details to join the Digital Impact Team.',
])

<form id="volunteerForm" class="js-ajax-form" action="{{ route('digital-impact.volunteers.store') }}" enctype="multipart/form-data"
    data-success-title="Application Submitted" data-success-text="Thank you for volunteering! Our team will contact you shortly.">
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'person-fill', 'name' => 'name', 'id' => 'volunteerName', 'placeholder' => 'Full Name'])
        </div>
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'envelope-fill', 'name' => 'email', 'id' => 'volunteerEmail', 'type' => 'email', 'placeholder' => 'Email Address'])
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'telephone-fill', 'name' => 'phone', 'id' => 'volunteerPhone', 'type' => 'tel', 'placeholder' => 'e.g. +260 123 456 789'])
        </div>
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'geo-alt-fill', 'name' => 'country', 'id' => 'volunteerCountry', 'placeholder' => 'Country'])
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            @include('digital-impact.modals._input', ['icon' => 'tools', 'name' => 'skills', 'id' => 'volunteerSkills', 'placeholder' => 'Skills / Area of Contribution'])
        </div>
        <div class="col-md-6">
            @include('digital-impact.modals._select', ['icon' => 'calendar-event', 'name' => 'availability', 'id' => 'volunteerAvailability', 'placeholder' => 'Select Availability', 'options' => $availability])
        </div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="volunteerNRC">Upload Passport / National ID (PDF/JPG/PNG)</label>
            <input class="form-control" type="file" name="nrc" id="volunteerNRC" accept=".pdf,.jpg,.jpeg,.png" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="volunteerResume">Upload Resume/CV (PDF/DOC)</label>
            <input class="form-control" type="file" name="resume" id="volunteerResume" accept=".pdf,.doc,.docx" required>
        </div>
    </div>

    <div class="mb-3">
        @include('digital-impact.modals._textarea', ['name' => 'message', 'id' => 'volunteerMessage', 'placeholder' => 'Tell us about yourself, your motivation, or any questions.'])
    </div>

    <button type="submit" class="btn btn-primary w-100 btn-lg">Sign Up</button>
    <p class="small mb-3 text-primary mt-2">
        All information is kept confidential and used only for Team Member onboarding.
    </p>
</form>

@include('digital-impact.modals._modal-close')
