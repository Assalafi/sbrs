<?php

namespace App\Http\Controllers\Applicant;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Programme;
use App\Models\SubjectCombination;
use App\Models\IjmbApplication;
use App\Models\RemedialApplication;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ApplicationFormController extends Controller
{
    public function edit()
    {
        $applicant = Auth::guard('applicant')->user();

        if (!$applicant->hasPaidApplicationFee()) {
            return redirect()->route('applicant.payment.application-fee')
                ->with('error', 'Please pay the application fee first.');
        }

        $programmes = Programme::where('type', $applicant->programme_type)
            ->where('is_active', true)->get();

        $openTab = request('tab', 'personalInfo');

        if ($applicant->programme_type === 'IJMB') {
            $application = $applicant->ijmbApplication ?? IjmbApplication::create([
                'applicant_id' => $applicant->id,
                'academic_session_id' => $applicant->academic_session_id,
            ]);
            $application->load(['schoolsAttended', 'olevelResults.subjects', 'referees']);
            $sections = $applicant->getSectionCompletion();
            return view('applicant.application.ijmb', compact('applicant', 'application', 'programmes', 'sections', 'openTab'));
        } else {
            $application = $applicant->remedialApplication ?? RemedialApplication::create([
                'applicant_id' => $applicant->id,
                'academic_session_id' => $applicant->academic_session_id,
            ]);
            $application->load(['institutions', 'examResults', 'employmentRecords', 'referees']);
            $sections = $applicant->getSectionCompletion();
            return view('applicant.application.remedial', compact('applicant', 'application', 'programmes', 'sections', 'openTab'));
        }
    }

    public function updatePersonalInfo(Request $request)
    {
        $applicant = Auth::guard('applicant')->user();

        $request->validate([
            'programme_id' => 'required|exists:programmes,id',
            'subject_combination_id' => 'nullable|exists:subject_combinations,id',
            'passport_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:500',
            'indigene_cert' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:2048',
            'primary_cert' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:2048',
            'ssce_cert' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:2048',
            'birth_cert' => 'nullable|file|mimes:jpeg,jpg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('passport_photo')) {
            if ($applicant->passport_photo) {
                Storage::disk('public')->delete($applicant->passport_photo);
            }
            $path = $request->file('passport_photo')->store('passport_photos', 'public');
            $applicant->update(['passport_photo' => $path]);
        }

        $docFields = ['indigene_cert', 'primary_cert', 'ssce_cert', 'birth_cert'];
        foreach ($docFields as $field) {
            if ($request->hasFile($field)) {
                if ($applicant->$field) {
                    Storage::disk('public')->delete($applicant->$field);
                }
                $applicant->$field = $request->file($field)->store('documents', 'public');
            }
        }

        $applicant->programme_id = $request->programme_id;
        $applicant->subject_combination_id = $request->subject_combination_id;
        if ($applicant->status === 'registered') {
            $applicant->status = 'form_filling';
        }
        $applicant->save();

        if ($applicant->programme_type === 'IJMB') {
            $applicant->ijmbApplication->update($request->only([
                'date_of_birth', 'gender', 'marital_status', 'nationality',
                'state_of_origin', 'lga', 'permanent_address',
                'extracurricular_activities', 'disability_or_sickness',
                'nok_name', 'nok_phone', 'nok_relationship', 'nok_address',
            ]));
        } else {
            $applicant->remedialApplication->update($request->only([
                'date_of_birth', 'gender', 'marital_status',
                'state_of_origin', 'lga', 'correspondence_address',
                'guardian_name', 'guardian_address', 'guardian_phone', 'guardian_email',
                'permanent_address', 'permanent_phone', 'permanent_email',
                'primary_school_name', 'primary_school_from', 'primary_school_to',
            ]));
        }

        $nextTab = $applicant->programme_type === 'IJMB' ? 'schoolsSection' : 'institutionsSection';
        return redirect()->route('applicant.application.edit', ['tab' => $nextTab])->with('success', 'Personal information saved.');
    }

    public function updateSchools(Request $request)
    {
        $applicant = Auth::guard('applicant')->user();

        if ($applicant->programme_type === 'IJMB') {
            $application = $applicant->ijmbApplication;
            $application->schoolsAttended()->delete();

            if ($request->has('schools')) {
                foreach ($request->schools as $school) {
                    if (!empty($school['school_name'])) {
                        $application->schoolsAttended()->create($school);
                    }
                }
            }
        } else {
            $application = $applicant->remedialApplication;
            $application->institutions()->delete();

            if ($request->has('institutions')) {
                foreach ($request->institutions as $inst) {
                    if (!empty($inst['institution_name'])) {
                        $application->institutions()->create($inst);
                    }
                }
            }
        }

        return redirect()->route('applicant.application.edit', ['tab' => 'resultsSection'])->with('success', 'Education history saved.');
    }

    public function updateResults(Request $request)
    {
        $applicant = Auth::guard('applicant')->user();

        if ($applicant->programme_type === 'IJMB') {
            $application = $applicant->ijmbApplication;
            $application->olevelResults()->each(function ($result) {
                $result->subjects()->delete();
                $result->delete();
            });

            if ($request->has('olevel_results')) {
                foreach ($request->olevel_results as $resultData) {
                    if (empty($resultData['exam_number'])) continue;
                    $olevel = $application->olevelResults()->create([
                        'exam_type' => $resultData['exam_type'],
                        'examination_type_other' => $resultData['examination_type_other'] ?? null,
                        'exam_number' => $resultData['exam_number'],
                        'exam_year' => $resultData['exam_year'],
                        'exam_centre' => $resultData['exam_centre'] ?? null,
                    ]);
                    if (isset($resultData['subjects'])) {
                        foreach ($resultData['subjects'] as $subject) {
                            if (!empty($subject['subject'])) {
                                $olevel->subjects()->create($subject);
                            }
                        }
                    }
                }
            }
        } else {
            $application = $applicant->remedialApplication;
            $application->examResults()->delete();

            if ($request->has('exam_results')) {
                foreach ($request->exam_results as $result) {
                    if (!empty($result['subject'])) {
                        $application->examResults()->create($result);
                    }
                }
            }

            $application->update($request->only(['exam_date', 'exam_centre', 'exam_number']));
        }

        return redirect()->route('applicant.application.edit', ['tab' => 'sponsorSection'])->with('success', 'Examination results saved.');
    }

    public function updateSponsorship(Request $request)
    {
        $applicant = Auth::guard('applicant')->user();

        if ($applicant->programme_type === 'IJMB') {
            $applicant->ijmbApplication->update($request->only([
                'sponsor_type', 'sponsor_name', 'sponsor_address',
            ]));
        } else {
            $applicant->remedialApplication->update($request->only([
                'sponsor_type', 'sponsor_name', 'sponsor_address',
                'games', 'hobbies', 'other_activities', 'positions_held',
            ]));

            $applicant->remedialApplication->employmentRecords()->delete();
            if ($request->has('employment')) {
                foreach ($request->employment as $emp) {
                    if (!empty($emp['employer'])) {
                        $applicant->remedialApplication->employmentRecords()->create($emp);
                    }
                }
            }
        }

        return redirect()->route('applicant.application.edit', ['tab' => 'refereesSection'])->with('success', 'Sponsorship info saved.');
    }

    public function updateReferees(Request $request)
    {
        $applicant = Auth::guard('applicant')->user();

        if ($applicant->programme_type === 'IJMB') {
            $application = $applicant->ijmbApplication;
            $application->referees()->delete();
            if ($request->has('referees')) {
                foreach ($request->referees as $ref) {
                    if (!empty($ref['name'])) {
                        $application->referees()->create($ref);
                    }
                }
            }
        } else {
            $application = $applicant->remedialApplication;
            $application->referees()->delete();
            if ($request->has('referees')) {
                foreach ($request->referees as $ref) {
                    if (!empty($ref['name'])) {
                        $application->referees()->create($ref);
                    }
                }
            }
        }

        return redirect()->route('applicant.application.edit', ['tab' => 'declarationSection'])->with('success', 'Referees saved.');
    }

    public function submit(Request $request)
    {
        $applicant = Auth::guard('applicant')->user();

        $request->validate([
            'declaration_confirmed' => 'required|accepted',
            'declaration_name' => 'required|string',
        ]);

        // Comprehensive validation: all sections must be complete
        $sections = $applicant->getSectionCompletion();
        $missing = [];
        if (!$sections['personal']) $missing[] = 'Personal Information (including all document uploads & passport photo)';
        if (!$sections['schools']) $missing[] = 'Schools / Institutions Attended';
        if (!$sections['results']) $missing[] = "O'Level / Examination Results";
        if (!$sections['sponsorship']) $missing[] = 'Sponsorship';
        if (!$sections['referees']) $missing[] = 'Referees (3 required)';

        if (!empty($missing)) {
            return back()->with('error', 'Please complete the following sections before submitting: ' . implode(', ', $missing) . '.');
        }

        $application = $applicant->programme_type === 'IJMB'
            ? $applicant->ijmbApplication
            : $applicant->remedialApplication;

        $application->update([
            'declaration_confirmed' => true,
            'declaration_name' => $request->declaration_name,
            'declaration_date' => now(),
            'status' => 'submitted',
        ]);

        $applicant->update(['status' => 'submitted']);

        return redirect()->route('applicant.dashboard')
            ->with('success', 'Application submitted successfully! Please wait for review.');
    }

    public function getCombinations(Programme $programme)
    {
        return response()->json(
            $programme->subjectCombinations()->where('is_active', true)->get()
        );
    }

    public function getLgas(string $state)
    {
        $lgas = config('lgas.' . $state, []);
        return response()->json($lgas);
    }
}
