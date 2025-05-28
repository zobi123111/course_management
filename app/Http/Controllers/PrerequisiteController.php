<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CoursePrerequisiteDetail;
use App\Models\Courses;

class PrerequisiteController extends Controller
{
    public function store(Request $request, Courses $course)
    {
        $request->validate([
            'prerequisite_details.*' => 'nullable',
        ]);

        foreach ($course->prerequisites as $index => $prerequisite) {
            $detail = $request->input("prerequisite_details.$index");

            $baseConditions = [
                'course_id' => $course->id,
                'prereq_id' => $prerequisite->id,
                'created_by' => auth()->id(),
            ];

            $existing = CoursePrerequisiteDetail::where($baseConditions)->first();

            $data = [
                'prerequisite_type' => $prerequisite->prerequisite_type,
            ];

            if ($prerequisite->prerequisite_type === 'file') {
                if ($request->hasFile("prerequisite_details.$index")) {
                    $file = $request->file("prerequisite_details.$index");
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];

                    if (!in_array($file->getClientOriginalExtension(), $allowedExtensions) || $file->getSize() > 10485760) {
                        return back()->withErrors([
                            "prerequisite_details.$index" => 'Invalid file type or size. Only JPG, JPEG, PNG, and PDF files under 10MB are allowed.',
                        ])->withInput();
                    }

                    $path = $file->store('prerequisites', 'public');
                    $data['file_path'] = $path;
                } else {
                    // Keep existing file path if no new file uploaded
                    $data['file_path'] = $existing->file_path ?? null;
                }

                $data['prerequisite_detail'] = null;
            } else {
                $data['prerequisite_detail'] = $detail;
                $data['file_path'] = null;
            }

            if ($existing) {
                $existing->update($data);
            } else {
                CoursePrerequisiteDetail::create(array_merge($baseConditions, $data));
            }
        }

        return back()->with('message', 'Prerequisites saved successfully.');
    }    
}
