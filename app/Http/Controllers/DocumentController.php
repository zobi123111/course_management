<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;


class DocumentController extends Controller
{
    public function index()
    {
        $documents = Document::all();
        return view('documents.index',compact('documents'));
    }

    public function createDocument(Request $request)
    {
        // dd($request);
        $request->validate([
            'version_no' => 'required',
            'issue_date' => 'required|date',
            // 'expiry_date' => 'required|date',
            'expiry_date' => 'required|date|after:issue_date',
            'document_file' => 'required|file|mimes:pdf|max:2048',
            'status' => 'required',

        ]);

        // Handle file upload
        if ($request->hasFile('document_file')) {
            $filePath = $request->file('document_file')->store('document', 'public');
        }

        Document::create([
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
        // Validate the request, making 'document_file' optional
        $request->validate([
            'version_no' => 'required',
            'issue_date' => 'required|date',
            'expiry_date' => 'required|date',
            'document_file' => 'nullable|file|mimes:pdf|max:2048', // 'nullable' makes it optional
            'status' => 'required',
        ]);
    
        // Retrieve the document by ID
        $document = Document::findOrFail($request->document_id);
    
        // Handle file upload if a new file is provided
        if ($request->hasFile('document_file')) {
            // Delete the old file if it exists
            if ($document->document_file) {
                Storage::disk('public')->delete($document->document_file);
            }
    
            // Store the new file and get its path
            $filePath = $request->file('document_file')->store('document', 'public');
        } else {
            // If no file is uploaded, keep the existing file path
            $filePath = $document->document_file;
        }
    
        // Update the document
        $document->update([
            'version_no' => $request->version_no,
            'issue_date' => $request->issue_date,
            'expiry_date' => $request->expiry_date,
            'document_file' => $filePath, // Will be either the new file path or the existing one
            'status' => $request->status,
        ]);
    
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


}
