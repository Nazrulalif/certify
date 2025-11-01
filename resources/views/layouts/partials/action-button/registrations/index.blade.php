<div class="dropdown">
    <button class="btn btn-light-primary dropdown-toggle" data-bs-toggle="dropdown">
        Actions
    </button>
    <div class="dropdown-menu dropdown-menu-end">
        <button type="button" class="dropdown-item" onclick="viewDetails({{ json_encode($registration->data) }})">
            <i class="ki-duotone ki-eye fs-5 me-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            View Details
        </button>
        <div class="dropdown-divider"></div>
        <button type="button" class="dropdown-item text-danger" action-row-table-1="delete"
            data-id="{{ route('events.registrations.destroy', [$event->id, $registration->id]) }}">
            <i class="ki-duotone ki-trash fs-5 me-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
                <span class="path5"></span>
            </i>
            Delete
        </button>
    </div>
</div>
