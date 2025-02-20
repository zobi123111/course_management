<?php

namespace App\Http\Controllers;

use App\Models\SubLesson;
use Illuminate\Http\Request;
use App\Models\CourseLesson;
use App\Models\Courses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class LessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    
    public function showCourse(Request $request, $course_id)
    {

        // dd($course_id);
        $course = Courses::with('courseLessons')->findOrFail(decode_id($course_id));

        // $course = Courses::findOrFail(decode_id($course_id));


        // dd($course->course_name);

        $breadcrumbs = [
            ['title' => 'Courses', 'url' => route('course.index')],
            ['title' => $course->course_name, 'url' => ''],
        ];

        return view('courses.show', compact('course', 'breadcrumbs'));
    }


    public function createLesson(Request $request)
    {
        // dd($request);
        $request->validate([            
            'lesson_title' => 'required',
            'description' => 'required',
            'status' => 'required|boolean'
        ]);

        if ($request->has('comment_required') && $request->comment_required) {
            $request->validate([
                'comment' => 'required|string',
            ],
            [
                'comment.required' => 'Comment field is required',
            ]);
        }

        CourseLesson::create([
            'course_id' => $request->course_id,
            'lesson_title' => $request->lesson_title,
            'description' => $request->description,
            'comment' => $request->comment,
            'status' => $request->status
        ]);


        Session::flash('message', 'Lesson created successfully.');
        return response()->json(['success' => 'Lesson created successfully.']);
    }


    public function getLesson(Request $request)
    {
        $lesson = CourseLesson::findOrFail(decode_id($request->id));
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


    //Update course
    public function updateLesson(Request $request)
    {
        $request->validate([
            'edit_lesson_title' => 'required',
            'edit_description' => 'required',
            'edit_status' => 'required'
        ]);

        if ($request->has('edit_comment_required') && $request->edit_comment_required) {
            $request->validate([
                'edit_comment' => 'required|string',
            ],
            [
                'edit_comment.required' => 'Comment field is required',
            ]);
        }

        $comment = $request->has('edit_comment_required') && !$request->edit_comment_required ? null : $request->edit_comment;
        
        // dd($request);
        $lesson = CourseLesson::findOrFail($request->lesson_id);
        $lesson->update([
            'lesson_title' => $request->edit_lesson_title,
            'description' => $request->edit_description,
            'comment' => $comment,
            'status' => $request->edit_status
        ]);

        Session::flash('message','Lesson updated successfully.');
        return response()->json(['success'=> 'Lesson updated successfully.']);
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
        * SUB LESSON FUNCTIONS
    */

    
    public function createSubLesson(Request $request)
    {
        // dd($request->all());
        $request->validate([            
            'sub_lesson_title' => 'required',
            'sub_description' => 'required',
            'sub_status' => 'required|boolean',
        ]);

        SubLesson::create([
            'lesson_id' => $request->lesson_id,
            'title' => $request->sub_lesson_title,
            'description' => $request->sub_description,
            'status' => $request->sub_status
        ]);

        Session::flash('message', 'Sub Lesson created successfully.');
        return response()->json(['success' => 'Sub Lesson created successfully.']);
    }

    public function getSubLesson(Request $request)
    {
        // dd(decode_id($request->id));
        $subLesson = SubLesson::findOrFail(decode_id($request->id));
        return response()->json(['subLesson'=> $subLesson]);
    }


    public function updateSubLesson(Request $request)
    {

        // dd($request->all());
        $request->validate([
            'edit_sub_lesson_title' => 'required',
            'edit_sub_description' => 'required',
            'edit_sub_status' => 'required'
        ]);

        // if ($request->has('edit_comment_required') && $request->edit_comment_required) {
        //     $request->validate([
        //         'edit_comment' => 'required|string',
        //     ]);
        // }

        // $comment = $request->has('edit_comment_required') && !$request->edit_comment_required ? null : $request->edit_comment;
        
        // dd($request);
        $lesson = SubLesson::findOrFail($request->edit_sub_lesson_id);
        $lesson->update([
            'title' => $request->edit_sub_lesson_title,
            'description' => $request->edit_sub_description,
            'status' => $request->edit_sub_status
        ]);

        Session::flash('message','Sub-Lesson updated successfully.');
        return response()->json(['success'=> 'Sub-Lesson updated successfully.']);
    }

    public function deleteSubLesson(Request $request)
    {
        $sublesson = SubLesson::findOrFail(decode_id($request->sub_lesson_id));
        
        // dd($sublesson);

        if ($sublesson) {
            $lesson_id = $sublesson->lesson_id;
            $sublesson->delete();
            return redirect()->route('lesson.show',['id' => encode_id($lesson_id)])->with('message', 'This Sub-lesson deleted successfully');
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
}
