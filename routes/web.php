<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\CourseLessonController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\LessonController;

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

//change passwword
// Route::get('change-password', [LoginController::class, 'showChangePasswordForm'])->name('changePassword');
// Route::post('change-password', [LoginController::class, 'changePassword'])->name('update-password');

Route::group(['middleware' => ['auth']], function () {
    Route::get('change-password', [LoginController::class, 'showChangePasswordForm'])->name('change-password');
    Route::post('change-password', [LoginController::class, 'changePassword'])->name('update-password');
    Route::get('/users/profile', [UserController::class, 'profile'])->name('user.profile');
    Route::post('/users/profile/update', [UserController::class, 'profileUpdate'])->name('profile.update');
});

Route::middleware(['auth', 'role.permission'])->group(function () {
    //Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    //Users Route
    Route::get('/users', [UserController::class, 'users'])->name('user.index');
    // Route::get('/users/profile', [UserController::class, 'profile'])->name('user.profile');
    // Route::post('/users/profile/update', [UserController::class, 'profileUpdate'])->name('profile.update');
    Route::post('/users/save', [UserController::class, 'save_user'])->name('user.store');
    Route::post('/users/edit', [UserController::class, 'getUserById'])->name('user.get');
    Route::post('/users/update', [UserController::class, 'update'])->name('user.update');
    Route::post('/users/delete', [UserController::class, 'destroy'])->name('user.destroy');


    Route::post('/users/switch_role', [UserController::class, 'switchRole'])->name('user.switch_role');


    
    //Organization Unit
    Route::get('/orgunit', [OrganizationController::class, 'index'])->name('orgunit.index');
    Route::post('/orgunit/save', [OrganizationController::class, 'saveOrgUnit'])->name('orgunit.store');
    Route::get('/orgunit/edit', [OrganizationController::class, 'getOrgUnit'])->name('orgunit.edit');
    Route::post('/orgunit/update', [OrganizationController::class, 'updateOrgUnit'])->name('orgunit.update');
    Route::post('/orgunit/delete', [OrganizationController::class, 'deleteOrgUnit'])->name('orgunit.delete');
    Route::get('/orgunit/user_list', [OrganizationController::class, 'showOrgUsers'])->name('orgunit.user_list');
    
    //Courses 
    Route::get('/courses', [CourseController::class, 'index'])->name('course.index');
    Route::post('/course/create', [CourseController::class, 'createCourse'])->name('course.store');
    Route::get('/course/edit', [CourseController::class, 'getCourse'])->name('course.edit');
    Route::post('/course/update', [CourseController::class, 'updateCourse'])->name('course.update');
    Route::post('/course/delete', [CourseController::class, 'deleteCourse'])->name('course.delete');
    Route::get('/course/show/{course_id}', [LessonController::class, 'showCourse'])->name('course.show');

    //Lesson 
    Route::post('/lesson/create', [LessonController::class, 'createLesson'])->name('lesson.store');
    Route::get('/lesson/edit', [LessonController::class, 'getLesson'])->name('lesson.edit');
    Route::get('/lesson/{id}', [LessonController::class, 'showLesson'])->name('lesson.show');
    Route::post('/lesson/update', [LessonController::class, 'updateLesson'])->name('lesson.update');
    Route::post('/lesson/delete', [LessonController::class, 'deleteLesson'])->name('lesson.delete');

    //Sub-Lesson 
    Route::post('/sub-lesson/create', [LessonController::class, 'createSubLesson'])->name('sub-lesson.store');
    Route::get('/sub-lesson/edit', [LessonController::class, 'getSubLesson'])->name('sub-lesson.edit');
    Route::get('/sub-lesson/{id}', [LessonController::class, 'showSubLesson'])->name('sub-lesson.show');
    Route::post('/sub-lesson/update', [LessonController::class, 'updateSubLesson'])->name('sub-lesson.update');
    Route::post('/sub-lesson/delete', [LessonController::class, 'deleteSubLesson'])->name('sub-lesson.delete');

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
    Route::get('/folder/edit', [FolderController::class, 'getFolder'])->name('folder.edit');
    Route::post('/folder/update', [FolderController::class, 'updateFolder'])->name('folder.update');
    Route::post('/folder/delete', [FolderController::class, 'deleteFolder'])->name('folder.delete');
    
    Route::get('/folder/show/{folder_id}', [FolderController::class, 'showFolder'])->name('folder.show');

    //roles 
    Route::resource('roles', RolePermissionController::class);

    // Route::get('/test', [CourseController::class, 'test'])->name('test.test');

});



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
