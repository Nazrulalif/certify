@props([
    'label' => '',
    'name' => '',
    'value' => '',
    'placeholder' => '',
    'rows' => 3,
    'required' => false,
    'help' => '',
    'size' => '', // 'sm' or 'lg' or empty for default
])

<div class="fv-row{{ $size === 'sm' ? ' mb-5' : ' mb-8' }}">
    @if ($label)
        <label
            class="{{ $required ? 'required' : '' }} form-label{{ $size ? ' fw-bold fs-7 mb-2' : '' }}">{{ $label }}</label>
    @endif
    <textarea name="{{ $name }}" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}
        rows="{{ $rows }}"
        class="form-control{{ $size ? ' form-control-' . $size : '' }} @error($name) is-invalid @enderror"
        {{ $attributes }}>{{ old($name, $value) }}</textarea>

    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif

    @error($name)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
