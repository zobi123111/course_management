<?php 
use Hashids\Hashids;
use Illuminate\Support\Facades\Auth;
use App\Models\Page;

function encode_id($id)
{
    $hashids = new Hashids(env('HASHIDS_SALT'), 8);
    return $hashids->encode($id);
}

function decode_id($hashedId)
{
    $hashids = new Hashids(env('HASHIDS_SALT'), 8);
    $decoded = $hashids->decode($hashedId);
    return !empty($decoded) ? $decoded[0] : null;
}

 function hasPermission($module)
{
    return auth()->user()->roledata->rolePermissions->contains('module',$module);
}


function getAllowedPages()
{
    $user = Auth::user();

    if (!$user || !$user->role) {
        return collect(); 
    }

    if ($user->is_owner) {
        return Page::with('modules')->orderBy('position', 'asc')->get();
    }

    $dashboardPage = Page::with('modules')->whereHas('modules', function ($query) {
        $query->where('route_name', 'dashboard');
    })->first(); // Get the Dashboard page
    
    $allowedPages = Page::with('modules')
        ->orderBy('position', 'asc')
        ->whereHas('modules', function ($query) use ($user) {
            $query->whereHas('rolePermissions', function ($subQuery) use ($user) {
                $subQuery->where('role_id', $user->role);
            });
        })
        ->get();
    
    // Merge the Dashboard page with the allowed pages (if it's not already included)
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
            $query->where('route_name', $routeName);
        }
    }])
    ->orderBy('position', 'asc')
    ->whereHas('modules', function ($query) use ($user, $routeName) {
        $query->whereHas('rolePermissions', function ($subQuery) use ($user) {
            $subQuery->where('role_id', $user->role);
        });

        if ($routeName) {
            $query->where('route_name', $routeName);
        }
    })->get();
}

?>
