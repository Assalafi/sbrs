<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\AcademicSession;
use App\Models\Programme;
use App\Models\SubjectCombination;
use App\Models\Student;
use Illuminate\Http\Request;

class ApplicationController extends Controller
{
    public function index(Request $request)
    {
        $query = Applicant::with(['programme', 'academicSession'])
            ->whereIn('status', ['submitted', 'under_review', 'approved', 'rejected']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('programme_type')) {
            $query->where('programme_type', $request->programme_type);
        }
        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->academic_session_id);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('surname', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('application_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $applicants = $query->orderBy('created_at', 'desc')->paginate(25);
        $sessions = AcademicSession::orderBy('name', 'desc')->get();

        return view('admin.applications.index', compact('applicants', 'sessions'));
    }

    public function show(Applicant $application)
    {
        $application->load(['programme', 'subjectCombination', 'academicSession', 'ijmbApplication', 'remedialApplication', 'payments']);

        if ($application->programme_type === 'IJMB' && $application->ijmbApplication) {
            $application->ijmbApplication->load(['schoolsAttended', 'olevelResults.subjects', 'referees']);
        } elseif ($application->programme_type === 'Remedial' && $application->remedialApplication) {
            $application->remedialApplication->load(['institutions', 'examResults', 'employmentRecords', 'referees']);
        }

        return view('admin.applications.show', compact('application'));
    }

    /**
     * Show the edit form for an applicant's basic record and status.
     */
    public function edit(Applicant $application)
    {
        $application->load(['programme', 'subjectCombination', 'academicSession', 'student']);

        $sessions = AcademicSession::orderBy('name', 'desc')->get();
        $programmes = Programme::orderBy('name')->get();
        $combinations = SubjectCombination::orderBy('name')->get();

        return view('admin.applications.edit', compact('application', 'sessions', 'programmes', 'combinations'));
    }

    /**
     * Update the applicant's basic record + status.
     * Changes propagate to the linked student record when present.
     */
    public function update(Request $request, Applicant $application)
    {
        $request->validate([
            'surname' => 'required|string|max:255',
            'first_name' => 'required|string|max:255',
            'other_names' => 'nullable|string|max:255',
            'email' => 'required|email|unique:applicants,email,' . $application->id,
            'phone' => 'nullable|string|max:20',
            'programme_type' => 'required|in:IJMB,Remedial',
            'programme_id' => 'required|exists:programmes,id',
            'subject_combination_id' => 'nullable|exists:subject_combinations,id',
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'status' => 'required|in:registered,payment_pending,form_filling,submitted,under_review,approved,rejected,admitted',
            'is_active' => 'boolean',
        ]);

        $application->update($request->only([
            'surname',
            'first_name',
            'other_names',
            'email',
            'phone',
            'programme_type',
            'programme_id',
            'subject_combination_id',
            'academic_session_id',
            'status',
        ]) + ['is_active' => $request->boolean('is_active')]);

        // Propagate changes to the linked student record if it exists
        $student = $application->student;
        if ($student) {
            $student->update([
                'surname' => $application->surname,
                'first_name' => $application->first_name,
                'middle_name' => $application->other_names,
                'email' => $application->email,
                'phone' => $application->phone,
                'programme_type' => $application->programme_type,
                'programme_id' => $application->programme_id,
                'subject_combination_id' => $application->subject_combination_id,
                'academic_session_id' => $application->academic_session_id,
            ]);
        }

        // If set to admitted and no student record yet, create one (matches the admission-fee flow)
        if ($application->status === 'admitted' && !$student) {
            Student::createFromApplicant($application, $application->password);
        }

        return redirect()->route('admin.applications.show', $application)
            ->with('success', 'Application updated successfully.');
    }

    public function approve(Applicant $application)
    {
        if ($application->status !== 'submitted' && $application->status !== 'under_review') {
            return back()->with('error', 'Only submitted applications can be approved.');
        }

        $application->update(['status' => 'approved']);

        return back()->with('success', 'Application approved successfully.');
    }

    public function reject(Request $request, Applicant $application)
    {
        if ($application->status !== 'submitted' && $application->status !== 'under_review') {
            return back()->with('error', 'Only submitted applications can be rejected.');
        }

        $application->update(['status' => 'rejected']);

        return back()->with('success', 'Application rejected.');
    }

    public function bulkApprove(Request $request)
    {
        $request->validate([
            'applicant_ids' => 'required|array',
            'applicant_ids.*' => 'exists:applicants,id',
        ]);

        Applicant::whereIn('id', $request->applicant_ids)
            ->whereIn('status', ['submitted', 'under_review'])
            ->update(['status' => 'approved']);

        return back()->with('success', count($request->applicant_ids) . ' applications approved.');
    }

    public function export(Request $request)
    {
        $query = Applicant::with(['programme', 'academicSession'])
            ->whereIn('status', ['submitted', 'under_review', 'approved', 'rejected']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->academic_session_id);
        }

        $applicants = $query->orderBy('surname')->orderBy('first_name')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'Phone');
        $sheet->setCellValue('C1', 'Application Number');
        $sheet->setCellValue('D1', 'Program');
        $sheet->setCellValue('E1', 'Status');
        $sheet->setCellValue('F1', 'Session');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '006633']],
            'font' => ['color' => ['rgb' => 'FFFFFF']],
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);

        // Add data
        $row = 2;
        foreach ($applicants as $a) {
            $sheet->setCellValue('A' . $row, $a->surname . ' ' . $a->first_name);
            $sheet->setCellValue('B' . $row, $a->phone ?? 'N/A');
            $sheet->setCellValue('C' . $row, $a->application_number);
            $sheet->setCellValue('D' . $row, $a->programme->name ?? 'N/A');
            $sheet->setCellValue('E' . $row, ucfirst(str_replace('_', ' ', $a->status)));
            $sheet->setCellValue('F' . $row, $a->academicSession->name ?? 'N/A');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'applications_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }
}
