<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;
use App\Models\OrganizationUnits;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use File;

class FolderController extends Controller
{
    public function index()
    {
        $urganizationUnits = OrganizationUnits::all();
        if (Auth::user()->role == 1 && empty(Auth::user()->ou_id)) {
            // Admin without OU restriction: Fetch all folders with their children
            $folders = Folder::whereNull('parent_id')->with('children')->get();
        } else {
            // Regular users: Fetch only their org unit folders
            $folders = Folder::where('ou_id', Auth::user()->ou_id)->whereNull('parent_id')->with('children')->get();
        }
        // dd($folders);
        return view('folders.index',compact('folders','urganizationUnits'));
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
    
        // Determine correct OU ID
        $ouId = (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id;
    
        // Determine the folder path based on parent_id
        if ($request->parent_id) {
            $parentFolder = Folder::find($request->parent_id);
            if (!$parentFolder) {
                return response()->json(['error' => 'Invalid parent folder.'], 400);
            }
            $path = public_path("storage/{$parentFolder->folder_name}/{$request->folder_name}");
        } else {
            $path = public_path("storage/{$request->folder_name}");
        }
    
        // Check if folder already exists
        if (File::exists($path)) {
            return response()->json(['error' => 'Folder already exists.'], 400);
        }
    
        // Create directory
        $folderCreated = File::makeDirectory($path, 0777, true, true);
    
        if ($folderCreated) {
            Folder::create([
                'ou_id' => $ouId,
                'folder_name' => $request->folder_name,
                'description' => $request->description,
                'status' => $request->status,
                'parent_id' => $request->parent_id, // Assign parent_id if provided
            ]);
    
            Session::flash('message', 'Folder created successfully.');
            return response()->json(['success' => 'Folder created successfully.']);
        } else {
            return response()->json(['error' => 'Failed to create folder.'], 500);
        }
    }

    public function getFolder(Request $request)
    {
        $folder = Folder::findOrFail(decode_id($request->id));
        return response()->json(['folder'=> $folder]);
    }

    public function updateFolder(Request $request)
    {
        $request->validate([
            'folder_name' => 'required',
            'description' => 'required',
            'status' => 'required',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ]
        ]);

        $folder = Folder::findOrFail($request->folder_id);
        $disk = Storage::disk('public'); // Change 'public' to your correct disk
        if ($disk->exists($folder->folder_name)) {
            $renameFolder = $disk->move($folder->folder_name, $request->folder_name);
        } else {
            return response()->json(['error' => 'Folder does not exist.'], 404);
        }

        if ($renameFolder) {
            $folder->update([
                'ou_id' =>  (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id, // Assign ou_id only if Super Admin provided it
                'folder_name' => $request->folder_name,
                'description' => $request->description,
                'status' => $request->status
            ]);

            Session::flash('message', 'Folder updated successfully.');
            return response()->json(['success' => 'Folder updated successfully.']);
        } else {
            return response()->json(['error' => 'Failed to rename folder.'], 500);
        }
    }

    public function deleteFolder(Request $request)
    {
        $folder = Folder::findOrFail(decode_id($request->folder_id));
        if ($folder) {
            $folder->delete();
            return redirect()->route('folder.index')->with('message', 'This Folder deleted successfully');
        }
    }
}
