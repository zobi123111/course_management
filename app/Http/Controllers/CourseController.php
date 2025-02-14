<?php

namespace App\Http\Controllers; 

use Illuminate\Http\Request;
use App\Models\Courses;
use App\Models\OrganizationUnits;
use App\Models\CourseLesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;


class CourseController extends Controller
{
    public function index()
    {
        $ouId = Auth::user()->ou_id;
        if(Auth::user()->role==1 && empty(Auth::user()->ou_id)){
            $courses = Courses::all();
        }else{            
            $courses = Courses::where('ou_id', $ouId)->get();
        }
        $urganizationUnits = OrganizationUnits::all();
        return view('courses.index',compact('courses','urganizationUnits'));
    }

    public function create_course()
    {
        return view('Courses.create_course');
    }

    public function createCourse(Request $request)
    {
        // dd($request->all());
        $request->validate([            
            'course_name' => 'required|unique:courses',
            'description' => 'required',
            'image' => 'required',
            'status' => 'required|boolean',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ]
        ]);

        if ($request->hasFile('image')) {
            $filePath = $request->file('image')->store('courses', 'public');
        }

        Courses::create([
            'ou_id' =>  (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id, // Assign ou_id only if Super Admin provided it
            'course_name' => $request->course_name,
            'description' => $request->description,
            'image' => $filePath ?? null,
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
            'status' => 'required',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ]
        ]);

        $courses = Courses::findOrFail($request->course_id);
        
        if ($request->hasFile('image')) {
            if ($courses->image) {
                Storage::disk('public')->delete($courses->image);
            }
    
            $filePath = $request->file('image')->store('courses', 'public');
        } else {
            $filePath = $courses->image;
        }

        $course = Courses::findOrFail($request->course_id);
        $course->update([
            'ou_id' =>  (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id, // Assign ou_id only if Super Admin provided it
            'course_name' => $request->course_name,
            'description' => $request->description,
            'image' => $filePath,
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


}
