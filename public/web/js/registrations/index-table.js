"use strict";

var RegistrationsDataTable = (function () {
    var table;
    var datatable;
    var csrf = document.querySelector('meta[name="csrf-token"]').getAttribute("content");

    var initDatatable = function () {
        datatable = $("#registrations-table").DataTable({
            searchDelay: 500,
            processing: true,
            serverSide: true,
            order: [[3, "desc"]],
            select: {
                style: "multi",
                selector: 'td:first-child input[type="checkbox"]',
                className: "row-selected",
            },
            ajax: {
                url: window.location.origin + window.location.pathname,
                type: "GET",
            },
            columns: [
                { data: "id", orderable: false, searchable: false },
                { data: "id", searchable: false },
                { data: "fields", orderable: false, searchable: true },
                { data: "status", searchable: false, orderable: false },
                { data: "registered_at_formatted", searchable: false },
                { data: "action", orderable: false, searchable: false, className: "text-end" },
            ],
            columnDefs: [
                {
                    targets: 0,
                    render: function (data) {
                        return '<div class="form-check form-check-sm form-check-custom form-check-solid"><input class="form-check-input" type="checkbox" value="' + data + '" data-id="' + data + '"/></div>';
                    },
                },
                {
                    targets: 1,
                    render: function (data) {
                        return '#' + data;
                    },
                },
            ],
        });

        datatable.on("draw", function () {
            rows();
            checkboxToolbar();
            toolbars();
            KTMenu.createInstances();
        });
    };

    var search = function () {
        const filterSearch = document.querySelector('[data-kt-registrations-table-filter="search"]');
        if (filterSearch) {
            filterSearch.addEventListener("keyup", function (e) {
                datatable.search(e.target.value).draw();
            });
        }
    };

    var rows = function () {
        const deleteButtons = document.querySelectorAll('[action-row-table-1="delete"]');
        deleteButtons.forEach(function (btn) {
            btn.addEventListener("click", function (event) {
                event.preventDefault();
                let targetElement = event.target;
                while (targetElement && !targetElement.hasAttribute("action-row-table-1")) {
                    targetElement = targetElement.parentElement;
                }
                if (!targetElement) return;

                const tableRow = targetElement.closest("tr");
                const itemId = tableRow.querySelector("td:nth-child(2)").innerText.trim();
                let deleteUrl = targetElement.getAttribute("data-id");

                Swal.fire({
                    title: "Delete Confirmation",
                    text: "Are you sure you want to delete registration " + itemId + "?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Yes, delete it",
                    cancelButtonText: "Cancel",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary",
                    },
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: deleteUrl,
                            type: "DELETE",
                            headers: { "X-CSRF-TOKEN": csrf },
                            success: function (data) {
                                if (data.success) {
                                    Swal.fire({
                                        title: "Deleted!",
                                        text: data.message,
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "OK",
                                        customClass: { confirmButton: "btn fw-bold btn-primary" },
                                    }).then(() => datatable.draw());
                                }
                            },
                        });
                    }
                });
            });
        });
    };

    var toolbars = function () {
        const container = document.querySelector("#registrations-table");
        const toolbarBase = document.querySelector('[data-kt-registrations-toolbar="base"]');
        const toolbarSelected = document.querySelector('[data-kt-registrations-toolbar="selected"]');
        const selectedCount = document.querySelector('[data-kt-registrations-selected-count]');

        if (!container || !toolbarBase || !toolbarSelected || !selectedCount) return;

        const allCheckboxes = container.querySelectorAll('tbody [type="checkbox"]');
        let checkedState = false;
        let count = 0;

        allCheckboxes.forEach((c) => {
            if (c.checked) {
                checkedState = true;
                count++;
            }
        });

        if (checkedState) {
            selectedCount.innerHTML = count;
            toolbarBase.classList.add("d-none");
            toolbarSelected.classList.remove("d-none");
        } else {
            toolbarBase.classList.remove("d-none");
            toolbarSelected.classList.add("d-none");
        }
    };

    var checkboxToolbar = function () {
        const container = document.querySelector("#registrations-table");
        if (!container) return;

        const checkboxes = container.querySelectorAll('[type="checkbox"]');
        const deleteSelected = document.querySelector('[data-kt-registrations-table-select="delete_selected"]');

        checkboxes.forEach((c) => {
            c.addEventListener("click", function () {
                setTimeout(function () { toolbars(); }, 50);
            });
        });

        if (deleteSelected) {
            deleteSelected.addEventListener("click", function () {
                const selectedIds = Array.from(container.querySelectorAll('tbody input[type="checkbox"]:checked')).map((checkbox) => checkbox.dataset.id);

                if (selectedIds.length === 0) {
                    Swal.fire({
                        title: "No Selection",
                        text: "Please select at least one registration to delete.",
                        icon: "info",
                        buttonsStyling: false,
                        confirmButtonText: "OK",
                        customClass: { confirmButton: "btn fw-bold btn-primary" },
                    });
                    return;
                }

                const bulkDeleteUrl = this.getAttribute('data-url');

                Swal.fire({
                    title: "Confirm Deletion",
                    text: "Are you sure you want to delete the " + selectedIds.length + " selected registration(s)?",
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    showLoaderOnConfirm: true,
                    confirmButtonText: "Yes, delete!",
                    cancelButtonText: "Cancel",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary",
                    },
                    preConfirm: function () {
                        return $.ajax({
                            url: bulkDeleteUrl,
                            type: "POST",
                            headers: { "X-CSRF-TOKEN": csrf },
                            data: JSON.stringify({ ids: selectedIds }),
                            contentType: "application/json",
                        }).fail(function() {
                            Swal.showValidationMessage("Request failed");
                        });
                    },
                }).then(function (result) {
                    if (result.isConfirmed && result.value.success) {
                        Swal.fire({
                            title: "Deleted!",
                            text: result.value.message,
                            icon: "success",
                            buttonsStyling: false,
                            confirmButtonText: "OK",
                            customClass: { confirmButton: "btn fw-bold btn-primary" },
                        }).then(function () { datatable.draw(); });
                    }
                });
            });
        }
    };

    return {
        init: function () {
            initDatatable();
            search();
            rows();
            checkboxToolbar();
        },
    };
})();

KTUtil.onDOMContentLoaded(function () {
    RegistrationsDataTable.init();
});
