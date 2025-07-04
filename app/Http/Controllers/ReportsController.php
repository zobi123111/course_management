<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Courses;
use App\Models\TrainingEvents;
use App\Models\TrainingEventLessons;
use App\Models\TaskGrading;
use App\Models\CompetencyGrading;
use App\Models\OverallAssessment;
use Illuminate\Http\Request;

class ReportsController extends Controller
{
    public function index()
    {
        $userOuId = auth()->user()->ou_id;

        // Get all courses in this OU
        $courses = Courses::where('ou_id', $userOuId)->get();

        // Get all training events for this OU grouped by course
        $trainingEvents = TrainingEvents::withCount(['taskGradings', 'competencyGradings'])
            ->where('ou_id', $userOuId)
            ->get()
            ->groupBy('course_id');

        // Enrich courses with student counts
        foreach ($courses as $course) {
            $events = $trainingEvents->get($course->id, collect());

            $course->students_enrolled = $events->pluck('student_id')->unique()->count();

            $course->students_completed = $events->filter(function ($event) {
                return $event->task_gradings_count > 0 && $event->competency_gradings_count > 0;
            })->pluck('student_id')->unique()->count();

            // Optional: attach detailed student info
            $course->students = $events->map(function ($event) {
                return $event->student;
            })->unique('id');
        }

        // dd($courses);
        return view('reports.index', compact('courses'));
    }


}
