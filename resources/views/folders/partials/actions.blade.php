@if(checkAllowedModule('folders', 'folder.show')->isNotEmpty())
    <a href="{{ route('folder.show', ['folder_id' => encode_id($folder->id)]) }}" class="btn btn-sm btn-primary" title="View Folder">
        <i class="fa fa-eye"></i> View
    </a> 
@endif  

@if(checkAllowedModule('folders', 'folder.edit')->isNotEmpty()) 
    <button class="btn btn-sm btn-secondary edit-folder-icon" data-folder-id="{{ encode_id($folder->id) }}" title="Edit Folder">  
        <i class="fa fa-edit"></i> Edit
    </button>
@endif

@if(checkAllowedModule('folders', 'folder.delete')->isNotEmpty())
    <button class="btn btn-sm btn-primary delete-folder-icon" data-folder-id="{{ encode_id($folder->id) }}" title="Delete Folder">
        <i class="fa fa-solid fa-trash"></i> Delete
    </button>
@endif