<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Courses;
use App\Models\CourseLesson;
use App\Models\User;
use App\Models\OrganizationUnits;
use App\Models\TrainingEvents;
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
        $resurces =  Resource::all();
        if ($currentUser->is_owner == 1 && empty($currentUser->ou_id)) {
            // $resurces =  Resource::all();
            $course =  Courses::all();            
            $group =  Group::all();            
            $instructor =  User::whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Instructor%'); 
            })->with('roles')->get();          
            $student =  User::whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Student%'); 
            })->with('roles')->get();          
            $trainingEvents = TrainingEvents::with(['course:id,course_name', 'group:id,name', 'instructor:id,fname,lname'])->get();
            // dd($trainingEvents);
        } elseif (checkAllowedModule('training', 'training.index')->isNotEmpty() && empty($currentUser->is_admin)) {
            $course =  Courses::where('ou_id', $currentUser->ou_id)->get();    
            $group =  Group::where('ou_id', $currentUser->ou_id)->get();   
            // $resurces =  Resource::where('ou_id',$currentUser->ou_id)->get();

            $instructor =  User::where('ou_id', $currentUser->ou_id)
            ->where(function ($query) {
                $query->whereNull('is_admin')->orWhere('is_admin', false);
            })
            ->whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Instructor%');
            })->with('roles')->get(); 

            $student =  User::where('ou_id', $currentUser->ou_id)
            ->where(function ($query) {
                $query->whereNull('is_admin')->orWhere('is_admin', false);
            })
            ->whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Student%'); 
            })->with('roles')->get();    

            if (hasUserRole($currentUser, 'Instructor')) {
                // For instructors: filter by instructor_id
                $trainingEvents = TrainingEvents::where('ou_id', $currentUser->ou_id)
                    ->where('instructor_id', $currentUser->id)
                    ->get();
            } else {
                // For students: check if the current user's id exists in the group's user_ids JSON column
                $trainingEvents = TrainingEvents::where('ou_id', $currentUser->ou_id)
                    ->whereHas('group', function ($query) use ($currentUser) {
                        $query->whereJsonContains('user_ids', (string)$currentUser->id);
                    })
                    ->get();
            }
        }else{
            $group =  Group::where('ou_id', $currentUser->ou_id)->get();            
            $course =  Courses::where('ou_id', $currentUser->ou_id)->get(); 
            // $resurces =  Resource::where('ou_id',$currentUser->ou_id)->get();       
            $instructor = User::where('ou_id', $currentUser->ou_id)
            ->where(function ($query) {
                $query->whereNull('is_admin')->orWhere('is_admin', false);
            })
            ->whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Instructor%');
            })->with('roles')->get();    

            $student =  User::where('ou_id', $currentUser->ou_id)
            ->where(function ($query) {
                $query->whereNull('is_admin')->orWhere('is_admin', false);
            })
            ->whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Student%'); 
            })->with('roles')->get();      

            $trainingEvents =  TrainingEvents::where('ou_id', $currentUser->ou_id)->get();   
                
        }
        return view('trainings.index', compact('group', 'course', 'instructor', 'organizationUnits', 'trainingEvents', 'resurces', 'student'));
    }

    public function getStudentLicenseNumber(Request $request,$user_id)
    {
        // dd($request->all());
        $user = User::find($user_id);
        if ($user) {
            return response()->json(['success' => true, 'licence_number' => $user->licence_number]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function createTrainingEvent(Request $request)
    {
        // Validate request data
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'student_id' => 'required|exists:users,id',
            'instructor_id' => 'required|exists:users,id',
            'start_time' => 'required|date_format:Y-m-d\TH:i', // Validating datetime-local format
            'end_time' => 'required|date_format:Y-m-d\TH:i|after:start_time', // Ensuring end_time is after start_time
            'departure_airfield' => 'required|string|size:4', // Ensuring exactly 4-letter airfield code
            'destination_airfield' => 'required|string|size:4',
            'resource_id' => 'required|exists:resources,id',
            'total_time' => 'required|date_format:H:i', // Total time as hh:mm from user input
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->is_owner == 1 && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
            'licence_number' => 'nullable|string',
        ]);
    
        // Create a new training event
        $trainingEvent = TrainingEvents::create([
            "ou_id" => (auth()->user()->is_owner == 1) ? $request->ou_id : auth()->user()->ou_id,
            'course_id' => $request->course_id,
            'group_id' => $request->group_id,
            'instructor_id' => $request->instructor_id,
            'resource_id' => $request->resource_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'departure_airfield' => strtoupper($request->departure_airfield), // Convert to uppercase
            'destination_airfield' => strtoupper($request->destination_airfield),
            'total_time' => $request->total_time, // Using the user-provided total time
            'licence_number' => $request->licence_number ?? auth()->user()->licence_number,
        ]);
    
        Session::flash('message', 'Training event created successfully.');
    
        // Return JSON response
        return response()->json([
            'success' => true,
            'message' => 'Training event created successfully',
            'trainingEvent' => $trainingEvent
        ], 201);
    }
    

    public function getTrainingEvent(Request $request)
    {
        $trainingEvent = TrainingEvents::findOrFail(decode_id($request->eventId));
        if($trainingEvent)
        {
            return response()->json(['trainingEvent'=> $trainingEvent]);
        }else{
            return response()->json(['status'=> false, 'message' => 'Training event Not found']);
        }
    }

    public function updateTrainingEvent(Request $request)
    {
        // Convert start_time and end_time to proper format
        $request->merge([
            'start_time' => date('Y-m-d H:i:s', strtotime($request->start_time)),
            'end_time' => date('Y-m-d H:i:s', strtotime($request->end_time)),
        ]);
    
        // Validate request
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'student_id' => 'required|exists:users,id',
            'instructor_id' => 'required|exists:users,id',
            'start_time' => 'required|date_format:Y-m-d H:i:s',
            'end_time' => 'required|date_format:Y-m-d H:i:s|after:start_time',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
            'resource_id' => 'nullable|exists:resources,id',
            'departure_airfield' => 'nullable|string|max:255',
            'destination_airfield' => 'nullable|string|max:255',
            'licence_number' => 'nullable|string|max:255',
        ]);
    
        // Find the training event
        $trainingEvent = TrainingEvents::find($request->event_id);
    
        if (!$trainingEvent) {
            return response()->json(['message' => 'Training event not found'], 404);
        }
    
        // Update fields
        $trainingEvent->update([
            "ou_id" => (auth()->user()->is_owner == 1) ? $request->ou_id : auth()->user()->ou_id, // Assign ou_id only if Super Admin provided it
            'course_id' => $request->course_id,
            'student_id' => $request->student_id,
            'instructor_id' => $request->instructor_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'resource_id' => $request->resource_id,
            'departure_airfield' => $request->departure_airfield,
            'destination_airfield' => $request->destination_airfield,
            'licence_number' => $request->licence_number,
        ]);
    
        Session::flash('message', 'Training event updated successfully.');
        return response()->json([
            'message' => 'Training event updated successfully',
            'trainingEvent' => $trainingEvent
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

    public function getOrgGroupsAndInstructors(Request $request)
    {
        $orgUnitGroups = Group::where('ou_id', $request->ou_id)
               ->get();
        $ouInstructors = User::where('ou_id', $request->ou_id)
        ->whereHas('roles', function ($query) {
            $query->where('role_name', 'like', '%Instructor%');
        })
        ->with('roles')
        ->get();
           
        if($orgUnitGroups){
            return response()->json(['orgUnitGroups' => $orgUnitGroups, 'ouInstructors'=> $ouInstructors]);
        }else{
            return response()->json(['error'=> 'Org Unit Groups not found.']);
        }
    }

    public function showTrainingEvent(Request $request, $event_id)
    {
      
        $trainingEvent = TrainingEvents::with(['course:id,course_name', 'group:id,name,user_ids', 'instructor:id,fname,lname'])
        ->find(decode_id($event_id));
      //  dd($trainingEvent);
        if ($trainingEvent && !empty($trainingEvent->group->user_ids)) {
            // Ensure user_ids is an array (convert from JSON if needed)
            $userIds = is_string($trainingEvent->group->user_ids) 
            ? json_decode($trainingEvent->group->user_ids, true) 
            : $trainingEvent->group->user_ids;
            // Fetch users only if decoding is successful
            $groupUsers = is_array($userIds) 
            ? User::whereIn('id', $userIds)
                ->with([
                    'taskGrades' => function ($query) use ($event_id) {
                        $query->where('event_id', decode_id($event_id));
                    },
                    'competencyGrades' => function ($query) use ($event_id) {
                        $query->where('event_id', decode_id($event_id));
                    }
                ])
                ->get() 
            : collect();
        } else {
            $groupUsers = collect(); // Return empty collection if no users found
        }
        // Ensure course exists before accessing course_id
        $courseLessons = $trainingEvent->course ? CourseLesson::with('sublessons')->where('course_id', $trainingEvent->course->id)->whereHas('sublessons')->get() : collect(); // Only fetch lessons that have sublessons

        // Fetch overall assessments for users in this event
        $overallAssessments = OverallAssessment::where('event_id', $trainingEvent->id)
        ->whereIn('user_id', $groupUsers->pluck('id'))
        ->pluck('result', 'user_id'); // Get result indexed by user_id

        $overallRemarks = OverallAssessment::where('event_id', $trainingEvent->id)
        ->whereIn('user_id', $groupUsers->pluck('id'))
        ->pluck('remarks', 'user_id'); // Get remark indexed by user_id
        // dd($courseLessons);;
        return view('trainings.show', compact('trainingEvent', 'groupUsers', 'courseLessons', 'overallAssessments', 'overallRemarks'));
    }


    public function createGrading(Request $request)
    {
        // Validate the request
        $request->validate([
            'event_id' => 'required|integer|exists:training_events,id',
            'task_grade' => 'nullable|array',
            'task_grade.*.*.*' => ['required', 'string', Rule::in(['N/A', 'Further training required', 'Competent', '1', '2', '3', '4', '5'])],
            'comp_grade' => 'nullable|array',
            'comp_grade.*.*' => 'required|integer|min:1|max:5',
        ]);

        DB::beginTransaction();

        try {
            $event_id = $request->event_id;

            // Store or Update Task Grading
            if ($request->has('task_grade')) {
                foreach ($request->task_grade as $lesson_id => $subLessons) {
                    foreach ($subLessons as $sub_lesson_id => $users) {
                        foreach ($users as $user_id => $task_grade) {
                            TaskGrading::updateOrCreate(
                                [
                                    'event_id' => $event_id,
                                    'lesson_id' => $lesson_id,
                                    'sub_lesson_id' => $sub_lesson_id,
                                    'user_id' => $user_id,
                                ],
                                [
                                    'task_grade' => $task_grade,
                                    'created_by' => auth()->user()->id, // Moved to update fields
                                ]
                            );
                        }
                    }
                }
            }

            // Store or Update Competency Grading
            if ($request->has('comp_grade')) {
                foreach ($request->comp_grade as $lesson_id => $users) {
                    foreach ($users as $user_id => $competency_grade) {
                        CompetencyGrading::updateOrCreate(
                            [
                                'event_id' => $event_id,
                                'lesson_id' => $lesson_id,
                                'user_id' => $user_id,
                            ],
                            [
                                'competency_grade' => $competency_grade,
                                'created_by'=> auth()->user()->id,
                            ]
                        );
                    }
                }
            }

            DB::commit();
            Session::flash('message', 'student grading updated successfully.');
            return response()->json(['success' => true, 'message'=> 'student grading updated successfully.']);

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

        Session::flash('message', 'Overall Assessment saved successfully.');
        return response()->json(['success' => true, 'message' => 'Overall Assessment saved successfully.']);
    }

    public function getStudentGrading(Request $request,$event_id)
    {
        $userId = auth()->user()->id;
        $ouId = auth()->user()->ou_id;

        $events = TrainingEvents::where('ou_id', $ouId)->where('id', decode_id($event_id))
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
                'course:id,course_name',  // Load only course name
                'group:id,name',   // Load only group name
                'instructor:id,fname,lname' // Load only instructor name
            ])
            ->get();
        return view('trainings.grading-list', compact('events'));
    }


    
    
}
