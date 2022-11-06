<?php

namespace App\Http\Controllers;

use App\Mail\SuccessfulRegistrationMail;
use App\Models\Role;
use App\Models\RoleUser;
use App\Models\School;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function verifyEmail(EmailVerificationRequest $request) {
        $request->fulfill();

        return redirect()->route('login')->with('status', __('messages.email_verified'));
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($data)) {
            return redirect()->route('classrooms.index');
        } else {
            return redirect()->route('login')->with('status', __('messages.login_failed'));
        }
    }

    public function showRegisterForm()
    {
        $schools = School::all();

        return view('auth.register', compact('schools'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'school_id' => 'required|exists:schools,id',
            'password' => 'required|confirmed|min:6'
        ]);
        $data['password'] = Hash::make($data['password']);
        $user = User::query()->create($data);

        $user->assignRole(Role::TEACHER);
        $user->sendEmailVerificationNotification();

        return Redirect::to("login");
    }

    public function terms()
    {
        return view('terms');
    }

    public function logout()
    {
        Session::flush();
        Auth::logout();
        return redirect()->route('login.show');
    }

    public function passwordResetShow(Request $request)
    {
        $request->validate([
            'token' => 'required',
        ]);
        return view('auth.reset-password', ['token' => $request->get('token')]);
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? view('auth.reset-success')
            : back()->withErrors(['email' => __($status)]);
    }
}
