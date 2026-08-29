@extends('layouts.admin')
@section('title', 'Payment Types')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Payment Types</h3>
    <a href="{{ route('admin.payment-types.create') }}" class="btn btn-primary btn-sm" style="background:#006633;border-color:#006633;">
        <i class="material-symbols-outlined fs-16 align-middle">add</i> New Payment Type
    </a>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Payer</th>
                        <th>Programme</th>
                        <th>Session</th>
                        <th>Amount</th>
                        <th>Split</th>
                        <th>Required</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($paymentTypes as $pt)
                    <tr>
                        <td><span class="badge bg-dark bg-opacity-10 text-dark">{{ $pt->code }}</span></td>
                        <td class="fw-medium">
                            {{ $pt->name }}
                            @if($pt->description)
                                <br><small class="text-muted">{{ $pt->description }}</small>
                            @endif
                        </td>
                        <td>
                            @if($pt->payer_type === 'applicant')
                                <span class="badge bg-info">Applicant</span>
                            @elseif($pt->payer_type === 'student')
                                <span class="badge bg-primary">Student</span>
                            @else
                                <span class="badge bg-secondary">Both</span>
                            @endif
                        </td>
                        <td>{{ $pt->programme_type === 'all' ? 'All Programmes' : $pt->programme_type }}</td>
                        <td>{{ $pt->academicSession->name ?? 'All Sessions' }}</td>
                        <td class="fw-medium">&#8358;{{ number_format($pt->amount, 2) }}</td>
                        <td>
                            @if($pt->split_enabled)
                                <span class="badge bg-info">Yes ({{ rtrim(rtrim($pt->split_percent, '0'), '.') }}% / {{ $pt->installment_count }}x)</span>
                            @else
                                <span class="badge bg-secondary">No</span>
                            @endif
                        </td>
                        <td>
                            @if($pt->is_required)
                                <span class="badge bg-danger">MUST</span>
                            @else
                                <span class="badge bg-secondary">Optional</span>
                            @endif
                        </td>
                        <td><span class="badge {{ $pt->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $pt->is_active ? 'Active' : 'Inactive' }}</span></td>
                        <td>
                            <a href="{{ route('admin.payment-types.edit', $pt) }}" class="btn btn-sm btn-outline-primary me-1"><i class="material-symbols-outlined fs-16">edit</i></a>
                            <form action="{{ route('admin.payment-types.destroy', $pt) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this payment type? Existing records are kept.')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="material-symbols-outlined fs-16">delete</i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="10" class="text-center text-muted py-4">No payment types configured.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
