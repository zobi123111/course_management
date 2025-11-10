<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Courses;
use App\Models\User;
use App\Models\TrainingEvents;
use App\Models\TrainingEventLessons;
use App\Models\TaskGrading;
use App\Models\CompetencyGrading;
use App\Models\OverallAssessment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use App\Models\OrganizationUnits;
use App\Models\DefLessonTask;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ReportsController extends Controller
{
    // public function index()
    // {
    //     $user = auth()->user();
    //     $userOuId = $user->ou_id;
    //     $courses = TrainingEvents::with(['course', 'student'])
    //         ->when($user->is_owner != 1, function ($query) use ($userOuId) {
    //             $query->where('ou_id', $userOuId)
    //                 ->whereNull('deleted_at');
    //         })
    //         ->orderBy('id', 'asc')
    //         ->get();


    //     foreach ($courses as $event) {

    //         $student = $event->student;
    //         if (!$student) {
    //             continue;
    //         }
    //         $enrolledStudentIds = collect([$student->id]);
    //         $completedStudentIds = collect();

    //         // dump($enrolledStudentIds);
    //         $completedStudentIds = collect();
    //         // If course has ended
    //         if (!empty($event->course_end_date)) {
    //             $completedStudentIds->push($student->id);
    //         } else {
    //             // Check grading
    //             $grades = TaskGrading::where('user_id', $student->id)
    //                 ->when($event->id, fn($q) => $q->where('event_id', $event->id))
    //                 ->pluck('task_grade')
    //                 ->map(fn($g) => strtolower((string) $g));

    //             $total      = $grades->count();
    //             $incomplete = $grades->filter(fn($g) => in_array($g, ['1', 'incomplete']))->count();
    //             $further    = $grades->filter(fn($g) => in_array($g, ['2', 'further training required']))->count();

    //             if ($incomplete == 0 && $further == 0 && $total > 0) {
    //                 $completedStudentIds->push($student->id);
    //             }
    //         }
    //         $event->students_enrolled  = $enrolledStudentIds->count();
    //         $event->students_completed = $completedStudentIds->unique()->count();
    //         $event->students_active    = $enrolledStudentIds->diff($completedStudentIds->unique())->count();

    //     }

    //     $ous = $user->is_owner ? OrganizationUnits::select('id', 'org_unit_name')->get() : [];

    //     return view('reports.index', compact('courses', 'ous'));
    // }
public function index()
{
    $user = auth()->user();
    $userOuId = $user->ou_id;

    $events = TrainingEvents::with(['course', 'student'])
        ->when($user->is_owner != 1, function ($query) use ($userOuId) {
            $query->where('ou_id', $userOuId)
                ->whereNull('deleted_at');
        })
        ->orderBy('id', 'asc')
        ->get();

    // Group events by course_id
    $courses = $events->groupBy('course_id')->map(function ($courseEvents) {
        $course = $courseEvents->first()->course;

        $enrolledStudentIds  = collect();
        $completedStudentIds = collect();
        $archivedStudentIds  = collect();

        foreach ($courseEvents as $event) {
            $student = $event->student;
            if (!$student) {
                continue;
            }

            // enrolled
            $enrolledStudentIds->push($student->id); 

              if ($student->is_activated == 1) {
                $archivedStudentIds->push($student->id);
            }

            // completed check
            if (!empty($event->course_end_date)) {
                $completedStudentIds->push($student->id);
            } else {
                $grades = TaskGrading::where('user_id', $student->id)
                    ->where('event_id', $event->id)
                    ->pluck('task_grade')
                    ->map(fn($g) => strtolower((string) $g));

                $total      = $grades->count();
                $incomplete = $grades->filter(fn($g) => in_array($g, ['1', 'incomplete']))->count();
                $further    = $grades->filter(fn($g) => in_array($g, ['2', 'further training required']))->count();

                if ($incomplete == 0 && $further == 0 && $total > 0) {
                    $completedStudentIds->push($student->id);
                }
            }
        }
        // attach counts to course
        $course->students_enrolled  = $enrolledStudentIds->unique()->count();
        $course->students_completed = $completedStudentIds->unique()->count();
        $course->students_active    = $enrolledStudentIds->diff($completedStudentIds)->unique()->count();
        $course->archived           = $archivedStudentIds->unique()->count();

        return $course;
    });
 

    $ous = $user->is_owner ? OrganizationUnits::select('id', 'org_unit_name')->get() : [];

    return view('reports.index', compact('courses', 'ous'));
}



    public function showCourse($hashedId, Request $request)
    {
        $id = decode_id($hashedId);

        if (!$id) {
            abort(404, 'Invalid Course ID');
        }

        $showArchived = $request->has('show_archived') && $request->show_archived == '1';
        $showFailing = $request->has('show_failing') && $request->show_failing == '1';

        

        $course = Courses::with(['trainingEvents.student'])->findOrFail($id);

        // Create a map of student_id => training_event data
        $eventMap = $course->trainingEvents
            ->keyBy('student_id')
            ->map(function ($event) {
                return [
                    'event_id' => $event->id,
                    'event_date' => $event->event_date,
                    'course_end_date' => $event->course_end_date,
                ];
            });

        // Get unique students
        $students = $course->trainingEvents
                    ->pluck('student')
                    ->filter();
        $students = $students
                    ->unique('id')
                    ->values();   
                 

       $employees = $course->trainingEvents
                        ->pluck('student')
                        ->filter(); 
                        if ($showArchived == true) {
                        } else {
                            $employees = $employees->filter(fn($student) => $student->is_activated == 0);
                        }
                        $employees = $employees
                            ->unique('id')
                            ->values();                 
          

        $activeStudents = $students->filter(fn($s) => $s->course_end_date === null);
        $activeStudentIds = $activeStudents->pluck('id');

        // Active training events only
        $activeEventIds = $course->trainingEvents
            ->filter(fn($e) => $e->course_end_date === null && $e->student !== null)
            ->pluck('id');

        // Apply failing student filter
        $failingStudentIds = TaskGrading::whereIn('task_grade', ['1', '2', 'Incomplete', 'Further training required'])
            ->whereIn('user_id', $activeStudentIds)
            ->whereIn('event_id', $activeEventIds)
            ->pluck('user_id')
            ->unique();

        if ($showFailing) {
            $students->each(function ($student) use ($failingStudentIds) {
                $student->is_failing = $failingStudentIds->contains($student->id);
            });
        }

        // Attach event data to each student
        $students->transform(function ($student) use ($eventMap) {
            $student->event_id = $eventMap[$student->id]['event_id'] ?? null;
            $student->event_date = $eventMap[$student->id]['event_date'] ?? null;
            $student->course_end_date = $eventMap[$student->id]['course_end_date'] ?? null;

            // Alert logic
            $student->show_alert = false;
            if ($student->event_date && !$student->course_end_date) {
                $startDate = Carbon::parse($student->event_date);
                $daysSinceStart = $startDate->diffInDays(Carbon::now());
                $student->show_alert = $daysSinceStart >= 150;
            }
            
            // Progress breakdown from task_grading
            $grades = TaskGrading::where('user_id', $student->id)
                ->when($student->event_id, fn($q) => $q->where('event_id', $student->event_id))
                ->pluck('task_grade')
                ->map(fn($grade) => strtolower((string) $grade));

            $total = $grades->count();

  
            // 1️⃣ Fetch student's TaskGrading records
                $grades = TaskGrading::where('user_id', $student->id)
                    ->when($student->event_id, fn($q) => $q->where('event_id', $student->event_id))
                    ->get(['sub_lesson_id', 'task_grade']);

                // 2️⃣ Fetch DefLessonTask records for comparison
              $defTasks = DefLessonTask::where('user_id', $student->id)
                        ->when($student->event_id, fn($q) => $q->where('event_id', $student->event_id))
                        ->get(['task_id', 'task_grade']);

                // 3️⃣ Normalize all task grades to lowercase strings for consistent comparison
              $normalizedGrades = $grades->map(function ($g) {
                    $g->task_grade = strtolower((string) $g->task_grade);
                    return $g;
                });

              $normalizedDef = $defTasks->map(function ($d) {
                    $d->task_grade = strtolower((string) $d->task_grade);
                    return $d;
                });
                // 4️⃣ Initialize progress counters
              $progress = [
                    'total' => $normalizedGrades->count(),
                    'incomplete' => 0,
                    'further' => 0,
                    'competent' => 0,
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


            // Completion logic
            $student->is_completed = false;
            if ($student->course_end_date !== null) {
                $student->is_completed = true;
            } elseif ($student->progress['incomplete'] == 0 && $student->progress['further'] == 0 && $student->progress['total'] > 0) {
                $student->is_completed = true;
            }

            return $student;
        });
        // === Chart Data ===
       //  dump($students);
        $chartData = [
            'enrolled'    => $students->count(),
            'completed'   => $students->filter(fn($s) => $s->is_completed)->count(),
            'active'      => $students->filter(fn($s) => !$s->is_archived && $s->course_end_date === null)->count(),
            'archived'    => $students->filter(fn($s) => $s->is_archived)->count(),
            'is_activated' => $students->filter(fn($s) => $s->is_activated)->count(),
            'failing'     => $failingStudentIds->intersect($students->pluck('id'))->count(),
        ]; 

        return view('reports.course_detail', compact('course', 'students', 'showArchived', 'showFailing', 'chartData', 'employees'));
    } 

    public function updateStudentArchiveStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:users,id',
            'event_id' => 'required|exists:training_events,id',
            'is_archived' => 'required|boolean',
        ]);

        $student = User::findOrFail($request->id);
        $student->is_archived = $request->is_archived;
        $student->save();

        $event = TrainingEvents::find($request->event_id);
        if (!$event || !$event->course_id) {
            return response()->json(['message' => 'Updated, but course not found']);
        }

        $course = Courses::with(['trainingEvents.student'])->find($event->course_id);
        // Get all students with attached event data
        $students = $course->trainingEvents
            ->pluck('student')
            ->filter()
            ->map(function ($student) use ($course) {
                $event = $course->trainingEvents->firstWhere('student_id', $student->id);
                $student->course_end_date = $event->course_end_date ?? null;
                $student->is_archived = $student->is_archived ?? 0;
                return $student;
            })
            ->unique('id')
            ->values();

        $activeStudents = $students->filter(fn($s) => $s->course_end_date === null);
        $activeStudentIds = $activeStudents->pluck('id');
        $activeEventIds = $course->trainingEvents
            ->filter(fn($e) => $e->course_end_date === null && $e->student !== null)
            ->pluck('id');

        $failingStudentIds = TaskGrading::whereIn('task_grade', ['1', '2', 'Incomplete', 'Further training required'])
            ->whereIn('user_id', $activeStudentIds)
            ->whereIn('event_id', $activeEventIds)
            ->pluck('user_id')
            ->unique();

        $chartData = [
            'enrolled'    => $students->count(),
            'completed' => $students->filter(function ($s) {
                // Consider completed if course_end_date exists OR all progress is competent
                if ($s->course_end_date !== null) {
                    return true;
                }
              

                // Fallback to check task_grading competency
                $grades = TaskGrading::where('user_id', $s->id)
                    ->when($s->event_id, fn($q) => $q->where('event_id', $s->event_id))
                    ->pluck('task_grade')
                    ->map(fn($grade) => strtolower((string) $grade));

                $total = $grades->count();
                $incomplete = $grades->filter(fn($g) => in_array($g, ['1', 'incomplete']))->count();
                $further = $grades->filter(fn($g) => in_array($g, ['2', 'further training required']))->count();

                return $total > 0 && $incomplete == 0 && $further == 0;
            })->count(),
            'active'      => $students->filter(fn($s) => !$s->is_archived && $s->course_end_date === null)->count(),
            'archived'    => $students->filter(fn($s) => $s->is_archived)->count(),
            'failing'     => $failingStudentIds->intersect($students->pluck('id'))->count(),
        ];

        return response()->json([
            'message' => $request->is_archived ? 'Student archived successfully.' : 'Student unarchived successfully.',
            'chartData' => $chartData
        ]);
    }

    public function getStudentReports() 
    {
        $user = auth()->user();
        $ou_id = $user->ou_id;
        $userId = $user->id;
        

        $users = User::where('ou_id', $ou_id)
            ->whereNull('is_admin')
            ->with([
                'documents',
                'usrRatings' => function ($query) {
                    $query->whereIn('linked_to', ['licence_1', 'licence_2'])
                        ->with([
                            'rating.associatedChildren', 
                            'parentRating'
                        ]);
                }
            ])
            ->get();
        
        return view('reports.student-report', compact('users'));
    }
}
