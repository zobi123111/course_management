<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Page;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

// class CheckRolePermission
// {
//     public function handle(Request $request, Closure $next)
//     {
//         $user = Auth::user();
//         if (!$user || !$user->role) {
//             return redirect()->route('login')->with('error', 'Unauthorized access!');
//         }
//         if ($user->is_owner) {
//             return $next($request);
//         }

//         if ($user && $user->password_flag == 1) {
//             return redirect()->route('change-password');
//         } 

//           // Get allowed pages based on role permissions
//           if($request->route()->getName() == 'dashboard'){
//                 return $next($request);
//             }

//           $allowedPages = getAllowedPages()->pluck('modules.*.route_name')->flatten();

//          // Handle specific permissions for Create and Edit actions
//          if ($request->isMethod('get') && !$allowedPages->contains($request->route()->getName())) {
//             Session::flash('message', 'You dont have permission to access this page');
//             return redirect()->route('dashboard')->with('error', 'Access Denied!');
//         }
//         return $next($request);
//     }
// }

class CheckRolePermission
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // If no user is authenticated, redirect to login
        if (!$user || !$user->role) {
            return redirect()->route('login')->with('error', 'Unauthorized access!');
        }

        // Get the active role from the session, fallback to user's default role
        $current_role = session('current_role', $user->role);

        // If the user is the owner, allow all access
        if ($user->is_owner) { 
        }

        // Redirect to change password if required
        if ($user->password_flag == 1) {
            return redirect()->route('change-password');
        }

        // Allow access to the dashboard for all roles
        if ($request->route()->getName() == 'dashboard') {
            return $next($request);
        }

        // Fetch allowed pages based on the active session role
        $allowedPages = getAllowedPages($current_role)->pluck('modules.*.route_name')->flatten();
        // dd($allowedPages); 
        // Check if the user has access to the requested route
        if ($request->isMethod('get') && !$allowedPages->contains($request->route()->getName())) {
            Session::flash('message', 'You don\'t have permission to access this page.');
            return redirect()->route('dashboard')->with('error', 'Access Denied!');
        }

        return $next($request);
    }
}
