@extends('layouts.app')

@section('title', 'Create Template')

@section('page-title', 'Create Template')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('templates.index') }}" class="text-muted text-hover-primary">Templates</a>
    </li>
    <li class="breadcrumb-item text-gray-900">Create</li>
@endsection

@section('content')

<form action="{{ route('templates.store') }}" method="POST" enctype="multipart/form-data" id="template-form">
    @csrf
    
    <div class="row g-5">
        <!--begin::Left column-->
        <div class="col-xl-8">
            <div class="card mb-5">
                <div class="card-header">
                    <div class="card-title">
                        <h3>Template Information</h3>
                    </div>
                </div>
                <div class="card-body">
                    <!--begin::Input group-->
                    <div class="mb-10">
                        <label class="required form-label">Template Name</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                               placeholder="Enter template name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Give your template a descriptive name</div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Input group-->
                    <div class="mb-10">
                        <label class="required form-label">Background Image</label>
                        <input type="file" name="background" class="form-control @error('background') is-invalid @enderror" 
                               accept="image/jpeg,image/png,image/jpg" required id="background-input">
                        @error('background')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">Upload certificate background (JPG, PNG - Max 5MB)</div>
                    </div>
                    <!--end::Input group-->

                    <!--begin::Image preview-->
                    <div id="image-preview-container" style="display: none;">
                        <label class="form-label">Preview</label>
                        <div class="border border-gray-300 rounded p-3">
                            <img id="image-preview" src="" alt="Preview" class="mw-100" style="max-height: 400px;">
                        </div>
                        <div id="image-dimensions" class="form-text mt-2"></div>
                    </div>
                    <!--end::Image preview-->
                </div>
            </div>
        </div>
        <!--end::Left column-->

        <!--begin::Right column-->
        <div class="col-xl-4">
            <div class="card mb-5">
                <div class="card-header">
                    <div class="card-title">
                        <h3>Settings</h3>
                    </div>
                </div>
                <div class="card-body">
                    <!--begin::Input group-->
                    <div class="mb-10">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default" 
                                   {{ old('is_default') ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">
                                Set as Default Template
                            </label>
                        </div>
                        <div class="form-text">Default template will be pre-selected when creating events</div>
                    </div>
                    <!--end::Input group-->
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        <button type="submit" class="btn btn-primary">
                            <i class="ki-duotone ki-check fs-2"></i>
                            Create Template
                        </button>
                        <a href="{{ route('templates.index') }}" class="btn btn-light">
                            <i class="ki-duotone ki-cross fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Right column-->
    </div>
</form>

@endsection

@push('scripts')
<script>
    // Image preview
    document.getElementById('background-input').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('image-preview');
                img.src = e.target.result;
                document.getElementById('image-preview-container').style.display = 'block';
                
                // Get image dimensions
                img.onload = function() {
                    document.getElementById('image-dimensions').textContent = 
                        `Image dimensions: ${this.naturalWidth}px × ${this.naturalHeight}px`;
                };
            };
            reader.readAsDataURL(file);
        }
    });
</script>
@endpush
