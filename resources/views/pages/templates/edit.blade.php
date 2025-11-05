@extends('layouts.app')

@section('title', 'Edit Template - ' . $template->name)

@section('page-title', 'Edit Template')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('templates.index') }}" class="text-muted text-hover-primary">Templates</a>
    </li>
    <li class="breadcrumb-item text-gray-900">{{ $template->name }}</li>
@endsection

@section('content')

    <div class="row g-5">
        <!--begin::Left column - Canvas-->
        <div class="col-xl-8">
            <!--begin::Field Definition Table-->
            <div class="card mb-5">
                <div class="card-header">
                    <h3 class="card-title">Field Configuration</h3>
                    <div class="card-toolbar">
                        <button type="button" class="btn btn-sm btn-primary" id="add-custom-field-btn">
                            <i class="ki-duotone ki-plus fs-3"></i>
                            Add Custom Field
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-row-bordered align-middle gs-5" id="fields-table">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="min-w-150px">Field Name</th>
                                    <th class="min-w-120px">Field Label</th>
                                    <th class="min-w-80px">Type</th>
                                    <th class="text-center min-w-100px">Show in Form</th>
                                    <th class="text-center min-w-100px">Show in Cert</th>
                                    <th class="text-center min-w-80px">Required</th>
                                    <th class="text-center min-w-80px">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="fields-tbody">
                                @foreach($template->fields->sortBy('order') as $field)
                                <tr data-field-id="{{ $field->id }}" data-field-name="{{ $field->field_name }}">
                                    <td>
                                        <span class="badge badge-light-dark">{{ $field->field_name }}</span>
                                        @if($field->is_predefined)
                                        <span class="badge badge-info badge-sm ms-1">Predefined</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-gray-800 fw-semibold">{{ $field->field_label }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-light">{{ ucfirst($field->field_type) }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-check-custom form-check-solid form-check-sm d-inline-block">
                                            <input class="form-check-input toggle-show-in-form" type="checkbox" 
                                                {{ $field->show_in_form ? 'checked' : '' }}
                                                data-field-id="{{ $field->id }}">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-check-custom form-check-solid form-check-sm d-inline-block">
                                            <input class="form-check-input toggle-show-in-cert" type="checkbox" 
                                                {{ $field->show_in_cert ? 'checked' : '' }}
                                                data-field-id="{{ $field->id }}">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-check-custom form-check-solid form-check-sm d-inline-block">
                                            <input class="form-check-input toggle-required" type="checkbox" 
                                                {{ $field->is_required ? 'checked' : '' }}
                                                data-field-id="{{ $field->id }}"
                                                {{ !$field->show_in_form ? 'disabled' : '' }}>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if(!$field->is_predefined)
                                        <button class="btn btn-sm btn-icon btn-light-danger delete-field-btn" 
                                            data-field-id="{{ $field->id }}" title="Delete Field">
                                            <i class="ki-duotone ki-trash fs-5">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                                <span class="path5"></span>
                                            </i>
                                        </button>
                                        @else
                                        <span class="text-muted fs-7">Protected</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!--end::Field Definition Table-->

            <div class="card ">
                <div class="card-header">
                    <div class="card-title">
                        <h3>Template Editor</h3>
                    </div>
                    <div class="card-toolbar gap-3">
                        <a href="{{ route('templates.index') }}" class="btn btn-sm btn-secondary">
                            <i class="ki-duotone ki-arrow-left fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                            Back
                        </a>
                        <button type="button" class="btn btn-sm btn-info" id="download-preview-btn">
                            <i class="ki-duotone ki-printer fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                                <span class="path4"></span>
                                <span class="path5"></span>
                            </i>
                            Download Preview
                        </button>
                        <button type="button" class="btn btn-sm btn-primary" id="save-fields-btn">
                            <i class="ki-duotone ki-check fs-2"></i>
                            Save Fields
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-5 d-none">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <button type="button" class="btn btn-sm btn-light-primary" id="add-text-btn">
                                <i class="ki-duotone ki-text fs-3"></i>
                                Add Text Field
                            </button>
                            <button type="button" class="btn btn-sm btn-light-danger" id="delete-selected-btn">
                                <i class="ki-duotone ki-trash fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                </i>
                                Delete Selected
                            </button>
                            <button type="button" class="btn btn-sm btn-light-warning" id="clear-all-btn">
                                <i class="ki-duotone ki-cross-circle fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                                Clear All
                            </button>
                        </div>
                    </div>

                    <!--begin::Canvas container-->
                    <div class="border border-gray-300 rounded" style="overflow: auto; max-height: 800px;">
                        <canvas id="canvas"></canvas>
                    </div>
                    <!--end::Canvas container-->
                </div>
            </div>
        </div>
        <!--end::Left column-->

        <!--begin::Right column - Properties-->
        <div class="col-xl-4">
            <!--begin::Field Properties-->
            <div class="card mb-5" id="field-properties" style="display: none;">
                <div class="card-header">
                    <div class="card-title">
                        <h3>Field Properties</h3>
                    </div>
                </div>
                <div class="card-body">
                    <div class="mb-5">
                        <x-form.input label="Field Name" id="field-name" name="field_name"
                            placeholder="e.g., participant_name" help="Unique identifier for this field" />
                    </div>

                    <div class="mb-5">
                        <x-form.select label="Field Type" id="field-type" name="field_type" :options="[
                            'text' => 'Text',
                            'date' => 'Date',
                            'number' => 'Number',
                        ]" />
                    </div>

                    <div class="mb-5">
                        <x-form.input label="Font Size" type="number" id="font-size" name="font_size" value="16"
                            min="8" max="200" />
                    </div>

                    <div class="mb-5">
                        <x-form.select label="Font Family" id="font-family" name="font_family" :options="[
                            'Arial' => 'Arial',
                            'Times New Roman' => 'Times New Roman',
                            'Courier New' => 'Courier New',
                            'Georgia' => 'Georgia',
                            'Verdana' => 'Verdana',
                        ]" />
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Text Color</label>
                        <input type="color" id="text-color" class="form-control form-control-color" value="#000000">
                    </div>

                    <div class="d-flex flex-row gap-3 mb-5">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="text-bold">
                            <label class="form-check-label">Bold</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="text-italic">
                            <label class="form-check-label">Italic</label>
                        </div>
                    </div>

                    <div class="mb-5">
                        <label class="form-label">Text Alignment</label>
                        <div class="btn-group w-100" role="group">
                            <button type="button" class="btn btn-sm btn-light-primary align-btn" data-align="left">
                                <i class="ki-duotone ki-text-align-left fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                                Left
                            </button>
                            <button type="button" class="btn btn-sm btn-light-primary align-btn" data-align="center">
                                <i class="ki-duotone ki-text-align-center fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                                Center
                            </button>
                            <button type="button" class="btn btn-sm btn-light-primary align-btn" data-align="right">
                                <i class="ki-duotone ki-text-align-right fs-3">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                </i>
                                Right
                            </button>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary w-100" id="apply-properties-btn">
                        Apply Properties
                    </button>
                </div>
            </div>
            <!--end::Field Properties-->

            <!--begin::Template Info-->
            <div class="card mb-5">
                <div class="card-header">
                    <div class="card-title">
                        <h3>Template Information</h3>
                    </div>
                </div>
                <div class="card-body">
                    <form action="{{ route('templates.update', $template->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-5">
                            <x-form.input label="Template Name" name="name" value="{{ $template->name }}" required />
                        </div>

                        <div class="mb-5">
                            <label class="form-label">Change Background</label>
                            <input type="file" name="background" class="form-control" accept="image/*">
                            <div class="form-text">Leave empty to keep current background</div>
                        </div>

                        <div class="mb-5">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_default" value="1"
                                    {{ $template->is_default ? 'checked' : '' }}>
                                <label class="form-check-label">Set as Default</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Update Template Info</button>
                    </form>
                </div>
            </div>
            <!--end::Template Info-->

        </div>
        <!--end::Right column-->
    </div>

    <!--begin::Add Field Modal-->
    <div class="modal fade" id="addFieldModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Text Field</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="add-field-form">
                        <div class="mb-5">
                            <x-form.input label="Field Name" id="new-field-name" name="new_field_name"
                                placeholder="e.g., participant_name, event_name, date" required
                                help="This will be the unique identifier for this field" />
                        </div>

                        <div class="mb-5">
                            <x-form.select label="Field Type" id="new-field-type" name="new_field_type"
                                :options="[
                                    'text' => 'Text',
                                    'date' => 'Date',
                                    'number' => 'Number',
                                ]" />
                        </div>

                        <div class="mb-5">
                            <x-form.input label="Font Size" type="number" id="new-font-size" name="new_font_size"
                                value="16" min="8" max="200" />
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-5">
                                <x-form.select label="Font Family" id="new-font-family" name="new_font_family"
                                    :options="[
                                        'Arial' => 'Arial',
                                        'Times New Roman' => 'Times New Roman',
                                        'Courier New' => 'Courier New',
                                        'Georgia' => 'Georgia',
                                        'Verdana' => 'Verdana',
                                    ]" />
                            </div>
                            <div class="col-md-6 mb-5">
                                <label class="form-label">Text Color</label>
                                <input type="color" id="new-text-color" class="form-control form-control-color"
                                    value="#000000">
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label">Text Style</label>
                            <div class="d-flex gap-5">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="new-text-bold">
                                    <label class="form-check-label">Bold</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="new-text-italic">
                                    <label class="form-check-label">Italic</label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-5">
                            <label class="form-label">Text Alignment</label>
                            <div class="btn-group w-100" role="group">
                                <button type="button" class="btn btn-sm btn-light-primary new-align-btn active" data-align="left">
                                    <i class="ki-duotone ki-text-align-left fs-3">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                    Left
                                </button>
                                <button type="button" class="btn btn-sm btn-light-primary new-align-btn" data-align="center">
                                    <i class="ki-duotone ki-text-align-center fs-3">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                    Center
                                </button>
                                <button type="button" class="btn btn-sm btn-light-primary new-align-btn" data-align="right">
                                    <i class="ki-duotone ki-text-align-right fs-3">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                        <span class="path4"></span>
                                    </i>
                                    Right
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" id="confirm-add-field">
                        <i class="ki-duotone ki-plus fs-2"></i>
                        Add Field
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!--end::Add Field Modal-->

