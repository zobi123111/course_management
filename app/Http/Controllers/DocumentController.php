<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use App\Models\Group;
use App\Models\OrganizationUnits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


class DocumentController extends Controller
{
    // public function index()
    // {
    //     $userId = Auth::user()->id;
    //     $ou_id =  auth()->user()->ou_id;
    //     if(checkAllowedModule('courses', 'document.index')->isNotEmpty() && Auth()->user()->is_owner ==  1){
    //         $groups = Group::all();
    //         $folders = Folder::whereNull('parent_id')->with('children')->get();
    //         $documents = Document::orderBy('id', 'asc')->get();

    //     }
    //     elseif(checkAllowedModule('documents', 'document.index')->isNotEmpty() && Auth()->user()->is_admin ==  0){   
    //         $groups = Group::where('ou_id', $ou_id)->get();
    //         $filteredGroups = $groups->filter(function ($group) use ($userId) {
    //             $userIds = is_array($group->user_ids) ? $group->user_ids : explode(',', $group->user_ids);                
    //             return in_array($userId, $userIds);
    //         });
    
    //         $groupIds = $filteredGroups->pluck('id')->toArray();
    //         $documents = Document::whereIn('group_id', $groupIds)
    //                     ->where('status', 1)
    //                     ->orderBy('id', 'desc')
    //                     ->get();
    //         $folders = [];
    //     }else{           
    //         $groups = Group::where('ou_id', $ou_id)->get();
    //         $folders = Folder::where('ou_id', auth()->user()->ou_id)->whereNull('parent_id')->with('children')->get();
    //         $documents = Document::where('ou_id', $ou_id)->orderBy('id', 'desc')->get();

    //     }
    //     $organizationUnits = OrganizationUnits::all();
    //     return view('documents.index',compact('documents', 'folders', 'groups', 'organizationUnits'));
    // }

    //Used Index method for Student/Instructure Role & get_documents method for admin Roles 
    public function index()
    {
        $user = Auth::user();
        $userId = $user->id;
        $ou_id = $user->ou_id;

        $groups = collect();
        $folders = collect();
        $documents = collect();

        if (checkAllowedModule('courses', 'document.index')->isNotEmpty() && $user->is_owner == 1) {
            // Owner view
            $groups = Group::all();
            $documents = Document::with('folder.groups')->orderBy('id', 'asc')->get();
            $folders = Folder::whereNull('parent_id')->with('children')->get();
        }elseif (checkAllowedModule('documents', 'document.index')->isNotEmpty() && $user->is_admin == 0) {
            // Get user's group IDs
            $groups = Group::where('ou_id', $ou_id)->get();

            $filteredGroups = $groups->filter(function ($group) use ($userId) {
                $userIds = is_array($group->user_ids) ? $group->user_ids : explode(',', $group->user_ids);
                return in_array($userId, $userIds);
            });

            $groupIds = $filteredGroups->pluck('id')->toArray();

            // Get folders that are assigned to user's groups
            $folders = Folder::whereHas('groups', function ($query) use ($groupIds) {
                $query->whereIn('groups.id', $groupIds);
            })
            ->with(['parent', 'groups']) // Load parent if you want to show folder hierarchy
            ->get();


            // Get documents assigned to the same user's group
            $documents = Document::whereHas('groups', function ($query) use ($groupIds) {
                $query->whereIn('groups.id', $groupIds);
            })
            ->with(['folder.groups', 'groups'])
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();
        }else {
            // Admin or other users
            $groups = Group::where('ou_id', $ou_id)->get();
            $documents = Document::with('folder.groups')
                ->where('ou_id', $ou_id)
                ->orderBy('id', 'desc')
                ->get();

            $folders = Folder::where('ou_id', $ou_id)
                ->whereNull('parent_id')
                ->with('children')
                ->get();

        }

        $organizationUnits = OrganizationUnits::all();

        if(auth()->user()->is_owner == 1 || auth()->user()->is_admin == 1){
            return view('documents.index', compact('documents', 'folders', 'groups', 'organizationUnits'));
        }else{
            return view('documents.doc-tabs', compact('documents', 'folders', 'groups', 'organizationUnits'));
        }
    }

