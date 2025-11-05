"use strict";

// Method 1: From Registrations
(function () {
    const eventSelect = $('#event-select-registrations');
    const registrationsContainer = $('#registrations-container');
    const registrationsList = $('#registrations-list');
    const selectAllCheckbox = $('#select-all-registrations');
    const form = $('#form-from-registrations');
    const submitBtn = $('#btn-generate-registrations');

    eventSelect.on('change', function () {
        const eventId = $(this).val();
        if (!eventId) {
            registrationsContainer.addClass('d-none');
            return;
        }

        // Show loading
        registrationsList.html(`
            <div class="text-center py-10">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        `);
        registrationsContainer.removeClass('d-none');

        // Fetch registrations
        $.ajax({
            url: `/v1/events/${eventId}/registrations`,
            type: 'GET',
            success: function (data) {
                if (data.length === 0) {
                    registrationsList.html(`
                        <div class="text-center py-10 text-gray-600">
                            <i class="ki-duotone ki-information-3 fs-5x mb-3">
                                <span class="path1"></span>
                                <span class="path2"></span>
                                <span class="path3"></span>
                            </i>
                            <p>No registrations available for certificate generation.</p>
                        </div>
                    `);
                    return;
                }

                let html = '';
                data.forEach(function (registration) {
                    html += `
                        <div class="form-check form-check-custom form-check-solid mb-3">
                            <input class="form-check-input registration-checkbox" type="checkbox"
                                name="registration_ids[]" value="${registration.id}" id="reg-${registration.id}">
                            <label class="form-check-label" for="reg-${registration.id}">
                                <span class="fw-bold">${registration.name}</span>
                                <span class="text-muted ms-2">(${registration.registered_at})</span>
                            </label>
                        </div>
                    `;
                });
                registrationsList.html(html);
            },
            error: function () {
                registrationsList.html(`
                    <div class="text-center py-10 text-danger">
                        <p>Failed to load registrations. Please try again.</p>
                    </div>
                `);
            }
        });
    });

    // Select all functionality
    selectAllCheckbox.on('change', function () {
        $('.registration-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Form submission
    form.on('submit', function (e) {
        e.preventDefault();

        const selectedIds = [];
        $('.registration-checkbox:checked').each(function () {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            Swal.fire({
                text: "Please select at least one registration",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary",
                }
            });
            return;
        }

        submitBtn.attr('data-kt-indicator', 'on').prop('disabled', true);

        $.ajax({
            url: '/certificates/generate-from-registrations',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                registration_ids: selectedIds
            },
            success: function (response) {
                submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);

                let message = response.message;
                if (response.errors && response.errors.length > 0) {
                    message += '\n\nErrors:\n' + response.errors.join('\n');
                }

                Swal.fire({
                    text: message,
                    icon: response.errors.length > 0 ? "warning" : "success",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                    }
                }).then(function () {
                    if (response.success) {
                        window.location.href = '/certificates';
                    }
                });
            },
            error: function (xhr) {
                submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);
                Swal.fire({
                    text: xhr.responseJSON?.message || "Failed to generate certificates",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                    }
                });
            }
        });
    });
})();

