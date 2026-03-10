<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BiodataController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $student->load(['programme', 'subjectCombination', 'academicSession']);
        return view('student.biodata.index', compact('student'));
    }

    public function update(Request $request)
    {
        $student = Auth::guard('student')->user();

        $validated = $request->validate([
            'state_of_origin' => 'nullable|string|max:100',
            'lga' => 'nullable|string|max:100',
            'home_address' => 'nullable|string',
            'guardian_name' => 'nullable|string|max:255',
            'guardian_address' => 'nullable|string',
            'guardian_email' => 'nullable|email',
            'guardian_phone' => 'nullable|string|max:20',
            'sponsor_name' => 'nullable|string|max:255',
            'sponsor_relationship' => 'nullable|string|max:100',
            'sponsor_address' => 'nullable|string',
            'health_status' => 'nullable|in:Normal,Disabled',
            'disability_type' => 'nullable|string|max:255',
            'medication_type' => 'nullable|string|max:255',
            'hobbies' => 'nullable|string',
            'passport_photo' => 'nullable|image|mimes:jpeg,jpg,png|max:500',
        ]);

        if ($request->hasFile('passport_photo')) {
            if ($student->passport_photo) {
                Storage::disk('public')->delete($student->passport_photo);
            }
            $validated['passport_photo'] = $request->file('passport_photo')->store('passport_photos', 'public');
        } else {
            unset($validated['passport_photo']);
        }

        $student->update($validated);

        return back()->with('success', 'Biodata updated successfully.');
    }

    public function getLgas(string $state)
    {
        $lgas = config('lgas.' . $state, []);
        return response()->json($lgas);
    }
}
