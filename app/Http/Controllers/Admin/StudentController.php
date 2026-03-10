<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with(['programme', 'academicSession']);

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
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('created_at', 'desc')->paginate(25);
        $sessions = AcademicSession::orderBy('name', 'desc')->get();

        return view('admin.students.index', compact('students', 'sessions'));
    }

    public function show(Student $student)
    {
        $student->load(['programme', 'subjectCombination', 'academicSession', 'courseRegistrations.course', 'results.course', 'payments']);
        return view('admin.students.show', compact('student'));
    }

    public function export(Request $request)
    {
        $query = Student::with(['programme', 'academicSession']);

        if ($request->filled('programme_type')) {
            $query->where('programme_type', $request->programme_type);
        }
        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->academic_session_id);
        }

        $students = $query->orderBy('surname')->get();

        $filename = 'students_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($students) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Reg. Number', 'Surname', 'First Name', 'Programme', 'Type', 'Email', 'Phone', 'Session', 'Status']);
            foreach ($students as $s) {
                fputcsv($file, [
                    $s->registration_number,
                    $s->surname,
                    $s->first_name,
                    $s->programme->name ?? 'N/A',
                    $s->programme_type,
                    $s->email,
                    $s->phone,
                    $s->academicSession->name ?? 'N/A',
                    $s->screening_status,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
