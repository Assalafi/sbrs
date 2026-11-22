<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentType;
use App\Models\AcademicSession;
use App\Models\Programme;
use Illuminate\Http\Request;

class PaymentTypeController extends Controller
{
    public function index()
    {
        $paymentTypes = PaymentType::with(['academicSession', 'programme'])->orderBy('sort_order')->orderBy('name')->get();
        return view('admin.payment-types.index', compact('paymentTypes'));
    }

    public function create()
    {
        $sessions = AcademicSession::orderBy('name', 'desc')->get();
        $programmes = Programme::where('is_active', true)->orderBy('type')->orderBy('name')->get();
        $remitaOptions = PaymentType::remitaServiceTypeOptions();
        return view('admin.payment-types.create', compact('sessions', 'programmes', 'remitaOptions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:payment_types,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'programme_type' => 'required|in:IJMB,Remedial,all',
            'programme_id' => 'nullable|exists:programmes,id',
            'academic_session_id' => 'nullable|exists:academic_sessions,id',
            'payer_type' => 'required|in:both,applicant,student',
            'amount' => 'required|numeric|min:0',
            'split_enabled' => 'boolean',
            'split_percent' => 'nullable|numeric|min:1|max:100',
            'installment_count' => 'required|integer|min:1|max:12',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'remita_service_type_id' => 'nullable|string|max:50',
            'remita_split_enabled' => 'boolean',
            'remita_surcharge' => 'nullable|numeric|min:0',
        ]);

        if (!empty($validated['programme_id'])) {
            $programme = Programme::find($validated['programme_id']);
            if ($validated['programme_type'] !== 'all' && $programme && $programme->type !== $validated['programme_type']) {
                return back()->withInput()->with('error', 'The selected programme belongs to the ' . $programme->type . ' programme type.');
            }
        }

        PaymentType::create($validated);

        return redirect()->route('admin.payment-types.index')
            ->with('success', 'Payment type created successfully.');
    }

    public function edit(PaymentType $paymentType)
    {
        $sessions = AcademicSession::orderBy('name', 'desc')->get();
        $programmes = Programme::where('is_active', true)->orderBy('type')->orderBy('name')->get();
        $remitaOptions = PaymentType::remitaServiceTypeOptions();
        return view('admin.payment-types.edit', compact('paymentType', 'sessions', 'programmes', 'remitaOptions'));
    }

    public function update(Request $request, PaymentType $paymentType)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:payment_types,code,' . $paymentType->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'programme_type' => 'required|in:IJMB,Remedial,all',
            'programme_id' => 'nullable|exists:programmes,id',
            'academic_session_id' => 'nullable|exists:academic_sessions,id',
            'payer_type' => 'required|in:both,applicant,student',
            'amount' => 'required|numeric|min:0',
            'split_enabled' => 'boolean',
            'split_percent' => 'nullable|numeric|min:1|max:100',
            'installment_count' => 'required|integer|min:1|max:12',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
            'remita_service_type_id' => 'nullable|string|max:50',
            'remita_split_enabled' => 'boolean',
            'remita_surcharge' => 'nullable|numeric|min:0',
        ]);

        if (!empty($validated['programme_id'])) {
            $programme = Programme::find($validated['programme_id']);
            if ($validated['programme_type'] !== 'all' && $programme && $programme->type !== $validated['programme_type']) {
                return back()->withInput()->with('error', 'The selected programme belongs to the ' . $programme->type . ' programme type.');
            }
        }

        $paymentType->update($validated);

        return redirect()->route('admin.payment-types.index')
            ->with('success', 'Payment type updated successfully.');
    }

    public function destroy(PaymentType $paymentType)
    {
        $paymentType->delete();
        return redirect()->route('admin.payment-types.index')
            ->with('success', 'Payment type deleted.');
    }
}
