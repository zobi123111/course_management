<?php

namespace App\Http\Controllers;

use App\Models\Courses;
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
            $documents = Document::all();

        }
        elseif(checkAllowedModule('courses', 'course.index')->isNotEmpty()){

            $userId = Auth::user()->id;

            $groups = Group::all();
    
            $filteredGroups = $groups->filter(function ($group) use ($userId) {
                $userIds = is_array($group->user_ids) ? $group->user_ids : explode(',', $group->user_ids);
                 
                return in_array($userId, $userIds);
            });
    
            $groupIds = $filteredGroups->pluck('id')->toArray();
    
            $courses = Courses::whereIn('id', function ($query) use ($groupIds) {
                $query->select('courses_id')
                    ->from('courses_group')
                    ->whereIn('group_id', $groupIds);
            })->get();

            $user_count = $courses->count();
            $group_count = $filteredGroups->count();
            $folder_count = 0;
            $documents = Document::where('ou_id', Auth::user()->ou_id)->get();

        }else{

            $user_count = user::where('ou_id' , auth()->user()->ou_id)->count();
            $group_count = 0;
            $folder_count = 0;
            $documents = Document::where('ou_id', Auth::user()->ou_id)->get();

        }

        $totalDocuments = $documents->count();
        $readDocuments = $documents->where('acknowledged', 1)->count();
        $unreadDocuments = $totalDocuments-$readDocuments;

        return view('dashboard.index', compact('user_count', 'group_count', 'folder_count', 'totalDocuments','readDocuments','unreadDocuments'));
    }
}
