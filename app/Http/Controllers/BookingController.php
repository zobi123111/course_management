<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Resource;
use App\Models\OrganizationUnits;
use App\Models\Group;
use App\Models\User;
use App\Models\OuSetting;
use App\Models\Courses;
use App\Models\CourseLesson;
use App\Models\TrainingEvents;
use App\Models\TrainingEventLessons;
use Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;


class BookingController extends Controller
{
    public function index()
    {
        $organizationUnits = OrganizationUnits::all();
        return view('calender.index', compact('organizationUnits'));
    }


    public function loadEvents(Request $request)
    {
        $mode = $request->mode ?? 'resource';
        $role = auth()->user()->role;

        $is_owner = auth()->user()->is_owner;
        if ($role == 3) {
            $login_id = auth()->user()->id;
            $events = Booking::with(['users', 'resources', 'instructor'])->where('std_id', $login_id)->get();
        } else {
            $events = Booking::with(['users', 'resources', 'instructor'])->get();
        }

        $data = [];

        if ($is_owner == 1) {
            $ou_id = '';
        } else {
            $ou_id = auth()->user()->ou_id;
        }

        foreach ($events as $e) {
            $mailSend = OuSetting::where('organization_id', $ou_id)->select('send_email')->first();
            if ($mode === 'instructor') {
                $resourceId = $e->instructor_id;
                // dd($e);
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
                    $id       = $e->std_id;
                    break;

                case 2: // LESSON
                    $title = $instructorName . ' - ' . $studentName;
                    $color = '#2563EB'; // blue
                    $typeText = 'Lesson';
                    $id       = $e->std_id;
                    break;

                case 3: // STANDBY
                    $title = $studentName . ' (Standby)';
                    $color = '#F59E0B'; // orange
                    $typeText = 'Standby';
                    $id       = $e->std_id;
                    break;

                default:
                    $title = $studentName;
                    $color = '#6B7280';
                    $typeText = 'Unknown';
                    $id       = $e->std_id;
            }

            /* ---------------- Push event ---------------- */
            $start_time = timezone($e->start, $ou_id);

            $start_timezone = $start_time['datetime'];

            $end_time = timezone($e->end, $ou_id);
            $end_timezone = $end_time['datetime'];

            $utc = timezone($e->end, $ou_id);
            $utc_offset = $utc['utc_offset'] ?? 'UTC+00:00';


            $data[] = [
                'id'         => $e->id,
                'resourceId' => (string) $resourceId,
                'start'      => $start_timezone,
                'end'        => $end_timezone,
                'utc_offset' => $utc_offset,
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
                    'encode_std_id'         => encode_id($e->std_id),
                    'resource_id'           => encode_id($e->resource),
                    'ou_id'                 => $e->ou_id,
                    'instructor_id'         => $e->instructor_id,
                    'encode_instructor_id'  => encode_id($e->instructor_id),
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
        $ou_id = auth()->user()->ou_id;
        $role = auth()->user()->role;
        $is_admin = auth()->user()->is_admin;
        $is_owner = auth()->user()->is_owner;

        if ($mode === 'instructor') {
            if ($is_owner == 1) {
                $instructor =  User::where('role', 18)->where('is_activated', 0)->where('status', 1)->get()
                    ->map(fn($u) => [
                        'id' => $u->id,
                        'title' => $u->fname . ' ' . $u->lname
                    ]);
                return $instructor;
            } elseif ($is_admin == 1) {
                $instructor =  User::where('role', 18)->where('ou_id', $ou_id)->where('is_activated', 0)->where('status', 1)->get()
                    ->map(fn($u) => [
                        'id' => $u->id,
                        'title' => $u->fname . ' ' . $u->lname
                    ]);
                return $instructor;
            } else {
                $id = auth()->id();

                $groups = Group::whereJsonContains('user_ids', (string) $id)->get();

                $userIds = $groups->pluck('user_ids')->flatten()->unique()->values();

                $users = User::where('role', 18)->whereIn('id', $userIds)
                    ->where('ou_id', $ou_id)
                    ->where('is_activated', 0)->where('status', 1)
                    ->get()
                    ->map(fn($u) => [
                        'id'    => $u->id,
                        'title' => $u->fname . ' ' . $u->lname
                    ]);
                return $users;
            }
        }

        if ($mode === 'student') {
            if ($is_owner == 1) {
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
            } elseif ($is_admin == 1) {
                $user =  User::where('role', 3)->where('ou_id', $ou_id)
                    ->where(function ($query) {
                        $query->where('is_activated', 1)
                            ->orWhere('status', 1);
                    })
                    ->get()
                    ->map(fn($u) => [
                        'id' => $u->id,
                        'title' => $u->fname . ' ' . $u->lname
                    ]);
                return $user;
            } else {
                $id = auth()->id();


                $users = User::where('id', $id)
                    ->get()
                    ->map(fn($u) => [
                        'id'    => $u->id,
                        'title' => $u->fname . ' ' . $u->lname
                    ]);
                //  dd($users); 

                return $users;
            }
        }

        if ($mode === 'resource') {
            if ($is_owner == 1) {
                return Resource::all()
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'title' => $r->name
                    ]);
            } elseif ($is_admin == 1) {
                $ou_id = auth()->user()->ou_id;



                return Resource::where('ou_id', $ou_id)->get()
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'title' => $r->name
                    ]);
            } else {
                $id = auth()->id();
                $ou_id = auth()->user()->ou_id;
                // Get group IDs where user exists
                $groupIds = Group::whereJsonContains('user_ids', (string) $id)
                    ->pluck('id');

                // Get resources linked to those groups
                return Resource::join('group_resource', 'resources.id', '=', 'group_resource.resource_id')
                    ->whereIn('group_resource.group_id', $groupIds)
                    ->where('resources.ou_id', $ou_id)
                    ->select('resources.id', 'resources.name')
                    ->distinct()
                    ->get()
                    ->map(fn($r) => [
                        'id' => $r->id,
                        'title' => $r->name
                    ]);
            }
        }
    }

    public function store(Request $request)
    {
        $rules = [
            'organizationUnits' => 'required|exists:organization_units,id',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after:start_date',
            'booking_type'      => 'required|in:1,2,3',
            'resource_type'     => 'required|in:1,2,3',
            'resource'          => 'required',
           
        ];

        if ($request->booking_type == 2 || $request->booking_type == 3) {
            $rules['instructor'] = 'required';
            $rules['course'] = 'required';
            $rules['lesson'] = 'required';
            $rules['course_date'] = 'required';
            $rules['rank'] = 'required';
            $rules['lesson_date'] = 'required';
            $rules['start_time'] = 'required';
            $rules['end_time'] = 'required';
            $rules['departure_airfield'] = 'required';
            $rules['destination_airfield'] = 'required';
            $rules['operation'] = 'required';
            $rules['role'] = 'required';
        }

        if (Auth::user()->is_owner == 1) {
            $rules['student'] = 'required|exists:users,id';
        }

        $validated = $request->validate($rules);

        $start = Carbon::parse($request->start_date);
        $end   = Carbon::parse($request->end_date);

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


        $booking                = new Booking();
        $booking->ou_id         = $request->organizationUnits;
        $booking->std_id        = $request->student ?? Auth::user()->id;
        $booking->resource      = $request->resource;
        $booking->start         = $request->start_date;
        $booking->end           = $request->end_date;
        $booking->booking_type  = $request->booking_type;
        $booking->resource_type = $request->resource_type;
        $booking->instructor_id = $request->instructor;
        $booking->status        = "pending";
        $booking->send_email    = $request->boolean('send_email') ? 1 : 0;
        $booking->save();

        

        // $sendemail = organizationUnits::where('id', $request->organizationUnits)->first();
        $checkSend_mail = OuSetting::where('organization_id', $request->organizationUnits)->select('send_email')->first();


        //--------------------------------------------------------------------------------------------------------------------------

        if ($request->booking_type == 2 || $request->booking_type == 3) {
            //  Create tarining event
            $lesson_ids = collect($request->lesson)->pluck('lesson_id')->toArray();
            $existingEvent = TrainingEvents::where('student_id', $request->student)
                ->where('course_id', $request->course)
                ->where('ou_id', auth()->user()->is_owner ? $request->organizationUnits : auth()->user()->ou_id)
                ->whereJsonContains('lesson_ids', $lesson_ids)
                ->get();




            $trainingEvent = TrainingEvents::create([
                'student_id'        => $request->student,
                'course_id'         => $request->course,
                'lesson_ids'        => json_encode([$request->lesson]),
                'event_date'        => $request->course_date,
                // 'opc_validity' => $request->opc_validity_months,
                // 'opc_extend' => $request->opc_extend_eom,
                // 'total_time' => $request->total_time,
                // 'simulator_time' => $request->total_simulator_time ?? '00:00',
                'std_license_number' => $request->licence_number,
                'ou_id' => auth()->user()->is_owner ? $request->organizationUnits : auth()->user()->ou_id,
                // 'entry_source' => $request->entry_source,
                'rank'            => $request->rank ?? null,
                'trainingEvent_type' => 1
            ]);


            $lessonModel = \App\Models\CourseLesson::find($request->lesson);
            $resourceModel = \App\Models\Resource::find($request->resource);

            $lessonType = $lessonModel?->lesson_type;
            $resourceName = $resourceModel?->name;

            $start = $request->start_time ?? null;
            $end = $request->end_time ?? null;
            $creditMinutes = 0;

            //Calculate credit_hours if applicable
            //Apply logic based on lesson type and resource
            if ($lessonType === 'groundschool' && $resourceName === 'Homestudy') {
                // Fixed 8 hours for Homestudy
                $creditMinutes = 480;
                $start = '00:00';
                $end = '08:00';
            } elseif ($start && $end) {
                // For all other lessons (including simulator and classroom)
                try {
                    $startTime = \Carbon\Carbon::createFromFormat('H:i', $start);
                    $endTime = \Carbon\Carbon::createFromFormat('H:i', $end);

                    if ($endTime->lessThan($startTime)) {
                        $endTime->addDay(); // Handles overnight sessions
                    }

                    $creditMinutes = $startTime->diffInMinutes($endTime);
                } catch (\Exception $e) {
                    $creditMinutes = 0; // fallback in case of invalid time format
                }
            }

            TrainingEventLessons::create([
                'training_event_id'  => $trainingEvent->id,
                'lesson_id'          => $request->lesson,
                'instructor_id'      => $request->instructor,
                'resource_id'        => $request->resource,
                'lesson_date'        => $request->lesson_date,
                'start_time'         => $start,
                'end_time'           => $end,
                'departure_airfield' => ($lessonType === 'groundschool' && in_array($resourceName, ['Classroom', 'Homestudy'])) ? null : strtoupper($request->departure_airfield),
                'destination_airfield' => ($lessonType === 'groundschool' && in_array($resourceName, ['Classroom', 'Homestudy'])) ? null : strtoupper($request->destination_airfield,),
                'instructor_license_number' => $request->licence_number ?? null,
                'hours_credited'    => gmdate("H:i", $creditMinutes * 60),
                'operation1'        => $request->Operation ?? null,
                'role1'             => $request->role ?? null,
                'operation2'        =>  null,
                'role2'             =>  null,

            ]);

         
          return response()->json(['message' => 'Training event created successfully']);
          

        }
        //--------------------------------------------------------------------------------------------------------------------------------------
       return response()->json(['success' => true,'message' => 'Booking created successfully.']);
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
        $ou_id = auth()->user()->ou_id;
        $role = auth()->user()->role;
        $is_admin = auth()->user()->is_admin;
        $is_owner = auth()->user()->is_owner;
        $students = User::where('ou_id', $request->ou_id)->get();
        $id = auth()->id();

        $ato_num = OrganizationUnits::where('id', $request->ou_id)->get();

        if ($is_owner == 1) {
            $instructors = User::where('ou_id', $request->ou_id)->where('role', 18)->get(['id', 'fname', 'lname']);
            $org_resource = Resource::where('ou_id', $request->ou_id)->get();
            $courses = Courses::where('ou_id', $request->ou_id)->get();
        } elseif ($is_admin == 1) {
            $org_resource = Resource::where('ou_id', $request->ou_id)->get();
            $instructors = User::where('ou_id', $request->ou_id)->where('role', 18)->where('is_activated', 0)->where('status', 1)->get(['id', 'fname', 'lname']);
        } else {
            $org_resource = Resource::where('ou_id', $request->ou_id)->get();

            $id = auth()->id();
            $ou_id = auth()->user()->ou_id;
            $groupIds = Group::whereJsonContains('user_ids', (string) $id)->pluck('id');

            // Get resources linked to those groups
            $org_resource =  Resource::join('group_resource', 'resources.id', '=', 'group_resource.resource_id')
                ->whereIn('group_resource.group_id', $groupIds)
                ->where('resources.ou_id', $ou_id)
                ->select('resources.id', 'resources.name')
                ->distinct()
                ->get();

            $groups = Group::whereJsonContains('user_ids', (string) $id)->get();
            $userIds = $groups->pluck('user_ids')->flatten()->unique()->values();
            $instructors = User::where('role', 18)->where('ou_id', $request->ou_id)
                ->where('is_activated', 0)->where('status', 1)
                ->whereIn('id', $userIds)
                ->get(['id', 'fname', 'lname']);
        }

        return response()->json(['org_resource' => $org_resource, 'ato_num' => $ato_num, 'students' => $students, 'instructors' => $instructors, 'courses' => $courses]);
    }

    public function lessons(Request $request)
    {
        $course_id = $request->course_id;
        $lessons = CourseLesson::where('course_id', $course_id)->get();
        $course = Courses::with(['courseLessons', 'resources'])->find($course_id);
        $enable_mp_lifus  = $course->enable_mp_lifus;


        return response()->json(['lessons' => $lessons, 'enable_mp_lifus' =>  $enable_mp_lifus, 'resources' => $course->resources]);
    }

    public function edit_booking(Request $request)
    {
        $booking_id = $request->id;
        $booking    =  Booking::where('id', $booking_id)->get();
        return response()->json(['success' => true, 'response' => $booking]);
    }
}
