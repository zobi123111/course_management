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
        if(empty($ou_id)){
            $groups = Group::all();
            $folders = Folder::all();
            $documents = Document::all();
        }else{
            $groups = Group::where('ou_id', $ou_id)->get();
            $folders = Folder::where('ou_id',$ou_id)->get();
            $documents = Document::where('ou_id',$ou_id)->get();
        }
        return view('documents.index',compact('documents', 'folders', 'groups'));
    }

    public function createDocument(Request $request)
    {
        // dd(auth()->user()->ou_id);
        $request->validate([
            'doc_title' => 'required',
            'version_no' => 'required',
            'issue_date' => 'required|date',
            // 'expiry_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'document_file' => 'required|file|mimes:pdf|max:2048',
            'folder' => 'required',
            'status' => 'required',
            'group' => "required",

        ]);

        $folder = Folder::find($request->folder);
        // Handle file upload
        if ($request->hasFile('document_file')) {
            $filePath = $request->file('document_file')->store($folder->folder_name, 'public');
        }

        Document::create([
            'ou_id' => auth()->user()->ou_id ?? null,
            'folder_id' => $folder->id,
            'group_id' => $request->group,
            'doc_title' => $request->doc_title,
            'version_no' => $request->version_no,
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'document_file' => $filePath ?? null,
            'status' => $request->status
        ]);

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
        // dd(auth()->user()->ou_id);
        // Validate the request, making 'document_file' optional
        $request->validate([
            'folder' => 'required',
            'group' => 'required',
            'doc_title' => 'required',
            'version_no' => 'required',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'document_file' => 'nullable|file|mimes:pdf|max:2048', // Optional file
            'folder' => 'required', // Ensure folder selection
            'status' => 'required',
        ]);

        // Retrieve the document by ID
        $document = Document::findOrFail($request->document_id);

        // Retrieve the current folder and new folder
        $currentFolder = Folder::find($document->folder_id);
        $newFolder = Folder::find($request->folder);

        if (!$newFolder) {
            return response()->json(['error' => 'Folder not found.'], 404);
        }

        $filePath = $document->document_file; // Keep the existing file path by default

        // Handle file upload if a new file is provided
        if ($request->hasFile('document_file')) {
            // Delete the old file if it exists
            if ($document->document_file) {
                Storage::disk('public')->delete($document->document_file);
            }

            // Store the new file inside the new folder
            $filePath = $request->file('document_file')->store($newFolder->folder_name, 'public');
        } else {
            // If no new file is uploaded, check if the folder has changed
            if ($currentFolder && $currentFolder->id !== $newFolder->id && $document->document_file) {
                $oldPath = $document->document_file;
                $newPath = str_replace($currentFolder->folder_name, $newFolder->folder_name, $oldPath);

                // Move the file to the new folder
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->move($oldPath, $newPath);
                    $filePath = $newPath; // Update file path
                }
            }
        }

        // Update the document
        $document->update([
            'ou_id' => auth()->user()->ou_id ?? null,
            'folder_id' => $newFolder->id, // Update folder reference
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
