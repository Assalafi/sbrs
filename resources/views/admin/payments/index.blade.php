@extends('layouts.admin')
@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Payments</h3>
    <div class="d-flex gap-2">
        @can('payments.create')<a href="{{ route('admin.payments.offline') }}" class="btn btn-success btn-sm"><i class="material-symbols-outlined fs-16 align-middle">add_card</i> Record offline payment</a>@endcan
        <a href="{{ route('admin.payments.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-success btn-sm"><i class="material-symbols-outlined fs-16 align-middle">download</i> Export CSV</a>
    </div>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="">All</option>
                    @foreach(['pending','successful','failed','cancelled'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Type</label>
                <select class="form-select form-select-sm" name="payment_type">
                    <option value="">All</option>
                    @foreach(['application','admission','registration','examination','hostel','other'] as $t)
                        <option value="{{ $t }}" {{ request('payment_type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
                    @endforeach
                    @foreach($paymentTypes as $type)
                        <option value="{{ $type->legacyType() }}" {{ request('payment_type') == $type->legacyType() ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Session</label>
                <select class="form-select form-select-sm" name="academic_session_id">
                    <option value="">All</option>
                    @foreach($sessions as $session)
                        <option value="{{ $session->id }}" {{ request('academic_session_id') == $session->id ? 'selected' : '' }}>{{ $session->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small">Search student / reference</label>
                <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="Registration, name, reference...">
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">Filter</button>
                <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Date</th><th>Reference / RRR</th><th>Payer</th><th>Type</th><th>Sponsor</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td class="fs-13 text-muted">{{ $payment->created_at->format('M d, Y') }}</td>
                        <td><div class="fw-medium">{{ $payment->reference ?? 'N/A' }}</div><small class="text-muted">RRR: {{ $payment->rrr ?? 'N/A' }}</small></td>
                        <td>
                            @if($payment->payable)
                                {{ $payment->payable->surname ?? '' }} {{ $payment->payable->first_name ?? '' }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td><span class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($payment->payment_type) }}</span></td>
                        <td>{{ $payment->sponsor ?? '—' }}<br><small class="text-muted">{{ $payment->payment_method ?? 'Online' }}</small></td>
                        <td class="fw-medium">&#8358;{{ number_format($payment->amount, 2) }}</td>
                        <td><span class="badge bg-{{ $payment->status === 'successful' ? 'success' : ($payment->status === 'failed' ? 'danger' : ($payment->status === 'cancelled' ? 'secondary' : 'warning')) }}">{{ ucfirst($payment->status) }}</span></td>
                        <td>
                            <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary me-1"><i class="material-symbols-outlined fs-16">visibility</i></a>
                            @if($payment->status === 'successful')
                                <a href="{{ route('admin.payments.receipt', $payment) }}" class="btn btn-sm btn-outline-success me-1" title="Download receipt"><i class="material-symbols-outlined fs-16">receipt_long</i></a>
                            @endif
                            @if($payment->status === \App\Models\Payment::STATUS_PENDING)
                                @can('payments.delete')<button type="button" class="btn btn-sm btn-outline-danger me-1" title="Delete pending payment" data-bs-toggle="modal" data-bs-target="#deletePaymentModal-{{ $payment->id }}"><i class="material-symbols-outlined fs-16">delete</i></button>@endcan
                            @endif
                            @if(strtolower((string)$payment->payment_method) === 'offline' || $payment->reference)
                                @can('payments.edit')<a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-sm btn-outline-secondary me-1" title="Edit"><i class="material-symbols-outlined fs-16">edit</i></a>@endcan
                                @if($payment->status !== 'cancelled') @can('payments.edit')<form class="d-inline" method="POST" action="{{ route('admin.payments.cancel', $payment) }}" onsubmit="return confirm('Cancel this offline payment?');">@csrf<button class="btn btn-sm btn-outline-warning me-1" title="Cancel"><i class="material-symbols-outlined fs-16">cancel</i></button></form>@endcan
                                @else @can('payments.delete')<button type="button" class="btn btn-sm btn-outline-danger" title="Delete cancelled payment" data-bs-toggle="modal" data-bs-target="#deletePaymentModal-{{ $payment->id }}"><i class="material-symbols-outlined fs-16">delete</i></button>@endcan @endif
                            @endif
                            @if($payment->hasRrr() && $payment->status !== 'successful' && strtolower((string)$payment->payment_method) !== 'offline')
                            <button class="btn btn-sm btn-outline-info verify-btn" data-id="{{ $payment->id }}"><i class="material-symbols-outlined fs-16">sync</i></button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">No payments found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">{{ $payments->withQueryString()->links() }}</div>
    </div>
</div>

@foreach($payments as $payment)
    @php
        $isOfflinePayment = strtolower((string) $payment->payment_method) === 'offline' || !empty($payment->reference);
        $canDeletePayment = $payment->status === \App\Models\Payment::STATUS_PENDING
            || ($isOfflinePayment && $payment->status === \App\Models\Payment::STATUS_CANCELLED);
    @endphp
    @if($canDeletePayment)
        @can('payments.delete')
            <div class="modal fade" id="deletePaymentModal-{{ $payment->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('admin.payments.destroy', $payment) }}" method="POST">
                            @csrf
                            @method('DELETE')
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title"><i class="material-symbols-outlined me-2 align-middle">delete</i>{{ $payment->status === \App\Models\Payment::STATUS_PENDING ? 'Delete pending payment' : 'Delete cancelled payment' }}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <p class="mb-3">{{ $payment->status === \App\Models\Payment::STATUS_PENDING ? 'This removes the pending payment so a new payment can be started. Successful payments cannot be deleted.' : 'This permanently removes the cancelled offline payment record.' }}</p>
                                <label class="form-label fw-semibold">Reason for deletion <span class="text-danger">*</span></label>
                                <textarea name="deletion_reason" class="form-control" rows="3" minlength="5" maxlength="500" required placeholder="Explain why this payment is being removed"></textarea>
                                <small class="text-muted">Enter at least 5 characters for the audit record.</small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-danger"><i class="material-symbols-outlined align-middle me-1">delete</i>Delete payment</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan
    @endif
@endforeach
@endsection

@push('scripts')
<script>
document.querySelectorAll('.verify-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        fetch('/admin/payments/' + id + '/verify', { method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'} })
            .then(r => r.json())
            .then(data => { alert(data.message); location.reload(); })
            .catch(() => { alert('Verification failed'); this.disabled = false; this.innerHTML = '<i class="material-symbols-outlined fs-16">sync</i>'; });
    });
});
</script>
@endpush
