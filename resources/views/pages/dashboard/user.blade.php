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
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $myTemplates }}</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">My Templates</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        <span>{{ $myTemplates > 0 ? 'Certificate Designs' : 'Create your first template' }}</span>
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
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $myEvents }}</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">My Events</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        <span>{{ $activeEvents }} Active Event{{ $activeEvents != 1 ? 's' : '' }}</span>
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
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $myCertificates }}</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Certificates Generated</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        <span>{{ $certificatesThisMonth }} This Month</span>
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
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $totalRegistrations }}</span>
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
    <div class="col-md-12 col-lg-12 col-xl-6">
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
                                <i class="ki-duotone ki-document fs-2 text-{{ $myTemplates > 0 ? 'success' : 'gray-500' }}">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                        <div class="timeline-content mb-10 mt-n1">
                            <div class="pe-3 mb-5">
                                <div class="fs-5 fw-semibold mb-2">
                                    Step 1: Create a Template
                                    @if($myTemplates > 0)
                                        <span class="badge badge-success">{{ $myTemplates }} Created</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center mt-1 fs-6">
                                    <div class="text-muted me-2 fs-7">Upload a background image and add text fields for your certificate</div>
                                </div>
                            </div>
                            <div class="overflow-auto pb-5">
                                <a href="{{route('templates.index')}}" class="btn btn-sm btn-{{ $myTemplates > 0 ? 'light-' : '' }}primary">
                                    {{ $myTemplates > 0 ? 'View Templates' : 'Create Template' }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <!--end::Timeline item-->

                    <!--begin::Timeline item-->
                    <div class="timeline-item">
                        <div class="timeline-line w-40px"></div>
                        <div class="timeline-icon symbol symbol-circle symbol-40px me-4">
                            <div class="symbol-label bg-light">
                                <i class="ki-duotone ki-calendar fs-2 text-{{ $myEvents > 0 ? 'success' : 'gray-500' }}">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </div>
                        </div>
                        <div class="timeline-content mb-10 mt-n1">
                            <div class="pe-3 mb-5">
                                <div class="fs-5 fw-semibold mb-2">
                                    Step 2: Create an Event
                                    @if($myEvents > 0)
                                        <span class="badge badge-success">{{ $myEvents }} Created</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center mt-1 fs-6">
                                    <div class="text-muted me-2 fs-7">Set up your event and build a custom registration form</div>
                                </div>
                            </div>
                            <div class="overflow-auto pb-5">
                                <a href="{{ route('events.index') }}" class="btn btn-sm btn-{{ $myEvents > 0 ? 'light-' : '' }}primary">
                                    {{ $myEvents > 0 ? 'View Events' : 'Create Event' }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <!--end::Timeline item-->

                    <!--begin::Timeline item-->
                    <div class="timeline-item">
                        <div class="timeline-line w-40px"></div>
                        <div class="timeline-icon symbol symbol-circle symbol-40px me-4">
                            <div class="symbol-label bg-light">
                                <i class="ki-duotone ki-award fs-2 text-{{ $myCertificates > 0 ? 'success' : 'gray-500' }}">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </div>
                        </div>
                        <div class="timeline-content mb-10 mt-n1">
                            <div class="pe-3 mb-5">
                                <div class="fs-5 fw-semibold mb-2">
                                    Step 3: Generate Certificates
                                    @if($myCertificates > 0)
                                        <span class="badge badge-success">{{ $myCertificates }} Generated</span>
                                    @endif
                                </div>
                                <div class="d-flex align-items-center mt-1 fs-6">
                                    <div class="text-muted me-2 fs-7">Generate certificates from registrations, Excel upload, or manual entry</div>
                                </div>
                            </div>
                            <div class="overflow-auto pb-5">
                                <a href="{{ route('certificates.index') }}" class="btn btn-sm btn-{{ $myCertificates > 0 ? 'light-' : '' }}primary">
                                    {{ $myCertificates > 0 ? 'View Certificates' : 'Generate Certificates' }}
                                </a>
                            </div>
                        </div>
                    </div>
                    <!--end::Timeline item-->
                </div>
            </div>
        </div>
    </div>
    <!--end::Col-->

    <!--begin::Col-->
    <div class="col-md-12 col-lg-12 col-xl-6">
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-900">Recent Certificates</span>
                    <span class="text-gray-500 mt-1 fw-semibold fs-6">Your latest generated certificates</span>
                </h3>
                <div class="card-toolbar">
                    <a href="{{ route('certificates.index') }}" class="btn btn-sm btn-light">View All</a>
                </div>
            </div>
            <div class="card-body pt-5">
                @if($recentCertificates->count() > 0)
                    @foreach($recentCertificates as $certificate)
                        <div class="d-flex align-items-center mb-7">
                            <div class="symbol symbol-50px me-5">
                                <span class="symbol-label bg-light-primary">
                                    <i class="ki-duotone ki-award fs-2x text-primary">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <a href="{{ route('certificates.show', $certificate) }}" class="text-gray-900 fw-bold text-hover-primary fs-6">
                                    {{ $certificate->certificate_number }}
                                </a>
                                <span class="text-muted d-block fw-semibold">
                                    @php
                                        $data = $certificate->data ?? [];
                                        $recipient = $data['name'] ?? $data['participant_name'] ?? 'N/A';
                                    @endphp
                                    {{ $recipient }} · {{ $certificate->event->name ?? 'N/A' }}
                                </span>
                            </div>
                            <span class="badge badge-light fw-bold">{{ $certificate->generated_at->diffForHumans() }}</span>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-10">
                        <i class="ki-duotone ki-award fs-3x text-gray-400 mb-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <p class="text-gray-500 fs-5 fw-semibold mb-5">No certificates generated yet</p>
                        <a href="{{ route('certificates.create') }}" class="btn btn-sm btn-primary">Generate Your First Certificate</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <!--end::Col-->
</div>
<!--end::Row-->

@endsection

@push('scripts')

@endpush
