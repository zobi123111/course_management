<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Models\Courses;
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
            'course_name' => $request->course_name,
            'description' => $request->description,
            'status' => $request->status
        ]);

        Session::flash('message', 'Course created successfully.');
        return response()->json(['success' => 'Course created successfully.']);
    }


    public function getCourse(Request $request)
    {
        dd($request);
        $course = Courses::findOrFail($id);
        return view('courses.edit', compact('course'));
    }

}
