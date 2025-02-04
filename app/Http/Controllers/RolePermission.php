<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Module;
use App\Models\Page;
use Illuminate\Support\Facades\Session;

class RolePermission extends Controller
{
    public function index()
    {
        $rolePermission = RolePermission::with(['role','module'])->get();
        $roles = Role::all();     
        return view('role.index', compact('rolePermission','roles'));
    }
}
