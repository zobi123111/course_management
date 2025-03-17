<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Courses;
use App\Models\CourseLesson;
use App\Models\SubLesson;
use App\Models\User;
use App\Models\OrganizationUnits;
use App\Models\TrainingEvents;
use Illuminate\Support\Facades\Session;


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
            $instructor =  User::where('role', 2)->get();        
            $trainingEvents =  TrainingEvents::where('ou_id', $currentUser->ou_id)->get();  
        }else{
            $group =  Group::where('ou_id', $currentUser->ou_id)->get();            
            $course =  Courses::where('ou_id', $currentUser->ou_id)->get();    
            $instructor =  User::where('ou_id', $currentUser->ou_id)->where('role', 2)->get();            
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
            $groupUsers = is_array($userIds) ? User::whereIn('id', $userIds)->get() : collect();
        } else {
            $groupUsers = collect(); // Return empty collection if no users found
        }

        // Ensure course exists before accessing course_id
        $courseLessons = $trainingEvent->course 
        ? CourseLesson::with('sublessons')->where('course_id', $trainingEvent->course->id)->get() 
        : collect();
        // dd($courseLessons);;
        return view('trainings.show', compact('trainingEvent', 'groupUsers', 'courseLessons'));
    }
    
}
