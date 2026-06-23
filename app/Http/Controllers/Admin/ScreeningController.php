<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScreeningController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['programme', 'academicSession']);

        if ($request->filled('screening_status')) {
            $query->where('screening_status', $request->screening_status);
        }
        if ($request->filled('programme_type')) {
            $query->where('programme_type', $request->programme_type);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('surname', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(25);

        return view('admin.screening.index', compact('students'));
    }

    public function show(Student $student)
    {
        $student->load(['programme', 'subjectCombination', 'academicSession', 'applicant']);
        return view('admin.screening.show', compact('student'));
    }

    public function approve(Request $request, Student $student)
    {
        $student->update([
            'screening_status' => 'approved',
            'screening_remarks' => $request->input('remarks'),
            'screened_by' => Auth::id(),
            'screened_at' => now(),
        ]);

        return back()->with('success', 'Student screening approved.');
    }

    public function reject(Request $request, Student $student)
    {
        $request->validate(['remarks' => 'required|string']);

        $student->update([
            'screening_status' => 'rejected',
            'screening_remarks' => $request->input('remarks'),
            'screened_by' => Auth::id(),
            'screened_at' => now(),
        ]);

        return back()->with('success', 'Student screening rejected.');
    }

    public function export(Request $request)
    {
        $query = Student::with(['programme', 'academicSession', 'applicant']);

        if ($request->filled('screening_status')) {
            $query->where('screening_status', $request->screening_status);
        }
        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->academic_session_id);
        }

        $students = $query->orderBy('surname')->orderBy('first_name')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $sheet->setCellValue('A1', 'Name');
        $sheet->setCellValue('B1', 'Phone');
        $sheet->setCellValue('C1', 'Application Number');
        $sheet->setCellValue('D1', 'Registration Number');
        $sheet->setCellValue('E1', 'Program');
        $sheet->setCellValue('F1', 'Screening Status');
        $sheet->setCellValue('G1', 'Session');

        // Style headers
        $headerStyle = [
            'font' => ['bold' => true],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '006633']],
            'font' => ['color' => ['rgb' => 'FFFFFF']],
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

        // Add data
        $row = 2;
        foreach ($students as $s) {
            $sheet->setCellValue('A' . $row, $s->surname . ' ' . $s->first_name);
            $sheet->setCellValue('B' . $row, $s->phone ?? 'N/A');
            $sheet->setCellValue('C' . $row, $s->applicant->application_number ?? 'N/A');
            $sheet->setCellValue('D' . $row, $s->registration_number);
            $sheet->setCellValue('E' . $row, $s->programme->name ?? 'N/A');
            $sheet->setCellValue('F' . $row, ucfirst($s->screening_status));
            $sheet->setCellValue('G' . $row, $s->academicSession->name ?? 'N/A');
            $row++;
        }

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'screening_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $filename);
    }
}
