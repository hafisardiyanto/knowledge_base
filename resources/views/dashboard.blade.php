@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link rel="stylesheet" href="{{ asset('css/project_cards.css') }}">
@endpush

@section('content')
    <div class="dashboard-header">
        <h2 style="font-size: 1.6rem; color: #111827; margin-bottom: 5px;">Dashboard</h2>
        <p>Kelola seluruh project dan buat Knowledge Base dari satu tempat.</p>
    </div>

    <div class="stats-grid">
        <!-- Stat 1 -->
        <div class="stat-card">
            <div class="stat-label">TOTAL PROJECT</div>
            <div class="stat-value">{{ $totalProjects }}</div>
        </div>

        <!-- Stat 2 -->
        <div class="stat-card">
            <div class="stat-label">TOTAL KNOWLEDGE BASE</div>
            <div class="stat-value">{{ $totalKB }}</div>
        </div>
    </div>

    <div class="dashboard-projects-bar">
        <div>
            <h2 style="font-size: 1.4rem; color: #111827; margin-bottom: 5px;">Semua Project</h2>
            <p style="color: #6b7280; font-size: 0.95rem;">Daftar project yang sudah dibuat.</p>
        </div>
        <a href="{{ route('projects.create') }}" class="btn-primary"
            style="background: #eff6ff; color: #1d4ed8; border: none; font-weight: 600; padding: 10px 20px; box-shadow: none;">+
            Project Baru</a>
    </div>

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
                    <button type="button" 
                        class="action-btn btn-link" 
                        onclick="openShareLinkModal('{{ route('kb.online', $p->slug) }}', 'Link KB: {{ addslashes($p->name) }}', 'Bagikan tautan Knowledge Base online untuk project {{ addslashes($p->name) }}.')" 
                        title="Buka & Salin Link Online KB">
                        🔗 Link KB
                    </button>
                    <a href="{{ route('kb.editor', $p->id) }}" class="action-btn primary">Kelola KB</a>
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