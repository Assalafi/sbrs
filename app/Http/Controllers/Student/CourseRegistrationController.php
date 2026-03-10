<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseRegistration;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CourseRegistrationController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();

        if (!$student->is_registered) {
            return redirect()->route('student.dashboard')
                ->with('error', 'You must complete registration first.');
        }

        $session = AcademicSession::current();
        $registeredCourses = $student->courseRegistrations()
            ->where('academic_session_id', $session?->id)
            ->with('course')
            ->get();

        $availableCourses = Course::where('programme_id', $student->programme_id)
            ->where('is_active', true)
            ->where(function ($q) use ($student) {
                $q->whereNull('subject_combination_id')
                  ->orWhere('subject_combination_id', $student->subject_combination_id);
            })
            ->orderBy('semester')
            ->orderBy('course_code')
            ->get();

        return view('student.courses.index', compact('student', 'registeredCourses', 'availableCourses', 'session'));
    }

    public function store(Request $request)
    {
        $student = Auth::guard('student')->user();
        $session = AcademicSession::current();

        if (!$session) {
            return back()->with('error', 'No active academic session.');
        }

        $request->validate([
            'course_ids' => 'required|array',
            'course_ids.*' => 'exists:courses,id',
            'semester' => 'required|in:first,second',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->course_ids as $courseId) {
                CourseRegistration::firstOrCreate([
                    'student_id' => $student->id,
                    'course_id' => $courseId,
                    'academic_session_id' => $session->id,
                ], [
                    'semester' => $request->semester,
                ]);
            }
            DB::commit();
            return back()->with('success', 'Courses registered successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to register courses.');
        }
    }

    public function printForm()
    {
        $student = Auth::guard('student')->user();
        $student->load(['programme', 'subjectCombination', 'academicSession']);

        $session = AcademicSession::current();
        $registeredCourses = $student->courseRegistrations()
            ->where('academic_session_id', $session?->id)
            ->with('course')
            ->get();

        if ($registeredCourses->isEmpty()) {
            return redirect()->route('student.courses.index')
                ->with('error', 'No registered courses to print.');
        }

        return view('student.courses.print', compact('student', 'registeredCourses', 'session'));
    }

    public function destroy(CourseRegistration $courseRegistration)
    {
        $student = Auth::guard('student')->user();

        if ($courseRegistration->student_id !== $student->id) {
            abort(403);
        }

        $courseRegistration->delete();
        return back()->with('success', 'Course removed.');
    }
}
