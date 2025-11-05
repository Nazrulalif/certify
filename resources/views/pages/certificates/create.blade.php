@extends('layouts.app')

@section('title', 'Generate Certificate')

@section('page-title', 'Generate Certificate')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('certificates.index') }}" class="text-muted text-hover-primary">Certificates</a>
    </li>
    <li class="breadcrumb-item text-gray-900">Generate</li>
@endsection

@section('content')

    <div class="row g-6">
        <!--begin::Method 1: From Registrations-->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">From Registrations</h3>
                </div>
                <div class="card-body">
                    <div class="mb-5">
                        <i class="ki-duotone ki-profile-user fs-5x text-primary mb-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                        <p class="text-gray-600 fw-semibold fs-6">
                            Generate certificates for registered participants from an event.
                        </p>
                    </div>
                    <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal"
                        data-bs-target="#modal-from-registrations">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        Select Event
                    </button>
                </div>
            </div>
        </div>

        <!--begin::Method 2: Manual Entry-->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Manual Entry</h3>
                </div>
                <div class="card-body">
                    <div class="mb-5">
                        <i class="ki-duotone ki-document fs-5x text-success mb-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <p class="text-gray-600 fw-semibold fs-6">
                            Manually enter recipient information to generate a single certificate.
                        </p>
                    </div>
                    <button type="button" class="btn btn-success w-100" data-bs-toggle="modal"
                        data-bs-target="#modal-manual-entry">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        Enter Details
                    </button>
                </div>
            </div>
        </div>

        <!--begin::Method 3: Excel Import-->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h3 class="card-title">Excel Import</h3>
                </div>
                <div class="card-body">
                    <div class="mb-5">
                        <i class="ki-duotone ki-file-up fs-5x text-info mb-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <p class="text-gray-600 fw-semibold fs-6">
                            Import participant data from Excel file for bulk certificate generation.
                        </p>
                    </div>
                    <button type="button" class="btn btn-info w-100" data-bs-toggle="modal"
                        data-bs-target="#modal-excel-import">
                        <i class="ki-duotone ki-file-up fs-2"></i>
                        Upload Excel
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('pages.certificates.partials.modal-from-registrations')
    @include('pages.certificates.partials.modal-manual-entry')
    @include('pages.certificates.partials.modal-excel-import')

@endsection

@push('scripts')
    <script>
        window.eventsData = {!! json_encode(
            $events->mapWithKeys(function ($event) {
                    return [
                        $event->id => [
                            'id' => $event->id,
                            'fields' => $event->template->formFields->map(function ($field) {
                                    return [
                                        'field_name' => $field->field_name,
                                        'field_label' => $field->field_label,
                                        'field_type' => $field->field_type,
                                        'required' => $field->required,
                                        'options' => $field->options,
                                    ];
                                })->toArray(),
                        ],
                    ];
                })->toArray(),
        ) !!};
    </script>
    <script src="{{ asset('web/js/certificates/create.js') }}"></script>
@endpush
