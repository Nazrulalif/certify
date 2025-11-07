@extends('layouts.form')

@push('title')
    Register - {{ $event->name }}
@endpush

@section('content')
<div class="d-flex flex-center flex-column flex-lg-row-fluid">
    <!-- Wrapper -->
    <div class="w-lg-600px p-4 p-lg-10">

        <!-- Card -->
        <div class="card shadow-sm border-0 rounded-3">

            <!-- Card Header -->
            <div class="card-header bg-secondary text-center py-5 border-0 rounded-top-3 flex-column align-items-center">
                <h1 class="card-title text-dark fs-2 fw-bold mb-1 text-center">{{ $event->name }}</h1>

                @if($event->description)
                    <p class="text-dark-50 mb-0">{{ $event->description }}</p>
                @endif
            </div>

            <!-- Card Body -->
            <div class="card-body p-5 p-lg-10">

                <form id="form" action="{{ route('register.store', $event->slug) }}" method="POST" class="w-100">
                    @csrf

                    @foreach ($formConfig['fields'] as $field)
                        <div class="mb-4">
                            <label class="form-label fw-semibold text-dark">
                                {{ $field['label'] }}
                                @if ($field['required'])
                                    <span class="text-danger">*</span>
                                @endif
                            </label>

                            {{-- TEXT --}}
                            @if ($field['type'] === 'text')
                                <input type="text"
                                    name="{{ $field['name'] }}"
                                    id="{{ $field['name'] }}"
                                    class="form-control form-control-lg @error($field['name']) is-invalid @enderror"
                                    value="{{ old($field['name']) }}"
                                    placeholder="{{ $field['placeholder'] ?? $field['label'] }}"
                                    {{ $field['required'] ? 'required' : '' }}>

                            {{-- EMAIL --}}
                            @elseif ($field['type'] === 'email')
                                <input type="email"
                                    name="{{ $field['name'] }}"
                                    id="{{ $field['name'] }}"
                                    class="form-control form-control-lg @error($field['name']) is-invalid @enderror"
                                    value="{{ old($field['name']) }}"
                                    placeholder="{{ $field['placeholder'] ?? $field['label'] }}"
                                    {{ $field['required'] ? 'required' : '' }}>

                            {{-- NUMBER --}}
                            @elseif ($field['type'] === 'number')
                                <input type="number"
                                    name="{{ $field['name'] }}"
                                    id="{{ $field['name'] }}"
                                    class="form-control form-control-lg @error($field['name']) is-invalid @enderror"
                                    value="{{ old($field['name']) }}"
                                    placeholder="{{ $field['placeholder'] ?? $field['label'] }}"
                                    {{ $field['required'] ? 'required' : '' }}>

                            {{-- DATE --}}
                            @elseif ($field['type'] === 'date')
                                <input type="date"
                                    name="{{ $field['name'] }}"
                                    id="{{ $field['name'] }}"
                                    class="form-control form-control-lg @error($field['name']) is-invalid @enderror"
                                    value="{{ old($field['name']) }}"
                                    {{ $field['required'] ? 'required' : '' }}>

                            {{-- TEXTAREA --}}
                            @elseif ($field['type'] === 'textarea')
                                <textarea
                                    name="{{ $field['name'] }}"
                                    id="{{ $field['name'] }}"
                                    class="form-control form-control-lg @error($field['name']) is-invalid @enderror"
                                    rows="4"
                                    placeholder="{{ $field['placeholder'] ?? $field['label'] }}"
                                    {{ $field['required'] ? 'required' : '' }}>{{ old($field['name']) }}</textarea>

                            {{-- SELECT --}}
                            @elseif ($field['type'] === 'select' && isset($field['options']))
                                <select
                                    name="{{ $field['name'] }}"
                                    id="{{ $field['name'] }}"
                                    class="form-select form-select-lg @error($field['name']) is-invalid @enderror"
                                    {{ $field['required'] ? 'required' : '' }}>
                                    <option value="">Select {{ $field['label'] }}</option>
                                    @foreach ($field['options'] as $option)
                                        <option value="{{ $option }}"
                                            {{ old($field['name']) == $option ? 'selected' : '' }}>
                                            {{ $option }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif

                            @error($field['name'])
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    @endforeach

                    <!-- Submit -->
                    <div class="text-center pt-4">
                        <button type="submit" id="submit_form" class="btn btn-primary btn-lg w-100">
                            <span class="indicator-label">
                                <i class="ki-duotone ki-check-circle fs-2 me-2"></i>
                                Submit Registration
                            </span>
                            <span class="indicator-progress">
                                Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>

                        <div class="mt-4 text-muted small">
                            <span class="text-danger">*</span> Required fields
                        </div>
                    </div>

                </form>

            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-5">
            <span class="text-white fw-semibold fs-6">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </span>
        </div>

    </div>
</div>
@endsection
