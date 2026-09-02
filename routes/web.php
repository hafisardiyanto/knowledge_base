<?php

use Illuminate\Support\Facades\Route;

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

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\KbController;
use App\Http\Controllers\SettingController;

Route::get('login', [AuthController::class, 'showLogin'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('projects', ProjectController::class);

    Route::get('knowledge-base', [KbController::class, 'index'])->name('kb.index');
    Route::get('knowledge-base/{project}/editor', [KbController::class, 'editor'])->name('kb.editor');
    Route::post('knowledge-base/{project}/save-category', [KbController::class, 'saveCategory'])->name('kb.category.save');
    Route::delete('knowledge-base/{project}/delete-category/{category}', [KbController::class, 'deleteCategory'])->name('kb.category.delete');

    Route::post('knowledge-base/{project}/save-article-info', [KbController::class, 'saveArticleInfo'])->name('kb.article.saveInfo');
    Route::delete('knowledge-base/{project}/delete-article/{article}', [KbController::class, 'deleteArticle'])->name('kb.article.delete');

    Route::post('knowledge-base/{project}/save', [KbController::class, 'save'])->name('kb.save');
    Route::post('knowledge-base/{project}/generate', [KbController::class, 'generateHtml'])->name('kb.generate');

    Route::get('settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
});
