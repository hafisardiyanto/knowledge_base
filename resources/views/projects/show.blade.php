@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/projects.css') }}">
@endpush

@section('content')
    <div class="project-detail-back-bar">
        <a href="{{ route('projects.index') }}" class="btn-back-nav" title="Kembali ke Daftar Project">
            <span class="back-arrow">←</span> Kembali ke Daftar Project
        </a>
    </div>

    <div class="card project-detail-card">
        <div class="project-header-info">
            <div class="project-brand-box">
                @if($project->icon)
                    <img src="{{ asset($project->icon) }}" class="project-icon-img"
                        onerror="this.style.display='none'">
                @else
                    <div class="project-icon-large">📁</div>
                @endif
                <div class="project-meta-info">
                    <h1 class="project-title">{{ $project->name }}</h1>
                    <p class="project-desc">{{ $project->description }}</p>
                    <span class="project-status-badge">
                        Status: {{ $project->status }}
                    </span>
                </div>
            </div>

            <div class="header-actions">
                <button type="button" 
                    class="btn-primary btn-action-green" 
                    onclick="openShareLinkModal('{{ route('kb.online', $project->slug) }}', 'Link KB: {{ addslashes($project->name) }}', 'Bagikan tautan Knowledge Base online untuk project {{ addslashes($project->name) }}.')">
                    🔗 Link KB
                </button>
                <a href="{{ route('kb.editor', $project->id) }}" class="btn-primary btn-action-purple">
                    📚 Kelola
                </a>
                <button type="button" class="btn-primary btn-action-indigo"
                    onclick="openDuplicateProjectModal()">
                    📋 Duplikat Project
                </button>
                <a href="{{ route('projects.edit', $project->id) }}" class="btn-primary btn-action-amber">
                    ✏️ Edit Project
                </a>
                <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
                    onsubmit="return confirm('Hapus project ini?');" class="detail-delete-form">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-primary btn-action-red">
                        🗑️ Hapus Project
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="project-stats-grid">
        <!-- Stat 1 -->
        <div class="card project-stat-card">
            <div class="project-stat-label">Total Knowledge Base</div>
            <div class="project-stat-number">{{ $project->total_kb }}</div>
        </div>

        <!-- Stat 2 -->
        <div class="card project-stat-card">
            <div class="project-stat-label">Total Category</div>
            <div class="project-stat-number">{{ $project->total_categories }}</div>
        </div>
    </div>

    <!-- Duplicate Project Modal -->
    <div id="duplicate-project-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 12px; width: 500px; max-width: 90%; padding: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="margin: 0; font-size: 1.25rem; color: #111827;">📋 Duplikat Project Knowledge Base</h3>
                <span onclick="closeDuplicateProjectModal()" style="cursor: pointer; font-size: 1.5rem; color: #9ca3af;">&times;</span>
            </div>
            
            <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 20px;">
                Semua kategori, sub-menu, dan seluruh artikel dari project ini akan disalin ke project baru. Anda tinggal mengubah nama dan isi teksnya sesuai kebutuhan.
            </p>

            <form id="duplicate-project-form" method="POST" action="{{ route('projects.duplicate', $project->id) }}">
                @csrf
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Nama Project Baru (KB B) *
                    </label>
                    <input type="text" name="name" value="{{ $project->name }} (Copy)" required
                        style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; font-size: 0.95rem; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Deskripsi Singkat *
                    </label>
                    <textarea name="description" rows="3" required
                        style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; font-size: 0.95rem; font-family: inherit; box-sizing: border-box;">{{ $project->description }}</textarea>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Status Project Baru
                    </label>
                    <select name="status"
                        style="width: 100%; padding: 10px 14px; border: 1px solid #d1d5db; border-radius: 8px; outline: none; font-size: 0.95rem; background: white; box-sizing: border-box;">
                        <option value="Active" {{ $project->status == 'Active' ? 'selected' : '' }}>Aktif</option>
                        <option value="Draft" {{ $project->status == 'Draft' ? 'selected' : '' }}>Draft (Tidak Aktif)</option>
                    </select>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeDuplicateProjectModal()"
                        style="padding: 10px 18px; border: 1px solid #d1d5db; background: white; border-radius: 8px; cursor: pointer; color: #4b5563; font-weight: 500;">
                        Batal
                    </button>
                    <button type="submit" class="btn-primary" style="background: #2563eb; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer;">
                        Duplikat Project
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openDuplicateProjectModal() {
            document.getElementById('duplicate-project-modal').style.display = 'flex';
        }

        function closeDuplicateProjectModal() {
            document.getElementById('duplicate-project-modal').style.display = 'none';
        }
    </script>
@endsection