<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use App\Models\OrganizationUnits;
use App\Models\QuizQuestion;
use App\Models\QuizTopic;
use App\Models\Topic;
use App\Models\TopicQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TopicController extends Controller
{
    // public function index()
    // {

    //     $currentUser = auth()->user();
    //     $organizationUnits = OrganizationUnits::all();

    //     if ($currentUser->is_owner == 1 && empty($currentUser->ou_id)) {
    //         $topics = Topic::with('organizationUnit')->orderBy('id', 'desc')->get();
    //         $courses = Courses::where("status", 1)->get();
    //     }
    //     elseif ($currentUser->is_admin == 1 && !empty($currentUser->ou_id)) {
    //         $topics = Topic::with('organizationUnit')->orderBy('id', 'desc')->where('ou_id', $currentUser->ou_id)->get();
    //         $courses = Courses::where("status", 1)->get();
    //     }

        
    //     return view('topic.index', compact('topics', 'organizationUnits', 'courses'));
    // }

    public function index()
    {
        $currentUser = auth()->user();
        $organizationUnits = OrganizationUnits::all();

        if ($currentUser->is_owner == 1 && empty($currentUser->ou_id)) {
            $coursestopics = Courses::where('status', 1)->whereIn('id', function ($query) {
                    $query->select('course_id')->from('topics');
                })->orderByDesc(
                    Topic::select('id')->whereColumn('topics.course_id', 'courses.id')->latest('id')->limit(1)
                )->get();
                $courses = Courses::where("status", 1)->get();
        }
        elseif ($currentUser->is_admin == 1 && !empty($currentUser->ou_id)) {
            $coursestopics = Courses::where('status', 1)->where('ou_id', $currentUser->ou_id)->whereIn('id', function ($query) {
                    $query->select('course_id')->from('topics');
                })->orderByDesc(
                    Topic::select('id')->whereColumn('topics.course_id', 'courses.id')->latest('id')->limit(1)
                )->get();
                $courses = Courses::where("status", 1)->where('ou_id', $currentUser->ou_id)->get();
        }

        return view('topic.index', compact('coursestopics', 'courses', 'organizationUnits'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $topic = new Topic();
        $topic->title = $request->title;
        $topic->description = $request->description;
        $topic->ou_id = (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id;
        $topic->course_id = $request->course_id;
        $topic->save();

        return response()->json(['success' => true, 'message' => 'Topic created successfully.']);
    }

    public function view(Request $request)
    {
        $currentUser = auth()->user();
        $organizationUnits = OrganizationUnits::all();
        $courseId = decode_id($request->id);

        $course = Courses::findOrFail($courseId);

        if ($currentUser->is_owner == 1 && empty($currentUser->ou_id)) {
            $topics = Topic::with('organizationUnit')->orderBy('id', 'desc')->where('course_id', $courseId)->get();
            $courses = Courses::where("status", 1)->get();
        }
        elseif ($currentUser->is_admin == 1 && !empty($currentUser->ou_id)) {
            $topics = Topic::with('organizationUnit')->orderBy('id', 'desc')
                            ->where('ou_id', $currentUser->ou_id)
                            ->where('course_id', $courseId)
                            ->get();

            $courses = Courses::where("status", 1)->get();
        }

        $breadcrumbs = [
            ['title' => 'Courses Topics', 'url' => route('topic.index')],
            ['title' => $course->course_name, 'url' => ''],
        ];
        
        return view('topic.view', compact('topics', 'organizationUnits', 'courses', 'breadcrumbs'));
    }    

    public function topicView(Request $request)
    {
        $topicId = decode_id($request->id);
        $topic = Topic::findOrFail($topicId);
        $topicQuestions = TopicQuestion::where('topic_id', $topicId)->get();

        $breadcrumbs = [
            ['title' => 'Courses Topics', 'url' => route('topic.index')],
            ['title' => $topic->course->course_name, 'url' => route('topic.view', encode_id($topic->course->id))],
            ['title' => $topic->title, 'url' => ''],
        ];

        return view('topic.topic_view', compact('topic', 'topicQuestions', 'breadcrumbs'));
    }

    public function edit(Request $request)
    {
        $topicId = decode_id($request->id);
        $topic = Topic::findOrFail($topicId);

        $topicassigned = QuizTopic::where("topic_id", $topicId)->get();

        return response()->json(['topic' => $topic, 'topicassigned' => $topicassigned]);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $topic = Topic::findOrFail($request->topic_id);
        $topic->update([
            'title' => $request->title,
            'description' => $request->description,
            'ou_id' => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id,
            'course_id' => $request->course_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Topic updated successfully.']);
    }

    public function destroy(Request $request)
    {
        $topicId = decode_id($request->topic_id);
        $topic = Topic::findOrFail($topicId);
        $topic->delete();

        return redirect()->route('topic.index')->with('message', 'Topic deleted successfully');
    }
}
