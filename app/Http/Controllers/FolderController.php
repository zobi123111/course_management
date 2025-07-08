<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\OrganizationUnits;
use App\Models\Document;
use App\Models\Group;
use App\Models\FolderGroupAccess;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use File;

class FolderController extends Controller
{
    public function index()
    {  
        $ou_id = auth()->user()->ou_id;
        $organizationUnits = OrganizationUnits::all();
        if (Auth::user()->is_owner == 1) {
            // Admin without OU restriction: Fetch all folders with their children
            $folders = Folder::whereNull('parent_id')->with('children')->get();
            $documents = Document::whereNull('folder_id')->get();
            $groups = Group::all();
            // dd($documents);
        } else {           
            // Regular users: Fetch only their org unit folders
            $groups = Group::where('ou_id', $ou_id)->get();
            $folders = Folder::where('ou_id', Auth::user()->ou_id)
                ->whereNull('parent_id')
                ->with(['children' => function ($query) use ($ou_id) {
                    $query->where('ou_id', $ou_id);
                }])
                ->get();
            $documents = Document::whereNull('folder_id')->where('ou_id', Auth::user()->ou_id)->get();
        }
    
        return view('folders.index',compact('folders', 'documents', 'organizationUnits', 'groups'));
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'folder_name' => 'required|unique:folders,folder_name',
            'description' => 'required',
            'status' => 'required|boolean',
            'parent_id' => [
                'nullable',
                'exists:folders,id', // Ensures the provided parent_id exists in the folders table
            ],
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ]
        ]);
        Folder::create([
            'ou_id' => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id,
            'folder_name' => $request->folder_name,
            'description' => $request->description,
            'status' => $request->status,
            'parent_id' => $request->parent_id, // Assign parent_id if provided
        ]);

        Session::flash('message', 'Folder created successfully.');
        return response()->json(['success' => 'Folder created successfully.']);
    
    }

    public function getFolder(Request $request)
    {    
        $folder = Folder::with('groups')->findOrFail(decode_id($request->id));

        // Get all group IDs related to the folder
        $groupIds = $folder->groups->pluck('id')->toArray();
   
        $ou_id = $folder->ou_id;
        $org_folders = Folder::where('ou_id', $ou_id)->whereNull('parent_id') ->with('childrenRecursive') ->get();

        // Fetch all folders to display in the dropdown
        if (Auth::user()->role == 1 && empty(Auth::user()->ou_id)) {
            $folders = Folder::with('children')->get();
        } else {
            $folders = Folder::where('ou_id', Auth::user()->ou_id)
                            ->with('children')
                            ->get();
        }
    
        return response()->json([
            'folders' => $folders,
            'folder' => $folder,
            'org_folders' => $org_folders,
            'current_folder_id' => $folder->id,
            'selected_parent_id' => $folder->parent_id,
            'group_ids' => $groupIds
        ]);
    }

    public function updateFolder(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'folder_id'   => 'required|exists:folders,id',
            'folder_name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'status'      => 'required|in:0,1', // Assuming status is active/inactive
            'parent_id'   => 'nullable|exists:folders,id',
            'ou_id'       => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
            'is_published' => 'nullable|boolean',
            'group' => 'nullable|array',
            'group.*' => 'exists:groups,id'
        ]);
    
        $folder = Folder::findOrFail($request->folder_id);  
    
        // Prevent setting the folder as its own parent
        if ($request->parent_id == $folder->id) {
            return response()->json(['error' => 'A folder cannot be its own parent.']);
        }
    
        //Prevent moving a folder into its own subfolder (infinite loop prevention)
        if ($this->isDescendant($folder->id, $request->parent_id)) {
            return response()->json(['error' => 'Cannot move a folder into its own subfolder.']);
        }
        // dd((auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id);

        $ou_id =(auth()->user()->is_owner == 1) ? $request->ou_id : auth()->user()->ou_id;
        // Update Parent folder
        $folder->update([
            'folder_name' => $request->folder_name,
            'description' => $request->description,
            'status'      => $request->status,
            'parent_id'   => $request->parent_id,
            'ou_id'       => $ou_id,
            'is_published'  => $request->filled('is_published') ? $request->is_published : 0,
        ]);

        //update child folder
        $check_parent = Folder::where('id', $request->folder_id)
                        ->whereNull('parent_id')
                        ->exists();
    
        if ($check_parent) {
            // Update child folders' ou_id
            Folder::where('parent_id', $request->folder_id)->update(['ou_id' => $ou_id]);
        }

        // Update access control with timestamps
        if ($request->filled('is_published') && $request->has('group')) {
            // Detach all existing group access (including soft-deleted)
            $folder->groups()->detach();

            // Prepare attach data with timestamps
            $now = now();
            $attachData = [];

            foreach ($request->group as $groupId) {
                $attachData[$groupId] = [
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            // Attach with timestamps
            $folder->groups()->attach($attachData);
        } else {
            // Remove all group access
            $folder->groups()->detach();
        }


        Session::flash('message', 'Folder updated successfully.');
        return response()->json(['success' => 'Folder updated successfully.']);
    }

    public function deleteFolder(Request $request)
    {
        $folder = Folder::findOrFail(decode_id($request->folder_id));
     
          // dd($folder->children()->exists());
        // Check if the folder has any subfolders
        if ($folder->children()->exists()) { 
            return redirect()->back()->with('error', 'Cannot delete this folder because it contains subfolders.');
        }

        // Store parent_id before deletion to determine redirection
        $parentFolderId = $folder->parent_id;

        // Delete the folder
        $folder->delete();

        // Redirect logic based on parent folder existence
        if ($parentFolderId) {
            return redirect()->route('folder.show', ['folder_id' => encode_id($parentFolderId)])
                            ->with('message', 'Folder deleted successfully.');
        } else {
            return redirect()->route('folder.index')->with('message', 'Folder deleted successfully.');
        }
    }

    public function getFolders(Request $request)
    { 
        $query = Folder::query();
        // dd(Auth::user()->ou_id);
        $documentIds = Document::where('ou_id', Auth::user()->ou_id)->pluck('folder_id');
        //dd($documentIds);
        // Apply user-based folder filtering
        if (Auth::user()->role == 1 && empty(Auth::user()->ou_id) && Auth::user()->is_owner) { 
            // Admin without OU restriction: Fetch all folders
            $query->whereNull('parent_id')->with('children');
        } 
        else if(Auth::user()->is_admin == 1){ 
            $query->where('ou_id', Auth::user()->ou_id)->whereNull('parent_id')->with('children');
        }
        else {
            //$query->where('ou_id', Auth::user()->ou_id)->whereNull('parent_id')->with('children');
            $query->where('ou_id', Auth::user()->ou_id)->whereIn('id', $documentIds);

        }

        // Get total record count before filtering
        $totalRecords = (clone $query)->count();

        // Clone the query for filtering
        $filteredQuery = clone $query;
        //  dd($filteredQuery);

        // Apply search filter
        if ($request->has('search') && !empty($request->search['value'])) {
            $searchValue = $request->search['value'];

            $filteredQuery->where(function ($q) use ($searchValue) {
                $q->where('folder_name', 'LIKE', "%{$searchValue}%")
                ->orWhere('description', 'LIKE', "%{$searchValue}%");

                // Search by status (accepting "Active" or "Inactive" as input)
                if (stripos('Active', $searchValue) !== false) {
                    $q->orWhere('status', 1);
                } elseif (stripos('Inactive', $searchValue) !== false) {
                    $q->orWhere('status', 0);
                }
            });
        }

        // Get the filtered record count
        $filteredRecords = $filteredQuery->count();

        // Sorting
        $orderColumn = $request->order[0]['column'] ?? 1; // Default to 'folder_name' if not provided
        $orderDirection = $request->order[0]['dir'] ?? 'asc';
        $columns = ['id', 'folder_name', 'description', 'status']; // Columns index reference

        $filteredQuery->orderBy($columns[$orderColumn], $orderDirection);

        // Pagination
        $folders = $filteredQuery->offset($request->start)->limit($request->length)->get();
        
        // Prepare response data
        $data = [];
        foreach ($folders as $index => $val) {
            $encodedId = encode_id($val->id); 
            $actions = view('folders.partials.actions', ['folder' => $val])->render();  

            $data[] = [
                'DT_RowIndex' => $index + 1,
                'folder_name' => $val->folder_name,
                'description' => $val->description,
                'status' => $val->status == 1 ? 'Active' : 'Inactive',
                'actions' => $actions,
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    



    public function getRootFolderDocuments(Request $request)
    {
        if (Auth::user()->is_owner)
        {
            $query = Document::whereNull('folder_id');
        }
    
        else{
            $query = Document::whereNull('folder_id')->where('ou_id', Auth::user()->ou_id);
        }
       
      
        // Get total records count
        $totalRecords = $query->count();
        $filteredRecords = $totalRecords;
    
        // Apply search filter
        if ($request->has('search') && !empty($request->input('search')['value'])) {
            $search = $request->input('search')['value'];
            $query->where(function($q) use ($search) {
                $q->where('original_filename', 'LIKE', "%$search%")
                  ->orWhere('document_file', 'LIKE', "%$search%");
            });
            $filteredRecords = $query->count(); // Update count after filtering
        }
    
        // Apply sorting
        if ($request->has('order')) {
            $columnIndex = $request->input('order')[0]['column'];
            $sortDirection = $request->input('order')[0]['dir'];
    
            $columns = ['id', 'original_filename', 'created_at', 'id']; // Define actual columns
            $query->orderBy($columns[$columnIndex], $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc'); // Default sorting
        }
    
        // Pagination
        $limit = $request->input('length');
        $offset = $request->input('start');
        $documents = $query->skip($offset)->take($limit)->get();
    
        // Format data for DataTables
        $data = [];
        foreach ($documents as $index => $doc) {
            $data[] = [
                'index' => $offset + $index + 1,
                'document_name' => $doc->original_filename ?? basename($doc->document_file),
                'uploaded_on' => $doc->created_at->format('d M Y, h:i A'),
                'actions' => '
                    <a href="' . Storage::url($doc->document_file) . '" class="btn btn-sm btn-primary" target="_blank">
                        <i class="fa fas fa-eye"></i> View
                    </a>
                    <a href="' . Storage::url($doc->document_file) . '" class="btn btn-sm btn-secondary" download>
                        <i class="fas fa-download"></i> Download
                    </a>'
            ];
        }
    
        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data
        ]);
    }


     public function showFolder(Request $request)
    {
    
        $ou_id = auth()->user()->ou_id;
        $organizationUnits = OrganizationUnits::all();
        $folderId = decode_id($request->folder_id);
        $editingFolder = Folder::find($folderId);
       
        if (Auth::user()->is_owner == 1) {
            //Admins can see all folders
            $folders = Folder::whereNull('parent_id')->with('children')->get();
            $subfolders = Folder::where('parent_id', $folderId)->get(); // Fetch all subfolders
            $groups = Group::all();
        } else { 
            $documentIds = Document::where('ou_id', $ou_id)->pluck('folder_id')->toArray();
            //Regular users see only folders in their assigned org unit
            $folders = Folder::where('ou_id', $ou_id)
                        ->whereNull('parent_id')
                        ->with('children')
                        //->whereIn('id', $documentIds)
                        ->get();

            $subfolders = Folder::where('ou_id', $ou_id)
                                ->where('parent_id', $folderId)
                                ->get(); 
            $groups = Group::where('ou_id', $ou_id)->get();                               
        }

        //Fetch documents of the selected folder
        $documents = Document::where('folder_id', $folderId)->get();
        

        //Generate breadcrumbs
        $breadcrumbs = $this->getBreadcrumbs($editingFolder); 
    
        return view('folders.show', compact('subfolders', 'folders', 'documents', 'editingFolder', 'organizationUnits', 'breadcrumbs','groups'));
    }



    public function getSubfolders(Request $request)
    {
       
        $folderId = decode_id($request->folder_id); // Decode the folder ID
        $documentIds = Document::where('ou_id', Auth::user()->ou_id)->pluck('folder_id')->toArray();
        if (Auth::user()->role == 1 && empty(Auth::user()->ou_id)) {
            $query = Folder::where('parent_id', $folderId); 
        } else {
            $query = Folder::where('ou_id', Auth::user()->ou_id)
                        ->where('parent_id', $folderId)
                        ->whereIn('id', $documentIds);
        }
    
        // Get total records count
        $totalRecords = $query->count(); 
        $filteredRecords = $totalRecords; 
    
        // Apply search filter (including status search)
        if ($request->has('search') && !empty($request->input('search')['value'])) {
            $searchValue = $request->input('search')['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('folder_name', 'LIKE', "%{$searchValue}%")
                  ->orWhere('description', 'LIKE', "%{$searchValue}%");
    
                // Search by status (accepting "Active" or "Inactive" as input)
                if (stripos('Active', $searchValue) !== false) {
                    $q->orWhere('status', 1);
                } elseif (stripos('Inactive', $searchValue) !== false) {
                    $q->orWhere('status', 0);
                }
            });
    
            $filteredRecords = $query->count(); // Update filtered records count
        }
    
        // Apply sorting
        if ($request->has('order')) {
            $columnIndex = $request->input('order')[0]['column'];
            $sortDirection = $request->input('order')[0]['dir'];
    
            $columns = ['id', 'folder_name', 'description', 'status', 'id']; // Define actual columns
            $query->orderBy($columns[$columnIndex], $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc'); // Default sorting
        }
    
        // Pagination
        $limit = $request->input('length');
        $offset = $request->input('start');
        $subfolders = $query->skip($offset)->take($limit)->get();
    
        // Format data for DataTables
        $data = [];
        foreach ($subfolders as $index => $val) {
            $data[] = [
                'index' => $offset + $index + 1,
                'folder_name' => '<span class="folderName">' . $val->folder_name . '</span>',
                'description' => $val->description,
                'status' => ($val->status == 1) ? 'Active' : 'Inactive',
                'actions' => view('folders.partials.actions', ['folder' => $val])->render()
            ];
        }
    
        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data
        ]);
    }

    public function getSubfolderDocuments(Request $request)
    {
        $folderId = decode_id($request->folder_id);

        $query = Document::where('folder_id', $folderId);

        // Total record count before filtering
        $totalRecords = $query->count();
        $filteredRecords = $totalRecords;

        // Search logic
        if ($request->has('search') && !empty($request->input('search')['value'])) {
            $searchValue = $request->input('search')['value'];

            $query->where(function ($q) use ($searchValue) {
                $q->where('original_filename', 'LIKE', "%{$searchValue}%")
                ->orWhere('document_file', 'LIKE', "%{$searchValue}%");
            });

            $filteredRecords = $query->count();
        }

        // Sorting logic
        if ($request->has('order')) {
            $columnIndex = $request->input('order')[0]['column'];
            $sortDirection = $request->input('order')[0]['dir'];

            $columns = ['id', 'original_filename', 'created_at', 'id']; // Column mapping
            $query->orderBy($columns[$columnIndex], $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        // Pagination
        $limit = $request->input('length');
        $offset = $request->input('start');
        $documents = $query->skip($offset)->take($limit)->get();

        // Format DataTables response
        $data = [];
        foreach ($documents as $index => $doc) {
            $documentUrl = Storage::url($doc->document_file);
            $filename = $doc->original_filename ?? basename($doc->document_file);

            $actions = '
                <a href="' . $documentUrl . '" class="btn btn-sm btn-primary" target="_blank">
                    <i class="fa fas fa-eye" style="color: black;"></i> View
                </a>
                <a href="' . $documentUrl . '" class="btn btn-sm btn-secondary" download>
                    <i class="fas fa-download"></i> Download
                </a>
            ';

            $data[] = [
                'index' => $offset + $index + 1,
                'document_name' => $filename,
                'uploaded_on' => $doc->created_at->format('d M Y, h:i A'),
                'actions' => $actions,
            ];
        }

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data
        ]);
    }

    public function isDescendant($folderId, $parentId)
    {
        if (!$parentId) {
            return false; // No parent means it's a root folder.
        }

        $parent = Folder::find($parentId);

        while ($parent) {
            if ($parent->id == $folderId) {
                return true; // This means we are trying to move inside a descendant.
            }
            $parent = $parent->parent; // Move up the hierarchy.
        }

        return false;
    }

    private function getBreadcrumbs($folder)
    {
        $breadcrumbs = [];
        while ($folder) {
            $breadcrumbs[] = [
                'name' => $folder->folder_name,
                'url' => route('folder.show', ['folder_id' => encode_id($folder->id)])
            ];
            $folder = $folder->parent; // Assuming 'parent' is a relationship in the Folder model
        }
        return array_reverse($breadcrumbs); // Reverse to show Home → Parent → Subfolder
    }
 
    public function getOrgfolder(Request $request)
    {
        $org_folder = Folder::where('ou_id', $request->ou_id)
                    ->whereNull('parent_id') 
                    ->with('childrenRecursive') 
                    ->get();
       
        if($org_folder){
                return response()->json(['org_folder' => $org_folder]);
            }else{
                return response()->json(['error'=> 'No group Found']);
            }
    }

    
}
