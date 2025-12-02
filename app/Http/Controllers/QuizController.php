<?php

namespace App\Http\Controllers;

use App\Models\CourseGroup;
use App\Models\CourseLesson;
use App\Models\Courses;
use App\Models\Group;
use App\Models\OrganizationUnits;
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
        $organizationUnits = OrganizationUnits::all();

        if ($currentUser->is_owner == 1 && empty($currentUser->ou_id)) {
            $quizs = Quiz::with('course', 'lesson', 'quizOu')->orderBy('id', 'desc')->get();
            $courses = Courses::where("status", 1)->get(); 

        }
        elseif ($currentUser->is_admin == 1 && !empty($currentUser->ou_id)) {
            $quizs = Quiz::with('course', 'lesson', 'quizOu')->where('ou_id', $currentUser->ou_id)->orderBy('id', 'desc')->get();
            $courses = Courses::where("status", 1)->where('ou_id', $currentUser->ou_id)->get();
        }
        else{
            // $courseIds = TrainingEvents::where('student_id', $currentUser->id)
            // ->pluck('course_id');

            // $quizs = Quiz::with('course', 'lesson', 'quizAttempts')->where('status', 'published')
            // ->whereIn('course_id', $courseIds)->get();  

            $groups = Group::where('status', 1)->whereJsonContains('user_ids', (string)$currentUser->id)->pluck('id');
            $courseIds = CourseGroup::whereIn('group_id', $groups)->pluck('courses_id');

            $quizs = Quiz::with('course', 'lesson', 'quizOu', 'quizAttempts')->where('status', 'published')
                        ->whereIn('course_id', $courseIds)->orderBy('id', 'desc')->get();

        $courses = Courses::where("status", 1)->get(); 

        }
        
        // dd($quizs);
        return view('quiz.index', compact('quizs', 'courses', 'organizationUnits'));
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
            'show_result'   => 'required|string|in:yes,no',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
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
        $quiz->show_result = $request->show_result;
        $quiz->ou_id = (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id;
        $quiz->created_by = Auth::id();
        $quiz->save();

        return response()->json(['success' => true, 'message' => 'Quiz created successfully.']);
    }

    public function view(Request $request)
    {
        $quizId = decode_id($request->id);
        $quiz = Quiz::with('topics.topic')->findOrFail($quizId);
        $topics = Topic::withCount('questions')->where("ou_id", $quiz->ou_id)->get();
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

    public function getCourseByOu(Request $request)
    {
        // dd($request->all());

        $courses = Courses::where('ou_id', $request->ou_id)->get();
        return response()->json($courses);
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
            'show_result'   => 'required|string|in:yes,no',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
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
            'show_result' => $request->show_result,
            'ou_id' => (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id,
        ]);

        return response()->json(['success' => true, 'message' => 'Quiz updated successfully.']);
    }

    public function destroy(Request $request)
    {
        $quizId = decode_id($request->quiz_id);

         $quizTopics = QuizTopic::where('quiz_id', $quizId)->get();

        foreach ($quizTopics as $quizTopic) {
            $quizTopic->delete();
        }

        $quiz = Quiz::findOrFail($quizId);
        $quiz->delete();

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

    public function editTopic(Request $request)
    {
        $request->validate([
            'topic_id'          => 'required',
            'quiz_id'           => 'required',
            'question_quantity' => 'required|integer|min:1'
        ]);

        $topic_id = decode_id($request->topic_id);
        $quiz_id  = decode_id($request->quiz_id);

        $topic = QuizTopic::where('topic_id', $topic_id)->where('quiz_id', $quiz_id)->firstOrFail();

        $oldQty = $topic->question_quantity;
        $newQty = $request->question_quantity;

        $allQuestions = QuizQuestion::where('topic_id', $topic_id)
                                    ->where('quiz_id', $quiz_id)
                                    ->get();
        $assignedQuestions = QuizQuestion::where('topic_id', $topic_id)->where('quiz_id', $quiz_id)->pluck('question_id');

        if ($newQty > $oldQty) {

            $increaseBy = $newQty - $oldQty;

            $extraQuestions = $allQuestions
                                ->whereNotIn('id', $assignedQuestions)
                                ->shuffle()
                                ->take($increaseBy);

            foreach ($extraQuestions as $q) {
                QuizQuestion::create([
                    'quiz_id' => $quiz_id,
                    'topic_id' => $topic_id,
                    'question_id'   => $q->id,
                ]);
            }
        }

        if ($newQty < $oldQty) {

            $decreaseBy = $oldQty - $newQty;

            $randomAssigned = $assignedQuestions->shuffle()->take($decreaseBy);

            QuizQuestion::where('quiz_id', $quiz_id)->where('topic_id', $topic_id)
                ->whereIn('question_id', $randomAssigned)
                ->delete();
        }

        $topic->question_quantity = $newQty;
        $topic->save();

        return back()->with('message', 'Topic quantity updated successfully!');
    }

    public function deleteTopic(Request $request)
    {
        $id = decode_id($request->topic_id);
        $quiz_id = decode_id($request->quiz_id);

        $quizTopic = QuizTopic::where('topic_id', $id)
        ->where('quiz_id', $quiz_id)->first();

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
                    'started_at'  => now(),
                ]);
        }
       
        $quiz = Quiz::with('quizQuestions.question')->findOrFail($quiz_id);


        return view('quiz.quiz_start', compact('quiz'));
    }

    public function viewResult(Request $request)
    {
        $currentUser = auth()->user();
        $quiz_id = decode_id($request->id);

        $quiz = Quiz::with('quizQuestions.question')->findOrFail($quiz_id);

        $quizAttempt = $quiz->quizAttempts()->where('student_id', $currentUser->id)->first();

        // echo "<pre>";
        //     print_r($quizAttempt);
        // echo "</pre>";
        // dd();

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

        $quiz = QuizQuestion::findOrFail($validated['question_id']);

        $question = TopicQuestion::findOrFail($quiz->question_id);

        $isCorrect = false;

        if ($question->question_type === 'sequence') {
            $correctOrder = strtoupper(str_replace(' ', '', $question->correct_option));
            $userOrder = strtoupper(str_replace(' ', '', $validated['answer']));

            if ($userOrder === $correctOrder) {
                $isCorrect = true;
            }
        }
        elseif ($question->question_type === 'single_choice') {
            $correctanswer = strtoupper($question->correct_option);
            $useranswer = strtoupper($validated['answer']);

            if ($correctanswer === $useranswer) {
                $isCorrect = true;
            }
        } 
        elseif ($question->question_type === 'multiple_choice') {
            $correct = collect(explode(',', strtoupper($question->correct_option)))->sort()->values()->implode(',');
            $answer = collect(explode(',', strtoupper($validated['answer'])))->sort()->values()->implode(',');
            // $isCorrect = $correct === $answer;
            if ($correct === $answer) {
                $isCorrect = true;
            }
        }

        QuizAnswer::updateOrCreate(
            [
                'quiz_id'  => $validated['quiz_id'],
                'user_id'  => $userId,
                'question_id' => $validated['question_id'],
            ],
            [
                'selected_option' => $validated['answer'],
                'is_correct'      => $isCorrect,
            ]
        );
               
        if ($request->answertype === 'submitquiz') {
            
            $questionIds = QuizQuestion::where('quiz_id', $validated['quiz_id'])->pluck('question_id');

            $hastextquestion = TopicQuestion::whereIn('id', $questionIds)->where('question_type', 'text')->get();
            
            $quiz = Quiz::findOrFail($validated['quiz_id']);
            $totalCorrect = QuizAnswer::where('quiz_id', $quiz->id)->where('user_id', $userId)->where('is_correct', 1)->count();
            $totalQuestions = QuizTopic::where('quiz_id', $quiz->id)->sum('question_quantity');

            $percentage = round(($totalCorrect / $totalQuestions) * 100, 2);

            $result = ($percentage >= $quiz->passing_score) ? 'pass' : 'fail';

            if ($hastextquestion->isEmpty()) {

                QuizAttempt::updateOrCreate(
                    [
                        'quiz_id'    => $quiz->id,
                        'student_id' => $userId,
                        'status'     => 'in_progress',
                    ],
                    [
                        'submitted_at' => now(),
                        'score'        => $percentage,
                        'status'       => 'completed',
                        'result'       => $result,
                    ]
                );
            }
            else{
                QuizAttempt::updateOrCreate(
                    [
                        'quiz_id'    => $quiz->id,
                        'student_id' => $userId,
                    ],
                    [
                        'status'       => 'completed',
                        'submitted_at' => now(),
                    ]
                );
            }
        }

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
