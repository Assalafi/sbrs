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
                                @can('payments.delete')<form class="d-inline" method="POST" action="{{ route('admin.payments.destroy', $payment) }}" onsubmit="return confirm('Delete this pending payment permanently?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger me-1" title="Delete pending payment"><i class="material-symbols-outlined fs-16">delete</i></button></form>@endcan
                            @endif
                            @if(strtolower((string)$payment->payment_method) === 'offline' || $payment->reference)
                                @can('payments.edit')<a href="{{ route('admin.payments.edit', $payment) }}" class="btn btn-sm btn-outline-secondary me-1" title="Edit"><i class="material-symbols-outlined fs-16">edit</i></a>@endcan
                                @if($payment->status !== 'cancelled') @can('payments.edit')<form class="d-inline" method="POST" action="{{ route('admin.payments.cancel', $payment) }}" onsubmit="return confirm('Cancel this offline payment?');">@csrf<button class="btn btn-sm btn-outline-warning me-1" title="Cancel"><i class="material-symbols-outlined fs-16">cancel</i></button></form>@endcan
                                @else @can('payments.delete')<form class="d-inline" method="POST" action="{{ route('admin.payments.destroy', $payment) }}" onsubmit="return confirm('Delete this cancelled offline payment permanently?');">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="Delete"><i class="material-symbols-outlined fs-16">delete</i></button></form>@endcan @endif
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
