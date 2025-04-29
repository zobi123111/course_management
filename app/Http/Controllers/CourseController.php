<?php

namespace App\Http\Controllers;

use App\Models\CourseGroup;
use Illuminate\Http\Request;
use App\Models\Courses;
use App\Models\OrganizationUnits;
use App\Models\CoursePrerequisiteDetail;
use App\Models\CoursePrerequisite;
use App\Models\Group;
use App\Models\TrainingFeedbackQuestion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Models\Resource;
use App\Models\CourseResources;
use App\Models\CourseDocuments;


class CourseController extends Controller
{
    
    // public function index()
    // {
    //     $userId = Auth::user()->id;
    //     $ouId = Auth::user()->ou_id;
    //     $role = Auth::user()->role;


    
    //     if (checkAllowedModule('courses', 'course.index')->isNotEmpty()) {
    //         $groups = Group::all();
    //         $filteredGroups = $groups->filter(function ($group) use ($userId) {
    //             $userIds = is_array($group->user_ids) ? $group->user_ids : explode(',', $group->user_ids);
                
    //             return in_array($userId, $userIds);
    //         });
    
    //         $groupIds = $filteredGroups->pluck('id')->toArray();
    
    //         $courses = Courses::whereIn('id', function ($query) use ($groupIds) {
    //             $query->select('courses_id')
    //                 ->from('courses_group')
    //                 ->whereIn('group_id', $groupIds);
    //         })->get();
    //     } else {
    //         if ($role == 1 && empty($ouId)) {
    //             $courses = Courses::all();
    //         } else {
    //             $courses = Courses::where('ou_id', $ouId)->get();
    //         }
    
    //         $groups = Group::all();
    //     }
    
    //     $organizationUnits = OrganizationUnits::all();
    
    //     return view('courses.index', compact('courses', 'organizationUnits', 'groups'));
    // }
    
    public function index()
    {
        $userId = Auth::user()->id;
        $ouId = Auth::user()->ou_id;
        $role = Auth::user()->role;


    
        if (checkAllowedModule('courses', 'course.index')->isNotEmpty() && Auth()->user()->is_owner == 1) {
            // dd("if working");
            $courses = Courses::all();
            $groups = Group::all();  
            $resource  = Resource::all();
        } 
        elseif(checkAllowedModule('courses', 'course.index')->isNotEmpty() && Auth()->user()->is_admin ==  0)
        {
           // dd("else if working");
            $groups = Group::all();
            $resource  = Resource::all();

            $filteredGroups = $groups->filter(function ($group) use ($userId) {
                $userIds = is_array($group->user_ids) ? $group->user_ids : explode(',', $group->user_ids);              
                return in_array($userId, $userIds);
            });
            $groupIds = $filteredGroups->pluck('id')->toArray();
            $courses = Courses::whereIn('id', function ($query) use ($groupIds) {
                $query->select('courses_id')
                        ->from('courses_group')
                        ->whereIn('group_id', $groupIds);
                        })->where('status', 1)
                            ->get();
                     //  dump($courses);     
        }
        else 
        {
       //  dd("asds");
            if ($role == 1 && empty($ouId)) {
                $courses = Courses::all();
            } else {
                $courses = Courses::where('ou_id', $ouId)->get();
            }
            $groups = Group::where('ou_id', $ouId)->get();
            $resource  = Resource::where('ou_id', $ouId)->get();
        }
    
        $organizationUnits = OrganizationUnits::all();
        
    
        return view('courses.index', compact('courses', 'organizationUnits', 'groups', 'resource'));
    }


    public function create_course()
    {
        return view('Courses.create_course');
    }

