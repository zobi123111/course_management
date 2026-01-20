<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Resource;
use App\Models\OrganizationUnits;
use App\Models\Group;
use App\Models\User;
use App\Models\OuSetting;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;


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
            ->whereNotIn('status', ['cancelled', 'rejected'])
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

            if ($event->users->id == $user_id || Auth::user()->role == 1 || Auth::user()->is_admin == 1 ) {
                $canAccess = true;
            }

            $data[] = [
                'id'           => $event->id,
                'student'      => $event->users->fname . ' ' . $event->users->lname,
                'title'        => $event->users->fname . ' ' . $event->users->lname,
                'resource'     => $event->resources->name ?? '',
                'start'        => $event->start,
                'end'          => Carbon::parse($event->end)->addDay(),
                'booking_type' => $event->booking_type,
                'status'       => $event->status,
                'can_access'   => $canAccess,
                'std_id'       => $event->std_id,
                'resource_id'  => $event->resource,
                'ou_id'        => $event->ou_id,
                'send_email'   => $event->send_email,
                'instructor_id'=> $event->instructor_id,
            ];
        }

        return response()->json($data);
    }

    public function store(Request $request)
    {
        $booking = new Booking();
        $booking->ou_id = $request->organizationUnits;
        $booking->std_id = $request->student ?? Auth::user()->id;
        $booking->resource = $request->resource_id;
        $booking->start = $request->start;
        $booking->end = $request->end;
        $booking->booking_type = $request->booking_type;
        $booking->resource_type = $request->resource_type;
        $booking->instructor_id = $request->instructor;
        $booking->status = "pending";
        $booking->send_email = $request->boolean('send_email') ? 1 : 0;
        $booking->save();

       // $sendemail = organizationUnits::where('id', $request->organizationUnits)->first();
        $checkSend_mail = OuSetting::where('organization_id', $request->organizationUnits)->select('send_email')->first();
        if ($checkSend_mail->send_email == 1) { 
 
            $studentEmail = User::find($booking->std_id)->email;
            $instructor = User::find($booking->instructor_id)->email;
            $ouEmails = User::where('ou_id', $booking->ou_id)->where('is_admin', 1)->pluck('email')->toArray();

            $allEmails = array_merge([$studentEmail], $ouEmails, [$instructor]);

            Mail::send('emailtemplates.create_booking_email', ['booking' => $booking], function ($message) use ($allEmails) {
                $message->to($allEmails)
                        ->subject('New Booking Created');
            });
        }
        return response()->json(['success' => true]);
    }

    public function update(Request $request)
    {
        $booking = Booking::findOrFail($request->id);
        $booking->ou_id = $request->organizationUnits;
        $booking->std_id = $request->student ?? Auth::user()->id;
        $booking->resource = $request->resource_id;
        $booking->start = $request->start;
        $booking->end = $request->end;
        $booking->booking_type = $request->booking_type ?? $booking->booking_type;
        $booking->resource_type = $request->resource_type ?? $booking->resource_type;
        $booking->instructor_id = $request->instructor_id ?? $booking->instructor_id;
        $booking->send_email = $request->boolean('send_email') ? 1 : 0;
        // $booking->status = 'pending';
        $booking->save();

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'booking' => $booking
        ]);
    }

    public function approve(Request $request)
    {
        $booking = Booking::findOrFail($request->id);

        $booking->status = 'approved';
        $booking->save();

        Booking::where('id', '!=', $booking->id)
            ->where('resource', $booking->resource)
            ->where('resource_type', $booking->resource_type)
            ->where(function ($q) use ($booking) {
                $q->where('start', '=', $booking->end)
                ->where('end', '=', $booking->start);
            })
            ->update(['status' => 'rejected']);

        $sendemail = organizationUnits::where('id', $request->organizationUnits)->first();

        if ($sendemail->send_email == 1) {

            $studentEmail = User::find($booking->std_id)->email;
            $instructor = User::find($booking->instructor_id)->email;
            $ouEmails = User::where('ou_id', $booking->ou_id)->where('is_admin', 1)->pluck('email')->toArray();

            $allEmails = array_merge([$studentEmail], $ouEmails, [$instructor]);

            Mail::send('emailtemplates.approved_booking_email',['booking' => $booking],function ($message) use ($allEmails) {
                    $message->to($allEmails)->subject('Booking Approved');
                });
        }

        return response()->json(['success' => true]);
    }


    public function reject(Request $request)
    {
        $booking = Booking::find($request->id);
        $booking->status = "rejected";
        $booking->save();


        $sendemail = organizationUnits::where('id', $request->organizationUnits)->first();
        
        if ($sendemail->send_email == 1) {

            $studentEmail = User::find($booking->std_id)->email;
            $instructor = User::find($booking->instructor_id)->email;
            $ouEmails = User::where('ou_id', $booking->ou_id)->where('is_admin', 1)->pluck('email')->toArray();

            $allEmails = array_merge([$studentEmail], $ouEmails, [$instructor]);

            Mail::send('emailtemplates.rejected_booking_email', ['booking' => $booking], function ($message) use ($allEmails) {
                $message->to($allEmails)
                        ->subject('Booking Rejected');
            });
        }

        return response()->json(['success' => true]);
    }

    public function delete(Request $request)
    {
        $booking = Booking::find($request->id);
        $booking->status = 'cancelled';
        $booking->save();


        // $sendemail = organizationUnits::where('id', $request->organizationUnits)->first();
        
        // if ($sendemail->send_email == 1) {

        //     $studentEmail = User::find($booking->std_id)->email;
        //     $instructor = User::find($booking->instructor_id)->email;
        //     $ouEmails = User::where('ou_id', $booking->ou_id)->where('is_admin', 1)->pluck('email')->toArray();

        //     $allEmails = array_merge([$studentEmail], $ouEmails, [$instructor]);

        //     Mail::send('emailtemplates.rejected_booking_email', ['booking' => $booking], function ($message) use ($allEmails) {
        //         $message->to($allEmails)
        //                 ->subject('Booking Rejected');
        //     });
        // }

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

        $instructors = User::where('ou_id', $request->ou_id)->where('role', 18)->get(['id', 'fname', 'lname']);

        // remove duplicate users (if any)
        $students = $students->unique('id')->values(); 

        $bookedResourceIds = Booking::where('status','approved')->pluck('resource')->unique();
        
        $org_resource = Resource::whereNotIn('id', $bookedResourceIds)->where('ou_id', $request->ou_id)->get();

        $ato_num = OrganizationUnits::where('id', $request->ou_id)->get();
        if ($org_group) {
            return response()->json(['org_group' => $org_group, 'org_resource' => $org_resource, 'ato_num' => $ato_num, 'students' => $students, 'instructors' => $instructors]);
        } else {
            return response()->json(['error' => 'No group Found']);
        }
    }
}
