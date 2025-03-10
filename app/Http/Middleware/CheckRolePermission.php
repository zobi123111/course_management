<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Page;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class CheckRolePermission
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        if (!$user || !$user->role) {
            return redirect()->route('login')->with('error', 'Unauthorized access!');
        }
        if ($user->is_owner) {
            return $next($request);
        }

          // Get allowed pages based on role permissions
          if($request->route()->getName() == 'dashboard'){
                return $next($request);
            }

          $allowedPages = getAllowedPages()->pluck('modules.*.route_name')->flatten();

         // Handle specific permissions for Create and Edit actions
         if ($request->isMethod('get') && !$allowedPages->contains($request->route()->getName())) {
            Session::flash('message', 'You dont have permission to access this page');
            return redirect()->route('dashboard')->with('error', 'Access Denied!');
        }
        return $next($request);
    }
}
