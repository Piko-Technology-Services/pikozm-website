<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-primary">
                <h5 class="modal-title text-light" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close text-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="mb-4 text-muted fs-6">{{ $intro }}</p>
