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
use App\Models\UserDocument;
use App\Models\DeferredGrading;
use App\Models\CbtaGrading;
use App\Models\ExaminerGrading;
use App\Models\TrainingEventLog;
use App\Models\TrainingEventReview;
use App\Models\UserOpcRating;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use PDF;
use Illuminate\Support\Str;
use Auth;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;


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
            'eventLessons',
            'defLessons',
            'eventLessons.lesson:id,enable_cbta',
            'eventLessons.lesson.subLessons:id,lesson_id,title',
            'overallAssessments',
        ];

        // $trainingEvents_instructor = TrainingEvents::with($trainingEventsRelations)->get();

        // echo "<pre>";
        //     print_r($trainingEvents_instructor);
        // echo "</pre>";
        // dd();


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
                ->where('entry_source', null)
                ->withCount([
                    'taskGradings',
                    'competencyGradings'
                ])
                ->orderByDesc('event_date')
                ->get();

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
              //  dd($currentUser->id);
                $trainingEvents = $trainingEventsQuery
                    ->where('student_id', $currentUser->id)
                    // ->where(function ($query) use ($currentUser) {
                    //     $query->whereHas('taskGradings', function ($q) use ($currentUser) {
                    //         $q->where('user_id', $currentUser->id);
                    //      })->orWhereHas('competencyGradings', function ($q) use ($currentUser) {
                    //         $q->where('user_id', $currentUser->id);
                    //     })->orWhereHas('overallAssessments', function ($q) use ($currentUser) {
                    //         $q->where('user_id', $currentUser->id);
                    //      });
                    // })
                    ->get();
                $trainingEvents_instructor = [];
               // dd($trainingEvents);
                 
               

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
                ->where('entry_source', null)
                ->with($trainingEventsRelations)
                ->withCount(['taskGradings', 'competencyGradings'])
                ->get();
              

             

            $trainingEvents_instructor = TrainingEvents::where('ou_id', $currentUser->ou_id)
                ->where('entry_source', "instructor")
                //->with($trainingEventsRelations)
                ->withCount(['taskGradings', 'competencyGradings'])
                ->get();
        }
        // Attach instructor lists to each training event
        // $trainingEvents->each(function ($event) {
        //     if (!$event->relationLoaded('eventLessons', 'defLessons') || !($event->eventLessons instanceof \Illuminate\Support\Collection)) {
        //         $event->lesson_instructors = collect();
        //         $event->lesson_instructor_users = collect();
        //         $event->last_lesson_instructor_id = null;
        //         $event->last_lesson_instructor = null;
        //         return;
        //     }
        //     // Get unique instructor IDs from event lessons
        //     $event->lesson_instructors = $event->eventLessons
        //         ->pluck('instructor_id')
        //         ->filter()
        //         ->unique()
        //         ->values();

        //     $defLessonInstructorIds = $event->defLessons
        //         ->pluck('instructor_id')
        //         ->filter();

        //     $event->lesson_instructors = $event->lesson_instructors
        //         ->merge($defLessonInstructorIds)
        //         ->unique()
        //         ->values();

                
        //     // Load instructor users
        //     $event->lesson_instructor_users = User::whereIn('id', $event->lesson_instructors)->get();


        //     echo "<pre>";
        //         print_r($event->defLessons);
        //     echo "</pre>";

        //     dd();

        //     // Determine the last lesson instructor (by Id)
        //     $lastLesson = $event->eventLessons->sortByDesc('id')->first();
        //     $event->last_lesson_instructor_id = $lastLesson ? $lastLesson->instructor_id : null;

        //     // Optional: preload the actual user object
        //     $event->last_lesson_instructor = $event->lesson_instructor_users->firstWhere('id', $event->last_lesson_instructor_id);
        // });



        // $trainingEvents->each(function ($event) {

            

        //     // Always initialize
        //     $event->lesson_instructors = collect();
        //     $event->lesson_instructor_users = collect();
        //     $event->last_lesson_instructor_id = null;
        //     $event->last_lesson_instructor = null;

        //     // Event lesson instructors
        //     $eventLessonInstructorIds = $event->eventLessons
        //         ->pluck('instructor_id')
        //         ->filter();

        //     // Def lesson instructors
        //     $defLessonInstructorIds = $event->defLessons
        //         ->pluck('instructor_id')
        //         ->filter();

        //     // Merge both
        //     $event->lesson_instructors = $eventLessonInstructorIds
        //         ->merge($defLessonInstructorIds)
        //         ->unique()
        //         ->values();


        //     // Load users if any instructors exist
        //     if ($event->lesson_instructors->isNotEmpty()) {
        //         $event->lesson_instructor_users = User::whereIn('id', $event->lesson_instructors)->get();
        //     }

        //     // Last lesson instructor (eventLessons only)
        //     // $lastLesson = $event->eventLessons->sortByDesc('id')->first();
        //     // $event->last_lesson_instructor_id = $lastLesson?->instructor_id;

        //     // $event->last_lesson_instructor = $event->lesson_instructor_users->firstWhere('id', $event->last_lesson_instructor_id);

        //     // Try EVENT lessons first
        //     $lastEventLessonInstructorId = $event->eventLessons
        //         ->whereNotNull('instructor_id')
        //         ->sortByDesc('id')
        //         ->value('instructor_id');

        //     // Fallback to DEF lessons
        //     $lastDefLessonInstructorId = $event->defLessons
        //         ->whereNotNull('instructor_id')
        //         ->sortByDesc('id')
        //         ->value('instructor_id');

        //     // Final resolved instructor
        //     $event->last_lesson_instructor_id =
        //         $lastEventLessonInstructorId
        //         ?? $lastDefLessonInstructorId
        //         ?? null;

        //     // Attach user (safe)
        //     $event->last_lesson_instructor = $event->lesson_instructor_users->firstWhere('id', $event->last_lesson_instructor_id);


        //     // echo "<pre>";
        //     //     print_r($event->last_lesson_instructor_id);
        //     // echo "</pre>";

        //     // dd();
        // });

        $trainingEvents->each(function ($event) {

            $event->lesson_instructors = collect();
            $event->lesson_instructor_users = collect();
            $event->last_lesson_instructor_id = null;
            $event->last_lesson_instructor = null;

            $eventLessonInstructorIds = $event->eventLessons
                ->pluck('instructor_id')
                ->filter();

            $defLessonInstructorIds = $event->defLessons
                ->pluck('instructor_id')
                ->filter();

            $event->lesson_instructors = $eventLessonInstructorIds
                ->merge($defLessonInstructorIds)
                ->unique()
                ->values();

            $lastEventLessonInstructorId = $event->eventLessons
                ->whereNotNull('instructor_id')
                ->sortByDesc('id')
                ->value('instructor_id');

            $lastDefLessonInstructorId = $event->defLessons
                ->whereNotNull('instructor_id')
                ->sortByDesc('id')
                ->value('instructor_id');

            $event->last_lesson_instructor_id =
                $lastEventLessonInstructorId
                ?? $lastDefLessonInstructorId
                ?? null;

            if ($event->last_lesson_instructor_id) {
                $event->lesson_instructors = $event->lesson_instructors
                    ->push($event->last_lesson_instructor_id)
                    ->unique()
                    ->values();
            }

            if ($event->lesson_instructors->isNotEmpty()) {
                $event->lesson_instructor_users =
                    User::whereIn('id', $event->lesson_instructors)->get();
            }

            $event->last_lesson_instructor =
                $event->lesson_instructor_users
                    ->firstWhere('id', (int) $event->last_lesson_instructor_id);

            // echo "<pre>";
            //     print_r($event->lesson_instructor_users);
            // echo "</pre>";

            // dd();
        });


       // dd($trainingEvents);
        return view('trainings.index', compact('groups', 'courses', 'instructors', 'organizationUnits', 'trainingEvents', 'resources', 'students', 'trainingEvents_instructor'));
    }

    public function getOrgStudentsInstructorsResources(Request $request, $ou_id)
    {
        // $ou_id = ($ou_id)? $ou_id: auth()->user()->ou_id;
        $students = User::where('ou_id', $ou_id)
            ->where(function ($query) {
                $query->whereNull('is_admin')->orWhere('is_admin', false);
            })
            ->whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Student%');
            })->with('roles')->get();

        $instructors = User::where('ou_id', $ou_id)->where('is_activated', 0)
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

    public function getStudentLicenseNumberAndCourses(Request $request, $user_id, $ou_id)
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

    public function getInstructorLicenseNumber(Request $request, $instructor_id, $selectedCourseId)
    {

        $instructor = User::with('documents')->find($instructor_id);

        $course = Courses::with(['courseLessons', 'resources'])->find($selectedCourseId);
        $ato_num = strtolower($course->ato_num);

        if (str_contains($ato_num, 'uk')) {
            $licence = $instructor->licence;
        } elseif (str_contains($ato_num, 'easa')) {
            $licence = $instructor->documents->licence_2;
        } else {
            // $licence = $instructor->licence ?: $instructor->documents->licence_2;
            $licence = $instructor->licence ?: ($instructor->documents?->licence_2 ?? null);
        }

        if ($instructor) {
            return response()->json([
                'success' => true,
                'instructor_licence_number' => $licence
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
       
        $student_id = $request->selectedStudentId;
        $ou_id = $request->ou_id ?? Auth::user()->ou_id;

        $course = Courses::with(['courseLessons', 'resources'])->find($request->course_id);
     

        $get_licence = UserDocument::where('user_id', $student_id)->select('licence', 'licence_2')->first();

        $uk_licence = $get_licence->licence ?? null;
        $easa_licence = $get_licence->licence_2 ?? null;
        $ato_num = strtolower($course->ato_num) ?? null;

        if (str_contains($ato_num, 'uk')) {
            $instructors = User::with(['documents', 'roles'])->where('is_activated', 0)
                ->whereHas('documents', function ($query) {
                    $query->whereNotNull('licence')->where('licence', '!=', '');
                })
                ->whereHas('roles', function ($query) {
                    $query->where('role_name', 'like', '%Instructor%');
                })
                ->get();

            // attach licence number
            $instructors->each(function ($inst) {
                $inst->instructor_license_number = $inst->documents->licence ?? '';
            });

            $licence_type = [
                'flag' => 'uk',
                'number' => $uk_licence,
            ];
        } elseif (str_contains($ato_num, 'easa')) {
            $instructors = User::with(['documents', 'roles'])
                ->whereHas('documents', function ($query) {
                    $query->whereNotNull('licence_2')->where('licence_2', '!=', '');
                })
                ->whereHas('roles', function ($query) {
                    $query->where('role_name', 'like', '%Instructor%');
                })
                ->get();

            $instructors->each(function ($inst) {
                $inst->instructor_license_number = $inst->documents->licence_2 ?? '';
            });

            $licence_type = [
                'flag' => 'easa',
                'number' => $easa_licence,
            ];
        } else {
            $instructors = User::with(['documents', 'roles'])
                ->whereHas('roles', function ($query) {
                    $query->where('role_name', 'like', '%Instructor%');
                })->get();

            $instructors->each(function ($inst) {
                if (!empty($inst->documents->licence)) {
                    $inst->instructor_license_number = $inst->documents->licence;
                } elseif (!empty($inst->documents->licence_2)) {
                    $inst->instructor_license_number = $inst->documents->licence_2;
                } else {
                    $inst->instructor_license_number = '';
                }
            });

            $licence_type = [
                'flag' => 'generic',
                'number' => !empty($uk_licence) ? $uk_licence : $easa_licence,
            ];
        }

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' => 'Course not found.'
            ]);
        }

        $all_ou_instructor = User::where('ou_id', $ou_id)->where('is_activated', 0)
                            ->where(function ($query) {
                                $query->whereNull('is_admin')->orWhere('is_admin', false);
                            })
                            ->whereHas('roles', function ($query) {
                                $query->where('role_name', 'like', '%Instructor%');
                            })->with('roles')->get();

          
      
        return response()->json([
            'success'     => true,
            'lessons'     => $course->courseLessons,
            'resources'   => $course->resources,
            'licence'     => $licence_type,
            'instructors' => $instructors,
            'all_ou_instructor' => $all_ou_instructor,
            'course'         => $course
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
            'opc_validity' => $request->opc_validity_months,
            'opc_extend' => $request->opc_extend_eom,
            'total_time' => $request->total_time,
            'simulator_time' => $request->total_simulator_time ?? '00:00',
            'std_license_number' => $request->std_licence_number,
            'ou_id' => auth()->user()->is_owner ? $request->ou_id : auth()->user()->ou_id,
            'entry_source' => $request->entry_source,
            'rank'     => $request->rank ?? null
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
                'operation1'    => $lesson['operation_1'] ?? null,
                'role1'         => $lesson['role_1'] ?? null,
                'operation2'    => $lesson['operation_2'] ?? null,
                'role2'         => $lesson['role_2'] ?? null,

            ]);
        }
        $user = User::find($request->student_id);
        if ($user) {   
                $user->update(['is_activated' => 0]);
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
        $trainingEvent = TrainingEvents::with('eventLessons.lesson', 'course:id,course_name,course_type,duration_value,duration_type,groundschool_hours,simulator_hours,ato_num,enable_mp_lifus')->findOrFail(decode_id($request->eventId));
        $atoNum = strtolower($trainingEvent->course->ato_num);

        $isUK   = str_contains($atoNum, 'uk');
        $isEASA = str_contains($atoNum, 'easa');

        $ukLicence   = isset($trainingEvent->studentDocument->licence)
            ? trim($trainingEvent->studentDocument->licence) : '';
        $easaLicence = isset($trainingEvent->studentDocument->licence_2)
            ? trim($trainingEvent->studentDocument->licence_2) : '';

        // Helper to pick the first available licence or N/A
        $getLicence = function () use ($ukLicence, $easaLicence) {
            return !empty($ukLicence) ? $ukLicence
                : (!empty($easaLicence) ? $easaLicence : '');
        };

        if ($isUK && !$isEASA) {
            $label = "License Number (UK)";
            $student_licence = $getLicence();
        } elseif ($isEASA && !$isUK) {
            $label = "License Number (EASA)";
            $student_licence = $getLicence();
        } elseif ($isUK && $isEASA) {
            if (empty($ukLicence) && empty($easaLicence)) {
                $label = "License";
                $student_licence = $getLicence();
            } else {
                $label = "License Number (Generic)";
                $student_licence = implode(', ', array_filter([$ukLicence, $easaLicence]));
            }
        } else {
            if (empty($ukLicence) && empty($easaLicence)) {
                $label = "License";
                $student_licence = $getLicence();
            } else {
                $label = "License Number (Generic)";
                $student_licence = implode(', ', array_filter([$ukLicence, $easaLicence]));
            }
        }

        $user_id =  $trainingEvent->student_id;
        $user = User::with('documents')->find($user_id);
        $ou_id = $trainingEvent['ou_id'];
        $instructors = User::where('ou_id', $ou_id)
            ->where(function ($query) {
                $query->whereNull('is_admin')->orWhere('is_admin', false);
            })
            ->whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Instructor%');
            })->with('roles')->get();

          //  dd($trainingEvent);

        if ($trainingEvent) {
            return response()->json(['success' => true, 'trainingEvent' => $trainingEvent, 'instructors' => $instructors, 'licence_number' => $student_licence]);
        } else {
            return response()->json(['success' => false, 'message' => 'Training event Not found']);
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
            'total_time' => 'nullable',
            'total_simulator_time' => 'nullable',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->is_owner == 1 && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
            'lesson_data' => 'required|array|min:1',
       
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
            'opc_validity' => $request->edit_opc_validity_months,
            'opc_extend' => $request->edit_opc_extend_eom,
            'lesson_ids' => json_encode(array_column($lessonData, 'lesson_id')),
            'total_time' => $request->total_time ?? null,
            'simulator_time' => $request->total_simulator_time ?? '00:00',
            'std_license_number' => $request->std_licence_number ?? null,
            'rank'              => $request->rank ?? null
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
                $creditMinutes = $data['homestudy_time'] ?? 0;
                if (is_numeric($creditMinutes)) {
                    $creditMinutes *= 60;
                } else {
                    $creditMinutes  = 0;
                }
               
                // $creditMinutes = 480;
                $start = '00:00';
                $end = '00:00';
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
                    'resource_id' => $data['resource_id'] ?? Null,
                    'lesson_date' => $data['lesson_date'],
                    'start_time' => $start,
                    'end_time' => $end,
                    'departure_airfield' => ($lessonType === 'groundschool' && in_array($resourceName, ['Classroom', 'Homestudy'])) ? null : strtoupper($data['departure_airfield']),
                    'destination_airfield' => ($lessonType === 'groundschool' && in_array($resourceName, ['Classroom', 'Homestudy'])) ? null : strtoupper($data['destination_airfield']),
                    'instructor_license_number' => $data['instructor_license_number'] ?? null,
                    'hours_credited' => gmdate("H:i", $creditMinutes * 60),
                    'operation1'    => $data['operation_1'] ?? null,
                    'role1'         => $data['role_1'] ?? null,
                    'operation2'    => $data['operation_2'] ?? null,
                    'role2'         => $data['role_2'] ?? null,
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
            'course:id,course_name,enable_mp_lifus,course_type,duration_value,duration_type,groundschool_hours,simulator_hours,ato_num,instructor_cbta,examiner_cbta,enable_mp_lifus',
            'course.documents',
            'group:id,name,user_ids',
            'instructor:id,fname,lname',
            'student:id,fname,lname,licence',
            'trainingQuizzes' => function ($q) {
                $q->select('id', 'trainingevent_id', 'quiz_id', 'student_id', 'is_active');
            },
            'resource:id,name',
            'eventLessons' => function ($q) {
                $q->orderBy('position', 'asc'); 
            },
            'eventLessons.lesson:id,lesson_title,enable_cbta,grade_type,lesson_type,custom_time_id,position,instructor_cbta,examiner_cbta',
            'eventLessons.instructor:id,fname,lname', 
            'eventLessons.resource:id,name',
            'trainingFeedbacks.question',
            'documents',
            'studentDocument'
        ])->find(decode_id($event_id));
       // dd($trainingEvent);


        if (!$trainingEvent) {
            return abort(404, 'Training Event not found');
        }

      //  dump($eventLessons);
        if (hasUserRole($currentUser, 'Instructor') && empty($currentUser->is_admin)) { 
            $eventLessons = $trainingEvent->eventLessons->map(function ($lesson) use ($currentUser) {
                // Add a flag to indicate if this is instructor's own lesson
                $lesson->is_my_lesson = ($lesson->instructor_id == $currentUser->id);
                return $lesson;
            });
        } else {
                $eventLessons = $trainingEvent->eventLessons->map(function ($lesson) {
                    $lesson->is_my_lesson = true;
                    return $lesson;
                });
        }

        $eventLessons = $eventLessons->reject(function ($lesson) {
            return $lesson?->lesson?->quizzes?->count() > 0;
        });

        $courselessons = CourseLesson::where('course_id', $trainingEvent->course_id)->get();

        // if (hasUserRole($currentUser, 'Instructor') && empty($currentUser->is_admin)) { 
        //     $eventLessons = $trainingEvent->eventLessons->filter(function ($lesson) use ($currentUser) {
        //         return $lesson->instructor_id == $currentUser->id;
        //     })->values();
        // } else {
        //     $eventLessons = $trainingEvent->eventLessons; 
        // }

        $student = $trainingEvent->student;
        $lessonIds = $eventLessons->pluck('lesson_id')->filter()->unique();




        //Get task gradings (sublesson grades and comments)
        if (!empty($student)) {
            $taskGrades = TaskGrading::where('user_id', $student->id)
                ->where('event_id', $trainingEvent->id)
                ->whereIn('lesson_id', $lessonIds)
                ->get()
                ->keyBy(fn($item) => $item->lesson_id . '_' . $item->sub_lesson_id);

            //Get competency grades (competency area grades and comments)


            $def_lesson_id = DefLessonTask::where('event_id', decode_id($event_id))->pluck('def_lesson_id');


            $competencyGrades = CompetencyGrading::where('user_id', $student->id)
                ->where('event_id', $trainingEvent->id)
                ->whereIn('lesson_id', $lessonIds)
                ->get()
                ->groupBy('lesson_id');



            $def_grading  =  DeferredGrading::where('user_id', $student->id)
                ->where('event_id', $trainingEvent->id)
                ->whereIn('deflesson_id', $def_lesson_id)
                ->get()
                ->groupBy('deflesson_id');
        } else {
            $taskGrades = collect(); // instead of ''
            $competencyGrades = collect(); // instead of ''
            $def_grading = collect();
        }




        //Optional: Also pass overall assessments if you need them
        $overallAssessments = OverallAssessment::where('event_id', $trainingEvent->id)
            ->where('user_id', $student->id ?? null)
            ->first();

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

            -- Subquery join for latest def_lesson_tasks (ignore soft-deleted)
            LEFT JOIN (
                SELECT dlt.*
                FROM def_lesson_tasks dlt
                INNER JOIN (
                    SELECT event_id, user_id, task_id, MAX(id) AS max_id
                    FROM def_lesson_tasks
                    WHERE deleted_at IS NULL
                    GROUP BY event_id, user_id, task_id
                ) latest_dlt
                ON dlt.id = latest_dlt.max_id
                WHERE dlt.deleted_at IS NULL
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

        $deferredLessons = DefLesson::with(['student', 'instructor', 'instructor.documents', 'resource', 'defLesson', 'deftasks.subddddLesson.courseLesson'])
            ->where('event_id', $trainingEvent->id)
            ->where('lesson_type', "deferred")
            ->orderBy('id', 'desc')
            ->get();

        $customLessons = DefLesson::with(['student', 'instructor', 'instructor.documents', 'resource', 'defLesson', 'deftasks.subddddLesson.courseLesson'])
            ->where('event_id', $trainingEvent->id)
            ->where('lesson_type', "custom")
            ->orderBy('id', 'desc')
            ->get();



        $defLessonTasks = DefLessonTask::with(['user', 'defLesson.instructor', 'defLesson.instructor.documents', 'defLesson.resource', 'task'])
            ->where('event_id', $trainingEvent->id)
            ->whereRelation('defLesson', 'lesson_type', 'deferred')
            ->get();

        $customLessonTasks = DefLessonTask::with(['user', 'defLesson.instructor', 'defLesson.instructor.documents', 'defLesson.resource', 'task.courseLesson.course'])
            ->where('event_id', $trainingEvent->id)
            ->whereRelation('defLesson', 'lesson_type', 'custom')
            // ->orderBy('def_lesson_id', 'desc')
            ->get();
      


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
            return [$item->def_lesson_id . '_' . $item->task_id => true];
        });
                
        if ($currentUser->is_owner == 1) {
            // Super Admin: Get all data
            $instructors = User::whereHas('roles', function ($query) {
                $query->where('role_name', 'like', '%Instructor%');
            })->with('roles')->get();
        } else {
            $instructors = User::where('ou_id', $currentUser->ou_id)
                ->where(function ($query) {
                    $query->whereNull('is_admin')->orWhere('is_admin', false);
                })
                ->whereHas('roles', function ($query) {
                    $query->where('role_name', 'like', '%Instructor%');
                })->with('roles')->get();
        }

        $course = Courses::with('resources')->find($trainingEvent->course_id);
        $resources = $course ? $course->resources : collect();

        $courses = Courses::orderBy('position')->get();
        $event_id =  decode_id($event_id);

        $instructor_cbta = CbtaGrading::where('competency_type', 'instructor')->get()->toArray();
        $examiner_cbta = CbtaGrading::where('competency_type', 'examiner')->get()->toArray();

        $instructor_grading = ExaminerGrading::where('event_id', $event_id)->where('user_id', $student->id)->where('competency_type', 'instructor')->get()->toArray();

        $examiner_grading = ExaminerGrading::where('event_id', $event_id)->where('user_id', $student->id)->where('competency_type', 'examiner')->get()->toArray();
     


       $training_logs = TrainingEventLog::with('users', 'lesson.quizzes')
                        ->where('event_id', $trainingEvent->id)
                        ->where('lesson_type', 1)
                        ->orderBy('id', 'asc')
                        ->get();

        $groupedLogs = $training_logs->groupBy('lesson_id');

        $training_deferred_logs = TrainingEventLog::with('users', 'lesson.quizzes')
                        ->where('event_id', $trainingEvent->id)
                        ->where('lesson_type', 2)
                        ->orderBy('id', 'asc')
                        ->get();

        $grouped_deferredLogs = $training_deferred_logs->groupBy('lesson_id');
     
       

        $training_custom_logs = TrainingEventLog::with('users', 'lesson.quizzes')
                        ->where('event_id', $trainingEvent->id)
                        ->where('lesson_type', 3)
                        ->orderBy('id', 'asc')
                        ->get();
                        

        $grouped_customLogs = $training_custom_logs->groupBy('lesson_id');
          
        return view('trainings.show', compact('trainingEvent', 'student', 'overallAssessments', 'eventLessons', 'courselessons', 'taskGrades', 'competencyGrades', 'trainingFeedbacks', 'isGradingCompleted', 'resources', 'instructors', 'defTasks', 'deferredLessons', 'defLessonTasks', 'deferredTaskIds', 'gradedDefTasksMap', 'courses', 'customLessons', 'customLessonTasks', 'def_grading', 'instructor_cbta', 'examiner_cbta', 'examiner_grading', 'instructor_grading','groupedLogs','grouped_deferredLogs', 'grouped_customLogs'));
    }

    public function edit_customLesson(Request $request)
    {
        $event_id = $request->event_id;

        $lesson_type = $request->lesson_type;

        if ($lesson_type == "custom") {
            $custom_lesson_id = $request->custom_lesson_id;
        }
        if ($lesson_type == "deferred") {
            $deferred_lesson_id = $request->deferred_lesson_id;
        }

        $currentUser = auth()->user();

        $trainingEvent = TrainingEvents::with([
            'course:id,course_name,course_type,duration_value,duration_type,groundschool_hours,simulator_hours,ato_num',
            'course.documents', // Eager load course documents
            'group:id,name,user_ids',
            'instructor:id,fname,lname',
            'student:id,fname,lname,licence',
            'resource:id,name',
            'eventLessons.lesson:id,lesson_title,enable_cbta,grade_type,lesson_type,custom_time_id',
            'eventLessons.instructor:id,fname,lname',
            'eventLessons.resource:id,name',
            'trainingFeedbacks.question',
            'documents',
            'studentDocument'
        ])->find(($event_id));
        if (!$trainingEvent) {
            return abort(404, 'Training Event not found');
        }

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


        if ($lesson_type == "custom") {
            $custom_lesson_id = $request->custom_lesson_id;
            $deferredLessons = DefLesson::with(['student', 'instructor', 'instructor.documents', 'resource', 'defLesson', 'deftasks.subddddLesson.courseLesson'])
                ->where('event_id', $trainingEvent->id)
                ->where('id', $custom_lesson_id)
                ->where('lesson_type', "custom")
                ->orderBy('id', 'desc')
                ->get();

            $customLessons = DefLesson::with(['student', 'instructor', 'instructor.documents', 'resource', 'defLesson', 'deftasks.subddddLesson.courseLesson'])
                ->where('event_id', $trainingEvent->id)
                ->where('lesson_type', "custom")
                ->orderBy('id', 'desc')
                ->get();

            $defLessonTasks = DefLessonTask::with(['user', 'defLesson.instructor', 'defLesson.instructor.documents', 'defLesson.resource', 'task'])
                ->where('def_lesson_id', $custom_lesson_id)
                ->get();
        }
        if ($lesson_type == "deferred") {
            $deferred_lesson_id = $request->deferred_lesson_id;
            $deferredLessons = DefLesson::with(['student', 'instructor', 'instructor.documents', 'resource', 'defLesson', 'deftasks.subddddLesson.courseLesson'])
                ->where('event_id', $trainingEvent->id)
                ->where('id', $deferred_lesson_id)
                ->where('lesson_type', "deferred")
                ->orderBy('id', 'desc')
                ->get();

            $customLessons = DefLesson::with(['student', 'instructor', 'instructor.documents', 'resource', 'defLesson', 'deftasks.subddddLesson.courseLesson'])
                ->where('event_id', $trainingEvent->id)
                ->where('lesson_type', "deferred")
                ->orderBy('id', 'desc')
                ->get();

            $defLessonTasks = DefLessonTask::with(['user', 'defLesson.instructor', 'defLesson.instructor.documents', 'defLesson.resource', 'task'])
                ->where('def_lesson_id', $deferred_lesson_id)
                ->get();
        }


      // dump($deferredLessons);
        return response()->json(['success' => true,  'defTasks' => $defTasks, 'deferredLessons' => $deferredLessons, 'defLessonTasks' => $defLessonTasks]);
    }

    public function delete_customLesson(Request $request)
    {
        $custom_lesson_id = $request->custom_lesson_id;

        $lesson = DefLesson::find($custom_lesson_id);

        if (!$lesson) {
            return response()->json([
                'success' => false,
                'message' => "Lesson not found"
            ], 404);
        }

        // Delete manually
        DefLessonTask::where('def_lesson_id', $custom_lesson_id)->delete();
        DeferredGrading::where('deflesson_id', $custom_lesson_id)->delete();
        $lesson->delete();

        return response()->json([
            'success' => true,
            'message' => "Lesson deleted successfully"
        ], 200);
    }



    public function delete_deferredLesson(Request $request)
    {
        $deferred_lesson_id = $request->deferred_lesson_id;

        try {
            DB::beginTransaction();

            // Get DefLessonTask before delete
            $def_lesson = DefLessonTask::where('def_lesson_id', $deferred_lesson_id)->get();

            foreach ($def_lesson as $val) {
                $create_def_task = [
                    "event_id" => $val->event_id,
                    "user_id"  => $val->user_id,
                    "task_id"  => $val->task_id,
                ];
                DefTask::create($create_def_task);
            }

            // Delete def lesson
            $lesson = DefLesson::find($deferred_lesson_id);
            if (!$lesson) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => "Lesson not found"
                ], 404);
            }

            DefLessonTask::where('def_lesson_id', $deferred_lesson_id)->delete();
            DeferredGrading::where('deflesson_id', $deferred_lesson_id)->delete();
            $lesson->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Lesson deleted successfully"
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => "Error: " . $e->getMessage()
            ], 500);
        }
    }


    public function update_deferred_form(Request $request)
    {

        if ($request->lesson_type == "custom") {
            $validatedData = $request->validate([
                'event_id'      => 'required|integer|exists:training_events,id',
                'lesson_title'  => 'required|string|max:255',
                'select_courseTask'      => 'required|array',
                'select_courseTask.*'    => 'integer|exists:sub_lessons,id',
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
                'instructor_id' => 'Instructor'
            ]);

            $eventId = $validatedData['event_id'];
            $studentId = $validatedData['std_id'];
            $authId = auth()->id();
            $deferredLessons_id = $request->deferredLessons_id;

            $update_deffered_lesson = [
                'event_id'      => $eventId,
                'user_id'       => $studentId,
                'task_ids'      => $validatedData['select_courseTask'],
                'instructor_id' => $validatedData['instructor_id'],
                'resource_id'   => $validatedData['resource_id'],
                'lesson_title'  => $validatedData['lesson_title'],
                'lesson_date'   => $validatedData['lesson_date'],
                'start_time'    => $validatedData['start_time'],
                'end_time'      => $validatedData['end_time'],
                'departure_airfield'   => $validatedData['departure_airfield'],
                'destination_airfield' => $validatedData['destination_airfield'],
                'created_by'    => $authId,
                'lesson_type'   => $request->lesson_type,
                'operation'     => $request->edit_operation ?? 0,
               

            ];

            // Update lesson details
            DefLesson::where('id', $deferredLessons_id)->update($update_deffered_lesson);

            $selectedTasks = $validatedData['select_courseTask'];

            // Remove tasks not in new selection
            DefLessonTask::where('def_lesson_id', $deferredLessons_id)
                ->whereNotIn('task_id', $selectedTasks)
                ->delete();

            foreach ($selectedTasks as $taskId) {
                // Check if already exists
                $exists = DefLessonTask::where([
                    'def_lesson_id' => $deferredLessons_id,
                    'event_id'      => $eventId,
                    'user_id'       => $studentId,
                    'task_id'       => $taskId,
                ])->exists();

                if (!$exists) {
                    // Calculate credit minutes
                    $get_lesson_id = SubLesson::where('id', $taskId)->value('lesson_id');
                    $lessonType  = CourseLesson::where('id', $get_lesson_id)->value('lesson_type');

                    $start = $validatedData['start_time'] ?? null;
                    $end = $validatedData['end_time'] ?? null;
                    $creditMinutes = 0;

                    if ($lessonType === 'groundschool' && $validatedData['resource_id'] == 3) {
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

                    DefLessonTask::create([
                        'def_lesson_id' => $deferredLessons_id,
                        'event_id'      => $eventId,
                        'user_id'       => $studentId,
                        'task_id'       => $taskId,
                        'hours_credited' => gmdate("H:i", $creditMinutes * 60),
                        'created_by'    => $authId,
                    ]);
                }
            }



            return response()->json([
                'success' => true,
                'message' => $request->lesson_type
            ], 201);
        }

        // Deferred Lessn 
        else {
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
                'instructor_id' => 'Instructor'
            ]);

            $eventId = $validatedData['event_id'];
            $studentId = $validatedData['std_id'];
            $authId = auth()->id();
            $deferredLessons_id = $request->deferredLessons_id;

           
            $update_deffered_lesson = [
                'event_id'      => $eventId,
                'user_id'       => $studentId,
                'task_ids'      => $validatedData['item_ids'],
                'instructor_id' => $validatedData['instructor_id'],
                'resource_id'   => $validatedData['resource_id'],
                'lesson_title'  => $validatedData['lesson_title'],
                'lesson_date'   => $validatedData['lesson_date'],
                'start_time'    => $validatedData['start_time'],
                'end_time'      => $validatedData['end_time'],
                'departure_airfield'   => $validatedData['departure_airfield'],
                'destination_airfield' => $validatedData['destination_airfield'],
                'created_by'    => $authId,
                'lesson_type'   => $request->lesson_type,
                'operation'     => $request->edit_operation ?? 0,
            ];

            // Update lesson details
              DefLesson::where('id', $deferredLessons_id)->update($update_deffered_lesson);

            $selectedTasks = $validatedData['item_ids'];

            // Restore Def task

            $existingTasks = DefLessonTask::where('def_lesson_id', $deferredLessons_id)
                ->pluck('task_id')
                ->toArray();



            $tasksToAdd = array_diff($existingTasks, $selectedTasks);
            foreach ($tasksToAdd as $val) {
                $def_task = array(
                    'event_id' => $eventId,
                    'user_id'  => $studentId,
                    'task_id'  => $val,
                    'created_by' => $authId
                );
                DefTask::create($def_task);
            }
            // Remove tasks not in new selection
            DefLessonTask::where('def_lesson_id', $deferredLessons_id)
                ->whereNotIn('task_id', $selectedTasks)
                ->delete();

            foreach ($selectedTasks as $taskId) {
                // Check if already exists
                $exists = DefLessonTask::where([
                    'def_lesson_id' => $deferredLessons_id,
                    'event_id'      => $eventId,
                    'user_id'       => $studentId,
                    'task_id'       => $taskId,
                ])->exists();

                if (!$exists) {
                    // Calculate credit minutes
                    $get_lesson_id = SubLesson::where('id', $taskId)->value('lesson_id');
                    $lessonType  = CourseLesson::where('id', $get_lesson_id)->value('lesson_type');

                    $start = $validatedData['start_time'] ?? null;
                    $end = $validatedData['end_time'] ?? null;
                    $creditMinutes = 0;

                    if ($lessonType === 'groundschool' && $validatedData['resource_id'] == 3) {
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

                    DefLessonTask::create([
                        'def_lesson_id' => $deferredLessons_id,
                        'event_id'      => $eventId,
                        'user_id'       => $studentId,
                        'task_id'       => $taskId,
                        'hours_credited' => gmdate("H:i", $creditMinutes * 60),
                        'created_by'    => $authId,
                    ]);
                }

                DefTask::where('event_id', $eventId)->where('user_id', $studentId)->where('task_id', $taskId)->delete();
            }



            return response()->json([
                'success' => true,
                'message' => $request->lesson_type
            ], 201);
        }
    }


    public function createGrading(Request $request)
    {
        // ================================
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
                 // SAVE OVERALL LESSON ASSESSMENT
            // ================================
            if ($request->has('overall_result')) { 
                foreach ($request->overall_result as $lesson_id => $resultValue) {
                    $remarkValue = $request->overall_remark[$lesson_id] ?? null;
                    $update_assessment = array(
                             'overall_result'   => $resultValue,
                             'overall_remark'   => $remarkValue,
                    );
                    TrainingEventLessons::where('id', $lesson_id)->update($update_assessment);
                }
            }

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
                                'lesson_type'   => 1
                            ]
                        );
                           $grades = TaskGrading::where([
                                    'event_id'  => $event_id,
                                    'lesson_id' => $lesson_id,
                                    'user_id'   => $gradedStudentId,
                                ])->pluck('task_grade')->toArray();
                       
                                $finalResult = null;

                            // RULE 1: If any Incomplete → Incomplete
                            if (in_array('Incomplete', $grades)) {
                                $finalResult = 'Incomplete';

                            // RULE 2: If any Further training required → Further training required
                            } elseif (in_array('Further training required', $grades)) {
                                $finalResult = 'Further training required';

                            // RULE 3: All competent → Competent
                            } else {
                                $finalResult = 'Competent';
                            }  
                        TrainingEventLessons::where('training_event_id', $event_id)
                            ->where('lesson_id', $lesson_id)
                            ->update([
                                'overall_result' => $finalResult
                            ]); 


                        $lessonSummary = $request->input("lesson_summary.$lesson_id");
                        $instructor_summary = $request->input("instructor_summary.$lesson_id");

                        // Now update only the specific lesson_id
                        TrainingEventLessons::where('training_event_id', $event_id)
                            ->where('lesson_id', $lesson_id)
                            ->update([
                                'lesson_summary' => $lessonSummary,
                                'instructor_comment' => $instructor_summary,
                            ]);

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
                            
                         TrainingEventLog::create([
                             'event_id'  => $event_id,
                             'lesson_id' =>   $lesson_id,
                             'user_id'   => auth()->user()->id,
                             'is_locked' => 1, 
                             'lesson_type' => 1
                            ]);

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
                        'created_by' => auth()->user()->id,
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

            // Examiner CBTA Grading
            if ($request->has('examiner_grade')) {
                foreach ($request->input('examiner_grade') as $lesson_id => $competencyGrades) {
                    foreach ($competencyGrades as $competency_id => $grade) {

                        // fetch comment if exists
                        $comment = $request->input("examiner_comments.$lesson_id.$competency_id");

                        $compData = [
                            'event_id'          => $event_id,
                            'cbta_gradings_id'  => $competency_id,
                            'user_id'           => $gradedStudentIdForComp,
                            'lesson_id'         => $lesson_id,
                            'competency_value'  => $grade,
                            'competency_type'   => 'examiner',
                            'comment'           => $comment ?? null,
                        ];


                        ExaminerGrading::updateOrCreate(
                            [
                                'event_id' => $event_id,
                                'cbta_gradings_id' => $competency_id,
                                'lesson_id'         => $lesson_id,
                                'competency_type'   => 'examiner',
                                'user_id' => $gradedStudentIdForComp,
                            ],
                            $compData
                        );
                    }
                }
            }

            // Instructor Grading 
            if ($request->has('instructor_grade')) {
                foreach ($request->input('instructor_grade') as $lesson_id => $competencyGrades) {
                    foreach ($competencyGrades as $competency_id => $grade) {

                        // fetch comment if exists
                        $comment = $request->input("instructor_comments.$lesson_id.$competency_id");
                         
                        $compData = [
                            'event_id'          => $event_id,
                            'cbta_gradings_id'  => $competency_id,
                            'user_id'           => $gradedStudentIdForComp,
                            'lesson_id'         => $lesson_id,
                            'competency_value'  => $grade,
                            'competency_type'   => 'instructor',
                            'comment'           => $comment ?? null,
                        ];


                        ExaminerGrading::updateOrCreate(
                            [
                                'event_id' => $event_id,
                                'lesson_id'  => $lesson_id,
                                'cbta_gradings_id' => $competency_id,
                                'competency_type'   => 'instructor',
                                'user_id' => $gradedStudentIdForComp,
                            ],
                            $compData
                        );
                    }
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
                'created_by' => auth()->user()->id,
            ]
        );

        // TrainingEvents::where('id', $request->event_id)->where('is_locked', '!=', 1)->update(['is_locked' => 1]);
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
            ->where('id', $eventId)
            ->with([
                'taskGradings' => function ($query) use ($userId) { 
                    $query->where('user_id', $userId)
                        ->with('lesson:id,lesson_title,grade_type')
                        ->with('subLesson:id,title');
                },
                'defLessonTasks' => function ($query) use ($userId) {
                    $query->join('def_lessons', 'def_lesson_tasks.def_lesson_id', '=', 'def_lessons.id')
                        ->select('def_lesson_tasks.*', 'def_lessons.lesson_title')
                        ->where('def_lesson_tasks.user_id', $userId)
                        ->whereIn('def_lesson_tasks.id', function ($sub) use ($userId) {
                            $sub->select(\DB::raw('MIN(id)'))
                                ->from('def_lesson_tasks')
                                ->where('user_id', $userId)
                                ->groupBy('def_lesson_id');
                        });
                },

                'competencyGradings' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                },
                'overallAssessments' => function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                },
                'deferredGradings' => function ($query) {
                    $query->with('defLesson:id,lesson_title');
                },
                'course:id,course_name,enable_feedback',
                'group:id,name',
                'instructor:id,fname,lname',
                'documents:id,training_event_id,course_document_id,file_path',
                'documents.courseDocument:id,document_name',
                'eventLessons' => function ($query) {
                    $query->with([
                        'lesson:id,lesson_title,enable_cbta',
                        'instructor:id,fname,lname',
                        'quizzes.lesson:id,lesson_title'
                    ]);
                }
            ])
            ->first();
       
        if (!$event) {
            return redirect()
                ->route('training.index')
                ->with('error', 'Training event or grading not found.');
        }

        $defLessonGrading = DefLessonTask::with([
            'task',
            'defLesson.deferredGradings',
            'defLesson.student'
        ])
            ->where('event_id', $event->id)
            ->whereRelation('defLesson', 'lesson_type', 'deferred')
            ->get()
            ->groupBy('def_lesson_id');

        $CustomLessonGrading = DefLessonTask::with([
            'task',
            'defLesson.deferredGradings',
            'defLesson.student'
        ])
            ->where('event_id', $event->id)
            ->whereRelation('defLesson', 'lesson_type', 'custom')
            ->get()
            ->groupBy('def_lesson_id');

        $event_id = $event->id;

 
        $competencyType = 'examiner';

        $examiner_grading = CbtaGrading::with([
            'examinerGrading.courseLesson' // loads CourseLesson inside examinerGrading
        ])->whereHas('examinerGrading', function ($query) use ($event_id, $userId, $competencyType) {
            $query->where('event_id', $event_id)
                ->where('user_id', $userId)
                ->where('competency_type', $competencyType);
        })->get();

        // Flatten examinerGrading into one collection
        $examinerGradings = $examiner_grading->pluck('examinerGrading')->flatten();

        // Group by lesson_id
        $examinerGrouped = $examinerGradings->groupBy('lesson_id');
 
        $competencyType = 'instructor';

        $instructor_grading = CbtaGrading::with([
            'examinerGrading.courseLesson' // loads CourseLesson inside examinerGrading
        ])->whereHas('examinerGrading', function ($query) use ($event_id, $userId, $competencyType) {
            $query->where('event_id', $event_id)
                ->where('user_id', $userId)
                ->where('competency_type', $competencyType);
        })->get();

        $instructorGradings = $instructor_grading->pluck('examinerGrading')->flatten();

        // Group by lesson_id
        $instructorGrouped = $instructorGradings->groupBy('lesson_id');

        if ($event) { 
            $event->student_feedback_submitted = $event->trainingFeedbacks()->where('user_id', auth()->user()->id)->exists();
            // abort(404, 'Training Event not found.'); 
        }
        $reviews = TrainingEventReview::with('users')->where('event_id', $eventId)->get();
        
            $lessonIds = json_decode($event->lesson_ids, true);
            $allLessonsGraded = true;  // assume true unless a lesson fails the condition
            foreach ($lessonIds as $id) {
                $totalSubLessons = SubLesson::where('lesson_id', $id)->count();

                $gradedSubLessons = TaskGrading::where([
                        'event_id'  => $eventId,
                        'lesson_id' => $id,
                        'user_id'   => $userId,
                    ])
                    ->whereNotNull('task_grade')
                    ->where('task_grade', '!=', '')
                    ->count();
                
              if ($gradedSubLessons == 0 || $gradedSubLessons != $totalSubLessons) {
                        $allLessonsGraded = false;
                        break;
                    }
            }

        return view('trainings.grading-list', compact('event', 'userId', 'defLessonGrading', 'CustomLessonGrading', 'examinerGrouped', 'instructorGrouped', 'reviews','allLessonsGraded'));
    }

    // public function unlockEventGarding(Request $request, $event_id)
    // {
    //     $updateTrainingEvent = TrainingEvents::where('id', decode_id($event_id))->update(['is_locked' => 0]);
    //     if($updateTrainingEvent){
    //         Session::flash('message', 'Grading is unlocked for editing.');
    //         return response()->json(['success' => true, 'message' => 'Grading is unlocked for editing.']);
    //     }
    // }

    public function unlockEventGarding(Request $request, $event_id)
    {
        $updateTrainingEvent = TrainingEventLessons::where('id', ($event_id))->update(['is_locked' => 0]);
        if ($updateTrainingEvent) {
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
    public function downloadDefferedLessonReport($event_id, $lesson_id, $userID)
    {
        $userId = $userID;

        // Fetch Training Event with related Def Lessons and Def Lesson Tasks
        // $event = TrainingEvents::with([
        //     'course:id,course_name', 
        //     'orgUnit:id,org_unit_name,org_logo',
        //     'instructor:id,fname,lname',
        //     'student:id,fname,lname',
        //     'resource:id,name,type,class,registration',
        //     'defLessons' => function ($query) use ($lesson_id) {
        //         $query->where('id', $lesson_id)
        //             ->with([
        //                 'instructor:id,fname,lname',
        //                 'resource:id,name,type,class,registration'
        //             ]);
        //     },
        //     'defLessonTasks' => function ($query) use ($userId, $lesson_id) {
        //         $query->where('user_id', $userId)
        //             ->where('def_lesson_id', $lesson_id)
        //             ->with('task:id,title,grade_type,description');
        //     },
        // ])->findOrFail($event_id);
        $event = TrainingEvents::with([
            'course:id,course_name',
            'orgUnit:id,org_unit_name,org_logo',
            'instructor:id,fname,lname',
            'student:id,fname,lname',
            'resource:id,name,type,class,registration',
            'defLessons' => function ($query) use ($lesson_id, $userId) {
                $query->where('id', $lesson_id)
                    ->with([
                        'instructor:id,fname,lname',
                        'resource:id,name,type,class,registration',
                        'deferredGradings' => function ($q) use ($userId) {
                            $q->where('user_id', $userId);
                        },
                    ]);
            },
            'defLessonTasks' => function ($query) use ($userId, $lesson_id) {
                $query->where('user_id', $userId)
                    ->where('def_lesson_id', $lesson_id)
                    ->with('task:id,title,grade_type,description');
            },
        ])->findOrFail($event_id);


        $defLesson = $event->defLessons->first();
        $eventLesson = $event->defLessons->first();
        if (!$eventLesson) {
            abort(404, 'Lesson not found for this training event.');
        }
        if (!$defLesson) {
            abort(404, 'Deferred lesson not found for this training event.');
        }
        $tasks = $event->defLessonTasks;
       //  return view('trainings.deferred-lesson-report', compact('event', 'eventLesson', 'tasks'));
        
        $pdf = PDF::loadView('trainings.deferred-lesson-report', [
            'event' => $event,
            'eventLesson' => $eventLesson,
            'tasks' => $tasks,
        ]);

        $filename = 'Deferred_Lesson_Report_' . Str::slug($defLesson->lesson_title) . '.pdf';
        return $pdf->download($filename);
    }


    public function downloadLessonReport($event_id, $lesson_id, $userID)
    {
        $userId = $userID;

        $event = TrainingEvents::with([
            'course:id,course_name,ato_num,enable_cbta,instructor_cbta,examiner_cbta',
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

      //  dd($event);

        $eventLesson = $event->eventLessons->first();


        if (!$eventLesson) {
            abort(404, 'Lesson not found for this training event.');
        }

        $lesson = $eventLesson->lesson;

        $competencyType = 'examiner';
        $examiner_grading = CbtaGrading::with(['examinerGrading' => function ($query) use ($event_id, $userID, $competencyType, $lesson_id) {
                            $query->where('event_id', $event_id)
                                ->where('user_id', $userID)
                                ->where('competency_type', $competencyType)
                                ->where('lesson_id', $lesson_id);
                        }])
                            ->whereHas('examinerGrading', function ($query) use ($event_id, $userID, $competencyType, $lesson_id) {
                                $query->where('event_id', $event_id)
                                    ->where('user_id', $userID)
                                    ->where('competency_type', $competencyType)
                                     ->where('lesson_id', $lesson_id);
                            })
                            ->get();

        $instructor = 'instructor';
        $instructor_grading = CbtaGrading::with(['examinerGrading' => function ($query) use ($event_id, $userID, $instructor, $lesson_id) {
            $query->where('event_id', $event_id)
                ->where('user_id', $userID)
                ->where('lesson_id', $lesson_id)
                ->where('competency_type', $instructor);
        }])
            ->whereHas('examinerGrading', function ($query) use ($event_id, $userID, $instructor, $lesson_id) {
                $query->where('event_id', $event_id)
                    ->where('user_id', $userID)
                    ->where('lesson_id', $lesson_id)
                    ->where('competency_type', $instructor);
            })
            ->get();
        
          

       //  return view('trainings.lesson-report', compact('event', 'lesson', 'eventLesson','examiner_grading', 'instructor_grading'));

        $pdf = PDF::loadView('trainings.lesson-report', [   
            'event' => $event,
            'lesson' => $lesson,
            'eventLesson' => $eventLesson,
            'examiner_grading' => $examiner_grading,
            'instructor_grading' => $instructor_grading
        ]);

        $filename = 'Lesson_Report_' . Str::slug($lesson->lesson_title) . '.pdf';  
        return $pdf->download($filename);
    }

    public function uploadDocuments(Request $request, TrainingEvents $trainingEvent)
    {
        $request->validate([
            'training_event_documents' => 'required|array|min:1',
            'training_event_documents.*' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:10240', // Add more formats if needed
        ], [], [
            'training_event_documents.*' => 'Training Event Document'
        ]);

        // Loop through the uploaded files
        foreach ($request->file('training_event_documents', []) as $courseDocId => $file) {
            // If file exists in the form input

            if ($file) {
                // Get the original file name
                $originalName = $file->getClientOriginalName();

             
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
        $event = TrainingEvents::with('eventLessons', 'recommendedInstructor')->findOrFail($eventId);
        $student = $event->student;  
        $course = $event->course;
        $firstLesson = $event->firstLesson;

        $lessons = $event->eventLessons;
        $deferredLessons = DefLesson::where('event_id', $eventId)->get();

        $totals = [
            'flight' => 0,
            'deferred' => 0,
        ];

        foreach ($lessons as $lesson) {
            $lessonType = $lesson->lesson?->lesson_type ?? null;

            if ($lessonType === 'flight') {
                $credited = strtotime("1970-01-01 {$lesson->hours_credited}") ?: 0;
                $totals['flight'] += $credited;
            }
        }

        foreach ($deferredLessons as $defLesson) {
            $start = strtotime($defLesson->start_time);
            $end = strtotime($defLesson->end_time);
            $duration = max(0, $end - $start);
            $totals['deferred'] += $duration;
        }

        $totalFlightTimeSeconds = $totals['flight'] + $totals['deferred'];

        $hours = floor($totalFlightTimeSeconds / 3600);
        $minutes = floor(($totalFlightTimeSeconds % 3600) / 60);
        $totalFlightTimeFormatted = "{$hours}h {$minutes}m";



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

        $recommendedBy = $event->recommendedInstructor; 
        $licence1 = null;
        $licence2 = null;

        if (!empty($event) && !empty($event->recommendedInstructor)) {
            $recommendedBy = $event->recommendedInstructor;

            if (!empty($recommendedBy->id)) {
                $document_info = UserDocument::where('user_id', $recommendedBy->id)->get();

                if ($document_info && $document_info->count() > 0) {
                    // safely access first record and its property
                    $firstDocument = $document_info->first();

                    if (!empty($firstDocument->licence)) {
                        $licence1 = $firstDocument->licence;
                    }
                    if (!empty($firstDocument->licence_2)) {
                        $licence2 = $firstDocument->licence_2;
                    }
                }
            }
        }

        // Progress breakdown from task_grading
       

        $grades = TaskGrading::where('user_id', $student->id)
                    ->when($eventId, fn($q) => $q->where('event_id', $eventId))
                    ->get(['sub_lesson_id', 'task_grade']);
        $total = $grades->count();

         // 2️⃣ Fetch DefLessonTask records for comparison
        $defTasks = DefLessonTask::where('user_id', $student->id)
                    ->when($eventId, fn($q) => $q->where('event_id', $eventId))
                    ->get(['task_id', 'task_grade']);

        $normalizedGrades = $grades->map(function ($g) {
                                $g->task_grade = strtolower((string) $g->task_grade);
                                return $g;
                            });

        $normalizedDef = $defTasks->map(function ($d) {
                        $d->task_grade = strtolower((string) $d->task_grade);
                        return $d;
                    });

        $progress = [
                    'total'      => $normalizedGrades->count(),
                    'incomplete' => 0,
                    'further'    => 0,
                    'competent'  => 0,
                ];

        foreach ($normalizedGrades as $grade) {
                        $matchingDef = $normalizedDef->firstWhere('task_id', $grade->sub_lesson_id);

                        if ($matchingDef) {
                            $finalGrade = $matchingDef->task_grade ?: 'incomplete';
                        } else {
                            $finalGrade = $grade->task_grade;
                        }

                        if (in_array($finalGrade, ['1', 'incomplete'])) {
                            $progress['incomplete']++;
                        } elseif (in_array($finalGrade, ['2', 'further training required'])) {
                            $progress['further']++;
                        } elseif (in_array($finalGrade, ['3', '4', '5', 'competent'])) {
                            $progress['competent']++;
                        }
                    }
                 
                    $student->progress = $progress;
      
         
      //  return view('trainings.course-completion-certificate', compact('event', 'student', 'course', 'firstLesson', 'hoursOfGroundschool', 'flightTime', 'simulatorTime', 'recommendedBy', 'licence1'));

        $pdf = PDF::loadView('trainings.course-completion-certificate', [
            'event' => $event,
            'student' => $student,
            'course' => $course,
            'firstLesson' => $firstLesson,
            'hoursOfGroundschool' => $hoursOfGroundschool,
            'flightTime' => $flightTime,
            'simulatorTime' => $simulatorTime, 
            'recommendedBy' => $event->recommendedInstructor,
            'licence1' => $licence1,
            'licence2' => $licence2,
            'totalFlightTimeFormatted' => $totalFlightTimeFormatted,
        ]);

        $filename = 'Certificate_' . Str::slug($student->fname . ' ' . $student->lname) . '.pdf';
        return $pdf->download($filename); 
    }


    public function storeDeferredLessons(Request $request)
    {
        if ($request->lesson_type == "custom") {
            $validatedData = $request->validate([
                'event_id'      => 'required|integer|exists:training_events,id',
                'lesson_title'  => 'required|string|max:255',
                'select_courseTask'      => 'required|array',
                'select_courseTask.*'    => 'integer|exists:sub_lessons,id',
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
                'instructor_id' => 'Instructor'
            ]);

            $eventId = $validatedData['event_id'];
            $studentId = $validatedData['std_id'];
            $authId = auth()->id();

            $defLesson = DefLesson::create([
                'event_id'      => $eventId,
                'user_id'       => $studentId,
                'task_ids'      => $validatedData['select_courseTask'], // Optional if not needed
                'instructor_id' => $validatedData['instructor_id'],
                'resource_id'   => $validatedData['resource_id'],
                'lesson_title'  => $validatedData['lesson_title'],
                'lesson_date'   => $validatedData['lesson_date'],
                'start_time'    => $validatedData['start_time'],
                'end_time'      => $validatedData['end_time'],
                'departure_airfield'   => $validatedData['departure_airfield'],
                'destination_airfield' => $validatedData['destination_airfield'],
                'created_by'    => $authId,
                'lesson_type'   => $request->lesson_type,
                'operation'     => $request->operation ?? 0,
            ]);
            // Step 3: Create def_lesson_tasks entries
            foreach ($validatedData['select_courseTask'] as $index => $taskId) {

                $get_lesson_id = SubLesson::where('id', $taskId)->value('lesson_id');

                $lessonType  =  CourseLesson::where('id', $get_lesson_id)->value('lesson_type');

                $start = $validatedData['start_time'] ?? null;
                $end = $validatedData['end_time'] ?? null;
                $creditMinutes = 0;
                if ($lessonType === 'groundschool' && $validatedData['resource_id'] == 3) {
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
                DefLessonTask::create([
                    'def_lesson_id' => $defLesson->id,
                    'event_id'      => $eventId,
                    'user_id'       => $studentId,
                    'task_id'       => $taskId,
                    'hours_credited' => gmdate("H:i", $creditMinutes * 60),
                    'created_by'    => $authId,

                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Custom lesson stored successfully.'
            ], 201);
        }
        if ($request->lesson_type == "deferred") {
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
                'instructor_id' => 'Instructor'
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
                'lesson_type'   => $request->lesson_type,
                'operation'     => $request->operation ?? 0,

            ]);

            // Step 3: Create def_lesson_tasks entries
            foreach ($validatedData['item_ids'] as $index => $taskId) {

                $get_lesson_id = SubLesson::where('id', $taskId)->value('lesson_id');

                $lessonType  =  CourseLesson::where('id', $get_lesson_id)->value('lesson_type');

                $start = $validatedData['start_time'] ?? null;
                $end = $validatedData['end_time'] ?? null;
                $creditMinutes = 0;
                if ($lessonType === 'groundschool' && $validatedData['resource_id'] == 3) {
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
                DefLessonTask::create([
                    'def_lesson_id' => $defLesson->id,
                    'event_id'      => $eventId,
                    'user_id'       => $studentId,
                    'task_id'       => $taskId,
                    'hours_credited' => gmdate("H:i", $creditMinutes * 60),
                    'created_by'    => $authId,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Deferred lesson and tasks stored successfully.'
            ], 201);
        }
    }

    public function storeDefGrading(Request $request) 
    {
        $lesson_type = $request->lesson_type;
        $request->validate([
            'event_id' => 'required|integer|exists:training_events,id',
            'task_grade_def' => 'required|array',
            'task_grade_def.*' => 'required|array',
            'task_grade_def.*.*' => 'required|string|in:Competent,Incomplete,Further training required',
            
            'task_comment_def.*' => 'nullable', // ✅ first level also array
            'task_comment_def.*.*' => 'nullable|string|max:1000', // ✅ actual comments
        ]);

        $event_id = $request->input('event_id');
        $user_id = $request->input('tg_user_id');

        foreach ($request->input('task_grade_def') as $task_id => $lessonGrades) {
            foreach ($lessonGrades as $def_lesson_id => $task_grade) {
                $task_comment = $request->input("task_comment_def.$task_id.$def_lesson_id", null);
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

                // Grading 
                $gradedStudentIdForComp = $request->input('cg_user_id');
                if ($request->has('comp_grade')) {
                    foreach ($request->input('comp_grade') as $lesson_id => $competencyGrades) {
                        $compData = [
                            'event_id'   => $event_id,
                            'lesson_id'  => $lesson_id,
                            'user_id'    => $gradedStudentIdForComp,
                            'created_by' => auth()->user()->id,
                        ];

                        foreach ($competencyGrades as $code => $grade) {
                            $gradeColumn   = strtolower($code) . '_grade';
                            $commentColumn = strtolower($code) . '_comment';

                            $compData[$gradeColumn]   = $grade;
                            $compData[$commentColumn] = $request->input("comp_comments.$lesson_id.$code", null);
                        }

                        DeferredGrading::updateOrCreate(
                            [
                                'event_id'    => $event_id,
                                'deflesson_id' => $lesson_id,
                                'user_id'     => $gradedStudentIdForComp,
                            ],
                            $compData
                        );
                    }
                }
               
                // Is locked 
                $check_taskGrade = DefLessonTask::where('event_id', $event_id)
                    ->where('def_lesson_id', $def_lesson_id)
                    ->whereNull('task_grade')
                    ->count();

                if ($lesson_type == "custom") {
                    $customLessonTasks = DefLessonTask::with([
                        'user',
                        'defLesson.instructor',
                        'defLesson.instructor.documents',
                        'defLesson.resource',
                        'task.courseLesson.course'
                    ])
                        ->where('event_id', $event_id)
                        ->whereRelation('defLesson', 'lesson_type', 'custom') 
                        ->get();

                    if ($customLessonTasks->isNotEmpty()) {
                        $check_cbta = $customLessonTasks[0]->task->courseLesson->course->enable_cbta;
                           
                        if ($check_cbta == 1) {
                            $deferredExists = DeferredGrading::where('event_id', $event_id)->exists();

                            if ($check_taskGrade == 0) {
                                DefLesson::where('event_id', $event_id)
                                    ->where('lesson_type', "custom")
                                    ->where('id', $def_lesson_id)
                                    ->update(['is_locked' => 1]);
                            }
                                         // Training Logs 
                       // Log lock activity
                                $lastLog = TrainingEventLog::where('event_id', $event_id)
                                    ->where('lesson_id', $def_lesson_id)
                                  //  ->where('user_id', auth()->user()->id)
                                    ->where('lesson_type', 3)
                                    ->latest('id')
                                    ->first();

                                if (!$lastLog || $lastLog->is_locked == 0) {
                                    TrainingEventLog::create([
                                        'event_id'  => $event_id,
                                        'lesson_id' => $def_lesson_id,
                                        'user_id'   => auth()->user()->id,
                                        'lesson_type' => 3,
                                        'is_locked' => 1,
                                    ]);
                                }
                        } else {
                            if ($check_taskGrade == 0) {
                                DefLesson::where('event_id', $event_id)
                                    ->where('lesson_type', "custom")
                                    ->where('id', $def_lesson_id)
                                    ->update(['is_locked' => 1]);
                                                 // Training Logs 
                       // Log lock activity
                                $lastLog = TrainingEventLog::where('event_id', $event_id)
                                    ->where('lesson_id', $def_lesson_id)
                                  //  ->where('user_id', auth()->user()->id)
                                     ->where('lesson_type', 3)
                                    ->latest('id')
                                    ->first();

                                if (!$lastLog || $lastLog->is_locked == 0) {
                                    TrainingEventLog::create([
                                        'event_id'  => $event_id,
                                        'lesson_id' => $def_lesson_id,
                                        'user_id'   => auth()->user()->id,
                                        'lesson_type' => 3,
                                        'is_locked' => 1,
                                    ]);
                                }
                            }
                        }
                    }
                    $lessonSummary = $request->input("def_lesson_summary.$def_lesson_id");
                    $instructor_summary = $request->input("def_instructor_summary.$def_lesson_id");

                    DefLesson::where('event_id', $event_id)
                        ->where('id', $def_lesson_id)
                        ->update([
                            'lesson_summary' => $lessonSummary,
                            'instructor_comment' => $instructor_summary,
                        ]);
                }

                if ($lesson_type == "deferred") { 
                    $customLessonTasks = DefLessonTask::with([
                        'user',
                        'defLesson.instructor',
                        'defLesson.instructor.documents',
                        'defLesson.resource',
                        'task.courseLesson.course'
                    ])
                        ->where('event_id', $event_id)
                        ->whereRelation('defLesson', 'lesson_type', 'deferred')
                        ->get();
                        

                    if ($customLessonTasks->isNotEmpty()) {
                        $check_cbta = $customLessonTasks[0]->task->courseLesson->course->enable_cbta;

                        if ($check_cbta == 1) {
                            $deferredExists = DeferredGrading::where('event_id', $event_id)->exists();

                            if ($check_taskGrade == 0) { 
                                DefLesson::where('event_id', $event_id)
                                    ->where('lesson_type', "deferred")
                                    ->where('id', $def_lesson_id)
                                    ->update(['is_locked' => 1]);


                        // Training Logs 
                        // Log lock activity
                        $lastLog = TrainingEventLog::where('event_id', $event_id)
                                ->where('lesson_id', $def_lesson_id)
                               // ->where('user_id', auth()->user()->id)
                                ->where('lesson_type', 2)
                                ->latest('id')
                                ->first();
                              

                        if (!$lastLog || $lastLog->is_locked == 0) { 
                            $test = array(
                                'event_id'  => $event_id,
                                'lesson_id' => $def_lesson_id,
                                'user_id'   => auth()->user()->id,
                                'lesson_type' => 2,
                                'is_locked' => 1,

                            );
                          
                            TrainingEventLog::create([
                                'event_id'  => $event_id,
                                'lesson_id' => $def_lesson_id,
                                'user_id'   => auth()->user()->id,
                                'lesson_type' => 2,
                                'is_locked' => 1,
                            ]);
                        }

                            }
                        } else { 
                            if ($check_taskGrade == 0) {
                                DefLesson::where('event_id', $event_id)
                                    ->where('lesson_type', "deferred")
                                    ->where('id', $def_lesson_id)
                                    ->update(['is_locked' => 1]);
                            }
                            // Training Logs 
                       // Log lock activity
                                $lastLog = TrainingEventLog::where('event_id', $event_id)
                                    ->where('lesson_id', $def_lesson_id)
                                   // ->where('user_id', auth()->user()->id)
                                    ->where('lesson_type', 2)
                                    ->latest('id')
                                    ->first();

                                if (!$lastLog || $lastLog->is_locked == 0) {
                                       $test = array(
                                                'event_id'  => $event_id,
                                                'lesson_id' => $def_lesson_id,
                                                'user_id'   => auth()->user()->id,
                                                'lesson_type' => 2,
                                                'is_locked' => 1,

                                            );
                                          
                                    TrainingEventLog::create([
                                        'event_id'  => $event_id,
                                        'lesson_id' => $def_lesson_id,
                                        'user_id'   => auth()->user()->id,
                                        'is_locked' => 1,
                                        'lesson_type' => 2
                                    ]);
                                }


                        }
                    }
                    $lessonSummary = $request->input("def_lesson_summary.$def_lesson_id");
                    $instructor_summary = $request->input("def_instructor_summary.$def_lesson_id");

                    DefLesson::where('event_id', $event_id)
                        ->where('id', $def_lesson_id)
                        ->update([
                            'lesson_summary' => $lessonSummary,
                            'instructor_comment' => $instructor_summary,
                        ]);
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
        if ($user->is_admin != 1) {
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


        TrainingEventLog::create([
                'event_id'  => $eventId,
                'lesson_id' =>   $lessonId,
                'user_id'   => auth()->user()->id,
                'is_locked' => 0,
                'lesson_type' => 1
            ]);



        return response()->json(['success' => true]);
    }

    public function unlock_deflesson(Request $request)
    {
        $user = auth()->user();
        $event_id = $request->event_id;
        $lesson_type = $request->lesson_type;
        if($lesson_type == "deferred"){
           $type = 2;
        }else{
              $type = 3;
        }
        

        if ($user->is_admin != 1) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $deflesson_id = $request->input('deflesson_id');


        $deflesson = DefLesson::where('id', $deflesson_id)
            ->first();

        if (!$deflesson) {
            return response()->json(['success' => false, 'message' => 'Lesson not found'], 404);
        }

        $deflesson->is_locked = 0;
        $deflesson->save();

         TrainingEventLog::create([
                    'event_id'  => $event_id,
                    'lesson_id' =>   $deflesson_id,
                    'user_id'   => auth()->user()->id,
                    'is_locked' => 0,
                    'lesson_type' => $type
                ]);

        return response()->json(['success' => true]);
    }


    // private function calculateOpcExpiry(Carbon $completionDate, ?Carbon $currentExpiry = null): Carbon
    // {
    //     if ($currentExpiry && $completionDate->between(
    //             $currentExpiry->copy()->subMonths(3),
    //             $currentExpiry
    //         )) {

    //         return $currentExpiry->copy()->addMonths(6)->endOfMonth();
    //     }

    //     return $completionDate->copy()->addMonths(6)->endOfMonth();
    // }
    // private function calculateOpcExpiry( Carbon $completionDate, int $validityMonths, ?Carbon $currentExpiry = null): Carbon 
    // {
    //     if ($currentExpiry && $completionDate->between( $currentExpiry->copy()->subMonths(3), $currentExpiry))
    //     {
    //         return $currentExpiry->copy()->addMonths($validityMonths)->endOfMonth();
    //     }

    //     return $completionDate->copy()->addMonths($validityMonths)->endOfMonth();
    // }

    private function calculateOpcExpiry(Carbon $completionDate, int $validityMonths, int $opcExtend, ?Carbon $currentExpiry = null ): Carbon 
    {
        if ($currentExpiry && $completionDate->between($currentExpiry->copy()->subMonths(3), $currentExpiry)) 
        {
            $expiry = $currentExpiry->copy()->addMonths($validityMonths);
        } 
        else {
            $expiry = $completionDate->copy()->addMonths($validityMonths);
        }

        return $opcExtend == 1 ? $expiry->endOfMonth() : $expiry;
    }


    public function endCourse(Request $request)
    {
        $request->validate([
            'course_end_date' => 'required|date|before_or_equal:today',
            'recommended_by_instructor_id' => 'nullable|exists:users,id',
        ]);

        $id = decode_id($request->event_id);
        $event = TrainingEvents::with('course')->findOrFail($id);

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

        if ($event->course->opc == 1) {

            $completionDate = Carbon::parse($request->course_end_date);

            $opcRating = UserOpcRating::where('user_id', $event->student_id)
                ->where('aircraft_type', $event->course->opc_aircraft)
                ->latest('opc_expiry_date')
                ->first();

            $currentExpiry = $opcRating?->opc_expiry_date
                ? Carbon::parse($opcRating->opc_expiry_date)
                : null;

            $validityMonths = (int) ($event->opc_validity ?? 6);
            $opcExtend      = (int) ($event->opc_extend ?? 1);

            $newExpiry = $this->calculateOpcExpiry(
                $completionDate,
                $validityMonths,
                $opcExtend,
                $currentExpiry
            );

            UserOpcRating::create([
                'user_id' => $event->student_id,
                'aircraft_type' => $event->course->opc_aircraft,
                'event_id' => $id,
                'course_id' => $event->course->id,
                'opc_expiry_date' => $newExpiry,
            ]);
        }


        // if ($event->course->opc == 1) {

        //     $completionDate = Carbon::parse($request->course_end_date);

        //     $opcRating = UserOpcRating::where('user_id', $event->user_id)->where('aircraft_type', $event->course->opc_aircraft)->latest('opc_expiry_date')->first();

        //     $currentExpiry = $opcRating?->opc_expiry_date ? Carbon::parse($opcRating->opc_expiry_date) : null;

        //     $validityMonths = (int) ($event->opc_validity ?? 6);

        //     $newExpiry = $this->calculateOpcExpiry($completionDate, $validityMonths, $currentExpiry);

        //     UserOpcRating::create([
        //             'user_id' => $event->student_id,
        //             'aircraft_type' => $event->course->opc_aircraft,
        //             'event_id' => $id,
        //             'course_id' => $event->course->id,
        //             'opc_expiry_date' => $newExpiry,
        //         ]);
        // }

        return redirect()
            ->route('training.index')
            ->with('message', 'Course has been ended and locked.');
    }

    // public function endCourse(Request $request)
    // {
    //     $request->validate([
    //         'course_end_date' => 'required|date|before_or_equal:today',
    //         'recommended_by_instructor_id' => 'nullable|exists:users,id', // validate if provided
    //     ]);

    //     $id = decode_id($request->event_id);
    //     $event = TrainingEvents::findOrFail($id);

    //     if (auth()->user()->is_admin != 1) {
    //         abort(403, 'Unauthorized action.');
    //     }

    //     if ($event->is_locked) {
    //         return back()->withErrors(['error' => 'Course already ended.']);
    //     }

    //     $event->update([
    //         'course_end_date' => $request->course_end_date,
    //         'recommended_by_instructor_id' => $request->recommended_by_instructor_id ?? null,
    //         'is_locked' => 1,
    //     ]);

    //     return redirect()->route('training.index')->with('message', 'Course has been ended and locked.');
    // }

    // public function getEventInstructors($id)
    // {
    //     $event = TrainingEvents::with('eventLessons')->findOrFail(decode_id($id));
    //     $instructorIds = $event->eventLessons
    //         ->pluck('instructor_id')
    //         ->filter()
    //         ->unique()
    //         ->values();

    //     $instructors = User::whereIn('id', $instructorIds)->get();

    //     $lastLesson = $event->eventLessons->sortByDesc('id')->first();
    //     $lastInstructorId = $lastLesson?->instructor_id;

    //     return response()->json([
    //         'instructors' => $instructors,
    //         'last_instructor_id' => $lastInstructorId,
    //     ]);
    // }

    public function getEventInstructors($id)
    {
        $event = TrainingEvents::with(['eventLessons', 'defLessons'])->findOrFail(decode_id($id));

        // Get instructor IDs from eventLessons
        $eventLessonInstructorIds = $event->eventLessons
            ->pluck('instructor_id')
            ->filter();

        // Get instructor IDs from defLessons
        $defLessonInstructorIds = $event->defLessons
            ->pluck('instructor_id')
            ->filter();

        // Merge both sets of instructors and get unique IDs
        $instructorIds = $eventLessonInstructorIds
            ->merge($defLessonInstructorIds)
            ->unique()
            ->values();

        // Fetch instructor user records
        $instructors = User::whereIn('id', $instructorIds)->get(['id', 'fname', 'lname']);

        // Determine last instructor (prefer eventLessons first)
        $lastEventInstructorId = $event->eventLessons
            ->whereNotNull('instructor_id')
            ->sortByDesc('id')
            ->value('instructor_id');

        $lastDefInstructorId = $event->defLessons
            ->whereNotNull('instructor_id')
            ->sortByDesc('id')
            ->value('instructor_id');

        $lastInstructorId = $lastEventInstructorId ?? $lastDefInstructorId ?? null;

        return response()->json([
            'instructors' => $instructors,
            'last_instructor_id' => $lastInstructorId,
        ]);
    }


    public function unlocked_trainingEvent(Request $request)
    {
        if (auth()->user()->is_admin == 1) {
            $training_id = $request->training_id;

            $unlocked = TrainingEvents::where('id', $training_id)
                ->update(['is_locked' => 0]);

            if ($unlocked) {
                Session::flash('message', 'Training Unlocked successfully.');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Training unlocked successfully.'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unable to unlock training.'
                ]);
            }
        }

        return response()->json([
            'status' => 'error',
            'message' => 'You do not have permission to unlock this training.'
        ], 403);
    }

    public function submit_normal_items(Request $request)
    {


        // Fetch the sub lesson
        $sub_lesson = SubLesson::find($request->def_id);

        if (!$sub_lesson) {
            return response()->json(['status' => 'error', 'message' => 'Sub Lesson not found']);
        }

        // Prepare new normal lesson data
        $NormalLesson = [
            'lesson_id'    => $request->lesson,
            'title'        => $sub_lesson->title,
            'description'  => $sub_lesson->description,
            'grade_type'   => $sub_lesson->grade_type,
            'is_mandatory' => $sub_lesson->is_mandatory,
            'status'       => $sub_lesson->status,
            'normal_lesson' => 1,
            'event_id'     => $request->event_id,
            'user_id'      => $request->std_id,
            'task_id'      => $request->def_id
        ];

        $exists = SubLesson::where('lesson_id', $request->lesson)
            ->where('title', $sub_lesson->title)
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Task already exists in this Lesson. Please transfer to another Lesson.',
            ]);
        }

        // Insert into sub lessons
        SubLesson::create($NormalLesson);
        DefTask::where('event_id', $request->event_id)
            ->where('user_id', $request->std_id)
            ->where('task_id', $request->def_id)
            ->delete();


        TrainingEventLessons::where('training_event_id', $request->event_id)
            ->where('lesson_id', $request->lesson)
            ->update(['is_locked' => 0]);

        Session::flash('message', 'Normal Lesson Added Successfully');

        return response()->json([
            'status'  => 'success',
            'message' => 'Normal Lesson Added Successfully',
        ]);
    }


    public function get_lessonId(Request $request)
    {
        $getLessonId =  SubLesson::where('id', $request->task_id)->pluck('lesson_id');
        return response()->json(['status' => 'success', 'lessonId' => $getLessonId[0]]);
    }



    public function backToDeferredLesson(Request $request)
    {
        DB::beginTransaction();

        try {
            // Create def task
            $def_task = [
                'event_id'   => $request->event_id,
                'user_id'    => $request->user_id,
                'task_id'    => $request->task_id,
                'created_by' => auth()->id(),
            ];

            DefTask::create($def_task);

            // Delete sub lesson
            SubLesson::where('id', $request->sublesson_id)
                ->where('lesson_id', $request->lesson_id)
                ->delete();

            TaskGrading::where('sub_lesson_id', $request->sublesson_id)
                ->where('lesson_id', $request->lesson_id)
                ->delete();

            // Commit if everything is fine
            DB::commit();

            return response()->json(['status' => 'success']);
        } catch (Exception $e) {
            // Rollback all queries if something fails
            DB::rollBack();

            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage() 
            ], 500);
        }
    }


   public function unarchieveUser()
    {
        $today = now();
        // Group training events by student_id
        $students = TrainingEvents::select('student_id')
            ->groupBy('student_id')
            ->get();

        foreach ($students as $student) {
            $events = TrainingEvents::where('student_id', $student->student_id)->get();

            // Skip if no events found
            if ($events->isEmpty()) continue;

            $totalEvents = $events->count();
            $completedEvents = $events->whereNotNull('course_end_date')->count();

            // Proceed only if all events are completed
            if ($completedEvents === $totalEvents) {

                // Get the latest (most recent) course_end_date
                $latestEndDate = $events->max('course_end_date');

                // Check if last event ended at least 1 month ago
                if ($latestEndDate && $today->diffInMonths($latestEndDate) >= 1) {
                    $user = User::find($student->student_id);
                  
                    if ($user && $user->role == 3) {   
                        $user->update(['is_activated' => 1]);
                    }
                }
            }
        }
    }

    public function archieveUser()  
    {
        $user = User::with('organization')->where('is_activated', 1)->get();
        return view('users.archieveUser', compact('user')); 
    }

    public function unarchive(Request $request)
    {
        // Find and update the user
        $user = User::find($request->user_id);

        if ($user) {
            $user->update(['is_activated' => 0, "unarchived_by"  => auth()->user()->id]);
            Session::flash('message', "{$user->fname} {$user->lname} unarchived successfully");
            return response()->json([
                'success' => true,
                'message' => "{$user->fname} {$user->lname} unarchived successfully"
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'User not found'
        ], 404);
    }

    public function review_store(Request $request)
    {
        $validatedData = $request->validate([
            'review' => 'required',
        ]);

        TrainingEventReview::create([
            "event_id" => $request->event_id,
            "user_id" => auth()->user()->id,
            "review"  => $request->review
        ]);

        return response()->json(['success' => true, 'message' => 'Review saved successfully.']);
    }

    public function calender()
    {
        $resources  = Resource::all();
        return view('trainings.calender', compact('resources'));
    }

}
