@extends('layouts.admin')
@section('title', 'Edit ' . ucfirst($group) . ' Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">Edit {{ ucfirst($group) }} Settings</h3>
    <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="material-symbols-outlined fs-16 align-middle">arrow_back</i> Back
    </a>
</div>

<div class="card border-0 rounded-3">
    <div class="card-body p-4">
        <form action="{{ route('admin.settings.update', $group) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            @if($group === 'remita')
            <div class="card border-success-subtle bg-light mb-4"><div class="card-body">
                <div class="d-flex justify-content-between align-items-start gap-3"><div><h5 class="mb-1">Remita split payments</h5><p class="text-muted small mb-3">When enabled, the configured additional charge is added to the Remita amount and allocated to the three beneficiary accounts. Student receipts continue to show only the base fee.</p></div><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="remita_split_enabled" value="1" @checked($remitaSplitSettings['enabled'] ?? false)><label class="form-check-label">Enable</label></div></div>
                <div class="row g-3">
                @foreach(($remitaSplitSettings['beneficiaries'] ?? []) as $i => $b)
                    <div class="col-lg-4"><div class="border rounded-3 p-3 bg-white h-100"><h6>{{ $i === 0 ? 'Primary account' : 'Additional account '.$i }}</h6><input class="form-control form-control-sm mb-2" name="remita_beneficiaries[{{ $i }}][label]" placeholder="Account name" value="{{ old('remita_beneficiaries.'.$i.'.label',$b['label']??'') }}"><input class="form-control form-control-sm mb-2" name="remita_beneficiaries[{{ $i }}][account_number]" placeholder="Account number" inputmode="numeric" value="{{ old('remita_beneficiaries.'.$i.'.account_number',$b['account_number']??'') }}"><input class="form-control form-control-sm mb-2" name="remita_beneficiaries[{{ $i }}][bank_code]" placeholder="Bank code" inputmode="numeric" value="{{ old('remita_beneficiaries.'.$i.'.bank_code',$b['bank_code']??'') }}"><div class="input-group input-group-sm"><input class="form-control" type="number" min="0" max="100" step="0.01" name="remita_beneficiaries[{{ $i }}][percentage]" value="{{ old('remita_beneficiaries.'.$i.'.percentage',$b['percentage']??0) }}"><span class="input-group-text">%</span></div></div></div>
                @endforeach
                </div>
            </div></div>
            @endif

            @foreach($settings as $setting)
            <div class="mb-4">
                <label for="{{ $setting->key }}" class="form-label fw-medium">
                    {{ ucwords(str_replace('_', ' ', $setting->key)) }}
                    @if($setting->description)
                        <small class="text-muted d-block">{{ $setting->description }}</small>
                    @endif
                </label>

                @if($setting->type === 'text')
                    <input type="text" class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}">
                @elseif($setting->type === 'textarea')
                    <textarea class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" rows="3">{{ $setting->value }}</textarea>
                @elseif($setting->type === 'richtext')
                    <div class="sbrs-rich-editor" data-rich-editor-wrapper>
                        <div class="sbrs-rich-toolbar" role="toolbar" aria-label="Text formatting tools">
                            <select class="sbrs-rich-format" data-rich-format aria-label="Text style">
                                <option value="p">Paragraph</option><option value="h2">Heading</option><option value="h3">Small heading</option>
                            </select><span class="sbrs-rich-divider"></span>
                            <button type="button" data-command="bold" title="Bold"><i class="material-symbols-outlined">format_bold</i></button><button type="button" data-command="italic" title="Italic"><i class="material-symbols-outlined">format_italic</i></button><button type="button" data-command="underline" title="Underline"><i class="material-symbols-outlined">format_underlined</i></button><button type="button" data-command="strikeThrough" title="Strikethrough"><i class="material-symbols-outlined">format_strikethrough</i></button><span class="sbrs-rich-divider"></span>
                            <button type="button" data-command="justifyLeft" title="Align left"><i class="material-symbols-outlined">format_align_left</i></button><button type="button" data-command="justifyCenter" title="Align center"><i class="material-symbols-outlined">format_align_center</i></button><button type="button" data-command="justifyRight" title="Align right"><i class="material-symbols-outlined">format_align_right</i></button><button type="button" data-command="justifyFull" title="Justify"><i class="material-symbols-outlined">format_align_justify</i></button><span class="sbrs-rich-divider"></span>
                            <button type="button" data-command="insertUnorderedList" title="Bulleted list"><i class="material-symbols-outlined">format_list_bulleted</i></button><button type="button" data-command="insertOrderedList" title="Numbered list"><i class="material-symbols-outlined">format_list_numbered</i></button><button type="button" data-command="createLink" title="Insert link"><i class="material-symbols-outlined">link</i></button><button type="button" data-command="removeFormat" title="Clear formatting"><i class="material-symbols-outlined">format_clear</i></button><span class="sbrs-rich-divider"></span><button type="button" data-command="undo" title="Undo"><i class="material-symbols-outlined">undo</i></button><button type="button" data-command="redo" title="Redo"><i class="material-symbols-outlined">redo</i></button>
                        </div>
                        <div class="sbrs-rich-surface" data-rich-surface contenteditable="true" role="textbox" aria-multiline="true" spellcheck="true">{!! $setting->value !!}</div>
                        <textarea class="d-none" name="{{ $setting->key }}" data-rich-source>{{ $setting->value }}</textarea>
                    </div>
                    <small class="text-muted d-block mt-2"><i class="material-symbols-outlined align-middle fs-16 me-1">edit</i>Use headings, emphasis, lists, links and left, centre, right or justified alignment.</small>
                @elseif($setting->type === 'boolean')
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="{{ $setting->key }}" name="{{ $setting->key }}" {{ $setting->value ? 'checked' : '' }}>
                        <label class="form-check-label" for="{{ $setting->key }}">{{ $setting->value ? 'Enabled' : 'Disabled' }}</label>
                    </div>
                @elseif($setting->type === 'number')
                    <input type="number" class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}">
                @elseif($setting->type === 'image' || $setting->type === 'file')
                    @if($setting->value)
                        <div class="mb-2">
                            @if($setting->type === 'image')
                                <img src="{{ asset('storage/' . $setting->value) }}" alt="{{ $setting->key }}" class="rounded" style="max-height:100px;">
                            @else
                                <span class="badge bg-success">File uploaded</span>
                            @endif
                        </div>
                    @endif
                    <input type="file" class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" {{ $setting->type === 'image' ? 'accept=image/*' : '' }}>
                @elseif($setting->type === 'select')
                    <select class="form-select" id="{{ $setting->key }}" name="{{ $setting->key }}">
                        @foreach(explode(',', $setting->options ?? '') as $opt)
                            <option value="{{ trim($opt) }}" {{ $setting->value === trim($opt) ? 'selected' : '' }}>{{ trim($opt) }}</option>
                        @endforeach
                    </select>
                @else
                    <input type="text" class="form-control" id="{{ $setting->key }}" name="{{ $setting->key }}" value="{{ $setting->value }}">
                @endif
            </div>
            @endforeach

            <button type="submit" class="btn btn-primary" style="background:#006633;border-color:#006633;">
                <i class="material-symbols-outlined fs-16 align-middle">save</i> Save Settings
            </button>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
