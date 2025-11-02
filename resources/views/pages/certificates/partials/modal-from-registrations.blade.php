<!-- Modal: From Registrations -->
<div class="modal fade" id="modal-from-registrations" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Generate from Registrations</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="form-from-registrations">
                    @csrf
                    <div class="mb-7">
                        <x-form.select label="Select Event" name="event_id" id="event-select-registrations" required>
                            <option value="">Choose an event</option>
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}">{{ $event->name }}</option>
                            @endforeach
                        </x-form.select>
                    </div>

                    <div id="registrations-container" class="d-none">
                        <label class="form-label fw-bold">Select Participants</label>
                        <div class="mb-7 border rounded p-5" style="max-height: 400px; overflow-y: auto;">
                            <div id="registrations-list">
                                <div class="text-center py-10">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-check form-check-custom form-check-solid mb-7">
                            <input class="form-check-input" type="checkbox" id="select-all-registrations">
                            <label class="form-check-label fw-semibold" for="select-all-registrations">
                                Select All
                            </label>
                        </div>
                    </div>

                    <div class="text-center pt-5">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btn-generate-registrations">
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
