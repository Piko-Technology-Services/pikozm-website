{{-- Bootstrap input-group with a leading icon. Params: icon, name, type?, placeholder, required? --}}
<div class="input-group">
    <span class="input-group-text bg-white"><i class="bi bi-{{ $icon }}"></i></span>
    <input type="{{ $type ?? 'text' }}" class="form-control" name="{{ $name }}" id="{{ $id ?? $name }}"
        placeholder="{{ $placeholder }}" @if ($required ?? true) required @endif>
</div>
