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
        if ($folder) {
            $folder->update([
                'folder_name' => $request->folder_name,
                'description' => $request->description,
                'status'      => $request->status,
                'parent_id'   => $request->parent_id,
                'ou_id'       => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id
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
