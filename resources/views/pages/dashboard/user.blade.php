@extends('layouts.app')

@section('title', 'Dashboard')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Dashboard</li>
@endsection

@section('content')

<!--begin::Row-->
<div class="row g-5 g-xl-10 mb-5 mb-xl-5">
    <!--begin::Col-->
    <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 h-100">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #F1416C;background-image:url('{{ asset('assets/media/patterns/vector-1.png') }}')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">0</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">My Templates</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        <span>Create your first template</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Col-->

    <!--begin::Col-->
    <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 h-100">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #7239EA;background-image:url('{{ asset('assets/media/patterns/vector-1.png') }}')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">0</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">My Events</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        <span>0 Active Events</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Col-->

    <!--begin::Col-->
    <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 h-100">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #50CD89;background-image:url('{{ asset('assets/media/patterns/vector-1.png') }}')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">0</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Certificates Generated</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        <span>This Month</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Col-->

    <!--begin::Col-->
    <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 h-100">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #FFC700;background-image:url('{{ asset('assets/media/patterns/vector-1.png') }}')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">0</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Registrations</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        <span>Across All Events</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->

<!--begin::Row-->
<div class="row g-5 g-xl-10 mb-5 mb-xl-10">
    <!--begin::Col-->
    <div class="col-md-12">
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">Getting Started</span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-6">Follow these steps to create your first certificate</span>
                </h3>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <!--begin::Timeline item-->
                    <div class="timeline-item">
                        <div class="timeline-line w-40px"></div>
                        <div class="timeline-icon symbol symbol-circle symbol-40px me-4">
                            <div class="symbol-label bg-light">
                                <i class="ki-duotone ki-document fs-2 text-gray-500">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                        <div class="timeline-content mb-10 mt-n1">
                            <div class="pe-3 mb-5">
                                <div class="fs-5 fw-semibold mb-2">Step 1: Create a Template</div>
                                <div class="d-flex align-items-center mt-1 fs-6">
                                    <div class="text-muted me-2 fs-7">Upload a background image and add text fields for your certificate</div>
                                </div>
                            </div>
                            <div class="overflow-auto pb-5">
                                <a href="{{route('templates.index')}}" class="btn btn-sm btn-primary">Create Template</a>
                            </div>
                        </div>
                    </div>
                    <!--end::Timeline item-->

                    <!--begin::Timeline item-->
                    <div class="timeline-item">
                        <div class="timeline-line w-40px"></div>
                        <div class="timeline-icon symbol symbol-circle symbol-40px me-4">
                            <div class="symbol-label bg-light">
                                <i class="ki-duotone ki-calendar fs-2 text-gray-500">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                        <div class="timeline-content mb-10 mt-n1">
                            <div class="pe-3 mb-5">
                                <div class="fs-5 fw-semibold mb-2">Step 2: Create an Event</div>
                                <div class="d-flex align-items-center mt-1 fs-6">
                                    <div class="text-muted me-2 fs-7">Set up your event and build a custom registration form</div>
                                </div>
                            </div>
                            <div class="overflow-auto pb-5">
                                <a href="#" class="btn btn-sm btn-light-primary">Create Event</a>
                            </div>
                        </div>
                    </div>
                    <!--end::Timeline item-->

                    <!--begin::Timeline item-->
                    <div class="timeline-item">
                        <div class="timeline-line w-40px"></div>
                        <div class="timeline-icon symbol symbol-circle symbol-40px me-4">
                            <div class="symbol-label bg-light">
                                <i class="ki-duotone ki-award fs-2 text-gray-500">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </div>
                        </div>
                        <div class="timeline-content mb-10 mt-n1">
                            <div class="pe-3 mb-5">
                                <div class="fs-5 fw-semibold mb-2">Step 3: Generate Certificates</div>
                                <div class="d-flex align-items-center mt-1 fs-6">
                                    <div class="text-muted me-2 fs-7">Generate certificates from registrations, Excel upload, or manual entry</div>
                                </div>
                            </div>
                            <div class="overflow-auto pb-5">
                                <a href="#" class="btn btn-sm btn-light-primary">Generate Certificates</a>
                            </div>
                        </div>
                    </div>
                    <!--end::Timeline item-->
                </div>
            </div>
        </div>
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->

@endsection

@push('scripts')

@endpush
