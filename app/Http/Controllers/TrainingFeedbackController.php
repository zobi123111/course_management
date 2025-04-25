<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TrainingEvents;
use App\Models\TrainingFeedback;

class TrainingFeedbackController extends Controller
{
    public function index(Request $request, $eventId)
    {
        $event = TrainingEvents::with('course.training_feedback_questions')->findOrFail(decode_id($eventId));

        // Optional: Ensure student is allowed to give feedback
        if (auth()->user()->id !== $event->student_id) {
            abort(403);
        }

        // Optional: check if already submitted
        $existing = TrainingFeedback::where('training_event_id', $eventId)->where('user_id', auth()->id())->exists();
        if ($existing) {
            return redirect()->back()->with('message', 'You have already submitted feedback.');
        }
// dd($event);
        return view('trainings.feedback-form', compact('event'));
    }

    // Handle submission
    public function submitFeedbackForm(Request $request)
    {
        $event = TrainingEvents::findOrFail($request->event_id);

        $userId = auth()->user()->id;

        // Prevent double submissions
        $exists = TrainingFeedback::where('training_event_id', $event->id)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            return redirect()->back()->with('error', 'Feedback already submitted.');
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'required|string',
        ], [
            'answers.required' => 'This answer field is required.',
            'answers.*.required' => 'This answer field is required.', // override each item error
        ]);
        

        foreach ($request->answers as $questionId => $answer) {
            TrainingFeedback::create([
                'training_event_id' => $event->id,
                'user_id'           => $userId,
                'question_id'       => $questionId,
                'answer'            => $answer,
            ]);
        }

        return redirect()->route('training.feedback.form', ['event_id' => encode_id($event->id)])
            ->with('success', 'Feedback submitted successfully!');
    }


}
