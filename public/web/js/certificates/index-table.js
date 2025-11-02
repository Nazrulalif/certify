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
            columns: [
                { data: 'checkbox', orderable: false, searchable: false },
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
                { className: 'text-center', targets: 0 },
                { className: 'text-end', targets: 7 }
            ],
            language: {
                emptyTable: "No certificates available"
            }
        });

        table = datatable.$;

        datatable.on('draw', function () {
            handleDeleteRows();
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
        toolbarBase = document.querySelector('[data-kt-certificate-table-toolbar="base"]');
        toolbarSelected = document.querySelector('[data-kt-certificate-table-toolbar="selected"]');
        selectedCount = document.querySelector('[data-kt-certificate-table-select="selected_count"]');

        const deleteSelected = document.querySelector('[data-kt-certificate-table-select="delete_selected"]');

        const checkboxes = table.find('input[type="checkbox"]');

        checkboxes.on('change', function () {
            setTimeout(function () {
                toggleToolbars();
            }, 50);
        });

        deleteSelected.addEventListener('click', function () {
            const selectedIds = [];
            checkboxes.each(function () {
                if ($(this).prop('checked')) {
                    selectedIds.push($(this).val());
                }
            });

            if (selectedIds.length > 0) {
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
                                    const headerCheckbox = table.find('thead input[type="checkbox"]');
                                    headerCheckbox.prop('checked', false);
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
            }
        });
    };

    var toggleToolbars = function () {
        const allCheckboxes = table.find('tbody input[type="checkbox"]');
        const checkedState = false;
        let count = 0;

        allCheckboxes.each(function () {
            if ($(this).prop('checked')) {
                count++;
            }
        });

        if (count > 0) {
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
            handleToolbarSelection();
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
