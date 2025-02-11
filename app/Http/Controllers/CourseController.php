<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Models\Courses;
use App\Models\CourseLesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CourseController extends Controller
{
    public function index()
    {
        $ouId = Auth::user()->ou_id;
        $courses = Courses::where('ou_id', $ouId)->get();
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
        $course = Courses::findOrFail(decode_id($request->id));
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
        $courses = Courses::findOrFail(decode_id($request->course_id));
        if ($courses) {
            $courses->delete();
            return redirect()->route('course.index')->with('message', 'This Course deleted successfully');
        }
    }

    public function showCourse(Request $request,$course_id)
    {
        // $course = Courses::with('courseLessons')->find(decode_id($course_id));
        $course = Courses::with('courseLessons')->findOrFail(decode_id($course_id));
        return view('courses.show', compact('course'));
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


    public function getLesson(Request $request)
    {
        $lesson = CourseLesson::findOrFail(decode_id($request->id));
        return response()->json(['lesson'=> $lesson]);
    }

    //Update course
    public function updateLesson(Request $request)
    {
        $request->validate([
            'edit_lesson_title' => 'required',
            'edit_description' => 'required',
            'edit_status' => 'required'
        ]);

        // dd($request);
        $lesson = CourseLesson::findOrFail($request->lesson_id);
        $lesson->update([
            'lesson_title' => $request->edit_lesson_title,
            'description' => $request->edit_description,
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


}
