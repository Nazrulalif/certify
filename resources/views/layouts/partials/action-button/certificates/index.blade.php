<div class="d-flex justify-content-end gap-2">
    <a href="{{ route('certificates.show', $row->id) }}" class="btn btn-sm btn-icon btn-light-primary"
        title="View Certificate">
        <i class="ki-duotone ki-eye fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
        </i>
    </a>
    <a href="{{ route('certificates.download', $row->id) }}" class="btn btn-sm btn-icon btn-light-success"
        title="Download PDF" target="_blank">
        <i class="ki-duotone ki-cloud-download fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </a>
    <a href="#" class="btn btn-sm btn-icon btn-light-info" title="Regenerate"
        onclick="regenerateCertificate('{{ $row->id }}'); return false;">
        <i class="ki-duotone ki-arrows-circle fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
        </i>
    </a>
    <a href="#" class="btn btn-sm btn-icon btn-light-danger" title="Delete"
        onclick="deleteCertificate('{{ $row->id }}'); return false;">
        <i class="ki-duotone ki-trash fs-2">
            <span class="path1"></span>
            <span class="path2"></span>
            <span class="path3"></span>
            <span class="path4"></span>
            <span class="path5"></span>
        </i>
    </a>
</div>
