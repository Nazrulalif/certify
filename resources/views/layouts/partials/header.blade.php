<!--begin::Header-->
<div id="kt_header" class="header mt-0 mt-lg-0 pt-lg-0" data-kt-sticky="true" data-kt-sticky-name="header"
    data-kt-sticky-offset="{lg: '300px'}">
    <!--begin::Container-->
    <div class="container d-flex flex-stack flex-wrap gap-4" id="kt_header_container">
        <!--begin::Page title-->
        <div class="page-title d-flex flex-column align-items-start justify-content-center flex-wrap me-lg-2 pb-10 pb-lg-0"
            data-kt-swapper="true" data-kt-swapper-mode="prepend"
            data-kt-swapper-parent="{default: '#kt_content_container', lg: '#kt_header_container'}">
            <!--begin::Heading-->
            <h1 class="d-flex flex-column text-gray-900 fw-bold my-0 fs-1">@yield('page-title', 'Dashboard')</h1>
            <!--end::Heading-->
            <!--begin::Breadcrumb-->
            <ul class="breadcrumb breadcrumb-dot fw-semibold fs-base my-1">
                <li class="breadcrumb-item text-muted">
                    <a href="{{ url('/') }}" class="text-muted text-hover-primary">Home</a>
                </li>
                @yield('breadcrumb')
            </ul>
            <!--end::Breadcrumb-->
        </div>
        <!--end::Page title=-->
        <!--begin::Wrapper-->
        <div class="d-flex d-lg-none align-items-center ms-n3 me-2">
            <!--begin::Aside mobile toggle-->
            <div class="btn btn-icon btn-active-icon-primary" id="kt_aside_toggle">
                <i class="ki-duotone ki-abstract-14 fs-1 mt-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
            <!--end::Aside mobile toggle-->
            <!--begin::Logo-->
            <a href="{{ url('/') }}" class="logo-font fs-2hx text-gray-800 text-hover-primary">certify.</a>
            {{-- <a href="{{ url('/') }}" class="d-flex align-items-center">
                <img alt="Logo" src="{{ asset('assets/media/logos/demo3.svg') }}" class="theme-light-show h-20px" />
                <img alt="Logo" src="{{ asset('assets/media/logos/demo3-dark.svg') }}"
                    class="theme-dark-show h-20px" />
            </a> --}}
            <!--end::Logo-->
        </div>
        <!--end::Wrapper-->
        <!--begin::Topbar-->
        <div class="d-flex align-items-center flex-shrink-0 mb-0 mb-lg-0">
            <!--begin::Search-->
            <div class="d-none d-md-block w-100 position-relative mb-2 mb-lg-0">
                <i class="ki-duotone ki-magnifier fs-2 text-gray-700 position-absolute top-50 translate-middle-y ms-4">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
                <input type="text" data-bs-toggle="modal" data-bs-target="#globalSearchModal"
                    class="form-control bg-transparent ps-13 fs-7 h-40px" value="" placeholder="Quick Search" />
            </div>
            <!--end::Search-->
            <div class="d-flex d-sm-none align-items-center ms-3">
                <div class="btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline w-40px h-40px"
                    data-bs-toggle="modal" data-bs-target="#globalSearchModal">
                    <i class="ki-duotone ki-magnifier fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <!--begin::Theme mode-->
            <div class="d-flex align-items-center ms-3 ms-lg-4">
                <a href="#"
                    class="btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline w-40px h-40px"
                    data-kt-menu-trigger="{default:'click', lg: 'hover'}" data-kt-menu-attach="parent"
                    data-kt-menu-placement="bottom-end">
                    <i class="ki-duotone ki-night-day theme-light-show fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                        <span class="path6"></span>
                        <span class="path7"></span>
                        <span class="path8"></span>
                        <span class="path9"></span>
                        <span class="path10"></span>
                    </i>
                    <i class="ki-duotone ki-moon theme-dark-show fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </a>
                <!--begin::Menu-->
                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-title-gray-700 menu-icon-gray-500 menu-active-bg menu-state-color fw-semibold py-4 fs-base w-150px"
                    data-kt-menu="true" data-kt-element="theme-mode-menu">
                    <div class="menu-item px-3 my-0">
                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="light">
                            <span class="menu-icon" data-kt-element="icon">
                                <i class="ki-duotone ki-night-day fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                    <span class="path6"></span>
                                    <span class="path7"></span>
                                    <span class="path8"></span>
                                    <span class="path9"></span>
                                    <span class="path10"></span>
                                </i>
                            </span>
                            <span class="menu-title">Light</span>
                        </a>
                    </div>
                    <div class="menu-item px-3 my-0">
                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="dark">
                            <span class="menu-icon" data-kt-element="icon">
                                <i class="ki-duotone ki-moon fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <span class="menu-title">Dark</span>
                        </a>
                    </div>
                    <div class="menu-item px-3 my-0">
                        <a href="#" class="menu-link px-3 py-2" data-kt-element="mode" data-kt-value="system">
                            <span class="menu-icon" data-kt-element="icon">
                                <i class="ki-duotone ki-screen fs-2">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                            </span>
                            <span class="menu-title">System</span>
                        </a>
                    </div>
                </div>
                <!--end::Menu-->
            </div>
            <!--end::Theme mode-->
            <!--begin::Activities-->
            <div class="d-flex align-items-center ms-3 ms-lg-4">
                <a href="{{ route('logout') }}"
                    class="btn btn-icon btn-color-gray-700 btn-active-color-primary btn-outline w-40px h-40px"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                    data-bs-toggle="tooltip" data-bs-dismiss="click" title="Logout">
                    <i class="ki-duotone ki-exit-right fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </a>
            </div>
            <!--end::Activities-->
        </div>
        <!--end::Topbar-->
    </div>
    <!--end::Container-->
</div>
<!--end::Header-->
