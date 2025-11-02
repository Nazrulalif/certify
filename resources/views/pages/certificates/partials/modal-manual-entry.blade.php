<!-- Modal: Manual Entry -->
<div class="modal fade" id="modal-manual-entry" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold">Manual Certificate Entry</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body scroll-y mx-5 mx-xl-15 my-7">
                <form id="form-manual-entry">
                    @csrf
                    <div class="mb-7">
                        <x-form.select label="Select Event" name="event_id" id="event-select-manual" required>
                            <option value="">Choose an event</option>
                            @foreach ($events as $event)
                                <option value="{{ $event->id }}" data-template-id="{{ $event->template_id }}">
                                    {{ $event->name }}
                                </option>
                            @endforeach
                        </x-form.select>
                    </div>

                    <div id="manual-fields-container" class="d-none">
                        <div class="separator separator-dashed my-7"></div>
                        <h4 class="mb-5">Certificate Information</h4>
                        <div id="manual-fields">
                            <!-- Dynamic fields will be loaded here -->
                        </div>
                    </div>

                    <div class="text-center pt-5">
                        <button type="reset" class="btn btn-light me-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="btn-generate-manual">
                            <span class="indicator-label">Generate Certificate</span>
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
