<?php
 
use Hashids\Hashids;
use Illuminate\Support\Facades\Auth;
use App\Models\Page;
use App\Models\User;
use App\Models\Role;
use App\Models\Setting;
use App\Models\OrganizationUnits;


function encode_id($id)
{
    $salt = config('hashids.connections.main.salt'); 
    $length = config('hashids.connections.main.length', 8);
    // $hashids = new Hashids(env('HASHIDS_SALT'), 8);
    $hashids = new Hashids($salt, $length);
    return $hashids->encode($id);
}

function decode_id($hashedId)
{
    $salt = config('hashids.connections.main.salt'); 
    $length = config('hashids.connections.main.length', 8);
    // $hashids = new Hashids(env('HASHIDS_SALT'), 8);
    $hashids = new Hashids($salt, $length);
    $decoded = $hashids->decode($hashedId);
    return !empty($decoded) ? $decoded[0] : null;
}

 function hasPermission($module)
{
    return auth()->user()->roledata->rolePermissions->contains('module',$module);
}


// function getAllowedPages()
// {
//     $user = Auth::user();

//     if (!$user || !$user->role) {
//         return collect(); 
//     }

//     if ($user->is_owner) {
//         return Page::with('modules')->orderBy('position', 'asc')->get();
//     }

//     $dashboardPage = Page::with('modules')->whereHas('modules', function ($query) {
//         $query->where('route_name', 'dashboard');
//     })->first(); // Get the Dashboard page
    
//     $allowedPages = Page::with('modules')
//         ->orderBy('position', 'asc')
//         ->whereHas('modules', function ($query) use ($user) {
//             $query->whereHas('rolePermissions', function ($subQuery) use ($user) {
//                 $subQuery->where('role_id', $user->role);
//             });
//         })
//         ->get();
    
//     // Merge the Dashboard page with the allowed pages (if it's not already included)
//     if ($dashboardPage && !$allowedPages->contains('id', $dashboardPage->id)) {
//         $allowedPages->prepend($dashboardPage);
//     }

//     return $allowedPages;
// }

function getAllowedPages()
{
    $user = Auth::user();
    
    if (!$user  || !$user->role) {
        return collect();
    }
    // Get the active role from session (fallback to the default role)
    $current_role = session('current_role', $user->role);


    

    // If user is the owner, return all pages
    if ($user->is_owner) {
        return Page::with('modules')->orderBy('position', 'asc')->get();
    }  

    // Always allow the Dashboard page
    $dashboardPage = Page::with('modules')->whereHas('modules', function ($query) {
        $query->where('route_name', 'dashboard');
    })->first();


    if ($user->is_admin == 1) {
        $organizationUnit = DB::table('organization_units')->where('id', $user->ou_id)->first();

        if ($organizationUnit && $organizationUnit->permission) {
            $allowedPageIds = json_decode($organizationUnit->permission, true);
            
            if (!is_array($allowedPageIds) || empty($allowedPageIds)) {
                return collect([$dashboardPage]); 
            }

            return Page::with('modules')
                ->whereIn('id', $allowedPageIds)
                ->orderBy('position', 'asc')
                ->get();
        }

        return collect([$dashboardPage]);
    }

    if (empty($user->is_admin)) {
        $organizationUnit = DB::table('organization_units')->where('id', $user->ou_id)->first();

        if ($organizationUnit && $organizationUnit->permission) {
            $allowedPageIds = json_decode($organizationUnit->permission, true);

            if (!is_array($allowedPageIds) || empty($allowedPageIds)) {
                return collect($dashboardPage ? [$dashboardPage] : []);
            }

            $allowedPages = Page::with('modules')
                ->whereIn('id', $allowedPageIds)
                ->whereHas('modules', function ($query) use ($current_role) {
                    $query->whereHas('rolePermissions', function ($subQuery) use ($current_role) {
                        $subQuery->where('role_id', $current_role);
                    });
                })
                ->orderBy('position', 'asc')
                ->get();

            if ($dashboardPage && !$allowedPages->contains('id', $dashboardPage->id)) {
                $allowedPages->prepend($dashboardPage);
            }

            return $allowedPages;
        }

        return collect($dashboardPage ? [$dashboardPage] : []);
    }


    // Get allowed pages based on the current role
    $allowedPages = Page::with('modules')
        ->orderBy('position', 'asc')
        ->whereHas('modules', function ($query) use ($current_role) {
            $query->whereHas('rolePermissions', function ($subQuery) use ($current_role) {
                $subQuery->where('role_id', $current_role);
            });
        })
        ->get();

    // Ensure the dashboard page is included
    if ($dashboardPage && !$allowedPages->contains('id', $dashboardPage->id)) {
        $allowedPages->prepend($dashboardPage);
    }

    return $allowedPages;
}

