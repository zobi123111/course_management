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
use App\Models\UserQuiz;
use App\Models\TrainingQuiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

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

            // $quizs = Quiz::with('course', 'lesson', 'quizOu', 'quizAttempts')->where('status', 'published')
            //             ->whereIn('course_id', $courseIds)->orderBy('id', 'desc')->get();

            $quizs = Quiz::with('course', 'lesson', 'quizOu', 'quizAttempts', 'trainingQuizzes')
                ->where('status', 'published')
                ->whereIn('course_id', $courseIds)
                ->whereHas('trainingQuizzes', function ($query) use ($currentUser) {
                    $query->where('is_active', 1)
                        ->where('student_id', $currentUser->id);
                })
                ->orderBy('id', 'desc')
                ->get();

            $courses = Courses::where("status", 1)->get();
        }
        
        // dd($quizs);

        return view('quiz.index', compact('quizs', 'courses', 'organizationUnits'));
    }

    // public function store(Request $request)
    // {

    //     $validator = Validator::make($request->all(), [
    //         'title'         => 'required|string|max:255',
    //         'course_id'     => 'required|numeric',
    //         'lesson_id'     => 'required|numeric',
    //         'duration'      => 'required|numeric|min:1',
    //         'passing_score' => 'required|numeric|min:0|max:100',
    //         'status'        => 'required|string|in:draft,published',
    //         'show_result'   => 'required|string|in:yes,no',
    //         'question_selection' => 'required|in:manual,random',
    //         'question_count' => 'required_if:question_selection,random|nullable|numeric|min:1',
    //         'ou_id' => [
    //             function ($attribute, $value, $fail) {
    //                 if (auth()->user()->role == 1 && empty(auth()->user()->ou_id) && empty($value)) {
    //                     $fail('The Organizational Unit (OU) is required for Super Admin.');
    //                 }
    //             }
    //         ],
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     $quiz = new Quiz();
    //     $quiz->title = $request->title;
    //     $quiz->course_id = $request->course_id;
    //     $quiz->lesson_id = $request->lesson_id;
    //     $quiz->duration = $request->duration;
    //     $quiz->passing_score = $request->passing_score;
    //     $quiz->quiz_type = $request->quiz_type;
    //     $quiz->status = $request->status;
    //     $quiz->show_result = $request->show_result;
    //     $quiz->question_selection = $request->question_selection;
    //     $quiz->question_count = $request->question_selection === 'random' ? $request->question_count : null;
    //     $quiz->ou_id = (auth()->user()->role == 1 && empty(auth()->user()->ou_id)) ? $request->ou_id : auth()->user()->ou_id;
    //     $quiz->created_by = Auth::id();
    //     $quiz->save();

    //     return response()->json(['success' => true, 'message' => 'Quiz created successfully.']);
    // }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'course_id' => 'required|numeric',
            'lesson_id' => 'required|numeric',
            'duration' => 'required|numeric|min:1',
            'passing_score' => 'required|numeric|min:0|max:100',
            'status' => 'required|in:draft,published',
            'show_result' => 'required|in:yes,no',
            'question_selection' => 'required|in:manual,random',
            'question_count' => 'required_if:question_selection,random|nullable|numeric|min:1',
            'ou_id' => [
                function ($attribute, $value, $fail) {
                    if (
                        auth()->user()->role == 1 &&
                        empty(auth()->user()->ou_id) &&
                        empty($value)
                    ) {
                        $fail('The Organizational Unit (OU) is required for Super Admin.');
                    }
                }
            ],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $quiz = Quiz::create([
            'title' => $request->title,
            'course_id' => $request->course_id,
            'lesson_id' => $request->lesson_id,
            'duration' => $request->duration,
            'passing_score' => $request->passing_score,
            'quiz_type' => $request->quiz_type,
            'status' => $request->status,
            'show_result' => $request->show_result,
            'question_selection' => $request->question_selection,
            'question_count' => $request->question_selection === 'random'
                ? $request->question_count
                : null,
            'ou_id' => (auth()->user()->role == 1 && empty(auth()->user()->ou_id))
                ? $request->ou_id
                : auth()->user()->ou_id,
            'created_by' => Auth::id(),
        ]);

        if ($request->question_selection === 'random') {

            $topics = Topic::where('course_id', $request->course_id)
                ->withCount('questions')
                ->get();

            if ($topics->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No topics found for this course.'
                ], 422);
            }

            $requested = (int) $request->question_count;
            $totalAvailable = $topics->sum('questions_count');

            $distribution = [];

            if ($requested >= $totalAvailable) {

                foreach ($topics as $topic) {
                    $distribution[$topic->id] = $topic->questions_count;
                }

            } else {
                $remaining = $requested;
                $topicCount = $topics->count();
                $base = intdiv($requested, $topicCount);

                foreach ($topics as $topic) {
                    $assign = min($base, $topic->questions_count);
                    $distribution[$topic->id] = $assign;
                    $remaining -= $assign;
                }

                while ($remaining > 0) {
                    $progress = false;

                    foreach ($topics as $topic) {
                        if ($remaining === 0) break;

                        if ($distribution[$topic->id] < $topic->questions_count) {
                            $distribution[$topic->id]++;
                            $remaining--;
                            $progress = true;
                        }
                    }

                    if (!$progress) {
                        break;
                    }
                }
            }

            foreach ($distribution as $topicId => $quantity) {
                QuizTopic::create([
                    'quiz_id' => $quiz->id,
                    'topic_id' => $topicId,
                    'question_quantity' => $quantity,
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Quiz created successfully.'
        ]);
    }

    public function view(Request $request)
    {
        $quizId = decode_id($request->id);
        $quiz = Quiz::with('topics.topic', 'quizQuestions')->findOrFail($quizId);
        $topics = Topic::withCount('questions')->where("ou_id", $quiz->ou_id)
                    ->where("course_id", $quiz->course_id)->get();
                    
        $quizQuestions = QuizQuestion::where('quiz_id', $quizId)->get();
        
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
            'question_selection' => 'required|in:manual,random',
            'question_count' => 'required_if:question_selection,random|nullable|numeric|min:1',
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
            'question_selection' => $request->question_selection,
            'question_count' => $request->question_selection === 'random' ? $request->random_question_count : null,
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
                'question_text'   => $row['question'] ?? null,
                'question_type'   => $row['type'] ?? null,
                'option_type'     => 'text',
                'option_A'        => $option_A,
                'option_B'        => $option_B,
                'option_C'        => $option_C,
                'option_D'        => $option_D,
                'correct_option'  => $row['correct_answers'] ?? null,
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

        // $topicQuestions = TopicQuestion::where('topic_id', $request->topic_id)->pluck('id')->toArray();

        // if (count($topicQuestions) == 0) {
        //     return back()->with('error', 'No questions found for this topic.');
        // }

        // $selectedQuestions = collect($topicQuestions)->shuffle()->take($request->question_quantity);

        // foreach ($selectedQuestions as $questionId) {
        //     QuizTopic::create([
        //         'quiz_id' => $quizId,
        //         'topic_id' => $request->topic_id,
        //         'question_quantity' => $questionId,
        //     ]);
        // }

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

    public function trainingquizupdateStatus(Request $request)
    {
        $quiz = TrainingQuiz::updateOrCreate(
            [
                'student_id' => $request->student,
                'trainingevent_id' => $request->trainingEvent,
                'quiz_id' => $request->id,
            ],
            [
                'is_active' => $request->status ? 1 : 0,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Status saved successfully!',
            'status' => $quiz->status
        ]);
    }

    public function startQuiz(Request $request)
    {
        $currentUser = auth()->user();
        $quiz_id = decode_id($request->id);

        DB::transaction(function () use ($quiz_id, $currentUser) {

            $quiz = Quiz::findOrFail($quiz_id);

            QuizAttempt::firstOrCreate(
                [
                    'quiz_id' => $quiz_id,
                    'student_id' => $currentUser->id,
                ],
                [
                    'started_at' => now(),
                ]
            );

            $exists = QuizQuestion::where('quiz_id', $quiz_id)
                ->where('user_id', $currentUser->id)
                ->exists();

            if ($exists) {
                return;
            }

            $quizTopics = QuizTopic::where('quiz_id', $quiz_id)->get();

            $topicIds = [];
            $questionIds = [];

            foreach ($quizTopics as $topic) {
                $questions = TopicQuestion::where('topic_id', $topic->topic_id)
                    ->inRandomOrder()
                    ->limit($topic->question_quantity)
                    ->pluck('id')
                    ->toArray();

                if (!empty($questions)) {
                    $topicIds[] = $topic->topic_id;
                    $questionIds = array_merge($questionIds, $questions);
                }
            }

            if ($quiz->question_selection === 'random') {
                $requiredCount = $quiz->question_count;
                $currentCount = count($questionIds);

                if ($currentCount < $requiredCount && $currentCount > 0) {
                    $missing = $requiredCount - $currentCount;

                    $duplicates = collect($questionIds)
                        ->random($missing)
                        ->toArray();

                    $questionIds = array_merge($questionIds, $duplicates);
                }
            }

            QuizQuestion::create([
                'quiz_id' => $quiz_id,
                'user_id' => $currentUser->id,
                'topic_id' => $topicIds,
                'question_id' => $questionIds,
            ]);

        });

        $quiz = Quiz::with('quizQuestions')->findOrFail($quiz_id);

        $allQuestionIds = $quiz->quizQuestions
            ->pluck('question_id')
            ->flatten()
            ->unique()
            ->toArray();

        $questions = TopicQuestion::whereIn('id', $allQuestionIds)
            ->get()
            ->keyBy('id');

        return view('quiz.quiz_start', compact('quiz', 'questions'));
    }
    
    // public function startQuiz(Request $request)
    // {
    //     $currentUser = auth()->user();
    //     $quiz_id = decode_id($request->id);

    //     $checkAttempt = QuizAttempt::where('quiz_id', $quiz_id)->where('student_id', $currentUser->id)->first();

    //     if(!$checkAttempt){
    //         $attempt = QuizAttempt::create([
    //                 'quiz_id'        => $quiz_id,
    //                 'student_id'  => $currentUser->id,
    //                 'started_at'  => now(),
    //             ]);
    //     }
       
    //     $quiz = Quiz::with('quizQuestions.question')->findOrFail($quiz_id);

    //     $quiz->setRelation(
    //         'quizQuestions',
    //         $quiz->quizQuestions->shuffle()
    //     );

    //     return view('quiz.quiz_start', compact('quiz'));
    // }

    // public function viewResult(Request $request)
    // {
    //     $currentUser = auth()->user();
    //     $quiz_id = decode_id($request->id);

    //     $quiz = Quiz::with('quizQuestions.question')->findOrFail($quiz_id);

    //     $quizAttempt = $quiz->quizAttempts()->where('student_id', $currentUser->id)->first();

    //     // echo "<pre>";
    //     //     print_r($quizAttempt);
    //     // echo "</pre>";
    //     // dd();

    //     $answers = QuizAnswer::where('quiz_id', $quiz_id)
    //         ->where('user_id', $currentUser->id)
    //         ->get()
    //         ->keyBy('question_id');

    //     return view('quiz.view_result', compact('quiz', 'answers', 'quizAttempt'));
    // }

    // public function viewResult(Request $request)
    // {
    //     $currentUser = auth()->user();
    //     $quiz_id = decode_id($request->id);

    //     $quiz = Quiz::with('quizQuestions.question')->findOrFail($quiz_id);

    //     $quizAttempt = $quiz->quizAttempts()->where('student_id', $currentUser->id)->first();

    //     $userQuiz = UserQuiz::where('quiz_id', $quiz_id)
    //                         ->where('user_id', $currentUser->id)
    //                         ->firstOrFail();

    //     $quizDetails = json_decode($userQuiz->quiz_details, true);

    //     return view('quiz.view_result', compact('quiz', 'quizDetails', 'userQuiz', 'quizAttempt'));
    // }

    // public function viewSingleAttempt(Request $request)
    // {

    //     $quiz_id = decode_id($request->quiz_id);
    //     $user_id = decode_id($request->user_id);

    //     $quiz = Quiz::with('quizQuestions.question')->findOrFail($quiz_id);

    //     $quizAttempt = $quiz->quizAttempts()->where('student_id', $user_id)->first();

    //     $userQuiz = UserQuiz::where('quiz_id', $quiz_id)
    //                         ->where('user_id', $user_id)
    //                         ->firstOrFail();

    //     $quizDetails = json_decode($userQuiz->quiz_details, true);

    //     return view('quiz.view_result', compact('quiz', 'quizDetails', 'userQuiz', 'quizAttempt'));
    // }

    // public function viewResult(Request $request)
    // {
    //     $currentUser = auth()->user();
    //     $quiz_id = decode_id($request->id);

    //     return redirect()->route('quiz.showResultPage', [
    //         'quiz_id' => encode_id($quiz_id),
    //         'user_id' => encode_id($currentUser->id)
    //     ]);
    // }
    public function viewResult(Request $request)
    {
        $quizId = decode_id($request->id);

        $userId = $request->filled('user_id')
            ? $request->query('user_id')
            : auth()->id();

        return redirect()->route('quiz.showResultPage', [
            'quiz_id' => encode_id($quizId),
            'user_id' => encode_id($userId),
        ]);
    }



    public function viewSingleAttempt(Request $request)
    {
        $quiz_id = decode_id($request->quiz_id);
        $user_id = decode_id($request->user_id);

        return response()->json([
            'redirect_url' => route('quiz.showResultPage', [
                'quiz_id' => encode_id($quiz_id),
                'user_id' => encode_id($user_id)
            ])
        ]);
    }

    // public function showResultPage(Request $request)
    // {
    //     $quiz_id = decode_id($request->query('quiz_id'));
    //     $user_id = decode_id($request->query('user_id'));

    //     $quiz = Quiz::with(['quizQuestions' => function ($q) use ($user_id) {
    //         $q->where('user_id', $user_id);
    //     }])->findOrFail($quiz_id);

    //     $quizAttempt = QuizAttempt::where('quiz_id', $quiz_id)->where('student_id', $user_id)->first();

    //     $userQuiz = UserQuiz::where('quiz_id', $quiz_id)->where('user_id', $user_id)->firstOrFail();
    //     $quizDetails = json_decode($userQuiz->quiz_details, true);
    //     $allQuestionIds = $quiz->quizQuestions->pluck('question_id')->flatten()->unique()->toArray();
    //     $questions = TopicQuestion::whereIn('id', $allQuestionIds)->get()->keyBy('id');

    //     return view('quiz.view_result', compact('quiz', 'quizAttempt', 'quizDetails', 'userQuiz', 'questions'));
    // }
    public function showResultPage(Request $request)
    {
        $quiz_id = decode_id($request->query('quiz_id'));
        $user_id = decode_id($request->query('user_id'));

        $quiz = Quiz::findOrFail($quiz_id);

        $userQuizzes = UserQuiz::where('quiz_id', $quiz_id)
            ->where('user_id', $user_id)
            ->orderByDesc('created_at')
            ->get();

        $quizAttempt = QuizAttempt::where('quiz_id', $quiz_id)->where('student_id', $user_id)->first();

        $latestQuizAttempt = QuizAttempt::where('quiz_id', $quiz_id)
            ->where('student_id', $user_id)
            ->orderByDesc('submitted_at')
            ->first();

        $latestQuizDetails = json_decode(optional($userQuizzes->first())->quiz_details, true) ?? [];
        $allQuestionIds = collect($latestQuizDetails)->pluck('question_id')->unique();

        $questions = TopicQuestion::whereIn('id', $allQuestionIds)->get()->keyBy('id');

        return view('quiz.view_result', compact(
            'quiz',
            'userQuizzes',
            'quizAttempt',
            'latestQuizAttempt',
            'questions'
        ));
    }



    public function viewAttempts(Request $request)
    {
        $currentUser = auth()->user();
        $quiz_id = decode_id($request->id);

        $quiz = Quiz::findOrFail($quiz_id);

        $Attempt = QuizAttempt::with('student')->where('quiz_id', $quiz_id)->get();

        return view('quiz.view_attempts', compact('Attempt', 'quiz'));
    }

    public function resetAttempt(Request $request)
    {
        $quiz_id = decode_id($request->quiz_id);
        $user_id = decode_id($request->user_id);
        $attempt_id = decode_id($request->attempt_id);

        $attempt = QuizAttempt::where('id', $attempt_id)
                            ->where('quiz_id', $quiz_id)
                            ->where('student_id', $user_id)
                            ->first();

        if (!$attempt) {
            return response()->json(['message' => 'Attempt not found'], 404);
        }

        $answers = QuizAnswer::where('quiz_id', $quiz_id)->where('user_id', $user_id)->get();

        foreach ($answers as $answer) {
            $answer->delete();
        }

        $attempt->delete();

        return response()->json(['message' => 'Attempt reset successfully']);
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

    public function saveFinalQuizData(Request $request)
    {
        $quiz = Quiz::with('quizQuestions')->find($request->quiz_id);

        if (!$quiz) {
            return response()->json(['status' => false, 'message' => 'Quiz not found'], 404);
        }

        $userId = Auth::id();
        $finalData = [];

        foreach ($quiz->quizQuestions as $qq) {
            $questionIds = is_array($qq->question_id) ? $qq->question_id : json_decode($qq->question_id, true);

            if (!$questionIds) continue;

            $questions = TopicQuestion::whereIn('id', $questionIds)->get();

            foreach ($questions as $q) {
                $userAnswer = QuizAnswer::where('quiz_id', $quiz->id)
                    ->where('user_id', $userId)
                    ->where('question_id', $q->id)
                    ->value('selected_option');

                $finalData[] = [
                    'question_id' => $q->id,
                    'question_text' => $q->question_text,
                    'question_image' => $q->question_image,
                    'question_type' => $q->question_type,
                    'option_type' => $q->option_type,
                    'option_A' => $q->option_A,
                    'option_B' => $q->option_B,
                    'option_C' => $q->option_C,
                    'option_D' => $q->option_D,
                    'correct_option' => $q->correct_option,
                    'user_answer' => $userAnswer,
                ];
            }
        }

        UserQuiz::create([
            'quiz_id' => $quiz->id,
            'user_id' => $userId,
            'quiz_details' => json_encode($finalData),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Final quiz data saved successfully!',
        ]);
    }

    // public function saveFinalQuizData(Request $request)
    // {
    //     $quiz = Quiz::with('quizQuestions.question')->find($request->quiz_id);

    //     if (!$quiz) {
    //         return response()->json(['status' => false, 'message' => 'Quiz not found'], 404);
    //     }

    //     $userId = auth()->id();

    //     $finalData = [];

    //     foreach ($quiz->quizQuestions as $qq) {
    //         $q = $qq->question;

    //         $userAnswer = $qq->userAnswer ? $qq->userAnswer->selected_option : null;

    //         $finalData[] = [
    //             'question_id'    => $q->id,
    //             'question_text'  => $q->question_text,
    //             'question_image' => $q->question_image,
    //             'question_type'  => $q->question_type,
    //             'option_type'    => $q->option_type,
    //             'option_A'       => $q->option_A,
    //             'option_B'       => $q->option_B,
    //             'option_C'       => $q->option_C,
    //             'option_D'       => $q->option_D,
    //             'correct_option' => $q->correct_option,
    //             'user_answer'    => $userAnswer,
    //         ];
    //     }

    //     echo "<pre>";
    //         print_r('Quiz = ' . $quiz->id);
    //     echo "<br>";
    //         print_r('User = ' . $userId);
    //     echo "<br>";
    //         print_r('finalData = ' . $finalData);
    //     echo "</pre>";

    //     dd();

    //     UserQuiz::create([
    //         'quiz_id'   => $quiz->id,
    //         'user_id'   => $userId,
    //         'quiz_details' => json_encode($finalData),
    //     ]);

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Final quiz data saved successfully!'
    //     ]);
    // }


    // Question functions 

    // public function createQuestion(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'topic_id'   => 'required|integer|exists:topics,id',
    //         'questions' => 'required|array|min:1',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     $createdQuestions = [];

    //     foreach ($request->questions as $q) {

    //         $questionValidator = Validator::make($q, [
    //             'question' => 'required|string|max:1000',
    //             'type'     => 'required|string|in:text,single_choice,multiple_choice,sequence',
    //             'options'  => 'nullable|array',
    //         ]);

    //         if ($questionValidator->fails()) {
    //             return response()->json(['errors' => $questionValidator->errors()], 422);
    //         }

    //         $options = [
    //             'option_A' => $q['options'][0] ?? null,
    //             'option_B' => $q['options'][1] ?? null,
    //             'option_C' => $q['options'][2] ?? null,
    //             'option_D' => $q['options'][3] ?? null,
    //         ];

    //         $correct_option = $q['correct_answer'] ?? null;

    //         $createdQuestions[] = TopicQuestion::create([
    //             'topic_id'        => $request->topic_id,
    //             'question_text'  => $q['question'],
    //             'question_type'  => $q['type'],
    //             'option_A'       => $options['option_A'],
    //             'option_B'       => $options['option_B'],
    //             'option_C'       => $options['option_C'],
    //             'option_D'       => $options['option_D'],
    //             'correct_option' => $correct_option,
    //         ]);
    //     }

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Questions created successfully.',
    //     ]);
    // }

    public function createQuestion(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic_id'   => 'required|integer|exists:topics,id',
            'questions'  => 'required|array|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        foreach ($request->questions as $index => $q) {

            $questionValidator = Validator::make($q, [
                'question'        => 'nullable|string|max:1000',
                'question_image'  => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
                'type'            => 'required|string|in:text,single_choice,multiple_choice,sequence',
                'option_type'     => 'nullable|string|in:text,image',
                'options_text'    => 'nullable|array',
                'options_image'   => 'nullable|array',
                'correct_answer'  => 'nullable|string|max:255',
            ]);

            if ($questionValidator->fails()) {
                return response()->json(['errors' => $questionValidator->errors()], 422);
            }

            $questionImagePath = null;

            if (isset($q['question_image']) && $q['question_image']) {
                $questionImagePath = $q['question_image']->store('question_images', 'public');
            }

            $optionType = $q['option_type'] ?? 'text';

            $option_A = null;
            $option_B = null;
            $option_C = null;
            $option_D = null;

            if ($optionType === 'text') {
                $option_A = $q['options_text'][0] ?? null;
                $option_B = $q['options_text'][1] ?? null;
                $option_C = $q['options_text'][2] ?? null;
                $option_D = $q['options_text'][3] ?? null;

            } else {
                if (!empty($q['options_image'][0])) {
                    $option_A = $q['options_image'][0]->store('option_images', 'public');
                }
                if (!empty($q['options_image'][1])) {
                    $option_B = $q['options_image'][1]->store('option_images', 'public');
                }
                if (!empty($q['options_image'][2])) {
                    $option_C = $q['options_image'][2]->store('option_images', 'public');
                }
                if (!empty($q['options_image'][3])) {
                    $option_D = $q['options_image'][3]->store('option_images', 'public');
                }
            }

            TopicQuestion::create([
                'topic_id'        => $request->topic_id,
                'question_text'   => $q['question'] ?? null,
                'question_image'  => $questionImagePath,
                'question_type'   => $q['type'],
                'option_type'     => $optionType,
                'option_A'        => $option_A,
                'option_B'        => $option_B,
                'option_C'        => $option_C,
                'option_D'        => $option_D,
                'correct_option'  => $q['correct_answer'] ?? null,
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
            'question_id'      => 'required|numeric|exists:topic_questions,id',
            'question_text'    => 'nullable|string|max:1000',
            'question_image'   => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'option_type'      => 'nullable|string|in:text,image',
            'options_text'     => 'nullable|array',
            'options_image'    => 'nullable|array',
            'correct_option'   => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $question = TopicQuestion::findOrFail($request->question_id);

        if ($request->hasFile('question_image')) {
            $questionImagePath = $request->file('question_image')->store('question_images', 'public');
        } else {
            $questionImagePath = $question->question_image;
        }

        $option_A = $option_B = $option_C = $option_D = null;

        if ($question->option_type === 'text') {
            $option_A = $request->option_A ?? $question->option_A;
            $option_B = $request->option_B ?? $question->option_B;
            $option_C = $request->option_C ?? $question->option_C;
            $option_D = $request->option_D ?? $question->option_D;
        } else {
            $option_A = isset($request->option_A) ? $request->option_A->store('option_images', 'public') : $question->option_A;
            $option_B = isset($request->option_B) ? $request->option_B->store('option_images', 'public') : $question->option_B;
            $option_C = isset($request->option_C) ? $request->option_C->store('option_images', 'public') : $question->option_C;
            $option_D = isset($request->option_D) ? $request->option_D->store('option_images', 'public') : $question->option_D;
        }

        $question->update([
            'question_text'  => $request->question_text ?? $question->question_text,
            'question_image' => $questionImagePath,
            'option_A'       => $option_A,
            'option_B'       => $option_B,
            'option_C'       => $option_C,
            'option_D'       => $option_D,
            'correct_option' => $request->correct_option ?? $question->correct_option,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Question updated successfully.',
            'question' => $question
        ]);
    }


    // public function updateQuestion(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'question_id'   => 'required|numeric',
    //         'question_text' => 'required|string|max:1000',
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json(['errors' => $validator->errors()], 422);
    //     }

    //     $option_A = $request->option_A ? $request->option_A : null;
    //     $option_B = $request->option_B ? $request->option_B : null;
    //     $option_C = $request->option_C ? $request->option_C : null;
    //     $option_D = $request->option_D ? $request->option_D : null;

    //     $correct_option = $request->correct_option ? $request->correct_option : null;

    //     $question = TopicQuestion::findOrFail($request->question_id);

    //     $question->update([
    //         'question_text' => $request->question_text,
    //         'option_A' => $option_A,
    //         'option_B' => $option_B,
    //         'option_C' => $option_C,
    //         'option_D' => $option_D,
    //         'correct_option' => $correct_option,
    //     ]);

    //     return response()->json(['success' => true, 'message' => 'Question updated successfully.']);
    // }

    public function destroyQuestion(Request $request)
    {
        $questionId = decode_id($request->question_id);
        $topicId = decode_id($request->quiz_id);

        $quizQuestions = QuizQuestion::where('question_id', $questionId)->where('topic_id', $topicId)->get();

        foreach ($quizQuestions as $quizQuestion) {
            $quizQuestion->delete();
        }

        $question = TopicQuestion::findOrFail($questionId);
        $question->delete();

        return redirect()->route('topic.topic_view', ['id' => $request->quiz_id])->with('message', 'Question deleted successfully');
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
