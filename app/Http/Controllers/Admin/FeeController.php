<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    public function index()
    {
        $fees = Fee::with('academicSession')->orderBy('created_at', 'desc')->get();
        $sessions = AcademicSession::where('is_active', true)->orderBy('name', 'desc')->get();
        return view('admin.fees.index', compact('fees', 'sessions'));
    }

    public function create()
    {
        $sessions = AcademicSession::where('is_active', true)->orderBy('name', 'desc')->get();
        return view('admin.fees.create', compact('sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'fee_type' => 'required|in:application,admission,registration,examination',
            'programme_type' => 'required|in:IJMB,Remedial,all',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        $exists = Fee::where('academic_session_id', $validated['academic_session_id'])
            ->where('fee_type', $validated['fee_type'])
            ->where('programme_type', $validated['programme_type'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'A fee with this combination already exists.')->withInput();
        }

        Fee::create($validated);

        return redirect()->route('admin.fees.index')
            ->with('success', 'Fee created successfully.');
    }

    public function edit(Fee $fee)
    {
        $sessions = AcademicSession::where('is_active', true)->orderBy('name', 'desc')->get();
        return view('admin.fees.edit', compact('fee', 'sessions'));
    }

    public function update(Request $request, Fee $fee)
    {
        $validated = $request->validate([
            'academic_session_id' => 'required|exists:academic_sessions,id',
            'fee_type' => 'required|in:application,admission,registration,examination',
            'programme_type' => 'required|in:IJMB,Remedial,all',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'is_active' => 'boolean',
        ]);

        $fee->update($validated);

        return redirect()->route('admin.fees.index')
            ->with('success', 'Fee updated successfully.');
    }

    public function destroy(Fee $fee)
    {
        $fee->delete();
        return redirect()->route('admin.fees.index')
            ->with('success', 'Fee deleted.');
    }
}
