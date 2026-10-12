@extends('layouts.student')
@section('title', 'Hostel Application')
@section('page-title', 'Hostel Application')

@section('content')
@php($pinVerified = $pinVerified ?? false)
<div class="student-page-heading">
    <div><h1>Hostel Application</h1><p>Validate your PIN, choose an available bed and complete your allocation.</p></div>
    <a href="{{ route('student.dashboard') }}" class="btn btn-outline-secondary btn-sm"><i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Dashboard</a>
</div>

<section class="hostel-hero mb-4">
    <div class="hostel-hero-icon"><span class="material-symbols-outlined">bedroom_parent</span></div>
    <div><strong>Apply for a hostel bed space</strong><p>One reservation is allowed per student. Keep your PIN and payment reference until your permit is ready.</p></div>
    <span class="hostel-hero-status">{{ $reservation ? 'Reservation in progress' : ($pinVerified ? 'PIN validated' : 'PIN required') }}</span>
</section>

@if($overviewError)
<div class="alert alert-danger">
    <i class="material-symbols-outlined align-middle">error</i>
    <strong>Hostel application is temporarily unavailable:</strong> {{ $overviewError }}
</div>
@endif
@if(!empty($pinError))
<div class="alert alert-warning">
    <i class="material-symbols-outlined align-middle">info</i>
    {{ $pinError }}
</div>
@endif

{{-- Current Reservation --}}
@if($reservation)
<div class="card hostel-reservation-card mb-4">
    <div class="card-header bg-transparent">
        <h5 class="mb-0"><i class="material-symbols-outlined me-2 align-middle">bed</i>Your Reservation</h5>
    </div>
    <div class="card-body p-4">
        <div class="hostel-reservation-banner"><span class="material-symbols-outlined">check_circle</span><div><strong>Your bed space is reserved</strong><small>Review the allocation details below and complete payment if required.</small></div></div>
        <div class="table-responsive">
            <table class="table table-borderless mb-3">
                <tbody>
                    <tr><td class="text-muted" width="40%">Hall:</td><td class="fw-bold">{{ $reservation['hall'] }}</td></tr>
                    <tr><td class="text-muted">Block:</td><td class="fw-bold">{{ $reservation['block'] }}</td></tr>
                    <tr><td class="text-muted">Room:</td><td class="fw-bold">{{ $reservation['room'] }}</td></tr>
                    <tr><td class="text-muted">Bed:</td><td class="fw-bold">{{ $reservation['bed'] }}</td></tr>
                    <tr><td class="text-muted">Hostel Fee:</td><td class="fw-bold text-success">&#8358;{{ number_format($reservation['amount'] ?? 0, 2) }}</td></tr>
                    <tr><td class="text-muted">Payment Method:</td>
                        <td>
                            @if(strtolower($reservation['payment_method'] ?? '') === 'bank')
                                <span class="badge bg-info">Bank (Cash)</span>
                            @else
                                <span class="badge bg-primary">Online</span>
                            @endif
                        </td>
                    </tr>
                    <tr><td class="text-muted">Payment Status:</td>
                        <td>
                            @if(($reservation['hostel_payment'] ?? 0) == 1)
                                <span class="badge bg-success">Paid</span>
                            @else
                                <span class="badge bg-warning">Pending</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        @if(($reservation['hostel_payment'] ?? 0) == 1)
            <div class="alert alert-success mb-3">
                <i class="material-symbols-outlined align-middle me-1">check_circle</i>
                Your hostel fee has been <strong>paid and confirmed</strong>. Your allocation is complete.
            </div>
            <a href="{{ route('student.hostel.permit') }}" class="btn btn-success mb-3">
                <i class="material-symbols-outlined fs-16 align-middle me-1">download</i> Download hostel permit
            </a>
        @elseif(strtolower($reservation['payment_method'] ?? '') === 'bank')
            <div class="alert alert-info mb-3">
                <i class="material-symbols-outlined align-middle me-1">account_balance</i>
                Your hostel is on <strong>Bank (Cash)</strong> payment. Proceed to the Office of the Dean of Students (Student Affairs) to complete your payment and confirm your allocation.
            </div>
        @else
            <div class="alert alert-info mb-3">
                <i class="material-symbols-outlined align-middle me-1">credit_card</i>
                Your hostel is on <strong>Online</strong> payment. Complete your payment online to confirm your allocation.
            </div>
            @if($payment && $payment->hasRrr())
                <div class="bg-light p-4 rounded text-center mb-4">
                    <p class="text-muted mb-2">Your RRR</p>
                    <h2 class="text-primary mb-0" style="letter-spacing: 3px;">{{ $payment->rrr }}</h2>
                    <p class="text-muted mt-2 mb-0">Amount: &#8358;{{ number_format($payment->amount, 2) }}</p>
                </div>
                <div class="row g-3 mb-4">
                    @if($payment->status !== 'successful')
                        <div class="col-md-6">
                            <button type="button" onclick="makePayment()" class="btn btn-success btn-lg w-100">
                                <i class="material-symbols-outlined me-2 align-middle">credit_card</i>Pay Online Now
                            </button>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <form id="verify-form" action="{{ route('student.hostel.pay-verify') }}" method="GET">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="material-symbols-outlined me-2 align-middle">sync</i>{{ $payment->status === 'successful' ? 'Complete allocation sync' : 'Verify Payment' }}
                            </button>
                        </form>
                    </div>
                </div>
                @if($payment->status === 'successful')
                    <div class="alert alert-warning mb-3">
                        <i class="material-symbols-outlined align-middle me-1">info</i>
                        Your payment is already recorded. Use <strong>Complete allocation sync</strong> to finish updating your hostel allocation.
                    </div>
                @endif
                @if($payment->status !== 'successful')
                    <div class="text-center mb-3">
                        <small class="text-muted">Or pay at any bank with RRR: <strong>{{ $payment->rrr }}</strong></small>
                    </div>
                @endif
            @else
                <form action="{{ route('student.hostel.pay-initiate') }}" method="POST">
                    @csrf
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg" style="background:#006633;border-color:#006633;">
                            <i class="material-symbols-outlined me-2 align-middle">receipt</i>Generate RRR & Continue
                        </button>
                    </div>
                </form>
            @endif
        @endif
        @if(($reservation['hostel_payment'] ?? 0) != 1)
            <button type="button" class="btn btn-outline-danger" onclick="releaseReservation()">
                <i class="material-symbols-outlined fs-16 align-middle">undo</i> Release Reservation
            </button>
        @endif
    </div>
