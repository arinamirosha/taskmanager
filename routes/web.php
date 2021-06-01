<?php

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

    Route::get('/users', [App\Http\Controllers\UserController::class, 'show'])->name('users.show');
    Route::post('/users', [App\Http\Controllers\UserController::class, 'update'])->name('users.update');

    Route::get('/projects', [App\Http\Controllers\ProjectsController::class, 'index'])->name('projects.index');
    Route::post('/projects', [App\Http\Controllers\ProjectsController::class, 'store'])->name('projects.store');
    Route::get('/projects/{project}', [App\Http\Controllers\ProjectsController::class, 'show'])->name('projects.show')->middleware('can:view,project');
    Route::post('/projects/{project}', [App\Http\Controllers\ProjectsController::class, 'update'])->name('projects.update')->middleware('can:update,project');
    Route::delete('/projects/{project}/archive', [App\Http\Controllers\ProjectsController::class, 'archive'])->name('projects.archive')->middleware('can:delete,project');
    Route::delete('/projects/{project}/force', [App\Http\Controllers\ProjectsController::class, 'destroyForce'])->name('projects.destroy-force')->middleware('can:forceDelete,project');

    Route::get('/tasks', [App\Http\Controllers\TaskController::class, 'index'])->name('tasks.index');
    Route::post('/tasks', [App\Http\Controllers\TaskController::class, 'store'])->name('tasks.store');
//    Route::get('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'update'])->name('tasks.update');
    Route::delete('/tasks/{task}', [App\Http\Controllers\TaskController::class, 'destroy'])->name('tasks.destroy')->middleware('can:delete,task');
    Route::delete('/tasks/archive/all', [App\Http\Controllers\TaskController::class, 'archive'])->name('tasks.archive')->middleware('can:update,task');
    Route::delete('/tasks/{task}/force', [App\Http\Controllers\TaskController::class, 'destroyForce'])->name('tasks.destroy-force')->middleware('can:forceDelete,task');
});
