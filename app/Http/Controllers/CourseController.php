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
use App\Models\CourseCustomTime;
use Illuminate\Support\Facades\DB;


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
            // $courses = Courses::all();
            $courses = Courses::orderBy('position')->get();
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
            // $courses = Courses::whereIn('id', function ($query) use ($groupIds) {
            //     $query->select('courses_id')
            //             ->from('courses_group')
            //             ->whereIn('group_id', $groupIds);
            //             })->where('status', 1)
            //                 ->get();
            $courses = Courses::whereIn('id', function ($query) use ($groupIds) {
                $query->select('courses_id')
                        ->from('courses_group')
                        ->whereIn('group_id', $groupIds);
                        })->where('status', 1)->orderBy('position')->get();
                     //  dump($courses);     
        }
        else 
        {
       //  dd("asds");
            if ($role == 1 && empty($ouId)) {
                $courses = Courses::all();
            } else {
                $courses = Courses::where('ou_id', $ouId)->orderBy('position')->get();
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
            'course_name' => 'required',
            'description' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
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

            // Validation for add feedback questions
            'enable_feedback' => 'nullable|boolean',
            'feedback_questions' => 'nullable|array',
            'feedback_questions.*.question' => 'required_if:enable_feedback,1|string',
            'feedback_questions.*.answer_type' => 'required_if:enable_feedback,1|in:yes_no,rating',

            // Validation for add custom time tracking
            'enable_custom_time_tracking' => 'nullable|boolean',
            'custom_time'                 => 'nullable|array',
            'custom_time.*.name'          => 'nullable|string',
            'custom_time.*.hours'         => 'nullable|numeric|min:0',
    
            // Validation for Instructor Upload Documents
            'enable_instructor_upload' => 'nullable|boolean',
            'instructor_documents' => 'nullable|array',
            'instructor_documents.*.name' => 'nullable|string|max:255',

            // Validation for Ground Time Tracking
            'enable_groundschool_time' => 'nullable|boolean',
            'groundschool_hours' => 'nullable|numeric|min:0',

            'enable_simulator_time' => 'nullable|boolean',
            'simulator_hours' => 'nullable|numeric|min:0',

            'enable_custom_time_tracking' => 'nullable|boolean',
            'custom_time_name' => 'nullable|string|max:255',
            'custom_time_hours' => 'nullable|numeric|min:0',
        ], [], [
            'feedback_questions.*.question' => 'Feedback question',
            'feedback_questions.*.answer_type' => 'Answer type',
            'custom_time.*.name' => 'Custom time name',
            'custom_time.*.hours' => 'Custom time hours',
            'instructor_documents.*.name' => 'Document Name',
            'custom_time_name' => 'Custom Time Name',
            'custom_time_hours' => 'Custom Time Hours',
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
            'enable_custom_time_tracking' => (int) $request->input('enable_custom_time_tracking', 0),
            'enable_instructor_upload' => (int) $request->input('enable_instructor_upload', 0),
            'enable_groundschool_time' => (int) $request->input('enable_groundschool_time', 0),
            'groundschool_hours' => $request->groundschool_hours ?? null,
            'enable_simulator_time' => (int) $request->input('enable_simulator_time', 0),
            'simulator_hours' => $request->simulator_hours ?? null,
            'enable_custom_time_tracking' => (int) $request->input('enable_custom_time_tracking', 0),
            'custom_time_name' => $request->custom_time_name ?? null,
            'custom_time_hours' => $request->custom_time_hours ?? null,
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

        // Store course custom time
        if ($request->enable_custom_time_tracking && $request->custom_time) {
            foreach ($request->custom_time as $t) {
                CourseCustomTime::create([
                    'course_id' => $course->id,
                    'name' => $t['name'],
                    'hours' => $t['hours'],
                ]);
            }
        }
    
        // Store Instructor Uploaded Documents
        if ($request->has('enable_instructor_upload')) {
            foreach ($request->instructor_documents as $index => $doc) {
                if (isset($doc['name'])) {
                    $documentPath = null;
    
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
       //dd((decode_id($request->id)));
       $course = Courses::with(['groups', 'prerequisites', 'training_feedback_questions','documents','customTimes'])
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
        $request->validate([
            'course_name' => 'required',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'course_type' => 'required|in:one_event,multi_lesson',
            'description' => 'required',
            'status' => 'required',
            'enable_prerequisites' => 'nullable|boolean',
            'prerequisite_details' => 'nullable|array',
            'prerequisite_type' => 'nullable|array',
            'duration_type' => 'nullable|in:hours,events',
            'duration_value' => 'nullable|numeric|min:1',
            'enable_groundschool_time' => 'nullable|boolean',
            'groundschool_hours' => 'nullable|numeric|min:0',
            'enable_simulator_time' => 'nullable|boolean',
            'simulator_hours' => 'nullable|numeric|min:0',
            'enable_feedback' => 'nullable|boolean',
            'feedback_questions' => 'nullable|array',
            'feedback_questions.*.question' => 'nullable|string',
            'feedback_questions.*.answer_type' => 'nullable|string|in:yes_no,rating,text',
            'enable_instructor_upload' => 'nullable|boolean',
            'instructor_documents' => 'nullable|array',
            'instructor_documents.*.name' => 'nullable|string|max:255',
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
        ]);
    
        //Find the course to be updated
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
            'enable_groundschool_time' => (int) $request->input('enable_groundschool_time', 0),
            'groundschool_hours' => $request->groundschool_hours,
            'enable_simulator_time' => (int) $request->input('enable_simulator_time', 0),
            'simulator_hours' => $request->simulator_hours,
        ]);
    
        // Update groups and resources relationships
        if ($request->has('group_ids')) {
            $course->groups()->sync($request->group_ids);
        }
        if ($request->has('resources')) {
            $course->resources()->sync($request->resources);
        }
    
        // Handle Prerequisites
       // If prerequisites are disabled
        $enable = (int) $request->input('enable_prerequisites', 0);

        if (!$enable) {
            $course->prerequisites()->delete();
            CoursePrerequisiteDetail::where('course_id', $course->id)
                ->where('created_by', auth()->id())
                ->delete();
        } else {
            $existingPrereqs = $course->prerequisites()->get()->keyBy('id');
            $submittedIds = [];

            if ($request->has('prerequisite_details')) {
                foreach ($request->prerequisite_details as $index => $detail) {
                    if (!empty($detail)) {
                        $type = $request->prerequisite_type[$index] ?? 'text';
                        $id = $request->prerequisite_id[$index] ?? null;

                        if ($id && $existingPrereqs->has($id)) {
                            // Update existing
                            $prereq = $existingPrereqs->get($id);
                            $prereq->update([
                                'prerequisite_detail' => $detail,
                                'prerequisite_type' => $type,
                            ]);
                            $submittedIds[] = $id;
                        } else {
                            // Insert new
                            $newPrereq = CoursePrerequisite::create([
                                'course_id' => $course->id,
                                'prerequisite_detail' => $detail,
                                'prerequisite_type' => $type,
                            ]);
                            $submittedIds[] = $newPrereq->id;
                        }
                    }
                }
            }

            // Delete removed ones
            $course->prerequisites()
                ->whereNotIn('id', $submittedIds)
                ->delete();
        }
    
        //Handle Feedback Questions
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

        //Handle Custom time
        CourseCustomTime::where('course_id', $course->id)->delete();
        if ((int) $request->input('enable_custom_time_tracking', 0) && $request->has('custom_time')) {
            foreach ($request->custom_time as $time) {
                if (!empty($time['name'])) {
                    CourseCustomTime::create([
                        'course_id' => $course->id,
                        'name' => $time['name'],
                        'hours' => $time['hours'] ?? null
                    ]);
                }
            }
        }
    
        // Remove existing instructor documents first
        $course->documents()->delete();

        if ($request->filled('instructor_documents')) {
            foreach ($request->instructor_documents as $doc) {
                if (!empty($doc['name'])) {
                    $course->documents()->create([
                        'document_name' => $doc['name'],
                        'file_path' => null, // or provide actual path if uploading
                    ]);
                }
            }
        }


    
        // Flash success message and return a JSON response
        Session::flash('message', 'Course updated successfully.');
        return response()->json(['success' => 'Course updated successfully.']);
    }
    
    public function deleteCourse(Request $request)
    {
        $course = Courses::findOrFail(decode_id($request->course_id));
    
        DB::transaction(function () use ($course) {
            // Delete related course groups
            $course->courseGroups()->delete();
    
            // Delete related course prerequisites
            $course->prerequisites()->delete();
    
            // Delete related course documents
            $course->documents()->delete();
    
            // Finally, delete the course itself
            $course->delete();
        });
    
        return redirect()->route('course.index')->with('message', 'This Course deleted successfully');
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $item) {
            Courses::where('id', $item['id'])->update(['position' => $item['position']]);
        }

        return response()->json(['status' => 'success']);
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
