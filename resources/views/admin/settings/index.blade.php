@extends('layouts.admin')
@section('title', 'Settings')

@section('content')
<div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
    <h3 class="fw-semibold mb-0">System Settings</h3>
    <form action="{{ route('admin.settings.clear-cache') }}" method="POST" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-outline-warning btn-sm">
            <i class="material-symbols-outlined fs-16 align-middle">cached</i> Clear Cache
        </button>
    </form>
</div>

<div class="row">
    @forelse($settings as $group => $items)
    <div class="col-md-6 col-lg-4 mb-4">
        <div class="card border-0 rounded-3 h-100">
            <div class="card-body p-4">
                @php
                    $groupIcons = [
                        'general' => 'tune',
                        'contact' => 'contact_mail',
                        'ijmb' => 'school',
                        'remedial' => 'menu_book',
                        'remita' => 'payment',
                        'footer' => 'copyright',
                        'application' => 'description',
                    ];
                    $groupColors = [
                        'general' => 'primary',
                        'contact' => 'info',
                        'ijmb' => 'success',
                        'remedial' => 'warning',
                        'remita' => 'danger',
                        'footer' => 'secondary',
                        'application' => 'danger',
                    ];
                    $icon = $groupIcons[$group] ?? 'settings';
                    $color = $groupColors[$group] ?? 'primary';
                @endphp
                <div class="d-flex align-items-center mb-3">
                    <div class="wh-45 bg-{{ $color }} bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3">
                        <i class="material-symbols-outlined text-{{ $color }}">{{ $icon }}</i>
                    </div>
                    <div>
                        <h5 class="fw-semibold mb-0">{{ ucfirst($group) }}</h5>
                        <small class="text-muted">{{ $items->count() }} settings</small>
                    </div>
                </div>
                <ul class="list-unstyled mb-3">
                    @foreach($items->take(4) as $item)
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <small class="text-muted">{{ ucwords(str_replace('_', ' ', $item->key)) }}</small>
                        <small class="fw-medium text-truncate ms-2" style="max-width:120px;">
                            @if($item->type === 'image')
                                @if($item->value) <span class="text-success">Uploaded</span> @else <span class="text-muted">Not set</span> @endif
                            @else
                                {{ Str::limit($item->value ?? 'Not set', 20) }}
                            @endif
                        </small>
                    </li>
                    @endforeach
                    @if($items->count() > 4)
                    <li class="text-muted small pt-1">+{{ $items->count() - 4 }} more...</li>
                    @endif
                </ul>
                <a href="{{ route('admin.settings.edit', $group) }}" class="btn btn-primary btn-sm w-100" style="background:#006633;border-color:#006633;">
                    Edit {{ ucfirst($group) }} Settings
                </a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card border-0 rounded-3">
            <div class="card-body text-center py-5">
                <i class="material-symbols-outlined text-muted" style="font-size:3rem;">settings</i>
                <p class="text-muted mt-2">No settings configured yet. Run the seeder to add default settings.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>
@endsection
