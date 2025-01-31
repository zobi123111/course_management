<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Models\Courses;
use App\Models\CourseLesson;
use Illuminate\Support\Facades\Session;

class CourseController extends Controller
{
    public function index()
    {
        $courses = Courses::all();
        return view('courses.index',compact('courses'));
    }

    public function create_course()
    {
        return view('Courses.create_course');
    }

    public function createCourse(Request $request)
    {
        $request->validate([            
            'course_name' => 'required',
            'description' => 'required',
            'status' => 'required|boolean'
        ]);

        Courses::create([
            'ou_id' => auth()->user()->ou_id,
            'course_name' => $request->course_name,
            'description' => $request->description,
            'status' => $request->status
        ]);

        Session::flash('message', 'Course created successfully.');
        return response()->json(['success' => 'Course created successfully.']);
    }


    public function getCourse(Request $request)
    {
        $course = Courses::findOrFail($request->id);
        return response()->json(['course'=> $course]);
    }

    //Update course
    public function updateCourse(Request $request)
    {
        $request->validate([
            'course_name' => 'required',
            'description' => 'required',
            'status' => 'required'
        ]);

        $course = Courses::findOrFail($request->course_id);
        $course->update([
            'course_name' => $request->course_name,
            'description' => $request->description,
            'status' => $request->status
        ]);

        Session::flash('message','Course updated successfully.');
        return response()->json(['success'=> 'Course updated successfully.']);
    }

    public function deleteCourse(Request $request)
    {        
        $courses = Courses::findOrFail($request->course_id);
        if ($courses) {
            $courses->delete();
            return redirect()->route('course.index')->with('message', 'This Course deleted successfully');
        }
    }

    public function showCourse(Request $request)
    {
        $courseId = $request->query('course_id');
        $course = Courses::findOrFail($courseId);
        $courseLesson = CourseLesson::all();
        return view('courses.show', compact('course', 'courseLesson'));
    }

    public function showLesson(Request $request)
    {
        // dd($request);
        $request->validate([            
            'lesson_title' => 'required',
            'description' => 'required',
            'status' => 'required|boolean'
        ]);

        CourseLesson::create([
            'course_id' => $request->course_id,
            'lesson_title' => $request->lesson_title,
            'description' => $request->description,
            'status' => $request->status
        ]);

        Session::flash('message', 'Lesson created successfully.');
        return response()->json(['success' => 'Lesson created successfully.']);
    }


}
