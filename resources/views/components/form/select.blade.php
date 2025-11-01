@props([
    'label' => '',
    'name' => '',
    'placeholder' => 'Select an option',
    'required' => false,
    'options' => [],
    'selected' => '',
    'useSelect2' => true,
    'help' => '',
    'size' => '', // 'sm' or 'lg' or empty for default
])

<div class="fv-row{{ $size === 'sm' ? ' mb-5' : ' mb-8' }}">
    @if ($label)
        <label
            class="{{ $required ? 'required' : '' }} form-label{{ $size ? ' fw-bold fs-7 mb-2' : '' }}">{{ $label }}</label>
    @endif
    <select name="{{ $name }}"
        class="form-select{{ $size ? ' form-select-' . $size : '' }} @error($name) is-invalid @enderror"
        {{ $useSelect2 ? 'data-control=select2' : '' }} {{ $useSelect2 ? 'data-placeholder=' . $placeholder : '' }}
        {{ $required ? 'required' : '' }} {{ $attributes }}>
        @if (!count($options))
            {{ $slot }}
        @else
            <option value="">{{ $placeholder }}</option>
            @foreach ($options as $value => $text)
                <option value="{{ $value }}" {{ old($name, $selected) == $value ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
        @endif
    </select>

    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif

    @error($name)
        <span class="invalid-feedback" role="alert">
            <strong>{{ $message }}</strong>
        </span>
    @enderror
</div>
