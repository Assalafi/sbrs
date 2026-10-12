<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $student->load(['programme', 'subjectCombination', 'academicSession', 'payments', 'courseRegistrations.course', 'results.course']);

        $dueTypes = $student->duePaymentTypes();
        $requiredDue = $dueTypes->filter(fn ($t) => $t->is_required)->values();
        $dashboardNotice = $this->sanitizeRichText(setting('student_dashboard_notice', ''));

        return view('student.dashboard.index', compact('student', 'requiredDue', 'dueTypes', 'dashboardNotice'));
    }

    private function sanitizeRichText(?string $value): string
    {
        $value = strip_tags((string) $value, '<p><br><strong><b><em><i><u><s><h2><h3><h4><ul><ol><li><a><blockquote><div><span>');
        $value = preg_replace('/\s(on[a-z]+|formaction)\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+)/i', '', $value) ?? $value;
        $value = preg_replace_callback('/\sstyle\s*=\s*("[^"]*"|\'[^\']*\')/i', function ($match) {
            $style = trim($match[1], "\"'");
            preg_match('/text-align\s*:\s*(left|center|right|justify)\s*;?/i', $style, $align);
            return $align ? ' style="text-align:' . strtolower($align[1]) . '"' : '';
        }, $value) ?? $value;
        $value = preg_replace('/(href\s*=\s*["\'])\s*javascript:[^"\']*(["\'])/i', '$1#$2', $value) ?? $value;

        return trim($value);
    }

    public function showPasswordForm()
    {
        return view('student.password');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $student = Auth::guard('student')->user();

        if (!Hash::check($request->current_password, $student->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $student->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }
}
