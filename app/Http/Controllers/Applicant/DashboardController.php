<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        $applicant = Auth::guard('applicant')->user();
        $applicant->load(['programme', 'subjectCombination', 'academicSession', 'payments', 'student']);

        $applicationForm = $applicant->programme_type === 'IJMB'
            ? $applicant->ijmbApplication
            : $applicant->remedialApplication;

        return view('applicant.dashboard.index', compact('applicant', 'applicationForm'));
    }

    public function showPasswordForm()
    {
        return view('applicant.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $applicant = Auth::guard('applicant')->user();

        if (!Hash::check($request->current_password, $applicant->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $applicant->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }
}
