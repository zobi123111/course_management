<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\User;
use Illuminate\Support\Facades\Session;

class GroupController extends Controller
{
    public function index()
    {
        // Get the currently logged-in user
        $currentUser = auth()->user();

        // Fetch users with the same ouid as the logged-in user
        $users = User::where('ou_id', $currentUser->ou_id)->get();

        $groups = Group::all()->map(function ($group) {
            // Ensure user_ids is a valid array
            $userIds = is_string($group->user_ids) ? json_decode($group->user_ids, true) : $group->user_ids;
    
            // Ensure it's an array before counting
            $group->user_count = is_array($userIds) ? count($userIds) : 0;
    
            return $group;
        });    
        
        return view('Groups.index', compact('groups', 'users'));
    }

    public function store(Request $request)
    {   
        $request->validate([
            'name' => 'required|unique:groups|max:255',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id', // Ensure all user IDs exist
        ]);

        $group = Group::create([
            'name' => $request->name,
            'user_ids' => $request->user_ids,
        ]);

        Session::flash('message', 'Group created successfully.');
        return response()->json(['message' => 'Group created successfully', 'group' => $group]);
    }

    public function show(Request $request, Group $group)
    {
        $group = Group::findOrFail($request->id);
        return response()->json(['group'=> $group]);
    }

    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'required',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $group->update([
            'name' => $request->name,
            'user_ids' => $request->user_ids,
        ]);
        Session::flash('message', 'Group updated successfully.');
        return response()->json(['message' => 'Group updated successfully', 'group' => $group]);
    }

    public function destroy(Group $group)
    {
        $groups = Group::findOrFail($request->id);
        if ($groups) {
            $groups->delete();
            return redirect()->route('group.index')->with('message', 'Group deleted successfully');
        }
    }
}