@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.0/fabric.min.js"></script>
    <script>
        // ====================================================
        // FIELD TABLE INTERACTIONS
        // ====================================================

        // Toggle "Show in Form" checkbox
        $(document).on('change', '.toggle-show-in-form', function() {
            const fieldId = $(this).data('field-id');
            const showInForm = $(this).is(':checked');
            const $requiredCheckbox = $(this).closest('tr').find('.toggle-required');
            
            // Disable "Required" if not in form
            $requiredCheckbox.prop('disabled', !showInForm);
            if (!showInForm) {
                $requiredCheckbox.prop('checked', false);
            }
            
            // Update via AJAX
            $.ajax({
                url: `/template-fields/${fieldId}`,
                type: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    show_in_form: showInForm,
                    is_required: !showInForm ? false : $requiredCheckbox.is(':checked')
                }),
                success: function(response) {
                    toastr.success('Field visibility updated successfully');
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to update field');
                    // Revert checkbox on error
                    $(this).prop('checked', !showInForm);
                }
            });
        });

        // Toggle "Show in Cert" checkbox - Add/Remove from canvas
        $(document).on('change', '.toggle-show-in-cert', function() {
            const fieldId = $(this).data('field-id');
            const fieldName = $(this).closest('tr').data('field-name');
            const showInCert = $(this).is(':checked');
            
            // Update field via AJAX
            $.ajax({
                url: `/template-fields/${fieldId}`,
                type: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    show_in_cert: showInCert
                }),
                success: function(response) {
                    if (showInCert) {
                        // Add field to canvas with position from response
                        const positionData = response.field.position_data || {};
                        addFieldToCanvas(fieldName, {
                            left: (positionData.x || 100) * canvasScaleRatio,
                            top: (positionData.y || 100) * canvasScaleRatio,
                            fontSize: (positionData.fontSize || 16) * canvasScaleRatio,
                            fontFamily: positionData.fontFamily || 'Arial',
                            fill: positionData.color || '#000000',
                            textAlign: positionData.textAlign || 'left',
                            fontWeight: positionData.bold ? 'bold' : 'normal',
                            fontStyle: positionData.italic ? 'italic' : 'normal',
                            angle: positionData.rotation || 0,
                            fieldType: response.field.field_type
                        });
                        toastr.success('Field added to certificate canvas');
                    } else {
                        // Remove field from canvas
                        removeFieldFromCanvas(fieldName);
                        toastr.success('Field removed from certificate canvas');
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to update field');
                    // Revert checkbox on error
                    $(this).prop('checked', !showInCert);
                }
            });
        });

        // Toggle "Required" checkbox
        $(document).on('change', '.toggle-required', function() {
            const fieldId = $(this).data('field-id');
            const isRequired = $(this).is(':checked');
            
            $.ajax({
                url: `/template-fields/${fieldId}`,
                type: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                contentType: 'application/json',
                data: JSON.stringify({
                    is_required: isRequired
                }),
                success: function() {
                    toastr.success('Field requirement updated');
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to update field');
                    // Revert checkbox on error
                    $(this).prop('checked', !isRequired);
                }
            });
        });

        // Add Custom Field Button
        $('#add-custom-field-btn').on('click', function() {
            Swal.fire({
                title: 'Add Custom Field',
                html: `
                    <div class="text-start">
                        <div class="mb-4">
                            <label class="form-label required">Field Name (no spaces)</label>
                            <input type="text" class="form-control" id="new-custom-field-name" 
                                placeholder="e.g., company_name, department">
                            <div class="form-text">Used as identifier, lowercase with underscores</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label required">Field Label</label>
                            <input type="text" class="form-control" id="new-custom-field-label" 
                                placeholder="e.g., Company Name, Department">
                            <div class="form-text">Display name shown to users</div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label">Field Type</label>
                            <select class="form-select" id="new-custom-field-type">
                                <option value="text">Text</option>
                                <option value="email">Email</option>
                                <option value="date">Date</option>
                                <option value="number">Number</option>
                                <option value="textarea">Textarea</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <div class="form-check form-check-custom form-check-solid mb-2">
                                <input class="form-check-input" type="checkbox" id="new-custom-show-in-form" checked>
                                <label class="form-check-label">Show in Registration Form</label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid mb-2">
                                <input class="form-check-input" type="checkbox" id="new-custom-show-in-cert">
                                <label class="form-check-label">Show on Certificate</label>
                            </div>
                            <div class="form-check form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" id="new-custom-required">
                                <label class="form-check-label">Required Field</label>
                            </div>
                        </div>
                    </div>
                `,
                width: '600px',
                showCancelButton: true,
                confirmButtonText: 'Add Field',
                cancelButtonText: 'Cancel',
                customClass: {
                    confirmButton: 'btn btn-primary',
                    cancelButton: 'btn btn-light'
                },
                preConfirm: () => {
                    const fieldName = $('#new-custom-field-name').val().trim();
                    const fieldLabel = $('#new-custom-field-label').val().trim();
                    
                    if (!fieldName || !fieldLabel) {
                        Swal.showValidationMessage('Please fill all required fields');
                        return false;
                    }
                    
                    // Validate field name format (lowercase, underscores only)
                    if (!/^[a-z][a-z0-9_]*$/.test(fieldName)) {
                        Swal.showValidationMessage('Field name must start with a letter and contain only lowercase letters, numbers, and underscores');
                        return false;
                    }
                    
                    return {
                        field_name: fieldName,
                        field_label: fieldLabel,
                        field_type: $('#new-custom-field-type').val(),
                        show_in_form: $('#new-custom-show-in-form').is(':checked'),
                        show_in_cert: $('#new-custom-show-in-cert').is(':checked'),
                        is_required: $('#new-custom-required').is(':checked')
                    };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/templates/{{ $template->id }}/fields`,
                        type: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        contentType: 'application/json',
                        data: JSON.stringify(result.value),
                        success: function(response) {
                            // Reload page to show new field
                            location.reload();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to add custom field'
                            });
                        }
                    });
                }
            });
        });

        // Delete Field Button
        $(document).on('click', '.delete-field-btn', function() {
            const fieldId = $(this).data('field-id');
            const $row = $(this).closest('tr');
            const fieldName = $row.data('field-name');
            
            Swal.fire({
                title: 'Delete Custom Field?',
                text: 'This will permanently remove this field from the template',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/template-fields/${fieldId}`,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function() {
                            // Remove from canvas if present
                            removeFieldFromCanvas(fieldName);
                            // Remove row from table
                            $row.fadeOut(300, function() { 
                                $(this).remove(); 
                            });
                            toastr.success('Custom field deleted successfully');
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: xhr.responseJSON?.message || 'Failed to delete field'
                            });
                        }
                    });
                }
            });
        });

        // Helper: Remove field from canvas by field name
        function removeFieldFromCanvas(fieldName) {
            const objects = canvas.getObjects();
            objects.forEach(obj => {
                if (obj.type === 'i-text' && obj.text === fieldName) {
                    canvas.remove(obj);
                }
            });
            canvas.renderAll();
        }

        // ====================================================
        // FABRIC.JS CANVAS INITIALIZATION
        // ====================================================

        // Initialize Fabric.js canvas
        const canvas = new fabric.Canvas('canvas', {
            width: 800,
            height: 600,
            backgroundColor: '#ffffff'
        });

        // Template data
        const templateId = `{{ $template->id }}`;
        const backgroundUrl = '{{ $template->background_url }}';
        const existingFields = @json($template->fields);

        // Store original image dimensions and scale ratio
        let originalImageWidth = 0;
        let originalImageHeight = 0;
        let canvasScaleRatio = 1;

        // Load background image
        if (backgroundUrl) {
            fabric.Image.fromURL(backgroundUrl, function(img) {
                // Store original dimensions
                originalImageWidth = img.width;
                originalImageHeight = img.height;

                // Calculate scale to fit canvas
                const scale = Math.min(canvas.width / img.width, canvas.height / img.height);
                canvasScaleRatio = scale;

                img.scale(scale);
                canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas), {
                    scaleX: scale,
                    scaleY: scale
                });

                // Update canvas size to match scaled image
                canvas.setWidth(img.width * scale);
                canvas.setHeight(img.height * scale);

                // Load existing fields AFTER image is loaded and canvas is sized
                loadExistingFields();
            });
        }

        // Load existing fields with scaled positions
        function loadExistingFields() {
            existingFields.forEach(field => {
                // Only load fields that should be shown on certificate
                if (field.show_in_cert) {
                    const positionData = field.position_data || {};
                    addTextField(field.field_name, {
                        left: (positionData.x || parseFloat(field.x) || 100) * canvasScaleRatio,
                        top: (positionData.y || parseFloat(field.y) || 100) * canvasScaleRatio,
                        fontSize: (positionData.fontSize || field.font_size || 16) * canvasScaleRatio,
                        fontFamily: positionData.fontFamily || field.font_family || 'Arial',
                        fill: positionData.color || field.color || '#000000',
                        textAlign: positionData.textAlign || field.text_align || 'left',
                        fontWeight: (positionData.bold || field.bold) ? 'bold' : 'normal',
                        fontStyle: (positionData.italic || field.italic) ? 'italic' : 'normal',
                        angle: positionData.rotation || parseFloat(field.rotation) || 0,
                        fieldType: field.field_type,
                    });
                }
            });
        }

        // Add text field to canvas
        function addTextField(text = 'New Field', options = {}) {
            const defaultOptions = {
                left: 100,
                top: 100,
                fontSize: 16 * canvasScaleRatio,
                fontFamily: 'Arial',
                fill: '#000000',
                textAlign: 'left',
                fieldType: 'text',
                // Enable corner controls for resizing
                cornerSize: 10,
                transparentCorners: false,
                cornerColor: '#4A90E2',
                cornerStrokeColor: '#fff',
                borderColor: '#4A90E2',
                // Enable controls
                hasControls: true,
                hasBorders: true,
                lockScalingFlip: true,
            };

            const textObj = new fabric.IText(text, {
                ...defaultOptions,
                ...options
            });
            textObj.fieldType = options.fieldType || 'text';

            // Set control visibility
            textObj.setControlsVisibility({
                mt: false, // middle top
                mb: false, // middle bottom
                ml: true, // middle left
                mr: true, // middle right
                bl: true, // bottom left
                br: true, // bottom right
                tl: true, // top left
                tr: true, // top right
                mtr: true // rotation control
            });

            canvas.add(textObj);
            canvas.setActiveObject(textObj);
            canvas.renderAll();
            updateFieldProperties(textObj);
        }

        // Helper: Add field to canvas (wrapper for addTextField)
        function addFieldToCanvas(fieldName, options = {}) {
            // Check if field already exists on canvas
            const existingField = canvas.getObjects().find(obj => 
                obj.type === 'i-text' && obj.text === fieldName
            );
            
            if (existingField) {
                console.log(`Field ${fieldName} already exists on canvas`);
                canvas.setActiveObject(existingField);
                canvas.renderAll();
                return;
            }
            
            // Add new field using addTextField
            addTextField(fieldName, options);
        }

        // Add new text field button - Open modal
        document.getElementById('add-text-btn').addEventListener('click', function() {
            // Reset form
            document.getElementById('add-field-form').reset();
            document.getElementById('new-field-name').value = '';
            document.getElementById('new-font-size').value = '16';
            document.getElementById('new-text-color').value = '#000000';
            document.getElementById('new-text-bold').checked = false;
            document.getElementById('new-text-italic').checked = false;

            // Reset Select2 dropdowns
            const newFieldTypeSelect = document.getElementById('new-field-type');
            const newFontFamilySelect = document.getElementById('new-font-family');

            if ($(newFieldTypeSelect).hasClass('select2-hidden-accessible')) {
                $(newFieldTypeSelect).val('text').trigger('change');
            } else {
                newFieldTypeSelect.value = 'text';
            }

            if ($(newFontFamilySelect).hasClass('select2-hidden-accessible')) {
                $(newFontFamilySelect).val('Arial').trigger('change');
            } else {
                newFontFamilySelect.value = 'Arial';
            }

            // Reset alignment buttons to 'left'
            document.querySelectorAll('.new-align-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.align === 'left') {
                    btn.classList.add('active');
                }
            });

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('addFieldModal'));
            modal.show();
        });

        // Confirm add field from modal
        document.getElementById('confirm-add-field').addEventListener('click', function() {
            const fieldName = document.getElementById('new-field-name').value.trim();

            if (!fieldName) {
                alert('Please enter a field name');
                return;
            }

            // Get field properties from modal - compatible with Select2
            const fieldType = $('#new-field-type').hasClass('select2-hidden-accessible') ?
                $('#new-field-type').val() :
                document.getElementById('new-field-type').value;

            const fontFamily = $('#new-font-family').hasClass('select2-hidden-accessible') ?
                $('#new-font-family').val() :
                document.getElementById('new-font-family').value;

            const fontSize = parseInt(document.getElementById('new-font-size').value);
            const textColor = document.getElementById('new-text-color').value;
            const isBold = document.getElementById('new-text-bold').checked;
            const isItalic = document.getElementById('new-text-italic').checked;

            // Get selected alignment
            const activeAlignBtn = document.querySelector('.new-align-btn.active');
            const textAlign = activeAlignBtn ? activeAlignBtn.dataset.align : 'left';

            // Add text field with properties
            addTextField(fieldName, {
                fieldType: fieldType,
                fontSize: fontSize * canvasScaleRatio,
                fontFamily: fontFamily,
                fill: textColor,
                textAlign: textAlign,
                fontWeight: isBold ? 'bold' : 'normal',
                fontStyle: isItalic ? 'italic' : 'normal'
            });

            // Close modal
            const modal = bootstrap.Modal.getInstance(document.getElementById('addFieldModal'));
            modal.hide();
        });

        // Allow Enter key to submit modal form
        document.getElementById('add-field-form').addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById('confirm-add-field').click();
        });

        // Delete selected object
        document.getElementById('delete-selected-btn').addEventListener('click', function() {
            const activeObject = canvas.getActiveObject();
            if (activeObject) {
                canvas.remove(activeObject);
                canvas.renderAll();
                document.getElementById('field-properties').style.display = 'none';
            }
        });

        // Clear all objects
        document.getElementById('clear-all-btn').addEventListener('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will remove all text fields from the template!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, clear all!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    canvas.getObjects().forEach(obj => {
                        if (obj.type === 'i-text') {
                            canvas.remove(obj);
                        }
                    });
                    canvas.renderAll();
                    document.getElementById('field-properties').style.display = 'none';

                    Swal.fire(
                        'Cleared!',
                        'All text fields have been removed.',
                        'success'
                    );
                }
            });
        });

        // Update field properties panel when object is selected
        canvas.on('selection:created', function(e) {
            updateFieldProperties(e.selected[0]);
        });

        canvas.on('selection:updated', function(e) {
            updateFieldProperties(e.selected[0]);
        });

        canvas.on('selection:cleared', function() {
            document.getElementById('field-properties').style.display = 'none';
        });

        function updateFieldProperties(obj) {
            if (!obj || obj.type !== 'i-text') return;

            document.getElementById('field-properties').style.display = 'block';
            document.getElementById('field-name').value = obj.text;

            // Update field type - compatible with Select2
            const fieldTypeSelect = document.getElementById('field-type');
            fieldTypeSelect.value = obj.fieldType || 'text';
            if ($(fieldTypeSelect).hasClass('select2-hidden-accessible')) {
                $(fieldTypeSelect).trigger('change');
            }

            // Show original font size (unscaled)
            document.getElementById('font-size').value = Math.round(obj.fontSize / canvasScaleRatio);

            // Update font family - compatible with Select2
            const fontFamilySelect = document.getElementById('font-family');
            fontFamilySelect.value = obj.fontFamily;
            if ($(fontFamilySelect).hasClass('select2-hidden-accessible')) {
                $(fontFamilySelect).trigger('change');
            }

            document.getElementById('text-color').value = obj.fill;
            document.getElementById('text-bold').checked = obj.fontWeight === 'bold';
            document.getElementById('text-italic').checked = obj.fontStyle === 'italic';

            // Update alignment buttons
            const currentAlign = obj.textAlign || 'left';
            document.querySelectorAll('.align-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.dataset.align === currentAlign) {
                    btn.classList.add('active');
                }
            });
        }

        // Apply properties to selected object
        document.getElementById('apply-properties-btn').addEventListener('click', function() {
            const activeObject = canvas.getActiveObject();
            if (!activeObject) return;

            // Get values - compatible with Select2
            const fieldTypeValue = $('#field-type').hasClass('select2-hidden-accessible') ?
                $('#field-type').val() :
                document.getElementById('field-type').value;

            const fontFamilyValue = $('#font-family').hasClass('select2-hidden-accessible') ?
                $('#font-family').val() :
                document.getElementById('font-family').value;

            activeObject.set({
                text: document.getElementById('field-name').value,
                fieldType: fieldTypeValue,
                fontSize: parseInt(document.getElementById('font-size').value) * canvasScaleRatio,
                fontFamily: fontFamilyValue,
                fill: document.getElementById('text-color').value,
                fontWeight: document.getElementById('text-bold').checked ? 'bold' : 'normal',
                fontStyle: document.getElementById('text-italic').checked ? 'italic' : 'normal'
            });

            canvas.renderAll();
        });

        // Text align buttons (Field Properties panel)
        document.querySelectorAll('.align-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const activeObject = canvas.getActiveObject();
                if (!activeObject) return;

                const align = this.dataset.align;
                activeObject.set('textAlign', align);
                canvas.renderAll();

                // Update active state
                document.querySelectorAll('.align-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Text align buttons (Add Field Modal)
        document.querySelectorAll('.new-align-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Toggle active state
                document.querySelectorAll('.new-align-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });

        // Save fields to database (convert back to original image coordinates)
        document.getElementById('save-fields-btn').addEventListener('click', function() {
            const objects = canvas.getObjects().filter(obj => obj.type === 'i-text');

            if (objects.length === 0) {
                toastr.warning('No text fields to save. Add at least one field first.');
                return;
            }

            // Build array of position updates
            const updates = objects.map(obj => {
                return {
                    field_name: obj.text,
                    position_data: {
                        x: Math.round(obj.left / canvasScaleRatio),
                        y: Math.round(obj.top / canvasScaleRatio),
                        fontSize: Math.round(obj.fontSize / canvasScaleRatio),
                        fontFamily: obj.fontFamily,
                        color: obj.fill,
                        textAlign: obj.textAlign || 'left',
                        bold: obj.fontWeight === 'bold',
                        italic: obj.fontStyle === 'italic',
                        rotation: Math.round(obj.angle || 0)
                    }
                };
            });

            console.log('Saving field positions:', updates);

            // Send each field update to server
            let successCount = 0;
            let errorCount = 0;
            
            const savePromises = updates.map(update => {
                // Find field ID from table
                const $row = $(`#fields-tbody tr[data-field-name="${update.field_name}"]`);
                const fieldId = $row.data('field-id');
                
                if (!fieldId) {
                    console.warn(`Field ID not found for: ${update.field_name}`);
                    errorCount++;
                    return Promise.resolve();
                }
                
                return $.ajax({
                    url: `/template-fields/${fieldId}/position`,
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    contentType: 'application/json',
                    data: JSON.stringify({
                        position_data: update.position_data
                    })
                }).done(() => {
                    successCount++;
                }).fail((xhr) => {
                    console.error(`Failed to save ${update.field_name}:`, xhr);
                    if (xhr.responseJSON) {
                        console.error('Validation errors:', xhr.responseJSON);
                    }
                    errorCount++;
                });
            });

            Promise.all(savePromises).then(() => {
                if (errorCount === 0) {
                    toastr.success(`All ${successCount} field positions saved successfully!`);
                } else {
                    toastr.warning(`Saved ${successCount} fields, ${errorCount} failed`);
                }
            });
        });

        // Download preview as PDF with dummy data
        document.getElementById('download-preview-btn').addEventListener('click', function() {
            const objects = canvas.getObjects().filter(obj => obj.type === 'i-text');

            if (objects.length === 0) {
                toastr.warning('No text fields to preview. Add at least one field first.');
                return;
            }

            // Redirect to preview endpoint which will generate and download the PDF
            window.location.href = '{{ route("templates.preview", $template->id) }}';
        });
        // });
    </script>
@endpush
