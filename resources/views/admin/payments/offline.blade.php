@extends('layouts.admin')
@section('title', 'Record Offline Payment')

@section('content')
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <div class="text-uppercase small fw-semibold text-success mb-1" style="letter-spacing:.08em;">Finance control</div>
        <h3 class="fw-semibold mb-1">Record an offline payment</h3>
        <p class="text-muted mb-0">Record a bank, cash or scholarship payment once. The payment becomes available to the student immediately.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.payments.offline.template') }}" class="btn btn-outline-success"><i class="material-symbols-outlined align-middle fs-18">download</i> Download template</a>
        <a href="{{ route('admin.payments.index') }}" class="btn btn-outline-secondary"><i class="material-symbols-outlined align-middle fs-18">arrow_back</i> Payments</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-xl-8">
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white border-0 px-4 pt-4">
                <h5 class="mb-1 fw-semibold">Enter one payment</h5>
                <p class="small text-muted mb-0">Every field marked with <span class="text-danger">*</span> is required.</p>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.payments.offline.store') }}" enctype="multipart/form-data" id="offlinePaymentForm">
                    @csrf
                    <div class="mb-3 position-relative">
                        <label class="form-label fw-semibold">Student <span class="text-danger">*</span></label>
                        <input type="hidden" name="student_id" id="student_id" value="{{ old('student_id') }}">
                        <input type="search" class="form-control" id="studentSearch" autocomplete="off" placeholder="Search registration number or student name" required>
                        <div id="studentResults" class="list-group position-absolute w-100 shadow-sm" style="z-index:20; top:100%;"></div>
                        <div id="selectedStudent" class="mt-2"></div>
                        <div class="form-text">Search is limited to matching records; all students are not loaded at once.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment type <span class="text-danger">*</span></label>
                            <select name="payment_type" class="form-select" required>
                                <option value="">Select payment type</option>
                                @php $configuredLegacyTypes = $paymentTypes->map(fn($type) => $type->legacyType())->all(); @endphp
                                @foreach(['application' => 'Application fee', 'admission' => 'Admission fee', 'registration' => 'Registration fee', 'examination' => 'Examination fee', 'hostel' => 'Hostel fee', 'other' => 'Other'] as $value => $label)
                                    @if(!in_array($value, $configuredLegacyTypes, true))<option value="{{ $value }}" @selected(old('payment_type') === $value)>{{ $label }}</option>@endif
                                @endforeach
                                @foreach($paymentTypes as $type)
                                    <option value="custom:{{ $type->id }}" @selected(old('payment_type') === 'custom:' . $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Academic session <span class="text-danger">*</span></label>
                            <select name="academic_session_id" class="form-select" required>
                                @foreach($sessions as $session)
                                    <option value="{{ $session->id }}" @selected(old('academic_session_id', $currentSession?->id) === $session->id)>{{ $session->name }}{{ $session->is_current ? ' (current)' : '' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sponsor <span class="text-danger">*</span></label>
                            <select name="sponsor" class="form-select" required>
                                <option value="Self" @selected(old('sponsor', 'Self') === 'Self')>Self</option>
                                <option value="Scholarship" @selected(old('sponsor') === 'Scholarship')>Scholarship</option>
                            </select>
                            <div class="form-text">This is used in reports and sponsor reconciliation.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Amount (₦) <span class="text-danger">*</span></label>
                            <input type="number" name="amount" class="form-control" min="0.01" step="0.01" value="{{ old('amount') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment reference <span class="text-danger">*</span></label>
                            <input type="text" name="reference" class="form-control" maxlength="120" value="{{ old('reference') }}" placeholder="Teller, transfer or receipt number" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Proof of payment</label>
                            <input type="file" name="proof_file" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text">JPG, PNG or PDF up to 5 MB.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <input type="text" name="description" class="form-control" maxlength="255" value="{{ old('description') }}" placeholder="What was paid for?">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Internal notes</label>
                            <textarea name="notes" class="form-control" rows="3" maxlength="2000" placeholder="Optional note for finance staff">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('admin.payments.index') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-success px-4" id="savePayment"><i class="material-symbols-outlined align-middle fs-18">save</i> Save offline payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-xl-4">
        <div class="card border-0 rounded-4 shadow-sm mb-4" style="background:#f1f8f4;">
            <div class="card-body p-4">
                <div class="d-flex gap-3 align-items-start"><span class="rounded-circle bg-success text-white p-2"><i class="material-symbols-outlined">verified</i></span><div><h6 class="fw-semibold mb-1">What happens after saving?</h6><p class="small text-muted mb-0">The payment is recorded as successful, appears in the student's history and can be downloaded as an official receipt.</p></div></div>
            </div>
        </div>
        <div class="card border-0 rounded-4 shadow-sm">
            <div class="card-header bg-white border-0 pt-4 px-4"><h6 class="fw-semibold mb-1">Bulk upload</h6><p class="small text-muted mb-0">Choose the common payment details once, then upload a simple student list.</p></div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.payments.offline.upload') }}" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    @php $uploadLegacyTypes = $paymentTypes->map(fn($type) => $type->legacyType())->all(); @endphp
                    <div class="mb-3"><label class="form-label fw-semibold">Payment type <span class="text-danger">*</span></label><select name="payment_type" class="form-select" required><option value="">Select payment type</option>@foreach(['application'=>'Application fee','admission'=>'Admission fee','registration'=>'Registration fee','examination'=>'Examination fee','hostel'=>'Hostel fee','other'=>'Other'] as $value=>$label)@if(!in_array($value, $uploadLegacyTypes, true))<option value="{{ $value }}">{{ $label }}</option>@endif @endforeach @foreach($paymentTypes as $type)<option value="custom:{{ $type->id }}">{{ $type->name }}</option>@endforeach</select></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Academic session <span class="text-danger">*</span></label><select name="academic_session_id" class="form-select" required><option value="">Select session</option>@foreach($sessions as $session)<option value="{{ $session->id }}" @selected($session->is_current)>{{ $session->name }}{{ $session->is_current?' (current)':'' }}</option>@endforeach</select></div>
                    <div class="mb-3"><label class="form-label fw-semibold">Sponsor <span class="text-danger">*</span></label><select name="sponsor" class="form-select" required><option value="Self">Self</option><option value="Scholarship">Scholarship</option></select></div>
                    <input type="file" name="payment_file" class="form-control mb-3" accept=".xlsx,.xls,.csv,.txt" required>
                    <button type="submit" class="btn btn-outline-success w-100"><i class="material-symbols-outlined align-middle fs-18">upload_file</i> Validate and import</button>
                </form>
                <hr>
                <ul class="small text-muted mb-0 ps-3">
                    <li>Use registration number in <code>student_id</code>.</li>
                    <li>Payment type, session and sponsor are selected above.</li>
                    <li>The upload is all-or-nothing: invalid rows are not saved.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@if(session('import_errors'))
<div class="modal fade" id="importErrors" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Upload needs attention</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p class="text-muted">No records were saved. Correct these rows and upload again:</p><ul class="mb-0">@foreach(session('import_errors') as $error)<li>{{ $error }}</li>@endforeach</ul></div></div></div></div>
@endif
@endsection

@push('styles')
<style>#studentResults:empty{display:none}.student-result{cursor:pointer}.student-result:hover{background:#f1f8f4}.selected-student{border:1px solid #b7dec5;background:#f1f8f4;border-radius:.75rem;padding:.65rem .8rem}</style>
@endpush
@push('scripts')
<script>
(function(){
 const search=document.getElementById('studentSearch');
 const results=document.getElementById('studentResults');
 const hidden=document.getElementById('student_id');
 const selected=document.getElementById('selectedStudent');
 const searchUrl=@json(route('admin.payments.offline.students.search'));
 let timer=null;
 let requestController=null;

 function escapeHtml(value){
     return String(value ?? '').replace(/[&<>'"]/g, character => ({
         '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#39;', '"':'&quot;'
     }[character]));
 }

 function showMessage(message, className='text-muted'){
     results.innerHTML='<div class="list-group-item '+className+'">'+escapeHtml(message)+'</div>';
 }

 function choose(student){
     hidden.value=student.id || '';
     search.value=student.registration_number || '';
     results.innerHTML='';
     selected.innerHTML='<div class="selected-student d-flex justify-content-between gap-2"><span><strong>'+escapeHtml(student.registration_number || 'No registration number')+'</strong> · '+escapeHtml(student.name || 'Unnamed student')+'<br><small class="text-muted">'+escapeHtml(student.programme || 'Programme not set')+'</small></span><button type="button" class="btn btn-sm btn-link text-danger p-0" id="clearStudent">Clear</button></div>';
     document.getElementById('clearStudent').onclick=function(){
         hidden.value=''; search.value=''; selected.innerHTML=''; search.focus();
     };
 }

 async function findStudents(query){
     if(requestController) requestController.abort();
     requestController=new AbortController();
     showMessage('Searching…', 'text-muted');
     try {
         const response=await fetch(searchUrl+'?q='+encodeURIComponent(query), {
             method:'GET',
             credentials:'same-origin',
             headers:{Accept:'application/json', 'X-Requested-With':'XMLHttpRequest'},
             signal:requestController.signal
         });
         if(!response.ok){
             if(response.status===401 || response.status===419){
                 showMessage('Your session has expired. Refresh this page and sign in again.', 'text-danger');
             } else if(response.status===403){
                 showMessage('You do not have permission to search students.', 'text-danger');
             } else {
                 showMessage('Student search is temporarily unavailable. Please try again.', 'text-danger');
             }
             return;
         }
         const payload=await response.json();
         const list=Array.isArray(payload) ? payload : (Array.isArray(payload.data) ? payload.data : []);
         if(!list.length){showMessage('No matching student found. Try the registration number or name.'); return;}
         results.innerHTML=list.map(student=>'<button type="button" class="list-group-item list-group-item-action student-result" data-student="'+encodeURIComponent(JSON.stringify(student))+'"><strong>'+escapeHtml(student.registration_number || '')+'</strong> · '+escapeHtml(student.name || 'Unnamed student')+'<small class="d-block text-muted">'+escapeHtml(student.programme || '')+'</small></button>').join('');
         results.querySelectorAll('[data-student]').forEach(button=>button.addEventListener('click',()=>choose(JSON.parse(decodeURIComponent(button.dataset.student)))));
     } catch(error){
         if(error.name!=='AbortError') showMessage('Student search failed. Check your connection and try again.', 'text-danger');
     }
 }

 search.addEventListener('input',function(){
     clearTimeout(timer);
     hidden.value='';
     selected.innerHTML='';
     const query=this.value.trim();
     if(query.length<2){results.innerHTML=''; if(requestController) requestController.abort(); return;}
     timer=setTimeout(()=>findStudents(query),250);
 });
 document.getElementById('offlinePaymentForm').addEventListener('submit',function(e){if(!hidden.value){e.preventDefault();alert('Select a student before saving the payment.');return;}const b=document.getElementById('savePayment');b.disabled=true;b.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Saving...';});
 document.getElementById('uploadForm').addEventListener('submit',function(){this.querySelector('button').disabled=true;this.querySelector('button').innerHTML='<span class="spinner-border spinner-border-sm me-1"></span> Validating...';});
 @if(session('import_errors')) new bootstrap.Modal(document.getElementById('importErrors')).show(); @endif
})();
</script>
@endpush
