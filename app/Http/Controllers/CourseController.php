<?php

namespace App\Http\Controllers;

use App\Models\CourseGroup;
use Illuminate\Http\Request;
use App\Models\Courses;
use App\Models\OrganizationUnits;
use App\Models\CoursePrerequisiteDetail;
use App\Models\CoursePrerequisite;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Models\Resource;
use App\Models\CourseResources;


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
        } 
        elseif(checkAllowedModule('courses', 'course.index')->isNotEmpty() && Auth()->user()->is_admin ==  0)
        {
           // dd("else if working");
            $groups = Group::all();

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
       
            if ($role == 1 && empty($ouId)) {
                $courses = Courses::all();
            } else {
                $courses = Courses::where('ou_id', $ouId)->get();
            }
            $groups = Group::where('ou_id', $ouId)->get();
        }
    
        $organizationUnits = OrganizationUnits::all();
        $resource  = Resource::all();
    
        return view('courses.index', compact('courses', 'organizationUnits', 'groups', 'resource'));
    }


    public function create_course()
    {
        return view('Courses.create_course');
    }

    public function createCourse(Request $request)
    {
        // dd($request->all());
        $request->validate([  
            'resources' => 'required',          
            'course_name' => 'required|unique:courses',
            'description' => 'required',
            'image' => 'required',
            'status' => 'required|boolean',
            'group_ids' => 'required',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ]
            ],
            [
            'group_ids.required' => 'Groups are required.'
        ]);

        if ($request->hasFile('image')) {
            $filePath = $request->file('image')->store('courses', 'public');
        }

        $course = Courses::create([
            'ou_id' =>  (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id, // Assign ou_id only if Super Admin provided it
            'course_name' => $request->course_name,
            'description' => $request->description,
            'image' => $filePath ?? null,
            'status' => $request->status
        ]);

        $course->groups()->attach($request->group_ids); 
        $course->resources()->attach($request->resources);

        Session::flash('message', 'Course created successfully.');
        return response()->json(['success' => 'Course created successfully.']);
    }



    public function getCourse(Request $request)
    {
       // dd((decode_id($request->id)));
        $course = Courses::with('groups', 'prerequisites')->findOrFail(decode_id($request->id));
     
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

    //Update course
    public function updateCourse(Request $request)
    {

        $request->validate([
            'resources' => 'required',          
            'course_name' => 'required',
            'description' => 'required',
            'status' => 'required',
            'group_ids' => 'required',
            'enable_prerequisites' => 'nullable|boolean',
            'prerequisite_details' => 'nullable|array',
            'prerequisite_type' => 'nullable|array',
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
            'status' => $request->status,
            'enable_prerequisites' => (int) $request->input('enable_prerequisites', 0),
        ]);

        if ($request->has('group_ids')) {
            $course->groups()->sync($request->group_ids);
        }
        if ($request->has('resources')) {
            $course->resources()->sync($request->resources);
        }

         // Handle Prerequisites
         if ((int) $request->input('enable_prerequisites', 0)) {
            $course->prerequisites()->delete(); // Remove old prerequisites

            if ($request->has('prerequisite_details')) {
                foreach ($request->prerequisite_details as $index => $detail) {
                    if (!empty($detail)) {
                        CoursePrerequisite::create([
                            'course_id' => $course->id,
                            'prerequisite_detail' => $detail,
                            'prerequisite_type' => $request->prerequisite_type[$index] ?? 'text',
                        ]);
                    }
                }
            }
        }else{
            $course->prerequisites()->delete();
            CoursePrerequisiteDetail::where('course_id', $course->id)
            ->where('created_by', auth()->id())
            ->delete();
        }

        Session::flash('message','Course updated successfully.');
        return response()->json(['success'=> 'Course updated successfully.']);
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
