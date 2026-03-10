<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Result;
use App\Models\Student;
use App\Models\Course;
use App\Models\AcademicSession;
use App\Models\Programme;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $query = Result::with(['student', 'course', 'academicSession']);

        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->academic_session_id);
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function ($q) use ($search) {
                $q->where('surname', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%");
            });
        }

        $results = $query->orderBy('created_at', 'desc')->paginate(25);
        $sessions = AcademicSession::orderBy('name', 'desc')->get();

        return view('admin.results.index', compact('results', 'sessions'));
    }

    public function create()
    {
        $sessions = AcademicSession::where('is_active', true)->orderBy('name', 'desc')->get();
        $programmes = Programme::where('is_active', true)->orderBy('name')->get();
        return view('admin.results.create', compact('sessions', 'programmes'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'course_id' => 'required|exists:courses,id',
            'semester' => 'required|in:first,second',
            'results' => 'required|array',
            'results.*.student_id' => 'required|exists:students,id',
            'results.*.ca_score' => 'nullable|numeric|min:0|max:40',
            'results.*.exam_score' => 'nullable|numeric|min:0|max:60',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->results as $resultData) {
                $caScore = $resultData['ca_score'] ?? 0;
                $examScore = $resultData['exam_score'] ?? 0;
                $totalScore = $caScore + $examScore;
                $grade = Result::calculateGrade($totalScore);
                $remark = Result::getRemark($grade);

                Result::updateOrCreate(
                    [
                        'student_id' => $resultData['student_id'],
                        'course_id' => $request->course_id,
                        'academic_session_id' => $request->academic_session_id,
                        'semester' => $request->semester,
                    ],
                    [
                        'ca_score' => $caScore,
                        'exam_score' => $examScore,
                        'total_score' => $totalScore,
                        'grade' => $grade,
                        'remark' => $remark,
                        'uploaded_by' => Auth::id(),
                    ]
                );
            }

            DB::commit();
            return back()->with('success', 'Results uploaded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to upload results: ' . $e->getMessage());
        }
    }

    public function getStudentsForCourse(Request $request)
    {
        $students = Student::whereHas('courseRegistrations', function ($q) use ($request) {
            $q->where('course_id', $request->course_id)
              ->where('academic_session_id', $request->academic_session_id);
        })->with(['results' => function ($q) use ($request) {
            $q->where('course_id', $request->course_id)
              ->where('academic_session_id', $request->academic_session_id);
        }])->orderBy('surname')->get();

        return response()->json($students);
    }

    public function getCoursesForProgramme(Request $request)
    {
        $courses = Course::where('programme_id', $request->programme_id)
            ->where('is_active', true)
            ->orderBy('course_code')
            ->get();

        return response()->json($courses);
    }
}
