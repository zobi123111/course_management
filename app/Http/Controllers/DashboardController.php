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
use Illuminate\Support\Facades\Auth;
use App\Models\ParentRating;
 
class DashboardController extends Controller
{
    // public function index()
    // {
    //     $ou_id = auth()->user()->ou_id;
    //     $user_count = 0;
    //     $course_count = 0;
    //     $group_count = 0;
    //     $folder_count = 0;
       
    //     if(Auth()->user()->is_owner ==  1){
    //         // dd('admin');            
    //         $user_count = User::count();
    //         $course_count = Courses::count();
    //         $group_count = Group::count();
    //         $folder_count = Folder::whereNull('parent_id')->with('children')->get()->count();
    //         $documents = Document::all();
    //         $requestCount = 0;
    //     }elseif(Auth()->user()->is_admin==1){
    //         // dd('ou');            
    //         $user_count = User::where('ou_id' , $ou_id)->count();
    //         $course_count = Courses::where('ou_id' , $ou_id)->count();
    //         $group_count = Group::where('ou_id' , $ou_id)->count();
    //         $folder_count = Folder::whereNull('parent_id')->where('ou_id', $ou_id)->with('children')->get()->count();
    //         $documents = Document::where('ou_id', $ou_id)->get();
    //         $requestCount = $requestCount = BookedResource::where('ou_id', $ou_id)->count();
    //     }else{
    //         // dd('user');            
    //         $userId = Auth::user()->id;
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
 
    //         $documents = Document::where('ou_id', $ou_id)
    //         ->whereHas('group', function ($query) use ($userId) {
    //             $query->whereJsonContains('user_ids', (string) $userId);
    //         })
    //         ->get();
    //         $course_count = $courses->count();
    //         $group_count = $filteredGroups->count();
    //         // $folder_count = 0;
    //         $requestCount =$requestCount = BookedResource::where('user_id', $userId)
    //         ->where('ou_id', $ou_id)
    //         ->count();
        
    //     }
 
    //     $totalDocuments = $documents->count();
    //     $readDocuments = $documents->where('acknowledged', 1)->count();
    //     $unreadDocuments = $totalDocuments-$readDocuments;
    //     return view('dashboard.index', compact('user_count','course_count', 'group_count', 'folder_count', 'totalDocuments','readDocuments','unreadDocuments', 'requestCount'));
    // }
 
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
    
            // Get documents where user has access via group pivot
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
                        
                        }
    
        $totalDocuments = $documents->count();
        $readDocuments = countAcknowledgedDocuments($documents, $user);
        $unreadDocuments = $totalDocuments - $readDocuments;
    

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
      //  dd($users);
      phpinfo();
      //  return view('dashboard.index', compact('user_count', 'course_count', 'group_count', 'folder_count','totalDocuments', 'readDocuments', 'unreadDocuments', 'requestCount', 'users', 'trainingEvents'
      //  ));
    }
    
}