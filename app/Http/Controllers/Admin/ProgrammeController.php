<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Programme;
use App\Models\SubjectCombination;
use Illuminate\Http\Request;

class ProgrammeController extends Controller
{
    public function index()
    {
        $programmes = Programme::withCount('subjectCombinations')->orderBy('type')->orderBy('name')->get();
        return view('admin.programmes.index', compact('programmes'));
    }

    public function create()
    {
        return view('admin.programmes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:programmes,code',
            'type' => 'required|in:IJMB,Remedial',
            'description' => 'nullable|string',
        ]);

        Programme::create($validated);

        return redirect()->route('admin.programmes.index')
            ->with('success', 'Programme created successfully.');
    }

    public function show(Programme $programme)
    {
        $programme->load('subjectCombinations');
        return view('admin.programmes.show', compact('programme'));
    }

    public function edit(Programme $programme)
    {
        return view('admin.programmes.edit', compact('programme'));
    }

    public function update(Request $request, Programme $programme)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:20|unique:programmes,code,' . $programme->id,
            'type' => 'required|in:IJMB,Remedial',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $programme->update($validated);

        return redirect()->route('admin.programmes.index')
            ->with('success', 'Programme updated successfully.');
    }

    public function destroy(Programme $programme)
    {
        $programme->delete();
        return redirect()->route('admin.programmes.index')
            ->with('success', 'Programme deleted.');
    }

    public function addCombination(Request $request, Programme $programme)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:20',
            'subjects' => 'required|string',
        ]);

        $programme->subjectCombinations()->create($validated);

        return back()->with('success', 'Subject combination added.');
    }

    public function removeCombination(SubjectCombination $combination)
    {
        $combination->delete();
        return back()->with('success', 'Subject combination removed.');
    }

    public function getCombinations(Programme $programme)
    {
        return response()->json($programme->subjectCombinations()->where('is_active', true)->get());
    }
}
