@extends('layouts.app')

@section('title', 'Event Registrations')

@section('page-title', 'Event Registrations')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('events.index') }}" class="text-muted text-hover-primary">Events</a>
    </li>
    <li class="breadcrumb-item">
        <a href="{{ route('events.show', $event) }}" class="text-muted text-hover-primary">{{ $event->name }}</a>
    </li>
    <li class="breadcrumb-item text-gray-900">Registrations</li>
@endsection

@section('content')

    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <!--begin::Card title-->
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-kt-registrations-table-filter="search"
                        class="form-control form-control-solid w-250px ps-13" placeholder="Search registrations...">
                </div>
            </div>
            <!--end::Card title-->

            <!--begin::Card toolbar-->
            <div class="card-toolbar">
                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-registrations-toolbar="base">
                    <button type="button" class="btn btn-sm btn-light-primary me-3" onclick="copyPublicUrl()">
                        <i class="ki-duotone ki-copy fs-5"></i>
                        Copy Registration Link
                    </button>
                    <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-light-secondary">
                        <i class="ki-duotone ki-arrow-left fs-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Back to Event
                    </a>
                </div>
                <!--end::Toolbar-->

                <!--begin::Group actions-->
                <div class="d-flex justify-content-end align-items-center d-none" data-kt-registrations-toolbar="selected">
                    <div class="fw-bold me-5">
                        <span class="me-2" data-kt-registrations-selected-count></span> Selected
                    </div>
                    <button type="button" class="btn btn-danger" data-kt-registrations-table-select="delete_selected"
                        data-url="{{ route('events.registrations.bulk-destroy', $event) }}">
                        Delete Selected
                    </button>
                </div>
                <!--end::Group actions-->
            </div>
            <!--end::Card toolbar-->
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body pt-0">
            <!--begin::Table-->
            <table class="table align-middle table-row-dashed fs-6 gy-5" id="registrations-table">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2"></th>
                        <th class="min-w-50px">ID</th>
                        <th class="min-w-200px">Registration Data</th>
                        <th class="min-w-100px">Status</th>
                        <th class="min-w-125px">Registered At</th>
                        <th class="text-end min-w-100px">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold">
                </tbody>
            </table>
            <!--end::Table-->
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

    <!--begin::Modal for Registration Details-->
    <div class="modal fade" id="detailsModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registration Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detailsContent">
                    <!-- Details will be populated here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Modal-->

@endsection

@push('scripts')
    <script src="{{ asset('web/js/registrations/index-table.js') }}"></script>
    <script>
        function viewDetails(data) {
            let html = '<div class="table-responsive"><table class="table table-row-bordered">';

            for (let [key, value] of Object.entries(data)) {
                html += `
                    <tr>
                        <td class="fw-bold text-gray-700" style="width: 40%;">${key.replace(/_/g, ' ').toUpperCase()}</td>
                        <td class="text-gray-600">${value || '-'}</td>
                    </tr>
                `;
            }

            html += '</table></div>';

            document.getElementById('detailsContent').innerHTML = html;

            const modal = new bootstrap.Modal(document.getElementById('detailsModal'));
            modal.show();
        }

        function copyPublicUrl() {
            const url = "{{ $event->public_url }}";
            navigator.clipboard.writeText(url).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Public registration URL copied to clipboard',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        }
    </script>
@endpush
