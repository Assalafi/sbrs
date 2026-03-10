<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Programme;
use App\Models\SubjectCombination;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function index(Request $request)
    {
        $query = Course::with(['programme', 'subjectCombination']);

        if ($request->filled('programme_id')) {
            $query->where('programme_id', $request->programme_id);
        }
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        $courses = $query->orderBy('course_code')->paginate(25);
        $programmes = Programme::where('is_active', true)->orderBy('name')->get();

        return view('admin.courses.index', compact('courses', 'programmes'));
    }

    public function create()
    {
        $programmes = Programme::where('is_active', true)->orderBy('name')->get();
        return view('admin.courses.create', compact('programmes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'programme_id' => 'required|exists:programmes,id',
            'subject_combination_id' => 'nullable|exists:subject_combinations,id',
            'course_code' => 'required|string|max:20',
            'course_title' => 'required|string|max:255',
            'credit_units' => 'required|integer|min:0',
            'semester' => 'required|in:first,second',
        ]);

        Course::create($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course created successfully.');
    }

    public function edit(Course $course)
    {
        $programmes = Programme::where('is_active', true)->orderBy('name')->get();
        $combinations = SubjectCombination::where('programme_id', $course->programme_id)->get();
        return view('admin.courses.edit', compact('course', 'programmes', 'combinations'));
    }

    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'programme_id' => 'required|exists:programmes,id',
            'subject_combination_id' => 'nullable|exists:subject_combinations,id',
            'course_code' => 'required|string|max:20',
            'course_title' => 'required|string|max:255',
            'credit_units' => 'required|integer|min:0',
            'semester' => 'required|in:first,second',
            'is_active' => 'boolean',
        ]);

        $course->update($validated);

        return redirect()->route('admin.courses.index')
            ->with('success', 'Course updated successfully.');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return redirect()->route('admin.courses.index')
            ->with('success', 'Course deleted.');
    }
}
