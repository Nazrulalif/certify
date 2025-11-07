<!DOCTYPE html>
<html lang="en">
<!--begin::Head-->

<head>
    <title>
        {{ trim($__env->yieldPushContent('title')) ? trim($__env->yieldPushContent('title')) . ' | ' : '' }}{{ config('app.name') }}
    </title>
    <meta charset="utf-8" />
    <!--begin::Header-->
    @include('layouts.partials.meta')
    <!--end::Header-->
    <meta name="asset-url" content="{{ asset('assets/') . '/' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="based-url" content="{{ url('/') }}">


    <link rel="shortcut icon" href="{{ asset(env('APP_FAVICON', 'assets/media/logos/favicon.ico')) }}" />

    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!--end::Fonts-->
    <link href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap" rel="stylesheet">
    <!--begin::Vendor Stylesheets(used for this page only)-->
    @stack('styles')
    <!--end::Vendor Stylesheets-->

    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <!--end::Global Stylesheets Bundle-->
    <script>
        // Frame-busting to prevent site from being loaded within a frame without permission (click-jacking)
        if (window.top != window.self) {
            window.top.location.replace(window.self.location.href);
        }
    </script>
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center bgi-no-repeat">
    <!--begin::Theme mode setup on page load-->
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>
    <!--end::Theme mode setup on page load-->
    <!--begin::Root-->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <!--begin::Page bg image-->
        <style>
            body {
                background-image: url('{{ asset('assets/media/auth/bg1.jpg') }}');
            }

            [data-bs-theme="dark"] body {
                background-image: url('{{ asset('assets/media/auth/bg1-dark.jpg') }}')
            }

            .logo-font {
                font-family: "Lilita One", sans-serif;
                font-weight: 200;
                font-style: normal;
            }
        </style>
        <!--end::Page bg image-->
        <!--begin::Authentication - Sign-in -->
        <div class="d-flex flex-column flex-column-fluid flex-lg-row">
            <!--begin::Aside-->
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
            <!--end::Aside-->

            <!--begin::Body-->

            <div class="d-flex align-items-center justify-content-center p-lg-15 p-8"> <!--min-vh-100-->
                <!--begin::Card-->
                <div
                    class="bg-body d-flex flex-column align-items-stretch flex-center rounded-4 w-md-600px p-lg-15 p-10 shadow">
                    <!--begin::Wrapper-->
                    <div class="d-flex flex-center flex-column flex-column-fluid px-lg-5 pb-lg-10 w-100 pb-8">
                        @yield('content')
                    </div>
                    <!--end::Wrapper-->
                </div>
                <!--end::Card-->
            </div>

            <!--end::Body-->
        </div>
        <!--end::Authentication - Sign-in-->
    </div>
    <!--end::Root-->

    <!--begin::Javascript-->
    <script>
        var assetURL = document.querySelector('meta[name="asset-url"]').getAttribute('content');
        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var basedURL = document.querySelector('meta[name="based-url"]').getAttribute('content');
    </script>
    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>

    <!--end::Global Javascript Bundle-->
    <!--begin::Vendors Javascript(used for this page only)-->
    @stack('scripts')

    <script>
        // When the document is ready
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form');
            const submitButton = document.getElementById('submit_form');

            if (form && submitButton) {
                form.addEventListener('submit', function() {
                    // Hide the normal label
                    submitButton.querySelector('.indicator-label').style.display = 'none';
                    // Show the loading indicator
                    submitButton.querySelector('.indicator-progress').style.display = 'inline-block';
                    // Optional: Disable the button to prevent multiple submissions
                    submitButton.disabled = true;
                });
            }
        });
    </script>

    @if (session('error') || session('success') || session('info'))
        @php
            $type = session('error') ? 'error' : (session('success') ? 'success' : 'info');
            $message = session('error') ?? (session('success') ?? session('info'));

            // Custom class untuk warna title
            $titleClass = match ($type) {
                'error' => 'text-danger',
                'success' => 'text-success',
                'info' => 'text-primary',
                default => '',
            };

            // Button label & style
            $buttonText = match ($type) {
                'error' => 'Okay, got it!',
                'success' => 'Awesome, thanks!',
                'info' => 'Noted!',
                default => 'OK',
            };

            $buttonClass = match ($type) {
                'error' => 'btn btn-danger',
                'success' => 'btn btn-success',
                'info' => 'btn btn-primary',
                default => 'btn btn-secondary',
            };
        @endphp

        <script>
            Swal.fire({
                icon: '{{ $type }}',
                title: '<strong class="{{ $titleClass }}">{{ $message }}</strong>',
                confirmButtonText: '{{ $buttonText }}',
                customClass: {
                    confirmButton: '{{ $buttonClass }}'
                },
                buttonsStyling: false,
                html: false
            });
        </script>
    @endif

    <!--end::Vendors Javascript-->
    <!--end::Javascript-->
</body>
<!--end::Body-->

</html>
