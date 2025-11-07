@extends('layouts.app')

@section('title', 'Event Details')

@section('page-title', $event->name)

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('events.index') }}" class="text-muted text-hover-primary">Events</a>
    </li>
    <li class="breadcrumb-item text-gray-900">{{ $event->name }}</li>
@endsection

@section('content')

    <div class="row g-6 g-xl-9">
        <!--begin::Event Info-->
        <div class="col-lg-8">
            <div class="card mb-6">
                <div class="card-header">
                    <h3 class="card-title">Event Information</h3>
                    <div class="card-toolbar">
                        <a href="{{ route('events.edit', $event) }}" class="btn btn-sm btn-primary">
                            <i class="ki-duotone ki-pencil fs-5">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Edit Event
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-7">
                        <label class="fw-bold text-gray-600 fs-7">Event Name</label>
                        <div class="fw-bold text-gray-800 fs-5">{{ $event->name }}</div>
                    </div>

                    @if ($event->description)
                        <div class="mb-7">
                            <label class="fw-bold text-gray-600 fs-7">Description</label>
                            <div class="text-gray-800">{{ $event->description }}</div>
                        </div>
                    @endif

                    <div class="mb-7">
                        <label class="fw-bold text-gray-600 fs-7">Template</label>
                        <div class="fw-bold text-gray-800 fs-6">{{ $event->template->name }}</div>
                    </div>

                    <div class="mb-7">
                        <label class="fw-bold text-gray-600 fs-7">Created By</label>
                        <div class="fw-bold text-gray-800 fs-6">{{ $event->creator->name }}</div>
                    </div>

                    <div class="mb-7">
                        <label class="fw-bold text-gray-600 fs-7">Created At</label>
                        <div class="fw-bold text-gray-800 fs-6">{{ $event->created_at->format('F d, Y h:i A') }}</div>
                    </div>

                    <div class="mb-7">
                        <label class="fw-bold text-gray-600 fs-7">Registration Status</label>
                        <div>
                            <span class="badge badge-light-{{ $event->registration_enabled ? 'success' : 'danger' }} fs-6">
                                {{ $event->registration_enabled ? 'Open' : 'Closed' }}
                            </span>
                        </div>
                    </div>

                    <div class="mb-7">
                        <label class="fw-bold text-gray-600 fs-7">Public Registration URL</label>
                        <div class="d-flex align-items-center">
                            <input type="text" class="form-control form-control-sm me-3"
                                value="{{ $event->public_url }}" readonly>
                            <button type="button" class="btn btn-sm btn-light-primary" onclick="copyPublicUrl()">
                                <i class="ki-duotone ki-copy fs-5"></i>
                                Copy
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!--begin::Registration Fields-->
            <div class="card mb-6">
                <div class="card-header">
                    <h3 class="card-title">Registration Form Fields</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-row-bordered gy-5">
                            <thead>
                                <tr class="fw-bold text-gray-600 fs-7">
                                    <th>Field Label</th>
                                    <th>Field Name</th>
                                    <th>Type</th>
                                    <th>In Cert</th>
                                    <th>In Form</th>
                                    <th>Required</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($event->template->fields as $field)
                                    <tr>
                                        <td class="fw-bold">{{ $field->field_label }}</td>
                                        <td class="text-gray-600">{{ $field->field_name }}</td>
                                        <td>
                                            <span class="badge badge-light-info">{{ ucfirst($field->field_type) }}</span>
                                        </td>
                                        <td>
                                            @if ($field->show_in_cert)
                                                <i class="ki-duotone ki-check-circle fs-2 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            @else
                                                <i class="ki-duotone ki-cross-circle fs-2 text-gray-400">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($field->show_in_form)
                                                <i class="ki-duotone ki-check-circle fs-2 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            @else
                                                <i class="ki-duotone ki-cross-circle fs-2 text-gray-400">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($field->is_required)
                                                <i class="ki-duotone ki-check-circle fs-2 text-success">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            @else
                                                <i class="ki-duotone ki-cross-circle fs-2 text-gray-400">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                </i>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-gray-600">No fields configured</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Event Info-->

        <!--begin::Stats-->
        <div class="col-lg-4">
            <div class="card mb-6">
                <div class="card-header">
                    <h3 class="card-title">Statistics</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column">
                        <div class="d-flex align-items-center border border-dashed border-gray-300 rounded p-5 mb-5">
                            <div class="symbol symbol-50px me-5">
                                <span class="symbol-label bg-light-primary">
                                    <i class="ki-duotone ki-people fs-2x text-primary">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                        <span class="path5"></span>
                                    </i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-gray-600 fw-semibold fs-7">Total Registrations</div>
                                <div class="fw-bold text-gray-800 fs-2">{{ $event->registrations->count() }}</div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center border border-dashed border-gray-300 rounded p-5 mb-5">
                            <div class="symbol symbol-50px me-5">
                                <span class="symbol-label bg-light-success">
                                    <i class="ki-duotone ki-check-circle fs-2x text-success">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-gray-600 fw-semibold fs-7">Approved</div>
                                <div class="fw-bold text-gray-800 fs-2">
                                    {{ $event->registrations->where('status', 'approved')->count() }}
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center border border-dashed border-gray-300 rounded p-5 mb-5">
                            <div class="symbol symbol-50px me-5">
                                <span class="symbol-label bg-light-warning">
                                    <i class="ki-duotone ki-time fs-2x text-warning">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="text-gray-600 fw-semibold fs-7">Pending</div>
                                <div class="fw-bold text-gray-800 fs-2">
                                    {{ $event->registrations->where('status', 'pending')->count() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!--begin::Actions-->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Actions</h3>
                </div>
                <div class="card-body">
                    <a href="{{ route('events.registrations.index', $event) }}" class="btn btn-light-primary w-100 mb-3">
                        <i class="ki-duotone ki-people fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                            <span class="path5"></span>
                        </i>
                        View All Registrations
                    </a>

                    <form action="{{ route('events.toggle-registration', $event) }}" method="POST" class="mb-3">
                        @csrf
                        <button type="submit"
                            class="btn btn-light-{{ $event->registration_enabled ? 'danger' : 'success' }} w-100">
                            <i class="ki-duotone ki-toggle-{{ $event->registration_enabled ? 'off' : 'on' }} fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            {{ $event->registration_enabled ? 'Close' : 'Open' }} Registration
                        </button>
                    </form>

                    <a href="{{ route('register.show', $event->slug) }}" target="_blank"
                        class="btn btn-light-info w-100 mb-3">
                        <i class="ki-duotone ki-mouse-square fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        Open Public Form
                    </a>

                    <div class="separator my-5"></div>

                    <button type="button" class="btn btn-light-danger w-100" onclick="confirmDelete()">
                        <i class="ki-duotone ki-trash fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                            <span class="path5"></span>
                        </i>
                        Delete Event
                    </button>

                    <form id="delete-form" action="{{ route('events.destroy', $event) }}" method="POST"
                        class="d-none">
                        @csrf
                        @method('DELETE')
                    </form>
                </div>
            </div>
        </div>
        <!--end::Stats-->
    </div>

@endsection

@push('scripts')
    <script>
        function copyPublicUrl() {
            const input = document.querySelector('input[readonly]');
            input.select();
            navigator.clipboard.writeText(input.value).then(() => {
                Swal.fire({
                    icon: 'success',
                    title: 'Copied!',
                    text: 'Public registration URL copied to clipboard',
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        }

        function confirmDelete() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the event and all associated registrations!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form').submit();
                }
            });
        }
    </script>
@endpush
