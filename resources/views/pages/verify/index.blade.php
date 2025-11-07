@extends('layouts.verify')

@push('title')
    Verify Certificate
@endpush

@section('content')
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <!--begin::Authentication - Verification-->
        <div class="d-flex flex-column flex-column-fluid flex-lg-row">
            <div class="d-flex flex-center w-lg-50 pt-15 pt-lg-0 px-10">
                <!--begin::Aside Content-->
                <div class="d-flex flex-center flex-lg-start flex-column text-lg-start w-100 text-center"
                    style="max-width: 500px;">
                    <!--begin::Title-->
                    <h1 class="fw-bold mb-3 text-wrap text-white logo-font fs-3hx">{{ config('app.name') }}</h1>
                    <!--end::Title-->

                    <!--begin::Subtitle-->
                    <h2 class="fw-normal fs-3 m-0 text-wrap text-white">
                        {{ env('APP_DESCRIPTION', 'Default description') }}
                    </h2>
                    <!--end::Subtitle-->
                </div>
                <!--end::Aside Content-->
            </div>

            <!--begin::Aside-->
            <div class="d-flex flex-center w-lg-50 p-10">
                <!--begin::Card-->
                <div class="card rounded-3 w-md-550px">
                    <!--begin::Card body-->
                    <div class="card-body p-10 p-lg-20">
                        <!--begin::Form-->
                        <form class="form w-100" id="verification-form">
                            @csrf

                            <!--begin::Heading-->
                            <div class="text-center mb-11">
                                <!--begin::Title-->
                                <h1 class="text-gray-900 fw-bolder mb-3">
                                    Verify Certificate
                                </h1>
                                <!--end::Title-->

                                <!--begin::Subtitle-->
                                <div class="text-gray-500 fw-semibold fs-6">
                                    Enter certificate number or scan QR code
                                </div>
                                <!--end::Subtitle-->
                            </div>
                            <!--begin::Heading-->

                            <!--begin::Input group-->
                            <div class="fv-row mb-8">
                                <!--begin::Certificate Number-->
                                <input type="text" placeholder="Enter Certificate Number (e.g., CERT-2025-000001)"
                                    name="certificate_number" autocomplete="off" class="form-control bg-transparent"
                                    id="certificate-number-input" />
                                <!--end::Certificate Number-->
                            </div>
                            <!--end::Input group-->

                            <!--begin::Submit button-->
                            <div class="d-grid mb-10">
                                <button type="submit" id="verify-submit-btn" class="btn btn-primary">
                                    <!--begin::Indicator label-->
                                    <span class="indicator-label">Verify Certificate</span>
                                    <!--end::Indicator label-->

                                    <!--begin::Indicator progress-->
                                    <span class="indicator-progress">
                                        Please wait...
                                        <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                                    </span>
                                    <!--end::Indicator progress-->
                                </button>
                            </div>
                            <!--end::Submit button-->
                        </form>
                        <!--end::Form-->

                        <!--begin::Result-->
                        <div id="verification-result" class="d-none mt-10">
                            <!-- Results will be displayed here -->
                        </div>
                        <!--end::Result-->
                    </div>
                    <!--end::Card body-->
                </div>
                <!--end::Card-->
            </div>
            <!--end::Aside-->
        </div>
        <!--end::Authentication - Verification-->
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
    <script src="{{ asset('web/js/verification.js') }}"></script>
@endpush
