<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

class AdmissionController extends Controller
{
    public function letter()
    {
        $applicant = Auth::guard('applicant')->user();
        $applicant->load(['programme', 'subjectCombination', 'academicSession', 'student']);

        if ($applicant->status !== 'admitted') {
            return redirect()->route('applicant.dashboard')
                ->with('error', 'You have not been admitted yet.');
        }

        return view('applicant.admission.letter', compact('applicant'));
    }

    public function downloadLetter()
    {
        $applicant = Auth::guard('applicant')->user();
        $applicant->load(['programme', 'subjectCombination', 'academicSession', 'student']);

        if ($applicant->status !== 'admitted') {
            return redirect()->route('applicant.dashboard')
                ->with('error', 'You have not been admitted yet.');
        }

        $pdf = Pdf::loadView('applicant.admission.letter-pdf', compact('applicant'));
        $filename = 'admission_letter_' . str_replace('/', '_', $applicant->application_number) . '.pdf';
        return $pdf->download($filename);
    }
}
