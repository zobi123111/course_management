<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\Role;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }   

    public function createRole(Request $request)
    {
        $request->validate([            
            'role_name' => 'required',
            'status' => 'required|boolean'
        ]);

        Role::create([
            'role_name' => $request->role_name,
            'status' => $request->status
        ]);

        Session::flash('message', 'Role created successfully.');
        return response()->json(['success' => 'Role created successfully.']);
    }

    public function getRoleById(Request $request)
    {
        $role = Role::findOrFail($request->role_id);
        return response()->json(['role'=> $role]);
    }
}
