@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/projects.css') }}">
    <link rel="stylesheet" href="{{ asset('css/project_cards.css') }}">
@endpush

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div>
            <p style="color: #6b7280; font-size: 0.95rem; margin-bottom: 20px;">Kelola seluruh project dan buat Knowledge
                Base dari satu tempat.</p>
            <h2 style="font-size: 1.6rem; color: #111827;">Semua Project</h2>
            <p style="color: #6b7280; font-size: 0.95rem;">Daftar project yang sudah dibuat.</p>
        </div>

        <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 20px;">
            <a href="{{ route('projects.create') }}" class="btn-primary"
                style="background: #eff6ff; color: #1d4ed8; border: none; font-weight: 600; padding: 10px 20px; box-shadow: none;">+
                Project Baru</a>

            <form method="GET" action="{{ route('projects.index') }}" style="display: flex; gap: 10px;">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari project..."
                    style="padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; width: 250px; outline: none; font-size: 0.9rem;">
                <select name="status"
                    style="padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 8px; background: white; outline: none; font-size: 0.9rem;"
                    onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="Draft" {{ request('status') == 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                </select>
            </form>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="project-grid">
        @foreach($projects as $p)
            <div class="project-card">
                <div class="project-card-header">
                    <div style="display: flex; gap: 15px; align-items: flex-start;">
                        @if($p->icon)
                            <img src="{{ asset($p->icon) }}"
                                style="width: 45px; height: 45px; border-radius: 8px; object-fit: cover;">
                        @endif
                        <div class="project-title">{{ $p->name }}</div>
                    </div>
                    <div class="project-status status-{{ $p->status }}">
                        <div class="status-dot"></div>
                        {{ $p->status }}
                    </div>
                </div>

                <div class="project-desc">{{ $p->description }}</div>

                <div class="project-stats">
                    <div class="stat-box">
                        <div class="stat-label-card">KB Item</div>
                        <div class="stat-value-card">{{ $p->total_kb }}</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-label-card">Update</div>
                        <div class="stat-value-card">{{ $p->updated_at->format('d M Y') }}</div>
                    </div>
                </div>

                <div class="project-actions">
                    <a href="{{ route('projects.show', $p->id) }}" class="action-btn">Detail</a>
                    <a href="{{ route('projects.edit', $p->id) }}" class="action-btn">Edit</a>
                    <form action="{{ route('projects.destroy', $p->id) }}" method="POST"
                        onsubmit="return confirm('Hapus project ini?');" style="margin: 0;">
                        @csrf @method('DELETE')
                        <button type="submit" class="action-btn" style="cursor:pointer;">Hapus</button>
                    </form>
                    <a href="{{ route('kb.editor', $p->id) }}" class="action-btn primary">Kelola</a>
                </div>
            </div>
        @endforeach

        @if(count($projects) == 0)
            <div
                style="grid-column: 1 / -1; padding: 40px; text-align: center; color: #9ca3af; background: white; border: 1px solid #e5e7eb; border-radius: 12px;">
                Tidak ada project ditemukan.
            </div>
        @endif
    </div>

@endsection