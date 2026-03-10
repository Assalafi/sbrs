<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class AcademicSessionController extends Controller
{
    public function index()
    {
        $sessions = AcademicSession::orderBy('created_at', 'desc')->get();
        return view('admin.academic-sessions.index', compact('sessions'));
    }

    public function create()
    {
        return view('admin.academic-sessions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:academic_sessions,name',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'boolean',
        ]);

        $session = AcademicSession::create($validated);

        if ($request->boolean('is_current')) {
            $session->markAsCurrent();
        }

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Academic session created successfully.');
    }

    public function edit(AcademicSession $academicSession)
    {
        return view('admin.academic-sessions.edit', compact('academicSession'));
    }

    public function update(Request $request, AcademicSession $academicSession)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:academic_sessions,name,' . $academicSession->id,
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'is_current' => 'boolean',
            'is_active' => 'boolean',
        ]);

        // Handle is_current flag properly
        if ($request->boolean('is_current')) {
            // If setting as current, mark it as current (this will unset others)
            $academicSession->markAsCurrent();
        } else {
            // If unsetting current, explicitly set to false
            $academicSession->update(['is_current' => false]);
        }
        
        // Update other fields (excluding is_current since we handled it above)
        $academicSession->update([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'is_active' => $validated['is_active'],
        ]);

        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Academic session updated successfully.');
    }

    public function destroy(AcademicSession $academicSession)
    {
        $academicSession->delete();
        return redirect()->route('admin.academic-sessions.index')
            ->with('success', 'Academic session deleted successfully.');
    }

    public function setCurrent(AcademicSession $academicSession)
    {
        $academicSession->markAsCurrent();
        return back()->with('success', $academicSession->name . ' set as current session.');
    }
}
