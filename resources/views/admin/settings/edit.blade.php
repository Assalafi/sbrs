@extends('layouts.admin')
@section('title', 'Edit ' . ucfirst($group) . ' Settings')

@section('content')
<div class="sbrs-remita-page">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            @if($group === 'remita')
                <div class="text-uppercase text-muted small fw-semibold letter-spacing-1">Administration · Payments</div>
                <h3 class="fw-bold mb-1">Remita payment settings</h3>
                <p class="text-muted mb-0">Control the Remita connection, optional service charges, and how charges are shared between beneficiaries.</p>
            @else
                <div class="text-uppercase text-muted small fw-semibold letter-spacing-1">Administration · Settings</div>
                <h3 class="fw-bold mb-1">Edit {{ ucfirst($group) }} settings</h3>
                <p class="text-muted mb-0">Update the settings for this part of the SBRS portal.</p>
            @endif
        </div>
        <a href="{{ route('admin.settings.index') }}" class="btn btn-light border">
            <i class="material-symbols-outlined align-middle fs-18 me-1">arrow_back</i> All settings
        </a>
    </div>

    <form action="{{ route('admin.settings.update', $group) }}" method="POST" enctype="multipart/form-data" id="remitaSettingsForm">
        @csrf
        @method('PUT')

        @if($group === 'remita')
        @php
            $splitEnabled = (bool) ($remitaSplitSettings['enabled'] ?? false);
            $beneficiaries = $remitaSplitSettings['beneficiaries'] ?? [];
        @endphp
        <div class="row g-3 mb-4">
            <div class="col-md-4"><div class="sbrs-remita-stat"><span class="stat-icon {{ $splitEnabled ? 'is-on' : 'is-off' }}"><i class="material-symbols-outlined">{{ $splitEnabled ? 'check_circle' : 'pause_circle' }}</i></span><div><small>Split payments</small><strong>{{ $splitEnabled ? 'Enabled' : 'Disabled' }}</strong></div></div></div>
            <div class="col-md-4"><div class="sbrs-remita-stat"><span class="stat-icon"><i class="material-symbols-outlined">account_balance</i></span><div><small>Beneficiary accounts</small><strong>3 accounts</strong></div></div></div>
            <div class="col-md-4"><div class="sbrs-remita-stat"><span class="stat-icon"><i class="material-symbols-outlined">percent</i></span><div><small>Allocation total</small><strong id="allocationTotal">0%</strong></div></div></div>
        </div>

        <div class="sbrs-remita-hero mb-4">
            <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
                <div><span class="eyebrow">Optional payment allocation</span><h4>Remita split payments</h4><p>When enabled, a service-level charge is added to the student’s Remita payment and distributed to the three accounts below. Student receipts continue to show the normal base service amount.</p></div>
                <label class="sbrs-switch"><input type="checkbox" name="remita_split_enabled" value="1" @checked($splitEnabled)><span></span><b>Enable split</b></label>
            </div>
            <div class="sbrs-remita-notice"><i class="material-symbols-outlined">info</i><span>Activation is two-step: enable this setting here, then enable <strong>Use Remita split for this service</strong> and enter its charge on the individual payment type.</span></div>
        </div>

        <div class="sbrs-panel mb-4">
            <div class="panel-heading"><div><h5>Beneficiary allocation</h5><p>Enter the bank account details and the percentage each account receives.</p></div><span class="badge rounded-pill bg-light text-dark border">Must total 100%</span></div>
            <div class="row g-3">
                @foreach($beneficiaries as $i => $b)
                    <div class="col-lg-4">
                        <div class="sbrs-beneficiary-card">
                            <div class="d-flex justify-content-between align-items-center mb-3"><div class="account-title"><span>{{ $i + 1 }}</span><strong>{{ $i === 0 ? 'Primary account' : 'Additional account '.$i }}</strong></div><span class="allocation-chip" data-chip="{{ $i }}">0%</span></div>
                            <label class="form-label">Account name</label>
                            <input class="form-control mb-3" name="remita_beneficiaries[{{ $i }}][label]" placeholder="e.g. University revenue account" value="{{ old('remita_beneficiaries.'.$i.'.label',$b['label']??'') }}">
                            <label class="form-label">Account number</label>
                            <input class="form-control mb-3" name="remita_beneficiaries[{{ $i }}][account_number]" placeholder="Enter account number" inputmode="numeric" autocomplete="off" value="{{ old('remita_beneficiaries.'.$i.'.account_number',$b['account_number']??'') }}">
                            <label class="form-label">Bank code</label>
                            <input class="form-control mb-3" name="remita_beneficiaries[{{ $i }}][bank_code]" placeholder="Enter bank code" inputmode="numeric" value="{{ old('remita_beneficiaries.'.$i.'.bank_code',$b['bank_code']??'') }}">
                            <label class="form-label">Share percentage</label>
                            <div class="input-group"><input class="form-control js-allocation" type="number" min="0" max="100" step="0.01" name="remita_beneficiaries[{{ $i }}][percentage]" value="{{ old('remita_beneficiaries.'.$i.'.percentage',$b['percentage']??0) }}"><span class="input-group-text">%</span></div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="sbrs-total-message mt-3" id="allocationMessage" role="status"></div>
        </div>
        @endif

        <div class="sbrs-panel mb-4">
            <div class="panel-heading"><div><h5>{{ $group === 'remita' ? 'Remita connection' : ucfirst($group).' settings' }}</h5><p>{{ $group === 'remita' ? 'These values connect SBRS to your Remita merchant account. Change them only when your Remita credentials change.' : 'Review and update the settings below.' }}</p></div><i class="material-symbols-outlined text-muted">{{ $group === 'remita' ? 'verified_user' : 'tune' }}</i></div>
            @foreach($settings as $setting)
            @if($setting->key === 'remita_split_settings') @continue @endif
            <div class="mb-4">
                <label for="{{ $setting->key }}" class="form-label fw-medium">
                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                    @if($setting->description)<small class="text-muted d-block">{{ $setting->description }}</small>@endif
                </label>
                @if($setting->type === 'text')
                    <input type="text" class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}">
                @elseif($setting->type === 'textarea')
                    <textarea class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" rows="3">{{ $setting->value }}</textarea>
                @elseif($setting->type === 'richtext')
                    <div class="sbrs-rich-editor" data-rich-editor-wrapper><div class="sbrs-rich-toolbar" role="toolbar" aria-label="Text formatting tools"><select class="sbrs-rich-format" data-rich-format aria-label="Text style"><option value="p">Paragraph</option><option value="h2">Heading</option><option value="h3">Small heading</option></select><span class="sbrs-rich-divider"></span><button type="button" data-command="bold" title="Bold"><i class="material-symbols-outlined">format_bold</i></button><button type="button" data-command="italic" title="Italic"><i class="material-symbols-outlined">format_italic</i></button><button type="button" data-command="underline" title="Underline"><i class="material-symbols-outlined">format_underlined</i></button><button type="button" data-command="strikeThrough" title="Strikethrough"><i class="material-symbols-outlined">format_strikethrough</i></button><span class="sbrs-rich-divider"></span><button type="button" data-command="justifyLeft" title="Align left"><i class="material-symbols-outlined">format_align_left</i></button><button type="button" data-command="justifyCenter" title="Align center"><i class="material-symbols-outlined">format_align_center</i></button><button type="button" data-command="justifyRight" title="Align right"><i class="material-symbols-outlined">format_align_right</i></button><button type="button" data-command="justifyFull" title="Justify"><i class="material-symbols-outlined">format_align_justify</i></button><span class="sbrs-rich-divider"></span><button type="button" data-command="insertUnorderedList" title="Bulleted list"><i class="material-symbols-outlined">format_list_bulleted</i></button><button type="button" data-command="insertOrderedList" title="Numbered list"><i class="material-symbols-outlined">format_list_numbered</i></button><button type="button" data-command="createLink" title="Insert link"><i class="material-symbols-outlined">link</i></button><button type="button" data-command="removeFormat" title="Clear formatting"><i class="material-symbols-outlined">format_clear</i></button><span class="sbrs-rich-divider"></span><button type="button" data-command="undo" title="Undo"><i class="material-symbols-outlined">undo</i></button><button type="button" data-command="redo" title="Redo"><i class="material-symbols-outlined">redo</i></button></div><div class="sbrs-rich-surface" data-rich-surface contenteditable="true" role="textbox" aria-multiline="true" spellcheck="true">{!! $setting->value !!}</div><textarea class="d-none" name="{{ $setting->key }}" data-rich-source>{{ $setting->value }}</textarea></div>
                @elseif($setting->type === 'boolean')
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" id="{{ $setting->key }}" name="{{ $setting->key }}" {{ $setting->value ? 'checked' : '' }}><label class="form-check-label" for="{{ $setting->key }}">{{ $setting->value ? 'Enabled' : 'Disabled' }}</label></div>
                @elseif($setting->type === 'number')
                    <input type="number" class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}">
                @elseif($setting->type === 'image' || $setting->type === 'file')
                    @if($setting->value)<div class="mb-2">@if($setting->type === 'image')<img src="{{ asset('storage/' . $setting->value) }}" alt="{{ $setting->key }}" class="rounded" style="max-height:100px;">@else<span class="badge bg-success">File uploaded</span>@endif</div>@endif
                    <input type="file" class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" {{ $setting->type === 'image' ? 'accept=image/*' : '' }}>
                @elseif($setting->type === 'select')
                    <select class="form-select" id="{{ $setting->key }}" name="{{ $setting->key }}">@foreach(explode(',', $setting->options ?? '') as $opt)<option value="{{ trim($opt) }}" {{ $setting->value === trim($opt) ? 'selected' : '' }}>{{ trim($opt) }}</option>@endforeach</select>
                @else
                    <input type="text" class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}">
                @endif
            </div>
            @endforeach
        </div>

        <div class="sbrs-remita-footer d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="small text-muted"><i class="material-symbols-outlined align-middle fs-17 me-1">lock</i>Changes are applied to new Remita payment references.</div>
            <button type="submit" class="btn btn-primary px-4"><i class="material-symbols-outlined fs-18 align-middle me-1">save</i>Save Remita settings</button>
        </div>
    </form>

    @if($group === 'remita')
    <div class="sbrs-service-link mt-4"><div><i class="material-symbols-outlined">receipt_long</i><div><strong>Configure service charges</strong><p class="mb-0 text-muted">Enable split and set the additional charge separately for each application, registration, hostel, or examination service.</p></div></div><a href="{{ route('admin.payment-types.index') }}" class="btn btn-outline-primary">Open payment services <i class="material-symbols-outlined align-middle fs-17">arrow_forward</i></a></div>
    @endif
