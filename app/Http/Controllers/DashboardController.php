<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index() 
    {
        if(Auth::user()->role==1 && empty(Auth::user()->ou_id)){
            $documents = Document::where('ou_id', Auth::user()->ou_id)->get();
        }else{
            $documents = Document::all();
        }
        $totalDocuments = $documents->count();
        $readDocuments = $documents->where('acknowledged', 1)->count();
        $unreadDocuments = $totalDocuments-$readDocuments;
        return view('dashboard.index', compact('totalDocuments','readDocuments','unreadDocuments'));
    }
}
