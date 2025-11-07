@extends('layouts.app')

@section('title', 'Root Dashboard')

@section('page-title', 'Dashboard')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">Root Dashboard</li>
@endsection

@section('content')

<div class="row g-5 g-xl-10 mb-5 mb-xl-5">
    <!--begin::Col-->
    <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3 h-100">
        <div class="card card-flush bgi-no-repeat bgi-size-contain bgi-position-x-end h-md-50 mb-5 mb-xl-10" style="background-color: #F1416C;background-image:url('{{ asset('assets/media/patterns/vector-1.png') }}')">
            <div class="card-header pt-5">
                <div class="card-title d-flex flex-column">
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $totalUsers }}</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Users</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        <span>{{ $activeUsers }} Active</span>
                        <span>{{ $totalUsers - $activeUsers }} Inactive</span>
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
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $rootUsers }}</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Root Users</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        <span>System Administrators</span>
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
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $regularUsers }}</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Regular Users</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        <span>Certificate Managers</span>
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
                    <span class="fs-2hx fw-bold text-white me-2 lh-1 ls-n2">{{ $totalTemplates }}</span>
                    <span class="text-white opacity-75 pt-1 fw-semibold fs-6">Total Templates</span>
                </div>
            </div>
            <div class="card-body d-flex align-items-end pt-0">
                <div class="d-flex align-items-center flex-column mt-3 w-100">
                    <div class="d-flex justify-content-between fw-bold fs-6 text-white opacity-75 w-100 mt-auto mb-2">
                        <span>Certificate Designs</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Col-->
</div>

<div class="row g-5 g-xl-10">
    <!--begin::Col-->
    <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3">
        <a href="{{ route('events.index') }}" class="card bg-body hoverable card-xl-stretch mb-xl-8">
            <div class="card-body">
                <i class="ki-duotone ki-chart-simple text-primary fs-2x ms-n1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                </i>
                <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5">{{ $totalEvents }}</div>
                <div class="fw-semibold text-gray-500">Total Events</div>
            </div>
        </a>
    </div>
    <!--end::Col-->

    <!--begin::Col-->
    <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3">
        <a href="{{ route('certificates.index') }}" class="card bg-body hoverable card-xl-stretch mb-xl-8">
            <div class="card-body">
                <i class="ki-duotone ki-award text-success fs-2x ms-n1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                </i>
                <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5">{{ $totalCertificates }}</div>
                <div class="fw-semibold text-gray-500">Certificates Generated</div>
            </div>
        </a>
    </div>
    <!--end::Col-->

    <!--begin::Col-->
    <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3">
        <a href="#" class="card bg-body hoverable card-xl-stretch mb-5 mb-xl-8">
            <div class="card-body">
                <i class="ki-duotone ki-people text-info fs-2x ms-n1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                    <span class="path5"></span>
                </i>
                <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5">{{ $totalRegistrations }}</div>
                <div class="fw-semibold text-gray-500">Total Registrations</div>
            </div>
        </a>
    </div>
    <!--end::Col-->

    <!--begin::Col-->
    <div class="col-md-6 col-lg-6 col-xl-6 col-xxl-3">
        <a href="{{ route('verify.index') }}" class="card bg-body hoverable card-xl-stretch mb-5 mb-xl-8">
            <div class="card-body">
                <i class="ki-duotone ki-verify text-warning fs-2x ms-n1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <div class="text-gray-900 fw-bold fs-2 mb-2 mt-5">{{ $verificationsToday }}</div>
                <div class="fw-semibold text-gray-500">Verifications Today</div>
            </div>
        </a>
    </div>
    <!--end::Col-->
</div>


@endsection

@push('scripts')

@endpush
