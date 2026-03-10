<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Student;
use App\Models\Payment;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        $currentSession = AcademicSession::current();

        $stats = [
            'total_applicants' => Applicant::when($currentSession, fn($q) => $q->where('academic_session_id', $currentSession->id))->count(),
            'pending_applications' => Applicant::where('status', 'submitted')->count(),
            'approved_applications' => Applicant::where('status', 'approved')->count(),
            'total_students' => Student::when($currentSession, fn($q) => $q->where('academic_session_id', $currentSession->id))->count(),
            'ijmb_applicants' => Applicant::where('programme_type', 'IJMB')->when($currentSession, fn($q) => $q->where('academic_session_id', $currentSession->id))->count(),
            'remedial_applicants' => Applicant::where('programme_type', 'Remedial')->when($currentSession, fn($q) => $q->where('academic_session_id', $currentSession->id))->count(),
            'total_payments' => Payment::where('status', 'successful')->sum('amount'),
            'pending_screening' => Student::where('screening_status', 'pending')->count(),
        ];

        $recentApplications = Applicant::where('status', 'submitted')
            ->latest()
            ->take(10)
            ->get();

        $recentPayments = Payment::where('status', 'successful')
            ->with('payable')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard.index', compact('stats', 'recentApplications', 'recentPayments', 'currentSession'));
    }

    public function showPasswordForm()
    {
        return view('admin.dashboard.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }
}
