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
