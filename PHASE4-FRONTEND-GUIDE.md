# Phase 4: Frontend Implementation Guide

## Overview
Phase 4 focuses on updating the UI to work with the new field system. The key changes are:
1. **Template Builder**: Field definition table + canvas sync
2. **Event Configuration**: Static values form + registration preview
3. **Public Registration**: Dynamic form rendering

---

## Part 1: Template Builder Updates

### Current State
- Fabric.js canvas for positioning fields
- Fields are added/removed on canvas
- Properties panel for styling

### Required Changes

#### A. Add Field Definition Table (Above Canvas)

**Location**: `resources/views/pages/templates/edit.blade.php`

**New Section** (Add before canvas):
```blade
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
                        <th>Field Name</th>
                        <th>Field Label</th>
                        <th>Type</th>
                        <th class="text-center">Show in Form</th>
                        <th class="text-center">Show in Cert</th>
                        <th class="text-center">Required</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="fields-tbody">
                    @foreach($template->fields as $field)
                    <tr data-field-id="{{ $field->id }}" data-field-name="{{ $field->field_name }}">
                        <td>
                            <span class="badge badge-light">{{ $field->field_name }}</span>
                            @if($field->is_predefined)
                            <span class="badge badge-info badge-sm">Predefined</span>
                            @endif
                        </td>
                        <td>{{ $field->field_label }}</td>
                        <td>{{ ucfirst($field->field_type) }}</td>
                        <td class="text-center">
                            <div class="form-check form-check-custom form-check-solid form-check-sm">
                                <input class="form-check-input toggle-show-in-form" type="checkbox" 
                                    {{ $field->show_in_form ? 'checked' : '' }}
                                    data-field-id="{{ $field->id }}">
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-check-custom form-check-solid form-check-sm">
                                <input class="form-check-input toggle-show-in-cert" type="checkbox" 
                                    {{ $field->show_in_cert ? 'checked' : '' }}
                                    data-field-id="{{ $field->id }}">
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="form-check form-check-custom form-check-solid form-check-sm">
                                <input class="form-check-input toggle-required" type="checkbox" 
                                    {{ $field->is_required ? 'checked' : '' }}
                                    data-field-id="{{ $field->id }}"
                                    {{ !$field->show_in_form ? 'disabled' : '' }}>
                            </div>
                        </td>
                        <td>
                            @if(!$field->is_predefined)
                            <button class="btn btn-sm btn-icon btn-light-danger delete-field-btn" 
                                data-field-id="{{ $field->id }}">
                                <i class="ki-duotone ki-trash fs-5"></i>
                            </button>
                            @else
                            <span class="text-muted">-</span>
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
```

#### B. JavaScript for Field Table & Canvas Sync

**Location**: `resources/views/pages/templates/edit.blade.php` (in @push('scripts'))

```javascript
// Toggle "Show in Cert" - Add/Remove from canvas
$('.toggle-show-in-cert').on('change', function() {
    const fieldId = $(this).data('field-id');
    const fieldName = $(this).closest('tr').data('field-name');
    const showInCert = $(this).is(':checked');
    
    // Update field via AJAX
    $.ajax({
        url: `/template-fields/${fieldId}`,
        type: 'PATCH',
        data: {
            _token: '{{ csrf_token() }}',
            show_in_cert: showInCert
        },
        success: function(response) {
            if (showInCert) {
                // Add field to canvas
                addFieldToCanvas(fieldName, response.field);
            } else {
                // Remove field from canvas
                removeFieldFromCanvas(fieldName);
            }
            
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                text: 'Field visibility updated',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
});

// Toggle "Show in Form"
$('.toggle-show-in-form').on('change', function() {
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
        data: {
            _token: '{{ csrf_token() }}',
            show_in_form: showInForm,
            is_required: !showInForm ? false : $requiredCheckbox.is(':checked')
        },
        success: function() {
            Swal.fire({
                icon: 'success',
                title: 'Updated!',
                timer: 1500,
                showConfirmButton: false
            });
        }
    });
});

// Toggle "Required"
$('.toggle-required').on('change', function() {
    const fieldId = $(this).data('field-id');
    const isRequired = $(this).is(':checked');
    
    $.ajax({
        url: `/template-fields/${fieldId}`,
        type: 'PATCH',
        data: {
            _token: '{{ csrf_token() }}',
            is_required: isRequired
        }
    });
});

// Add Custom Field Modal
$('#add-custom-field-btn').on('click', function() {
    Swal.fire({
        title: 'Add Custom Field',
        html: `
            <div class="mb-3">
                <label class="form-label">Field Name (no spaces)</label>
                <input type="text" class="form-control" id="new-field-name" placeholder="e.g., company">
            </div>
            <div class="mb-3">
                <label class="form-label">Field Label</label>
                <input type="text" class="form-control" id="new-field-label" placeholder="e.g., Company Name">
            </div>
            <div class="mb-3">
                <label class="form-label">Field Type</label>
                <select class="form-select" id="new-field-type">
                    <option value="text">Text</option>
                    <option value="email">Email</option>
                    <option value="date">Date</option>
                    <option value="number">Number</option>
                    <option value="textarea">Textarea</option>
                </select>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="new-show-in-form" checked>
                <label class="form-check-label">Show in Registration Form</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="checkbox" id="new-show-in-cert">
                <label class="form-check-label">Show on Certificate</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="new-required">
                <label class="form-check-label">Required Field</label>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Add Field',
        preConfirm: () => {
            const fieldName = $('#new-field-name').val();
            const fieldLabel = $('#new-field-label').val();
            
            if (!fieldName || !fieldLabel) {
                Swal.showValidationMessage('Please fill all required fields');
                return false;
            }
            
            return {
                field_name: fieldName,
                field_label: fieldLabel,
                field_type: $('#new-field-type').val(),
                show_in_form: $('#new-show-in-form').is(':checked'),
                show_in_cert: $('#new-show-in-cert').is(':checked'),
                is_required: $('#new-required').is(':checked')
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/templates/{{ $template->id }}/fields`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ...result.value
                },
                success: function(response) {
                    // Add row to table
                    location.reload(); // Or dynamically add row
                    
                    Swal.fire('Added!', 'Custom field added successfully', 'success');
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON.message, 'error');
                }
            });
        }
    });
});

// Delete Field
$('.delete-field-btn').on('click', function() {
    const fieldId = $(this).data('field-id');
    const $row = $(this).closest('tr');
    
    Swal.fire({
        title: 'Delete Field?',
        text: 'This cannot be undone',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/template-fields/${fieldId}`,
                type: 'DELETE',
                data: { _token: '{{ csrf_token() }}' },
                success: function() {
                    $row.fadeOut(300, function() { $(this).remove(); });
                    Swal.fire('Deleted!', 'Field has been deleted', 'success');
                },
                error: function(xhr) {
                    Swal.fire('Error!', xhr.responseJSON.message, 'error');
                }
            });
        }
    });
});

