<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CourseTemplate;
use App\Models\CourseTemplateField;
use App\Models\OrganizationUnits;

class CourseTemplateController extends Controller
{
    public function index()
    {
        $current_user =auth()->user();
        if($current_user->is_owner==1){
            $courseTemplate = CourseTemplate::all();
        }elseif($current_user->is_admin==1){
            $courseTemplate = CourseTemplate::where('ou_id', $current_user->ou_id)->get();            
        }else{
            $courseTemplate = CourseTemplate::where('ou_id', $current_user->ou_id)->get();            
        }
        return view('course-template.index', compact('courseTemplate'));
    }

    public function createCourseTemplate(Request $request)
    {
        // dd($request);
        $organizationUnits = OrganizationUnits::all();
        return view('course-template.create', compact('organizationUnits'));
    }

    public function saveCourseTemplate(Request $request)
    {
        // dd($request->all());
        // Validate the incoming request data
        $validatedData = $request->validate([
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
            'name' => 'required|string|max:255|unique:course_templates,name,NULL,id,deleted_at,NULL',
            'description' => 'nullable|string',
            'enable_cbta' => 'nullable|boolean',
            'enable_manual_time_entry' => 'nullable|boolean',
            'fields' => 'required|array|min:1', // At least one field is required
            'fields.*.name' => 'required|string|max:255',
            'fields.*.grading_type' => 'required|in:pass_fail,deferred,numeric',
        ]);
    
        // Store the course template
        $courseTemplate = CourseTemplate::create([
            'ou_id' => (auth()->user()->is_owner == 1) ? $request->ou_id : auth()->user()->ou_id,
            'name' => $validatedData['name'],
            'description' => $validatedData['description'] ?? null,
            'enable_cbta' => $request->has('enable_cbta'), // Convert checkbox to boolean
            'enable_manual_time_entry' => $request->has('enable_manual_time_entry'),
        ]);
    
        // Save template fields in `course_template_fields` table
        foreach ($validatedData['fields'] as $field) {
            CourseTemplateField::create([
                'template_id' => $courseTemplate->id, // Foreign key reference
                'field_name' => $field['name'],
                'grading_type' => $field['grading_type'],
            ]);
        }
    
        return redirect()->route('course-template.index')->with('success', 'Course template created successfully.');
    }
    
}
