<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.student-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'registration_number' => 'required|string',
            'password' => 'required',
        ]);

        $student = \App\Models\Student::where('registration_number', $credentials['registration_number'])->first();

        if ($student && \Illuminate\Support\Facades\Hash::check($credentials['password'], $student->password)) {
            if (!$student->is_active) {
                return back()->with('error', 'Your account has been deactivated.');
            }
            Auth::guard('student')->login($student, $request->boolean('remember'));
            $request->session()->regenerate();
            return redirect()->intended(route('student.dashboard'));
        }

        return back()->withErrors(['registration_number' => 'Invalid credentials.'])->onlyInput('registration_number');
    }

    public function showForgotForm()
    {
        return view('auth.student-forgot');
    }

    public function verifyIdentity(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        $student = Student::where('email', $request->email)
            ->where('phone', $request->phone)
            ->first();

        if (!$student) {
            return back()->withErrors(['email' => 'No account found with that email and phone combination.'])->onlyInput('email', 'phone');
        }

        $request->session()->put('student_reset_id', $student->id);

        return redirect()->route('student.forgot.reset');
    }

    public function showResetForm(Request $request)
    {
        if (!$request->session()->has('student_reset_id')) {
            return redirect()->route('student.forgot')->with('error', 'Please verify your identity first.');
        }

        $student = Student::find($request->session()->get('student_reset_id'));
        if (!$student) {
            return redirect()->route('student.forgot')->with('error', 'Invalid session. Please try again.');
        }

        return view('auth.student-reset', compact('student'));
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (!$request->session()->has('student_reset_id')) {
            return redirect()->route('student.forgot')->with('error', 'Session expired. Please verify again.');
        }

        $student = Student::findOrFail($request->session()->get('student_reset_id'));
        $student->update(['password' => Hash::make($request->password)]);

        $request->session()->forget('student_reset_id');

        return redirect()->route('student.login')->with('success', 'Password reset successfully! You can now log in with your new password.');
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('student.login');
    }
}
