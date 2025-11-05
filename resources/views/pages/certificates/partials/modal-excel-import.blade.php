<!-- Modal: Excel Import -->
<div class="modal fade" id="modal-excel-import" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Import from Excel</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="form-excel-import">
                    @csrf
                    <div class="mb-7">
                        <x-form.select label="Select Event" name="event_id" id="event-select-excel" required>
                            <option value="">Choose an event</option>
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>

                    <div class="mb-7">
                        <label class="form-label fw-bold">Upload Excel File</label>
                        <input type="file" name="excel_file" id="excel-file" class="form-control"
                            accept=".xlsx,.xls,.csv" required>
                        <div class="form-text">Supported formats: .xlsx, .xls, .csv</div>
                    </div>

                    <div class="notice d-flex bg-light-info rounded border-info border border-dashed p-6 mb-7">
                        <i class="ki-duotone ki-information-5 fs-2tx text-info me-4">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                        </i>
                        <div class="d-flex flex-stack flex-grow-1">
                            <div class="fw-semibold">
                                <h4 class="text-gray-900 fw-bold">Excel File Format</h4>
                                <div class="fs-6 text-gray-700">
                                    The Excel file should have column headers matching the event's template fields.
                                    <a href="#" class="fw-bold" id="download-template">Download Template</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="excel-preview-container" class="d-none">
                        <div class="separator separator-dashed my-7"></div>
                        <h4 class="mb-5">Preview Data</h4>
                        <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                            <table class="table table-row-bordered table-row-gray-100 align-middle gs-0 gy-3">
                                <thead class="sticky-top bg-light">
                                    <tr class="fw-bold text-muted">
                                        <th class="text-start">#</th>
                                        <th id="preview-headers"></th>
                                    </tr>
                                </thead>
                                <tbody id="preview-body">
                                    <!-- Preview rows will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="text-center pt-5">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-info" id="btn-generate-excel">
                            <span class="indicator-label">Generate Certificates</span>
                            <span class="indicator-progress">Please wait...
                                <span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
