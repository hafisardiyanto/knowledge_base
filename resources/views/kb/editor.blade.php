@extends('layouts.app')

@push('styles')
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/editor.css') }}">
@endpush

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h2>📚 Kelola KB: {{ $project->name }}</h2>
        </div>
        <div style="display: flex; gap: 10px;">
            <form action="{{ route('kb.generate', $project->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary" style="background-color: #10b981;">👁️ Generate & Download
                    ZIP</button>
            </form>
            <button class="btn-primary" onclick="saveKB()">💾 Save KB</button>
        </div>
    </div>

    <div class="editor-layout">
        <div class="sidebar-categories">
            <div class="cat-header">
                <span>Kategori & Sub-menu</span>
                <button onclick="addCategory()"
                    style="background: none; border: none; font-size: 1.4rem; cursor: pointer; color: #4b5563; font-weight: bold;">+</button>
            </div>
            <div class="cat-list" id="category-list">
                @foreach($project->categories as $cat)
                    <div class="cat-group" data-id="{{ $cat->id }}">
                        <div class="cat-item">
                            <div class="cat-item-left" style="cursor: pointer;"
                                onclick="renameCategory({{ $cat->id }}, '{{ addslashes($cat->name) }}')"
                                title="Klik untuk ubah nama kategori">{{ $cat->name }}</div>
                            <div style="display: flex; gap: 10px;">
                                <div class="cat-item-right" onclick="addArticle({{ $cat->id }}, null)" title="Tambah Sub Menu">
                                    +📄</div>
                                <div class="cat-item-right" onclick="deleteCategory({{ $cat->id }})" style="color:#ef4444;"
                                    title="Hapus Kategori">🗑️</div>
                            </div>
                        </div>
                        <!-- Articles in Category -->
                        <div class="article-list">
                            @include('kb.article_node', ['articles' => $project->articles->where('category_id', $cat->id), 'parentId' => null, 'depth' => 0])
                            <div class="article-add-btn" onclick="addArticle({{ $cat->id }}, null)">+ Tambah Sub-menu lagi...
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="editor-main">
            <div class="editor-top">
                <input type="text" id="article-title" placeholder="Title Artikel" style="flex: 1;" readonly>
                <select id="article-type" disabled>
                    <option value="Dokumentasi">📄 Dokumentasi</option>
                    <option value="Tutorial">📖 Tutorial</option>
                    <option value="FAQ">❓ FAQ</option>
                    <option value="Troubleshooting">🛠️ Troubleshooting</option>
                </select>
            </div>
            <div id="editor-container" style="display: none;"></div>
            <div id="editor-placeholder" style="padding: 20px; color: #9ca3af; text-align: center;">Pilih artikel dari
                sidebar untuk diedit.</div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script>
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const projectId = {{ $project->id }};
            const routes = {
                saveCategory: "{{ route('kb.category.save', $project->id) }}",
                saveArticleInfo: "{{ route('kb.article.saveInfo', $project->id) }}",
                saveArticle: "{{ route('kb.save', $project->id) }}"
            };

            // DB Cache
            let articles = @json($project->articles->keyBy('id'));
            let currentArticleId = null;

            const quill = new Quill('#editor-container', {
                theme: 'snow',
                modules: {
                    imageResize: {
                        displaySize: true
                    },
                    toolbar: [
                        [{ 'header': [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        ['blockquote', 'code-block'],
                        ['link', 'image', 'video'],
                        [{ 'align': [] }],
                        ['clean']
                    ]
                }
            });

            var el = document.getElementById('category-list');
            Sortable.create(el, { animation: 150, ghostClass: 'sortable-ghost' });

            async function addCategory() {
                let name = prompt("Nama Kategori?");
                if (!name) return;

                let res = await fetch(routes.saveCategory, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ name: name })
                });
                if (res.ok) window.location.reload();
            }

            async function renameCategory(id, currentName) {
                let name = prompt("Ubah Nama Kategori:", currentName);
                if (!name || name === currentName) return;

                let res = await fetch(routes.saveCategory, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ name: name, category_id: id })
                });
                if (res.ok) window.location.reload();
            }

            async function addArticle(catId, parentId = null) {
                let name = prompt("Nama Artikel / Sub-menu?");
                if (!name) return;

                let res = await fetch(routes.saveArticleInfo, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({ title: name, category_id: catId, parent_id: parentId })
                });
                if (res.ok) {
                    window.location.reload();
                }
            }

            async function deleteCategory(id) {
                if (!confirm('Hapus kategori ini beserta seluru isinya?')) return;
                let res = await fetch(`{{ url('knowledge-base') }}/${projectId}/delete-category/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                if (res.ok) window.location.reload();
            }

            async function deleteArticle(id) {
                if (!confirm('Hapus sub-menu ini beserta seluruh isinya?')) return;
                let res = await fetch(`{{ url('knowledge-base') }}/${projectId}/delete-article/${id}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': csrfToken }
                });
                if (res.ok) window.location.reload();
            }


            function loadArticle(id) {
                if (!articles[id]) return;
                currentArticleId = id;

                document.getElementById('editor-placeholder').style.display = 'none';
                document.getElementById('editor-container').style.display = 'block';

                document.getElementById('article-title').value = articles[id].title;
                document.getElementById('article-title').readOnly = false;
                document.getElementById('article-type').value = articles[id].type;
                document.getElementById('article-type').disabled = false;

                // Reset and set quill html
                quill.root.innerHTML = articles[id].content || '';
            }

            async function saveKB() {
                if (!currentArticleId) {
                    alert('Pilih artikel dulu sebelum save!'); return;
                }

                let res = await fetch(routes.saveArticle, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        article_id: currentArticleId,
                        title: document.getElementById('article-title').value,
                        type: document.getElementById('article-type').value,
                        content: quill.root.innerHTML
                    })
                });

                if (res.ok) {
                    alert('Knowledge Base tersimpan!');
                    // Update local cache
                    articles[currentArticleId].title = document.getElementById('article-title').value;
                    articles[currentArticleId].type = document.getElementById('article-type').value;
                    articles[currentArticleId].content = quill.root.innerHTML;

                    // Note: to update the sidebar title text, a full refresh is easiest for prototype
                    window.location.reload();
                } else {
                    alert('Gagal menyimpan KB');
                }
            }
        </script>
    @endpush
@endsection