    public function createCourse(Request $request)
    {

        // dd($request->all());
        if (!$request->enable_feedback) {
            $request->merge(['feedback_questions' => null]);
        }
    
        $request->validate([
            'course_name' => 'required|unique:courses,course_name,NULL,id,deleted_at,NULL',
            'description' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|boolean',
            'duration_type' => 'nullable|in:hours,events',
            'duration_value' => 'nullable|integer|min:1',
            'course_type' => 'required|in:one_event,multi_lesson',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
            'enable_feedback' => 'nullable|boolean',
            'feedback_questions' => 'nullable|array',
            'feedback_questions.*.question' => 'required_if:enable_feedback,1|string',
            'feedback_questions.*.answer_type' => 'required_if:enable_feedback,1|in:yes_no,rating',
    
            // Validation for Instructor Upload Documents
            'enable_instructor_upload' => 'nullable|boolean',
            'instructor_documents' => 'nullable|array',
            'instructor_documents.*.name' => 'string|max:255',
            'instructor_documents.*.file' => 'file|mimes:pdf,doc,docx,jpeg,png,jpg|max:15360', // 20MB max
        ], [], [
            'feedback_questions.*.question' => 'Feedback question',
            'feedback_questions.*.answer_type' => 'Answer type',
            'instructor_documents.*.name' => 'Document Name',
            'instructor_documents.*.file' => 'Document File',
        ]);
    
        if ($request->hasFile('image')) {
            $filePath = $request->file('image')->store('courses', 'public');
        }
    
        $course = Courses::create([
            'ou_id' => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id,
            'course_name' => $request->course_name,
            'description' => $request->description,
            'image' => $filePath ?? null,
            'status' => $request->status,
            'duration_type' => $request->duration_type ?? null,
            'duration_value' => $request->duration_value ?? null,
            'course_type' => $request->course_type,
            'enable_feedback' => (int) $request->input('enable_feedback', 0),
            'enable_instructor_upload' => (int) $request->input('enable_instructor_upload', 0),
        ]);
    
        $course->groups()->attach($request->group_ids);
        $course->resources()->attach($request->resources);
    
        // Store training feedback questions
        if ($request->enable_feedback && $request->feedback_questions) {
            foreach ($request->feedback_questions as $q) {
                TrainingFeedbackQuestion::create([
                    'course_id' => $course->id,
                    'question' => $q['question'],
                    'answer_type' => $q['answer_type'],
                ]);
            }
        }
    
        // Store Instructor Uploaded Documents
        if ($request->has('enable_instructor_upload')) {
            foreach ($request->instructor_documents as $index => $doc) {
                if (isset($doc['file'])) {
                    $uploadedFile = $doc['file'];
                    $originalName = $uploadedFile->getClientOriginalName(); // Get the original file name
                    $documentPath = $uploadedFile->storeAs('course_documents', $originalName, 'public');
    
                    CourseDocuments::create([
                        'course_id' => $course->id,
                        'document_name' => $doc['name'],
                        'file_path' => $documentPath,
                    ]);
                }
            }
        }
    
        Session::flash('message', 'Course created successfully.');
        return response()->json(['success' => 'Course created successfully.']);
    }
    

    



    public function getCourse(Request $request)
    {
    //    dd((decode_id($request->id)));
       $course = Courses::with(['groups', 'prerequisites', 'training_feedback_questions','documents'])
       ->findOrFail(decode_id($request->id));
     
        $ou_id = $course->ou_id;
        $allGroups = Group::all();
        $courseResources = CourseResources::where('courses_id', decode_id($request->id))->get();
        $resources = Resource::where('ou_id', $ou_id)->get();
        
        return response()->json([
            'course' => $course,
            'allGroups' => $allGroups,
            'courseResources' => $courseResources,
            'resources' => $resources
        ]);
    }



    public function editCourse(Request $request)
    {
        $course = Courses::with('groups')->findOrFail($request->id);
    
        $allGroups = Group::all(); 
    
        return view('your-view-path.edit-course', compact('course', 'allGroups'));
    }

