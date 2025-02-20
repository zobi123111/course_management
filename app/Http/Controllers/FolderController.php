<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\OrganizationUnits;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use File;

class FolderController extends Controller
{
    public function index()
    {
        $organizationUnits = OrganizationUnits::all();
        if (Auth::user()->role == 1 && empty(Auth::user()->ou_id)) {
            // Admin without OU restriction: Fetch all folders with their children
            $folders = Folder::whereNull('parent_id')->with('children')->get();
            $documents = Document::whereNull('folder_id')->get();
            // dd($documents);
        } else {
            // Regular users: Fetch only their org unit folders
            $folders = Folder::where('ou_id', Auth::user()->ou_id)->whereNull('parent_id')->with('children')->get();
            $documents = Document::whereNull('folder_id')->where('ou_id', Auth::user()->ou_id)->get();
        }
        // dd($folders);
        return view('folders.index',compact('folders', 'documents', 'organizationUnits'));
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
        $folder = Folder::findOrFail(decode_id($request->id));

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
            'current_folder_id' => $folder->id,
            'selected_parent_id' => $folder->parent_id
        ]);
    }

    public function updateFolder(Request $request)
    {
        // dd($request);
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
            ]
        ]);
    
        $folder = Folder::findOrFail($request->folder_id);  
    
        // Prevent setting the folder as its own parent
        if ($request->parent_id == $folder->id) {
            return response()->json(['error' => 'A folder cannot be its own parent.'], 400);
        }
    
        // Prevent moving a folder into its own subfolder (infinite loop prevention)
        if ($this->isDescendant($folder->id, $request->parent_id)) {
            return response()->json(['error' => 'Cannot move a folder into its own subfolder.'], 400);
        }

        // Update folder
        $folder->update([
            'folder_name' => $request->folder_name,
            'description' => $request->description,
            'status'      => $request->status,
            'parent_id'   => $request->parent_id,
            'ou_id'       => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id
        ]);
        Session::flash('message', 'Folder updated successfully.');
        return response()->json(['success' => 'Folder updated successfully.']);
    }

    public function deleteFolder(Request $request)
    {
        $folder = Folder::findOrFail(decode_id($request->folder_id));
        if ($folder) {
            $folder->delete();
            return redirect()->route('folder.index')->with('message', 'This Folder deleted successfully');
        }
    }

    public function showFolder(Request $request)
    {
        $organizationUnits = OrganizationUnits::all();
        $folderId = decode_id($request->folder_id);
        $editingFolder = Folder::find($folderId);
    
        if (Auth::user()->role == 1 && empty(Auth::user()->ou_id)) {
            //Admins can see all folders
            $folders = Folder::whereNull('parent_id')->with('children')->get();
            $subfolders = Folder::where('parent_id', $folderId)->get(); // Fetch all subfolders
        } else {
            //Regular users see only folders in their assigned org unit
            $folders = Folder::where('ou_id', Auth::user()->ou_id)->whereNull('parent_id')->with('children')->get();
    
            $subfolders = Folder::where('ou_id', Auth::user()->ou_id)
                                ->where('parent_id', $folderId)
                                ->get(); // Fetch only their org unit's subfolders
        }
    
        //Fetch documents of the selected folder
        $documents = Document::where('folder_id', $folderId)->get();
    
        return view('folders.show', compact('subfolders', 'folders', 'documents', 'editingFolder', 'organizationUnits'));
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

    
}
