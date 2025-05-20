<?php

namespace App\Http\Controllers;

use App\Models\SubLesson;
use Illuminate\Http\Request;
use App\Models\CourseLesson;
use App\Models\Courses;
use App\Models\TrainingEvents;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Models\LessonPrerequisite;
use App\Models\LessonPrerequisiteDetail;
use PDF;

class LessonController extends Controller 
{
    /**
     * Display a listing of the resource.
     */
    
    public function showCourse(Request $request, $course_id)
    {
        $user = auth()->user();
        $course = Courses::with('courseLessons', 'prerequisites')->findOrFail(decode_id($course_id));

        $breadcrumbs = [
            ['title' => 'Courses', 'url' => route('course.index')],
            ['title' => $course->course_name, 'url' => ''],
        ];
        if(get_user_role($user->role) == 'student'){
            $studentAcknowledged = TrainingEvents::where('course_id', decode_id($course_id))
            ->where('student_id', $user->id)
            ->where('student_acknowledged', 1)
            ->exists(); // returns true/false
        }else{
            $studentAcknowledged = false;
        }
        return view('courses.show', compact('course', 'breadcrumbs','studentAcknowledged'));
    }


    public function createLesson(Request $request)
    {
        // Validate request data
        $request->validate([
            'lesson_title' => 'required',
            'description' => 'required|string',
            'status' => 'required|boolean',
            'grade_type' => 'required|in:pass_fail,score',
            'enable_cbta' => 'sometimes|boolean'
        ]);
    
        if ($request->has('comment_required') && $request->comment_required) {
            $request->validate([
                'comment' => 'required|string',
            ], [
                'comment.required' => 'Comment field is required',
            ]);
        }
    
        CourseLesson::create([
            'course_id' => $request->course_id,
            'lesson_title' => $request->lesson_title,
            'description' => $request->description,
            'comment' => $request->comment ?? null,
            'status' => $request->status,
            'grade_type' => $request->grade_type,
            'enable_cbta' => $request->enable_cbta ?? 0
        ]);
    
        Session::flash('message', 'Lesson created successfully.');
        return response()->json(['success' => 'Lesson created successfully.']);
    }
    


    public function getLesson(Request $request)
    {
        $lesson = CourseLesson::with('prerequisites')->findOrFail(decode_id($request->id));
        return response()->json(['lesson'=> $lesson]);
    }

    public function showLesson(Request $request)
    {
        $lesson = CourseLesson::with('sublessons')->findOrFail(decode_id($request->id));

        $course = $lesson->course;

        $breadcrumbs = [
            ['title' => 'Courses', 'url' => route('course.index')],
            ['title' => $course->course_name, 'url' => route('course.show', encode_id($course->id))],
            ['title' => $lesson->lesson_title, 'url' => ''],
        ];

        return view('lesson.show', compact('lesson', 'breadcrumbs'));
    }


