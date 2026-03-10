<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\AcademicSession;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegisterForm()
    {
        $session = AcademicSession::current();
        $programmes = Programme::where('is_active', true)->get();
        return view('auth.applicant-register', compact('session', 'programmes'));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'surname' => 'required|string|max:100',
            'first_name' => 'required|string|max:100',
            'other_names' => 'nullable|string|max:100',
            'email' => 'required|email|unique:applicants,email',
            'phone' => 'required|string|max:20',
            'programme_type' => 'required|in:IJMB,Remedial',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $session = AcademicSession::current();
        if (!$session) {
            return back()->with('error', 'No active academic session. Please contact admin.')->withInput();
        }

        $applicant = Applicant::create([
            'application_number' => Applicant::generateApplicationNumber(),
            'surname' => strtoupper($validated['surname']),
            'first_name' => ucfirst($validated['first_name']),
            'other_names' => $validated['other_names'] ? ucfirst($validated['other_names']) : null,
            'email' => strtolower($validated['email']),
            'phone' => $validated['phone'],
            'programme_type' => $validated['programme_type'],
            'academic_session_id' => $session->id,
            'password' => $validated['password'],
            'status' => 'registered',
        ]);

        Auth::guard('applicant')->login($applicant);

        return redirect()->route('applicant.dashboard')
            ->with('success', 'Account created successfully! Your Application Number is: ' . $applicant->application_number);
    }

    public function showLoginForm()
    {
        return view('auth.applicant-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('applicant')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $applicant = Auth::guard('applicant')->user();
            if (!$applicant->is_active) {
                Auth::guard('applicant')->logout();
                return back()->with('error', 'Your account has been deactivated.');
            }

            return redirect()->intended(route('applicant.dashboard'));
        }

        return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('applicant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('applicant.login');
    }
}