</div>
@endsection

@push('styles')
<style>
.sbrs-remita-page{max-width:1180px;margin:0 auto}.letter-spacing-1{letter-spacing:.08em}.sbrs-remita-stat{display:flex;align-items:center;gap:.8rem;background:#fff;border:1px solid #e6edf2;border-radius:1rem;padding:1rem 1.1rem;min-height:82px;box-shadow:0 5px 18px rgba(29,67,91,.04)}.sbrs-remita-stat small{display:block;color:#718394;font-size:.76rem}.sbrs-remita-stat strong{display:block;color:#1e3445;font-size:1.05rem;margin-top:.18rem}.stat-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:.75rem;background:#e9f3fb;color:#1575a8}.stat-icon.is-on{background:#e8f7ef;color:#147a47}.stat-icon.is-off{background:#f4f5f6;color:#718394}.sbrs-remita-hero{border-radius:1.2rem;padding:1.35rem 1.45rem;background:linear-gradient(120deg,#0f6d9d,#1689b4);color:#fff;box-shadow:0 10px 26px rgba(10,80,120,.16)}.sbrs-remita-hero .eyebrow{text-transform:uppercase;font-size:.68rem;font-weight:700;letter-spacing:.12em;opacity:.8}.sbrs-remita-hero h4{font-size:1.35rem;margin:.35rem 0 .45rem}.sbrs-remita-hero p{max-width:720px;margin:0;color:rgba(255,255,255,.85);line-height:1.55}.sbrs-switch{display:inline-flex;align-items:center;gap:.55rem;cursor:pointer;white-space:nowrap}.sbrs-switch input{position:absolute;opacity:0}.sbrs-switch span{width:2.8rem;height:1.55rem;background:rgba(255,255,255,.35);border-radius:99px;position:relative;transition:.2s}.sbrs-switch span:after{content:"";position:absolute;width:1.15rem;height:1.15rem;top:.2rem;left:.2rem;border-radius:50%;background:#fff;transition:.2s;box-shadow:0 1px 4px rgba(0,0,0,.22)}.sbrs-switch input:checked+span{background:#72e0a6}.sbrs-switch input:checked+span:after{transform:translateX(1.25rem)}.sbrs-remita-notice{display:flex;align-items:flex-start;gap:.55rem;margin-top:1.1rem;padding:.7rem .85rem;border-radius:.7rem;background:rgba(255,255,255,.12);font-size:.86rem;color:rgba(255,255,255,.9)}.sbrs-panel{background:#fff;border:1px solid #e4edf2;border-radius:1rem;padding:1.3rem;box-shadow:0 5px 18px rgba(29,67,91,.04)}.panel-heading{display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;margin-bottom:1.2rem;padding-bottom:1rem;border-bottom:1px solid #edf2f5}.panel-heading h5{font-size:1.05rem;margin:0 0 .25rem;color:#20394a}.panel-heading p{font-size:.83rem;color:#738493;margin:0}.sbrs-beneficiary-card{height:100%;padding:1rem;border:1px solid #dfe9ef;border-radius:.85rem;background:#fbfdfe}.account-title{display:flex;align-items:center;gap:.55rem}.account-title span{width:1.8rem;height:1.8rem;border-radius:50%;display:grid;place-items:center;background:#e5f2f9;color:#116e9e;font-size:.8rem;font-weight:700}.account-title strong{font-size:.9rem;color:#264456}.allocation-chip{background:#edf6fb;color:#1a759e;border-radius:99px;padding:.25rem .55rem;font-size:.75rem;font-weight:700}.sbrs-beneficiary-card .form-label{font-size:.77rem;font-weight:600;color:#597182;margin-bottom:.35rem}.sbrs-beneficiary-card .form-control{border-color:#dce7ed;min-height:42px}.sbrs-total-message{font-size:.84rem;padding:.7rem .85rem;border-radius:.65rem;background:#f5f8fa;color:#607685}.sbrs-total-message.is-valid{background:#eaf8ef;color:#217346}.sbrs-total-message.is-invalid{background:#fff4e7;color:#a05b0b}.sbrs-remita-footer{padding:1rem 0;border-top:1px solid #e5edf1}.sbrs-remita-footer .btn-primary{background:#0f6d9d;border-color:#0f6d9d}.sbrs-service-link{display:flex;justify-content:space-between;align-items:center;gap:1rem;padding:1rem 1.2rem;border:1px dashed #9bc6da;background:#f4fbfe;border-radius:1rem}.sbrs-service-link>div{display:flex;align-items:flex-start;gap:.7rem}.sbrs-service-link>div>i{color:#147da8}.sbrs-service-link strong{color:#244759}.sbrs-service-link p{font-size:.82rem;margin-top:.2rem}.sbrs-service-link a{white-space:nowrap}@media(max-width:576px){.sbrs-remita-page{margin:0 -.25rem}.sbrs-remita-hero{padding:1.15rem}.sbrs-remita-hero h4{font-size:1.2rem}.sbrs-panel{padding:1rem}.panel-heading{display:block}.panel-heading .badge{display:inline-block;margin-top:.7rem}.sbrs-service-link{display:block}.sbrs-service-link a{display:inline-flex;margin-top:.8rem}.sbrs-remita-footer{align-items:stretch!important}.sbrs-remita-footer .btn{width:100%}}
.sbrs-rich-editor{overflow:hidden;border:1px solid #d6e2eb;border-radius:.5rem;background:#fff}.sbrs-rich-toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:.2rem;padding:.45rem;border-bottom:1px solid #d6e2eb;background:#f7fafc}.sbrs-rich-toolbar button,.sbrs-rich-format{display:inline-flex;align-items:center;justify-content:center;min-width:2.1rem;height:2rem;border:1px solid transparent;border-radius:.3rem;background:transparent;color:#39566b;cursor:pointer}.sbrs-rich-toolbar button .material-symbols-outlined{font-size:18px;line-height:1;color:inherit}.sbrs-rich-toolbar button:hover,.sbrs-rich-toolbar button:focus,.sbrs-rich-format:focus{border-color:#8fc5e3;background:#eaf6fd;color:#126d9f;outline:none}.sbrs-rich-format{min-width:8.5rem;justify-content:flex-start;padding:0 .45rem;background:#fff}.sbrs-rich-divider{width:1px;height:1.4rem;margin:0 .2rem;background:#d6e2eb}.sbrs-rich-surface{min-height:180px;padding:.85rem 1rem;color:#243b4d;line-height:1.6;outline:none}.sbrs-rich-surface:focus{box-shadow:inset 0 0 0 2px rgba(0,102,51,.15)}.sbrs-rich-surface p,.sbrs-rich-surface h2,.sbrs-rich-surface h3,.sbrs-rich-surface h4,.sbrs-rich-surface blockquote{margin:0 0 .55rem}.sbrs-rich-surface ul,.sbrs-rich-surface ol{margin:.25rem 0 .65rem;padding-left:1.4rem}.sbrs-rich-surface blockquote{border-left:3px solid #b7dec5;padding-left:.8rem;color:#587284}@media(max-width:576px){.sbrs-rich-toolbar{gap:.1rem}.sbrs-rich-toolbar button{min-width:1.95rem}}
</style>
@endpush
@push('scripts')
<script>
(function(){function initRemita(){var inputs=[].slice.call(document.querySelectorAll('.js-allocation')),total=document.getElementById('allocationTotal'),message=document.getElementById('allocationMessage');if(!inputs.length||!total||!message)return;var refresh=function(){var sum=inputs.reduce(function(v,input){return v+(parseFloat(input.value)||0)},0);total.textContent=(Math.round(sum*100)/100)+'%';inputs.forEach(function(input,index){var chip=document.querySelector('[data-chip="'+index+'"]');if(chip)chip.textContent=(parseFloat(input.value)||0)+'%';});var valid=Math.abs(sum-100)<.001;message.className='sbrs-total-message '+(valid?'is-valid':'is-invalid');message.innerHTML=valid?'<i class="material-symbols-outlined align-middle fs-17 me-1">check_circle</i>Allocation is complete. The three shares add up to 100%.':'<i class="material-symbols-outlined align-middle fs-17 me-1">warning</i>Allocation must total exactly 100%. Current total: <strong>'+((Math.round(sum*100)/100))+'%</strong>.';};inputs.forEach(function(input){input.addEventListener('input',refresh)});refresh();var form=document.getElementById('remitaSettingsForm');if(form)form.addEventListener('submit',function(e){var sum=inputs.reduce(function(v,input){return v+(parseFloat(input.value)||0)},0);if(Math.abs(sum-100)>=.001){e.preventDefault();refresh();message.scrollIntoView({behavior:'smooth',block:'center'});}});}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',initRemita);else initRemita();})();
(function(){function init(){document.querySelectorAll('[data-rich-editor-wrapper]').forEach(function(wrapper){var surface=wrapper.querySelector('[data-rich-surface]'),source=wrapper.querySelector('[data-rich-source]');if(!surface||!source)return;var sync=function(){source.value=surface.innerHTML;};wrapper.querySelectorAll('[data-command]').forEach(function(button){button.addEventListener('mousedown',function(e){e.preventDefault();});button.addEventListener('click',function(){var command=button.getAttribute('data-command');surface.focus();if(command==='createLink'){var url=window.prompt('Enter the link address','https://');if(url)document.execCommand(command,false,url.trim());}else{document.execCommand(command,false,null);}sync();});});var format=wrapper.querySelector('[data-rich-format]');if(format)format.addEventListener('change',function(){surface.focus();document.execCommand('formatBlock',false,'<'+format.value+'>');sync();});surface.addEventListener('input',sync);var form=wrapper.closest('form');if(form)form.addEventListener('submit',sync,true);sync();});}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();})();
</script>
@endpush