// Helper: Add field to canvas
function addFieldToCanvas(fieldName, fieldData) {
    const text = new fabric.IText(fieldName, {
        left: 100,
        top: 100,
        fontSize: 20,
        fill: '#000000',
        fontFamily: 'Arial',
        fieldName: fieldName
    });
    
    canvas.add(text);
    canvas.setActiveObject(text);
    canvas.renderAll();
}

// Helper: Remove field from canvas
function removeFieldFromCanvas(fieldName) {
    const objects = canvas.getObjects();
    objects.forEach(obj => {
        if (obj.fieldName === fieldName) {
            canvas.remove(obj);
        }
    });
    canvas.renderAll();
}
```

---

## Part 2: Event Configuration Updates

### A. Static Values Form

**Location**: `resources/views/pages/events/create.blade.php` or `edit.blade.php`

```blade
<!--begin::Static Values Section-->
<div class="card mb-5" id="static-values-card" style="display: none;">
    <div class="card-header">
        <h3 class="card-title">Certificate Static Values</h3>
        <span class="text-muted">These values will appear on all certificates for this event</span>
    </div>
    <div class="card-body" id="static-values-container">
        <!-- Dynamically loaded -->
    </div>
</div>
<!--end::Static Values Section-->

@push('scripts')
<script>
// When template is selected, load static value fields
$('#template_id').on('change', function() {
    const templateId = $(this).val();
    
    if (!templateId) {
        $('#static-values-card').hide();
        return;
    }
    
    $.ajax({
        url: `/templates/${templateId}/static-value-fields`,
        type: 'GET',
        success: function(response) {
            if (response.fields.length > 0) {
                renderStaticValuesForm(response.fields);
                $('#static-values-card').show();
            } else {
                $('#static-values-card').hide();
            }
        }
    });
});

function renderStaticValuesForm(fields) {
    let html = '';
    
    fields.forEach(field => {
        html += `
            <div class="mb-5">
                <label class="form-label required">${field.field_label}</label>
                ${getInputForFieldType(field)}
                <div class="form-text">This will appear on all certificates</div>
            </div>
        `;
    });
    
    $('#static-values-container').html(html);
}

function getInputForFieldType(field) {
    switch (field.field_type) {
        case 'date':
            return `<input type="date" class="form-control" name="static_values[${field.field_name}]" required>`;
        case 'number':
            return `<input type="number" class="form-control" name="static_values[${field.field_name}]" required>`;
        case 'textarea':
            return `<textarea class="form-control" name="static_values[${field.field_name}]" rows="3" required></textarea>`;
        default:
            return `<input type="text" class="form-control" name="static_values[${field.field_name}]" required>`;
    }
}
</script>
@endpush
```

### B. Registration Form Preview

```blade
<!--begin::Registration Preview-->
<div class="card mb-5">
    <div class="card-header">
        <h3 class="card-title">Registration Form Preview</h3>
        <button type="button" class="btn btn-sm btn-light" id="refresh-preview-btn">
            <i class="ki-duotone ki-arrows-circle fs-3"></i>
            Refresh
        </button>
    </div>
    <div class="card-body">
        <div class="alert alert-info">
            <i class="ki-duotone ki-information fs-2x">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            This is how participants will see the registration form
        </div>
        <div id="form-preview-container">
            <!-- Dynamically loaded -->
        </div>
    </div>
