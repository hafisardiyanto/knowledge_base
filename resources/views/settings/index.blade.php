@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/settings.css') }}?v={{ filemtime(public_path('css/settings.css')) }}">
@endpush

@section('content')
    <div style="margin-bottom: 30px;">
        <h2>⚙️ Settings</h2>
        <p style="color: #6b7280; font-size: 0.95rem;">Pengaturan aplikasi KB Generator.</p>
    </div>

    @if(session('success'))
        <div style="background: #dcfce7; color: #166534; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('settings.update') }}" method="POST">
        @csrf

        <div class="settings-grid">

            <!-- General Settings -->
            <div class="card">
                <h3 class="settings-section-title">General</h3>

                <div class="settings-form-group">
                    <label class="settings-label">Nama Aplikasi</label>
                    <input type="text" name="app_name" value="{{ $settings['app_name'] ?? 'KB Generator' }}"
                        class="settings-input">
                </div>

                <div class="settings-form-group">
                    <label class="settings-label">Timezone</label>
                    <select name="timezone" class="settings-select">
                        <option value="Asia/Jakarta" {{ ($settings['timezone'] ?? '') == 'Asia/Jakarta' ? 'selected' : '' }}>
                            Asia/Jakarta (WIB)</option>
                        <option value="UTC" {{ ($settings['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>UTC</option>
                    </select>
                </div>
            </div>

            <!-- Editor Settings -->
            <div class="card">
                <h3 class="settings-section-title">Editor & File</h3>

                <div class="settings-form-group">
                    <label class="settings-label">Default Article Type</label>
                    <select name="default_article_type" class="settings-select">
                        <option value="Dokumentasi">📄 Dokumentasi</option>
                        <option value="Tutorial">📖 Tutorial</option>
                    </select>
                </div>

                <div class="settings-form-group">
                    <label class="settings-label">Maximum Upload Size (MB)</label>
                    <input type="number" name="max_upload_size" value="{{ $settings['max_upload_size'] ?? '5' }}"
                        class="settings-input">
                </div>
            </div>

        </div>

        <div class="settings-footer">
            <button type="submit" class="btn-primary" style="padding: 12px 24px; font-size: 1.05rem;">💾 Simpan
                Pengaturan</button>
        </div>
    </form>
@endsection