// Method 2: Manual Entry
(function () {
    const eventSelect = $('#event-select-manual');
    const fieldsContainer = $('#manual-fields-container');
    const fieldsDiv = $('#manual-fields');
    const form = $('#form-manual-entry');
    const submitBtn = $('#btn-generate-manual');

    eventSelect.on('change', function () {
        const eventId = $(this).val();
        if (!eventId) {
            fieldsContainer.addClass('d-none');
            return;
        }

        const event = window.eventsData[eventId];
        if (!event || !event.fields || event.fields.length === 0) {
            fieldsContainer.addClass('d-none');
            return;
        }

        let html = '';
        event.fields.forEach(function (field) {
            html += '<div class="mb-5">';
            html += `<label class="form-label ${field.required ? 'required' : ''}">${field.field_label}</label>`;

            if (field.field_type === 'textarea') {
                html += `<textarea name="data[${field.field_name}]" class="form-control" rows="3" ${field.required ? 'required' : ''}></textarea>`;
            } else if (field.field_type === 'select') {
                html += `<select name="data[${field.field_name}]" class="form-select" ${field.required ? 'required' : ''}>`;
                html += '<option value="">Select an option</option>';
                if (field.options) {
                    field.options.forEach(function (option) {
                        html += `<option value="${option}">${option}</option>`;
                    });
                }
                html += '</select>';
            } else if (field.field_type === 'date') {
                html += `<input type="date" name="data[${field.field_name}]" class="form-control" ${field.required ? 'required' : ''}>`;
            } else if (field.field_type === 'email') {
                html += `<input type="email" name="data[${field.field_name}]" class="form-control" ${field.required ? 'required' : ''}>`;
            } else {
                html += `<input type="text" name="data[${field.field_name}]" class="form-control" ${field.required ? 'required' : ''}>`;
            }

            html += '</div>';
        });

        fieldsDiv.html(html);
        fieldsContainer.removeClass('d-none');
    });

    // Form submission
    form.on('submit', function (e) {
        e.preventDefault();

        submitBtn.attr('data-kt-indicator', 'on').prop('disabled', true);

        $.ajax({
            url: '/certificates/generate-manual',
            type: 'POST',
            data: form.serialize(),
            success: function (response) {
                submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);

                Swal.fire({
                    text: response.message,
                    icon: "success",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                    }
                }).then(function () {
                    window.location.href = '/certificates';
                });
            },
            error: function (xhr) {
                submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);
                Swal.fire({
                    text: xhr.responseJSON?.message || "Failed to generate certificate",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                    }
                });
            }
        });
    });
})();

