<div class="input-group">
    <span class="input-group-text bg-white"><i class="bi bi-{{ $icon ?? 'chat-dots-fill' }}"></i></span>
    <textarea class="form-control" name="{{ $name }}" id="{{ $id ?? $name }}" rows="3"
        placeholder="{{ $placeholder }}" @if ($required ?? false) required @endif></textarea>
</div>
