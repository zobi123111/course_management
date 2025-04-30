<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Courses;
use App\Models\CourseLesson;
use App\Models\User;
use App\Models\OrganizationUnits;
use App\Models\TrainingEvents;
use App\Models\TrainingEventLessons;
use App\Models\TaskGrading;
use App\Models\Resource;
use App\Models\CompetencyGrading;
use App\Models\OverallAssessment;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;



class TrainingEventsController extends Controller
{ 
    public function index()
    {
        $currentUser = auth()->user();
        $organizationUnits = OrganizationUnits::all();
        // $resources = Resource::all();
        // Define relationships for trainingEvents query
        $trainingEventsRelations = [
            'course:id,course_name',
            'instructor:id,fname,lname',
            'student:id,fname,lname',
            'resource:id,name',
            'firstLesson.instructor:id,fname,lname',
            'firstLesson.resource:id,name',
        ];

        if ($currentUser->is_owner == 1 && empty($currentUser->ou_id)) {
            // Super Admin: Get all data
            $resources = Resource::all();
            $courses = Courses::all();
            $groups = Group::all();
            $instructors = User::whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Instructor%');
            })->with('roles')->get();
            $students = User::whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Student%');
            })->with('roles')->get();
            $trainingEvents = TrainingEvents::with($trainingEventsRelations)->get();
        } elseif (checkAllowedModule('training', 'training.index')->isNotEmpty() && empty($currentUser->is_admin)) {
            // Regular User: Get data within their organizational unit
            $resources = Resource::where('ou_id', $currentUser->ou_id)->get();
            $courses = Courses::where('ou_id', $currentUser->ou_id)->get();
            $groups = Group::where('ou_id', $currentUser->ou_id)->get();

            $instructors = User::where('ou_id', $currentUser->ou_id)
                ->where(function ($query) {
                    $query->whereNull('is_admin')->orWhere('is_admin', false);
                })
                ->whereHas('roles', function ($query) {
                    $query->where('role_name', 'like', '%Instructor%');
                })->with('roles')->get();

            $students = User::where('ou_id', $currentUser->ou_id)
                ->where(function ($query) {
                    $query->whereNull('is_admin')->orWhere('is_admin', false);
                })
                ->whereHas('roles', function ($query) {
                    $query->where('role_name', 'like', '%Student%');
                })->with('roles')->get();

            // Determine if user is an Instructor or Student and filter events accordingly
            $trainingEventsQuery = TrainingEvents::where('ou_id', $currentUser->ou_id)->with($trainingEventsRelations);

            if (hasUserRole($currentUser, 'Instructor')) {
                // Get training event IDs where the current instructor is assigned to at least one lesson
                $eventIds = TrainingEventLessons::where('instructor_id', $currentUser->id)
                    ->pluck('training_event_id')
                    ->unique();
            
                $trainingEvents = $trainingEventsQuery->whereIn('id', $eventIds)->get();
            } else {
                $trainingEvents = $trainingEventsQuery->where('student_id', $currentUser->id)->get();
            }
        } else {
            // Default Case: Users with limited access within their organization
            $resources = Resource::where('ou_id', $currentUser->ou_id)->get();
            $courses = Courses::where('ou_id', $currentUser->ou_id)->get();
            $groups = Group::where('ou_id', $currentUser->ou_id)->get();

            $instructors = User::where('ou_id', $currentUser->ou_id)
                ->where(function ($query) {
                    $query->whereNull('is_admin')->orWhere('is_admin', false);
                })
                ->whereHas('roles', function ($query) {
                    $query->where('role_name', 'like', '%Instructor%');
                })->with('roles')->get();

            $students = User::where('ou_id', $currentUser->ou_id)
                ->where(function ($query) {
                    $query->whereNull('is_admin')->orWhere('is_admin', false);
                })
                ->whereHas('roles', function ($query) {
                    $query->where('role_name', 'like', '%Student%');
                })->with('roles')->get();

            $trainingEvents = TrainingEvents::where('ou_id', $currentUser->ou_id)->with($trainingEventsRelations)->get();
        }

        return view('trainings.index', compact('groups', 'courses', 'instructors', 'organizationUnits', 'trainingEvents', 'resources', 'students'));
    }

    public function getOrgStudentsInstructorsResources(Request $request,$ou_id)
    {
        // $ou_id = ($ou_id)? $ou_id: auth()->user()->ou_id;
        $students = User::where('ou_id', $ou_id)
        ->where(function ($query) {
            $query->whereNull('is_admin')->orWhere('is_admin', false);
        })
        ->whereHas('roles', function ($query) {
            $query->where('role_name', 'like', '%Student%');
        })->with('roles')->get();

        $instructors = User::where('ou_id', $ou_id)
                ->where(function ($query) {
                    $query->whereNull('is_admin')->orWhere('is_admin', false);
                })
                ->whereHas('roles', function ($query) {
                    $query->where('role_name', 'like', '%Instructor%');
                })->with('roles')->get();

        $resources = Resource::where('ou_id', $ou_id)->get();

        if ($students) {
            // return response()->json(['success' => true, 'students' => $students, 'instructors' => $instructors, 'resources' => $resources]);
            return response()->json(['success' => true, 'students' => $students, 'instructors' => $instructors, 'resources' => $resources]);
        } else {
            return response()->json(['success' => false]);
        }
    }
    public function getStudentLicenseNumberAndCourses(Request $request,$user_id,$ou_id)
    {
        $user = User::find($user_id);
        $groups = Group::where('ou_id', $ou_id)
        ->whereJsonContains('user_ids', strval($user_id)) // Ensure user_id is a string
        ->pluck('id'); // Get only the group IDs
        
        $courses = Courses::with('groups') // Load only the groups relationship
        ->whereHas('groups', function ($query) use ($groups) {
            $query->whereIn('groups.id', $groups);
        })
        ->get();        
        if ($user) {
            return response()->json(['success' => true, 'licence_number' => $user->licence, 'courses' => $courses]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function getInstructorLicenseNumber(Request $request, $instructor_id)
    {
        $instructor = User::find($instructor_id);      
    
        if ($instructor) {
            return response()->json([
                'success' => true,
                'instructor_licence_number' => $instructor->licence
            ]);
        }
    
        return response()->json(['success' => false], 404);
    }
    

    public function getCourseLessons(Request $request)
    {
        $lessons = CourseLesson::where('course_id', $request->course_id)->get();
        if ($lessons) {
            return response()->json(['success' => true, 'lessons' => $lessons]);
        } else {
            return response()->json(['success' => false]);
        }
    }


    public function createTrainingEvent(Request $request)
    {
        // Convert total time
        // $request->merge([
        //     'total_time' => date('H:i', strtotime($request->total_time)),
        // ]);
    
        // Validate base fields
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'lesson_ids' => 'required|array',
            'lesson_ids.*' => 'exists:course_lessons,id',
            // 'event_date' => 'required|date_format:Y-m-d',
            // 'total_time' => 'required|date_format:H:i',
            'std_license_number' => 'nullable|string',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->is_owner == 1 && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
            'lesson_data' => 'required|array',
            'lesson_data.*.lesson_id' => 'required|exists:course_lessons,id',
            'lesson_data.*.instructor_id' => 'required|exists:users,id',
            'lesson_data.*.resource_id' => 'required|exists:resources,id',
            'lesson_data.*.lesson_date' => 'required|date_format:Y-m-d',
            'lesson_data.*.start_time' => 'required|date_format:H:i',
            'lesson_data.*.end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    preg_match('/lesson_data\.(\d+)\.end_time/', $attribute, $matches);
                    if (isset($matches[1])) {
                        $index = $matches[1];
                        $startTime = $request->input("lesson_data.$index.start_time");
    
                        if (strtotime($value) <= strtotime($startTime)) {
                            $fail("End time must be after start time.");
                        }
                    }
                },
            ],
            'lesson_data.*.departure_airfield' => 'required|string|size:4',
            'lesson_data.*.destination_airfield' => 'required|string|size:4',
            'lesson_data.*.instructor_license_number' => 'nullable|string',
        ], [], [
            'lesson_data.*.instructor_id' => 'instructor',
            'lesson_data.*.resource_id' => 'resource',
            'lesson_data.*.lesson_date' => 'lesson date',
            'lesson_data.*.start_time' => 'start time',
            'lesson_data.*.end_time' => 'end time',
            'lesson_data.*.departure_airfield' => 'departure airfield',
            'lesson_data.*.destination_airfield' => 'destination airfield',
            'lesson_data.*.instructor_license_number' => 'instructor license number',
        ]);
    
        // Create main training event
        $trainingEvent = TrainingEvents::create([
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
            'lesson_ids' => json_encode($request->lesson_ids),  
            // 'event_date' => $request->event_date,
            // 'total_time' => $request->total_time,
            'std_license_number' => $request->std_license_number,
            'ou_id' => auth()->user()->is_owner ? $request->ou_id : auth()->user()->ou_id,
        ]);
    
        // Insert lesson details into TrainingEventLessons
        foreach ($request->lesson_data as $lesson) {
            TrainingEventLessons::create([
                'training_event_id' => $trainingEvent->id,
                'lesson_id' => $lesson['lesson_id'],
                'instructor_id' => $lesson['instructor_id'],
                'resource_id' => $lesson['resource_id'],
                'lesson_date' => $lesson['lesson_date'],
                'start_time' => $lesson['start_time'],
                'end_time' => $lesson['end_time'],
                'departure_airfield' => strtoupper($lesson['departure_airfield']),
                'destination_airfield' => strtoupper($lesson['destination_airfield']),
                'instructor_license_number' => $lesson['instructor_license_number'] ?? null,
            ]);
        }
    
        Session::flash('message', 'Training event created successfully.');
    
        return response()->json([
            'success' => true,
            'message' => 'Training event created successfully',
            'trainingEvent' => $trainingEvent
        ], 201);
    }
    
    

    

    public function getTrainingEvent(Request $request)
    {
        $trainingEvent = TrainingEvents::with('eventLessons.lesson')->findOrFail(decode_id($request->eventId));
        if($trainingEvent)
        {
            return response()->json(['success'=> true,'trainingEvent'=> $trainingEvent]);
        }else{
            return response()->json(['success'=> false, 'message' => 'Training event Not found']);
        }
    }

    public function updateTrainingEvent(Request $request)
    {
        // Convert nested lesson times to H:i format
        $lessonData = $request->input('lesson_data', []);
        foreach ($lessonData as $key => $lesson) {
            if (!empty($lesson['start_time'])) {
                $lessonData[$key]['start_time'] = date('H:i', strtotime($lesson['start_time']));
            }
            if (!empty($lesson['end_time'])) {
                $lessonData[$key]['end_time'] = date('H:i', strtotime($lesson['end_time']));
            }
        }
        $request->merge(['lesson_data' => $lessonData]);
    
        // Validate request
        $request->validate([
            'event_id' => 'required|exists:training_events,id',
            'student_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'lesson_data' => 'required|array',
    
            'lesson_data.*.lesson_id' => 'required|exists:course_lessons,id',
            'lesson_data.*.instructor_id' => 'required|exists:users,id',
            'lesson_data.*.resource_id' => 'required|exists:resources,id',
            'lesson_data.*.lesson_date' => 'required|date_format:Y-m-d',
            'lesson_data.*.start_time' => 'required|date_format:H:i',
            'lesson_data.*.end_time' => [
                'required',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($request) {
                    preg_match('/lesson_data\.(\d+)\.end_time/', $attribute, $matches);
                    if (isset($matches[1])) {
                        $index = $matches[1];
                        $startTime = $request->input("lesson_data.$index.start_time");
                        if (strtotime($value) <= strtotime($startTime)) {
                            $fail("End time must be after start time.");
                        }
                    }
                },
            ],
            'lesson_data.*.departure_airfield' => 'required|string|size:4',
            'lesson_data.*.destination_airfield' => 'required|string|size:4',
            'lesson_data.*.instructor_license_number' => 'nullable|string',
    
            'std_license_number' => 'nullable|string',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->is_owner == 1 && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
        ], [], [
            'lesson_data.*.instructor_id' => 'instructor',
            'lesson_data.*.resource_id' => 'resource',
            'lesson_data.*.lesson_date' => 'lesson date',
            'lesson_data.*.start_time' => 'start time',
            'lesson_data.*.end_time' => 'end time',
            'lesson_data.*.departure_airfield' => 'departure airfield',
            'lesson_data.*.destination_airfield' => 'destination airfield',
        ]);
    
        // Update Training Event
        $trainingEvent = TrainingEvents::findOrFail($request->event_id);
        $trainingEvent->update([
            'ou_id' => auth()->user()->is_owner ? $request->ou_id : auth()->user()->ou_id,
            'course_id' => $request->course_id,
            'student_id' => $request->student_id,
            'lesson_ids' => json_encode(array_column($lessonData, 'lesson_id')),
            'std_license_number' => $request->std_license_number ?? null,
        ]);
    
        // Get all lesson_ids from the request
        $incomingLessonIds = collect($lessonData)->pluck('lesson_id')->toArray();
    
        // Delete removed lessons
        TrainingEventLessons::where('training_event_id', $trainingEvent->id)
            ->whereNotIn('lesson_id', $incomingLessonIds)
            ->delete();
    
        // Update or create each lesson's detail
        foreach ($lessonData as $data) {
            TrainingEventLessons::updateOrCreate(
                [
                    'training_event_id' => $trainingEvent->id,
                    'lesson_id' => $data['lesson_id'],
                ],
                [
                    'instructor_id' => $data['instructor_id'],
                    'resource_id' => $data['resource_id'],
                    'lesson_date' => $data['lesson_date'],
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'departure_airfield' => strtoupper($data['departure_airfield']),
                    'destination_airfield' => strtoupper($data['destination_airfield']),
                    'instructor_license_number' => $data['instructor_license_number'] ?? null,
                ]
            );
        }
    
        Session::flash('message', 'Training event updated successfully.');
    
        return response()->json([
            'success' => true,
            'message' => 'Training event updated successfully',
            'trainingEvent' => $trainingEvent->load('eventLessons'),
        ]);
    }
    
    
    
    
    public function deleteTrainingEvent(Request $request)
    {
        $trainingEvents = TrainingEvents::findOrFail(decode_id($request->event_id));
        if ($trainingEvents) {
            $trainingEvents->delete();
            return redirect()->route('training.index')->with('message', 'Training event deleted successfully');
        }        
    }

    // public function getOrgGroupsAndInstructors(Request $request)
    // {
    //     $orgUnitGroups = Group::where('ou_id', $request->ou_id)
    //            ->get();
    //     $ouInstructors = User::where('ou_id', $request->ou_id)
    //     ->whereHas('roles', function ($query) {
    //         $query->where('role_name', 'like', '%Instructor%');
    //     })
    //     ->with('roles')
    //     ->get();
           
    //     if($orgUnitGroups){
    //         return response()->json(['orgUnitGroups' => $orgUnitGroups, 'ouInstructors'=> $ouInstructors]);
    //     }else{
    //         return response()->json(['error'=> 'Org Unit Groups not found.']);
    //     }
    // }

    // public function showTrainingEvent(Request $request, $event_id)
    // {
      
    //     $trainingEvent = TrainingEvents::with(['course:id,course_name', 'group:id,name,user_ids', 'instructor:id,fname,lname', 'student:id,fname,lname', 'resource:id,name'])
    //     ->find(decode_id($event_id));
    //     //  dd($trainingEvent);
    //     // Fetch the single student
    //     $student = $trainingEvent->student;
    //     // Ensure course exists before accessing course_id
    //     $courseLessons = $trainingEvent->course 
    //     ? CourseLesson::with('sublessons')
    //     ->where('course_id', $trainingEvent->course->id)
    //     ->get() : collect();
    
    //     // Fetch the overall assessment for the single student
    //     $overallAssessments = OverallAssessment::where('event_id', $trainingEvent->id)
    //     ->where('user_id', $student->id ?? null)
    //     ->first(); // Fetch only one record

    //     return view('trainings.show', compact('trainingEvent', 'student', 'courseLessons', 'overallAssessments'));
    // }

    public function showTrainingEvent(Request $request, $event_id)
    {
        $currentUser = auth()->user();
        $trainingEvent = TrainingEvents::with([
            'course:id,course_name,course_type',
            'course.documents', // Eager load course documents
            'group:id,name,user_ids',
            'instructor:id,fname,lname',
            'student:id,fname,lname',
            'resource:id,name',
            'eventLessons.lesson:id,lesson_title',
            'eventLessons.instructor:id,fname,lname',
            'eventLessons.resource:id,name',    
            'trainingFeedbacks.question' // Eager load the question relationship
        ])->find(decode_id($event_id));
    
        if (!$trainingEvent) {
            return abort(404, 'Training Event not found');
        }
    
        // Filter lessons based on role
        if (hasUserRole($currentUser, 'Instructor') && empty($currentUser->is_admin)) {
            // Only show lessons assigned to this instructor
            $eventLessons = $trainingEvent->eventLessons->filter(function ($lesson) use ($currentUser) {
                return $lesson->instructor_id == $currentUser->id;
            })->values();
        } else {
            // Admins and others with access can view all lessons
            $eventLessons = $trainingEvent->eventLessons;
        }
        $student = $trainingEvent->student;
        $lessonIds = $eventLessons->pluck('lesson_id')->filter()->unique();

        // Get task gradings (sublesson grades and comments)
        $taskGrades = TaskGrading::where('user_id', $student->id)
            ->whereIn('lesson_id', $eventLessons->pluck('lesson_id')->filter()->unique())
            ->get()
            ->keyBy(function ($item) {
                return $item->sub_lesson_id;
            });
    
        // Get competency grades (competency area grades and comments)
        $competencyGrades = CompetencyGrading::where('user_id', $student->id)
            ->whereIn('lesson_id', $eventLessons->pluck('lesson_id')->filter()->unique())
            ->get()
            ->groupBy('lesson_id');

        // Optional: Also pass overall assessments if you need them
        $overallAssessments = OverallAssessment::where('event_id', $trainingEvent->id)
            ->where('user_id', $student->id ?? null)
            ->first();
    
        $lessonIds = json_decode($trainingEvent->lesson_ids, true) ?? [];
        $selectedLessons = !empty($lessonIds) 
            ? CourseLesson::with('sublessons')->whereIn('id', $lessonIds)->get() 
            : collect();

        // Retrieve feedback data
        $trainingFeedbacks = $trainingEvent->trainingFeedbacks;    
        return view('trainings.show', compact(
            'trainingEvent', 
            'student', 
            'overallAssessments', 
            'selectedLessons',
            'eventLessons',
            'taskGrades',
            'competencyGrades',
            'trainingFeedbacks'
        ));
    }
    


    // public function showTrainingEvent(Request $request, $event_id)
    // {
      
    //     $trainingEvent = TrainingEvents::with(['course:id,course_name', 'group:id,name,user_ids', 'instructor:id,fname,lname', 'student:id,fname,lname', 'resource:id,name'])
    //     ->find(decode_id($event_id));
    //   //  dd($trainingEvent);
    //     if ($trainingEvent && !empty($trainingEvent->group->user_ids)) {
    //         // Ensure user_ids is an array (convert from JSON if needed)
    //         $userIds = is_string($trainingEvent->group->user_ids) 
    //         ? json_decode($trainingEvent->group->user_ids, true) 
    //         : $trainingEvent->group->user_ids;
    //         // Fetch users only if decoding is successful
    //         $groupUsers = is_array($userIds) 
    //         ? User::whereIn('id', $userIds)
    //             ->with([
    //                 'taskGrades' => function ($query) use ($event_id) {
    //                     $query->where('event_id', decode_id($event_id));
    //                 },
    //                 'competencyGrades' => function ($query) use ($event_id) {
    //                     $query->where('event_id', decode_id($event_id));
    //                 }
    //             ])
    //             ->get() 
    //         : collect();
    //     } else {
    //         $groupUsers = collect(); // Return empty collection if no users found
    //     }
    //     // Ensure course exists before accessing course_id
    //     $courseLessons = $trainingEvent->course ? CourseLesson::with('sublessons')->where('course_id', $trainingEvent->course->id)->whereHas('sublessons')->get() : collect(); // Only fetch lessons that have sublessons

    //     // Fetch overall assessments for users in this event
    //     $overallAssessments = OverallAssessment::where('event_id', $trainingEvent->id)
    //     ->whereIn('user_id', $groupUsers->pluck('id'))
    //     ->pluck('result', 'user_id'); // Get result indexed by user_id

    //     $overallRemarks = OverallAssessment::where('event_id', $trainingEvent->id)
    //     ->whereIn('user_id', $groupUsers->pluck('id'))
    //     ->pluck('remarks', 'user_id'); // Get remark indexed by user_id
    //     // dd($courseLessons);;
    //     return view('trainings.show', compact('trainingEvent', 'groupUsers', 'courseLessons', 'overallAssessments', 'overallRemarks'));
    // }

    // public function createGrading(Request $request)
    // {
    //     // Validate the request
    //     $request->validate([
    //         'event_id' => 'required|integer|exists:training_events,id',
    //         'task_grade' => 'nullable|array',
    //         'task_grade.*.*.*' => ['required', 'string', Rule::in(['N/A', 'Further training required', 'Competent', '1', '2', '3', '4', '5'])], 
    //         'comp_grade' => 'nullable|array',
    //         'comp_grade.*.*' => 'required|integer|min:1|max:5',
    //     ]);

    //     DB::beginTransaction(); 

    //     try {
    //         $event_id = $request->event_id;

    //         // Store or Update Task Grading
    //         if ($request->has('task_grade')) {
    //             foreach ($request->task_grade as $lesson_id => $subLessons) {
    //                 foreach ($subLessons as $sub_lesson_id => $users) {
    //                     foreach ($users as $user_id => $task_grade) {
    //                         TaskGrading::updateOrCreate(
    //                             [
    //                                 'event_id' => $event_id,
    //                                 'lesson_id' => $lesson_id,
    //                                 'sub_lesson_id' => $sub_lesson_id,
    //                                 'user_id' => $user_id,
    //                             ],
    //                             [
    //                                 'task_grade' => $task_grade,
    //                                 'created_by' => auth()->user()->id, // Moved to update fields
    //                             ]
    //                         );
    //                     }
    //                 }
    //             }
    //         }

    //         // Store or Update Competency Grading
    //         if ($request->has('comp_grade')) {
    //             foreach ($request->comp_grade as $lesson_id => $users) {
    //                 foreach ($users as $user_id => $competency_grade) {
    //                     CompetencyGrading::updateOrCreate(
    //                         [
    //                             'event_id' => $event_id,
    //                             'lesson_id' => $lesson_id,
    //                             'user_id' => $user_id,
    //                         ],
    //                         [
    //                             'competency_grade' => $competency_grade,
    //                             'created_by'=> auth()->user()->id,
    //                         ]
    //                     );
    //                 }
    //             }
    //         }

    //         DB::commit();
    //         Session::flash('message', 'student grading updated successfully.');
    //         return response()->json(['success' => true, 'message'=> 'student grading updated successfully.']);

    //     } catch (\Exception $e) {
    //         DB::rollback();
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }

    public function createGrading(Request $request)
    {
        // Validate the incoming data:
        $request->validate([
            'event_id'             => 'required|integer|exists:training_events,id',
            'task_grade'           => 'nullable|array',
            'task_grade.*.*'       => ['required', 'string', Rule::in(['Incomplete', 'Further training required', 'Competent', '1', '2', '3', '4', '5'])],
            'task_comments'        => 'nullable|array',
            'task_comments.*.*'    => 'nullable|string|max:255',  // Optional comment validation
            'comp_grade'           => 'nullable|array',
            'comp_grade.*.*'       => 'required|integer|min:1|max:5',  // Grading from 1-5
            'comp_comments'        => 'nullable|array',
            'comp_comments.*.*'    => 'nullable|string|max:255',  // Optional comment validation
            // Ensure the student being graded is provided from the form:
            'tg_user_id'           => 'required|integer|exists:users,id',
            'cg_user_id'           => 'required|integer|exists:users,id',
        ]);
    
        // Begin the database transaction
        DB::beginTransaction();
        
        try {
            $event_id = $request->input('event_id');
            // Get the student ID being graded from the hidden fields:
            $gradedStudentId = $request->input('tg_user_id');
            $gradedStudentIdForComp = $request->input('cg_user_id');
            
            // Store or update Task Grading (for sublessons):
            if ($request->has('task_grade')) {
                foreach ($request->input('task_grade') as $lesson_id => $subLessons) {
                    foreach ($subLessons as $sub_lesson_id => $task_grade) {
                        TaskGrading::updateOrCreate(
                            [
                                'event_id'      => $event_id,
                                'lesson_id'     => $lesson_id,
                                'sub_lesson_id' => $sub_lesson_id,
                                'user_id'       => $gradedStudentId,  // Use student ID being graded
                            ],
                            [
                                'task_grade'    => $task_grade,
                                'task_comment'  => $request->input("task_comments.$lesson_id.$sub_lesson_id", null),
                                'created_by'    => auth()->user()->id,  // evaluator's ID
                            ]
                        );
                    }
                }
            }
    
            // Store or update Competency Grading (lesson-level):
            if ($request->has('comp_grade')) {
                foreach ($request->input('comp_grade') as $lesson_id => $competencyGrades) {
                    $compData = [   
                        'event_id'  => $event_id,
                        'lesson_id' => $lesson_id,
                        'user_id'   => $gradedStudentIdForComp, // Use student ID from hidden input
                        'created_by'=> auth()->user()->id,
                    ];
        
                    // Map each competency grade and comment to its respective database columns.
                    foreach ($competencyGrades as $code => $grade) {
                        // Build column names based on competency code (e.g. KNO becomes kno_grade, kno_comment)
                        $gradeColumn   = strtolower($code) . '_grade';
                        $commentColumn = strtolower($code) . '_comment';
                        
                        $compData[$gradeColumn] = $grade;
                        $compData[$commentColumn] = $request->input("comp_comments.$lesson_id.$code", null);
                    }
        
                    CompetencyGrading::updateOrCreate(
                        [
                            'event_id'  => $event_id,
                            'lesson_id' => $lesson_id,
                            'user_id'   => $gradedStudentIdForComp,
                        ],
                        $compData
                    );
                }
            }
            
            // Commit the transaction on success
            DB::commit();

            TrainingEvents::where('id', $event_id)->where('is_locked', '!=', 1)->update(['is_locked' => 1]);
            Session::flash('message', 'Student grading updated successfully.');
            return response()->json(['success' => true, 'message' => 'Student grading updated successfully.']);
        
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    
    public function storeOverallAssessment(Request $request)
    {
        // Validate request
        $request->validate([
            'event_id' => 'required|integer|exists:training_events,id',
            'user_id' => 'required|integer|exists:users,id',
            'result' => 'required|string',
            'remarks' => 'nullable|string'
        ]);

        // Create or update overall assessment
        OverallAssessment::updateOrCreate(
            [
                'event_id' => $request->event_id,
                'user_id' => $request->user_id,
                
            ],
            [
                'result' => $request->result,
                'remarks' => $request->remarks,
                'created_by'=>auth()->user()->id,
            ]
        );

        TrainingEvents::where('id', $request->event_id)->where('is_locked', '!=', 1)->update(['is_locked' => 1]);
        Session::flash('message', 'Overall Assessment saved successfully.');
        return response()->json(['success' => true, 'message' => 'Overall Assessment saved successfully.']);
    }

    public function getStudentGrading(Request $request, $event_id)
    {
        $userId = auth()->user()->id;
        $ouId = auth()->user()->ou_id;
    
        $event = TrainingEvents::where('ou_id', $ouId)
            ->where('id', decode_id($event_id))
            ->whereHas('taskGradings', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with([
                'taskGradings' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->with('lesson:id,lesson_title') // Load only lesson_name
                        ->with('subLesson:id,title'); // Load only sub_lesson_name
                },
                'competencyGradings' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                },
                'overallAssessments' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                },
                'course:id,course_name,enable_feedback', // Load only course name
                'group:id,name', // Load only group name
                'instructor:id,fname,lname' // Load only instructor name
            ])
            ->first(); // Use first() to get a single event
        
        if (!$event) {
            abort(404, 'Training Event not found.');
        }    
        
        $event->student_feedback_submitted = $event->trainingFeedbacks()->where('user_id', auth()->user()->id)->exists();    
        // dd($event->student_feedback_submitted);
        // dd($event->course->enable_feedback);
    
        return view('trainings.grading-list', compact('event'));    
    }
    

    public function unlockEventGarding(Request $request, $event_id)
    {
        $updateTrainingEvent = TrainingEvents::where('id', decode_id($event_id))->update(['is_locked' => 0]);
        if($updateTrainingEvent){
            Session::flash('message', 'Grading is unlocked for editing.');
            return response()->json(['success' => true, 'message' => 'Grading is unlocked for editing.']);
        }
    }


    public function acknowledgeGarding(Request $request)
    {
        // Optional: Validate the incoming comment
        $request->validate([
            'ack_comment' => 'nullable|string|max:1000',
        ]);
    
        $eventId = decode_id($request->eventId);
    
        $updated = TrainingEvents::where('id', $eventId)->update([
            'student_acknowledged' => 1,
            'student_acknowledgement_comments' => $request->ack_comment,
        ]);
    
        if ($updated) {
            Session::flash('message', 'Acknowledgment recorded');
            return response()->json(['success' => true, 'message' => 'Acknowledgment recorded']);
        }
    
        return response()->json(['success' => false, 'message' => 'Acknowledgment failed'], 500);
    }





    
    
}
