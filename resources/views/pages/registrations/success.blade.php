@extends('layouts.form')

@push('title')
    Registration Successful
@endpush

@section('content')
<div class="d-flex flex-center flex-column flex-lg-row-fluid">

    <div class="w-lg-600px p-10">
        <div class="card shadow-sm">

            <!-- Header -->
            <div class="card-header bg-secondary text-center py-5 border-0 rounded-top-3 flex-column align-items-center">
                <h1 class="card-title text-dark fs-2 fw-bold mb-1 text-center">
                    Registration Successful
                </h1>
                <p class="text-dark-50 mb-0">
                    Thank you for completing your registration
                </p>
            </div>

            <!-- Body -->
            <div class="card-body p-lg-10 text-center">

                <!-- Success Icon -->
                <div class="mb-7 d-flex justify-content-center">
                    <div class="rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 100px; height: 100px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <svg viewBox="0 0 52 52" width="60" height="60">
                            <path d="M14.1 27.2l7.1 7.2 16.7-16.8"
                                  stroke="white"
                                  stroke-width="4"
                                  fill="none"
                                  stroke-linecap="round"
                                  stroke-linejoin="round"
                                  style="stroke-dasharray: 100; stroke-dashoffset: 0;">
                            </path>
                        </svg>
                    </div>
                </div>

                <h2 class="fw-bold text-gray-900 mb-3">You're all set!</h2>

                <p class="fs-5 text-gray-600 mb-7">
                    Your registration has been submitted successfully.
                    You will receive further updates through your email.
                </p>

                <!-- Info Box -->
                <div class="p-5 rounded bg-light text-start mb-7">
                    <h5 class="text-primary fw-bold mb-3">What happens next?</h5>
                    <ul class="mb-0">
                        <li>Your registration will be reviewed</li>
                        <li>A confirmation email will be sent to you</li>
                        <li>Your certificate will be generated once approved</li>
                        <li>Please check your inbox for updates</li>
                    </ul>
                </div>

                <p class="text-muted">
                    <small>You may now close this window.</small>
                </p>

            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-10">
            <span class="text-white fw-semibold fs-6">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </span>
        </div>
    </div>
</div>
@endsection
