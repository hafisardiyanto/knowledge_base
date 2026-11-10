@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kb.css') }}?v={{ filemtime(public_path('css/kb.css')) }}">
@endpush

@section('content')
    <div class="kb-page-topbar">
        <div>
            <h2>📖 Knowledge Base</h2>
            <p style="color: #6b7280; font-size: 0.95rem;">Lihat seluruh KB dari semua project.</p>
        </div>
        <a href="{{ route('projects.index') }}" class="btn-primary kb-btn-create-link">+ Buat KB Baru (Pilih Project)</a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('kb.index') }}" class="kb-list-form">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Cari Knowledge Base (Title)"
                class="kb-search-input">

            <select name="project_id" class="kb-filter-select">
                <option value="">Semua Project ▼</option>
                @foreach($projects as $proj)
                    <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}
                    </option>
                @endforeach
            </select>

            <select name="category_id" class="kb-filter-select">
                <option value="">Semua Category ▼</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                    </option>
                @endforeach
            </select>

            <select name="type" class="kb-filter-select">
                <option value="">Semua Type ▼</option>
                <option value="Dokumentasi" {{ request('type') == 'Dokumentasi' ? 'selected' : '' }}>📄 Dokumentasi</option>
                <option value="Tutorial" {{ request('type') == 'Tutorial' ? 'selected' : '' }}>📖 Tutorial</option>
                <option value="FAQ" {{ request('type') == 'FAQ' ? 'selected' : '' }}>❓ FAQ</option>
                <option value="Troubleshooting" {{ request('type') == 'Troubleshooting' ? 'selected' : '' }}>🛠️
                    Troubleshooting</option>
            </select>

            <button type="submit" class="btn-primary kb-filter-btn">Filter</button>
        </form>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            @foreach($articles as $article)
                <div class="kb-article-card">
                    <div class="kb-article-header">
                        <div>
                            <h3 class="kb-article-title">{{ $article->title }}</h3>
                            <div class="kb-article-meta">
                                <span><strong>Project:</strong> {{ $article->project->name }}</span>
                                <span><strong>Category:</strong>
                                    {{ $article->category ? $article->category->name : 'Tanpa Kategori' }}</span>
                                <span><strong>Type:</strong> {{ $article->type }}</span>
                            </div>
                        </div>
                        <div class="kb-article-actions">
                            <button type="button" 
                                onclick="openShareLinkModal('{{ route('kb.online', $article->project->slug) }}#article-{{ $article->id }}', 'Link Artikel: {{ addslashes($article->title) }}', 'Bagikan tautan langsung ke artikel &quot;{{ addslashes($article->title) }}&quot; (Project: {{ addslashes($article->project->name) }}).')"
                                class="btn-primary" 
                                style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; cursor: pointer;"
                                title="Buka & Salin Link Online Artikel">
                                🔗 Link KB
                            </button>
                            <button type="button" 
                                onclick="openDuplicateModal({{ $article->id }}, '{{ addslashes($article->title) }}', {{ $article->project_id }}, {{ $article->category_id ?? 'null' }})"
                                class="btn-primary" 
                                style="background: #eef2ff; border: 1px solid #c7d2fe; color: #4f46e5; cursor: pointer;">
                                📋 Duplikat
                            </button>
                            <a href="{{ route('kb.editor', ['project' => $article->project_id, 'article_id' => $article->id]) }}"
                                class="btn-primary"
                                style="background: white; border: 1px solid #d1d5db; color: #374151;">Lihat</a>
                        </div>
                    </div>
                </div>
            @endforeach

            @if(count($articles) == 0)
                <div class="kb-empty-state">
                    <div class="kb-empty-icon">🔍</div>
                    <p>Tidak ada Knowledge Base ditemukan.</p>
                </div>
            @endif

            <div style="margin-top: 20px;">
                {{ $articles->links() }}
            </div>
        </div>
    </div>

    <!-- Duplicate Modal -->
    <div id="duplicate-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; justify-content: center; align-items: center;">
        <div style="background: white; border-radius: 8px; width: 450px; max-width: 90%; padding: 24px; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="margin: 0; font-size: 1.2rem; color: #1f2937;">📋 Duplikat Knowledge Base</h3>
                <span onclick="closeDuplicateModal()" style="cursor: pointer; font-size: 1.4rem; color: #9ca3af;">&times;</span>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 20px;">
                Duplikat artikel: <strong id="dup-article-title-display"></strong>
            </p>

            <form id="duplicate-form" onsubmit="submitDuplicate(event)">
                <input type="hidden" id="dup-article-id">
                <input type="hidden" id="dup-source-project-id">
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Pilih Project Tujuan:
                    </label>
                    <select id="dup-target-project" onchange="onDuplicateProjectChange()" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; outline: none; font-size: 0.9rem; background: white;">
                        @foreach($projects as $proj)
                            <option value="{{ $proj->id }}">
                                {{ $proj->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        Pilih Kategori Tujuan:
                    </label>
                    <select id="dup-target-category" style="width: 100%; padding: 8px 12px; border: 1px solid #d1d5db; border-radius: 6px; outline: none; font-size: 0.9rem; background: white;">
                        <!-- Populated by JS -->
                    </select>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="button" onclick="closeDuplicateModal()" style="padding: 8px 16px; border: 1px solid #d1d5db; background: white; border-radius: 6px; cursor: pointer; color: #4b5563;">
                        Batal
                    </button>
                    <button type="submit" id="btn-submit-duplicate" class="btn-primary" style="background-color: #6366f1;">
                        Duplikat Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            const projectsData = @json($projects);
            const csrfToken = "{{ csrf_token() }}";
            let currentSourceProjectId = null;
            let currentPendingCatId = null;

            function openDuplicateModal(articleId, title, sourceProjectId, categoryId) {
                document.getElementById('dup-article-id').value = articleId;
                document.getElementById('dup-source-project-id').value = sourceProjectId;
                document.getElementById('dup-article-title-display').textContent = title;
                currentSourceProjectId = sourceProjectId;
                currentPendingCatId = categoryId;

                const projSelect = document.getElementById('dup-target-project');
                projSelect.value = sourceProjectId;

                populateCategoryOptions(sourceProjectId, categoryId);

                const modal = document.getElementById('duplicate-modal');
                modal.style.display = 'flex';
            }

            function closeDuplicateModal() {
                const modal = document.getElementById('duplicate-modal');
                modal.style.display = 'none';
            }

            function onDuplicateProjectChange() {
                const projSelect = document.getElementById('dup-target-project');
                const selectedProjId = parseInt(projSelect.value);
                const defaultCat = (selectedProjId === currentSourceProjectId) ? currentPendingCatId : null;
                populateCategoryOptions(selectedProjId, defaultCat);
            }

            function populateCategoryOptions(projId, selectedCatId) {
                const catSelect = document.getElementById('dup-target-category');
                catSelect.innerHTML = '';

                const proj = projectsData.find(p => p.id == projId);
                const categories = (proj && proj.categories) ? proj.categories : [];

                if (categories.length === 0) {
                    let opt = document.createElement('option');
                    opt.value = '';
                    opt.textContent = '-- Tanpa Kategori --';
                    catSelect.appendChild(opt);
                    return;
                }

                categories.forEach(cat => {
                    let opt = document.createElement('option');
                    opt.value = cat.id;
                    opt.textContent = '📁 ' + cat.name;
                    if (selectedCatId && cat.id == selectedCatId) {
                        opt.selected = true;
                    }
                    catSelect.appendChild(opt);
                });
            }

            async function submitDuplicate(event) {
                event.preventDefault();
                const articleId = document.getElementById('dup-article-id').value;
                const sourceProjectId = document.getElementById('dup-source-project-id').value;
                const targetProjectId = document.getElementById('dup-target-project').value;
                const targetCategoryId = document.getElementById('dup-target-category').value;
                const submitBtn = document.getElementById('btn-submit-duplicate');

                submitBtn.disabled = true;
                submitBtn.textContent = 'Menduplikasi...';

                try {
                    let res = await fetch(`{{ url('knowledge-base') }}/${sourceProjectId}/duplicate-article/${articleId}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            target_project_id: targetProjectId,
                            target_category_id: targetCategoryId || null
                        })
                    });

                    let data = await res.json();
                    if (data.success) {
                        alert(data.message);
                        closeDuplicateModal();
                        if (confirm('Buka artikel hasil duplikat di Editor sekarang?')) {
                            window.location.href = `{{ url('knowledge-base') }}/${targetProjectId}/editor?article_id=${data.article.id}`;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert('Gagal menduplikat: ' + (data.message || 'Terjadi kesalahan'));
                    }
                } catch (err) {
                    alert('Gagal melakukan permintaan duplikasi.');
                } finally {
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Duplikat Sekarang';
                }
            }
        </script>
    @endpush
@endsection