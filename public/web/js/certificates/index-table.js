"use strict";

var KTCertificatesList = function () {
    var table;
    var datatable;
    var toolbarBase;
    var toolbarSelected;
    var selectedCount;

    var initDatatable = function () {
        datatable = $('#certificates-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: window.location.href,
                type: 'GET'
            },
            select: {
                style: "multi",
                selector: 'td:first-child input[type="checkbox"]',
                className: "row-selected",
            },
            columns: [
                { data: "id", name: "id", orderable: false, searchable: false },
                { data: 'certificate_number', name: 'certificate_number' },
                { data: 'event', name: 'event.name' },
                { data: 'recipient', name: 'recipient' },
                { data: 'generated_by', name: 'generator.name' },
                { data: 'generated_at', name: 'generated_at' },
                { data: 'status', name: 'status', orderable: false },
                { data: 'actions', orderable: false, searchable: false }
            ],
            order: [[5, 'desc']],
            columnDefs: [
                {
                    target: 0,
                    orderable: false,
                    render: function (data, type, row) {
                        if (row.myself) {
                            return ``;
                        } else {
                            return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="${data}" data-id="${row}"/>
                            </div>
                        `;
                        }
                    },
                },
                { className: 'text-end', targets: 7 }
            ],
            language: {
                emptyTable: "No certificates available"
            }
        });

        table = datatable.$;

        datatable.on('draw', function () {
            handleDeleteRows();
            handleToolbarSelection();
            KTMenu.createInstances();
        });
    };

    var handleSearchDatatable = function () {
        const filterSearch = document.querySelector('#search-input');
        filterSearch.addEventListener('keyup', function (e) {
            datatable.search(e.target.value).draw();
        });
    };

    var handleDeleteRows = function () {
        const deleteButtons = document.querySelectorAll('[data-kt-certificate-table-filter="delete_row"]');
        deleteButtons.forEach(d => {
            d.addEventListener('click', function (e) {
                e.preventDefault();
                const parent = e.target.closest('tr');
                const certificateId = parent.querySelector('input[type="checkbox"]').value;
                deleteCertificate(certificateId);
            });
        });
    };

    var handleToolbarSelection = function () {
        const container = document.querySelector('#certificates-table');
        toolbarBase = document.querySelector('[data-kt-certificate-table-toolbar="base"]');
        toolbarSelected = document.querySelector('[toolbar-table-1="selected"]');
        selectedCount = document.querySelector('[toolbar-table-1="count"]');

        if (!container || !toolbarBase || !toolbarSelected || !selectedCount) {
            return;
        }

        const deleteSelected = document.querySelector('[action-select-table-1="delete"]');
        const downloadSelected = document.querySelector('[action-select-table-1="download"]');
        const emailSelected = document.querySelector('[action-select-table-1="email"]');

        // Get header checkbox
        const headerCheckbox = container.querySelector('thead input[type="checkbox"]');
        
        // Get all checkboxes in tbody
        const checkboxes = container.querySelectorAll('tbody [type="checkbox"]');

        // Handle "Select All" checkbox in header
        if (headerCheckbox) {
            headerCheckbox.addEventListener('change', function () {
                const isChecked = this.checked;
                checkboxes.forEach(function (checkbox) {
                    checkbox.checked = isChecked;
                });
                setTimeout(function () {
                    toggleToolbars();
                }, 50);
            });
        }

        // Attach change event to each checkbox
        checkboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                // Update header checkbox state
                if (headerCheckbox) {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    const someChecked = Array.from(checkboxes).some(cb => cb.checked);
                    headerCheckbox.checked = allChecked;
                    headerCheckbox.indeterminate = someChecked && !allChecked;
                }
                
                setTimeout(function () {
                    toggleToolbars();
                }, 50);
            });
        });

        // Bulk Download
        if (downloadSelected) {
            downloadSelected.addEventListener('click', function () {
                const selectedIds = Array.from(
                    container.querySelectorAll('tbody input[type="checkbox"]:checked')
                ).map((checkbox) => checkbox.value);

                if (selectedIds.length === 0) {
                    Swal.fire({
                        text: "Please select certificates to download",
                        icon: "warning",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    });
                    return;
                }

                // Show loading
                Swal.fire({
                    title: 'Preparing Download',
                    html: 'Creating ZIP file with ' + selectedIds.length + ' certificate(s)...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Request ZIP file
                $.ajax({
                    url: '/certificates/bulk-download',
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content'),
                        ids: selectedIds
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function (blob, status, xhr) {
                        Swal.close();

                        // Get filename from header or use default
                        const disposition = xhr.getResponseHeader('Content-Disposition');
                        let filename = 'certificates.zip';
                        if (disposition && disposition.indexOf('filename=') !== -1) {
                            const matches = /"([^"]*)"/.exec(disposition);
                            if (matches != null && matches[1]) {
                                filename = matches[1];
                            }
                        }

                        // Create download link
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.href = url;
                        a.download = filename;
                        document.body.appendChild(a);
                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);

                        Swal.fire({
                            text: "Downloaded " + selectedIds.length + " certificate(s) successfully!",
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "Ok, got it!",
                            customClass: {
                                confirmButton: "btn fw-bold btn-primary",
                            }
                        });
                    },
                    error: function (xhr) {
                        Swal.close();
                        
                        // Handle error response
                        let errorMessage = "Failed to download certificates";
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseText) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                errorMessage = response.message || errorMessage;
                            } catch (e) {
                                // Use default message
                            }
                        }

                        Swal.fire({
                            text: errorMessage,
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
        }

        // Bulk Delete
        if (deleteSelected) {
            deleteSelected.addEventListener('click', function () {
                const selectedIds = Array.from(
                    container.querySelectorAll('tbody input[type="checkbox"]:checked')
                ).map((checkbox) => checkbox.value);

                if (selectedIds.length === 0) {
                    Swal.fire({
                        text: "Please select certificates to delete",
                        icon: "warning",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    });
                    return;
                }

                Swal.fire({
                    text: "Are you sure you want to delete " + selectedIds.length + " certificate(s)?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Yes, delete!",
                    cancelButtonText: "No, cancel",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then(function (result) {
                    if (result.value) {
                        $.ajax({
                            url: '/certificates/bulk-destroy',
                            type: 'POST',
                            data: {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                ids: selectedIds
                            },
                            success: function (response) {
                                Swal.fire({
                                    text: response.message,
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    datatable.ajax.reload();
                                    const headerCheckbox = container.querySelector('thead input[type="checkbox"]');
                                    if (headerCheckbox) {
                                        headerCheckbox.checked = false;
                                    }
                                    toggleToolbars();
                                });
                            },
                            error: function (xhr) {
                                Swal.fire({
                                    text: xhr.responseJSON?.message || "Failed to delete certificates",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        });
                    }
                });
            });
        }

        // Email Selected (Placeholder)
        if (emailSelected) {
            emailSelected.addEventListener('click', function () {
                Swal.fire({
                    text: "Email feature is coming soon!",
                    icon: "info",
                    buttonsStyling: false,
                    confirmButtonText: "Ok, got it!",
                    customClass: {
                        confirmButton: "btn fw-bold btn-primary",
                    }
                });
            });
        }
    };

    var toggleToolbars = function () {
        const container = document.querySelector('#certificates-table');
        const toolbarBase = document.querySelector('[data-kt-certificate-table-toolbar="base"]');
        const toolbarSelected = document.querySelector('[toolbar-table-1="selected"]');
        const selectedCount = document.querySelector('[toolbar-table-1="count"]');

        if (!container || !toolbarBase || !toolbarSelected || !selectedCount) {
            return;
        }

        const allCheckboxes = container.querySelectorAll('tbody [type="checkbox"]');

        // Detect checkboxes state & count
        let checkedState = false;
        let count = 0;

        // Count checked boxes
        allCheckboxes.forEach((c) => {
            if (c.checked) {
                checkedState = true;
                count++;
            }
        });

        // Toggle toolbars
        if (checkedState) {
            selectedCount.innerHTML = count;
            toolbarBase.classList.add('d-none');
            toolbarSelected.classList.remove('d-none');
        } else {
            toolbarBase.classList.remove('d-none');
            toolbarSelected.classList.add('d-none');
        }
    };

    return {
        init: function () {
            initDatatable();
            handleSearchDatatable();
        }
    };
}();

window.deleteCertificate = function (id) {
    Swal.fire({
        text: "Are you sure you want to delete this certificate?",
        icon: "warning",
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "No, return",
        customClass: {
            confirmButton: "btn fw-bold btn-danger",
            cancelButton: "btn fw-bold btn-active-light-primary"
        }
    }).then(function (result) {
        if (result.value) {
            $.ajax({
                url: `/certificates/${id}`,
                type: 'DELETE',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    }).then(function () {
                        $('#certificates-table').DataTable().ajax.reload();
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        text: xhr.responseJSON?.message || "Failed to delete certificate",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    });
                }
            });
        }
    });
};

window.regenerateCertificate = function (id) {
    Swal.fire({
        text: "This will regenerate the certificate PDF. Continue?",
        icon: "question",
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonText: "Yes, regenerate!",
        cancelButtonText: "No, cancel",
        customClass: {
            confirmButton: "btn fw-bold btn-primary",
            cancelButton: "btn fw-bold btn-active-light-primary"
        }
    }).then(function (result) {
        if (result.value) {
            $.ajax({
                url: `/certificates/${id}/regenerate`,
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    Swal.fire({
                        text: response.message,
                        icon: "success",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    }).then(function () {
                        $('#certificates-table').DataTable().ajax.reload();
                    });
                },
                error: function (xhr) {
                    Swal.fire({
                        text: xhr.responseJSON?.message || "Failed to regenerate certificate",
                        icon: "error",
                        buttonsStyling: false,
                        confirmButtonText: "Ok, got it!",
                        customClass: {
                            confirmButton: "btn fw-bold btn-primary",
                        }
                    });
                }
            });
        }
    });
};

KTUtil.onDOMContentLoaded(function () {
    KTCertificatesList.init();
});
