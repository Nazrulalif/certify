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

                <!--begin::Form Builder-->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Registration Form Fields</h3>
                        <div class="card-toolbar">
                            <button type="button" class="btn btn-sm btn-primary" onclick="addField()">
                                <i class="ki-duotone ki-plus fs-2"></i>
                                Add Field
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="fields-container">
                            @if (isset($event) && $event->fields->count() > 0)
                                @foreach ($event->fields as $index => $field)
                                    <div class="field-item mb-5 p-5 border border-gray-300 rounded position-relative"
                                        data-index="{{ $index }}">
                                        <button type="button"
                                            class="btn btn-sm btn-icon btn-light-danger position-absolute"
                                            style="top: 10px; right: 10px;" onclick="removeField(this)">
                                            <i class="ki-duotone ki-cross fs-2"></i>
                                        </button>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <x-form.input label="Field Label" :name="'fields[' . $index . '][field_label]'" :value="old(
                                                    'fields.' . $index . '.field_label',
                                                    $field->field_label,
                                                )"
                                                    placeholder="e.g., Participant Name" size="sm" required />
                                            </div>
                                            <div class="col-md-6">
                                                <x-form.input label="Field Name (Internal)" :name="'fields[' . $index . '][field_name]'"
                                                    :value="old(
                                                        'fields.' . $index . '.field_name',
                                                        $field->field_name,
                                                    )" placeholder="e.g., participant_name"
                                                    help="Use lowercase with underscores (no spaces)" size="sm"
                                                    required />
                                            </div>
                                            <div class="col-md-6">
                                                <x-form.select label="Field Type" :name="'fields[' . $index . '][field_type]'"
                                                    class="field-type-select" size="sm" required>
                                                    @foreach ($fieldTypes as $value => $label)
                                                        <option value="{{ $value }}"
                                                            {{ old("fields.{$index}.field_type", $field->field_type) == $value ? 'selected' : '' }}>
                                                            {{ $label }}
                                                        </option>
                                                    @endforeach
                                                </x-form.select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="fw-bold fs-7 mb-2">Options</label>
                                                <div class="form-check form-switch form-check-custom form-check-solid">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="fields[{{ $index }}][required]" value="1"
                                                        {{ old("fields.{$index}.required", $field->required) ? 'checked' : '' }}>
                                                    <label class="form-check-label">Required Field</label>
                                                </div>
                                            </div>
                                            <div class="col-12 select-options-container"
                                                style="{{ $field->field_type === 'select' ? '' : 'display: none;' }}">
                                                <x-form.input label="Dropdown Options (comma-separated)" :name="'fields[' . $index . '][options]'"
                                                    :value="old(
                                                        'fields.' . $index . '.options',
                                                        is_array($field->options) ? implode(', ', $field->options) : '',
                                                    )" placeholder="e.g., Option 1, Option 2"
                                                    size="sm" />
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-10 text-gray-600" id="no-fields-message">
                                    <i class="ki-duotone ki-information-3 fs-5x mb-3">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                        <span class="path3"></span>
                                    </i>
                                    <p>No fields added yet. Click "Add Field" to create your registration form.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
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
        let fieldIndex = {{ isset($event) ? $event->fields->count() : 0 }};

        function addField() {
            const noFieldsMessage = document.getElementById('no-fields-message');
            if (noFieldsMessage) {
                noFieldsMessage.remove();
            }

            const container = document.getElementById('fields-container');
            const fieldHtml = `
                <div class="field-item mb-5 p-5 border border-gray-300 rounded position-relative" data-index="${fieldIndex}">
                    <a class="position-absolute cursor-pointer"
                        style="top: 10px; right: 10px;" onclick="removeField(this)">
                        <i class="ki-duotone ki-cross-circle text-danger fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                    </a>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="required fw-bold fs-7 mb-2">Field Label</label>
                            <input type="text" name="fields[${fieldIndex}][field_label]"
                                class="form-control form-control-sm" placeholder="e.g., Participant Name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="required fw-bold fs-7 mb-2">Field Name (Internal)</label>
                            <input type="text" name="fields[${fieldIndex}][field_name]"
                                class="form-control form-control-sm" placeholder="e.g., participant_name" required>
                            <div class="form-text">Use lowercase with underscores (no spaces)</div>
                        </div>
                        <div class="col-md-6">
                            <label class="required fw-bold fs-7 mb-2">Field Type</label>
                            <select name="fields[${fieldIndex}][field_type]" class="form-select form-select-sm field-type-select" required>
                                @foreach ($fieldTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold fs-7 mb-2">Options</label>
                            <div class="form-check form-switch form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" name="fields[${fieldIndex}][required]" value="1">
                                <label class="form-check-label">Required Field</label>
                            </div>
                        </div>
                        <div class="col-12 select-options-container" style="display: none;">
                            <label class="fw-bold fs-7 mb-2">Dropdown Options (comma-separated)</label>
                            <input type="text" name="fields[${fieldIndex}][options]" class="form-control form-control-sm"
                                placeholder="e.g., Option 1, Option 2">
                        </div>
                    </div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', fieldHtml);
            fieldIndex++;

            // Attach event listener to the newly added field type select
            attachFieldTypeListener();
        }

        function removeField(button) {
            const fieldItem = button.closest('.field-item');
            fieldItem.remove();

            // Show "no fields" message if no fields remain
            const container = document.getElementById('fields-container');
            if (container.children.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-10 text-gray-600" id="no-fields-message">
                        <i class="ki-duotone ki-information-3 fs-5x mb-3">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <p>No fields added yet. Click "Add Field" to create your registration form.</p>
                    </div>
                `;
            }
        }

        function attachFieldTypeListener() {
            document.querySelectorAll('.field-type-select').forEach(select => {
                select.removeEventListener('change', handleFieldTypeChange);
                select.addEventListener('change', handleFieldTypeChange);
            });
        }

        function handleFieldTypeChange(e) {
            const fieldItem = e.target.closest('.field-item');
            const optionsContainer = fieldItem.querySelector('.select-options-container');

            if (e.target.value === 'select') {
                optionsContainer.style.display = 'block';
            } else {
                optionsContainer.style.display = 'none';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            attachFieldTypeListener();
        });

        // Form validation
        document.getElementById('event-form').addEventListener('submit', function(e) {
            const fieldsContainer = document.getElementById('fields-container');
            const fieldItems = fieldsContainer.querySelectorAll('.field-item');

            if (fieldItems.length === 0) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'No Fields Added',
                    text: 'Please add at least one registration form field.'
                });
                return false;
            }
        });
    </script>
@endpush
