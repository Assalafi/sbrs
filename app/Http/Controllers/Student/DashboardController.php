<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $student->load(['programme', 'subjectCombination', 'academicSession', 'payments', 'courseRegistrations.course', 'results.course']);

        return view('student.dashboard.index', compact('student'));
    }

    public function showPasswordForm()
    {
        return view('student.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $student = Auth::guard('student')->user();

        if (!Hash::check($request->current_password, $student->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $student->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }
}