    // Update course
    public function updateCourse(Request $request)
    {

        // dd($request->all());
        // Validate input data
        $request->validate([
            'course_name' => 'required|unique:courses,course_name,' . $request->course_id . ',id,deleted_at,NULL',
            'course_type' => 'required|in:one_event,multi_lesson',
            'description' => 'required',
            'status' => 'required',
            'enable_prerequisites' => 'nullable|boolean',
            'prerequisite_details' => 'nullable|array',
            'prerequisite_type' => 'nullable|array',
            'duration_type' => 'nullable|in:hours,events',
            'duration_value' => 'nullable|numeric|min:1',
            'enable_feedback' => 'nullable|boolean',
            'feedback_questions' => 'nullable|array',
            'feedback_questions.*.question' => 'nullable|string',
            'feedback_questions.*.answer_type' => 'nullable|string|in:yes_no,rating,text',
            'enable_instructor_upload' => 'nullable|boolean',
            'instructor_documents' => 'nullable|array',
            'instructor_documents.*.name' => 'nullable|string|max:255',
            'instructor_documents.*.file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ]
        ], [], [
            'feedback_questions.*.question' => 'Feedback question',
            'feedback_questions.*.answer_type' => 'Answer type',
            'instructor_documents.*.name' => 'Document Name',
            'instructor_documents.*.file' => 'Document File',
        ]);
    
        // Find the course to be updated
        $course = Courses::findOrFail($request->course_id);
    
        // Handle Image Update
        if ($request->hasFile('image')) {
            if ($course->image && Storage::disk('public')->exists($course->image)) {
                Storage::disk('public')->delete($course->image);
            }
            $filePath = $request->file('image')->store('courses', 'public');
        } else {
            $filePath = $course->image;
        }
    
        // Update the course details
        $course->update([
            'ou_id' => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : (auth()->user()->ou_id ?? null),
            'course_name' => $request->course_name,
            'course_type' => $request->course_type,
            'description' => $request->description,
            'image' => $filePath,
            'status' => $request->status,
            'enable_prerequisites' => (int) $request->input('enable_prerequisites', 0),
            'enable_feedback' => (int) $request->input('enable_feedback', 0),
            'enable_instructor_upload' => (int) $request->input('enable_instructor_upload', 0),
            'duration_type' => $request->duration_type,
            'duration_value' => $request->duration_value,
        ]);
    
        // Update groups and resources relationships
        if ($request->has('group_ids')) {
            $course->groups()->sync($request->group_ids);
        }
        if ($request->has('resources')) {
            $course->resources()->sync($request->resources);
        }
    
        // Handle Prerequisites
        $course->prerequisites()->delete();
        if ((int) $request->input('enable_prerequisites', 0) && $request->has('prerequisite_details')) {
            foreach ($request->prerequisite_details as $index => $detail) {
                if (!empty($detail)) {
                    CoursePrerequisite::create([
                        'course_id' => $course->id,
                        'prerequisite_detail' => $detail,
                        'prerequisite_type' => $request->prerequisite_type[$index] ?? 'text',
                    ]);
                }
            }
        } else {
            CoursePrerequisiteDetail::where('course_id', $course->id)
                ->where('created_by', auth()->id())
                ->delete();
        }
    
        // Handle Feedback Questions
        TrainingFeedbackQuestion::where('course_id', $course->id)->delete();
        if ((int) $request->input('enable_feedback', 0) && $request->has('feedback_questions')) {
            foreach ($request->feedback_questions as $feedback) {
                if (!empty($feedback['question'])) {
                    TrainingFeedbackQuestion::create([
                        'course_id' => $course->id,
                        'question' => $feedback['question'],
                        'answer_type' => $feedback['answer_type'] ?? null,
                        'created_by' => auth()->id(),
                    ]);
                }
            }
        }
    
        // Handle Instructor Documents
        if ($request->filled('instructor_documents')) {
            foreach ($request->instructor_documents as $doc) {
                $filePath = null;

                // Check if a new file is uploaded
                if (isset($doc['file']) && $doc['file'] instanceof \Illuminate\Http\UploadedFile) {
                    // If an existing file path is provided, delete the old file
                    if (!empty($doc['existing_file_path']) && Storage::disk('public')->exists($doc['existing_file_path'])) {
                        Storage::disk('public')->delete($doc['existing_file_path']);
                    }

                    // Store the new file with its original name
                    $filePath = $doc['file']->storeAs('course_documents', $doc['file']->getClientOriginalName(), 'public');
                } else {
                    // If no new file is uploaded, retain the existing file path
                    $filePath = $doc['existing_file_path'] ?? null;
                }

                // Create or update the document record
                CourseDocuments::updateOrCreate(
                    ['id' => $doc['row_id'] ?? null], // Use 'id' if available for updating
                    [
                        'course_id' => $course->id,
                        'document_name' => $doc['name'] ?? '',
                        'file_path' => $filePath,
                    ]
                );
            }
        }

    
        // Flash success message and return a JSON response
        Session::flash('message', 'Course updated successfully.');
        return response()->json(['success' => 'Course updated successfully.']);
    }
    
    
    
    


