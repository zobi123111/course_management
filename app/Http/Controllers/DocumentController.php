<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;


class DocumentController extends Controller
{
    public function index()
    {
        $ou_id =  auth()->user()->ou_id;
        if(Auth::user()->role==1 && empty($ou_id)){
            $groups = Group::all();
            $folders = Folder::whereNull('parent_id')->with('children')->get();
            $documents = Document::all();
        }else{
            $groups = Group::where('ou_id', $ou_id)->get();
            $folders = Folder::where('ou_id', Auth::user()->ou_id)->whereNull('parent_id')->with('children')->get();
            $documents = Document::where('ou_id',$ou_id)->get();
        }
        return view('documents.index',compact('documents', 'folders', 'groups'));
    }

    public function createDocument(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'doc_title' => 'required',
            'version_no' => 'required',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'document_file' => 'required|file|mimes:pdf|max:2048',
            'status' => 'required',
            'group' => 'required',
        ]);
    
        // Initialize variables
        $folder = null;
        $filePath = null;
    
        // Check if a folder is selected
        if ($request->filled('folder')) {
            $folder = Folder::find($request->folder);
    
            if ($folder) {
                // Store the file in the specified folder
                $filePath = $request->file('document_file')->store("documents/{$folder->folder_name}", 'public');
            } else {
                return response()->json(['error' => 'Invalid folder specified.'], 400);
            }
        } else {
            // Store the file in the default 'documents' folder
            $filePath = $request->file('document_file')->store('documents', 'public');
        }
    
        // Create the document record in the database
        Document::create([
            'ou_id' => auth()->user()->ou_id ?? null,
            'folder_id' => $folder->id ?? null,
            'group_id' => $request->group,
            'doc_title' => $request->doc_title,
            'version_no' => $request->version_no,
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'document_file' => $filePath,
            'status' => $request->status,
        ]);
    
        // Flash success message and return JSON response
        Session::flash('message', 'Document added successfully.');
        return response()->json(['success' => 'Document added successfully.']);
    }

    public function getDocument(Request $request)
    {
        $document = Document::findOrFail(decode_id($request->id));
        return response()->json(['document'=> $document]);
    }

    public function updateDocument(Request $request)
    {
        // Validate the request, making 'folder' optional
        $request->validate([
            'group' => 'required',
            'doc_title' => 'required',
            'version_no' => 'required',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'document_file' => 'nullable|file|mimes:pdf|max:2048', // Optional file
            'status' => 'required',
        ]);

        // Retrieve the document by ID
        $document = Document::findOrFail($request->document_id);

        // Retrieve the current folder and new folder (if provided)
        $currentFolder = Folder::find($document->folder_id);
        $newFolder = $request->filled('folder') ? Folder::find($request->folder) : null;

        if ($request->filled('folder') && !$newFolder) {
            return response()->json(['error' => 'Folder not found.'], 404);
        }

        $filePath = $document->document_file; // Keep the existing file path by default

        // Determine the storage path
        $folderPath = $newFolder ? 'documents/' . $newFolder->folder_name : 'documents';

        // Handle file upload if a new file is provided
        if ($request->hasFile('document_file')) {
            // Delete the old file if it exists
            if ($document->document_file) {
                Storage::disk('public')->delete($document->document_file);
            }

            // Store the new file inside the specified folder (if provided) or use default location
            $filePath = $request->file('document_file')->store($folderPath, 'public');
        } elseif ($newFolder && $currentFolder && $currentFolder->id !== $newFolder->id && $document->document_file) {
            // If no new file is uploaded but the folder is changed, move the existing file
            $oldPath = $document->document_file;
            $newPath = str_replace('documents/' . $currentFolder->folder_name, 'documents/' . $newFolder->folder_name, $oldPath);

            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->move($oldPath, $newPath);
                $filePath = $newPath; // Update file path
            }
        }

        // Update the document
        $document->update([
            'ou_id' => auth()->user()->ou_id ?? null,
            'folder_id' => $newFolder->id ?? $document->folder_id, // Keep existing folder if none is provided
            'group_id' => $request->group,
            'doc_title' => $request->doc_title,
            'version_no' => $request->version_no,
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'document_file' => $filePath, // Keep old file or move it
            'status' => $request->status,
        ]);

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

    public function showDocument(Request $request,$doc_id)
    {
        $document = Document::find(decode_id($doc_id));
        return view('documents.show',compact('document'));
    }

    public function acknowledgeDocument(Request $request)
    {
        $request->validate([
            'document_id' => 'required|exists:documents,id',
            'acknowledged' => 'required|boolean',
        ]);
    
        $document = Document::findOrFail($request->document_id);
        if($document){
            $document->update(['acknowledged' => $request->acknowledged]); // Assuming you have an 'acknowledged' column
            return response()->json(['success' => 'Document acknowledged successfully.']);
        }else{
            return response()->json(['error' => 'Something went wrong, Please try after some time.']);
        }
    
    }


}
