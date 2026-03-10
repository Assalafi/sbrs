<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('student.login');
    }
}
