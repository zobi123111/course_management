<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CoursePrerequisiteDetail;
use App\Models\Courses;

class PrerequisiteController extends Controller
{
    public function store(Request $request, Courses $course) {
        $request->validate([
            'prerequisite_details.*' => 'nullable',
        ]);
        CoursePrerequisiteDetail::where('course_id', $course->id)
        ->where('created_by', auth()->id())
        ->delete();
        foreach ($course->prerequisites as $index => $prerequisite) {
            $detail = $request->input("prerequisite_details.$index");
    
            if ($prerequisite->prerequisite_type == 'file' && $request->hasFile("prerequisite_details.$index")) {
                $file = $request->file("prerequisite_details.$index");
                if (!in_array($file->getClientOriginalExtension(), ['jpg', 'jpeg', 'png', 'pdf']) || $file->getSize() > 2048000) {
                    return back()->withErrors([
                        "prerequisite_details.$index" => 'Invalid file type or size. Only JPG, JPEG, PNG, and PDF files under 2MB are allowed.',
                    ])->withInput();
                }
                $path = $file->store('prerequisites', 'public');
    
                CoursePrerequisiteDetail::create([
                    'course_id' => $course->id,
                    'prerequisite_type' => $prerequisite->prerequisite_type,
                    'prerequisite_detail' => null,
                    'file_path' => $path,
                    'created_by' => auth()->id(),
                ]);
            } else {
                CoursePrerequisiteDetail::create([
                    'course_id' => $course->id,
                    'prerequisite_type' => $prerequisite->prerequisite_type,
                    'prerequisite_detail' => $detail,
                    'file_path' => null,
                    'created_by' => auth()->id(),
                ]);
            }
        }
    
        return back()->with('success', 'Prerequisites saved successfully.');
    }
    
}