.sbrs-rich-editor{overflow:hidden;border:1px solid #d6e2eb;border-radius:.5rem;background:#fff}.sbrs-rich-toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:.2rem;padding:.45rem;border-bottom:1px solid #d6e2eb;background:#f7fafc}.sbrs-rich-toolbar button,.sbrs-rich-format{display:inline-flex;align-items:center;justify-content:center;min-width:2.1rem;height:2rem;border:1px solid transparent;border-radius:.3rem;background:transparent;color:#39566b;cursor:pointer}.sbrs-rich-toolbar button .material-symbols-outlined{font-size:18px;line-height:1;color:inherit}.sbrs-rich-toolbar button:hover,.sbrs-rich-toolbar button:focus,.sbrs-rich-format:focus{border-color:#8fc5e3;background:#eaf6fd;color:#126d9f;outline:none}.sbrs-rich-format{min-width:8.5rem;justify-content:flex-start;padding:0 .45rem;background:#fff}.sbrs-rich-divider{width:1px;height:1.4rem;margin:0 .2rem;background:#d6e2eb}.sbrs-rich-surface{min-height:180px;padding:.85rem 1rem;color:#243b4d;line-height:1.6;outline:none}.sbrs-rich-surface:focus{box-shadow:inset 0 0 0 2px rgba(0,102,51,.15)}.sbrs-rich-surface p,.sbrs-rich-surface h2,.sbrs-rich-surface h3,.sbrs-rich-surface h4,.sbrs-rich-surface blockquote{margin:0 0 .55rem}.sbrs-rich-surface ul,.sbrs-rich-surface ol{margin:.25rem 0 .65rem;padding-left:1.4rem}.sbrs-rich-surface blockquote{border-left:3px solid #b7dec5;padding-left:.8rem;color:#587284}@media(max-width:576px){.sbrs-rich-toolbar{gap:.1rem}.sbrs-rich-toolbar button{min-width:1.95rem}}
</style>
@endpush
@push('scripts')
<script>
(function(){function init(){document.querySelectorAll('[data-rich-editor-wrapper]').forEach(function(wrapper){var surface=wrapper.querySelector('[data-rich-surface]'),source=wrapper.querySelector('[data-rich-source]');if(!surface||!source)return;var sync=function(){source.value=surface.innerHTML;};wrapper.querySelectorAll('[data-command]').forEach(function(button){button.addEventListener('mousedown',function(e){e.preventDefault();});button.addEventListener('click',function(){var command=button.getAttribute('data-command');surface.focus();if(command==='createLink'){var url=window.prompt('Enter the link address','https://');if(url)document.execCommand(command,false,url.trim());}else{document.execCommand(command,false,null);}sync();});});var format=wrapper.querySelector('[data-rich-format]');if(format)format.addEventListener('change',function(){surface.focus();document.execCommand('formatBlock',false,'<'+format.value+'>');sync();});surface.addEventListener('input',sync);var form=wrapper.closest('form');if(form)form.addEventListener('submit',sync,true);sync();});}if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();})();
</script>
@endpush
