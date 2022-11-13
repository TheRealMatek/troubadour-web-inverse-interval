<?php

use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\TeacherController;
use App\Models\Role;
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
Route::get('/', function () {
    return view('welcome');
});

Route::group([
    'prefix' => 'administracija',
], function () {
    Route::get('/', function () {
        return redirect()
            ->route('classrooms.index');
    });

    Route::get('terms', 'AuthController@terms')->name('terms');

    Route::middleware(['guest'])->group(function () {
        Route::get('login', 'AuthController@showLoginForm')->name('login.show');
        Route::post('login', 'AuthController@login')->name('login');

        Route::get('register', 'AuthController@showRegisterForm')->name('register.show');
        Route::post('register', 'AuthController@register')->name('register');

        Route::get('password/request', 'ForgotPasswordController@request')->name('password.request');
        Route::post('password/request', 'ForgotPasswordController@send')->name("password.send");
        Route::get('password/reset', 'AuthController@passwordResetShow')->name('password.change');
        Route::post('password/reset', 'AuthController@passwordUpdate')->name("password.reset");
    });
    Route::get('/email/verify/{id}/{hash}', 'AuthController@verifyEmail')->middleware(['signed'])->name('verification.verify');
    Route::get('/roles/invalid', 'RoleController@invalid')->name('roles.invalid')->withoutMiddleware('teacher');
    Route::post('logout', 'AuthController@logout')->name('logout')->middleware('auth');

    Route::get('teacher/', [TeacherController::class, 'index'])->name('teacher.index')->middleware(['auth', 'role:' . Role::TEACHER]);

    // NEW ROUTES
    Route::group([
        'prefix' => 'super-admin',
        'as' => 'super-admin.',
        'middleware' => ['auth', 'role:' . Role::SUPER_ADMIN],
    ], function () {
        Route::get('/', [SuperAdminController::class, 'index'])->name('index');
        Route::get('settings', [SuperAdminController::class, 'settings'])->name('settings');

        Route::resource('game-types', 'GameType\GameTypeController');
        Route::get('game-types/{gameType}/restore', 'GameType\GameTypeController@restore')->name('game-types.restore');
        Route::resource('game-types.difficulties', 'GameType\DifficultyController');
        Route::get('game-types/{gameType}/difficulties/{difficulty}/restore', 'GameType\DifficultyController@restore')->name('game-types.difficulties.restore');
    });
    Route::resource('schools', 'School\SchoolController');
    Route::resource('users', 'User\UserController');
    Route::get('users/{user}/roles/edit', 'User\UserController@editRoles')->name('users.roles.edit');
    Route::put('users/{user}/roles', 'User\UserController@updateRoles')->name('users.roles.update');

    Route::resource('classrooms', 'ClassroomController');
    Route::resource('classrooms.users', 'Classroom\UserController')->only('index', 'create', 'store');
    Route::resource('classrooms.homeworks', 'Classroom\HomeworkController');
    Route::get('exercises/{exercise}/recreate', 'Classroom\HomeworkController@recreateExercise')->name('exercises.recreate');


});



