<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Group;
use App\Models\Folder;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index() 
    {
        if(auth()->user()->role == 1 && auth()->user()->ou_id == null) {
            $user_count = user::count();
            $group_count = Group::count();
            $folder_count = Folder::count();
        }
        else{
            $user_count = user::where('ou_id' , auth()->user()->ou_id)->count();
            $group_count = 0;
            $folder_count = 0;
            // $group_count = Group::where('ou_id' , auth()->user()->ou_id)->count();
            // $folder_count = Folder::where('ou_id' , auth()->user()->ou_id)->count();
        }
        return view('dashboard.index', compact('user_count', 'group_count', 'folder_count'));
    }
}
