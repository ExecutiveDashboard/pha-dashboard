@extends('layouts.app')
@section('title', 'Settings')
@section('page-title', 'Dashboard Settings & Criteria')

@section('content')
<form method="POST" action="{{ route('settings.update') }}">
    @csrf
    @method('POST')

    @foreach($settings as $group => $groupSettings)
    <div class="chart-card mb-4">
        <h6 class="mb-1" style="text-transform:capitalize;">
            <i class="bi bi-gear-fill me-2" style="color:#1B6B35;"></i>{{ ucfirst($group) }} Settings
        </h6>
        <p class="chart-sub">Configure {{ $group }} parameters</p>
        <div class="row g-3">
            @foreach($groupSettings as $setting)
            <div class="col-md-4">
                <label class="form-label" style="font-size:13px;font-weight:600;">{{ $setting->label }}</label>
                <input type="{{ $setting->type === 'number' ? 'number' : 'text' }}"
                       step="any"
                       name="{{ $setting->key }}"
                       class="form-control"
                       value="{{ $setting->value }}">
                <div style="font-size:11px;color:#94a3b8;margin-top:4px;">Key: <code>{{ $setting->key }}</code></div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="d-flex gap-2">
        <button type="submit" class="btn" style="background:#1B6B35;color:#fff;font-weight:600;">
            <i class="bi bi-check-circle me-2"></i>Save All Settings
        </button>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection
