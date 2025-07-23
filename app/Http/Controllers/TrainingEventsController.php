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
use App\Models\TrainingEventLessons;
use App\Models\TaskGrading;
use App\Models\DeferredItem;
use App\Models\Resource;
use App\Models\CompetencyGrading;
use App\Models\OverallAssessment;
use App\Models\DefTask;
use App\Models\DefLesson;
use App\Models\DefLessonTask;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PDF;
use Illuminate\Support\Str;



class TrainingEventsController extends Controller
{ 
    public function index()
    {
        $currentUser = auth()->user();
        $organizationUnits = OrganizationUnits::all();
        // $resources = Resource::all();
        // Define relationships for trainingEvents query
        $trainingEventsRelations = [
            'course:id,course_name,course_type',
            'student:id,fname,lname',
            'instructor:id,fname,lname',
            'resource:id,name',
            'firstLesson.instructor:id,fname,lname',
            'firstLesson.resource:id,name',            
            // for your getCanEndCourseAttribute()
            'eventLessons',
            'eventLessons.lesson:id,enable_cbta',            // pull enable_cbta from course_lessons
            'eventLessons.lesson.subLessons:id,lesson_id,title', // pull sub-lessons from each lesson
            'overallAssessments',                              // for single-event overall check
        ];

        if ($currentUser->is_owner == 1 && empty($currentUser->ou_id)) {
            // Super Admin: Get all data
            $resources = Resource::all();
            // $courses = Courses::all();
            $courses = Courses::orderBy('position')->get();
            $groups = Group::all();
            $instructors = User::whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Instructor%');
            })->with('roles')->get();
            $students = User::whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Student%');
            })->with('roles')->get();
            $trainingEvents = TrainingEvents::with($trainingEventsRelations)
                            ->withCount([
                                'taskGradings',
                                'competencyGradings'
                            ])->get();
            $trainingEvents_instructor = TrainingEvents::with($trainingEventsRelations)
                                        ->where('entry_source', "instructor")
                                        ->withCount([
                                            'taskGradings',
                                            'competencyGradings'
                                        ])->get();
          
        } elseif (checkAllowedModule('training', 'training.index')->isNotEmpty() && empty($currentUser->is_admin)) {
            // Regular User: Get data within their organizational unit
            $resources = Resource::where('ou_id', $currentUser->ou_id)->get();
            // $courses = Courses::where('ou_id', $currentUser->ou_id)->get();
            $courses = Courses::where('ou_id', $currentUser->ou_id)->orderBy('position')->get();
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
            $trainingEventsQuery = TrainingEvents::where('ou_id', $currentUser->ou_id)
                ->with($trainingEventsRelations)
                ->withCount(['taskGradings', 'competencyGradings']);

            $trainingEvents_instructorQuery = TrainingEvents::where('ou_id', $currentUser->ou_id)
                                        ->with($trainingEventsRelations)
                                        ->withCount(['taskGradings', 'competencyGradings']);               

            if (hasUserRole($currentUser, 'Instructor')) {
                // Get training event IDs where the current instructor is assigned to at least one lesson
                $eventIds = TrainingEventLessons::where('instructor_id', $currentUser->id)
                           ->pluck('training_event_id')
                           ->unique();
                           
            
                $trainingEvents = $trainingEvents_instructorQuery->whereIn('id', $eventIds)->get();
                $trainingEvents_instructor = TrainingEvents::with($trainingEventsRelations)
                                        ->where('entry_source', "instructor")
                                        ->where('student_id', $currentUser->id)
                                        ->withCount([
                                            'taskGradings',
                                            'competencyGradings'
                                        ])->get();
                
            } else {
                $trainingEvents = $trainingEventsQuery
                    ->where('student_id', $currentUser->id)
                    ->where(function ($query) use ($currentUser) {
                        $query->whereHas('taskGradings', function ($q) use ($currentUser) {
                            $q->where('user_id', $currentUser->id);
                        })->orWhereHas('competencyGradings', function ($q) use ($currentUser) {
                            $q->where('user_id', $currentUser->id);
                        })->orWhereHas('overallAssessments', function ($q) use ($currentUser) {
                            $q->where('user_id', $currentUser->id);
                        });
                    })
                    ->get();
                $trainingEvents_instructor = [];
            }
        } else {
            // Default Case: Users with limited access within their organization
            $resources = Resource::where('ou_id', $currentUser->ou_id)->get();
            $courses = Courses::where('ou_id', $currentUser->ou_id)->orderBy('position')->get();
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

            $trainingEvents = TrainingEvents::where('ou_id', $currentUser->ou_id)
                ->with($trainingEventsRelations)
                ->withCount(['taskGradings', 'competencyGradings'])
                ->get();
            $trainingEvents_instructor = TrainingEvents::where('ou_id', $currentUser->ou_id)
                ->where('entry_source', "instructor")
                ->with($trainingEventsRelations)
                ->withCount(['taskGradings', 'competencyGradings'])
                ->get();
              
        } 
        // Attach instructor lists to each training event
        $trainingEvents->each(function ($event) {
            if (!$event->relationLoaded('eventLessons') || !($event->eventLessons instanceof \Illuminate\Support\Collection)) {
                $event->lesson_instructors = collect();
                $event->lesson_instructor_users = collect();
                $event->last_lesson_instructor_id = null;
                $event->last_lesson_instructor = null;
                return;
            }
            // Get unique instructor IDs from event lessons
            $event->lesson_instructors = $event->eventLessons
                ->pluck('instructor_id')
                ->filter()
                ->unique()
                ->values();

            // Load instructor users
            $event->lesson_instructor_users = User::whereIn('id', $event->lesson_instructors)->get();

            // Determine the last lesson instructor (by Id)
            $lastLesson = $event->eventLessons->sortByDesc('id')->first();
            $event->last_lesson_instructor_id = $lastLesson ? $lastLesson->instructor_id : null;

            // Optional: preload the actual user object
            $event->last_lesson_instructor = $event->lesson_instructor_users->firstWhere('id', $event->last_lesson_instructor_id);
        });

        return view('trainings.index', compact('groups', 'courses', 'instructors', 'organizationUnits', 'trainingEvents', 'resources', 'students', 'trainingEvents_instructor'));

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
        $user = User::with('documents')->find($user_id);
        $groups = Group::where('ou_id', $ou_id)
        ->whereJsonContains('user_ids', strval($user_id)) // Ensure user_id is a string
        ->pluck('id'); // Get only the group IDs
        
        $courses = Courses::with('groups') // Load only the groups relationship
        ->whereHas('groups', function ($query) use ($groups) {
            $query->whereIn('groups.id', $groups);
        })
        ->get();        
        if ($user) {
            return response()->json(['success' => true, 'licence_number' => $user->licence ?: $user->licence_2, 'courses' => $courses]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    public function getInstructorLicenseNumber(Request $request, $instructor_id)
    {
        $instructor = User::with('documents')->find($instructor_id);   
    
        if ($instructor) {
            return response()->json([
                'success' => true,
                'instructor_licence_number' => $instructor->licence ?: $instructor->licence_2
            ]);
        }
    
        return response()->json(['success' => false], 404);
    }

    // public function getCourseLessons(Request $request)
    // {
    //     $lessons = CourseLesson::where('course_id', $request->course_id)->get();
    //     if ($lessons) {
    //         return response()->json(['success' => true, 'lessons' => $lessons]);
    //     } else {
    //         return response()->json(['success' => false]);
    //     }
    // }

    public function getCourseLessons(Request $request)
    {
        $course = Courses::with(['courseLessons', 'resources'])->find($request->course_id);

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        return response()->json([   
            'success'   => true,
            'lessons'   => $course->courseLessons,
            'resources' => $course->resources,
        ]);
    }

    public function createTrainingEvent(Request $request)   
    {   
        //Validate base fields
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            // 'lesson_ids' => 'required|array',
            // 'lesson_ids.*' => 'exists:course_lessons,id',
            // 'total_time' => 'required|date_format:H:i',
            'event_date' => 'required|date_format:Y-m-d',
            'std_license_number' => 'nullable|string',
            'total_simulator_time' => 'nullable|date_format:H:i',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->is_owner == 1 && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
            'lesson_data' => 'required|array|min:1',
                // Validate ONLY the first lesson (index 0)
                // 'lesson_data.0.lesson_id' => 'required|exists:course_lessons,id',
                // 'lesson_data.0.instructor_id' => 'required|exists:users,id',
                // 'lesson_data.0.resource_id' => 'required|exists:resources,id',
                // 'lesson_data.0.lesson_date' => 'required|date_format:Y-m-d',
                // 'lesson_data.*.start_time' => [
                //         'nullable',
                //         'date_format:H:i',
                //     ],
                // 'lesson_data.*.end_time' => [
                //         'nullable',
                //         'date_format:H:i',
                //         function ($attribute, $value, $fail) use ($request) {
                //             preg_match('/lesson_data\.(\d+)\.end_time/', $attribute, $matches);
                //             if (!isset($matches[1])) return;

                //             $index = (int) $matches[1];
                //             $startTime = $request->input("lesson_data.$index.start_time");

                //             if ($index === 0) {
                //                 if (empty($startTime) || empty($value)) {
                //                     return $fail("Start time and end time are required for the first lesson.");
                //                 }
                //             }

                //             // Validate only if both times exist
                //             if (!empty($startTime) && !empty($value)) {
                //                 if (strtotime($value) <= strtotime($startTime)) {
                //                     $fail("End time must be after start time.");
                //                 }
                //             }
                //         },
                //     ],
                // 'lesson_data.0.departure_airfield' => 'required|string|size:4',
                // 'lesson_data.0.destination_airfield' => 'required|string|size:4',
                // 'lesson_data.0.instructor_license_number' => 'nullable|string',
            ], [], [
                'event_date' => 'Course start date',
                'lesson_data.0.instructor_id' => 'instructor',
                'lesson_data.0.resource_id' => 'resource',
                'lesson_data.0.lesson_date' => 'lesson date',
                'lesson_data.0.start_time' => 'start time',
                'lesson_data.0.end_time' => 'end time',
                'lesson_data.0.departure_airfield' => 'departure airfield',
                'lesson_data.0.destination_airfield' => 'destination airfield',
                'lesson_data.0.instructor_license_number' => 'instructor license number',
        ]);
        
        $lesson_ids = collect($request->lesson_data)->pluck('lesson_id')->toArray();



        // Check for duplicate training event
        $existingEvent = TrainingEvents::where('student_id', $request->student_id)
            ->where('course_id', $request->course_id)
            ->where('ou_id', auth()->user()->is_owner ? $request->ou_id : auth()->user()->ou_id)
            ->whereJsonContains('lesson_ids', $lesson_ids)
            ->get();

        foreach ($existingEvent as $event) {
            $existingLessons = TrainingEventLessons::where('training_event_id', $event->id)->get();

            $duplicateFound = true;
            foreach ($request->lesson_data as $newLesson) {
                $match = $existingLessons->firstWhere(function ($existingLesson) use ($newLesson) {
                    return $existingLesson->lesson_id == $newLesson['lesson_id']
                        && $existingLesson->lesson_date == $newLesson['lesson_date'];
                        
                });

                if (!$match) {
                    $duplicateFound = false;
                    break;
                }
            }

            if ($duplicateFound) {
                return response()->json([
                    'success' => false,
                    'message' => 'Duplicate training event found for this student, course, and lesson dates.'
                ]);
            }
        }

        // Create main training event first with simulator_time = 0
        $trainingEvent = TrainingEvents::create([
            'student_id' => $request->student_id,
            'course_id' => $request->course_id,
            'lesson_ids' => json_encode($lesson_ids),
            'event_date' => $request->event_date,
            'total_time' => $request->total_time,
            'simulator_time' => $request->total_simulator_time ?? '00:00',
            'std_license_number' => $request->std_licence_number,
            'ou_id' => auth()->user()->is_owner ? $request->ou_id : auth()->user()->ou_id,
            'entry_source' => $request->entry_source,
        ]);

        // Loop through lesson data
        foreach ($request->lesson_data as $lesson) { 
            $lessonModel = \App\Models\CourseLesson::find($lesson['lesson_id']);
            $resourceModel = \App\Models\Resource::find($lesson['resource_id']);

            $lessonType = $lessonModel?->lesson_type;
            $resourceName = $resourceModel?->name;

            $start = $lesson['start_time'] ?? null;
            $end = $lesson['end_time'] ?? null;
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
                'training_event_id' => $trainingEvent->id,
                'lesson_id' => $lesson['lesson_id'],
                'instructor_id' => $lesson['instructor_id'],
                'resource_id' => $lesson['resource_id'],
                'lesson_date' => $lesson['lesson_date'],
                'start_time' => $start,
                'end_time' => $end,
                'departure_airfield' => ($lessonType === 'groundschool' && in_array($resourceName, ['Classroom', 'Homestudy'])) ? null : strtoupper($lesson['departure_airfield']),
                'destination_airfield' => ($lessonType === 'groundschool' && in_array($resourceName, ['Classroom', 'Homestudy'])) ? null : strtoupper($lesson['destination_airfield']),
                'instructor_license_number' => $lesson['instructor_license_number'] ?? null,
                'hours_credited' => gmdate("H:i", $creditMinutes * 60),
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
        $ou_id = $trainingEvent['ou_id'];
        $instructors = User::where('ou_id', $ou_id)
                        ->where(function ($query) {
                            $query->whereNull('is_admin')->orWhere('is_admin', false);
                        })
                        ->whereHas('roles', function ($query) {
                            $query->where('role_name', 'like', '%Instructor%');
                        })->with('roles')->get();
                    
        if($trainingEvent)
        {
            return response()->json(['success'=> true,'trainingEvent'=> $trainingEvent, 'instructors' => $instructors]);
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
            'event_date' => 'required|date_format:Y-m-d',
            'std_license_number' => 'nullable|string',
            'total_time' => 'nullable|date_format:H:i',
            'total_simulator_time' => 'nullable|date_format:H:i',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->is_owner == 1 && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
            'lesson_data' => 'required|array|min:1',
                // Validate ONLY the first lesson (index 0)
                // 'lesson_data.0.lesson_id' => 'required|exists:course_lessons,id',
                // 'lesson_data.0.instructor_id' => 'required|exists:users,id',
                // 'lesson_data.0.resource_id' => 'required|exists:resources,id',
                // 'lesson_data.0.lesson_date' => 'required|date_format:Y-m-d',
                // 'lesson_data.*.start_time' => [
                //         'nullable',
                //         'date_format:H:i',
                //     ],
                // 'lesson_data.*.end_time' => [
                //         'nullable',
                //         'date_format:H:i',
                //         function ($attribute, $value, $fail) use ($request) {
                //             preg_match('/lesson_data\.(\d+)\.end_time/', $attribute, $matches);
                //             if (!isset($matches[1])) return;

                //             $index = (int) $matches[1];
                //             $startTime = $request->input("lesson_data.$index.start_time");

                //             if ($index === 0) {
                //                 if (empty($startTime) || empty($value)) {
                //                     return $fail("Start time and end time are required for the first lesson.");
                //                 }
                //             }

                //             // Validate only if both times exist
                //             if (!empty($startTime) && !empty($value)) {
                //                 if (strtotime($value) <= strtotime($startTime)) {
                //                     $fail("End time must be after start time.");
                //                 }
                //             }
                //         },
                //     ],
                // 'lesson_data.0.departure_airfield' => 'required|string|size:4',
                // 'lesson_data.0.destination_airfield' => 'required|string|size:4',
                // 'lesson_data.0.instructor_license_number' => 'nullable|string',
            ], [], [
                'event_date' => 'Course start date',
                'lesson_data.0.instructor_id' => 'instructor',
                'lesson_data.0.resource_id' => 'resource',
                'lesson_data.0.lesson_date' => 'lesson date',
                'lesson_data.0.start_time' => 'start time',
                'lesson_data.0.end_time' => 'end time',
                'lesson_data.0.departure_airfield' => 'departure airfield',
                'lesson_data.0.destination_airfield' => 'destination airfield',
                'lesson_data.0.instructor_license_number' => 'instructor license number',
        ]);

        // Check for duplicate training events (same student, course, and lesson dates)
        $duplicateFound = false;
        $eventId = $request->event_id;
        $studentId = $request->student_id;
        $courseId = $request->course_id;

        $incomingLessonDates = collect($lessonData)->pluck('lesson_date')->toArray();

        $duplicateFound = TrainingEventLessons::whereHas('trainingEvent', function ($query) use ($studentId, $courseId, $eventId) {
                $query->where('student_id', $studentId)
                    ->where('course_id', $courseId)
                    ->where('id', '!=', $eventId); // exclude current event
            })
            ->whereIn('lesson_date', $incomingLessonDates)
            ->exists();

        if ($duplicateFound) {
            return response()->json([
                'success' => false,
                'message' => 'Duplicate training event found for this student, course, and lesson dates.'
            ]);
        }

    
        // Update Training Event
        $trainingEvent = TrainingEvents::findOrFail($request->event_id);
        $trainingEvent->update([
            'ou_id' => auth()->user()->is_owner ? $request->ou_id : auth()->user()->ou_id,
            'course_id' => $request->course_id,
            'student_id' => $request->student_id,
            'event_date' => $request->event_date,
            'lesson_ids' => json_encode(array_column($lessonData, 'lesson_id')),
            'total_time' => $request->total_time ?? null,
            'simulator_time' => $request->total_simulator_time ?? '00:00',
            'std_license_number' => $request->std_licence_number ?? null,
        ]);
    
        // Get all lesson_ids from the request
        $incomingLessonIds = collect($lessonData)->pluck('lesson_id')->toArray();
    
        // Delete removed lessons
        TrainingEventLessons::where('training_event_id', $trainingEvent->id)
            ->whereNotIn('lesson_id', $incomingLessonIds)
            ->delete();
    
        // Re-insert/update lessons
        foreach ($lessonData as $data) {
            $lessonModel = \App\Models\CourseLesson::find($data['lesson_id']);
            $resourceModel = \App\Models\Resource::find($data['resource_id']);

            $lessonType = $lessonModel?->lesson_type;
            $resourceName = $resourceModel?->name;
            $start = $data['start_time'] ?? null;
            $end = $data['end_time'] ?? null;

            $creditMinutes = 0;
            if ($lessonType === 'groundschool' && $resourceName === 'Homestudy') {
                $creditMinutes = 480;
                $start = '00:00';
                $end = '08:00';
            } elseif ($start && $end) {
                try {
                    $startTime = \Carbon\Carbon::createFromFormat('H:i', $start);
                    $endTime = \Carbon\Carbon::createFromFormat('H:i', $end);
                    if ($endTime->lessThan($startTime)) {
                        $endTime->addDay();
                    }
                    $creditMinutes = $startTime->diffInMinutes($endTime);
                } catch (\Exception $e) {
                    $creditMinutes = 0;
                }
            }

            TrainingEventLessons::updateOrCreate(
                [
                    'training_event_id' => $trainingEvent->id,
                    'lesson_id' => $data['lesson_id'],
                ],
                [
                    'instructor_id' => $data['instructor_id'],
                    'resource_id' => $data['resource_id'],
                    'lesson_date' => $data['lesson_date'],
                    'start_time' => $start,
                    'end_time' => $end,
                    'departure_airfield' => ($lessonType === 'groundschool' && in_array($resourceName, ['Classroom', 'Homestudy'])) ? null : strtoupper($data['departure_airfield']),
                    'destination_airfield' => ($lessonType === 'groundschool' && in_array($resourceName, ['Classroom', 'Homestudy'])) ? null : strtoupper($data['destination_airfield']),
                    'instructor_license_number' => $data['instructor_license_number'] ?? null,
                    'hours_credited' => gmdate("H:i", $creditMinutes * 60),
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

    public function showTrainingEvent(Request $request, $event_id)
    {
        $currentUser = auth()->user();
        $trainingEvent = TrainingEvents::with([
            'course:id,course_name,course_type,duration_value,duration_type,groundschool_hours,simulator_hours',
            'course.documents', // Eager load course documents
            'group:id,name,user_ids',
            'instructor:id,fname,lname',
            'student:id,fname,lname',
            'resource:id,name',
            'eventLessons.lesson:id,lesson_title,enable_cbta,grade_type,lesson_type,custom_time_id',
            'eventLessons.instructor:id,fname,lname',
            'eventLessons.resource:id,name',
            'trainingFeedbacks.question', // Eager load the question relationship
            'documents' // Eager load the training event documents
        ])->find(decode_id($event_id));
        if (!$trainingEvent) {
            return abort(404, 'Training Event not found');
        }
        //Filter lessons based on role
        if (hasUserRole($currentUser, 'Instructor') && empty($currentUser->is_admin)) {
            // Only show lessons assigned to this instructor
            $eventLessons = $trainingEvent->eventLessons->filter(function ($lesson) use ($currentUser) {
                return $lesson->instructor_id == $currentUser->id;
            })->values();
        } else {
            //Admins and others with access can view all lessons
            $eventLessons = $trainingEvent->eventLessons;
        }
        //dd($eventLessons);
        $student = $trainingEvent->student;
        $lessonIds = $eventLessons->pluck('lesson_id')->filter()->unique();
        //Get task gradings (sublesson grades and comments)
        $taskGrades = TaskGrading::where('user_id', $student->id)
            ->where('event_id', $trainingEvent->id)
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->keyBy(fn($item) => $item->lesson_id . '_' . $item->sub_lesson_id);
    
        //Get competency grades (competency area grades and comments)
        $competencyGrades = CompetencyGrading::where('user_id', $student->id)
            ->where('event_id', $trainingEvent->id)
            ->whereIn('lesson_id', $lessonIds)
            ->get()
            ->groupBy('lesson_id');

        //Optional: Also pass overall assessments if you need them
        $overallAssessments = OverallAssessment::where('event_id', $trainingEvent->id)
            ->where('user_id', $student->id ?? null)
            ->first();
        //dd($trainingEvent->eventLessons);
        $hasCBTA = $trainingEvent->eventLessons->contains(function ($lesson) {
            return $lesson->enable_cbta == 1;
        });
        $isGradingCompleted = $taskGrades->isNotEmpty() && ($hasCBTA ? $competencyGrades->isNotEmpty() : true);

        //Retrieve feedback data
        $trainingFeedbacks = $trainingEvent->trainingFeedbacks;
        $defTasks = collect(DB::select("
            SELECT 
                dt.*,
                sl.title AS task_title,

                -- Prefer task_grade from dlt, else fallback to tg
                CASE 
                    WHEN dlt.task_grade IS NOT NULL THEN dlt.task_grade
                    ELSE tg.task_grade
                END AS task_grade,

                CASE 
                    WHEN dlt.task_comment IS NOT NULL THEN dlt.task_comment
                    ELSE tg.task_comment
                END AS task_comment

            FROM def_tasks dt

            -- Subquery join for latest def_lesson_tasks
            LEFT JOIN (
                SELECT dlt.*
                FROM def_lesson_tasks dlt
                INNER JOIN (
                    SELECT event_id, user_id, task_id, MAX(id) AS max_id
                    FROM def_lesson_tasks
                    GROUP BY event_id, user_id, task_id
                ) latest_dlt
                ON dlt.id = latest_dlt.max_id
            ) dlt ON dlt.event_id = dt.event_id
                AND dlt.user_id = dt.user_id
                AND dlt.task_id = dt.task_id

            -- Subquery join for latest task_gradings
            LEFT JOIN (
                SELECT tg.*
                FROM task_gradings tg
                INNER JOIN (
                    SELECT event_id, user_id, sub_lesson_id, MAX(id) AS max_id
                    FROM task_gradings
                    GROUP BY event_id, user_id, sub_lesson_id
                ) latest_tg
                ON tg.id = latest_tg.max_id
            ) tg ON tg.event_id = dt.event_id
                AND tg.user_id = dt.user_id
                AND tg.sub_lesson_id = dt.task_id

            -- Join to get sublesson title
            LEFT JOIN sub_lessons sl ON sl.id = dt.task_id

            WHERE dt.event_id = ?
        ", [$trainingEvent->id]));

        $getFirstdeftTasks = TaskGrading::where('event_id', $trainingEvent->id)
            ->whereIn('task_grade', ['Incomplete', 'Further training required'])
            ->get();
        $deferredTaskIds = collect($getFirstdeftTasks)->pluck('sub_lesson_id')->toArray();

        // $deferredLessons = DefLesson::with(['student', 'instructor', 'instructor.documents', 'resource'])
        // ->where('event_id', $trainingEvent->id)
        // ->get();
        $defLessonTasks = DefLessonTask::with(['user', 'defLesson.instructor', 'defLesson.instructor.documents', 'defLesson.resource', 'task'])
        ->where('event_id', $trainingEvent->id)
        ->get();
        // dd($deferredLessons);

        $deferredLessonsTasks = DefLessonTask::with([
                'user', 
                'defLesson.instructor', 
                'defLesson.resource', 
                'task'
            ])
            ->where('event_id', $trainingEvent->id)
            ->where('task_grade', '!=', 'Competent') // Excludes 'Competent' grades
            ->whereNotNull('task_grade')             // Ensures only graded tasks are included
            ->get();
        $gradedDefTasksMap = $deferredLessonsTasks->mapWithKeys(function ($item) {
            return [ $item->def_lesson_id . '_' . $item->task_id => true ];
        });     
        // dd($gradedDefTaskIds);           
        if ($currentUser->is_owner == 1) {
            // Super Admin: Get all data
            $instructors = User::whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Instructor%');
            })->with('roles')->get();
        }else {
            $instructors = User::where('ou_id', $currentUser->ou_id)
            ->where(function ($query) {
                $query->whereNull('is_admin')->orWhere('is_admin', false);
            })
            ->whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Instructor%');
            })->with('roles')->get();
        }

        $course = Courses::with('resources')->find($trainingEvent->course_id);
        $resources = $course ? $course->resources : collect(); // avoids null access
        // dd($deferredLessons);
        return view('trainings.show', compact(
            'trainingEvent', 
            'student', 
            'overallAssessments', 
            'eventLessons',
            'taskGrades',
            'competencyGrades',
            'trainingFeedbacks',
            'isGradingCompleted',
            'resources',
            'instructors',
            'defTasks',
            'defLessonTasks',
            'deferredTaskIds',
            'gradedDefTasksMap'
        ));
    }

    public function createGrading(Request $request)
    {
        //Validate the incoming data:
        $request->validate([
            'event_id'             => 'required|integer|exists:training_events,id',
            'task_grade'           => 'nullable|array',
            'task_grade.*.*' => ['nullable', function ($attribute, $value, $fail) {
                $allowedValues = ['Incomplete', 'Further training required', 'Competent'];
                if (!in_array($value, $allowedValues) && !is_numeric($value)) {
                    $fail('The ' . str_replace('_', ' ', $attribute) . ' must be a valid grade or a number.');
                }
            }],
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
            //Get the student ID being graded from the hidden fields:
            $gradedStudentId = $request->input('tg_user_id');
            $gradedStudentIdForComp = $request->input('cg_user_id');

            //Clear previous deferred items (optional: prevent duplicates)
            //DeferredItem::where('event_id', $event_id)->where('created_by', $evaluatorId)->delete();
            
            // Store or update Task Grading (for sublessons):
            if ($request->has('task_grade')) {
                foreach ($request->input('task_grade') as $lesson_id => $subLessons) {
                    foreach ($subLessons as $sub_lesson_id => $task_grade) {
                        // Update or create the normal task grade
                        TaskGrading::updateOrCreate(
                            [
                                'event_id'      => $event_id,
                                'lesson_id'     => $lesson_id,
                                'sub_lesson_id' => $sub_lesson_id,
                                'user_id'       => $gradedStudentId,
                            ],
                            [
                                'task_grade'    => $task_grade,
                                'task_comment'  => $request->input("task_comments.$lesson_id.$sub_lesson_id", null),
                                'created_by'    => auth()->user()->id,
                            ]
                        );
                    
                        if (strtolower($task_grade) == 'incomplete' || strtolower($task_grade) == 'further training required') {    
                            // Check if task already exists in def_lesson_tasks
                            $alreadyDeferred = DefLessonTask::where([
                                'event_id' => $event_id,
                                'user_id'  => $gradedStudentId,
                                'task_id'  => $sub_lesson_id,
                            ])->exists();

                            // Only insert into def_tasks if not found in def_lesson_tasks
                            if (!$alreadyDeferred) {
                                DefTask::firstOrCreate(
                                    [
                                        'event_id' => $event_id,
                                        'user_id'  => $gradedStudentId,
                                        'task_id'  => $sub_lesson_id,
                                    ],
                                    [
                                        'created_by' => auth()->user()->id,
                                    ]
                                );
                            }
                        }
                    }

                    $totalSubLessons = SubLesson::where('lesson_id', $lesson_id)->count();

                    $gradedSubLessons = TaskGrading::where([
                        'event_id'  => $event_id,
                        'lesson_id' => $lesson_id,
                        'user_id'   => $gradedStudentId,    
                    ])
                    ->whereNotNull('task_grade')
                    ->where('task_grade', '!=', '')
                    ->count(); 

                    if ($totalSubLessons > 0 && $totalSubLessons == $gradedSubLessons) {
                        TrainingEventLessons::where('training_event_id', $event_id)
                            ->where('lesson_id', $lesson_id)
                            ->update(['is_locked' => 1]);
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


    public function updateCompGrade(Request $request)
    {
        $request->validate([
            'event_id' => 'required|integer',
            'lesson_id' => 'required|integer',
            'user_id' => 'required|integer',
            'code' => 'required|string',
        ]);

        $eventId = $request->event_id;
        $lessonId = $request->lesson_id;
        $userId = $request->user_id;
        $code = $request->code;
        $column = $code . '_grade';

        // Check if the attribute is fillable or exists
        if (!in_array($column, (new CompetencyGrading)->getFillable())) {
            return response()->json(['error' => 'Invalid competency code.'], 400);
        }

        // Find the competency grading entry
        $grading = CompetencyGrading::where('event_id', $eventId)
            ->where('lesson_id', $lessonId)
            ->where('user_id', $userId)
            ->first();

        if (!$grading) {
            return response()->json(['error' => 'Competency grading not found.'], 404);
        }

        // Set the specified competency grade to null
        $grading->$column = null;
        $grading->save();

        return response()->json(['success' => true, 'message' => 'Competency grade updated.']);
    }



    public function getStudentGrading(Request $request, $event_id)
    {
        $eventId = decode_id($event_id);
        $trainingEvent = TrainingEvents::select('ou_id', 'student_id')->findOrFail($eventId);

        $ouId = $trainingEvent->ou_id;
        $userId = $trainingEvent->student_id;
    
        $event = TrainingEvents::where('ou_id', $ouId)
            ->where('id', decode_id($event_id))
            ->whereHas('taskGradings', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with([
                'taskGradings' => function ($query) use ($userId) {
                    $query->where('user_id', $userId)
                        ->with('lesson:id,lesson_title,grade_type') // Load only lesson_name
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
                'instructor:id,fname,lname', // Load only instructor name
                'documents:id,training_event_id,course_document_id,file_path', // make sure these fields exist
                'documents.courseDocument:id,document_name', // 🆕 Add this to load document name from course_documents
            ])
            ->first(); // Use first() to get a single event

            // ✅ Check if event exists
            if (!$event) {
                return redirect()
                    ->route('training.index')
                    ->with('error', 'Training event or grading not found.');
            }    

        $defLessonGrading = DefLessonTask::with(['task', 'defLesson'])
            ->where('event_id', $event->id)
            ->where('user_id', auth()->id())
            // ->whereNotNull('task_grade') // Only show newly graded items
            ->get()
            ->groupBy('def_lesson_id');

            if ($event) {
            // return redirect()->route('training.index')->with('message', 'Grading not found for this event.');
            $event->student_feedback_submitted = $event->trainingFeedbacks()->where('user_id', auth()->user()->id)->exists();    
            // abort(404, 'Training Event not found.');
            }    
        
        // dd($event->student_feedback_submitted);
        // dd($event->course->enable_feedback);
    
        return view('trainings.grading-list', compact('event','defLessonGrading'));    
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
    
    public function downloadLessonReport($event_id, $lesson_id)
    {
        $userId = auth()->id();
    
        $event = TrainingEvents::with([
            'course:id,course_name',
            'orgUnit:id,org_unit_name,org_logo',
            'instructor:id,fname,lname',
            'student:id,fname,lname',
            'resource:id,name,type,class,registration',
            'eventLessons' => function ($query) use ($lesson_id) {
                $query->where('lesson_id', $lesson_id);
            },
            'taskGradings' => function ($query) use ($userId, $lesson_id) {
                $query->where('user_id', $userId)
                      ->where('lesson_id', $lesson_id)
                      ->with('subLesson:id,title,grade_type');
            },
            'competencyGradings' => function ($query) use ($userId, $lesson_id) {
                $query->where('user_id', $userId)
                      ->where('lesson_id', $lesson_id);
            },
            'overallAssessments' => function ($query) use ($userId) {
                $query->where('user_id', $userId);
            },
            'eventLessons.instructor:id,fname,lname',
            'eventLessons.resource:id,id,name,type,class,registration',
        ])->findOrFail($event_id);
    
        $eventLesson = $event->eventLessons->first();
    
        if (!$eventLesson) {
            abort(404, 'Lesson not found for this training event.');
        }
    
        $lesson = $eventLesson->lesson;
    
        $pdf = PDF::loadView('trainings.lesson-report', [ 
            'event' => $event,
            'lesson' => $lesson,
            'eventLesson' => $eventLesson,
        ]);
    
        $filename = 'Lesson_Report_' . Str::slug($lesson->lesson_title) . '.pdf';
         return $pdf->download($filename); 
    }    

    public function uploadDocuments(Request $request, TrainingEvents $trainingEvent)
    {
        //dd($request->all());
        $request->validate([
            'training_event_documents' => 'required|array|min:1',
            'training_event_documents.*' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:10240', // Add more formats if needed
        ], [], [
            'training_event_documents.*' => 'Training Event Document'
        ]);
        
        // Loop through the uploaded files
        foreach ($request->file('training_event_documents', []) as $courseDocId => $file) {
            // If file exists in the form input
            // dd($file);
            if ($file) {
                // Get the original file name
                $originalName = $file->getClientOriginalName();

                // dd($originalName);
                // Generate a unique filename with the original file name
                $filename = time() . '_' . $originalName;

                // Store the file in the 'public' disk (change to your desired location)
                $path = $file->storeAs('training_event_documents', $filename, 'public');

                // Check if the document has already been uploaded for this course_document_id
                $existing = $trainingEvent->documents()
                    ->where('course_document_id', $courseDocId)
                    ->first();

                if ($existing) {
                    // Optionally, delete the old file before updating (to avoid leftover files)
                    Storage::disk('public')->delete($existing->file_path);

                    // Update the existing document entry
                    $existing->update([
                        'file_path' => $path,
                    ]);
                } else {
                    // Create a new document record in the database
                    $trainingEvent->documents()->create([
                        'course_document_id' => $courseDocId,
                        'file_path' => $path,
                    ]);
                }
            }
        }

        // Redirect back with a success message
        return redirect()->back()->with('success', 'Documents uploaded successfully.');
    }


    public function generateCertificate($event)
    {
        $eventId = decode_id($event); // decode the ID
        $event = TrainingEvents::with('eventLessons','recommendedInstructor')->findOrFail($eventId);
        $student = $event->student;
        $course = $event->course;
        $firstLesson = $event->firstLesson;

        // Calculate Hours of Groundschool (sum of hours_credited where lesson type is groundschool)
        $hoursOfGroundschoolMinutes = $event->eventLessons
            ->filter(function ($lesson) {
                return $lesson->lesson_type === 'groundschool';
            })
            ->sum(function ($lesson) {
                // safely convert hours_credited string to minutes
                $time = $lesson->hours_credited ?? '00:00:00';
                [$hours, $minutes, $seconds] = array_pad(explode(':', $time), 3, 0);
                return ($hours * 60) + $minutes; // ignore seconds for simplicity
            });

        // Convert to "Xhrs Ymins"
        $hoursOfGroundschool = floor($hoursOfGroundschoolMinutes / 60) . 'hrs ' . ($hoursOfGroundschoolMinutes % 60) . 'mins';

        // Hours, Flight and Simulator (from TrainingEvents table)
        $flightTime = $event->total_time ?? 0; // e.g., "10:00"
        $simulatorTime = $event->simulator_time ?? 0; // e.g., "2.00"

        $pdf = PDF::loadView('trainings.course-completion-certificate', [
            'event' => $event,
            'student' => $student,
            'course' => $course,
            'firstLesson' => $firstLesson,
            'hoursOfGroundschool' => $hoursOfGroundschool,
            'flightTime' => $flightTime,
            'simulatorTime' => $simulatorTime,
            'recommendedBy' => $event->recommendedInstructor,
        ]);

        $filename = 'Certificate_' . Str::slug($student->fname . ' ' . $student->lname) . '.pdf';

        return $pdf->download($filename);
    }


    public function storeDeferredLessons(Request $request)
    {
        // Validate the incoming request data
        $validatedData = $request->validate([   
            'event_id'      => 'required|integer|exists:training_events,id',
            'lesson_title'  => 'required|string|max:255',
            'item_ids'      => 'required|array',
            'item_ids.*'    => 'integer|exists:sub_lessons,id',
            'lesson_date'   => 'required|date',
            'start_time'    => 'required|date_format:H:i',
            'end_time'      => 'required|date_format:H:i|after:start_time',
            'resource_id'   => 'required|integer|exists:resources,id',
            'instructor_id' => 'required|integer|exists:users,id',
            'std_id'        => 'required|integer|exists:users,id',
            'departure_airfield'   => 'nullable|string|max:4',
            'destination_airfield' => 'nullable|string|max:4',
        ], [], [
            'item_ids'     => 'Tasks',
            'resource_id'  => 'Resource',
            'instructor_id'=> 'Instructor'
        ]);

        $eventId = $validatedData['event_id'];
        $studentId = $validatedData['std_id'];
        $authId = auth()->id();

        // Step 1: Delete matching records from def_tasks
        DefTask::where('event_id', $eventId)
            ->where('user_id', $studentId)
            ->whereIn('task_id', $validatedData['item_ids'])
            ->delete();

        // Step 2: Create def_lesson entry
        $defLesson = DefLesson::create([
            'event_id'      => $eventId,
            'user_id'       => $studentId,
            'task_ids'      => $validatedData['item_ids'], // Optional if not needed
            'instructor_id' => $validatedData['instructor_id'],
            'resource_id'   => $validatedData['resource_id'],
            'lesson_title'  => $validatedData['lesson_title'],
            'lesson_date'   => $validatedData['lesson_date'],
            'start_time'    => $validatedData['start_time'],
            'end_time'      => $validatedData['end_time'],
            'departure_airfield'   => $validatedData['departure_airfield'],
            'destination_airfield' => $validatedData['destination_airfield'],
            'created_by'    => $authId,
        ]);

        // Step 3: Create def_lesson_tasks entries
        foreach ($validatedData['item_ids'] as $index => $taskId) {
            DefLessonTask::create([
                'def_lesson_id' => $defLesson->id,
                'event_id'      => $eventId,
                'user_id'       => $studentId,
                'task_id'       => $taskId,
                'created_by'    => $authId,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Deferred lesson and tasks stored successfully.'
        ], 201);
    }    
    
    public function storeDefGrading(Request $request)
    { 
         $request->validate([
            'event_id' => 'required|integer|exists:training_events,id',
            'task_grade_def' => 'required|array',
            'task_grade_def.*' => 'required|string|in:Competent,Incomplete,Further training required',
            'task_comment_def' => 'nullable|array',
            'task_comment_def.*' => 'nullable|string|max:1000',
        ]);

        $event_id = $request->input('event_id');
        $user_id = $request->input('tg_user_id');

        foreach ($request->input('task_grade_def') as $task_id => $task_grade) {
            $task_comment = $request->input("task_comment_def.$task_id", null);

            // Update grading in def_lesson_tasks
            DefLessonTask::where('id', $task_id)->update([
                'task_grade'   => $task_grade,
                'task_comment' => $task_comment,
            ]);

            // If task is not 'Competent', insert/update into def_tasks
            if (strtolower($task_grade) !== 'competent') {
                $defTask = DefLessonTask::find($task_id);

                if ($defTask) {
                    DefTask::firstOrCreate(
                        [
                            'event_id' => $event_id,
                            'user_id'  => $defTask->user_id,
                            'task_id'  => $defTask->task_id,
                        ],
                        [
                            'created_by' => auth()->id(),
                        ]
                    );
                }
            }
        }

        Session::flash('message', 'Student grading updated successfully.');

        return response()->json([
            'success' => true,
            'message' => 'Deferred Task Grading saved successfully.'
        ]);
    }

    public function unlockLesson(Request $request)
    {
        $user = auth()->user();
        if ($user->is_admin!=1) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $eventId = $request->input('event_id');
        $lessonId = $request->input('lesson_id');

        $eventLesson = TrainingEventLessons::where('training_event_id', $eventId)
            ->where('lesson_id', $lessonId)
            ->first();

        if (!$eventLesson) {
            return response()->json(['success' => false, 'message' => 'Lesson not found'], 404);
        }

        $eventLesson->is_locked = 0;
        $eventLesson->save();

        return response()->json(['success' => true]);
    }


    public function endCourse(Request $request)
    {
        $request->validate([
            'course_end_date' => 'required|date|before_or_equal:today',
            'recommended_by_instructor_id' => 'nullable|exists:users,id', // validate if provided
        ]);

        $id = decode_id($request->event_id);
        $event = TrainingEvents::findOrFail($id);

        if (auth()->user()->is_admin != 1) {
            abort(403, 'Unauthorized action.');
        }

        if ($event->is_locked) {
            return back()->withErrors(['error' => 'Course already ended.']);
        }

        $event->update([
            'course_end_date' => $request->course_end_date,
            'recommended_by_instructor_id' => $request->recommended_by_instructor_id ?? null,
            'is_locked' => 1,
        ]);

        return redirect()->route('training.index')->with('message', 'Course has been ended and locked.');
    }


    
}
