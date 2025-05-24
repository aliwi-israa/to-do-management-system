<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Admin\Users;
use App\Livewire\TaskManager;

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

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/users', Users::class)->name('users');
});

// tasks route for both admin and regular users
Route::middleware(['auth'])->get('/tasks', TaskManager::class)->name('tasks');

require __DIR__.'/auth.php';
