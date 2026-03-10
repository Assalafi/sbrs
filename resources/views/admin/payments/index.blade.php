@extends('layouts.admin')
@section('title', 'Payments')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Payments</h3>
    <a href="{{ route('admin.payments.export') }}?{{ http_build_query(request()->all()) }}" class="btn btn-outline-success btn-sm">
        <i class="material-symbols-outlined fs-16 align-middle">download</i> Export CSV
    </a>
</div>

<div class="card border-0 rounded-3 mb-4">
    <div class="card-body p-4">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Status</label>
                <select class="form-select form-select-sm" name="status">
                    <option value="">All</option>
                    @foreach(['pending','successful','failed'] as $s)
                        <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Type</label>
                <select class="form-select form-select-sm" name="payment_type">
                    <option value="">All</option>
                    @foreach(['application','admission','registration','examination'] as $t)
                        <option value="{{ $t }}" {{ request('payment_type') == $t ? 'selected' : '' }}>{{ ucfirst($t) }}</option>
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
                <label class="form-label small">Search (RRR / Order ID)</label>
                <input type="text" class="form-control form-control-sm" name="search" value="{{ request('search') }}" placeholder="RRR or Order ID...">
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
                <thead><tr><th>Date</th><th>RRR</th><th>Payer</th><th>Type</th><th>Amount</th><th>Status</th><th>Actions</th></tr></thead>
                <tbody>
                    @forelse($payments as $payment)
                    <tr>
                        <td class="fs-13 text-muted">{{ $payment->created_at->format('M d, Y') }}</td>
                        <td class="fw-medium">{{ $payment->rrr ?? 'N/A' }}</td>
                        <td>
                            @if($payment->payable)
                                {{ $payment->payable->surname ?? '' }} {{ $payment->payable->first_name ?? '' }}
                            @else
                                N/A
                            @endif
                        </td>
                        <td><span class="badge bg-info bg-opacity-10 text-info">{{ ucfirst($payment->payment_type) }}</span></td>
                        <td class="fw-medium">&#8358;{{ number_format($payment->amount, 2) }}</td>
                        <td><span class="badge bg-{{ $payment->status === 'successful' ? 'success' : ($payment->status === 'failed' ? 'danger' : 'warning') }}">{{ ucfirst($payment->status) }}</span></td>
                        <td>
                            <a href="{{ route('admin.payments.show', $payment) }}" class="btn btn-sm btn-outline-primary me-1"><i class="material-symbols-outlined fs-16">visibility</i></a>
                            @if($payment->hasRrr() && $payment->status !== 'successful')
                            <button class="btn btn-sm btn-outline-info verify-btn" data-id="{{ $payment->id }}"><i class="material-symbols-outlined fs-16">sync</i></button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center text-muted py-4">No payments found.</td></tr>
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
