<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Resource;
use App\Models\OrganizationUnits;
use App\Models\Group;
use App\Models\User;
use Auth;

class BookingController extends Controller
{
    public function index()
    {
        $student = auth()->user()->role;
        $organizationUnits = OrganizationUnits::all();
        $groups = Group::all();
        $students = collect(); // ✅ collect all students

        foreach ($groups as $val) {
            // user_ids is an array like ["100","114","154"]
            if (!empty($val->user_ids) && is_array($val->user_ids)) {
                $groupStudents = User::whereIn('id', $val->user_ids)->get();
                $students = $students->merge($groupStudents);
            }
        }

        // remove duplicate users (if any)
        $students = $students->unique('id')->values();
        if ($student == 3) {
            $ou_id = auth()->user()->ou_id;
            $resources  = Resource::where('ou_id', $ou_id)->get();
        } else {
           // $resources  = Resource::all();
            $bookedResourceIds = Booking::pluck('resource')->unique();
           // dd($bookedResourceIds);
            $resources = Resource::whereNotIn('id', $bookedResourceIds)->get();
        }
        return view('calender.index', compact('resources', 'organizationUnits', 'groups', 'students'));
    }


  public function loadEvents(Request $request)
{
    

     $student = $request->student;
    $resource = $request->resource;

    $events = Booking::with('resources', 'users')
        ->when($student, function ($q) use ($student) {
            $q->whereHas('users', function ($u) use ($student) {
                $u->where('fname', 'like', "%$student%")
                  ->orWhere('lname', 'like', "%$student%");
            });
        })
        ->when($resource, function ($q) use ($resource) {
            $q->whereHas('resources', function ($r) use ($resource) {
                $r->where('name', 'like', "%$resource%");
            });
        })
        ->get();

    $user_id = Auth::id();
    $data = [];

    foreach ($events as $event) {

        $canAccess = false;

        if ($event->users->id == $user_id || Auth::user()->role == 1) {
            $canAccess = true;
        }

        $data[] = [
            'id'           => $event->id,
            'student'      => $event->users->fname . ' ' . $event->users->lname,
            'title'        => $event->users->fname . ' ' . $event->users->lname,
            'resource'     => $event->resources->name ?? '',
            'start'        => $event->start,
            'end'          => $event->end,
            'booking_type' => $event->booking_type,
            'status'       => $event->status,
            'can_access'   => $canAccess
        ];
    }

    return response()->json($data);
}


    public function store(Request $request)
    {
        // dd($request->all());
        $booking = new Booking();
        $booking->ou_id = $request->organizationUnits;
        $booking->std_id = $request->student ?? Auth::user()->id;
        $booking->resource = $request->resource_id;
        $booking->start = $request->start;
        $booking->end = $request->end;
        $booking->booking_type = $request->booking_type;
        $booking->resource_type = $request->resource_type;
        $booking->instructor_id = $request->instructor_id;
        $booking->status = "pending";
        $booking->save();
        return response()->json(['success' => true]);
    }

    public function approve(Request $request)
    {
        $booking = Booking::find($request->id);
        $booking->status = "approved";
        $booking->save();

        return response()->json(['success' => true]);
    }

    public function reject(Request $request)
    {
        $booking = Booking::find($request->id);
        $booking->status = "rejected";
        $booking->save();

        return response()->json(['success' => true]);
    }

    public function getstudents(Request $request)
    {
        $org_group    = Group::where('ou_id', $request->ou_id)->get();
        $students = collect(); // ✅ collect all students

        foreach ($org_group as $val) {
            // user_ids is an array like ["100","114","154"]
            if (!empty($val->user_ids) && is_array($val->user_ids)) {
                $groupStudents = User::whereIn('id', $val->user_ids)->get();
                $students = $students->merge($groupStudents);
            }
        }

        // remove duplicate users (if any)
        $students = $students->unique('id')->values(); 

        $bookedResourceIds = Booking::pluck('resource')->unique();

        $org_resource = Resource::whereNotIn('id', $bookedResourceIds)->where('ou_id', $request->ou_id)->get();
        $ato_num = OrganizationUnits::where('id', $request->ou_id)->get();
        if ($org_group) {
            return response()->json(['org_group' => $org_group, 'org_resource' => $org_resource, 'ato_num' => $ato_num, 'students' => $students]);
        } else {
            return response()->json(['error' => 'No group Found']);
        }
    }
}
