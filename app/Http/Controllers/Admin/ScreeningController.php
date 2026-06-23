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

        $filename = 'screening_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Name', 'Phone', 'Application Number', 'Registration Number', 'Program', 'Screening Status', 'Session']);
            foreach ($students as $s) {
                fputcsv($file, [
                    $s->surname . ' ' . $s->first_name,
                    $s->phone ?? 'N/A',
                    $s->applicant->application_number ?? 'N/A',
                    $s->registration_number,
                    $s->programme->name ?? 'N/A',
                    ucfirst($s->screening_status),
                    $s->academicSession->name ?? 'N/A',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
