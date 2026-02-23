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
use App\Models\CourseLesson;
use App\Models\SubLesson;
use App\Models\LessonPrerequisite;
use App\Models\Rating;
use App\Models\RhsTag;
use App\Models\UserTagRating;
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

            $ratings = Rating::with(['ou_ratings.organization_unit'])->where('status', 1)->get();
        } elseif (checkAllowedModule('courses', 'course.index')->isNotEmpty() && Auth()->user()->is_admin ==  0) {

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
            })->where('status', 1)->orderBy('position')->get();
            //  dump($courses);    

            $ratings = Rating::with(['ou_ratings.organization_unit'])->where('status', 1)->get();
        } else {
            if ($role == 1 && empty($ouId)) {
                $courses = Courses::all();
            } else {
                $courses = Courses::where('ou_id', $ouId)->orderBy('position')->get();
            }
            $groups = Group::where('ou_id', $ouId)->get();
            $resource  = Resource::where('ou_id', $ouId)->get();

            $ratings = Rating::where('status', 1)
                ->whereHas('ou_ratings', function ($query) use ($ouId) {
                    $query->where('ou_id', $ouId);
                })
                ->with(['ou_ratings' => function ($query) use ($ouId) {
                    $query->where('ou_id', $ouId);
                }])
                ->get();
        }

        $ou_id = auth()->user()->ou_id;
        if (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) {
            $organizationUnits = OrganizationUnits::all();
        } else {
            $organizationUnits = OrganizationUnits::where('id', $ou_id)->get();
        }

        if (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) {
            $tags = RhsTag::all();
        } else {
            $tags = RhsTag::where('ou_id', $ou_id)->get();
        }

        return view('courses.index', compact('courses', 'organizationUnits', 'groups', 'resource', 'ou_id', 'ratings', 'tags'));
    }

    public function getTagsByOu(Request $request)
    {
        $ouId = $request->ou_id;
        
        if (!$ouId) {
            return response()->json(['tags' => []]);
        }

        // Fetch tags for the specified OU
        $tags = RhsTag::where('ou_id', $ouId)->get();
        
        return response()->json(['tags' => $tags]);
    }


    public function create_course()
    {
        return view('Courses.create_course');
    }

    public function createCourse(Request $request)
    {
      //dd($request->enable_aircraft);
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
        //    'opc_validity_months' => [
        //             'sometimes',
        //             'required_if:enable_opc,!=,',
        //             'integer',
        //             'min:1',
        //         ],

        //     'opc_extend_eom' => [
        //             'sometimes',
        //             'required_if:enable_opc,!=,',
        //             'in:0,1',
        //         ],

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
            'enable_cbta' => $request->enable_cbta ?? 0,
            'enable_mp_lifus' => $request->enable_mp_lifus ?? 0,
            'instructor_cbta' => $request->instructor_cbta ?? 0,
            'examiner_cbta' => $request->examiner_cbta ?? 0,
            'opc' => $request->has('enable_opc') ? 1 : 0,
            'opc_aircraft' => $request->enable_aircraft ?? null,
            'opc_validity' => $request->opc_validity_months ?? null,
            'opc_extend' => $request->opc_extend_eom ?? null,
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

        //-------------------------------------------------------------------------------------------------------------------
        // Handle RHS tag
        if ($request->has('master_tag_select')) {
            foreach ($request->master_tag_select as $key => $tagId) {
                // check tag is not null
                if (!empty($tagId)) {
                    $validity = $request->master_validity[$key] ?? null;
                    if (!empty($validity)) {
                        UserTagRating::create([
                           // 'user_id'         => null,
                           // 'event_id'        => null,
                            'course_id'       => $course->id,
                            'tag_id'          => $tagId,
                            'tag_validity'    => $validity,
                            'tag_type'        => 'master',
                           // 'tag_expiry_date' => null,
                        ]);
                    }
                }
            }
        }

        // 2️⃣ MANUAL TAGS

        if ($request->has('manual_tag_select')) {
            foreach ($request->manual_tag_select as $key => $tagName) {
                if (empty($tagName)) {
                    continue;
                }
                $validity = $request->manual_validity[$key] ?? null;
                if (empty($validity)) {
                    continue;
                }
                // Normalize input
                $normalizedInput = strtolower(preg_replace('/\s+/', '', $tagName));
                $rhsTag = RhsTag::withTrashed()
                    ->get()
                    ->first(function ($tag) use ($normalizedInput) {
                        return strtolower(preg_replace('/\s+/', '', $tag->rhstag)) === $normalizedInput;
                    });

                if (!$rhsTag) {
                    $rhsTag = RhsTag::create([
                     'ou_id' => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id,
                     'rhstag' => trim($tagName)
                    ]);
                } else {
                    if ($rhsTag->trashed()) {
                        $rhsTag->restore();
                    }
                }
                $tagId = $rhsTag->id;
                UserTagRating::create([
                   // 'user_id'         => null,
                   // 'event_id'        => null,
                    'course_id'       => $course->id,
                    'tag_id'          => $tagId,
                    'tag_validity'    => $validity,
                    'tag_type'        => 'manual',
                   // 'tag_expiry_date' => null,
                ]);
            }
        }
        //-------------------------------------------------------------------------------------------------------------------
        Session::flash('message', 'Course created successfully.');
        return response()->json(['success' => 'Course created successfully.']);
    }

    public function getCourse(Request $request)
    {
        //dd((decode_id($request->id)));
        $course = Courses::with(['groups', 'prerequisites', 'training_feedback_questions', 'documents', 'customTimes', 'userTagRatings.rhsTag'])->findOrFail(decode_id($request->id));
        $ou_id = $course->ou_id;
        $allGroups = Group::all();
        $courseResources = CourseResources::where('courses_id', decode_id($request->id))->get();
        $resources = Resource::where('ou_id', $ou_id)->get();

        $ato_num = OrganizationUnits::where('id', $ou_id)->get();

        return response()->json([
            'course' => $course,
            'allGroups' => $allGroups,
            'courseResources' => $courseResources, 
            'resources' => $resources,
            'ato_num' => $ato_num
        ]);
    }

    public function getRatingsByOu(Request $request)
    {

        if (!$request->ajax()) {
            return response()->json([], 400);
        }

        $ouId = $request->ou_id;

        if (!$ouId) {
            return response()->json([]);
        }

        $ratings = Rating::where('status', 1)
            ->whereHas('ou_ratings', function ($q) use ($ouId) {
                $q->where('ou_id', $ouId);
            })
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return response()->json($ratings);
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
            ],
            // 'opc_validity_months' => [
            //         'sometimes',
            //         'required_if:enable_opc,!=,',
            //         'integer',
            //         'min:1',
            //     ],

            // 'opc_extend_eom' => [
            //         'sometimes',
            //         'required_if:enable_opc,!=,',
            //         'in:0,1',
            //     ],
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
            'ato_num' => $request->ato_number ?? null,
            'enable_cbta' => $request->edit_enable_cbta ?? 0,
            'enable_mp_lifus' => $request->enable_mp_lifus ?? 0,
            'instructor_cbta' => $request->edit_instructor_cbta ?? 0,
            'opc' => $request->has('enable_opc') ? 1 : 0,
            'opc_aircraft' => $request->enable_aircraft ?? null,
            'opc_validity' => $request->opc_validity_months ?? null,
            'opc_extend' => $request->opc_extend_eom ?? null,
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

        $existingDocs = $course->documents()->pluck('document_name', 'id')->toArray();


        $submittedDocIds = [];
        $submittedDocNames = [];

        if ($request->filled('instructor_documents')) {
            foreach ($request->instructor_documents as $doc) {
                $docId = $doc['id'] ?? null;   // hidden input should send id if editing
                $docName = $doc['name'] ?? null;

                if (!empty($docName)) {
                    if ($docId && isset($existingDocs[$docId])) {
                        // ✅ Update existing
                        $course->documents()
                            ->where('id', $docId)
                            ->update(['document_name' => $docName]);

                        $submittedDocIds[] = $docId;
                    } else {
                        // ✅ Create new
                        $newDoc = $course->documents()->create([
                            'document_name' => $docName,
                            'file_path' => null, // handle file upload if needed
                        ]);

                        $submittedDocIds[] = $newDoc->id;
                    }

                    $submittedDocNames[] = $docName;
                }
            }
        }
        $course->documents()
            ->whereNotIn('id', $submittedDocIds)
            ->delete();
        //--------------------------------------------------------------------------------
        // Handle rhs tag
        UserTagRating::where('course_id', $request->course_id)
            //  ->where('tag_id', $tagId)
            ->where('tag_type', 'master')
            ->delete();
        if ($request->has('master_tag_select')) {
            foreach ($request->master_tag_select as $key => $tagId) {

                // check tag is not null
                if (!empty($tagId)) {

                    $validity = $request->master_validity[$key] ?? null;

                    if (!empty($validity)) {

                        UserTagRating::create([
                           // 'user_id'         => null,
                           // 'event_id'        => null,
                            'course_id'       => $request->course_id,
                            'tag_id'          => $tagId,
                            'tag_validity'    => $validity,
                            'tag_type'        => 'master',
                           // 'tag_expiry_date' => null,
                        ]);
                    }
                }
            }
        }

        // 2️⃣ MANUAL TAGS
        UserTagRating::where('course_id', $request->course_id)
            // ->where('tag_id', $tagId)
            ->where('tag_type', 'manual')
            ->delete();
        if ($request->has('manual_tag_select')) {
            foreach ($request->manual_tag_select as $key => $tagName) {

                if (empty($tagName)) {
                    continue;
                }

                $validity = $request->manual_validity[$key] ?? null;
                if (empty($validity)) {
                    continue;
                }

                // Normalize input
                $normalizedInput = strtolower(preg_replace('/\s+/', '', $tagName));

                /**
                 * 1️⃣ Match ignoring spaces & case
                 */
                $rhsTag = RhsTag::withTrashed()
                    ->get()
                    ->first(function ($tag) use ($normalizedInput) {
                        return strtolower(preg_replace('/\s+/', '', $tag->rhstag)) === $normalizedInput;
                    });

                /**
                 * 2️⃣ Create only if no match found
                 */
                if (!$rhsTag) {
                    $rhsTag = RhsTag::create([
                    'ou_id' => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : (auth()->user()->ou_id ?? null),
                    'rhstag' => trim($tagName)
                    ]);
                } else {
                    if ($rhsTag->trashed()) {
                        $rhsTag->restore();
                    }
                }

                $tagId = $rhsTag->id;

                /**
                 * 3️⃣ Delete old + insert fresh
                 */
                // UserTagRating::where('course_id', $request->course_id)
                //     ->where('tag_id', $tagId)
                //     ->where('tag_type', 'manual')
                //     ->delete();

                UserTagRating::create([
                  //  'user_id'         => null,
                  //  'event_id'        => null,
                    'course_id'       => $request->course_id,
                    'tag_id'          => $tagId,
                    'tag_validity'    => $validity,
                    'tag_type'        => 'manual',
                  //  'tag_expiry_date' => null,
                ]);
            }
        }
        //--------------------------------------------------------------------------------
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




    public function copy_lesson(Request $request)
    {
        try {
            // Validate incoming request
            $request->validate([
                'course_id' => 'required',
                'lesson_id' => 'required',
            ]);

            $course_id = decode_id($request->course_id);
            $lesson_id = decode_id($request->lesson_id);

            // Validate decoded IDs
            if (!$course_id || !$lesson_id) {
                return response()->json(['error' => 'Invalid course_id or lesson_id'], 400);
            }

            // Fetch original lesson
            $lesson_info = CourseLesson::where('course_id', $course_id)
                ->where('id', $lesson_id)
                ->first();

            if (!$lesson_info) {
                return response()->json(['error' => 'Lesson not found'], 404);
            }

            // Count duplicates
            // $baseTitle = $lesson_info->lesson_title;
            $originalTitle = $lesson_info->lesson_title;

            // CHECK IF THE LESSON IS ALREADY A DUPLICATE
            $originalTitle = $lesson_info->lesson_title;

            // CASE A: The title already has a duplicate at the end → Nested duplication
            if (preg_match('/Duplicate \d+$/', $originalTitle)) {

                // This duplicate becomes the base for nested copies
                $duplicateBase = $originalTitle;

                $duplicateCount = CourseLesson::where('course_id', $course_id)
                    ->where('lesson_title', 'LIKE', $duplicateBase . ' - Duplicate %')
                    ->count();

                $newTitle = $duplicateBase . ' - Duplicate ' . ($duplicateCount + 1);
            } else {

                // CASE B: Original title → Only count direct duplicates
                $baseTitle = $originalTitle;

                // Match ONLY: "Profile 3 - Duplicate X"
                // NOT: "Profile 3 - Duplicate 1 - Duplicate 1"
                $duplicateCount = CourseLesson::where('course_id', $course_id)
                    ->where('lesson_title', 'REGEXP', '^' . preg_quote($baseTitle) . ' - Duplicate [0-9]+$')
                    ->count();

                $newTitle = $baseTitle . ' - Duplicate ' . ($duplicateCount + 1);
            }



            // Prepare lesson payload
            $lesson = [
                'course_id' => $lesson_info->course_id,
                'lesson_title' => $newTitle,
                'description' => $lesson_info->description,
                'comment' => $lesson_info->comment,
                'status' => $lesson_info->status,
                'grade_type' => $lesson_info->grade_type,
                'lesson_type' => $lesson_info->lesson_type,
                'enable_cbta' => $lesson_info->enable_cbta ?? 0,
                'instructor_cbta' => $lesson_info->instructor_cbta ?? 0,
                'examiner_cbta' => $lesson_info->examiner_cbta ?? 0,
                'custom_time_id' => $lesson_info->custom_time_type,
                'enable_prerequisites' => $lesson_info->enable_prerequisites,
            ];

            // 🔥 Start Transaction (MOST IMPORTANT)
            DB::beginTransaction();

            // Create duplicated lesson
            $create_lesson = CourseLesson::create($lesson);

            if (!$create_lesson) {
                DB::rollBack();
                return response()->json(['error' => 'Unable to duplicate lesson.'], 500);
            }

            // Lesson Prerequisite
            $lesson_pre = LessonPrerequisite::where('lesson_id', $lesson_id)->where('course_id', $course_id)->get();
            if ($lesson_pre->isNotEmpty()) {
                foreach ($lesson_pre as $row) {
                    $prequites = array(
                        "course_id"           => $row->course_id,
                        "lesson_id"           => $create_lesson->id,
                        "prerequisite_detail" => $row->prerequisite_detail,
                        "prerequisite_type"   => $row->prerequisite_type,
                    );
                    LessonPrerequisite::create($prequites);
                }
            }



            // Duplicate sub-lessons
            $sublesson_info = SubLesson::where('lesson_id', $lesson_id)->get();


            foreach ($sublesson_info as $val) {
                $subLesson = [
                    'lesson_id'    => $create_lesson->id,
                    'title'        => $val->title,
                    'description'  => $val->description,
                    'grade_type'   => $val->grade_type,
                    'status'       => $val->status,
                    'is_mandatory' => $val->is_mandatory,
                ];

                $created = SubLesson::create($subLesson);

                if (!$created) {
                    DB::rollBack();
                    return response()->json(['error' => 'Unable to duplicate sub lessons'], 500);
                }
            }
            // 🔥 Commit only if everything succeeds
            DB::commit();
            Session::flash('message', 'Lesson created successfully.');
            return response()->json([
                'success' => true,
                'message' => 'Lesson duplicated successfully.'
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'error' => 'Something went wrong.',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function copy_course(Request $request)
    {

        // ----------------------------------Start Copy Course ----------------------------------------------
        // ------------ VALIDATION ------------
        $request->validate([
            'course_id' => 'required|string',
        ]);

        // ------------ DECODE COURSE ID ------------
        $course_id = decode_id($request->course_id);


        if (!$course_id || !is_numeric($course_id)) {
            return response()->json(['status' => false, 'message' => 'Invalid Course ID'], 400);
        }

        // ------------ FETCH MAIN COURSE ------------
        $course_info = Courses::find($course_id);


        if (!$course_info) {
            return response()->json(['status' => false, 'message' => 'Course not found'], 404);
        }



        $baseTitle = $course_info->course_name;

        if (preg_match('/Duplicate \d+$/', $baseTitle)) {
            $duplicateBase = $baseTitle;
            $duplicateCount = Courses::where('course_name', 'LIKE', $duplicateBase . ' - Duplicate %')
                ->where('course_name', 'REGEXP', '^' . preg_quote($duplicateBase) . ' - Duplicate [0-9]+$')
                ->count();

            $newTitle = $duplicateBase . ' - Duplicate ' . ($duplicateCount + 1);
        } else {
            $duplicateCount = Courses::where(
                'course_name',
                'REGEXP',
                '^' . preg_quote($baseTitle) . ' - Duplicate [0-9]+$'
            )
                ->count();

            $newTitle = $baseTitle . ' - Duplicate ' . ($duplicateCount + 1);
        }


        // ------------ PREPARE COURSE PAYLOAD ------------
        $course = [
            'ou_id'                      => $request->ou_id,
            'course_name'                => $newTitle,
            'description'                => $course_info->description,
            'image'                      => $course_info->image,
            'status'                     => $course_info->status,
            'duration_type'              => $course_info->duration_type,
            'duration_value'             => $course_info->duration_value,
            'course_type'                => $course_info->course_type,
            'enable_feedback'            => $course_info->enable_feedback,
            'enable_custom_time_tracking' => $course_info->enable_custom_time_tracking,
            'enable_instructor_upload'   => $course_info->enable_instructor_upload,
            'enable_groundschool_time'   => $course_info->enable_groundschool_time,
            'groundschool_hours'         => $course_info->groundschool_hours,
            'enable_simulator_time'      => $course_info->enable_simulator_time,
            'simulator_hours'            => $course_info->simulator_hours,
            'custom_time_name'           => $course_info->custom_time_name,
            'custom_time_hours'          => $course_info->custom_time_hours,
            'enable_cbta'                => $course_info->enable_cbta,
            'enable_mp_lifus'            => $course_info->enable_mp_lifus,
            'enable_prerequisites'       => $course_info->enable_prerequisites,
            'opc'                        => $course_info->opc,
            'opc_aircraft'               => $course_info->opc_aircraft,
            'opc_validity'               => $course_info->opc_validity,
            'opc_extend'                 => $course_info->opc_extend ,
        ];
       // dd($course);

        // CREATE NEW COURSE
        $create_course = Courses::create($course);


        // ------------ COPY GROUPS ------------
        $group_info = CourseGroup::where('courses_id', $course_id)->get();

        if ($group_info->isNotEmpty()) {
            foreach ($group_info as $row) {
                CourseGroup::create([
                    "courses_id" => $create_course->id,
                    "group_id"   => $row->group_id
                ]);
            }
        }
        // ------------ Course Prerequisite Detail ------------

        $CoursePrerequisite =  CoursePrerequisite::where('course_id', $course_id)->get();
        if ($CoursePrerequisite->isNotEmpty()) {
            foreach ($CoursePrerequisite as $row) {
                CoursePrerequisite::create([
                    'course_id'           => $create_course->id,
                    'prerequisite_detail' => $row->prerequisite_detail,
                    'prerequisite_type'    => $row->prerequisite_type,
                ]);
            }
        }



        // ------------ COPY RESOURCES ------------
        $resource_info = CourseResources::where('courses_id', $course_id)->get();

        if ($resource_info->isNotEmpty()) {
            foreach ($resource_info as $val) {
                CourseResources::create([
                    "courses_id"   => $create_course->id,
                    "resources_id" => $val->resources_id
                ]);
            }
        }

        // ------------ COPY FEEDBACK QUESTIONS ------------
        $questions = TrainingFeedbackQuestion::where('course_id', $course_id)->get();

        if ($questions->isNotEmpty()) {
            foreach ($questions as $val) {
                TrainingFeedbackQuestion::create([
                    "course_id"   => $create_course->id,
                    "question"    => $val->question,
                    "answer_type" => $val->answer_type
                ]);
            }
        }

        // ------------ COPY CUSTOM TIME ------------
        $custom_time = CourseCustomTime::where('course_id', $course_id)->get();
        if ($custom_time->isNotEmpty()) {
            foreach ($custom_time as $val) {
                CourseCustomTime::create([
                    "course_id" => $create_course->id,
                    "name"      => $val->name ?? '',
                    "hours"     => is_numeric($val->hours) ? $val->hours : 0,
                ]);
            }
        }

        // ------------ COPY COURSE DOCUMENTS ------------
        $course_document = CourseDocuments::where('course_id', $course_id)->get();
        if ($course_document->isNotEmpty()) {
            foreach ($course_document as $val) {
                CourseDocuments::create([
                    "course_id"      => $create_course->id,
                    "document_name"  => $val->document_name,
                    "file_path"      => $val->file_path
                ]);
            }
        }

        // ------------------------------------- Copy Course End---------------------------------------------------------

        // ------------------------------------- Start Copy Lesson Start---------------------------------------------------------
        $lesson_info = CourseLesson::where('course_id', $course_id)->get();

        if ($lesson_info->isNotEmpty()) {
            foreach ($lesson_info as $lessonRow) {
                $lesson = [
                    'course_id'       => $create_course->id,
                    'lesson_title'    => $lessonRow->lesson_title,
                    'description'     => $lessonRow->description,
                    'comment'         => $lessonRow->comment,
                    'status'          => $lessonRow->status,
                    'grade_type'      => $lessonRow->grade_type,
                    'lesson_type'     => $lessonRow->lesson_type,
                    'enable_cbta'     => $lessonRow->enable_cbta ?? 0,
                    'instructor_cbta' => $lessonRow->instructor_cbta ?? 0,
                    'examiner_cbta'   => $lessonRow->examiner_cbta ?? 0,
                    'custom_time_id'  => $lessonRow->custom_time_type,
                ];



                // Create lesson copy
                $create_lesson = CourseLesson::create($lesson);

                // Fetch sub-lessons of the ORIGINAL lesson
                $sublesson_info = SubLesson::where('lesson_id', $lessonRow->id)->get();

                if ($sublesson_info->isNotEmpty()) {

                    foreach ($sublesson_info as $sub) {

                        $subLesson = [
                            'lesson_id'    => $create_lesson->id,
                            'title'        => $sub->title,
                            'description'  => $sub->description,
                            'grade_type'   => $sub->grade_type,
                            'status'       => $sub->status,
                            'is_mandatory' => $sub->is_mandatory,
                        ];

                        SubLesson::create($subLesson);
                    }
                }
            }
        }

        //------------------------------------------------UserTagRating---------------------------------------------------------
            $tags =  UserTagRating::where('course_id', $course_id)->get();

            if ($tags->isNotEmpty()) {
                foreach ($tags as $tag) {
                        $all_tags = [
                            'course_id'       => $create_course->id,
                            'tag_id'          => $tag->tag_id ,
                            'tag_validity'    =>  $tag->tag_validity ,
                            'tag_type'        =>  $tag->tag_type,
                    ];
                    UserTagRating::create($all_tags);

                }

            }


        // ------------------------------------- End Copy Lesson Start-----------------------------------------------------------


        Session::flash('message', 'Course created successfully.');
        return response()->json([
            'status' => true,
            'message' => 'Course copied successfully',
        ]);
    }
}
