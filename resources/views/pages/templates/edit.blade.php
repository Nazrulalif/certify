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
                    <div class="mb-5">
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
                addTextField(field.field_name, {
                    left: parseFloat(field.x) * canvasScaleRatio,
                    top: parseFloat(field.y) * canvasScaleRatio,
                    fontSize: field.font_size * canvasScaleRatio,
                    fontFamily: field.font_family,
                    fill: field.color,
                    textAlign: field.text_align,
                    fontWeight: field.bold ? 'bold' : 'normal',
                    fontStyle: field.italic ? 'italic' : 'normal',
                    angle: parseFloat(field.rotation) || 0,
                    fieldType: field.field_type
                });
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
                lockScalingFlip: true
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

            const fields = objects.map(obj => {
                // Calculate actual dimensions considering scale
                const actualWidth = obj.width * obj.scaleX;
                const actualHeight = obj.height * obj.scaleY;

                return {
                    field_name: obj.text,
                    field_type: obj.fieldType || 'text',
                    // Convert canvas coordinates back to original image coordinates
                    x: Math.round(obj.left / canvasScaleRatio),
                    y: Math.round(obj.top / canvasScaleRatio),
                    width: Math.round(actualWidth / canvasScaleRatio),
                    height: Math.round(actualHeight / canvasScaleRatio),
                    font_size: Math.round(obj.fontSize / canvasScaleRatio),
                    font_family: obj.fontFamily,
                    color: obj.fill,
                    text_align: obj.textAlign || 'left',
                    bold: obj.fontWeight === 'bold',
                    italic: obj.fontStyle === 'italic',
                    rotation: Math.round(obj.angle || 0)
                };
            });

            console.log('Saving fields:', fields);

            // Send to server
            fetch(`/templates/${templateId}/save-fields`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        fields: fields
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        toastr.success(data.message);
                    } else {
                        toastr.error(data.message);
                    }
                })
                .catch(error => {
                    toastr.error('Failed to save fields: ' + error.message);
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
