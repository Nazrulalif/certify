@extends('layouts.app')

@section('title', isset($event) ? 'Edit Event' : 'Create Event')

@section('page-title', isset($event) ? 'Edit Event' : 'Create Event')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ route('events.index') }}" class="text-muted text-hover-primary">Events</a>
    </li>
    <li class="breadcrumb-item text-gray-900">{{ isset($event) ? 'Edit' : 'Create' }}</li>
@endsection

@section('content')

    <form action="{{ isset($event) ? route('events.update', $event) : route('events.store') }}" method="POST" id="event-form">
        @csrf
        @if (isset($event))
            @method('PUT')
        @endif

        <div class="row g-6">
            <!--begin::Event Information-->
            <div class="col-lg-8">
                <div class="card mb-6">
                    <div class="card-header">
                        <h3 class="card-title">Event Information</h3>
                    </div>
                    <div class="card-body">
                        <!--begin::Event Name-->
                        <div class="mb-7">
                            <x-form.input label="Event Name" name="name" :value="old('name', $event->name ?? '')" placeholder="Enter event name"
                                required />
                        </div>

                        <!--begin::Description-->
                        <div class="mb-7">
                            <x-form.textarea label="Description" name="description" :value="old('description', $event->description ?? '')" rows="4"
                                placeholder="Enter event description" />
                        </div>

                        <!--begin::Template Selection-->
                        <div class="mb-7">
                            <x-form.select label="Certificate Template" name="template_id" required>
                                <option value="">Select a template</option>
                                @foreach ($templates as $template)
                                    <option value="{{ $template->id }}"
                                        {{ old('template_id', $event->template_id ?? '') == $template->id ? 'selected' : '' }}>
                                        {{ $template->name }} {{ $template->is_default ? '(Default)' : '' }}
                                    </option>
                                @endforeach
                            </x-form.select>
                        </div>

                        <!--begin::Registration Status-->
                        <div class="mb-7">
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="registration_enabled" value="1"
                                    id="registration_enabled"
                                    {{ old('registration_enabled', $event->registration_enabled ?? true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="registration_enabled">
                                    Enable Registration
                                </label>
                            </div>
                            <div class="form-text">When enabled, users can register through the public form</div>
                        </div>
                    </div>
                </div>

                <!--begin::Static Values Form-->
                <div class="card" id="static-values-card" style="display: none;">
                    <div class="card-header">
                        <h3 class="card-title">Certificate Static Values</h3>
                        <div class="card-toolbar">
                            <span class="text-muted fs-7">These values will appear on all certificates for this event</span>
                        </div>
                    </div>
                    <div class="card-body" id="static-values-container">
                        <!-- Dynamically loaded from template -->
                    </div>
                </div>
                <!--end::Static Values Form-->

                <!--begin::Registration Form Preview-->
                <div class="card mt-6" id="form-preview-card" style="display: none;">
                    <div class="card-header">
                        <h3 class="card-title">Registration Form Preview</h3>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-light" id="refresh-preview-btn">
                                <i class="ki-duotone ki-arrows-circle fs-3"></i>
                                Refresh
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="ki-duotone ki-information-3 fs-2x me-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <span>This is how participants will see the registration form</span>
                        </div>
                        <div id="form-preview-container">
                            <!-- Dynamically loaded -->
                        </div>
                    </div>
                </div>
                <!--end::Registration Form Preview-->
            </div>
            <!--end::Event Information-->

            <!--begin::Sidebar-->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 80px;">
                    <div class="card-header">
                        <h3 class="card-title">Actions</h3>
                    </div>
                    <div class="card-body">
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="ki-duotone ki-check fs-2"></i>
                            {{ isset($event) ? 'Update Event' : 'Create Event' }}
                        </button>

                        <a href="{{ route('events.index') }}" class="btn btn-light-secondary w-100">
                            <i class="ki-duotone ki-cross fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Cancel
                        </a>

                        @if (isset($event))
                            <div class="separator my-5"></div>
                            <a href="{{ route('events.show', $event) }}" class="btn btn-light-info w-100">
                                <i class="ki-duotone ki-eye fs-2"></i>
                                View Event
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            <!--end::Sidebar-->
        </div>
    </form>

@endsection

@push('scripts')
    <script>
        // When template is selected, load static value fields and form preview
        $('[name="template_id"]').on('change', function() {
            const templateId = $(this).val();
            
            if (!templateId) {
                $('#static-values-card').hide();
                $('#form-preview-card').hide();
                return;
            }
            
            // Load static value fields
            loadStaticValueFields(templateId);
            
            // Load form preview
            loadFormPreview(templateId);
        });

        // Trigger on page load if template is already selected (edit mode)
        $(document).ready(function() {
            const selectedTemplate = $('[name="template_id"]').val();
            if (selectedTemplate) {
                $('[name="template_id"]').trigger('change');
            }
        });

        // Load static value fields from template
        function loadStaticValueFields(templateId) {
            $.ajax({
                url: `/templates/${templateId}/static-value-fields`,
                type: 'GET',
                success: function(response) {
                    if (response.fields.length > 0) {
                        renderStaticValuesForm(response.fields);
                        $('#static-values-card').slideDown(300);
                    } else {
                        $('#static-values-card').hide();
                        toastr.info('This template has no fields that require static values');
                    }
                },
                error: function(xhr) {
                    toastr.error('Failed to load template fields');
                    console.error(xhr);
                }
            });
        }

        // Render static values form
        function renderStaticValuesForm(fields) {
            let html = '<div class="row g-5">';
            
            fields.forEach(field => {
                const existingValue = @json(isset($event) ? $event->static_values ?? [] : []);
                const fieldValue = existingValue[field.field_name] || '';
                
                html += `
                    <div class="col-md-6">
                        <label class="form-label required">${field.field_label}</label>
                        ${getInputForFieldType(field, fieldValue)}
                        <div class="form-text">This will appear on all certificates</div>
                    </div>
                `;
            });
            
            html += '</div>';
            $('#static-values-container').html(html);
        }

        // Get input HTML based on field type
        function getInputForFieldType(field, value = '') {
            const name = `static_values[${field.field_name}]`;
            const escapedValue = $('<div>').text(value).html(); // Escape HTML
            
            switch (field.field_type) {
                case 'date':
                    return `<input type="date" class="form-control" name="${name}" value="${escapedValue}" required>`;
                case 'number':
                    return `<input type="number" class="form-control" name="${name}" value="${escapedValue}" required>`;
                case 'email':
                    return `<input type="email" class="form-control" name="${name}" value="${escapedValue}" required>`;
                case 'textarea':
                    return `<textarea class="form-control" name="${name}" rows="3" required>${escapedValue}</textarea>`;
                default:
                    return `<input type="text" class="form-control" name="${name}" value="${escapedValue}" required>`;
            }
        }

        // Load form preview
        function loadFormPreview(templateId) {
            $.ajax({
                url: `/templates/${templateId}/registration-form-preview`,
                type: 'GET',
                success: function(response) {
                    if (response.fields.length > 0) {
                        renderFormPreview(response.fields);
                        $('#form-preview-card').slideDown(300);
                    } else {
                        $('#form-preview-card').hide();
                        toastr.warning('This template has no form fields configured');
                    }
                },
                error: function(xhr) {
                    toastr.error('Failed to load form preview');
                    console.error(xhr);
                }
            });
        }

        // Render form preview
        function renderFormPreview(fields) {
            let html = '<form class="form">';
            
            fields.forEach(field => {
                const requiredLabel = field.is_required ? '<span class="text-danger">*</span>' : '';
                
                html += `
                    <div class="mb-5">
                        <label class="form-label">${field.field_label} ${requiredLabel}</label>
                        ${getPreviewInputForType(field)}
                    </div>
                `;
            });
            
            html += `
                <div class="d-flex justify-content-between align-items-center">
                    <span class="text-muted fs-7"><span class="text-danger">*</span> Required fields</span>
                    <button type="button" class="btn btn-primary" disabled>
                        <i class="ki-duotone ki-check fs-2"></i>
                        Submit Registration
                    </button>
                </div>
            </form>`;
            
            $('#form-preview-container').html(html);
        }

        // Get preview input HTML
        function getPreviewInputForType(field) {
            const attrs = `class="form-control" ${field.is_required ? 'required' : ''} disabled`;
            const placeholder = `Enter ${field.field_label.toLowerCase()}`;
            
            switch (field.field_type) {
                case 'email':
                    return `<input type="email" ${attrs} placeholder="${placeholder}">`;
                case 'date':
                    return `<input type="date" ${attrs}>`;
                case 'number':
                    return `<input type="number" ${attrs} placeholder="${placeholder}">`;
                case 'textarea':
                    return `<textarea ${attrs} rows="3" placeholder="${placeholder}"></textarea>`;
                default:
                    return `<input type="text" ${attrs} placeholder="${placeholder}">`;
            }
        }

        // Refresh preview button
        $('#refresh-preview-btn').on('click', function() {
            const templateId = $('[name="template_id"]').val();
            if (templateId) {
                loadFormPreview(templateId);
                toastr.success('Preview refreshed');
            }
        });

        // Form validation
        $('#event-form').on('submit', function(e) {
            const templateId = $('[name="template_id"]').val();
            
            if (!templateId) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Template Required',
                    text: 'Please select a certificate template'
                });
                return false;
            }
            
            // Check if static values are filled (if any exist)
            const staticValuesInputs = $('#static-values-container input[required], #static-values-container textarea[required]');
            let hasEmptyStaticValues = false;
            
            staticValuesInputs.each(function() {
                if (!$(this).val()) {
                    hasEmptyStaticValues = true;
                    return false; // break loop
                }
            });
            
            if (hasEmptyStaticValues) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Static Values',
                    text: 'Please fill all required certificate static values'
                });
                return false;
            }
        });
    </script>
@endpush
