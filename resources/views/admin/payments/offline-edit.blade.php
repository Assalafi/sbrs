@extends('layouts.admin')
@section('title', 'Edit Offline Payment')
@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div><div class="text-uppercase small fw-semibold text-success mb-1" style="letter-spacing:.08em;">Finance control</div><h3 class="fw-semibold mb-1">Edit offline payment</h3><p class="text-muted mb-0">Correct the payment details below. The audit trail keeps the record traceable.</p></div>
    <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-outline-secondary"><i class="material-symbols-outlined align-middle fs-18">arrow_back</i> Payment details</a>
</div>
<div class="card border-0 rounded-4 shadow-sm">
    <div class="card-body p-4 p-lg-5">
        <div class="rounded-3 p-3 mb-4" style="background:#f1f8f4;"><div class="small text-muted">Student</div><div class="fw-semibold">{{ $payment->payable?->registration_number }} · {{ $payment->payable?->full_name }}</div></div>
        <form method="POST" action="{{ route('admin.payments.update', $payment) }}" enctype="multipart/form-data" id="editPaymentForm">
            @csrf @method('PUT')
            <div class="row g-3">
                @php $configuredLegacyTypes = $paymentTypes->map(fn($type) => $type->legacyType())->all(); @endphp
                <div class="col-md-6"><label class="form-label fw-semibold">Payment type <span class="text-danger">*</span></label><select name="payment_type" class="form-select" required>@foreach(['application'=>'Application fee','admission'=>'Admission fee','registration'=>'Registration fee','examination'=>'Examination fee','hostel'=>'Hostel fee','other'=>'Other'] as $value=>$label)@if(!in_array($value, $configuredLegacyTypes, true))<option value="{{ $value }}" @selected($payment->payment_type===$value && !$payment->payment_type_id)>{{ $label }}</option>@endif @endforeach @foreach($paymentTypes as $type)<option value="custom:{{ $type->id }}" @selected($payment->payment_type_id===$type->id)>{{ $type->name }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Academic session <span class="text-danger">*</span></label><select name="academic_session_id" class="form-select" required>@foreach($sessions as $session)<option value="{{ $session->id }}" @selected($payment->academic_session_id===$session->id)>{{ $session->name }}{{ $session->is_current?' (current)':'' }}</option>@endforeach</select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Sponsor <span class="text-danger">*</span></label><select name="sponsor" class="form-select" required><option value="Self" @selected($payment->sponsor==='Self')>Self</option><option value="Scholarship" @selected($payment->sponsor==='Scholarship')>Scholarship</option></select></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Amount (₦) <span class="text-danger">*</span></label><input type="number" name="amount" class="form-control" min="0.01" step="0.01" value="{{ old('amount', $payment->amount) }}" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Payment reference <span class="text-danger">*</span></label><input type="text" name="reference" class="form-control" maxlength="120" value="{{ old('reference', $payment->reference) }}" required></div>
                <div class="col-md-6"><label class="form-label fw-semibold">Replace proof of payment</label><input type="file" name="proof_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">@if($payment->proof_path)<div class="form-text"><a href="{{ asset('storage/'.$payment->proof_path) }}" target="_blank">View current proof</a></div>@endif</div>
                <div class="col-12"><label class="form-label fw-semibold">Description</label><input type="text" name="description" class="form-control" maxlength="255" value="{{ old('description', $payment->description) }}"></div>
                <div class="col-12"><label class="form-label fw-semibold">Internal notes</label><textarea name="notes" class="form-control" rows="4" maxlength="2000">{{ old('notes', $payment->notes) }}</textarea></div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top"><a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-light">Cancel</a><button type="submit" class="btn btn-success px-4" id="updatePayment"><i class="material-symbols-outlined align-middle fs-18">save</i> Save changes</button></div>
        </form>
    </div>
</div>
@endsection
@push('scripts')<script>document.getElementById('editPaymentForm').addEventListener('submit',function(){const b=document.getElementById('updatePayment');b.disabled=true;b.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Saving...';});</script>@endpush
