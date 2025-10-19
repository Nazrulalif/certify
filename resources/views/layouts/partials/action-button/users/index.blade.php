 <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click"
     data-kt-menu-placement="bottom-end">
     Actions
     <i class="ki-duotone ki-down fs-5 ms-1"></i>
 </a>
 <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4"
     data-kt-menu="true">
     <div class="menu-item px-3">
         <a href="{{ route('users.edit', $row->id) }}" class="menu-link px-3">
             <i class="ki-duotone ki-notepad-edit fs-5 me-2">
                 <span class="path1"></span>
                 <span class="path2"></span>
             </i>
             Edit
         </a>
     </div>

     @if ($row->id != Auth::user()->id)
         @if ($row->status == true)
             <div class="menu-item px-3">
                 <a href="#" class="menu-link px-3" action-row-table-1="deactivate"
                     data-id="{{ route('users.deactive', $row->id) }}">
                     <i class="ki-duotone ki-cross-circle fs-5 me-2">
                         <span class="path1"></span>
                         <span class="path2"></span>
                     </i>Deactivate</a>
             </div>
         @else
             <div class="menu-item px-3">
                 <a href="#" class="menu-link px-3" action-row-table-1="reactivate"
                     data-id="{{ route('users.reactive', $row->id) }}">
                     <i class="ki-duotone ki-check-square fs-5 me-2">
                         <span class="path1"></span>
                         <span class="path2"></span>
                     </i>Reactivate</a>
             </div>
         @endif
         <div class="separator my-2"></div>
         <div class="menu-item px-3">
             <a href="#" class="menu-link px-3 text-danger" action-row-table-1="delete"
                 data-id="{{ route('users.destroy', $row->id) }}">
                 <i class="ki-duotone ki-trash-square fs-5 me-2">
                     <span class="path1"></span>
                     <span class="path2"></span>
                     <span class="path3"></span>
                     <span class="path4"></span>
                 </i>Delete</a>
         </div>
     @endif

 </div>