    public function updateLesson(Request $request)
    {
        // Validate request data
        $request->validate([
            'edit_lesson_title' => 'required',
            'edit_description' => 'required|string',
            'edit_status' => 'required|boolean',
            'edit_grade_type' => 'required|in:pass_fail,score',
            'edit_enable_cbta'    => 'sometimes|boolean',
        ]);
    
        if ($request->has('edit_comment_required') && $request->edit_comment_required) {
            $request->validate([
                'edit_comment' => 'required|string',
            ], [
                'edit_comment.required' => 'Comment field is required',
            ]);
        }
    
        $comment = $request->has('edit_comment_required') && !$request->edit_comment_required ? null : $request->edit_comment;
        
        $lesson = CourseLesson::findOrFail($request->lesson_id);
        $lesson->update([
            'lesson_title' => $request->edit_lesson_title,
            'description' => $request->edit_description,
            'comment' => $comment,
            'status' => $request->edit_status,
            'grade_type' => $request->edit_grade_type, // Update grading type
            'enable_cbta' => $request->edit_enable_cbta ?? 0, // Update enable_cbta
            'enable_prerequisites' => (int) $request->input('enable_prerequisites', 0),
        ]);
    
        // Handle Prerequisites
        if ((int) $request->input('enable_prerequisites', 0)) {
            $lesson->prerequisites()->delete(); 
    
            if ($request->has('prerequisite_details')) {
                foreach ($request->prerequisite_details as $index => $detail) {
                    if (!empty($detail)) {
                        LessonPrerequisite::create([
                            'lesson_id' => $lesson->id,
                            'course_id' => $lesson->course_id,
                            'prerequisite_detail' => $detail,
                            'prerequisite_type' => $request->prerequisite_type[$index] ?? 'text',
                        ]);
                    }
                }
            }
        } else {
            $lesson->prerequisites()->delete();
            LessonPrerequisiteDetail::where('lesson_id', $lesson->id)
                ->where('created_by', auth()->id())
                ->where('course_id', $lesson->course_id)
                ->delete();
        }
    
        Session::flash('message', 'Lesson updated successfully.');
        return response()->json(['success' => 'Lesson updated successfully.']);
    }
    

    public function deleteLesson(Request $request)
    {        
        $lesson = CourseLesson::findOrFail(decode_id($request->lesson_id));
        if ($lesson) {
            $course_id = $lesson->course_id;
            $lesson->delete();
            return redirect()->route('course.show',['course_id' => encode_id($course_id)])->with('message', 'This lesson deleted successfully');
        }
    }

     


    
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(SubLesson $sub_lesson)
    {
        
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SubLesson $sub_lesson)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SubLesson $sub_lesson)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SubLesson $sub_lesson)
    {
        //
    }

    
    public function prerequisitesStore(Request $request, Courses $course, CourseLesson $lesson) {

        $request->validate([
            'prerequisite_details.*' => 'nullable',
        ]);
        LessonPrerequisiteDetail::where('course_id', $course->id)
        ->where('lesson_id', $lesson->id)
        ->where('created_by', auth()->id())
        ->delete();
        foreach ($lesson->prerequisites as $index => $prerequisite) {
            $detail = $request->input("prerequisite_details.$index");
    
            if ($prerequisite->prerequisite_type == 'file' && $request->hasFile("prerequisite_details.$index")) {
                $file = $request->file("prerequisite_details.$index");
                if (!in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'pdf']) || $file->getSize() > 2048000) {
                    return back()->withErrors([
                        "prerequisite_details.$index" => 'Invalid file type or size. Only JPG, JPEG, PNG, and PDF files under 2MB are allowed.',
                    ])->withInput();
                }
                $path = $file->store('prerequisites', 'public');
    
                LessonPrerequisiteDetail::create([
                    'course_id' => $course->id,
                    'lesson_id' => $lesson->id,
                    'prerequisite_type' => $prerequisite->prerequisite_type,
                    'prerequisite_detail' => null,
                    'file_path' => $path,
                    'created_by' => auth()->id(),
                ]);
            } else {
                // dd("uu");
                LessonPrerequisiteDetail::create([
                    'course_id' => $course->id,
                    'prerequisite_type' => $prerequisite->prerequisite_type,
                    'prerequisite_detail' => $detail,
                    'file_path' => null,
                    'lesson_id' => $lesson->id,
                    'created_by' => auth()->id(),
                ]);
            }
        }
    
        return back()->with('success', 'Prerequisites saved successfully.');
    }

    public function lessonPdf(Request $request, $lessonId)
    {
        $lesson_detail = CourseLesson::with('course')->where('id', $lessonId)->get();
       // dd($sublesson_detail[0]['course']['course_name']);
        $data = [
            'date' => date('m/d/Y'),
            'lesson_detail' => $lesson_detail
        ]; 
        $pdf = PDF::loadView('courses\generateLessonPdf', $data);
        return $pdf->download('lesson.pdf');
    }

}
