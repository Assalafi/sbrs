<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\AcademicSession;
use Illuminate\Http\Request;

class RegisteredUserController extends Controller
{
    public function index(Request $request)
    {
        $query = Applicant::with(['programme', 'academicSession', 'payments']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
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
                  ->orWhere('other_names', 'like', "%{$search}%")
                  ->orWhere('application_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $applicants = $query->orderBy('created_at', 'desc')->paginate(30)->withQueryString();

        $sessions = AcademicSession::orderBy('name', 'desc')->get();

        // Summary stats
        $totalRegistered = Applicant::count();
        $totalPaid = Applicant::whereHas('payments', fn($q) => $q->where('payment_type', 'application')->where('status', 'successful'))->count();
        $totalSubmitted = Applicant::where('status', 'submitted')->count();
        $totalNotStarted = Applicant::whereIn('status', ['registered', 'form_filling'])->count();

        return view('admin.registered-users.index', compact(
            'applicants', 'sessions',
            'totalRegistered', 'totalPaid', 'totalSubmitted', 'totalNotStarted'
        ));
    }

    public function export(Request $request)
    {
        $query = Applicant::with(['programme', 'academicSession']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('programme_type')) {
            $query->where('programme_type', $request->programme_type);
        }
        if ($request->filled('academic_session_id')) {
            $query->where('academic_session_id', $request->academic_session_id);
        }

        $applicants = $query->orderBy('surname')->get();

        $filename = 'registered_users_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($applicants) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['App. Number', 'Surname', 'First Name', 'Other Names', 'Email', 'Phone', 'Programme Type', 'Programme', 'Status', 'Paid', 'Session', 'Registered On']);
            foreach ($applicants as $a) {
                $hasPaid = $a->payments->where('payment_type', 'application')->where('status', 'successful')->count() > 0;
                fputcsv($file, [
                    $a->application_number ?? 'N/A',
                    $a->surname,
                    $a->first_name,
                    $a->other_names,
                    $a->email,
                    $a->phone,
                    $a->programme_type,
                    $a->programme->name ?? 'Not Selected',
                    ucfirst($a->status),
                    $hasPaid ? 'Yes' : 'No',
                    $a->academicSession->name ?? 'N/A',
                    $a->created_at->format('Y-m-d H:i'),
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
