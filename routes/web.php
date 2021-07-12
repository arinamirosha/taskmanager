<?php

use App\Models\Task;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();

Route::get('/', [App\Http\Controllers\WelcomeController::class, 'welcome'])->name('welcome');

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    //USERS

    Route::get('/users', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    Route::get('/users/profile', [App\Http\Controllers\UserController::class, 'profile'])->name('users.profile');
    Route::post('/users', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');

    // PROJECTS

    Route::get('/projects', [App\Http\Controllers\ProjectsController::class, 'index'])->name('projects.index');
    Route::post('/projects', [App\Http\Controllers\ProjectsController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [App\Http\Controllers\ProjectsController::class, 'show'])->name('projects.show');
    Route::post('/projects/{project}/restore', [App\Http\Controllers\ProjectsController::class, 'restore'])->name('projects.restore');
    Route::post('/projects/{project}', [App\Http\Controllers\ProjectsController::class, 'update'])->name('projects.update')->middleware('can:update,project');
    Route::post('/projects/{project}/favorite', [App\Http\Controllers\ProjectsController::class, 'favorite'])->name('projects.favorite')->middleware('can:view,project');
    Route::post('/projects/{project}/share', [App\Http\Controllers\ProjectsController::class, 'share'])->name('projects.share')->middleware('can:update,project');
    Route::delete('/projects/{project}/unshare', [App\Http\Controllers\ProjectsController::class, 'unshare'])->name('projects.unshare')->middleware('can:delete,project');
    Route::post('/projects/{project}/accepted', [App\Http\Controllers\ProjectsController::class, 'accepted'])->name('projects.accepted');
    Route::delete('/projects/{project}/archive', [App\Http\Controllers\ProjectsController::class, 'archive'])->name('projects.archive')->middleware('can:delete,project');
    Route::delete('/projects/{project}/force', [App\Http\Controllers\ProjectsController::class, 'destroyForce'])->name('projects.destroy-force');

    // TASKS

    Route::get('/tasks', [App\Http\Controllers\TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
//    Route::get('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{task}/restore', [App\Http\Controllers\TaskController::class, 'restore'])->name('tasks.restore');
    Route::post('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update')->middleware('can:update,task');
    Route::delete('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy')->middleware('can:delete,task');
    Route::delete('/tasks/archive/all', [App\Http\Controllers\TaskController::class, 'archive'])->name('tasks.archive');
    Route::delete('/tasks/{task}/force', [App\Http\Controllers\TaskController::class, 'destroyForce'])->name('tasks.destroy-force');

    // COMMENTS

    Route::get('/comments/{all_task}', [App\Http\Controllers\CommentController::class, 'index'])->name('comments.index');
    Route::bind('all_task', function ($id) { return Task::withTrashed()->findOrFail($id); });

    Route::post('/comments/{task}', [App\Http\Controllers\CommentController::class, 'store'])->name('comments.store');
    Route::put('/comments/{comment}', [App\Http\Controllers\CommentController::class, 'update'])->name('comments.update')->middleware('can:update,comment');
    Route::delete('/comments/{comment}', [App\Http\Controllers\CommentController::class, 'destroy'])->name('comments.destroy')->middleware('can:delete,comment');


    // HISTORY
    Route::get('/history', [App\Http\Controllers\HistoryController::class, 'index'])->name('history.index');
});