function checkAllowedModule($pageRoute, $routeName = null)
{
    $user = Auth::user();
    if (!$user || !$user->role) {
        return collect(); 
    }

    if ($user->is_owner) {
        return Page::where('route_name', $pageRoute)
            ->with('modules')
            ->orderBy('position', 'asc')
            ->get();
    }
    return Page::where('route_name', $pageRoute)->with(['modules' => function ($query) use ($routeName) {
        if ($routeName) {
           // dump($routeName);
            $query->where('route_name', $routeName);
        }
    }])
    ->orderBy('position', 'asc')
    ->whereHas('modules', function ($query) use ($user, $routeName) {
        $query->whereHas('rolePermissions', function ($subQuery) use ($user) {
            $subQuery->where('role_id', $user->role);
        });

        if ($routeName) {
           // dump($routeName);
            $query->where('route_name', $routeName);
        }
    })->get();
}

function getMultipleRoles()
{
    $user = User::with('roles')->where('id', auth()->id())->first(); // Get single user

    if (!$user) {
        return [];
    }

    $multiple_roles = [$user->role]; // Primary role

    // Check if extra_roles is stored as JSON
    if (!empty($user->extra_roles)) {
        $extra_roles = json_decode($user->extra_roles, true); // Decode JSON
        if (is_array($extra_roles)) {
            $multiple_roles = array_merge($multiple_roles, $extra_roles);
        }
    }

    // Fetch role details
    return Role::whereIn('id', $multiple_roles)->get(); 
}

function ou_logo()
{
    $ou_id = Auth::user()->ou_id;  
    $org_detail = OrganizationUnits::where('id', $ou_id)->first(); // Fetch only one record
    return $org_detail;
}

function hasUserRole($user, $roleName)
{
    if (!$user->relationLoaded('roles')) {
        $user->load('roles');
    }
    
    return collect($user->roles)->contains(function ($role) use ($roleName) {
        // If $role is an integer, retrieve the Role model.
        if (is_int($role)) {
            $roleObj = Role::find($role);
            return $roleObj && stripos($roleObj->role_name, $roleName) !== false;
        }
        // Otherwise, assume $role is a model instance.
        return isset($role->role_name) && stripos($role->role_name, $roleName) !== false;
    });
}

function settingData()
{
    
    $setting = Setting::first();

    return $setting;
}

if (!function_exists('countAcknowledgedDocuments')) {
    function countAcknowledgedDocuments($documents, $user)
    {
        $readDocuments = 0;
        $userId = $user->id;

        foreach ($documents as $doc) {
            $acknowledgedUsers = json_decode($doc->acknowledge_by ?? '[]', true);

            $groupUserIds = !empty($doc->group->user_ids)
                ? (is_array($doc->group->user_ids) ? $doc->group->user_ids : explode(',', trim($doc->group->user_ids)))
                : [];

            if ($user->is_owner || $user->is_admin) {
                if (!empty($groupUserIds) && !array_diff($groupUserIds, $acknowledgedUsers)) {
                    $readDocuments++;
                }
            } else {
                if (in_array($userId, $acknowledgedUsers)) {
                    $readDocuments++;
                }
            }
        }

        return $readDocuments;
    }
}

function get_user_role($roleId)
{
    $role = Role::find($roleId);

    return $role ? strtolower($role->role_name) : null;
}








?>
