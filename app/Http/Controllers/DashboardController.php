<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\Courses;
use App\Models\User;
use App\Models\Group;
use App\Models\Folder;
use App\Models\Document;
use App\Models\TrainingEvents;
use App\Models\BookedResource;
use App\Models\Booking;
use App\Models\CourseGroup;
use Illuminate\Support\Facades\Auth;
use App\Models\ParentRating;
use App\Models\Quiz;

class DashboardController extends Controller
{

 
    public function index()
    {
        $user = auth()->user();
        $ou_id = $user->ou_id;
        $userId = $user->id;
    
        $user_count = 0;
        $course_count = 0;
        $group_count = 0;
        $folder_count = 0;
        $requestCount = 0;
        $outstandingItems = collect();
    
        if ($user->is_owner) {
            $user_count = User::count();
            $course_count = Courses::count();
            $group_count = Group::count();
            $folder_count = Folder::whereNull('parent_id')->with('children')->count();
            $documents = Document::with('groups')->get();
             $trainingEvents = [];
        } elseif ($user->is_admin) {
            $user_count = User::where('ou_id', $ou_id)->count();
            $course_count = Courses::where('ou_id', $ou_id)->count();
            $group_count = Group::where('ou_id', $ou_id)->count();      
            $folder_count = Folder::whereNull('parent_id')->where('ou_id', $ou_id)->with('children')->count();
            $documents = Document::where('ou_id', $ou_id)->with('groups')->get();
            $requestCount = BookedResource::where('ou_id', $ou_id)->count();
            $trainingEvents = [];
        } else { 
            
            $groups = Group::all();
            $filteredGroups = $groups->filter(function ($group) use ($userId) {
                $userIds = is_array($group->user_ids) ? $group->user_ids : explode(',', $group->user_ids);
                return in_array($userId, $userIds);
            });
    
            $groupIds = $filteredGroups->pluck('id')->toArray();
    
            $courses = Courses::whereIn('id', function ($query) use ($groupIds) {
                $query->select('courses_id')->from('courses_group')->whereIn('group_id', $groupIds);
            })->get();
    
            $documents = Document::whereHas('groups', function ($query) use ($groupIds) { 
                $query->whereIn('groups.id', $groupIds);
            })
            ->where('ou_id', $ou_id)
            ->with('groups')
            ->get();
    
            $course_count = $courses->count();
            $group_count = $filteredGroups->count();
            $requestCount = BookedResource::where('user_id', $userId)->where('ou_id', $ou_id)->count();
            $user = auth()->user();
            $userId = $user->id;

            // Last Training event
            $currentUser = auth()->user();

            $trainingEventsRelations = [
                    'course:id,course_name,course_type',
                    'student:id,fname,lname',
                    'instructor:id,fname,lname',
                    'resource:id,name',
                    'firstLesson.instructor:id,fname,lname',
                    'firstLesson.resource:id,name',
                    'eventLessons',
                    'eventLessons.lesson:id,enable_cbta',
                    'eventLessons.lesson.subLessons:id,lesson_id,title',
                    'overallAssessments',
                ];



            $trainingEventsQuery = TrainingEvents::where('ou_id', $currentUser->ou_id)
                            ->with($trainingEventsRelations)
                            ->withCount(['taskGradings', 'competencyGradings']);
 
            $trainingEvents = $trainingEventsQuery
                            ->where('student_id', $currentUser->id)
                            ->where(function ($query) use ($currentUser) {
                                $query->whereHas('taskGradings', function ($q) use ($currentUser) {
                                    $q->where('user_id', $currentUser->id);
                                })->orWhereHas('competencyGradings', function ($q) use ($currentUser) {
                                    $q->where('user_id', $currentUser->id);
                                })->orWhereHas('overallAssessments', function ($q) use ($currentUser) {
                                    $q->where('user_id', $currentUser->id);
                                });
                            })

                           ->orderBy('id', 'DESC')
                           ->limit(1)  
                           ->get();
        
           $trainingEvents_instructor = TrainingEvents::where('entry_source', "instructor")
                                        ->where('entry_source', "instructor")
                                        ->where('student_id', $currentUser->id)
                                        ->withCount(['taskGradings', 'competencyGradings'])
                                        ->orderBy('id', 'DESC')
                                    // ->limit(1)  
                                        ->get();
            // dd($trainingEvents_instructor);

            foreach ($trainingEvents as $event) {
                $outstandingItems->push([
                    'type'        => 'TrainingEvents',
                    'title'       => 'Course Feedback Required',
                    'course'      => $event->course?->course_name ?? 'N/A',
                    'lesson'      => $event->firstLesson?->lesson?->title ?? '—',
                    'status'      => 'Pending',
                    'action_url'  => route('training.show', ['event_id' => encode_id($event->id)]),
                    'action_text' => 'Submit Feedback',
                ]);
            }              
        }

        // $quizs = TrainingEvents::where('student_id', $user->id)
        //                         ->with('quizzes')
        //                         ->get();
        // $courseIds = TrainingEvents::where('student_id', $user->id)
        //     ->pluck('course_id');

        // $quizzes = Quiz::whereIn('course_id', $courseIds)->where('status', 'published')->get();

        $groups = Group::where('status', 1)->whereJsonContains('user_ids', (string)$user->id)->pluck('id');
        $courseIds = CourseGroup::whereIn('group_id', $groups)->pluck('courses_id');

        $quizzes = Quiz::where('status', 'published')->whereIn('course_id', $courseIds)->get(); 

        $groups = Group::where('status', 1)->whereJsonContains('user_ids', (string)$user->id)->pluck('id');
        $courseIds = CourseGroup::whereIn('group_id', $groups)->pluck('courses_id');

        $quizs = Quiz::with('course', 'lesson', 'quizAttempts')
                        ->where('status', 'published')
                        ->whereIn('course_id', $courseIds)
                        ->whereDoesntHave('quizAttempts', function ($q) use ($user) {
                        $q->where('student_id', $user->id); })->get();

            foreach ($quizs as $quiz) {
                $outstandingItems->push([
                    'type'        => 'quiz',
                    'title'       => $quiz->title,
                    'course'      => $quiz->course->course_name ?? 'N/A',
                    'lesson'      => $quiz->lesson->lesson_title ?? 'N/A',
                    'duration'    => $quiz->duration . ' mins',
                    'passing'     => $quiz->passing_score . '%',
                    'status'      => 'Pending',
                    'action_url'  => route('quiz.view', ['id' => encode_id($quiz->id)]),
                    'action_text' => 'Start Quiz',
                ]);
            }

        $totalDocuments = $documents->count();
        $quizscount = $quizzes->count();
        $readDocuments = countAcknowledgedDocuments($documents, $user);
        $unreadDocuments = $totalDocuments - $readDocuments;


        $bookings = Booking::where('instructor_id', $userId)->where('status', 'pending')->get();

        $users = User::where('ou_id', $ou_id)
                    ->whereNull('is_admin')
                    ->with([
                        'documents',
                        'usrRatings' => function ($query) {
                            $query->whereIn('linked_to', ['licence_1', 'licence_2'])
                                ->with([
                                    'parentRating.associatedChildren',  
                                    'parentRating'
                                ]);
                        }
                    ])
                    ->get();
            

        return view('dashboard.index', compact('user_count', 'course_count', 'group_count', 'folder_count','totalDocuments', 'quizscount', 'quizs', 'readDocuments', 'unreadDocuments', 'requestCount', 'users', 'trainingEvents', 'bookings', 'outstandingItems'
        ));
    }
    
}