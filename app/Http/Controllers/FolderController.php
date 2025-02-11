<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Folder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use File;

class FolderController extends Controller
{
    public function index()
    {
        $folders = Folder::all();
        return view('folders.index',compact('folders'));
    }

    public function createFolder(Request $request)
    {
        
        $request->validate([            
            'folder_name' => 'required',
            'description' => 'required',
            'status' => 'required|boolean'
        ]);
        $path = public_path()."/".$request->folder_name;
        $folderCreated = File::makeDirectory($path, $mode = 0777, true, true);
        if($folderCreated){
            Folder::create([
                'folder_name' => $request->folder_name,
                'description' => $request->description,
                'status' => $request->status
            ]);            
            Session::flash('message', 'Folder created successfully.');
            return response()->json(['success' => 'Folder created successfully.']);
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
            'status' => 'required'
        ]);

        $folder = Folder::findOrFail($request->folder_id);

        // Move/Rename the folder
        $renameFolder = Storage::move($folder->folder_name, $request->folder_name);

        if ($renameFolder) {
            $folder->update([
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
}
