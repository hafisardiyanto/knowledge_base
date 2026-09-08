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
            <input type="file" id="docx-upload" accept=".docx" style="display: none;">

            <button type="button" class="btn-primary" style="background-color: #3b82f6;" onclick="triggerImport()">
                📥 Import Word (.docx)
            </button>
            <button class="btn-primary" style="background-color: #f59e0b;" onclick="publishAll()">🌐 Publish All</button>
            <a href="{{ route('kb.online', $project->slug) }}" onclick="openOnlineKb(event)" target="_blank"
                class="btn-primary"
                style="background-color: #6366f1; text-decoration: none; display: flex; align-items: center;">🌍 Buka Online
                KB</a>
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
                <a id="btn-preview" href="#" target="_blank"
                    style="display: none; padding: 8px 12px; border-radius: 6px; background-color: #3b82f6; color: white; text-decoration: none; font-size: 0.9rem; margin-left: 10px;">👁️
                    Preview</a>
                <button id="btn-publish" onclick="togglePublish()" class="btn-primary"
                    style="display: none; margin-left: 5px;">▶️ Publish</button>
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
        <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.21/mammoth.browser.min.js"></script>
        <script>
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const projectId = {{ $project->id }};
            const routes = {
                saveCategory: "{{ route('kb.category.save', $project->id) }}",
                saveArticleInfo: "{{ route('kb.article.saveInfo', $project->id) }}",
                saveArticle: "{{ route('kb.save', $project->id) }}",
                importDocx: "{{ route('kb.import', $project->id) }}"
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
                        [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
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

            /**
                     * ============================================================
                     * IMPORT WORD (.DOCX)
                     * ============================================================
                     */

            function triggerImport() {

                const input = document.getElementById('docx-upload');

                if (!input) {
                    alert('Input file Word tidak ditemukan.');
                    return;
                }

                input.value = '';

                input.click();
            }


            /**
             * ============================================================
             * FILE SELECTED
             * ============================================================
             */

            document
                .getElementById('docx-upload')
                .addEventListener('change', function (event) {

                    const file = event.target.files[0];

                    if (!file) {
                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | VALIDATE EXTENSION
                    |--------------------------------------------------------------------------
                    */

                    const fileName = file.name.toLowerCase();

                    if (!fileName.endsWith('.docx')) {

                        alert('Silakan pilih file Microsoft Word (.docx).');

                        event.target.value = '';

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | READ FILE
                    |--------------------------------------------------------------------------
                    */

                    const reader = new FileReader();

                    reader.onload = function (loadEvent) {

                        const arrayBuffer = loadEvent.target.result;

                        importWordDocument(arrayBuffer);
                    };

                    reader.onerror = function () {

                        alert('Gagal membaca file Word.');
                    };

                    reader.readAsArrayBuffer(file);
                });


            /**
             * ============================================================
             * CONVERT DOCX → HTML
             * ============================================================
             */

            function importWordDocument(arrayBuffer) {

                /*
                |--------------------------------------------------------------------------
                | IMPORTANT
                |--------------------------------------------------------------------------
                | Memastikan Heading Word dibaca sebagai H1-H6.
                */

                mammoth.convertToHtml({

                    arrayBuffer: arrayBuffer,

                    styleMap: [

                        "p[style-name='Heading 1'] => h1:fresh",

                        "p[style-name='Heading 2'] => h2:fresh",

                        "p[style-name='Heading 3'] => h3:fresh",

                        "p[style-name='Heading 4'] => h4:fresh",

                        "p[style-name='Heading 5'] => h5:fresh",

                        "p[style-name='Heading 6'] => h6:fresh"

                    ]

                })
                    .then(function (result) {

                        console.log(
                            'HTML hasil Mammoth:',
                            result.value
                        );

                        console.log(
                            'Warning Mammoth:',
                            result.messages
                        );

                        displayResult(result);
                    })
                    .catch(function (error) {

                        console.error(
                            'Mammoth error:',
                            error
                        );

                        alert(
                            'Gagal membaca file Word.\n\n' +
                            error.message
                        );
                    });
            }


            /**
             * ============================================================
             * PARSE HTML
             * ============================================================
             */

            function displayResult(result) {

                const html = result.value;

                const tempDiv = document.createElement('div');

                tempDiv.innerHTML = html;


                /*
                |--------------------------------------------------------------------------
                | TREE
                |--------------------------------------------------------------------------
                */

                const tree = [];

                let currentCategory = null;


                /*
                |--------------------------------------------------------------------------
                | HEADING STACK
                |--------------------------------------------------------------------------
                |
                | Menyimpan heading aktif berdasarkan level.
                |
                | H2 → index 2
                | H3 → index 3
                | H4 → index 4
                | H5 → index 5
                | H6 → index 6
                |
                */

                const headingStack = {};


                /*
                |--------------------------------------------------------------------------
                | TOC / DAFTAR ISI MODE
                |--------------------------------------------------------------------------
                */

                let skipTableOfContents = false;


                /*
                |--------------------------------------------------------------------------
                | PROCESS EVERY ELEMENT
                |--------------------------------------------------------------------------
                */

                Array
                    .from(tempDiv.children)
                    .forEach(function (el) {

                        const tag = el.tagName.toLowerCase();

                        const text = (
                            el.textContent || ''
                        ).trim();


                        /*
                        |--------------------------------------------------------------------------
                        | EMPTY ELEMENT
                        |--------------------------------------------------------------------------
                        */

                        if (
                            text === '' &&
                            !el.querySelector('img, table')
                        ) {
                            return;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | DETECT HEADING
                        |--------------------------------------------------------------------------
                        */

                        const headingMatch = tag.match(/^h([1-6])$/);


                        /*
                        |--------------------------------------------------------------------------
                        | DETECT DAFTAR ISI
                        |--------------------------------------------------------------------------
                        */

                        if (
                            (
                                headingMatch ||
                                tag === 'p'
                            ) &&
                            text
                                .toLowerCase()
                                .replace(/\s+/g, ' ')
                                .trim() === 'daftar isi'
                        ) {

                            skipTableOfContents = true;

                            return;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | SKIP TABLE OF CONTENTS
                        |--------------------------------------------------------------------------
                        |
                        | Kita terus skip sampai menemukan H1 berikutnya.
                        |
                        */

                        if (skipTableOfContents) {

                            if (
                                headingMatch &&
                                parseInt(headingMatch[1]) === 1 &&
                                text !== '' &&
                                text.toLowerCase() !== 'daftar isi'
                            ) {

                                skipTableOfContents = false;

                            } else {

                                return;
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | PROCESS HEADING
                        |--------------------------------------------------------------------------
                        */

                        if (headingMatch) {

                            const level = parseInt(
                                headingMatch[1]
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | IGNORE EMPTY HEADING
                            |--------------------------------------------------------------------------
                            */

                            if (!text) {
                                return;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | H1 = CATEGORY
                            |--------------------------------------------------------------------------
                            */

                            if (level === 1) {

                                currentCategory = {
                                    name: text,
                                    articles: []
                                };

                                tree.push(currentCategory);


                                /*
                                |--------------------------------------------------------------------------
                                | RESET HEADING STACK
                                |--------------------------------------------------------------------------
                                */

                                Object
                                    .keys(headingStack)
                                    .forEach(function (key) {

                                        delete headingStack[key];
                                    });


                                return;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | Jika belum menemukan H1
                            |--------------------------------------------------------------------------
                            */

                            if (!currentCategory) {

                                console.warn(
                                    'Heading ditemukan sebelum H1:',
                                    text
                                );

                                return;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | CREATE ARTICLE
                            |--------------------------------------------------------------------------
                            */

                            const article = {

                                title: text,

                                content: '',

                                level: level,

                                children: []
                            };


                            /*
                            |--------------------------------------------------------------------------
                            | FIND PARENT
                            |--------------------------------------------------------------------------
                            |
                            | Misalnya:
                            |
                            | H2
                            |   H3
                            |     H4
                            |
                            | H4 akan mempunyai parent H3.
                            |
                            */

                            let parentArticle = null;


                            for (
                                let parentLevel = level - 1;
                                parentLevel >= 2;
                                parentLevel--
                            ) {

                                if (
                                    headingStack[parentLevel]
                                ) {

                                    parentArticle =
                                        headingStack[parentLevel];

                                    break;
                                }
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | ROOT ARTICLE
                            |--------------------------------------------------------------------------
                            */

                            if (!parentArticle) {

                                currentCategory
                                    .articles
                                    .push(article);

                            }

                            /*
                            |--------------------------------------------------------------------------
                            | CHILD ARTICLE
                            |--------------------------------------------------------------------------
                            */

                            else {

                                parentArticle
                                    .children
                                    .push(article);
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | SAVE ACTIVE HEADING
                            |--------------------------------------------------------------------------
                            */

                            headingStack[level] = article;


                            /*
                            |--------------------------------------------------------------------------
                            | REMOVE CHILDREN LEVEL
                            |--------------------------------------------------------------------------
                            |
                            | Contoh:
                            |
                            | H3 selesai
                            |
                            | H4/H5/H6 sebelumnya tidak boleh digunakan lagi.
                            |
                            */

                            for (
                                let childLevel = level + 1;
                                childLevel <= 6;
                                childLevel++
                            ) {

                                delete headingStack[childLevel];
                            }


                            return;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | NORMAL CONTENT
                        |--------------------------------------------------------------------------
                        */

                        let currentArticle = null;


                        /*
                        |--------------------------------------------------------------------------
                        | Cari article paling dalam
                        |--------------------------------------------------------------------------
                        */

                        for (
                            let level = 6;
                            level >= 2;
                            level--
                        ) {

                            if (headingStack[level]) {

                                currentArticle =
                                    headingStack[level];

                                break;
                            }
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | MASUKKAN CONTENT KE ARTICLE
                        |--------------------------------------------------------------------------
                        */

                        if (currentArticle) {

                            currentArticle.content +=
                                el.outerHTML;
                        }

                    });


                /*
                |--------------------------------------------------------------------------
                | VALIDATION
                |--------------------------------------------------------------------------
                */

                if (tree.length === 0) {

                    alert(
                        'Tidak ditemukan Heading 1 pada dokumen Word.\n\n' +
                        'Pastikan struktur dokumen menggunakan Heading 1, Heading 2, dan seterusnya.'
                    );

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | DEBUG
                |--------------------------------------------------------------------------
                */

                console.log(
                    '========================================'
                );

                console.log(
                    'TREE HASIL IMPORT WORD'
                );

                console.log(
                    '========================================'
                );

                console.log(
                    JSON.stringify(
                        tree,
                        null,
                        2
                    )
                );


                /*
                |--------------------------------------------------------------------------
                | COUNT CATEGORY
                |--------------------------------------------------------------------------
                */

                let totalArticles = 0;


                function countArticles(articles) {

                    articles.forEach(function (article) {

                        totalArticles++;

                        if (
                            article.children &&
                            article.children.length
                        ) {

                            countArticles(
                                article.children
                            );
                        }
                    });
                }


                tree.forEach(function (category) {

                    countArticles(
                        category.articles
                    );
                });


                /*
                |--------------------------------------------------------------------------
                | CONFIRM
                |--------------------------------------------------------------------------
                */

                const confirmed = confirm(

                    'Dokumen Word berhasil dibaca.\n\n' +

                    'Kategori utama : ' +
                    tree.length +
                    '\n' +

                    'Total artikel   : ' +
                    totalArticles +
                    '\n\n' +

                    'Lanjutkan import ke Knowledge Base?'
                );


                if (!confirmed) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | SEND TO LARAVEL
                |--------------------------------------------------------------------------
                */

                fetch(
                    routes.importDocx,
                    {

                        method: 'POST',

                        headers: {

                            'Content-Type':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'Accept':
                                'application/json'
                        },

                        body: JSON.stringify({

                            tree: tree

                        })
                    }
                )
                    .then(async function (response) {

                        const data =
                            await response.json();


                        if (!response.ok) {

                            throw new Error(
                                data.message ||
                                'Server gagal memproses import.'
                            );
                        }


                        if (!data.success) {

                            throw new Error(
                                data.message ||
                                'Import Word gagal.'
                            );
                        }


                        return data;
                    })
                    .then(function (data) {

                        console.log(
                            'Import berhasil:',
                            data
                        );


                        alert(
                            'Import Word berhasil!'
                        );


                        window.location.reload();
                    })
                    .catch(function (error) {

                        console.error(
                            'Import error:',
                            error
                        );


                        alert(
                            'Gagal melakukan import Word.\n\n' +
                            error.message
                        );
                    });
            }

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

            function togglePublish() {
                if (!currentArticleId) return;
                fetch(`/kb/article/${currentArticleId}/toggle-publish`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            articles[currentArticleId].status = data.status;
                            let badge = document.getElementById('badge-' + currentArticleId);
                            if (badge) {
                                if (data.status === 'Published') {
                                    badge.textContent = '● Published';
                                    badge.style.background = '#10b981';
                                } else {
                                    badge.textContent = '● Draft';
                                    badge.style.background = '#ef4444';
                                }
                            }
                            updatePublishButton();
                            alert(data.message);
                        } else {
                            alert('Gagal merubah status.');
                        }
                    });
            }

            function updatePublishButton() {
                let btn = document.getElementById('btn-publish');
                if (!btn || !currentArticleId) return;
                btn.style.display = 'inline-block';
                if (articles[currentArticleId].status === 'Published') {
                    btn.textContent = '⏸️ Unpublish';
                    btn.style.backgroundColor = '#f59e0b';
                } else {
                    btn.textContent = '▶️ Publish';
                    btn.style.backgroundColor = '#10b981';
                }
            }

            function publishAll() {
                if (!confirm('Apakah Anda yakin ingin mem-publish SELURUH artikel di dalam proyek ini ke versi publik secara berbarengan?')) return;

                fetch(`/kb/project/{{ $project->id }}/publish-all`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            if (confirm(data.message + "\n\nApakah Anda ingin langsung melihat halaman Online Knowledge Base publiknya sekarang?")) {
                                window.open(`/docs/{{ $project->slug }}`, '_blank');
                            }
                            window.location.reload();
                        } else {
                            alert('Terjadi kesalahan.');
                        }
                    });
            }

            function openOnlineKb(e) {
                let hasPublished = Object.values(articles).some(a => a.status === 'Published');
                if (!hasPublished) {
                    e.preventDefault();
                    alert('Maaf, belum ada artikel yang di-Publish. Silakan Publish minimal 1 artikel terlebih dahulu untuk dapat mengakses halaman publik Online KB.');
                }
            }

            function loadArticle(id) {
                let article = articles[id];
                if (!article) return;
                currentArticleId = id;

                setTimeout(() => {
                    document.getElementById('editor-container').style.display = 'block';
                    document.getElementById('editor-placeholder').style.display = 'none';

                    document.getElementById('article-title').value = article.title || '';
                    document.getElementById('article-type').value = article.type || 'Dokumentasi';
                    document.getElementById('article-title').readOnly = false;
                    document.getElementById('article-type').disabled = false;

                    quill.root.innerHTML = article.content || '';

                    // Setup Publish and Preview buttons
                    updatePublishButton();

                    let previewBtn = document.getElementById('btn-preview');
                    if (previewBtn) {
                        previewBtn.style.display = 'inline-block';
                        previewBtn.href = `{{ route('kb.preview', $project->id) }}#article-${article.id}`;
                    }
                }, 50);
            }

            async function saveKB() {
                if (!currentArticleId) {
                    alert('Pilih artikel dulu sebelum save!'); return;
                }

                let content = quill.root.innerHTML;
                let res = await fetch(routes.saveArticle, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                    body: JSON.stringify({
                        article_id: currentArticleId,
                        title: document.getElementById('article-title').value,
                        type: document.getElementById('article-type').value,
                        content: content
                    })
                });

                if (res.ok) {
                    // Update local cache
                    articles[currentArticleId].title = document.getElementById('article-title').value;
                    articles[currentArticleId].type = document.getElementById('article-type').value;
                    articles[currentArticleId].content = content;
                    alert('Artikel berhasil disimpan.');
                    updatePublishButton();
                } else {
                    alert('Gagal menyimpan KB');
                }
            }
            // Auto-load article from URL parameter if present
            window.addEventListener('DOMContentLoaded', (event) => {
                const urlParams = new URLSearchParams(window.location.search);
                const articleIdParam = urlParams.get('article_id');
                if (articleIdParam && articles[articleIdParam]) {
                    loadArticle(articleIdParam);
                }
            });
        </script>
    @endpush
@endsection