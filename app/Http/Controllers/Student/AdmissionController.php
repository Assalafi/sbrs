<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Applicant\AdmissionController as ApplicantAdmissionController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AdmissionController extends Controller
{
    public function letter()
    {
        $student = Auth::guard('student')->user();
        $student->load(['applicant.programme', 'applicant.subjectCombination', 'applicant.academicSession', 'applicant.student']);

        $applicant = $student->applicant;
        if (!$applicant || $applicant->status !== 'admitted') {
            return redirect()->route('student.dashboard')
                ->with('error', 'Your admission letter is not available yet.');
        }

        return view('student.admission.letter', compact('student', 'applicant'));
    }

    public function downloadLetter()
    {
        $student = Auth::guard('student')->user();
        $student->load(['applicant.programme', 'applicant.subjectCombination', 'applicant.academicSession', 'applicant.student']);

        $applicant = $student->applicant;
        if (!$applicant || $applicant->status !== 'admitted') {
            return redirect()->route('student.dashboard')
                ->with('error', 'Your admission letter is not available yet.');
        }

        $filename = 'admission_letter_' . str_replace('/', '_', $student->registration_number ?: $applicant->application_number) . '.pdf';
        return ApplicantAdmissionController::downloadForApplicant($applicant, $filename);
    }
}
