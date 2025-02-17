<?php

namespace App\Http\Controllers;

use App\Models\SubLesson;
use Illuminate\Http\Request;
use App\Models\CourseLesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class SubLessonController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $id = Auth::user()->ou_id;
    //     if(Auth::user()->role==1 && empty(Auth::user()->ou_id)){
    //         $lessons = CourseLesson::all();
    //     }else{            
    //         $lessons = CourseLesson::where('ou_id', $id)->get();
    //     }

    //     // dd($lessons);
    //     return view('lesson.show',compact('lessons'));
    // }

    /**
     * Show the form for creating a new resource.
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
