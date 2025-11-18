<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\SubLessonController;
use App\Http\Controllers\TrainingEventsController;
use App\Http\Controllers\PrerequisiteController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CourseTemplateController;
use App\Http\Controllers\UserActivityLogController;
use App\Http\Controllers\TrainingFeedbackController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\CbtaControlller;




/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Login Route 
Route::match(['get', 'post'], '/', [LoginController::class, 'index']);
Route::match(['get', 'post'], '/login', [LoginController::class, 'login'])->name('login');
Route::get('/logout', [LoginController::class, 'logOut']);
Route::get('/forgot-password', [LoginController::class, 'forgotPasswordView'])->name('forgot-password');
Route::post('/forgot-password', [LoginController::class, 'forgotPassword'])->name('forgot.password');
Route::get('/reset/password/{token}', [LoginController::class, 'resetPassword']);
Route::post('/reset/password', [LoginController::class, 'submitResetPasswordForm'])->name('submit.reset.password');
Route::get('/send_notification', [LoginController::class, 'send_notification'])->name('send_notification.index');

//change passwword
// Route::get('change-password', [LoginController::class, 'showChangePasswordForm'])->name('changePassword');
// Route::post('change-password', [LoginController::class, 'changePassword'])->name('update-password');

Route::group(['middleware' => ['auth']], function () {

    Route::get('/activity-logs', [UserActivityLogController::class, 'showAll'])->name('logs.index');
    Route::get('/activity-logs/data', [UserActivityLogController::class, 'getLogs'])->name('logs.data');

    Route::get('change-password', [LoginController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('change-password', [LoginController::class, 'changePassword'])->name('update-password');
    Route::get('/users/profile', [UserController::class, 'profile'])->name('user.profile'); 
  
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index'); 
    Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');
    
    //User Routes
    Route::post('/users/profile/update', [UserController::class, 'profileUpdate'])->name('profile.update');
    Route::get('/users/data', [UserController::class, 'getData'])->name('users.data');
    Route::get('/users/show/{user_id}', [UserController::class, 'showUser'])->name('user.show');
    //Users rating routes
    Route::get('/users/rating', [UserController::class, 'showRating'])->name('users.rating'); 
    Route::get('/users/ou_rating', [UserController::class, 'ou_rating'])->name('users.ou_rating');  

    Route::post('/rating/save', [UserController::class, 'saveRating'])->name('rating.store');
    Route::get('/rating/edit', [UserController::class, 'getRating'])->name('rating.edit');
    Route::post('/rating/select_rating', [UserController::class, 'select_rating'])->name('rating.select_rating');
    Route::post('/rating/deselect_rating', [UserController::class, 'deselect_rating'])->name('rating.deselect_rating');
    Route::post('/users/change-password', [UserController::class, 'changePassword'])->name('user.change-password');




    Route::post('/rating/update', [UserController::class, 'updateRating'])->name('rating.update');
    Route::post('/rating/delete', [UserController::class, 'deleteRating'])->name('rating.delete');
    Route::get('/rating/get-by-ou', [UserController::class, 'getRatingsByOU']);
    //Server-Side Datatable Routes
    Route::get('/orgunit/data', [OrganizationController::class, 'getData'])->name('orgunit.data');  
    Route::post('/users/switch_role', [UserController::class, 'switchRole'])->name('user.switch_role');
    Route::get('/documents/data', [DocumentController::class, 'getDocuments'])->name('documents.data');
    Route::get('/folders/list', [FolderController::class, 'getFolders'])->name('folders.list');
    Route::get('/documents/root-list', [FolderController::class, 'getRootFolderDocuments'])->name('documents.root.list');
    Route::get('/folders/subfolders-list', [FolderController::class, 'getSubfolders'])->name('folders.subfolders.list');
    Route::get('/subfolder-documents', [FolderController::class, 'getSubfolderDocuments'])->name('subfolder.documents');

    Route::post('/prerequisites/save', [PrerequisiteController::class, 'store'])->name('prerequisites.save');

    //Coursess Routes
    Route::post('/courses/{course}/prerequisites/store', [PrerequisiteController::class, 'store'])
    ->name('course.prerequisites.store');
    Route::post('/lessons/{course}/{lesson}/prerequisites/store', [LessonController::class, 'prerequisitesStore'])
    ->name('lesson.prerequisites.store');
    Route::get('/lesson-pdf/{lessonId}', [LessonController::class, 'lessonPdf'])->name('lesson-pdf.downloadPdf');    
 
    //ORG Unit Routes
    Route::get('/orgunit/get_permissions', [OrganizationController::class, 'getPermissions'])->name('orgunit.getPermissions');  
    Route::post('/orgunit/permission_store', [OrganizationController::class, 'storePermissions'])->name('orgunit.storePermissions');
    Route::get('/orgunit/user_list', [OrganizationController::class, 'showOrgUsers'])->name('orgunit.user_list');  

    //Training Event Routes
    Route::get('/training/get_ou_students_instructors_resources/{ou_id}', [TrainingEventsController::class, 'getOrgStudentsInstructorsResources'])->name('training.get_ou_students_instructors_resources');
    Route::get('/training/get_course_lessons', [TrainingEventsController::class, 'getCourseLessons'])->name('training.course-lessons');
    Route::get('/training/get_licence_number_and_courses/{user_id}/{ou_id}', [TrainingEventsController::class, 'getStudentLicenseNumberAndCourses'])->name('training.get_licence_number_and_courses');
    Route::post('/grading/acknowledge', [TrainingEventsController::class, 'acknowledgeGarding'])->name('grading.acknowledge');
    Route::get('/training/get_instructor_license_no/{instructor_id}/{selectedCourseId}', [TrainingEventsController::class, 'getInstructorLicenseNumber'])->name('training.get_instructor_license_no');
    Route::get('/lesson-report/download/{event_id}/{lesson_id}/{userID}', [TrainingEventsController::class, 'downloadLessonReport'])
    ->name('lesson.report.download'); 

    Route::get('/deffered-lesson-report/download/{event_id}/{lesson_id}/{userID}', [TrainingEventsController::class, 'downloadDefferedLessonReport'])
    ->name('lesson.deffered.report.download');

    Route::post('/training/{trainingEvent}/upload-documents', [TrainingEventsController::class, 'uploadDocuments'])
    ->name('training.upload-documents');
    Route::get('/training/certificate/{event}', [TrainingEventsController::class, 'generateCertificate'])
     ->name('training.certificate');
    Route::post('/training/submit_deferred_items', [TrainingEventsController::class, 'storeDeferredLessons']) 
    ->name('training.deferred-lessons.store');

    Route::post('/training/edit_customLesson', [TrainingEventsController::class, 'edit_customLesson'])->name('edit_customLesson');
    Route::post('/training/delete_customLesson', [TrainingEventsController::class, 'delete_customLesson'])->name('delete_customLesson');
    Route::post('/training/delete_deferredLesson', [TrainingEventsController::class, 'delete_deferredLesson'])->name('delete_deferredLesson');
    Route::post('/training/update_deferred_form', [TrainingEventsController::class, 'update_deferred_form'])->name('update_deferred_form');
      

    Route::post('/training/store-def-grading', [TrainingEventsController::class, 'storeDefGrading'])
    ->name('training.store_def_grading');
    Route::post('/training/end-course', [TrainingEventsController::class, 'endCourse'])->name('training.endCourse');
    Route::post('/training/unlock-lesson', [TrainingEventsController::class, 'unlockLesson'])->name('training.unlockLesson');
     Route::post('/training/unlock-deflesson', [TrainingEventsController::class, 'unlock_deflesson'])->name('training.unlock_deflesson');
    Route::post('/training/update-competency-grade', [TrainingEventsController::class, 'updateCompGrade'])->name('training.updateCompGrade');
    Route::get('/training/get-recom-instructors/{id}', [TrainingEventsController::class, 'getEventInstructors'])->name('training.getRecommendedInstructors');
    Route::post('/training/unlocked', [TrainingEventsController::class, 'unlocked_trainingEvent'])->name('training.unlocked');
    Route::post('/training/submit_normal_items', [TrainingEventsController::class, 'submit_normal_items'])->name('submit_normal_items.unlocked');

     Route::post('/training/get_lessonId', [TrainingEventsController::class, 'get_lessonId'])->name('get_lessonId.unlocked');


    //Grading feedback routes
    Route::get('/grading/feedback_form/{event_id}', [TrainingFeedbackController::class, 'index'])->name('training.feedback.form');
    Route::post('/grading/feedback_form/submit', [TrainingFeedbackController::class, 'submitFeedbackForm'])->name('training.feedback.submit');

    //Folder Routes
    Route::get('/folder/get_ou_folder/', [DocumentController::class, 'getOrgfolder'])->name('folder.getOrgfolder');
    Route::get('/folder/edit', [FolderController::class, 'getFolder'])->name('folder.edit');

    //Document Routes
    Route::get('/document/get_ou_folder/', [DocumentController::class, 'getOrgfolder'])->name('document.getOrgfolder');
    Route::get('document/user_list', [DocumentController::class, 'getDocUserList'])->name('document.user_list');

    //Group Routes
    Route::get('/group/get_ou_user/', [GroupController::class, 'getOrgUser'])->name('group.get_ou_user');     
    Route::get('/group/get_ou_group/', [GroupController::class, 'getOrgroup'])->name('group.getOrgroup');


});

Route::middleware(['auth', 'role.permission'])->group(function () { 
    //Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
  
    Route::get('/users/document-data', [UserController::class, 'userData'])->name('users.document.data');
    
    //Users Route
    Route::get('/users', [UserController::class, 'getData'])->name('user.index'); 
    Route::post('/users/save', [UserController::class, 'save_user'])->name('user.store');
    Route::post('/users/edit', [UserController::class, 'getUserById'])->name('user.get'); 
    Route::post('/users/update', [UserController::class, 'update'])->name('user.update'); 
    Route::post('/users/delete', [UserController::class, 'destroy'])->name('user.destroy');
    Route::post('/users/verify', [UserController::class, 'docsVerify'])->name('user.verify');
    Route::post('/users/verify_rating', [UserController::class, 'verify_rating'])->name('user.verify_rating');

    Route::post('/users/invalidate-document', [UserController::class, 'invalidateDocument'])->name('user.invalidateDocument');
     Route::post('/get-child-ratings', [UserController::class, 'get_child_ratings']);
     Route::post('/get-children-by-parent', [UserController::class, 'getChildren']);
     Route::post('/users/invalidateRating', [UserController::class, 'invalidateRating'])->name('user.invalidateRating');


    
    //Organization Unit
    Route::get('/orgunit', [OrganizationController::class, 'index'])->name('orgunit.index');
    Route::post('/orgunit/save', [OrganizationController::class, 'saveOrgUnit'])->name('orgunit.store');
    Route::get('/orgunit/edit', [OrganizationController::class, 'getOrgUnit'])->name('orgunit.edit');
    Route::post('/orgunit/update', [OrganizationController::class, 'updateOrgUnit'])->name('orgunit.update');
    Route::post('/orgunit/delete', [OrganizationController::class, 'deleteOrgUnit'])->name('orgunit.delete');

    
    //Courses 
    Route::get('/courses', [CourseController::class, 'index'])->name('course.index'); 
    Route::post('/course/create', [CourseController::class, 'createCourse'])->name('course.store');  
    Route::get('/course/edit', [CourseController::class, 'getCourse'])->name('course.edit');
    Route::post('/course/update', [CourseController::class, 'updateCourse'])->name('course.update');
    Route::post('/course/delete', [CourseController::class, 'deleteCourse'])->name('course.delete'); 
    Route::get('/course/show/{course_id}', [LessonController::class, 'showCourse'])->name('course.show');
    Route::post('/courses/reorder', [CourseController::class, 'reorder'])->name('courses.reorder');
    Route::post('/copy_lesson', [CourseController::class, 'copy_lesson'])->name('copy_lesson.index');

    //Lesson 
    Route::post('/lesson/create', [LessonController::class, 'createLesson'])->name('lesson.store');
    Route::get('/lesson/edit', [LessonController::class, 'getLesson'])->name('lesson.edit');
    Route::get('/lesson/{id}', [LessonController::class, 'showLesson'])->name('lesson.show');
    Route::post('/lesson/update', [LessonController::class, 'updateLesson'])->name('lesson.update');
    Route::post('/lesson/delete', [LessonController::class, 'deleteLesson'])->name('lesson.delete');
    Route::post('/lessons/reorder', [LessonController::class, 'reorder'])->name('lessons.reorder');

    //Sub-Lesson 
    Route::post('/sub-lesson/create', [SubLessonController::class, 'createSubLesson'])->name('sub-lesson.store');
    Route::get('/sub-lesson/edit', [SubLessonController::class, 'getSubLesson'])->name('sub-lesson.edit');
    Route::get('/sub-lesson/{id}', [SubLessonController::class, 'showSubLesson'])->name('sub-lesson.show');
    Route::post('/sub-lesson/update', [SubLessonController::class, 'updateSubLesson'])->name('sub-lesson.update');
    Route::post('/sub-lesson/delete', [SubLessonController::class, 'deleteSubLesson'])->name('sub-lesson.delete');
    Route::post('/sublessons/reorder', [SubLessonController::class, 'reorder'])->name('sublessons.reorder');   


    //Groups Route
    Route::get('/groups', [GroupController::class, 'index'])->name('group.index');
    Route::post('/group/create', [GroupController::class, 'createGroup'])->name('group.store');
    Route::get('/group/edit', [GroupController::class, 'getGroup'])->name('group.edit');
    Route::post('/group/update', [GroupController::class, 'updateGroup'])->name('group.update');
    Route::post('/group/delete', [GroupController::class, 'deleteGroup'])->name('group.delete');
    
    
    //Documents Route
    Route::get('/documents', [DocumentController::class, 'index'])->name('document.index');
    Route::post('/document/create', [DocumentController::class, 'createDocument'])->name('document.store');
    Route::get('/document/edit', [DocumentController::class, 'getDocument'])->name('document.edit');
    Route::post('/document/update', [DocumentController::class, 'updateDocument'])->name('document.update');
    Route::post('/document/delete', [DocumentController::class, 'deleteDocument'])->name('document.delete');
    Route::get('/document/show/{doc_id}', [DocumentController::class, 'showDocument'])->name('document.show'); 
    Route::post('/document/acknowledge', [DocumentController::class, 'acknowledgeDocument'])->name('document.acknowledge');
   
    
    //Folders Route
    Route::get('/folders', [FolderController::class, 'index'])->name('folder.index');
    Route::post('/folder/create', [FolderController::class, 'createFolder'])->name('folder.store');
    Route::post('/folder/update', [FolderController::class, 'updateFolder'])->name('folder.update');
    Route::post('/folder/delete', [FolderController::class, 'deleteFolder'])->name('folder.delete');
    Route::get('/folder/show/{folder_id}', [FolderController::class, 'showFolder'])->name('folder.show');
   
  

    //roles 
    Route::resource('roles', RolePermissionController::class);

    // Route::get('/test', [CourseController::class, 'test'])->name('test.test');

    //Training Event Route
    Route::get('/training', [TrainingEventsController::class, 'index'])->name('training.index'); 
    Route::post('/training/create', [TrainingEventsController::class, 'createTrainingEvent'])->name('training.store'); 
    Route::get('/training/edit', [TrainingEventsController::class, 'getTrainingEvent'])->name('training.edit');
    Route::post('/training/update', [TrainingEventsController::class, 'updateTrainingEvent'])->name('training.update');
    Route::post('/training/delete', [TrainingEventsController::class, 'deleteTrainingEvent'])->name('training.delete');
    Route::post('/training/backToDeferredLesson', [TrainingEventsController::class, 'backToDeferredLesson'])->name('backToDeferredLesson.delete');
    // Route::get('/training/get_ou_groups_and_instructors/', [TrainingEventsController::class, 'getOrgGroupsAndInstructors'])->name('training.get_ou_groups_and_instructors');
    Route::get('/training/show/{event_id}', [TrainingEventsController::class, 'showTrainingEvent'])->name('training.show'); 
    Route::post('/training/store_grading', [TrainingEventsController::class, 'createGrading'])->name('training.store_grading'); 
    Route::post('/training/overall_assessment', [TrainingEventsController::class, 'storeOverallAssessment'])->name('training.overall_assessment');
    // Route::get('/grading', [TrainingEventsController::class, 'getStudentGrading'])->name('grading.list');
    Route::get('/training/grading-list/{event_id}', [TrainingEventsController::class, 'getStudentGrading'])->name('training.grading-list');
    Route::post('/grading/unlock/{event_id}', [TrainingEventsController::class, 'unlockEventGarding'])->name('grading.unlock'); 
    


    // Course Template
    Route::get('/course-template', [CourseTemplateController::class, 'index'])->name('course-template.index');
    Route::get('/course-template/create', [CourseTemplateController::class, 'createCourseTemplate'])->name('course-template.create');
    Route::post('/course-template/store', [CourseTemplateController::class, 'saveCourseTemplate'])->name('course-template.store');

    // Resources Routes
    Route::get('/resource', [ResourceController::class, 'resource_list'])->name('resource.index'); 
    Route::post('/resource/save', [ResourceController::class, 'save'])->name('save.index');
    Route::get('/resource/edit', [ResourceController::class, 'edit'])->name('edit.index');
    Route::post('/resourse/update', [ResourceController::class, 'update'])->name('update.index'); 
    Route::post('/resource/delete', [ResourceController::class, 'delete'])->name('delete.index');
    Route::post('/resource/getcourseResource', [ResourceController::class, 'getcourseResource'])->name('getcourseResource.index');
    Route::get('/resource/show/{resource_id}', [ResourceController::class, 'showResource'])->name('resource.show');

    // Booking Request
    Route::get('/booking/bookresource/{course_id}', [ResourceController::class, 'bookresource'])->name('bookresource.index');
    Route::post('/booking/store', [ResourceController::class, 'store'])->name('store.index');
    
    // Approval
    Route::get('/resource/approval', [ResourceController::class, 'resource_approval'])->name('resource.approval');
    Route::post('/resource/approve/request', [ResourceController::class, 'approve_request'])->name('approve_request.index');
    Route::post('/resource/reject/request', [ResourceController::class, 'reject_request'])->name('reject_request.index');

    // Route::get('/document/get_ou_folder/', [DocumentController::class, 'getOrgfolder'])->name('document.getOrgfolder');
    // Route::get('/folder/get_ou_folder/', [DocumentController::class, 'getOrgfolder'])->name('folder.getOrgfolder');
    Route::get('/folder/edit', [FolderController::class, 'getFolder'])->name('folder.edit');

    // Reporting section Routes
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
    Route::get('/reports/course/{hashedId}', [ReportsController::class, 'showCourse'])->name('reports.course'); 
    Route::post('/students/archive', [ReportsController::class, 'updateStudentArchiveStatus'])->name('students.archive.ajax'); 

    // Student specific reporting section Routes
    Route::get('/user-reporting', [ReportsController::class, 'getStudentReports'])->name('user.reporting');

    // Custom CBTA
     
    Route::get('/custom-cbta', [CbtaControlller::class, 'index'])->name('custom-cbta.show');
    Route::post('/custom-cbta-add', [CbtaControlller::class, 'save'])->name('custom-cbta.add');
    Route::post('/custom-cbta-edit', [CbtaControlller::class, 'edit'])->name('custom-cbta.edit');
    Route::post('/custom-cbta-update', [CbtaControlller::class, 'update'])->name('custom-cbta.update');
    Route::post('/custom-cbta-delete', [CbtaControlller::class, 'delete'])->name('custom-cbta.delete');
    
    

});

Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
Route::post('/settings', [SettingController::class, 'store'])->name('settings.store');

Route::post('/review_store', [TrainingEventsController::class, 'review_store'])->name('review.store');

Route::get('/archieveUser', [TrainingEventsController::class, 'archieveUser'])->name('archieveUser.index');

Route::get('/training/unarchieveUser', [TrainingEventsController::class, 'unarchieveUser'])->name('unarchieveUser.index');  

Route::post('/unarchive-user', [TrainingEventsController::class, 'unarchive'])->name('unarchive.index');

Route::post('/copy_course', [CourseController::class, 'copy_course'])->name('copy_course.index');


    
Route::get('/clear-cache', function() {
    Artisan::call('optimize:clear');
    return 'Application cache has been cleared';
});

Route::get('/migrate', function() {
    // Run migrations
    Artisan::call('migrate', ['--force' => true]); // '--force' to bypass confirmation

    return 'Migrations have been executed successfully.';
});

Route::get('/user_seeder', function() {
    // Specify the seeder class you want to run
    Artisan::call('db:seed', ['--class' => 'UserSeeder', '--force' => true]); // '--force' for production

    return 'UserSeeder has been executed successfully.';
});

Route::get('/role_Seeder', function() {
    // Specify the seeder class you want to run
    Artisan::call('db:seed', ['--class' => 'RoleSeeder', '--force' => true]); // '--force' for production
    return 'RoleSeeder has been executed successfully.';
});

Route::get('/link-storage', function () {
    Artisan::call('storage:link');
    return 'Storage linked successfully!';
});


