@extends('layouts.verify')

@push('title')
    Certificate Details - {{ $certificate->certificate_number }}
@endpush

@section('content')
<div class="d-flex flex-column flex-root" id="kt_app_root">
    <!--begin::Page bg image-->
    <style>
        body {
            background-image: url('{{ asset('assets/media/auth/bg4.jpg') }}');
        }

        [data-bs-theme="dark"] body {
            background-image: url('{{ asset('assets/media/auth/bg4-dark.jpg') }}');
        }
    </style>
    <!--end::Page bg image-->
    
    <!--begin::Certificate Details-->
    <div class="d-flex flex-center w-100 p-10">
        <!--begin::Card-->
        <div class="card w-lg-900px">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <div class="card-title">
                    <div class="d-flex align-items-center">
                        <!--begin::Icon-->
                        <i class="ki-duotone ki-verify fs-2x text-success me-4">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <!--end::Icon-->
                        
                        <!--begin::Title-->
                        <div>
                            <h1 class="fw-bold text-gray-900 mb-1">Certificate Verified</h1>
                            <div class="text-muted fs-6">This certificate is valid and authentic</div>
                        </div>
                        <!--end::Title-->
                    </div>
                </div>
                
                <!--begin::Actions-->
                <div class="card-toolbar">
                    <a href="{{ route('verify.index') }}" class="btn btn-sm btn-light">
                        <i class="ki-duotone ki-arrow-left fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Verify Another
                    </a>
                </div>
                <!--end::Actions-->
            </div>
            <!--end::Card header-->
            
            <!--begin::Card body-->
            <div class="card-body pt-4">
                <!--begin::Certificate Details-->
                <div class="row g-6 g-xl-9 mb-6 mb-xl-9">
                    <!--begin::Col-->
                    <div class="col-md-6">
                        <!--begin::Info-->
                        <div class="mb-7">
                            <label class="fw-bold text-gray-600 fs-6 mb-2">Certificate Number</label>
                            <div class="fw-bold text-gray-900 fs-4">{{ $certificate->certificate_number }}</div>
                        </div>
                        <!--end::Info-->
                    </div>
                    <!--end::Col-->
                    
                    <!--begin::Col-->
                    <div class="col-md-6">
                        <!--begin::Info-->
                        <div class="mb-7">
                            <label class="fw-bold text-gray-600 fs-6 mb-2">Event Name</label>
                            <div class="fw-bold text-gray-900 fs-4">{{ $certificate->event->name }}</div>
                        </div>
                        <!--end::Info-->
                    </div>
                    <!--end::Col-->
                </div>
                <!--end::Certificate Details-->
                
                <!--begin::Separator-->
                <div class="separator separator-dashed my-7"></div>
                <!--end::Separator-->
                
                <!--begin::Certificate Data-->
                <div class="mb-7">
                    <h3 class="fw-bold text-gray-900 mb-5">Certificate Information</h3>
                    <div class="row g-5">
                        @foreach($certificate->data as $key => $value)
                            @if($value && $key !== 'qr_code')
                                <div class="col-md-6">
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-600 fs-7 fw-semibold mb-1">
                                            {{ Str::headline($key) }}
                                        </span>
                                        <span class="text-gray-900 fs-6 fw-bold">
                                            {{ is_array($value) ? json_encode($value) : $value }}
                                        </span>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
                <!--end::Certificate Data-->
                
                <!--begin::Separator-->
                <div class="separator separator-dashed my-7"></div>
                <!--end::Separator-->
                
                <!--begin::Generation Info-->
                <div class="row g-5">
                    <div class="col-md-6">
                        <div class="d-flex flex-column">
                            <span class="text-gray-600 fs-7 fw-semibold mb-1">Generated On</span>
                            <span class="text-gray-900 fs-6 fw-bold">
                                {{ $certificate->created_at->format('F d, Y h:i A') }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="d-flex flex-column">
                            <span class="text-gray-600 fs-7 fw-semibold mb-1">Generated By</span>
                            <span class="text-gray-900 fs-6 fw-bold">
                                {{ $certificate->generator->name ?? 'System' }}
                            </span>
                        </div>
                    </div>
                </div>
                <!--end::Generation Info-->
                
                <!--begin::PDF Preview-->
                @if($certificate->pdf_path)
                <div class="mt-10">
                    <div class="d-flex justify-content-between align-items-center mb-5">
                        <h3 class="fw-bold text-gray-900 mb-0">Certificate Preview</h3>
                        <div class="badge badge-light-warning">
                            <i class="ki-duotone ki-shield-tick fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            View Only - No Download
                        </div>
                    </div>
                    
                    <!--begin::Security Notice-->
                    <div class="alert alert-dismissible bg-light-info d-flex flex-column flex-sm-row p-5 mb-5">
                        <i class="ki-duotone ki-information-5 fs-2hx text-info me-4 mb-5 mb-sm-0">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <div class="d-flex flex-column pe-0 pe-sm-10">
                            <h5 class="mb-1">Certificate Protection</h5>
                            <span>This certificate can only be downloaded by the authorized recipient via email or logged-in account. Public verification is for validation purposes only.</span>
                        </div>
                    </div>
                    <!--end::Security Notice-->
                    
                    <div class="border rounded p-5 bg-light-primary" id="pdf-preview-container">
                        <div class="text-center position-relative">
                            <!-- PDF Canvas Viewer (No toolbar, no download) -->
                            <canvas id="pdf-canvas" class="w-100 rounded"></canvas>
                            <div id="pdf-loading" class="text-center py-10">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3 text-gray-600">Loading certificate preview...</p>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <!--end::PDF Preview-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Certificate Details-->
</div>
@endsection

@push('scripts')
<!-- PDF.js Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
    // Set PDF.js worker
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    // Load and render PDF to canvas (no download/print buttons!)
    const pdfUrl = '{{ Storage::url($certificate->pdf_path) }}';
    const canvas = document.getElementById('pdf-canvas');
    const loadingDiv = document.getElementById('pdf-loading');
    const ctx = canvas.getContext('2d');

    // Load PDF
    pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
        // Get first page
        pdf.getPage(1).then(function(page) {
            // Calculate scale to fit container
            const container = document.getElementById('pdf-preview-container');
            const containerWidth = container.offsetWidth - 80; // Account for padding
            const viewport = page.getViewport({ scale: 1 });
            const scale = containerWidth / viewport.width;
            const scaledViewport = page.getViewport({ scale: scale });

            // Set canvas dimensions
            canvas.width = scaledViewport.width;
            canvas.height = scaledViewport.height;

            // Render PDF page to canvas
            const renderContext = {
                canvasContext: ctx,
                viewport: scaledViewport
            };

            page.render(renderContext).promise.then(function() {
                // Hide loading, show canvas
                loadingDiv.style.display = 'none';
                canvas.style.display = 'block';
                
                // Add watermark after rendering
                addWatermark();
            });
        });
    }).catch(function(error) {
        loadingDiv.innerHTML = '<div class="alert alert-danger">Failed to load certificate preview</div>';
        console.error('Error loading PDF:', error);
    });

    // Add watermark overlay
    function addWatermark() {
        const watermarkOverlay = document.createElement('div');
        watermarkOverlay.style.cssText = `
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 72px;
            color: rgba(255, 0, 0, 0.1);
            font-weight: bold;
            pointer-events: none;
            z-index: 10;
            user-select: none;
            width: 100%;
            text-align: center;
        `;
        watermarkOverlay.textContent = 'PREVIEW ONLY';
        
        const container = document.querySelector('#pdf-preview-container > div');
        if (container) {
            container.style.position = 'relative';
            container.appendChild(watermarkOverlay);
        }
    }
    
    // Prevent right-click on PDF preview
    document.getElementById('pdf-preview-container')?.addEventListener('contextmenu', function(e) {
        e.preventDefault();
        Swal.fire({
            text: "Right-click is disabled for security reasons. This certificate can only be downloaded by the authorized recipient.",
            icon: "warning",
            buttonsStyling: false,
            confirmButtonText: "Ok, got it!",
            customClass: {
                confirmButton: "btn fw-bold btn-primary",
            }
        });
        return false;
    });
    
    // Prevent text selection on PDF container
    document.getElementById('pdf-preview-container')?.addEventListener('selectstart', function(e) {
        e.preventDefault();
        return false;
    });
    
    // Disable print shortcut (Ctrl+P)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
            e.preventDefault();
            Swal.fire({
                text: "Printing is disabled for security reasons. This certificate can only be downloaded by the authorized recipient.",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary",
                }
            });
            return false;
        }
        
        // Also block Ctrl+S (Save)
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            e.preventDefault();
            Swal.fire({
                text: "Saving is disabled for security reasons. This certificate can only be downloaded by the authorized recipient.",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary",
                }
            });
            return false;
        }
    });
    
    // Prevent canvas image saving
    canvas?.addEventListener('dragstart', function(e) {
        e.preventDefault();
        return false;
    });
</script>

<style>
    /* Hide PDF canvas initially */
    #pdf-canvas {
        display: none;
        max-width: 100%;
        height: auto;
    }
    
    /* Prevent text selection in container */
    #pdf-preview-container {
        -webkit-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }
    
    /* Additional security styling */
    #pdf-preview-container * {
        -webkit-touch-callout: none;
    }
    
    #pdf-canvas {
        -webkit-user-drag: none;
        -khtml-user-drag: none;
        -moz-user-drag: none;
        -o-user-drag: none;
        user-drag: none;
    }
</style>
@endpush
