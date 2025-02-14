<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Models\Folder;
use App\Models\Document;
use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{
    public function index() 
    {
        if(Auth::user()->role==1 && empty(Auth::user()->ou_id)){
            $user_count = user::count();
            $group_count = Group::count();
            $folder_count = Folder::count();
            $documents = Document::where('ou_id', Auth::user()->ou_id)->get();
        }else{
            $user_count = user::where('ou_id' , auth()->user()->ou_id)->count();
            $group_count = 0;
            $folder_count = 0;
            $documents = Document::all();
        }
        $totalDocuments = $documents->count();
        $readDocuments = $documents->where('acknowledged', 1)->count();
        $unreadDocuments = $totalDocuments-$readDocuments;
        return view('dashboard.index', compact('user_count', 'group_count', 'folder_count', 'totalDocuments','readDocuments','unreadDocuments'));
    }
}
