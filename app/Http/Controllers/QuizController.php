<?php

namespace App\Http\Controllers;

use App\Models\CourseLesson;
use App\Models\Courses;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Models\Topic;
use App\Models\TopicQuestion;
use App\Models\TrainingEvents;
use App\Models\User;
use App\Models\QuizTopic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuizController extends Controller
{
    
    public function quizzes()
    {
        $currentUser = auth()->user();

        if ($currentUser->is_owner == 1 && empty($currentUser->ou_id)) {
            $quizs = Quiz::with('course', 'lesson')->get();
        }
        else{
            $courseIds = TrainingEvents::where('student_id', $currentUser->id)
            ->pluck('course_id');

            $quizs = Quiz::with('course', 'lesson', 'quizAttempts')->where('status', 'published')
            ->whereIn('course_id', $courseIds)->get();  
        }

        // dd($quizs);
        $courses = Courses::where("status", 1)->get();

        return view('quiz.index', compact('quizs', 'courses'));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'title'         => 'required|string|max:255',
            'course_id'     => 'required|numeric',
            'lesson_id'     => 'required|numeric',
            'duration'      => 'required|numeric|min:1',
            'passing_score' => 'required|numeric|min:0|max:100',
            'status'        => 'required|string|in:draft,published',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $quiz = new Quiz();
        $quiz->title = $request->title;
        $quiz->course_id = $request->course_id;
        $quiz->lesson_id = $request->lesson_id;
        $quiz->duration = $request->duration;
        $quiz->passing_score = $request->passing_score;
        $quiz->quiz_type = $request->quiz_type;
        $quiz->status = $request->status;
        $quiz->created_by = Auth::id();
        $quiz->save();

        return response()->json(['success' => true, 'message' => 'Quiz created successfully.']);
    }

    public function view(Request $request)
    {
        $quizId = decode_id($request->id);
        $quiz = Quiz::with('topics.topic')->findOrFail($quizId);
        $topics = Topic::get();
        $quizQuestions = QuizQuestion::with('question')->where('quiz_id', $quizId)->get();
        
        return view('quiz.view', compact('quiz', 'topics', 'quizQuestions'));
    }

    public function edit(Request $request)
    {
        $quizId = decode_id($request->id);
        $quiz = Quiz::findOrFail($quizId);

        return response()->json(['quiz' => $quiz]);
    }

    public function getLessonsByCourse(Request $request)
    {
        // dd($request->all());

        $lessons = CourseLesson::where('course_id', $request->course_id)->get();
        return response()->json($lessons);
    }

    public function update(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'quiz_id'       => 'required|numeric',
            'title'         => 'required|string|max:255',
            'course_id'     => 'required|numeric',
            'lesson_id'     => 'required|numeric',
            'duration'      => 'required|numeric|min:1',
            'passing_score' => 'required|numeric|min:0|max:100',
            'status'        => 'required|string|in:draft,published',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $quiz = Quiz::findOrFail($request->quiz_id);
        $quiz->update([
            'title' => $request->title,
            'course_id' => $request->course_id,
            'lesson_id' => $request->lesson_id,
            'duration' => $request->duration,
            'passing_score' => $request->passing_score,
            'quiz_type' => $request->quiz_type,
            'status' => $request->status,
        ]);

        return response()->json(['success' => true, 'message' => 'Quiz updated successfully.']);
    }

    public function destroy(Request $request)
    {
        $quizId = decode_id($request->quiz_id);
        $quiz = Quiz::findOrFail($quizId);
        $quiz->delete();

        // return response()->json(['success' => true, 'message' => 'Quiz deleted successfully!']);
        return redirect()->route('quiz.index')->with('message', 'Quiz deleted successfully');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'topic_id'  => 'required|integer|exists:topics,id',
            'csv_file' => 'required|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $path = $file->getRealPath();

        $data = array_map('str_getcsv', file($path));

        $header = array_map('trim', $data[0]);
        unset($data[0]);

        foreach ($data as $row) {
            $row = array_combine($header, $row);

            $option_A = isset($row['option_A']) ? trim($row['option_A']) : null;
            $option_B = isset($row['option_B']) ? trim($row['option_B']) : null;
            $option_C = isset($row['option_C']) ? trim($row['option_C']) : null;
            $option_D = isset($row['option_D']) ? trim($row['option_D']) : null;

            TopicQuestion::create([
                'topic_id'        => $request->topic_id,
                'question_text'  => $row['question'] ?? null,
                'question_type'  => $row['type'] ?? null,
                'option_A'      => $option_A,
                'option_B'      => $option_B,
                'option_C'      => $option_C,
                'option_D'      => $option_D,
                'correct_option' => $row['correct_answers'] ?? null,
            ]);
        }

        return back()->with('message', 'Questions imported successfully!');
    }

    public function exportCsv()
    {
        $filePath = resource_path('views/quiz/quiz_questions_blank.csv');

        if (!file_exists($filePath)) {
            return back()->with('message', 'CSV file not found!');
        }

        return response()->download($filePath, 'quiz_questions_blank.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function addTopic(Request $request, $quizId)
    {
        $request->validate([
            'topic_id' => 'required|exists:topics,id',
            'question_quantity' => 'required|integer|min:1'
        ]);

        QuizTopic::create([
            'quiz_id' => $quizId,
            'topic_id' => $request->topic_id,
            'question_quantity' => $request->question_quantity,
        ]);

        $topicQuestions = TopicQuestion::where('topic_id', $request->topic_id)->pluck('id')->toArray();

        if (count($topicQuestions) == 0) {
            return back()->with('error', 'No questions found for this topic.');
        }

        $selectedQuestions = collect($topicQuestions)->shuffle()->take($request->question_quantity);

        foreach ($selectedQuestions as $questionId) {
            QuizQuestion::create([
                'quiz_id' => $quizId,
                'topic_id' => $request->topic_id,
                'question_id' => $questionId,
            ]);
        }

        return back()->with('message', 'Topic added & questions assigned successfully!');
    }

    public function deleteTopic(Request $request)
    {
        $id = decode_id($request->topic_id);
        $quiz_id = decode_id($request->quiz_id);

        $quizTopic = QuizTopic::findOrFail($id);

        $quizQuestions = QuizQuestion::where('topic_id', $id)
        ->where('quiz_id', $quiz_id)
        ->get();

        $quizQuestions->each(function($question) {
            $question->delete();
        });

        $quizTopic->delete();

       return redirect()->back()->with('message', 'Topic and questions Unassigned successfully.');
    }

    public function updateStatus(Request $request)
    {
        $quiz = Quiz::findOrFail($request->id);
        $quiz->status = $request->status ? 'published' : 'draft';
        $quiz->save();

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully!',
            'status' => $quiz->status
        ]);
    }

    public function startQuiz(Request $request)
    {
        $currentUser = auth()->user();
        $quiz_id = decode_id($request->id);

        $checkAttempt = QuizAttempt::where('quiz_id', $quiz_id)->where('student_id', $currentUser->id)->first();

        if(!$checkAttempt){
            $attempt = QuizAttempt::create([
                    'quiz_id'        => $quiz_id,
                    'student_id'  => $currentUser->id,
                ]);
        }
       
        $quiz = Quiz::with('quizQuestions.question')->findOrFail($quiz_id);

        // echo "<pre>";
        //     print_r($quiz);
        // echo "</pre>";
        // dd();

        return view('quiz.quiz_start', compact('quiz'));
    }

    public function viewResult(Request $request)
    {
        $currentUser = auth()->user();
        $quiz_id = decode_id($request->id);

        $quiz = Quiz::with('quizQuestions.question')->findOrFail($quiz_id);

        $quizAttempt = $quiz->quizAttempts()->where('student_id', $currentUser->id)->first();

        $answers = QuizAnswer::where('quiz_id', $quiz_id)
            ->where('user_id', $currentUser->id)
            ->get()
            ->keyBy('question_id');

        return view('quiz.view_result', compact('quiz', 'answers', 'quizAttempt'));
    }

    public function saveAnswer(Request $request)
    {

        $validated = $request->validate([
            'quiz_id'      => 'required|integer',
            'question_id'  => 'required|integer',
            'answer'       => 'required|string',
        ]);

        $user = auth()->user();
        $userId = $user->id;

        $question = QuizQuestion::findOrFail($validated['question_id']);

        $isCorrect = false;

        if ($question->question_type === 'sequence') {
            $correctOrder = strtoupper(str_replace(' ', '', $question->correct_option));
            $userOrder = strtoupper(str_replace(' ', '', $validated['answer']));

            if ($userOrder === $correctOrder) {
                $isCorrect = true;
            }
        }
        elseif ($question->question_type === 'single_choice') {
            $isCorrect = strtoupper($question->correct_option) === strtoupper($validated['answer']);
        } 
        elseif ($question->question_type === 'multiple_choice') {
            $correct = collect(explode(',', strtoupper($question->correct_option)))->sort()->values()->implode(',');
            $answer = collect(explode(',', strtoupper($validated['answer'])))->sort()->values()->implode(',');
            $isCorrect = $correct === $answer;
        }

       QuizAnswer::updateOrCreate(
            [
                'quiz_id'  => $validated['quiz_id'],
                'user_id'  => $userId,
                'question_id' => $validated['question_id'],
            ],
            [
                'selected_option' => $validated['answer'],
                'is_correct'      => $isCorrect ?? 0,
            ]
        );

        return response()->json(['success' => true]);
    }

    // Question functions 

    public function createQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic_id'   => 'required|integer|exists:topics,id',
            'questions' => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $createdQuestions = [];

        foreach ($request->questions as $q) {

            $questionValidator = Validator::make($q, [
                'question' => 'required|string|max:1000',
                'type'     => 'required|string|in:text,single_choice,multiple_choice,sequence',
                'options'  => 'nullable|array',
            ]);

            if ($questionValidator->fails()) {
                return response()->json(['errors' => $questionValidator->errors()], 422);
            }

            $options = [
                'option_A' => $q['options'][0] ?? null,
                'option_B' => $q['options'][1] ?? null,
                'option_C' => $q['options'][2] ?? null,
                'option_D' => $q['options'][3] ?? null,
            ];

            $correct_option = $q['correct_answer'] ?? null;

            $createdQuestions[] = TopicQuestion::create([
                'topic_id'        => $request->topic_id,
                'question_text'  => $q['question'],
                'question_type'  => $q['type'],
                'option_A'       => $options['option_A'],
                'option_B'       => $options['option_B'],
                'option_C'       => $options['option_C'],
                'option_D'       => $options['option_D'],
                'correct_option' => $correct_option,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Questions created successfully.',
        ]);
    }

    public function editQuestion(Request $request)
    {
        $questionId = decode_id($request->id);
        $question = TopicQuestion::findOrFail($questionId);

        return response()->json(['question' => $question]);
    }

    public function updateQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'question_id'   => 'required|numeric',
            'question_text' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $option_A = $request->option_A ? $request->option_A : null;
        $option_B = $request->option_B ? $request->option_B : null;
        $option_C = $request->option_C ? $request->option_C : null;
        $option_D = $request->option_D ? $request->option_D : null;

        $correct_option = $request->correct_option ? $request->correct_option : null;

        $question = TopicQuestion::findOrFail($request->question_id);

        $question->update([
            'question_text' => $request->question_text,
            'option_A' => $option_A,
            'option_B' => $option_B,
            'option_C' => $option_C,
            'option_D' => $option_D,
            'correct_option' => $correct_option,
        ]);

        return response()->json(['success' => true, 'message' => 'Question updated successfully.']);
    }

    public function destroyQuestion(Request $request)
    {
        $questionId = decode_id($request->question_id);

        $question = TopicQuestion::findOrFail($questionId);
        $question->delete();

        return redirect()->route('quiz.view', ['id' => $request->quiz_id])->with('message', 'Question deleted successfully');
    }

    // public function submit(Request $request, $id)
    // {

    //     echo "<pre>";
    //         print_r($request->all());
    //     echo "</pre>";

    //     dd();

    //     $quiz = Quiz::findOrFail(decode_id($id));
    //     $answers = $request->input('questions');

    //     foreach ($answers as $questionId => $answerData) {
    //     }

    //     return redirect()->route('quiz.view', encode_id($quiz->id))
    //                     ->with('message', 'Quiz submitted successfully!');
    // }

    // public function saveAnswer(Request $request)
    // {

    //     echo "<pre>";
    //         print_r($request->all());
    //     echo "</pre>";

    //     dd();

    //     // Validate the incoming data
    //     $validated = $request->validate([
    //         'attempt_id' => 'required|exists:quiz_attempts,id',
    //         'user_id' => 'required|exists:users,id',
    //         'question_id' => 'required|exists:quiz_questions,id',
    //         'answer' => 'required|string',
    //     ]);

    //     // Get the question, attempt, and user
    //     $attempt = QuizAttempt::findOrFail($validated['attempt_id']);
    //     $question = QuizQuestion::findOrFail($validated['question_id']);
    //     $user = User::findOrFail($validated['user_id']);

    //     // Check if the answer is correct (you can customize this logic as needed)
    //     $isCorrect = false;
    //     if ($question->correct_option == $validated['answer']) {
    //         $isCorrect = true;
    //     }

    //     // Save the answer in the quiz_answers table
    //     QuizAnswer::updateOrCreate(
    //         [
    //             'attempt_id' => $validated['attempt_id'],
    //             'question_id' => $validated['question_id']
    //         ],
    //         [
    //             'selected_option' => $validated['answer'],
    //             'is_correct' => $isCorrect
    //         ]
    //     );

    //     // Respond with a success message
    //     return response()->json(['success' => true]);
    // }

    //  echo "<pre>";
    //     print_r($question);
    // echo "</pre>";

    // dd();


}