</div>
@endif

{{-- Availability + Booking --}}
@if(!$reservation)
<div class="card hostel-booking-card">
    <div class="card-header bg-transparent">
        <h5 class="mb-0"><i class="material-symbols-outlined me-2 align-middle">bedroom_parent</i>Available Bed Spaces</h5>
    </div>
    <div class="card-body p-4">
        @if(!$pinVerified)
            <div class="alert alert-info d-flex gap-2 align-items-start mb-0">
                <i class="material-symbols-outlined">key</i>
                <div>
                    <strong>Hostel PIN required</strong>
                    <div class="small mt-1">Enter the PIN issued by Student Affairs. You must validate it before you can choose a bed space.</div>
                </div>
            </div>
            <form action="{{ route('student.hostel.validate-pin') }}" method="POST" class="mt-4 hostel-pin-form">
                @csrf
                <label for="hostel-pin" class="form-label fw-semibold">Hostel PIN</label>
                <div class="input-group">
                    <input id="hostel-pin" name="pin" type="text" class="form-control" maxlength="64" autocomplete="one-time-code" required placeholder="Enter your hostel PIN">
                    <button type="submit" class="btn btn-primary">
                        <i class="material-symbols-outlined align-middle fs-18 me-1">verified</i> Validate PIN
                    </button>
                </div>
                @error('pin')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            </form>
        @elseif(count($hall) === 0)
            <div class="alert alert-info mb-0">
                <i class="material-symbols-outlined align-middle me-1">info</i>
                No bed spaces are available for your category right now. Please check again later.
            </div>
        @else
        <div class="hostel-selection-grid row mb-3 g-2">
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Hall</label>
                <select class="form-select" id="hallSel" onchange="loadBlocks()">
                    <option value="">Select Hall</option>
                    @foreach(($overview['halls'] ?? []) as $hall)
                        <option value="{{ $hall }}">{{ $hall }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Block</label>
                <select class="form-select" id="blockSel" onchange="loadRooms()" disabled>
                    <option value="">Select Hall First</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Room</label>
                <select class="form-select" id="roomSel" onchange="loadBeds()" disabled>
                    <option value="">Select Block First</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold">Bed</label>
                <select class="form-select" id="bedSel" disabled>
                    <option value="">Select Room First</option>
                </select>
            </div>
        </div>

        <div class="hostel-stat-strip p-3 rounded mb-3">
            <div class="row text-center">
                <div class="col-4">
                    <h4 class="mb-0 text-primary">{{ $overview['available_beds'] ?? 0 }}</h4>
                    <small class="text-muted">Available Beds</small>
                </div>
                <div class="col-4">
                    <h4 class="mb-0 text-warning">{{ $overview['reserved_beds'] ?? 0 }}</h4>
                    <small class="text-muted">Reserved Beds</small>
                </div>
                <div class="col-4">
                    <h4 class="mb-0 text-success">{{ $gender }}</h4>
                    <small class="text-muted">Your Gender Block</small>
                </div>
            </div>
        </div>

        <div id="bedInfo" class="alert alert-success d-none">
            <strong>Selected Bed:</strong> <span id="bedInfoText"></span>
            <div class="mt-2">
                <button type="button" class="btn btn-primary" style="background:#006633;border-color:#006633;" onclick="reserveBed()">
                    <i class="material-symbols-outlined fs-16 align-middle">check</i> Reserve Bed
                </button>
            </div>
        </div>

        <div class="alert alert-info mb-0">
            <i class="material-symbols-outlined align-middle me-1">info</i>
            Select a hall, block, room and bed to reserve your space. You can only reserve one bed.
        </div>
        @endif
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    const hostelBase = '{{ url("/student/hostel") }}';
    const csrfToken = '{{ csrf_token() }}';

    async function hostelFetch(url, options) {
        options = options || {};
        options.headers = options.headers || {};
        options.headers['Accept'] = 'application/json';
        options.headers['X-CSRF-TOKEN'] = csrfToken;
        const res = await fetch(url, options);
        return res.json();
    }

    function setSel(id, items, placeholder, mapFn) {
        const sel = document.getElementById(id);
        let html = '<option value="">' + placeholder + '</option>';
        (items || []).forEach(function (it) {
            const v = mapFn(it);
            html += '<option value="' + v + '">' + v + '</option>';
        });
        sel.innerHTML = html;
        sel.disabled = !items || items.length === 0;
    }

    async function loadBlocks() {
        const hall = document.getElementById('hallSel').value;
        document.getElementById('blockSel').disabled = true;
        document.getElementById('roomSel').disabled = true;
        document.getElementById('bedSel').disabled = true;
        document.getElementById('bedInfo').classList.add('d-none');
        if (!hall) { setSel('blockSel', [], 'Select Hall First', x => x); return; }
        const res = await hostelFetch(hostelBase + '/blocks?hall=' + encodeURIComponent(hall));
        setSel('blockSel', res.data, 'Select Block', x => x);
    }

    async function loadRooms() {
        const hall = document.getElementById('hallSel').value;
        const block = document.getElementById('blockSel').value;
        document.getElementById('roomSel').disabled = true;
        document.getElementById('bedSel').disabled = true;
        document.getElementById('bedInfo').classList.add('d-none');
        if (!block) { setSel('roomSel', [], 'Select Block First', x => x); return; }
        const res = await hostelFetch(hostelBase + '/rooms?hall=' + encodeURIComponent(hall) + '&block=' + encodeURIComponent(block));
        setSel('roomSel', res.data, 'Select Block First', x => x);
    }

    async function loadBeds() {
        const hall = document.getElementById('hallSel').value;
        const block = document.getElementById('blockSel').value;
        const room = document.getElementById('roomSel').value;
        document.getElementById('bedSel').disabled = true;
        document.getElementById('bedInfo').classList.add('d-none');
        if (!room) { setSel('bedSel', [], 'Select Room First', x => x); return; }
        const res = await hostelFetch(hostelBase + '/beds?hall=' + encodeURIComponent(hall) + '&block=' + encodeURIComponent(block) + '&room=' + room);
        setSel('bedSel', res.data, 'Select Room First', x => x.bed);
        document.getElementById('bedSel').addEventListener('change', showBedInfo);
    }

    function showBedInfo() {
        const sel = document.getElementById('bedSel');
        const info = document.getElementById('bedInfo');
        if (!sel.value) { info.classList.add('d-none'); return; }
        const hall = document.getElementById('hallSel').value;
        const block = document.getElementById('blockSel').value;
        const room = document.getElementById('roomSel').value;
        document.getElementById('bedInfoText').textContent = hall + ' | ' + block + ' | Room ' + room + ' | Bed ' + sel.value;
        info.classList.remove('d-none');
    }

    async function reserveBed() {
        const hall = document.getElementById('hallSel').value;
        const block = document.getElementById('blockSel').value;
        const room = document.getElementById('roomSel').value;
        const bed = document.getElementById('bedSel').value;
        if (!hall || !block || !room || !bed) { alert('Please select a hall, block, room and bed.'); return; }
        const reserveButton = document.querySelector('#bedInfo button');
        if (reserveButton) { reserveButton.disabled = true; reserveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Reserving…'; }

        const res = await hostelFetch(hostelBase + '/reserve', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ hall: hall, block: block, room: room, bed: bed })
        });

        if (res.success) {
            alert('Bed reserved successfully! You can now proceed to payment.');
            window.location.reload();
        } else {
            alert(res.message || 'Failed to reserve bed.');
            window.location.reload();
        }
    }

    async function releaseReservation() {
        if (!confirm('Are you sure you want to release your reserved bed?')) return;
        const res = await hostelFetch(hostelBase + '/release', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        if (res.success) { alert('Reservation released.'); window.location.reload(); }
        else { alert(res.message || 'Failed to release reservation.'); }
    }

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!form.matches('form[action*="/student/hostel/"]')) return;
        if (form.dataset.submitting === '1') { event.preventDefault(); return; }
        form.dataset.submitting = '1';
        const button = form.querySelector('button[type="submit"]');
        if (button) {
            button.disabled = true;
            button.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Please wait…';
        }
    });
</script>
@endpush

@if(isset($payment) && $payment && $payment->hasRrr() && ($reservation['hostel_payment'] ?? 0) != 1)
    @include('partials.remita-pay')
@endif
