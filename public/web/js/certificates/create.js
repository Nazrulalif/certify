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

// Method 3: Excel Import (Placeholder - to be implemented)
(function () {
    const form = $('#form-excel-import');
    const submitBtn = $('#btn-generate-excel');

    form.on('submit', function (e) {
        e.preventDefault();

        Swal.fire({
            text: "Excel import feature is coming soon!",
            icon: "info",
            buttonsStyling: false,
            confirmButtonText: "Ok, got it!",
            customClass: {
                confirmButton: "btn fw-bold btn-primary",
            }
        });
    });

    $('#download-template').on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            text: "Template download feature is coming soon!",
            icon: "info",
            buttonsStyling: false,
            confirmButtonText: "Ok, got it!",
            customClass: {
                confirmButton: "btn fw-bold btn-primary",
            }
        });
    });
})();
