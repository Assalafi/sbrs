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

        $filename = 'admission_letter_' . str_replace('/', '_', $applicant->application_number) . '.pdf';
        return self::downloadForApplicant($applicant, $filename);
    }

    /**
     * Generate the official admission letter for either an applicant or the
     * student record created from that applicant. Keeping this in one place
     * prevents the two portals from producing different letters.
     */
    public static function downloadForApplicant($applicant, string $filename)
    {
        $applicant->loadMissing(['programme', 'subjectCombination', 'academicSession', 'student']);

        $logoDataUri = self::localImageDataUri(setting_image('main_logo'))
            ?: self::localImageDataUri(setting_image('logo'))
            ?: self::localImageDataUri(asset('assets/images/unimaid-logo.png'));

        // Embed the supplied letterhead locally: downloads must not depend on HTTP access.
        $letterheadDataUri = self::localImageDataUri('/assets/images/admission/sbrs-letterhead-header.png');
        $letterheadFooterDataUri = self::localImageDataUri('/assets/images/admission/sbrs-letterhead-footer.png');
        $signatureDataUri = self::localImageDataUri('/assets/images/admission/admission-signature.png');

        $pdf = Pdf::loadView('applicant.admission.letter-pdf', compact('applicant', 'logoDataUri', 'letterheadDataUri', 'letterheadFooterDataUri', 'signatureDataUri'))
            ->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    public function paymentConfirmation()
    {
        $applicant = Auth::guard('applicant')->user();

        if ($applicant->status !== 'admitted') {
            return redirect()->route('applicant.dashboard')
                ->with('error', 'You have not been admitted yet.');
        }

        return view('applicant.admission.payment-confirmation', compact('applicant'));
    }

    private static function localImageDataUri(?string $url): ?string
    {
        if (!$url) {
            return null;
        }

        $pathPart = parse_url($url, PHP_URL_PATH) ?: $url;
        $relative = ltrim((string) $pathPart, '/');
        if (str_starts_with($relative, 'storage/')) {
            $relative = substr($relative, strlen('storage/'));
        }

        foreach ([storage_path('app/public/' . $relative), public_path($relative)] as $path) {
            if (!is_file($path)) {
                continue;
            }

            $mime = @mime_content_type($path) ?: 'image/png';
            $contents = @file_get_contents($path);
            if ($contents !== false) {
                return 'data:' . $mime . ';base64,' . base64_encode($contents);
            }
        }

        return null;
    }
}
