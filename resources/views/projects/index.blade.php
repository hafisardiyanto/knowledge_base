@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/projects.css') }}?v={{ filemtime(public_path('css/projects.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/project_cards.css') }}?v={{ filemtime(public_path('css/project_cards.css')) }}">
@endpush

@section('content')
    <div class="projects-page-topbar">
        <div>
            <p style="color: #6b7280; font-size: 0.95rem; margin-bottom: 20px;">Kelola seluruh project dan buat Knowledge
                Base dari satu tempat.</p>
            <h2 style="font-size: 1.6rem; color: #111827;">Semua Project</h2>
            <p style="color: #6b7280; font-size: 0.95rem;">Daftar project yang sudah dibuat.</p>
        </div>

        <div class="projects-page-controls">
            <a href="{{ route('projects.create') }}" class="btn-primary"
                style="background: #eff6ff; color: #1d4ed8; border: none; font-weight: 600; padding: 10px 20px; box-shadow: none;">+
                Project Baru</a>

            <form method="GET" action="{{ route('projects.index') }}" class="projects-filter-form">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari project..."
                    class="projects-search-input">
                <select name="status"
                    class="projects-select-input"
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

    @if(session('error'))
        <div class="alert-error">
            {{ session('error') }}
        </div>
    @endif

    <div class="project-grid">
        @foreach($projects as $p)
            <div class="project-card">
                <div class="project-card-header">
                    <span class="project-name">{{ $p->name }}</span>
                    <span class="project-status">
                        <span class="status-indicator {{ $p->status == 'Active' ? 'active' : 'draft' }}"></span>
                        {{ $p->status }}
                    </span>
                </div>

                <div class="project-desc">{{ $p->description }}</div>

                <div class="project-stats-row">
                    <div class="stat-box">
                        <span class="stat-label">KB Item</span>
                        <span class="stat-value">{{ $p->articles_count ?? 0 }}</span>
                    </div>
                    <div class="stat-box">
                        <span class="stat-label">Update</span>
                        <span class="stat-value">{{ $p->updated_at->format('d M Y') }}</span>
                    </div>
                </div>

                <div class="project-actions">
                    <div class="actions-left">
                        <a href="{{ route('projects.show', $p->id) }}" class="btn-action">Detail</a>
                        <a href="{{ route('projects.edit', $p->id) }}" class="btn-action">Edit</a>
                        <button type="button" 
                            class="btn-action btn-link-action" 
                            onclick="openShareLinkModal('{{ route('kb.online', $p->slug) }}', 'Link KB: {{ addslashes($p->name) }}', 'Bagikan tautan Knowledge Base online untuk project {{ addslashes($p->name) }}.')"
                            title="Buka & Salin Link Online KB">
                            🔗 Link KB
                        </button>
                        <button type="button" class="btn-action"
                            onclick="openDuplicateProjectModal({{ $p->id }}, '{{ addslashes($p->name) }}', '{{ addslashes($p->description) }}', '{{ $p->status }}')">
                            📋 Duplikat
                        </button>
                        <form action="{{ route('projects.destroy', $p->id) }}" method="POST"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus project ini beserta seluruh datanya?')" style="margin: 0; display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action btn-danger-action">Hapus</button>
                        </form>
                    </div>
                    <a href="{{ route('kb.editor', $p->id) }}" class="btn-manage">Kelola</a>
                </div>
            </div>
        @endforeach

        @if($projects->isEmpty())
            <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #6b7280;">
                Tidak ada project ditemukan.
            </div>
        @endif
    </div>

    <!-- Duplicate Project Modal -->
    <div id="duplicate-project-modal" class="project-modal-overlay" style="display: none;">
        <div class="project-modal-card">
            <div class="project-modal-header">
                <h3 class="project-modal-title">📋 Duplikat Project Knowledge Base</h3>
                <span onclick="closeDuplicateProjectModal()" class="project-modal-close">&times;</span>
            </div>
            
            <p class="project-modal-desc">
                Semua kategori, sub-menu, dan seluruh artikel dari project sumber akan disalin ke project baru ini. Anda tinggal mengubah isi dan namanya sesuai kebutuhan.
            </p>

            <form id="duplicate-project-form" method="POST" action="">
                @csrf
                <div class="project-modal-field">
                    <label class="project-modal-label">
                        Nama Project Baru (KB B) *
                    </label>
                    <input type="text" name="name" id="dup-proj-name" class="project-modal-input" required>
                </div>

                <div class="project-modal-field">
                    <label class="project-modal-label">
                        Deskripsi Singkat *
                    </label>
                    <textarea name="description" id="dup-proj-desc" rows="3" class="project-modal-textarea" required></textarea>
                </div>

                <div class="project-modal-field" style="margin-bottom: 24px;">
                    <label class="project-modal-label">
                        Status Project Baru
                    </label>
                    <select name="status" id="dup-proj-status" class="project-modal-select">
                        <option value="Active">Aktif</option>
                        <option value="Draft">Draft (Tidak Aktif)</option>
                    </select>
                </div>

                <div class="project-modal-actions">
                    <button type="button" onclick="closeDuplicateProjectModal()" class="project-modal-btn-cancel">
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
        function openDuplicateProjectModal(id, name, desc, status) {
            const form = document.getElementById('duplicate-project-form');
            form.action = `{{ url('projects') }}/${id}/duplicate`;

            document.getElementById('dup-proj-name').value = `${name} (Copy)`;
            document.getElementById('dup-proj-desc').value = desc;
            document.getElementById('dup-proj-status').value = status;

            const modal = document.getElementById('duplicate-project-modal');
            modal.style.display = 'flex';
        }

        function closeDuplicateProjectModal() {
            document.getElementById('duplicate-project-modal').style.display = 'none';
        }
    </script>
@endsection