// Method 3: Excel Import
(function () {
    const eventSelect = $('#event-select-excel');
    const fileInput = $('#excel-file');
    const previewContainer = $('#excel-preview-container');
    const previewHeaders = $('#preview-headers');
    const previewBody = $('#preview-body');
    const downloadTemplate = $('#download-template');
    const form = $('#form-excel-import');
    const submitBtn = $('#btn-generate-excel');
    
    let validatedData = null;

    // Download template
    downloadTemplate.on('click', function (e) {
        e.preventDefault();
        
        const eventId = eventSelect.val();
        if (!eventId) {
            Swal.fire({
                text: "Please select an event first",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary",
                }
            });
            return;
        }

        // Download template
        window.location.href = `/events/${eventId}/certificate-template`;
    });

    // File input change - validate and preview
    fileInput.on('change', function () {
        const eventId = eventSelect.val();
        if (!eventId) {
            Swal.fire({
                text: "Please select an event first",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary",
                }
            });
            $(this).val('');
            return;
        }

        const file = this.files[0];
        if (!file) {
            previewContainer.addClass('d-none');
            validatedData = null;
            return;
        }

        // Show loading
        previewContainer.removeClass('d-none');
        previewHeaders.html('<th colspan="100%">Validating...</th>');
        previewBody.html(`
            <tr>
                <td colspan="100%" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </td>
            </tr>
        `);

        // Prepare form data
        const formData = new FormData();
        formData.append('event_id', eventId);
        formData.append('excel_file', file);
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        // Validate Excel file
        $.ajax({
            url: '/certificates/import-excel',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                validatedData = response.data;

                // Show validation results
                if (response.invalid_rows > 0) {
                    Swal.fire({
                        title: "Validation Warning",
                        html: `
                            <p>Found <strong>${response.invalid_rows}</strong> invalid row(s) out of ${response.total_rows} total.</p>
                            <p>Only <strong>${response.valid_rows}</strong> valid row(s) will be processed.</p>
                            <p class="text-muted mt-3">Check the preview below for details.</p>
                        `,
                        icon: "warning",
                        buttonsStyling: false,
                        confirmButtonText: "Continue",
                        customClass: {
                            confirmButton: "btn fw-bold btn-warning",
                        }
                    });
                }

                // Get headers from first row
                if (response.data.length > 0 || response.errors.length > 0) {
                    const sampleData = response.data.length > 0 ? response.data[0].data : response.errors[0].data;
                    const headers = Object.keys(sampleData);
                    
                    let headerHtml = '';
                    headers.forEach(header => {
                        headerHtml += `<th class="text-start">${header}</th>`;
                    });
                    headerHtml += '<th class="text-center">Status</th>';
                    previewHeaders.html(headerHtml);

                    // Show preview rows (max 10)
                    let bodyHtml = '';
                    const allRows = [...response.data, ...response.errors].slice(0, 10);
                    
                    allRows.forEach(row => {
                        const isValid = response.data.includes(row);
                        const rowClass = isValid ? '' : 'table-danger';
                        
                        bodyHtml += `<tr class="${rowClass}">`;
                        bodyHtml += `<td class="text-muted">${row.row}</td>`;
                        
                        headers.forEach(header => {
                            bodyHtml += `<td>${row.data[header] || '-'}</td>`;
                        });
                        
                        if (isValid) {
                            bodyHtml += '<td class="text-center"><span class="badge badge-success">Valid</span></td>';
                        } else {
                            const errors = row.errors ? row.errors.join('<br>') : 'Invalid data';
                            bodyHtml += `<td class="text-center">
                                <span class="badge badge-danger" data-bs-toggle="tooltip" title="${errors}">Invalid</span>
                            </td>`;
                        }
                        
                        bodyHtml += '</tr>';
                    });

                    if (allRows.length < response.total_rows) {
                        bodyHtml += `<tr><td colspan="100%" class="text-center text-muted py-3">
                            ... and ${response.total_rows - allRows.length} more rows
                        </td></tr>`;
                    }

                    previewBody.html(bodyHtml);

                    // Initialize tooltips
                    $('[data-bs-toggle="tooltip"]').tooltip();
                }
            },
            error: function (xhr) {
                previewContainer.addClass('d-none');
                validatedData = null;
                
                Swal.fire({
                    text: xhr.responseJSON?.message || "Failed to validate Excel file",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                    }
                });
            }
        });
    });

    // Event select change - reset file
    eventSelect.on('change', function () {
        fileInput.val('');
        previewContainer.addClass('d-none');
        validatedData = null;
    });

    // Form submission
    form.on('submit', function (e) {
        e.preventDefault();

        if (!validatedData || validatedData.length === 0) {
            Swal.fire({
                text: "Please upload and validate an Excel file first",
                icon: "warning",
                buttonsStyling: false,
                confirmButtonText: "Ok, got it!",
                customClass: {
                    confirmButton: "btn fw-bold btn-primary",
                }
            });
            return;
        }

        submitBtn.attr('data-kt-indicator', 'on').prop('disabled', true);

        // Generate certificates from validated data
        $.ajax({
            url: '/certificates/generate-from-excel',
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                event_id: eventSelect.val(),
                data: validatedData
            },
            success: function (response) {
                submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);

                let icon = 'success';
                let title = 'Success';
                let html = `<p>${response.message}</p>`;

                if (response.error_count > 0) {
                    icon = 'warning';
                    title = 'Partially Completed';
                    html += `<p class="text-muted mt-3">Successfully generated: ${response.success_count}<br>Failed: ${response.error_count}</p>`;
                    
                    if (response.errors && response.errors.length > 0) {
                        html += '<div class="text-start mt-3"><strong>Errors:</strong><ul class="mt-2">';
                        response.errors.slice(0, 5).forEach(error => {
                            html += `<li>Row ${error.row}: ${error.error}</li>`;
                        });
                        if (response.errors.length > 5) {
                            html += `<li>... and ${response.errors.length - 5} more errors</li>`;
                        }
                        html += '</ul></div>';
                    }
                }

                Swal.fire({
                    title: title,
                    html: html,
                    icon: icon,
                    buttonsStyling: false,
                    confirmButtonText: "View Certificates",
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                    }
                }).then(function () {
                    window.location.href = '/certificates';
                });
            },
            error: function (xhr) {
                submitBtn.removeAttr('data-kt-indicator').prop('disabled', false);
                
                Swal.fire({
                    text: xhr.responseJSON?.message || "Failed to generate certificates",
                    icon: "error",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                    }
                });
            }
        });
    });
})();
