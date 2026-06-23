<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\AcademicSession;
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
