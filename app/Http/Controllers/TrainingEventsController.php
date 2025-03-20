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
use App\Models\CompetencyGrading;
use App\Models\OverallAssessment;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TrainingEventsController extends Controller
{
    public function index()
    {
        $currentUser = auth()->user();
        $organizationUnits = OrganizationUnits::all();
        if ($currentUser->is_owner == 1 && empty($currentUser->ou_id)) {
            $course =  Courses::all();            
            $group =  Group::all();            
            $instructor =  User::whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Instructor%');
            })->with('roles')->get();          
            $trainingEvents = TrainingEvents::with(['course:id,course_name', 'group:id,name', 'instructor:id,fname,lname'])->get();
            // dd($trainingEvents);
        } elseif (checkAllowedModule('training', 'training.index')->isNotEmpty() && empty($currentUser->is_admin)) {
            $course =  Courses::where('ou_id', $currentUser->ou_id)->get();    
            $group =  Group::where('ou_id', $currentUser->ou_id)->get();            
            $instructor =  User::whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Instructor%');
            })->with('roles')->get();       
            $trainingEvents =  TrainingEvents::where('ou_id', $currentUser->ou_id)->where('instructor_id', $currentUser->id)->get();  
        }else{
            $group =  Group::where('ou_id', $currentUser->ou_id)->get();            
            $course =  Courses::where('ou_id', $currentUser->ou_id)->get();    
            $instructor = User::where('ou_id', $currentUser->ou_id)
            ->whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Instructor%');
            })
            ->with('roles')
            ->get();            
            $trainingEvents =  TrainingEvents::where('ou_id', $currentUser->ou_id)->get();            
        }
        return view('trainings.index', compact('group', 'course', 'instructor', 'organizationUnits', 'trainingEvents'));
    }

    public function createTrainingEvent(Request $request)
    {
        // Validate request data
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'group_id' => 'required|exists:groups,id',
            'instructor_id' => 'required|exists:users,id',
            'start_time' => 'required|date_format:H:i', // Validating time format (HH:MM)
            'end_time' => 'required|date_format:H:i|after:start_time', // Ensuring end_time is after start_time
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->is_owner == 1 && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
        ]);
    
        // Create a new training event
        $trainingEvent = TrainingEvents::create([
            "ou_id" => (auth()->user()->is_owner == 1) ? $request->ou_id : auth()->user()->ou_id, // Assign ou_id only if Super Admin provided it
            'course_id' => $request->course_id,
            'group_id' => $request->group_id,
            'instructor_id' => $request->instructor_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time
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
        // Validate request
        $request->merge([
            'start_time' => date('H:i', strtotime($request->start_time)),
            'end_time' => date('H:i', strtotime($request->end_time)),
        ]);
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'group_id' => 'required|exists:groups,id',
            'instructor_id' => 'required|exists:users,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'ou_id' => [
                    function ($attribute, $value, $fail) {
                        if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                            $fail('The Organizational Unit (OU) is required for Super Admin.');
                        }
                    }
                ]
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
            'group_id' => $request->group_id,
            'instructor_id' => $request->instructor_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time
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
        $errors = [];

        // Get all submitted lesson, sub-lesson, and user IDs
        $lessons = $request->tg_lesson_id ?? [];
        $subLessons = $request->tg_subLesson_id ?? [];
        $users = $request->tg_user_id ?? [];
        $cgUsers = $request->cg_user_id ?? []; // Lessons for competency grading
        $cgLessons = $request->cg_lesson_id ?? []; // Lessons for competency grading

        // Convert indexed arrays to associative for easier validation
        $lessons = array_flip($lessons);
        $subLessons = array_flip($subLessons);
        $users = array_flip($users);
        $cgLessons = array_flip($cgLessons);
        
        // Validate task_grade
        foreach ($lessons as $lesson_id => $val) {
            foreach ($subLessons as $sub_lesson_id => $val) {
                foreach ($users as $user_id => $val) {
                    if (!isset($request->task_grade[$lesson_id][$sub_lesson_id][$user_id])) {
                        $user = User::find($user_id);
                        if ($user) {
                            $errors["task_grade_{$lesson_id}_{$sub_lesson_id}_{$user_id}"] = 
                                "Please select a task grade for {$user->fname} {$user->lname}.";
                        }
                    }
                }
            }
        }

        // Validate comp_grade using cg_lesson_id
        foreach ($cgLessons as $lesson_id => $val) {
            foreach ($users as $user_id => $val) {
                if (!isset($request->comp_grade[$lesson_id][$user_id])) {
                    $user = User::find($user_id);
                    if ($user) {
                    $errors["comp_grade_{$lesson_id}_{$user_id}"] = 
                        "Please select a competency grade for {$user->fname} {$user->lname}.";
                    }
                }
            }
        }

        // If there are errors, return them as JSON
        if (!empty($errors)) {
            return response()->json(['success' => false, 'errors' => $errors], 500);
        }

        // If validator fails, return validation errors
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

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

    
    
}