    public function deleteCourse(Request $request)
    {        
        $courses = Courses::findOrFail(decode_id($request->course_id));
        if ($courses) {

            $courses->courseGroups()->delete();

            $courses->delete();
            return redirect()->route('course.index')->with('message', 'This Course deleted successfully');
        }
    }

    // public function showCourse(Request $request,$course_id)
    // {
    //     // $course = Courses::with('courseLessons')->find(decode_id($course_id));
    //     $course = Courses::with('courseLessons')->findOrFail(decode_id($course_id));
    //     return view('courses.show', compact('course'));
    // }

    // public function createLesson(Request $request)
    // {
    //     // dd($request);
    //     $request->validate([            
    //         'lesson_title' => 'required',
    //         'description' => 'required',
    //         'status' => 'required|boolean'
    //     ]);

    //     if ($request->has('comment_required') && $request->comment_required) {
    //         $request->validate([
    //             'comment' => 'required|string',
    //         ]);
    //     }

    //     CourseLesson::create([
    //         'course_id' => $request->course_id,
    //         'lesson_title' => $request->lesson_title,
    //         'description' => $request->description,
    //         'comment' => $request->comment,
    //         'status' => $request->status
    //     ]);


    //     Session::flash('message', 'Lesson created successfully.');
    //     return response()->json(['success' => 'Lesson created successfully.']);
    // }


    // public function getLesson(Request $request)
    // {
    //     $lesson = CourseLesson::findOrFail(decode_id($request->id));
    //     return response()->json(['lesson'=> $lesson]);
    // }

    // public function showLesson(Request $request)
    // {
    //     $lesson = CourseLesson::with('sublessons')->findOrFail(decode_id($request->id));

    //     // dd($lesson);
    //     return view('lesson.show', compact('lesson'));
    // }

    // //Update course
    // public function updateLesson(Request $request)
    // {
    //     $request->validate([
    //         'edit_lesson_title' => 'required',
    //         'edit_description' => 'required',
    //         'edit_status' => 'required'
    //     ]);

    //     if ($request->has('edit_comment_required') && $request->edit_comment_required) {
    //         $request->validate([
    //             'edit_comment' => 'required|string',
    //         ]);
    //     }

    //     $comment = $request->has('edit_comment_required') && !$request->edit_comment_required ? null : $request->edit_comment;
        
    //     // dd($request);
    //     $lesson = CourseLesson::findOrFail($request->lesson_id);
    //     $lesson->update([
    //         'lesson_title' => $request->edit_lesson_title,
    //         'description' => $request->edit_description,
    //         'comment' => $comment,
    //         'status' => $request->edit_status
    //     ]);

    //     Session::flash('message','Lesson updated successfully.');
    //     return response()->json(['success'=> 'Lesson updated successfully.']);
    // }

    // public function deleteLesson(Request $request)
    // {        
    //     $lesson = CourseLesson::findOrFail(decode_id($request->lesson_id));
    //     if ($lesson) {
    //         $course_id = $lesson->course_id;
    //         $lesson->delete();
    //         return redirect()->route('course.show',['course_id' => encode_id($course_id)])->with('message', 'This lesson deleted successfully');
    //     }
    // }


}
