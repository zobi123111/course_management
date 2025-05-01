<?php

namespace App\Http\Controllers;

use App\Models\SubLesson;
use Illuminate\Http\Request;
use App\Models\CourseLesson;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use PDF;

class SubLessonController extends Controller
{
    /**
        * SUB LESSON FUNCTIONS
    */    
    public function createSubLesson(Request $request)
    {
        // Validate input data
        $request->validate([            
            'sub_lesson_title' => 'required',
            'sub_description' => 'required',
            'sub_status' => 'required|boolean',
            'grade_type' => 'required|in:pass_fail,score', // Validate grade type
            'mandatory' => 'nullable|boolean', // Validate mandatory field
        ]);
    
        // Store data in the database
        SubLesson::create([
            'lesson_id' => $request->lesson_id,
            'title' => $request->sub_lesson_title,
            'description' => $request->sub_description,
            'status' => $request->sub_status,
            'grade_type' => $request->grade_type, // Save grade type
            'is_mandatory' => $request->has('mandatory') ? 1 : 0, // Save as 1 if checked, otherwise 0
        ]);
    
        Session::flash('message', 'Task created successfully.');
        return response()->json(['success' => 'Task created successfully.']);
    }
    
    

    public function getSubLesson(Request $request)
    {
        // dd(decode_id($request->id));
        $subLesson = SubLesson::findOrFail(decode_id($request->id));
        return response()->json(['subLesson'=> $subLesson]);
    }


    public function updateSubLesson(Request $request)
    {

        // Validate input data
        $request->validate([
            'edit_sub_lesson_title' => 'required',
            'edit_sub_description' => 'required',
            'edit_sub_status' => 'required|boolean',
            'edit_grade_type' => 'required|in:pass_fail,score', // Validate grade type
            'edit_mandatory' => 'nullable|boolean', // Validate mandatory field
        ]);
    
        // Find the existing sub-lesson
        $lesson = SubLesson::findOrFail($request->edit_sub_lesson_id);
    
        // Update the sub-lesson
        $lesson->update([
            'title' => $request->edit_sub_lesson_title,
            'description' => $request->edit_sub_description,
            'status' => $request->edit_sub_status,
            'grade_type' => $request->edit_grade_type, // Update grade type
            'is_mandatory' => $request->has('edit_mandatory') ? 1 : 0, // Save as 1 if checked, otherwise 0
        ]);
    
        Session::flash('message', 'Task updated successfully.');
        return response()->json(['success' => 'Task updated successfully.']);
    }
    
    

    public function deleteSubLesson(Request $request)
    {
        $sublesson = SubLesson::findOrFail(decode_id($request->sub_lesson_id));
        
        // dd($sublesson);

        if ($sublesson) {
            $lesson_id = $sublesson->lesson_id;
            $sublesson->delete();
            return redirect()->route('lesson.show',['id' => encode_id($lesson_id)])->with('message', 'This Task deleted successfully');
        }
    }



    // public function subLessonPdf(Request $request, $sublessonid)
    // {
    //     $sublesson_detail = CourseLesson::with('course')->where('id', $sublessonid)->get();
    //    // dd($sublesson_detail[0]['course']['course_name']);
    //     $data = [
    //         'date' => date('m/d/Y'),
    //         'sublesson_detail' => $sublesson_detail
    //     ]; 
    //     $pdf = PDF::loadView('courses\generateSublessonPdf', $data);
    //     return $pdf->download('sublesson.pdf');
    // }
}
