<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Applicant;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
    public function index()
    {
        return view('admin.password-reset.index');
    }

    public function search(Request $request)
    {
        $request->validate([
            'search' => 'required|string|min:2',
            'type' => 'required|in:staff,student,applicant',
        ]);

        $search = $request->search;
        $type = $request->type;
        $results = collect();

        if ($type === 'staff') {
            $results = User::where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->limit(20)->get()->map(function ($u) {
                return [
                    'id' => $u->id,
                    'type' => 'staff',
                    'name' => $u->name,
                    'email' => $u->email,
                    'identifier' => $u->email,
                    'role' => $u->roles->pluck('name')->implode(', ') ?: 'N/A',
                    'status' => $u->is_active ? 'Active' : 'Inactive',
                ];
            });
        } elseif ($type === 'student') {
            $results = Student::where(function ($q) use ($search) {
                $q->where('surname', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('registration_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->limit(20)->get()->map(function ($s) {
                return [
                    'id' => $s->id,
                    'type' => 'student',
                    'name' => $s->surname . ' ' . $s->first_name,
                    'email' => $s->email,
                    'identifier' => $s->registration_number,
                    'role' => $s->programme_type,
                    'status' => ucfirst($s->screening_status ?? 'N/A'),
                ];
            });
        } elseif ($type === 'applicant') {
            $results = Applicant::where(function ($q) use ($search) {
                $q->where('surname', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('application_number', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->limit(20)->get()->map(function ($a) {
                return [
                    'id' => $a->id,
                    'type' => 'applicant',
                    'name' => $a->surname . ' ' . $a->first_name,
                    'email' => $a->email,
                    'identifier' => $a->application_number ?? 'N/A',
                    'role' => $a->programme_type,
                    'status' => ucfirst($a->status),
                ];
            });
        }

        return view('admin.password-reset.index', compact('results', 'search', 'type'));
    }

    public function reset(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'user_type' => 'required|in:staff,student,applicant',
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $model = match ($request->user_type) {
            'staff' => User::findOrFail($request->user_id),
            'student' => Student::findOrFail($request->user_id),
            'applicant' => Applicant::findOrFail($request->user_id),
        };

        $model->update(['password' => Hash::make($request->new_password)]);

        $name = $request->user_type === 'staff'
            ? $model->name
            : ($model->surname . ' ' . $model->first_name);

        return redirect()->route('admin.password-reset.index')
            ->with('success', "Password reset successfully for {$name}.");
    }
}
