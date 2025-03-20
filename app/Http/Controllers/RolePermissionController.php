<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Module;
use App\Models\Page;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class RolePermissionController extends Controller
{
    public function index()
    {
        $rolePermission = RolePermission::with(['role','module'])->get();
        // $roles = Role::all();
        // $roles = Role::with('users')->get();     
        $roles = Role::withCount('users')->get();
        return view('role_permissions.index', compact('rolePermission','roles'));
    }

    public function create()
    {
        $roles = Role::all();
        $pages = Page::with(['modules'])->get(); 
        return view('role_permissions.create', compact('roles', 'pages'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'role_name' => 'required|string',
            'module_ids' => 'required|array',  
            'module_ids.*' => 'exists:modules,id',
            'status' => 'required',

        ]);
    
        // Create a new RolePermission instance and save it
        try {

            $createRole = Role::create([
                'role_name' => $validated['role_name'],
                'status' => $validated['status'],
            ]);

            // Attach the selected modules to the role_permission
            foreach ($validated['module_ids'] as $pageId => $moduleIds) {
                foreach ($moduleIds as $moduleId) {
                    // dd($moduleId);
                    $rolePermission = RolePermission::create([
                        'role_id' => $createRole->id,
                        'module_id' => $moduleId

                    ]);
                    // $rolePermission->modules()->attach($moduleId);
                }
            }
    
            // Success flash message
            Session::flash('message', 'Role Created successfully');
            return redirect()->route('role_permissions.index');
        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors('Something went wrong. Please try again.')
                ->withInput();
        }
    }

    public function edit($roleId)
    {
        $roleId = decode_id($roleId);
        try {
            $role = Role::with('rolePermissions')->findOrFail($roleId);

            $currentModules = $role->rolePermissions->pluck('module_id')->toArray();

            // Get the list of pages and modules
            $pages = Page::with('modules')->get(); 
    
            return view('role_permissions.edit', compact('role', 'pages', 'currentModules'));
        } catch (\Exception $e) {
            // Handle error if role not found
            return redirect()->back()
                             ->withErrors('Role not found.')
                             ->withInput();
        }
    }

    public function update(Request $request, $roleId)
    {

        // dd($request->input('status'));
        $en = $roleId;
        $roleId = decode_id($roleId);

        $validated = $request->validate([
            'role_name' => 'required|string',
            'module_ids' => 'required|array', 
            'module_ids.*' => 'exists:modules,id', 
            'status' => 'required',
        ]);
        
        // Find the role
        $role = Role::findOrFail($roleId);
    
        $role->update([
            'role_name' => $request->input('role_name'),
            'status' => $request->input('status'),
        ]);

        // Get the selected modules from the request
        $selectedModules = $request->input('module_ids');
        $role->rolePermissions()->delete();

            // Attach the selected modules to the role_permission
            foreach ($validated['module_ids'] as $pageId => $moduleIds) {
                foreach ($moduleIds as $moduleId) {
                    $rolePermission = RolePermission::create([
                        'role_id' => $role->id,
                        'module_id' => $moduleId

                    ]);
                }
            }

        Session::flash('message', 'Role Updated successfully');
        return redirect()->route('roles.edit', $en);
    }

    // public function destroy($roleId)
    // {
    //     $en = $roleId;
    //     $roleId = decode_id($roleId);
    //     $role = Role::findOrFail($roleId);

    //     // if ($role->users->count() > 0) {
    //     //     Session::flash('error', 'Cannot delete this role. It is assigned to one or more users.');
    //     //     return redirect()->route('roles.index');
    //     // }
    //     if ($role->users->count() > 0) {
    //         $count = $role->users->count();
    //         Session::flash('error', 'Cannot delete this role. It is assigned to one or more users.');
    //         return redirect()->route('roles.index')->with('role_with_users', $count, true);
    //     }

    //     $role->rolePermissions()->delete();
    //     $role->delete();
    //     Session::flash('message', 'All permissions deleted successfully.');
    //     return redirect()->route('roles.index');
    // }

    public function destroy($roleId)
{
    $roleId = decode_id($roleId);
    $role = Role::findOrFail($roleId);

    if ($role->users->count() > 0) {
        Session::flash('error', 'Cannot delete this role. It is assigned to one or more users.');
        return redirect()->route('roles.index')->with('role_with_users', $roleId);
    }

    $role->rolePermissions()->delete();
    $role->delete();
    Session::flash('message', 'Role deleted successfully.');
    return redirect()->route('roles.index');
}

}
