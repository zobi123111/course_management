<?php

namespace App\Http\Controllers;

use App\Models\OrganizationUnits;
use App\Models\QuizQuestion;
use App\Models\Topic;
use App\Models\TopicQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class TopicController extends Controller
{
    public function index()
    {

        $topics = Topic::get();
        $organizationUnits = OrganizationUnits::all();
        return view('topic.index', compact('topics', 'organizationUnits'));
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
        $topic->save();

        return response()->json(['success' => true, 'message' => 'Topic created successfully.']);
    }

    public function view(Request $request)
    {
        $topicId = decode_id($request->id);
        $topic = Topic::findOrFail($topicId);
        $topicQuestions = TopicQuestion::where('topic_id', $topicId)->get();

        return view('topic.view', compact('topic', 'topicQuestions'));
    }

    public function edit(Request $request)
    {
        $topicId = decode_id($request->id);
        $topic = Topic::findOrFail($topicId);

        return response()->json(['topic' => $topic]);
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
