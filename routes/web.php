<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\GroupController;

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


Route::group(['middleware' => ['auth']], function () {
    // Dashboard Route
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    //Users Route
    Route::get('/users', [UserController::class, 'users'])->name('users.index');
    Route::post('/save_user', [UserController::class, 'save_user'])->name('save_user.index');
    Route::post('/users/edit', [UserController::class, 'getUserById'])->name('user.get');
    Route::post('/users/update', [UserController::class, 'update'])->name('user.update');
    Route::post('/users/delete', [UserController::class, 'destroy'])->name('user.destroy');
    
    //Organization Unit
    Route::get('/organization', [OrganizationController::class, 'index'])->name('orgunit.index');
    Route::post('/orgunit/save', [OrganizationController::class, 'saveOrgUnit'])->name('orgunit.store');
    Route::get('/orgunit/edit', [OrganizationController::class, 'getOrgUnit'])->name('orgunit.edit');
    Route::post('/orgunit/update', [OrganizationController::class, 'updateOrgUnit'])->name('orgunit.update');
    Route::post('/orgunit/delete', [OrganizationController::class, 'deleteOrgUnit'])->name('orgunit.delete');
    
    // Courses 
    Route::get('/courses', [CourseController::class, 'index'])->name('course.index');
    Route::post('/course/create', [CourseController::class, 'createCourse'])->name('course.store');
    Route::get('/course/edit', [CourseController::class, 'getCourse'])->name('course.edit');
    Route::post('/course/update', [CourseController::class, 'updateCourse'])->name('course.update');
    Route::post('/course/delete', [CourseController::class, 'deleteCourse'])->name('course.delete');
    Route::get('/course/show', [CourseController::class, 'showCourse'])->name('course.show');

    Route::post('/lesson/create', [CourseController::class, 'showLesson'])->name('lesson.store');

    Route::get('/groups', [GroupController::class, 'index'])->name('groups.index'); 
    Route::post('/group/create', [GroupController::class, 'store'])->name('groups.create'); 
    Route::get('/group/edit', [GroupController::class, 'show'])->name('groups.edit'); 
    Route::post('/group/update', [GroupController::class, 'update'])->name('groups.update'); 
    Route::post('/group/delete', [GroupController::class, 'destroy'])->name('groups.delete'); 
    Route::get('/group/show', [GroupController::class, 'show'])->name('groups.show');

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