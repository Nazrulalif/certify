@extends('layouts.app')

@section('title', 'Templates')

@section('page-title', 'Templates')

@section('breadcrumb')
    <li class="breadcrumb-item text-gray-900">Templates</li>
@endsection

@section('content')

    <!--begin::Card-->
    <div class="card">
        <!--begin::Card header-->
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" class="form-control form-control-solid w-250px ps-13"
                        placeholder="Search templates..." id="search-template">
                </div>
            </div>
            <div class="card-toolbar">
                <div class="d-flex justify-content-end">
                    <a href="{{ route('templates.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        Create Template
                    </a>
                </div>
            </div>
        </div>
        <!--end::Card header-->

        <!--begin::Card body-->
        <div class="card-body pt-0">
            @if ($templates->count() > 0)
                <div class="row g-6 g-xl-9 mb-6 mb-xl-9">
                    @foreach ($templates as $template)
                        <div class="col-md-6 col-xl-4">
                            <div class="card border border-2 border-gray-300 border-hover">
                                <div class="card-header border-0 pt-9">
                                    <div class="card-title m-0">
                                        <div class="symbol symbol-50px w-50px bg-light">
                                            <i class="ki-duotone ki-document fs-2x text-primary">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                    </div>
                                    <div class="card-toolbar">
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-icon btn-active-light-primary"
                                                data-bs-toggle="dropdown">
                                                <i class="ki-duotone ki-category fs-2">
                                                    <span class="path1"></span>
                                                    <span class="path2"></span>
                                                    <span class="path3"></span>
                                                    <span class="path4"></span>
                                                </i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end">
                                                <a href="{{ route('templates.edit', $template->id) }}"
                                                    class="dropdown-item">
                                                    <i class="ki-duotone ki-pencil fs-5 me-2">
                                                        <span class="path1"></span>
                                                        <span class="path2"></span>
                                                    </i>
                                                    Edit Template
                                                </a>
                                                @if (!$template->is_default)
                                                    <form action="{{ route('templates.set-default', $template->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item">
                                                            <i class="ki-duotone ki-check-circle fs-5 me-2">
                                                                <span class="path1"></span>
                                                                <span class="path2"></span>
                                                            </i>
                                                            Set as Default
                                                        </button>
                                                    </form>
                                                @endif
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('templates.destroy', $template->id) }}"
                                                    method="POST" class="d-inline delete-template-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="dropdown-item text-danger delete-template-btn">
                                                        <i class="ki-duotone ki-trash fs-5 me-2">
                                                            <span class="path1"></span>
                                                            <span class="path2"></span>
                                                            <span class="path3"></span>
                                                            <span class="path4"></span>
                                                            <span class="path5"></span>
                                                        </i>
                                                        Delete Template
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body p-9">
                                    <div class="fs-3 fw-bold text-gray-900 mb-1">
                                        {{ $template->name }}
                                        @if ($template->is_default)
                                            <span class="badge badge-success ms-2">Default</span>
                                        @endif
                                    </div>
                                    <p class="text-gray-500 fw-semibold fs-5 mt-1 mb-7">{{ $template->fields_count }} fields
                                    </p>

                                    @if ($template->background_url)
                                        <div class="d-flex flex-center mb-5" style="height: 200px; background: #f5f8fa;">
                                            <div
                                                style="position: relative; display: inline-block; max-width: 100%; max-height: 200px;">
                                                <img src="{{ $template->background_url }}" alt="{{ $template->name }}"
                                                    style="max-width: 100%; max-height: 200px; height: auto; display: block;"
                                                    class="cert-preview-img" data-template-id="{{ $template->id }}">

                                                @if ($template->fields->count() > 0)
                                                    <div class="fields-overlay" data-template-id="{{ $template->id }}"
                                                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none;">
                                                        @foreach ($template->fields as $field)
                                                            <div class="field-preview"
                                                                data-original-x="{{ $field->x }}"
                                                                data-original-y="{{ $field->y }}"
                                                                data-original-font-size="{{ $field->font_size }}"
                                                                style="
                                                        position: absolute; 
                                                        font-family: {{ $field->font_family }};
                                                        color: {{ $field->color }};
                                                        font-weight: {{ $field->bold ? 'bold' : 'normal' }};
                                                        font-style: {{ $field->italic ? 'italic' : 'normal' }};
                                                        text-align: {{ $field->text_align }};
                                                        transform: rotate({{ $field->rotation }}deg);
                                                        white-space: nowrap;
                                                        opacity: 0.8;
                                                        transform-origin: top left;
                                                    ">
                                                                {{ $field->field_name }}
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <div class="d-flex flex-stack flex-wrap gap-2 mt-5">
                                        <div class="text-gray-500 fw-semibold fs-7">
                                            Created by: {{ $template->creator->name ?? 'System' }}
                                        </div>
                                        <div class="text-gray-500 fw-semibold fs-7">
                                            {{ $template->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!--begin::Pagination-->
                <div class="d-flex justify-content-end">
                    <nav aria-label="Templates pagination">
                        {{ $templates->links('pagination::bootstrap-5') }}
                    </nav>
                </div>
                <!--end::Pagination-->
            @else
                <div class="text-center py-20">
                    <i class="ki-duotone ki-document fs-5x text-gray-400 mb-5">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <h3 class="text-gray-800 fw-bold mb-3">No Templates Found</h3>
                    <p class="text-gray-500 fs-6 mb-5">Start by creating your first certificate template</p>
                    <a href="{{ route('templates.create') }}" class="btn btn-primary">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        Create Your First Template
                    </a>
                </div>
            @endif
        </div>
        <!--end::Card body-->
    </div>
    <!--end::Card-->

@endsection

@push('scripts')
    <script>
        document.addEventListener("DOMContentLoaded", () => {

            const images = document.querySelectorAll(".cert-preview-img");

            function scaleFieldPositions(img) {
                const templateId = img.dataset.templateId;

                const overlay = document.querySelector(`.fields-overlay[data-template-id="${templateId}"]`);
                if (!overlay) return;

                // --- GET REAL IMAGE SIZE ON SCREEN ---
                const rect = img.getBoundingClientRect();
                const displayedWidth = rect.width;
                const displayedHeight = rect.height;

                const naturalWidth = img.naturalWidth;
                const naturalHeight = img.naturalHeight;

                if (!naturalWidth || !naturalHeight) return;

                // --- SCALE FACTORS ---
                const scaleX = displayedWidth / naturalWidth;
                const scaleY = displayedHeight / naturalHeight;

                // --- MAKE OVERLAY MATCH EXACT IMAGE BOX ---
                overlay.style.width = displayedWidth + "px";
                overlay.style.height = displayedHeight + "px";

                // Position overlay exactly over the image
                overlay.style.position = "absolute";
                overlay.style.top = "0";
                overlay.style.left = "0";

                // --- APPLY SCALING TO FIELDS ---
                overlay.querySelectorAll(".field-preview").forEach(field => {
                    const originalX = parseFloat(field.dataset.originalX);
                    const originalY = parseFloat(field.dataset.originalY);
                    const originalFont = parseFloat(field.dataset.originalFontSize);

                    field.style.left = (originalX * scaleX) + "px";
                    field.style.top = (originalY * scaleY) + "px";
                    field.style.fontSize = (originalFont * Math.min(scaleX, scaleY)) + "px";
                });
            }

            // Initialize whenever images load
            images.forEach(img => {
                img.addEventListener("load", () => scaleFieldPositions(img));

                if (img.complete) scaleFieldPositions(img);
            });

            // Recalculate on resize
            let resizeTimer;
            window.addEventListener("resize", () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    images.forEach(img => img.complete && scaleFieldPositions(img));
                }, 200);
            });

            // SweetAlert delete confirmation
            document.querySelectorAll('.delete-template-btn').forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    const form = this.closest('.delete-template-form');
                    
                    Swal.fire({
                        title: 'Are you sure?',
                        text: "This will permanently delete the template and all its fields!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endpush
