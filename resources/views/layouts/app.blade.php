<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<!--begin::Head-->

<head>
    <title>
        {{ trim($__env->yieldPushContent('title')) ? trim($__env->yieldPushContent('title')) . ' | ' : '' }}{{ config('app.name') }}
    </title>
    <meta charset="utf-8" />
    <meta name="robots" content="noindex, nofollow">

    <!--begin::Header-->
    @include('layouts.partials.meta')
    <!--end::Header-->
    <meta name="asset-url" content="{{ asset('assets/') . '/' }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="based-url" content="{{ url('/') }}">
    <link rel="shortcut icon" href="{{ asset(env('APP_FAVICON', 'assets/media/logos/favicon.ico')) }}" />
    <!--begin::Fonts(mandatory for all pages)-->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lilita+One&display=swap" rel="stylesheet">
    <!--end::Fonts-->
    <!--begin::Vendor Stylesheets(used for this page only)-->
    @stack('vendor-styles')
    <!--end::Vendor Stylesheets-->
    <!--begin::Global Stylesheets Bundle(mandatory for all pages)-->
    <link href="{{ asset('assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/plugins/custom/datatables/datatables.bundle.css') }}" rel="stylesheet"
        type="text/css" />
    <!--end::Global Stylesheets Bundle-->
    @stack('styles')
    <style>
        .logo-font {
            font-family: "Lilita One", sans-serif;
            font-weight: 200;
            font-style: normal;
        }
    </style>
    <script>
        // Frame-busting to prevent site from being loaded within a frame without permission (click-jacking) if (window.top != window.self) { window.top.location.replace(window.self.location.href); }
    </script>
</head>
<!--end::Head-->
<!--begin::Body-->

<body id="kt_body" class="header-fixed">
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
    <div class="d-flex flex-column flex-root">
        <!--begin::Page-->
        <div class="page d-flex flex-row flex-column-fluid">

            @include('layouts.partials.sidebar')

            <!--begin::Wrapper-->
            <div class="wrapper d-flex flex-column flex-row-fluid" id="kt_wrapper">

                @include('layouts.partials.header')

                <!--begin::Content-->
                <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
                    <!--begin::Container-->
                    <div class="container-xxl" id="kt_content_container">
                        @include('layouts.partials.globalSearch')
                        @yield('content')
                    </div>
                    <!--end::Container-->
                </div>
                <!--end::Content-->
                @include('layouts.partials.footer')
            </div>
            <!--end::Wrapper-->
        </div>
        <!--end::Page-->
    </div>
    <!--end::Root-->

    <!--begin::Scrolltop-->
    <div id="kt_scrolltop" class="scrolltop" data-kt-scrolltop="true">
        <i class="ki-duotone ki-arrow-up">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </div>
    <!--end::Scrolltop-->

    <!--begin::Javascript-->
    <script>
        var assetURL = document.querySelector('meta[name="asset-url"]').getAttribute('content');
        var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var basedURL = document.querySelector('meta[name="based-url"]').getAttribute('content');
    </script>
    <!--begin::Global Javascript Bundle(mandatory for all pages)-->
    <script src="{{ asset('assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('assets/js/scripts.bundle.js') }}"></script>
    <script src="{{ asset('assets/plugins/custom/datatables/datatables.bundle.js') }}"></script>
    <!--end::Global Javascript Bundle-->
    <!--begin::Vendors Javascript(used for this page only)-->
    @stack('vendor-scripts')
    <!--end::Vendors Javascript-->
    <!--begin::Custom Javascript(used for this page only)-->
    @stack('scripts')
    <!--end::Custom Javascript-->
    <!--end::Javascript-->

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
</body>
<!--end::Body-->

</html>
