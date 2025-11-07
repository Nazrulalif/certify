@extends('layouts.guest')

@push('title')
    Login
@endpush

@push('styles')
@endpush

@push('scripts')
@endpush

@section('content')
    <!--begin::Form-->
    <form class="form w-100" id="form" method="POST" action="{{ route('login') }}" novalidate>
        @csrf
        <!--begin::Heading-->
        <div class="mb-11 text-center">
            <h1 class="fw-bolder mb-3 text-gray-900">Sign In</h1>
            <div class="fw-semibold fs-6 text-gray-500">Access Your Account</div>
        </div>

        <!--begin::Login options-->
        {{-- <div class="row g-3 mb-9">
            <a href="#" class="btn btn-outline btn-text-gray-700 btn-active-color-primary bg-state-light w-100">
                <img alt="Logo" src="{{ asset('/assets/media/svg/brand-logos/google-icon.svg') }}"
                    class="h-15px me-3" />
                Sign in with Google
            </a>
        </div>
        <!--begin::Separator-->
        <div class="separator separator-content my-14">
            <span class="w-125px fw-semibold fs-7 text-gray-500">Or with email</span>
        </div> --}}
        <!--begin::Input group-->
        <div class="fv-row mb-8">
            <input type="email" placeholder="Email" name="email" autocomplete="email" required autofocus
                class="form-control @session('error') is-invalid @endsession @error('email') is-invalid @enderror bg-transparent"
                value="{{ old('email') }}" />
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
        </div>
        <div class="fv-row mb-3" data-kt-password-meter="true">
            <div class="mb-1">
                <div class="position-relative mb-3">
                    <input class="form-control @error('password') is-invalid @enderror bg-transparent" type="password"
                        placeholder="Password" name="password" required autocomplete="current-password" />
                    @if (!$errors->has('password'))
                        <span class="btn btn-sm btn-icon position-absolute translate-middle top-50 me-n2 end-0"
                            data-kt-password-meter-control="visibility">
                            <i class="fa-solid fa-eye-slash fs-2 @error('password') text-danger @enderror"></i>
                            <i class="fa-solid fa-eye fs-2 d-none @error('password') text-danger @enderror"></i>
                        </span>
                    @endif

                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror

                </div>
            </div>

            <!--begin::Wrapper-->
            <div class="d-flex flex-stack fs-base fw-semibold mb-8 flex-wrap gap-3">
                <div>

                </div>
                {{-- @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="link-primary">Forgot Password ?</a>
                    @endif --}}
            </div>
            <!--begin::Submit button-->
            <div class="d-grid mb-10">
                <button type="submit" id="submit_form" class="btn btn-primary">
                    <span class="indicator-label">Sign In</span>
                    <span class="indicator-progress">Please wait...
                        <span class="spinner-border spinner-border-sm ms-2 align-middle"></span>
                    </span>
                </button>
            </div>

            <!--begin::Sign up link-->
            <div class="text-gray-500 text-center fw-semibold fs-6">
                Don't have an Account?
                <a href="{{ route('register') }}" class="link-primary fw-semibold">
                    Create Account
                </a>
            </div>
            <!--end::Sign up link-->

            <!--begin::Separator-->
            <div class="separator separator-content my-10">
                <span class="w-200px fw-semibold fs-7 text-gray-500">Verify Certificate</span>
            </div>
            <!--end::Separator-->

            <!--begin::Verify certificate link-->
            <div class="text-center">
                <a href="{{ route('verify.index') }}" class="btn btn-flex btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary w-100">
                    <i class="ki-duotone ki-shield-tick fs-2 me-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    Verify Certificate Authenticity
                </a>
            </div>
            <!--end::Verify certificate link-->
    </form>
    <!--end::Form-->
@endsection
