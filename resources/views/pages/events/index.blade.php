@extends('layouts.app')

@section('title', 'Events')

@section('page-title', 'Events')

@section('breadcrumb')
    <li class="breadcrumb-item text-gray-900">Events</li>
@endsection

@section('content')

    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" class="form-control form-control-solid w-250px ps-13" placeholder="Search events..."
                        id="search-event">
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('events.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        Create Event
                    </a>
                </div>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body pt-0">
            @if ($events->count() > 0)
                <div class="row g-6 g-xl-9 mb-6 mb-xl-9">
                    @foreach ($events as $event)
                        <div class="col-md-6 col-xl-4">
                            <div class="card border border-2 border-gray-300 border-hover">
                                <div class="card-header border-0 pt-9">
                                    <div class="card-title m-0">
                                        <div class="symbol symbol-50px w-50px">
                                            <i class="ki-duotone ki-calendar fs-2x text-primary">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                    </div>
                                    <div class="card-toolbar">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-icon btn-active-light-primary"
                                                data-bs-toggle="dropdown">
                                                <i class="ki-duotone ki-category fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                </i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="{{ route('events.show', $event) }}" class="dropdown-item">
                                                    <i class="ki-duotone ki-eye fs-5 me-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                    </i>
                                                    View Details
                                                </a>
                                                <a href="{{ route('events.edit', $event) }}" class="dropdown-item">
                                                    <i class="ki-duotone ki-pencil fs-5 me-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Edit Event
                                                </a>
                                                <a href="{{ route('events.registrations.index', $event) }}"
                                                    class="dropdown-item">
                                                    <i class="ki-duotone ki-people fs-5 me-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                        <span class="path4"></span>
                                                        <span class="path5"></span>
                                                    </i>
                                                    View Registrations
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('events.toggle-registration', $event) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i
                                                            class="ki-duotone ki-toggle-{{ $event->registration_enabled ? 'off' : 'on' }} fs-5 me-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                        </i>
                                                        {{ $event->registration_enabled ? 'Disable' : 'Enable' }}
                                                        Registration
                                                    </button>
                                                </form>
                                                <div class="dropdown-divider"></div>
                                                <button type="button" class="dropdown-item text-danger"
                                                    onclick="confirmDelete(`{{ $event->id }}`)">
                                                    <i class="ki-duotone ki-trash fs-5 me-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                        <span class="path3"></span>
                                                        <span class="path4"></span>
                                                        <span class="path5"></span>
                                                    </i>
                                                    Delete Event
                                                </button>
                                                <form id="delete-form-{{ $event->id }}"
                                                    action="{{ route('events.destroy', $event) }}" method="POST"
                                                    class="d-none">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card-body p-9">
                                    <div class="fs-3 fw-bold text-gray-900 mb-3">{{ $event->name }}</div>

                                    @if ($event->description)
                                        <p class="text-gray-600 fw-semibold fs-6 mb-5">
                                            {{ Str::limit($event->description, 100) }}
                                        </p>
                                    @endif

                                    <div class="d-flex flex-wrap mb-5">
                                        <div class="border border-dashed border-gray-300 rounded py-2 px-3 mb-3 me-3">
                                            <div class="fs-7 text-gray-600 fw-semibold">Template</div>
                                            <div class="fs-6 text-gray-800 fw-bold">{{ $event->template->name }}</div>
                                        </div>
                                        <div class="border border-dashed border-gray-300 rounded py-2 px-3 mb-3">
                                            <div class="fs-7 text-gray-600 fw-semibold">Registrations</div>
                                            <div class="fs-6 text-gray-800 fw-bold">{{ $event->registrations->count() }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-stack mb-3">
                                        <span
                                            class="badge badge-light-{{ $event->registration_enabled ? 'success' : 'danger' }} fw-bold">
                                            {{ $event->registration_enabled ? 'Registration Open' : 'Registration Closed' }}
                                        </span>
                                    </div>

                                    <div class="separator separator-dashed my-5"></div>

                                    <div class="d-flex justify-content-between">
                                        <a href="{{ route('events.show', $event) }}" class="btn btn-sm btn-light-primary">
                                            View Details
                                        </a>
                                        <button type="button" class="btn btn-sm btn-light-info"
                                            onclick="copyPublicUrl('{{ $event->public_url }}')" title="Copy Public URL">
                                            <i class="ki-duotone ki-copy fs-5"></i>
                                            Copy Link
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!--begin::Pagination-->
                <div class="d-flex justify-content-end">
                    <nav aria-label="events pagination">
                        {{ $events->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
                <!--end::Pagination-->
            @else
                <!--begin::Empty State-->
                <div class="text-center py-20">
                    <i class="ki-duotone ki-calendar fs-5tx text-gray-400 mb-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <h3 class="fw-bold text-gray-900 mb-3">No Events Found</h3>
                    <p class="text-gray-600 fs-5 mb-8">Start by creating your first event</p>
                    <a href="{{ route('events.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        Create Event
                    </a>
                </div>
                <!--end::Empty State-->
            @endif
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

@endsection

@push('scripts')
    <script>
        function confirmDelete(eventId) {
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
                    document.getElementById('delete-form-' + eventId).submit();
                }
            });
        }

        function copyPublicUrl(url) {
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

        // Simple search functionality
        document.getElementById('search-event').addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            const cards = document.querySelectorAll('.col-md-6');

            cards.forEach(card => {
                const text = card.textContent.toLowerCase();
                card.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    </script>
@endpush
