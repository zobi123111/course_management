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
        $students = collect(); 

        foreach ($groups as $val) {
            // user_ids is an array like ["100","114","154"]
            if(!empty($val->user_ids) && is_array($val->user_ids)) {
                $groupStudents = User::whereIn('id', $val->user_ids)->where('is_activated', 1)->where('status', 1)->get();
                $students = $students->merge($groupStudents);
            }
        }

        // remove duplicate users (if any)
        $students = $students->unique('id')->values();
        if ($student == 3) {
            $ou_id = auth()->user()->ou_id;
          //  $resources  = Resource::where('ou_id', $ou_id)->get();
        } else {
            $bookedResourceIds = Booking::pluck('resource')->unique()->toArray();
            $resources = Resource::whereIn('id', $bookedResourceIds)->get();
           //  $resources = Resource::all();
        }
        return view('calender.index', compact('resources', 'organizationUnits', 'groups', 'students'));
    }


    public function loadEvents(Request $request)
    {
        $mode = $request->mode ?? 'resource';
        $events = Booking::with(['users', 'resources', 'instructor'])
                // ->whereNotIn('status', ['cancelled', 'rejected'])
                 ->get();
        $data = [];
        foreach ($events as $e) { 
            $mailSend = OuSetting::where('organization_id', $e->ou_id)->select('send_email')->first();
            if ($mode === 'instructor') {
                $resourceId = $e->instructor_id;
            } elseif ($mode === 'student') {
                $resourceId = $e->std_id;
            } else {
                $resourceId = $e->resource;
            }
            // Prevent invisible events
            if (!$resourceId) {
                continue;
            }

            /* ---------------- Names ---------------- */
            $studentName = $e->users->fname . ' ' . $e->users->lname;
            $instructorName = $e->instructor ? $e->instructor->fname . ' ' . $e->instructor->lname : null;

            /* ---------------- Booking type handling ---------------- */
            switch ((int) $e->booking_type) {
                case 1: 
                    $title = $studentName . ' (Solo)';
                    $color = '#9CA3AF'; // grey
                    $typeText = 'Solo';
                    break;

                case 2: // LESSON
                    $title = $instructorName . ' - ' . $studentName;
                    $color = '#2563EB'; // blue
                    $typeText = 'Lesson';
                    break;

                case 3: // STANDBY
                    $title = $studentName . ' (Standby)';
                    $color = '#F59E0B'; // orange
                    $typeText = 'Standby';
                    break;

                default:
                    $title = $studentName;
                    $color = '#6B7280';
                    $typeText = 'Unknown';
            }

            /* ---------------- Push event ---------------- */
            $data[] = [
                'id'         => $e->id,
                'resourceId' => (string) $resourceId,
                'start'      => timezone($e->start, $e->ou_id),
                'end'        => timezone($e->end, $e->ou_id),
                'title'      => $title,

                // Styling
                'backgroundColor' => $color,
                'borderColor'     => $color,

                'extendedProps' => [
                    'student'               => $studentName,
                    'instructor'            => $instructorName,
                    'resource'              => $e->resources->name ?? null,
                    'booking_type'          => $typeText,
                    'booking_type_numValue' => $e->booking_type,
                    'registration'          => $e->resources->registration,
                    'send_mail'             => $mailSend->send_email ?? '',
                    'std_id'                => $e->std_id,
                    'resource_id'           => $e->resource,
                    'ou_id'                 => $e->ou_id,
                    'instructor_id'         => $e->instructor_id,
                    'id'                    => $e->id,
                    'start'                 => $e->start,
                    'end'                   => $e->end,
                    'status'                => $e->status,
                ]
            ];
        }

        return response()->json($data);
    }

  public function loadResources(Request $request)
    {
        $mode = $request->mode ?? 'resource';

        if ($mode === 'instructor') {
            return User::where('role', 18)->where('is_activated', 0)->where('status', 1)->get()
                ->map(fn($u) => [
                    'id' => $u->id,
                    'title' => $u->fname.' '.$u->lname
                ]);
        }

        if ($mode === 'student') {
          return User::where('role', 3)
                        ->where(function ($query) {
                            $query->where('is_activated', 1)
                                ->orWhere('status', 1);
                        })
                        ->get()
                        ->map(fn($u) => [
                            'id' => $u->id,
                            'title' => $u->fname . ' ' . $u->lname
                        ]);

        }

        // DEFAULT: RESOURCE
        return Resource::all()
            ->map(fn($r) => [
                'id' => $r->id,
                'title' => $r->name
            ]);
    }


    public function store(Request $request)
    {
      //  dd($request->all());
         $validated = $request->validate([
                'organizationUnits' => 'required|exists:organization_units,id',
               // 'resource_id'       => 'required|exists:resources,id',
                'start_date'        => 'required|date',
                'end_date'          => 'required|date|after:start_date',
                'booking_type'      => 'required|in:1,2,3',
                'resource_type'     => 'required|in:1,2,3',
                'student'           => 'required|nullable|exists:users,id',
                'resource'          => 'required',
                // Instructor required only for Lesson (2)
                'instructor' => 'required_if:booking_type,2|nullable|exists:users,id',
            ]);
              // ✅ Parse dates
                $start = Carbon::parse($request->start_date);
                $end   = Carbon::parse($request->end_date);
               // dd($request->resource);

                // ✅ Resource + Time Conflict Check
                // $resourceConflict = Booking::where('resource', $request->resource)
                //     ->where(function ($q) use ($start, $end) {
                //         $q->where('start', '<', $end)
                //         ->where('end',   '>', $start);
                //     })
                //     ->exists();
                $resourceConflict = Booking::where('resource', $request->resource)
                                    ->whereDate('start', $start->toDateString())
                                    ->where(function ($q) use ($start, $end) {
                                        $q->where('start', '<', $end)
                                        ->where('end', '>', $start);
                                    })
                                    ->exists();
              

                if ($resourceConflict) {
                    return response()->json([
                        'errors' => [
                            'resource' => ['This resource is already booked for the selected time slot.']
                        ]
                    ], 422);
                } 

        
        $booking = new Booking();
        $booking->ou_id = $request->organizationUnits;
        $booking->std_id = $request->student ?? Auth::user()->id;
        $booking->resource = $request->resource;
        $booking->start = $request->start_date;
        $booking->end = $request->end_date;
        $booking->booking_type = $request->booking_type;
        $booking->resource_type = $request->resource_type;
        $booking->instructor_id = $request->instructor;
        $booking->status = "pending";
        $booking->send_email = $request->boolean('send_email') ? 1 : 0;
        $booking->save();

       // $sendemail = organizationUnits::where('id', $request->organizationUnits)->first();
        $checkSend_mail = OuSetting::where('organization_id', $request->organizationUnits)->select('send_email')->first();
        // if ($checkSend_mail->send_email == 1) { 
 
        //     $studentEmail = User::find($booking->std_id)->email;
        //     $instructor = User::find($booking->instructor_id)->email;
        //     $ouEmails = User::where('ou_id', $booking->ou_id)->where('is_admin', 1)->pluck('email')->toArray();

        //     $allEmails = array_merge([$studentEmail], $ouEmails, [$instructor]);

        //     Mail::send('emailtemplates.create_booking_email', ['booking' => $booking], function ($message) use ($allEmails) {
        //         $message->to($allEmails)
        //                 ->subject('New Booking Created');
        //     });
        // }
        // if ($checkSend_mail->send_email == 1) {
                // $studentEmail = User::where('id', $booking->std_id)->value('email');
                // $instructorEmail = User::where('id', $booking->instructor_id)->value('email');

                // $ouEmails = User::where('ou_id', $booking->ou_id)
                //                 ->where('is_admin', 1)
                //                 ->pluck('email')
                //                 ->toArray();

                // $allEmails = array_filter(array_merge(
                //     [$studentEmail],
                //     $ouEmails,
                //     [$instructorEmail]
                // ));

                // Mail::send('emailtemplates.create_booking_email',['booking' => $booking],function ($message) use ($allEmails) {
                //             $message->to($allEmails)
                //             ->subject('New Booking Created');
                //     }
                // );
           // }
             return response()->json([ 'message' => 'Booking created successfully'], 201);
    }

    public function update(Request $request)
    {
        // dd($request->all());
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
        // Booking::where('id', '!=', $booking->id)
        //     ->where('resource', $booking->resource)
        //     ->where('resource_type', $booking->resource_type)
        //     ->where(function ($q) use ($booking) {
        //         $q->where('start', '=', $booking->end)
        //         ->where('end', '=', $booking->start);
        //     })
        //     ->update(['status' => 'rejected']);
         

        $sendemail = organizationUnits::where('id', $request->organizationUnits)->first();

        // if ($sendemail->send_email == 1) {

        //     $studentEmail = User::find($booking->std_id)->email;
        //     $instructor = User::find($booking->instructor_id)->email;
        //     $ouEmails = User::where('ou_id', $booking->ou_id)->where('is_admin', 1)->pluck('email')->toArray();

        //     $allEmails = array_merge([$studentEmail], $ouEmails, [$instructor]);

        //     Mail::send('emailtemplates.approved_booking_email',['booking' => $booking],function ($message) use ($allEmails) {
        //             $message->to($allEmails)->subject('Booking Approved');
        //         });
        // }

        return response()->json(['success' => true]);
    }


    public function reject(Request $request)
    {
        $booking = Booking::find($request->id);
        $booking->status = "rejected";
        $booking->save();


        $sendemail = organizationUnits::where('id', $request->organizationUnits)->first();
        
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

    public function delete(Request $request)
    {
        $booking = Booking::find($request->id);
        // $booking->status = 'cancelled';
        // $booking->save();
         Booking::where('id', $request->id)->delete();



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
        // $org_group    = Group::where('ou_id', $request->ou_id)->get();
        // $students = collect();
        $students = User::where('ou_id', $request->ou_id)->get();

        // foreach ($org_group as $val) {
        //     // user_ids is an array like ["100","114","154"]
        //     if (!empty($val->user_ids) && is_array($val->user_ids)) {
        //         $groupStudents = User::whereIn('id', $val->user_ids)->get();
        //         $students = $students->merge($groupStudents);
        //     }
        // }

        $instructors = User::where('ou_id', $request->ou_id)->where('role', 18)->get(['id', 'fname', 'lname']);

        //  Remove duplicate users (if any)
       // $students = $students->unique('id')->values(); 
        $bookedResourceIds = Booking::where('status','approved')->pluck('resource')->unique();
        //$org_resource = Resource::whereNotIn('id', $bookedResourceIds)->where('ou_id', $request->ou_id)->get();
        $org_resource = Resource::where('ou_id', $request->ou_id)->get();

        $ato_num = OrganizationUnits::where('id', $request->ou_id)->get();
    
            return response()->json(['org_resource' => $org_resource, 'ato_num' => $ato_num, 'students' => $students, 'instructors' => $instructors]);
       
    }

    public function edit_booking(Request $request)
    {
        $booking_id = $request->id;
        $booking    =  Booking::where('id', $booking_id)->get();
        return response()->json(['success' => true, 'response'=> $booking]);

    }
}
