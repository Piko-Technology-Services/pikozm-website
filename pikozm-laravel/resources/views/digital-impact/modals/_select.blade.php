{{-- Bootstrap input-group select. Params: icon, name, placeholder, options (value => label), required? --}}
<div class="input-group">
    <span class="input-group-text bg-white"><i class="bi bi-{{ $icon }}"></i></span>
    <select class="form-select" name="{{ $name }}" id="{{ $id ?? $name }}" @if ($required ?? true) required @endif>
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $value => $label)
            <option value="{{ $value }}">{{ $label }}</option>
        @endforeach
    </select>
</div>
