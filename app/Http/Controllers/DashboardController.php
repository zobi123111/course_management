<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Courses;
use App\Models\User;
use App\Models\Group;
use App\Models\Folder;
use App\Models\Document;
use App\Models\BookedResource;
use Illuminate\Support\Facades\Auth;


class DashboardController extends Controller
{
    // public function index() 
    // {
    //     $ou_id = auth()->user()->ou_id;
    //     $user_count = 0;
    //     $course_count = 0;
    //     $group_count = 0;
    //     $folder_count = 0;
       
    //     if(Auth()->user()->is_owner ==  1){
    //         // dd('admin');            
    //         $user_count = User::count(); 
    //         $course_count = Courses::count();
    //         $group_count = Group::count();
    //         $folder_count = Folder::whereNull('parent_id')->with('children')->get()->count();
    //         $documents = Document::all();
    //         $requestCount = 0;
    //     }elseif(Auth()->user()->is_admin==1){ 
    //         // dd('ou');            
    //         $user_count = User::where('ou_id' , $ou_id)->count();
    //         $course_count = Courses::where('ou_id' , $ou_id)->count();
    //         $group_count = Group::where('ou_id' , $ou_id)->count();
    //         $folder_count = Folder::whereNull('parent_id')->where('ou_id', $ou_id)->with('children')->get()->count();
    //         $documents = Document::where('ou_id', $ou_id)->get();
    //         $requestCount = $requestCount = BookedResource::where('ou_id', $ou_id)->count();
    //     }else{
    //         // dd('user');            
    //         $userId = Auth::user()->id;
    //         $groups = Group::all();
    //         $filteredGroups = $groups->filter(function ($group) use ($userId) {
    //             $userIds = is_array($group->user_ids) ? $group->user_ids : explode(',', $group->user_ids);
    //             return in_array($userId, $userIds);
    //         });
    //         $groupIds = $filteredGroups->pluck('id')->toArray();
    //         $courses = Courses::whereIn('id', function ($query) use ($groupIds) {
    //             $query->select('courses_id')
    //                 ->from('courses_group')
    //                 ->whereIn('group_id', $groupIds);
    //         })->get();

    //         $documents = Document::where('ou_id', $ou_id)
    //         ->whereHas('group', function ($query) use ($userId) {
    //             $query->whereJsonContains('user_ids', (string) $userId);
    //         })
    //         ->get();
    //         $course_count = $courses->count();
    //         $group_count = $filteredGroups->count();
    //         // $folder_count = 0;
    //         $requestCount =$requestCount = BookedResource::where('user_id', $userId)
    //         ->where('ou_id', $ou_id)
    //         ->count();
        
    //     }

    //     $totalDocuments = $documents->count();
    //     $readDocuments = $documents->where('acknowledged', 1)->count();
    //     $unreadDocuments = $totalDocuments-$readDocuments;
    //     return view('dashboard.index', compact('user_count','course_count', 'group_count', 'folder_count', 'totalDocuments','readDocuments','unreadDocuments', 'requestCount'));
    // }

    public function index() 
{
    $ou_id = auth()->user()->ou_id;
    $user_count = 0;
    $course_count = 0;
    $group_count = 0;
    $folder_count = 0;
    $requestCount = 0;

    if (auth()->user()->is_owner == 1) {
        $user_count = User::count(); 
        $course_count = Courses::count();
        $group_count = Group::count();
        $folder_count = Folder::whereNull('parent_id')->with('children')->count();
        $documents = Document::with('group')->get();
    } elseif (auth()->user()->is_admin == 1) { 
        $user_count = User::where('ou_id', $ou_id)->count();
        $course_count = Courses::where('ou_id', $ou_id)->count();
        $group_count = Group::where('ou_id', $ou_id)->count();
        $folder_count = Folder::whereNull('parent_id')->where('ou_id', $ou_id)->with('children')->count();
        $documents = Document::where('ou_id', $ou_id)->with('group')->get();
        $requestCount = BookedResource::where('ou_id', $ou_id)->count();
    } else {
        $userId = auth()->user()->id;

        $groups = Group::all();
        $filteredGroups = $groups->filter(function ($group) use ($userId) {
            $userIds = is_array($group->user_ids) ? $group->user_ids : explode(',', $group->user_ids);
            return in_array($userId, $userIds);
        });

        $groupIds = $filteredGroups->pluck('id')->toArray();

        $courses = Courses::whereIn('id', function ($query) use ($groupIds) {
            $query->select('courses_id')->from('courses_group')->whereIn('group_id', $groupIds);
        })->get();

        $documents = Document::where('ou_id', $ou_id)
            ->whereHas('group', function ($query) use ($userId) {
                $query->whereJsonContains('user_ids', (string) $userId);
            })
            ->with('group')
            ->get();

        $course_count = $courses->count();
        $group_count = $filteredGroups->count();
        $requestCount = BookedResource::where('user_id', $userId)->where('ou_id', $ou_id)->count();
    }

    // Count total documents
    $totalDocuments = $documents->count();
    $readDocuments = 0;

    // Check if documents have been acknowledged
    foreach ($documents as $doc) {
        $groupUserIds = [];
        if (!empty($doc->group) && !empty($doc->group->user_ids)) {
            $groupUserIds = is_array($doc->group->user_ids) 
                ? $doc->group->user_ids 
                : explode(',', trim($doc->group->user_ids));
        }

        $acknowledgedUsers = json_decode($doc->acknowledge_by ?? '[]', true);

        if (!empty($groupUserIds) && !array_diff($groupUserIds, $acknowledgedUsers)) {
            $readDocuments++; // Increase count if all group users acknowledged
        }
    }

    $unreadDocuments = $totalDocuments - $readDocuments;  
    $users = User::where('ou_id', Auth::user()->ou_id)->where('is_admin', '=', null)->get();
                                                   
    return view('dashboard.index', compact(
        'user_count', 'course_count', 'group_count', 'folder_count', 
        'totalDocuments', 'readDocuments', 'unreadDocuments', 'requestCount', 'users'
    ));
}
}
