<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::guard('student')->user();
        $session = $request->filled('academic_session_id')
            ? AcademicSession::find($request->academic_session_id)
            : AcademicSession::current();

        $results = $student->results()
            ->where('academic_session_id', $session?->id)
            ->with('course')
            ->orderBy('semester')
            ->get();

        $sessions = AcademicSession::orderBy('name', 'desc')->get();

        return view('student.results.index', compact('student', 'results', 'session', 'sessions'));
    }
}