    public function createDocument(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'doc_title' => 'required',
            'version_no' => 'required',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'document_file' => 'required|file|max:20480',
            'status' => 'required',
            'group' => 'required|array', //Ensure it's an array of group IDs
            'group.*' => 'exists:groups,id', //Validate each group ID
            'folder' => 'required|exists:folders,id' 
        ]);

        // Get the original filename
        $originalFilename = $request->file('document_file')->getClientOriginalName();

        // Store the file in the 'documents' folder
       // $filePath = $request->file('document_file')->store('documents', 'public');
        $filePath =  $request->file('document_file')->storeAs('documents', $originalFilename, 'public');

        $ou_id = (auth()->user()->is_owner == 1) ? $request->ou_id : (auth()->user()->ou_id ?? null);

        // Create the document record in the database
        $document = Document::create([
             'ou_id' => $ou_id,
            'folder_id' => $request->folder ?? null, // Store only folder ID, not folder name
            // 'group_id' => $request->group,
            'doc_title' => $request->doc_title,
            'version_no' => $request->version_no,
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'document_file' => $filePath,
            'original_filename' => $originalFilename, // Store original filename
            'status' => $request->status,
        ]);

        $timestamp = now();
        $groupData = [];

        foreach ($request->group as $groupId) {
            $groupData[$groupId] = [
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ];
        }

        $document->groups()->sync($groupData);
        //Flash success message and return JSON response
        Session::flash('message', 'Document added successfully.');
        return response()->json(['success' => 'Document added successfully.']);
    }

    public function getDocument(Request $request)
    {
        $document = Document::with('groups')->findOrFail(decode_id($request->id));
        $group = Group::where('ou_id', $document->ou_id)->get();
        $folders = Folder::where('ou_id', $document->ou_id)->whereNull('parent_id') ->with('childrenRecursive')->get();
        return response()->json(['document'=> $document, 'group'=> $group, 'folders'=> $folders]);
    }

    public function updateDocument(Request $request)
    {
        //dd($request->ou_id());
        // Validate the incoming request
        $request->validate([
            'doc_title' => 'required',
            'version_no' => 'required',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'document_file' => 'nullable|file|max:20480', // File is optional
            'status' => 'required',
            'group' => 'required|array',
            'group.*' => 'exists:groups,id',
            'folder' => 'required|exists:folders,id' // Ensure folder exists if provided
        ]); 
    
        // Retrieve the document by ID
        $document = Document::findOrFail($request->document_id);
    
        // Handle file upload
        $filePath = $document->document_file; // Keep the existing file path by default
        $originalFilename = $document->original_filename; // Keep existing filename
    
        if ($request->hasFile('document_file')) {
            // Delete old file if it exists
            if ($document->document_file) {
                Storage::disk('public')->delete($document->document_file);
            }
    
            // Get original filename
            $originalFilename = $request->file('document_file')->getClientOriginalName();
    
            // Store the new file in the 'documents' folder
            $filePath =  $request->file('document_file')->storeAs('documents', $originalFilename, 'public');
        }
    
        $ou_id = (auth()->user()->is_owner == 1) ? $request->ou_id : (auth()->user()->ou_id ?? null);
        // Update the document in the database
        $document->update([
            'ou_id' =>  $ou_id, 
            'folder_id' => $request->folder ?? null,
            // 'group_id' => $request->group,
            'doc_title' => $request->doc_title,  
            'version_no' => $request->version_no,
            'issue_date' => $request->issue_date,
            'completed_date' => $request->completed_date,
            'expiry_date' => $request->expiry_date,
            'document_file' => $filePath,
            'document_type' => $request->document_type,
            'original_filename' => $originalFilename, 
            'status' => $request->status,
        ]);

        //Sync group assignments to pivot table with timestamps
        $timestamp = now();
        $groupData = [];
        foreach ($request->group as $groupId) {
            $groupData[$groupId] = ['created_at' => $timestamp, 'updated_at' => $timestamp];
        }

        $document->groups()->sync($groupData);
    
        // Flash success message and return JSON response
        Session::flash('message', 'Document updated successfully.');
        return response()->json(['success' => 'Document updated successfully.']);
    }


    public function deleteDocument(Request $request)
    {        
        // Find the document by ID
        $document = Document::findOrFail(decode_id($request->document_id));
        if ($document) {
            // Check if the document has an associated file
            if ($document->document_file) {
                // Delete the file from the 'public' disk in the 'document' folder
                Storage::disk('public')->delete($document->document_file);
            }

            // Delete the document record from the database
            $document->delete();

            // Return a success message
            return redirect()->route('document.index')->with('message', 'This Document deleted successfully');
        }
    }

    public function getDocuments(Request $request)
    {
        $columns = ['doc_title', 'version_no', 'issue_date', 'expiry_date', 'document_file', 'status'];
        $limit = $request->input('length', 10); // Default to 10 if not provided
        $start = $request->input('start', 0);
        $orderColumnIndex = $request->input('order')[0]['column'] ?? 0;
        $orderDirection = $request->input('order')[0]['dir'] ?? 'asc';
        $searchValue = strtolower($request->input('search')['value'] ?? ''); // Convert search input to lowercase
    
        $user = Auth::user();
        $userId = $user->id;
        $ou_id = $user->ou_id;
    
        // Determine which documents to fetch based on user role and permissions
        if (checkAllowedModule('courses', 'document.index')->isNotEmpty() && $user->is_owner == 1) {
            $query = Document::with('groups:id,name,user_ids')->orderBy('id', 'desc'); 
        } elseif (checkAllowedModule('documents', 'document.index')->isNotEmpty() && $user->is_admin == 0) {
            $groups = Group::where('ou_id', $ou_id)->get();
            $filteredGroups = $groups->filter(function ($group) use ($userId) {
                $userIds = is_array($group->user_ids) ? $group->user_ids : explode(',', $group->user_ids);
                return in_array($userId, $userIds);
            });
    
            $groupIds = $filteredGroups->pluck('id')->toArray();
            $query = Document::with('groups:id,name,user_ids')->whereIn('group_id', $groupIds)->where('status', 1);
        } else {
            $query = Document::with('groups:id,name,user_ids')->where('ou_id', $ou_id);
        }
    
        // Get total record count before filtering
        $totalRecords = $query->count();
    
        // Apply search filter
        if (!empty($searchValue)) {
            $query->where(function ($q) use ($searchValue) {
                if (str_contains('active', $searchValue)) {
                    $q->orWhere('status', 1);
                }
                if (str_contains('inactive', $searchValue)) {
                    $q->orWhere('status', 0);
                }
                $q->orWhere('doc_title', 'like', "%{$searchValue}%")
                    ->orWhere('version_no', 'like', "%{$searchValue}%")
                    ->orWhere('issue_date', 'like', "%{$searchValue}%")
                    ->orWhere('expiry_date', 'like', "%{$searchValue}%");
            });
        }
    
        // Get filtered record count
        $recordsFiltered = $query->count();
    
        // Apply sorting
        $query->orderBy($columns[$orderColumnIndex], $orderDirection);
    
        // Apply pagination (ensures 10 entries per page)
        $documents = $query->offset($start)->limit($limit)->get();
    
        // Format data for DataTables
        $data = [];
        foreach ($documents as $row) {
            $groupUserIds = [];
        
            // Ensure the document has a valid group before accessing its user IDs
            if (!empty($row->groups) && !empty($row->groups->user_ids)) {
                // Convert user_ids string to array safely
                $groupUserIds = is_array($row->groups->user_ids) 
                    ? $row->groups->user_ids 
                    : explode(',', trim($row->groups->user_ids));
            }
        
            // Get acknowledged user IDs from the document
            $acknowledgedUsers = json_decode($row->acknowledge_by ?? '[]', true);
        
            // Check if all group users have acknowledged the document
            $isFullyAcknowledged = !empty($groupUserIds) && !array_diff($groupUserIds, $acknowledgedUsers);
        
            // Determine what to display in the acknowledged column
            if (auth()->user()->is_admin == 1 || auth()->user()->is_owner == 1) {
                $acknowledgedDisplay = $isFullyAcknowledged
                    ? '<span style="color: green;">✔</span>'
                    : '<span style="color: red;">❌</span>';
            } else {
                $userAcknowledged = in_array(auth()->user()->id, $acknowledgedUsers);
                $acknowledgedDisplay = $userAcknowledged
                    ? '<span style="color: green;">✔</span>'
                    : '<span style="color: red;">❌</span>';
            }
        
            $data[] = [
                'doc_title' => $row->doc_title,
                'version_no' => $row->version_no,
                'issue_date' => $row->issue_date,
                'expiry_date' => $row->expiry_date,
                'assigned_group' => $row->groups->isNotEmpty()
                    ? $row->groups->map(function ($group) use ($row) {
                        if (auth()->user()->is_admin == 1 || auth()->user()->is_owner == 1) {
                            return '<a href="#" class="get_group_users" data-doc-id="' . encode_id($row->id) . '" data-group-id="' . encode_id($group->id) . '">' . e($group->name) . '</a>';
                        } else {
                            return e($group->name);
                        }
                    })->implode(', ')
                    : 'No Group Assigned',
                'document' => $row->document_file
                    ? '<a href="' . route('document.show', encode_id($row->id)) . '">View Document</a>'
                    : 'No File uploaded',
                'status' => ($row->status == 1) ? 'Active' : 'Inactive',
                'acknowledged' => $acknowledgedDisplay,
                'edit' => checkAllowedModule('documents', 'document.edit')->isNotEmpty()
                    ? '<i class="fa fa-edit edit-document-icon" style="font-size:25px; cursor: pointer;" data-document-id="' . encode_id($row->id) . '"></i>'
                    : '',
                'delete' => checkAllowedModule('documents', 'document.delete')->isNotEmpty()
                    ? '<i class="fa-solid fa-trash delete-document-icon" style="font-size:25px; cursor: pointer;" data-document-id="' . encode_id($row->id) . '"></i>'
                    : '',
            ];
        }
        
    
        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }
    
    

    public function showDocument(Request $request,$doc_id)
    {
        $document = Document::find(decode_id($doc_id));
        return view('documents.show',compact('document'));
    }

    // public function getDocUserList(Request $request)
    // {
       
    //     $doc_id = decode_id($request->doc_id);
    //     $document = Document::where('id', $doc_id)->with('group')->first();
    
    //     // Check if document exists
    //     if (!$document || !$document->group) {
    //         return response()->json(['error' => 'Document or Group Not Found.'], 404);
    //     }
    
    //     // Ensure user_ids is an array
    //     $groupUserIds = is_array($document->group->user_ids) 
    //         ? $document->group->user_ids 
    //         : explode(',', $document->group->user_ids ?? '');
    
    //     // Get acknowledged user IDs from document
    //     $acknowledgedUsers = json_decode($document->acknowledge_by ?? '[]', true);
    
    //     // Get users from group and check acknowledgment status
    //     $groupUsers = User::whereIn('id', $groupUserIds)
    //         ->select('id', 'fname', 'lname', 'email', 'image')
    //         ->get()
    //         ->map(function ($user) use ($acknowledgedUsers) {
    //             $user->acknowledged = in_array($user->id, $acknowledgedUsers);
    //             return $user;
    //         });    
    //     return response()->json(['groupUsers' => $groupUsers]);
    // }

    public function getDocUserList(Request $request)
    {
        $doc_id = decode_id($request->doc_id);
        $group_id = decode_id($request->group_id);

        // Load the document
        $document = Document::find($doc_id);

        if (!$document) {
            return response()->json(['error' => 'Document not found.'], 404);
        }

        // Load the group
        $group = Group::find($group_id);

        if (!$group) {
            return response()->json(['error' => 'Group not found.'], 404);
        }

        // Get user IDs from the group
        $groupUserIds = is_array($group->user_ids)
            ? $group->user_ids
            : explode(',', $group->user_ids ?? '');

        // Get acknowledged user IDs from document
        $acknowledgedUsers = json_decode($document->acknowledge_by ?? '[]', true);

        // Fetch user details with acknowledgment status
        $groupUsers = User::whereIn('id', $groupUserIds)
            ->select('id', 'fname', 'lname', 'email', 'image')
            ->get()
            ->map(function ($user) use ($acknowledgedUsers) {
                $user->acknowledged = in_array($user->id, $acknowledgedUsers);
                return $user;
            });

        return response()->json(['groupUsers' => $groupUsers]);
    }

    public function acknowledgeDocument(Request $request)
    {
        $userId = auth()->user()->id;
    
        $request->validate([
            'document_id' => 'required|exists:documents,id',
        ]);
    
        $document = Document::findOrFail($request->document_id);
    
        if ($document) {
            $acknowledgedUsers = json_decode($document->acknowledge_by ?? '[]', true);
    
            if (!in_array($userId, $acknowledgedUsers)) {
                $acknowledgedUsers[] = $userId;
                $document->update(['acknowledge_by' => json_encode($acknowledgedUsers)]);
            }
    
            return response()->json(['success' => 'Document acknowledged successfully.']);
        }
    
        return response()->json(['error' => 'Something went wrong, please try again later.'], 500);
    }
    

    public function getOrgfolder(Request $request)
    {
        try {
            if (!$request->has('ou_id')) {
                return response()->json(['error' => 'ou_id is required'], 400);
            }
    
            $org_group = Group::where('ou_id', $request->ou_id)->get();
            $org_folder = Folder::where('ou_id', $request->ou_id)
                        ->whereNull('parent_id')
                        ->with('childrenRecursive')
                        ->get();
    
            return response()->json([
                'org_group' => $org_group,
                'org_folder' => $org_folder
            ]);
    
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    


}
