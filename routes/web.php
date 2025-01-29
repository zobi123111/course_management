<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\OrganizationController;

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
    Route::get('/organization', [OrganizationController::class, 'index'])->name('org_units.index');
    Route::post('/save-org-unit', [OrganizationController::class, 'saveOrgUnit'])->name('org-unit.store');
    Route::get('/edit-org-unit', [OrganizationController::class, 'getOrgUnit'])->name('org-unit.edit');
    Route::post('/update-org-unit', [OrganizationController::class, 'updateOrgUnit'])->name('org-unit.update');
    Route::post('/org-unit-delete', [OrganizationController::class, 'deleteOrgUnit'])->name('org-unit.delete');
    });

// Courses 

Route::get('/courses', [CourseController::class, 'index'])->name('course.index');
Route::get('/create/course', [CourseController::class, 'create_course'])->name('create_course.index');
Route::post('/store/course', [CourseController::class, 'store_course'])->name('store_course.index');



// Route::post('/org/delete', [OrganizationController::class, 'destroy'])->name('org.destroy');


