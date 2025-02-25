<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use App\Models\OrganizationUnits;
use Illuminate\Support\Facades\Session;

class GroupController extends Controller
{
    // public function index()
    // {
    //     //Get the currently logged-in user
    //     $currentUser = auth()->user();
    //     // Fetch users with the same ouid as the logged-in user
    //     $users = User::where('ou_id', $currentUser->ou_id)->get();
    //     $urganizationUnits = OrganizationUnits::all();
    //     if ($currentUser->role == 1 && empty($currentUser->ou_id)) {
    //         $groups = Group::all();
    //     } else {
    //         $groups = Group::where('ou_id', $currentUser->ou_id)->get();
    //     }
        
    //     // Process user counts safely
    //     $groups = $groups->map(function ($group) {
    //         // Decode user_ids only if it's a string
    //         $userIds = is_string($group->user_ids) ? json_decode($group->user_ids, true) : $group->user_ids;
            
    //         // Ensure it's an array before counting
    //         $group->user_count = is_array($userIds) ? count($userIds) : 0;
    //         return $group;
    //     });
        
    //     return view('groups.index', compact('groups', 'users', 'urganizationUnits'));
    // }

    public function index()
    {
        $currentUser = auth()->user();
        if ($currentUser->role == 1 && empty($currentUser->ou_id)) {
            $users = User::all();
        }else{
            $users = User::where('ou_id', $currentUser->ou_id)->get();
        }
        $organizationUnits = OrganizationUnits::all();
        
        $groups = Group::all();

        if ($currentUser->role == 1 && empty($currentUser->ou_id)) {
            $groups = $groups;
        } 
        elseif (checkAllowedModule('groups', 'group.index')->isNotEmpty()) {
            $userId = $currentUser->id;

            $groups = $groups->filter(function ($group) use ($userId) {
                $userIds = is_string($group->user_ids) ? json_decode($group->user_ids, true) : $group->user_ids;
                $userIds = is_array($userIds) ? $userIds : [];
                return in_array($userId, $userIds);
            });
        } 
        else {
            $groups = Group::where('ou_id', $currentUser->ou_id)->get();
        }

        $groups = $groups->map(function ($group) {
            $userIds = is_string($group->user_ids) ? json_decode($group->user_ids, true) : $group->user_ids;

            $group->user_count = is_array($userIds) ? count($userIds) : 0;
            return $group;
        });
        
        return view('groups.index', compact('groups', 'users', 'organizationUnits'));
    }


    public function createGroup(Request $request)
    {   
        // dd($request);
        $request->validate([
            'name' => 'required|unique:groups|max:255',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id', // Ensure all user IDs exist
            'status' => 'required',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ]
        ]);

        $group = Group::create([
            'ou_id' =>  (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id, // Assign ou_id only if Super Admin provided it
            'name' => $request->name,
            'user_ids' => $request->user_ids,
            'status' => $request->status,
        ]);

        Session::flash('message', 'Group created successfully.');
        return response()->json(['message' => 'Group created successfully', 'group' => $group]);
    }

    public function getGroup(Request $request)
    {
        $group = Group::findOrFail(decode_id($request->id));
        return response()->json(['group'=> $group]);
    }

    public function updateGroup(Request $request)
    {
        // dd($request);
        $request->validate([
            'name' => 'required',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
            'status' => 'required',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ]
        ]);

        $group = Group::findOrFail($request->group_id);
        // dd($group);
        $group->update([
            'ou_id' =>  (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id, // Assign ou_id only if Super Admin provided it
            'name' => $request->name,
            'user_ids' => $request->user_ids,
            'status' => $request->status,
        ]);
        Session::flash('message', 'Group updated successfully.');
        return response()->json(['message' => 'Group updated successfully', 'group' => $group]);
    }

    public function deleteGroup(Request $request)
    {
        // dd($request->id);
        $group = Group::findOrFail(decode_id($request->group_id));
        if ($group) {
            $group->delete();
            return redirect()->route('group.index')->with('message', 'Group deleted successfully');
        }
    }
}