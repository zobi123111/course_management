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
        $lesson = CourseLesson::findOrFail($request->lesson_id);
        $rules = [
            'sub_lesson_title' => 'required',
            'sub_description'  => 'required',
            'sub_status'       => 'required|boolean',
            'mandatory'        => 'nullable|boolean',
        ];
        if ($lesson->grade_type !== 'percentage') {
            $rules['grade_type'] = 'required|in:pass_fail,score';
        }
        $request->validate($rules);
        $data = [
            'lesson_id'    => $request->lesson_id,
            'title'        => $request->sub_lesson_title,
            'description'  => $request->sub_description,
            'status'       => $request->sub_status,
            'is_mandatory' => $request->has('mandatory') ? 1 : 0,
        ];

        if ($lesson->grade_type !== 'percentage') {
            $data['grade_type'] = $request->grade_type;
        }
        SubLesson::create($data);

        Session::flash('message', 'Task created successfully.');
        return response()->json(['success' => 'Task created successfully.']);
    }


    public function getSubLesson(Request $request)
    {
        // dd(decode_id($request->id));
        $subLesson = SubLesson::findOrFail(decode_id($request->id));
        return response()->json(['subLesson'=> $subLesson]);
    }

    
    public function reorder(Request $request)
    {
        foreach ($request->order as $item) {
            SubLesson::where('id', $item['id'])->update(['position' => $item['position']]);
        }

        return response()->json(['status' => 'success']);
    }

    public function updateSubLesson(Request $request)
    {
        // Find the sub-lesson
        $lesson = SubLesson::findOrFail($request->edit_sub_lesson_id);

        // Get the parent lesson
        $parentLesson = CourseLesson::findOrFail($lesson->lesson_id);

        // Validation rules
        $rules = [
            'edit_sub_lesson_title' => 'required',
            'edit_sub_description'  => 'required',
            'edit_sub_status'       => 'required|boolean',
            // 'edit_mandatory' => 'nullable|boolean',
        ];

        if ($parentLesson->grade_type !== 'percentage') {
            $rules['edit_grade_type'] = 'required|in:pass_fail,score';
        }

        $request->validate($rules);

        // Prepare update data
        $updateData = [
            'title'         => $request->edit_sub_lesson_title,
            'description'   => $request->edit_sub_description,
            'status'        => $request->edit_sub_status,
            'is_mandatory'  => $request->has('edit_mandatory') ? 1 : 0,
        ];

        if ($parentLesson->grade_type !== 'percentage') {
            $updateData['grade_type'] = $request->edit_grade_type;
        } else {
            $updateData['grade_type'] = null;
        }

        // Update sub-lesson
        $lesson->update($updateData);

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