</div>

@push('scripts')
<script>
function loadFormPreview(eventId) {
    $.ajax({
        url: `/events/${eventId}/registration-form-preview`,
        type: 'GET',
        success: function(response) {
            renderFormPreview(response.fields);
        }
    });
}

function renderFormPreview(fields) {
    let html = '<form class="form">';
    
    fields.forEach(field => {
        const requiredLabel = field.required ? '<span class="text-danger">*</span>' : '';
        
        html += `
            <div class="mb-5">
                <label class="form-label">${field.label} ${requiredLabel}</label>
                ${getPreviewInputForType(field)}
            </div>
        `;
    });
    
    html += `
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-primary" disabled>Submit Registration</button>
        </div>
    </form>`;
    
    $('#form-preview-container').html(html);
}

function getPreviewInputForType(field) {
    const attrs = `class="form-control" ${field.required ? 'required' : ''} disabled`;
    
    switch (field.type) {
        case 'email':
            return `<input type="email" ${attrs} placeholder="Enter ${field.label.toLowerCase()}">`;
        case 'date':
            return `<input type="date" ${attrs}>`;
        case 'number':
            return `<input type="number" ${attrs}>`;
        case 'textarea':
            return `<textarea ${attrs} rows="3" placeholder="Enter ${field.label.toLowerCase()}"></textarea>`;
        default:
            return `<input type="text" ${attrs} placeholder="Enter ${field.label.toLowerCase()}">`;
    }
}
</script>
@endpush
```

---

## Part 3: Public Registration Form

**Location**: `resources/views/pages/registrations/form.blade.php`

```blade
@extends('layouts.guest')

@section('content')
<div class="container py-10">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary">
                    <h2 class="card-title text-white mb-0">{{ $formConfig['event']['name'] }}</h2>
                </div>
                <div class="card-body">
                    @if($formConfig['event']['description'])
                    <div class="alert alert-light mb-5">
                        {{ $formConfig['event']['description'] }}
                    </div>
                    @endif

                    <form method="POST" action="{{ route('register.store', $event->slug) }}">
                        @csrf

                        @foreach($formConfig['fields'] as $field)
                        <div class="mb-5">
                            <label class="form-label {{ $field['required'] ? 'required' : '' }}">
                                {{ $field['label'] }}
                            </label>
                            
                            @if($field['type'] === 'textarea')
                                <textarea 
                                    name="{{ $field['name'] }}" 
                                    class="form-control @error($field['name']) is-invalid @enderror" 
                                    rows="3"
                                    {{ $field['required'] ? 'required' : '' }}
                                    placeholder="{{ $field['placeholder'] ?? '' }}"
                                >{{ old($field['name']) }}</textarea>
                            @else
                                <input 
                                    type="{{ $field['type'] }}" 
                                    name="{{ $field['name'] }}" 
                                    class="form-control @error($field['name']) is-invalid @enderror"
                                    value="{{ old($field['name']) }}"
                                    {{ $field['required'] ? 'required' : '' }}
                                    placeholder="{{ $field['placeholder'] ?? '' }}">
                            @endif

                            @error($field['name'])
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endforeach

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted"><span class="text-danger">*</span> Required fields</span>
                            <button type="submit" class="btn btn-primary">
                                <i class="ki-duotone ki-check fs-2"></i>
                                Submit Registration
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
```

---

## Implementation Checklist

### Template Builder
- [ ] Add field definition table above canvas
- [ ] Implement toggle checkboxes (show_in_form, show_in_cert, required)
- [ ] Add "Add Custom Field" button with modal
- [ ] Delete custom field functionality
- [ ] Sync table with canvas (show/hide fields)
- [ ] AJAX calls to update field properties

### Event Configuration
- [ ] Template dropdown triggers static values form load
- [ ] Render dynamic static values form
- [ ] Show registration form preview
- [ ] Validate static values before saving

### Public Registration
- [ ] Dynamic form rendering from template fields
- [ ] All field types supported (text, email, date, number, textarea)
- [ ] Client-side validation
- [ ] Display validation errors
- [ ] Required field indicators

### Testing
- [ ] Create template with custom fields
- [ ] Toggle field visibility
- [ ] Create event and fill static values
- [ ] Test public registration form
- [ ] Verify certificate generation with merged data

---

## Status: Ready for Implementation
All backend APIs are ready. Frontend just needs to consume these endpoints.
