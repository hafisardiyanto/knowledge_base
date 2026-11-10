@extends('layouts.app')

@push('styles')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amatic+SC:wght@700&family=Caveat:wght@400;700&family=Comfortaa:wght@400;700&family=Comic+Neue:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@500;700&family=EB+Garamond:ital,wght@0,400;0,700;1,400&family=Inconsolata:wght@400;700&family=Lato:ital,wght@0,400;0,700;1,400&family=Lobster&family=Lora:ital,wght@0,400;0,700;1,400&family=Merriweather:ital,wght@0,400;0,700;1,400&family=Montserrat:ital,wght@0,400;0,600;0,700;1,400&family=Nunito:ital,wght@0,400;0,600;0,700;1,400&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400&family=Oswald:wght@400;600;700&family=Pacifico&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:ital,wght@0,400;0,600;0,700;1,400&family=PT+Sans:ital,wght@0,400;0,700;1,400&family=PT+Serif:ital,wght@0,400;0,700;1,400&family=Raleway:ital,wght@0,400;0,600;0,700;1,400&family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400&family=Roboto+Mono:wght@400;700&family=Roboto+Slab:wght@400;700&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&family=Ubuntu:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
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
            <input type="file" id="doc-upload" accept=".docx,.doc,.pdf" style="display: none;">

            <button type="button" class="btn-primary" style="background-color: #3b82f6;" onclick="triggerImport()">
                📥 Import Dokumen (Word / PDF)
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

    @if(session('error'))
        <div
            style="padding: 15px; margin-bottom: 20px; border-radius: 6px; background-color: #fee2e2; color: #b91c1c; border: 1px solid #f87171;">
            {{ session('error') }}
        </div>
    @endif

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

                @php
                    $uncategorizedArticles = $project->articles->whereNull('category_id');
                @endphp
                @if($uncategorizedArticles->count() > 0)
                    <div class="cat-group" data-id="uncategorized" style="margin-top: 15px;">
                        <div class="cat-item" style="background: #f9fafb; border-style: dashed;">
                            <div class="cat-item-left" style="font-weight: 600; color: #4b5563;">
                                📁 Artikel Tanpa Kategori
                            </div>
                            <div style="display: flex; gap: 10px;">
                                <div class="cat-item-right" onclick="addArticle(null, null)" title="Tambah Sub Menu">+📄</div>
                            </div>
                        </div>
                        <div class="article-list">
                            @include('kb.article_node', ['articles' => $uncategorizedArticles, 'parentId' => null, 'depth' => 0])
                            <div class="article-add-btn" onclick="addArticle(null, null)">+ Tambah Sub-menu lagi...</div>
                        </div>
                    </div>
                @endif
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
                <button id="btn-duplicate" onclick="duplicateCurrentArticle()" class="btn-primary"
                    style="display: none; background-color: #6366f1; margin-left: 10px;">📋 Duplikat</button>
                <a id="btn-preview" href="#" target="_blank"
                    style="display: none; padding: 8px 12px; border-radius: 6px; background-color: #3b82f6; color: white; text-decoration: none; font-size: 0.9rem; margin-left: 5px;">👁️
                    Preview</a>
                <button id="btn-publish" onclick="togglePublish()" class="btn-primary"
                    style="display: none; margin-left: 5px;">▶️ Publish</button>
            </div>
            <div id="editor-container" style="display: none;"></div>
            <div id="editor-placeholder" style="padding: 20px; color: #9ca3af; text-align: center;">Pilih artikel dari
                sidebar untuk diedit.</div>
        </div>
    </div>

    <!-- Duplicate Modal -->
    <div id="duplicate-modal" class="modal-overlay" style="display: none;">
        <div class="modal-content-card">
            <div class="modal-header-row">
                <h3>📋 Duplikat Knowledge Base</h3>
                <span onclick="closeDuplicateModal()" class="modal-close-icon">&times;</span>
            </div>
            <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 20px;">
                Duplikat artikel: <strong id="dup-article-title-display"></strong>
            </p>

            <form id="duplicate-form" onsubmit="submitDuplicate(event)">
                <input type="hidden" id="dup-article-id">
                
                <div class="modal-form-group">
                    <label class="modal-form-label">
                        Pilih Project Tujuan:
                    </label>
                    <select id="dup-target-project" onchange="onDuplicateProjectChange()" class="modal-form-control">
                        @foreach($allProjects as $proj)
                            <option value="{{ $proj->id }}" {{ $proj->id == $project->id ? 'selected' : '' }}>
                                {{ $proj->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="modal-form-group" style="margin-bottom: 20px;">
                    <label class="modal-form-label">
                        Pilih Kategori Tujuan:
                    </label>
                    <select id="dup-target-category" class="modal-form-control">
                        <!-- Populated by JS -->
                    </select>
                </div>

                <div class="modal-actions-row">
                    <button type="button" onclick="closeDuplicateModal()" class="modal-btn-cancel">
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
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/mammoth/1.4.21/mammoth.browser.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
        <script>
            // Setup PDF.js worker
            if (window.pdfjsLib) {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
            }
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const projectId = {{ $project->id }};
            const maxUploadSizeMB = {{ \App\Models\Setting::where('key', 'max_upload_size')->value('value') ?? 5 }};
            const maxUploadSizeBytes = maxUploadSizeMB * 1024 * 1024;
            const routes = {
                saveCategory: "{{ route('kb.category.save', $project->id) }}",
                saveArticleInfo: "{{ route('kb.article.saveInfo', $project->id) }}",
                saveArticle: "{{ route('kb.save', $project->id) }}",
                importDocx: "{{ route('kb.import', $project->id) }}"
            };

            // DB Cache
            let articles = @json($project->articles->keyBy('id'));
            let currentArticleId = null;
            const allProjectsData = @json($allProjects);

            // Register Google Docs Fonts and Sizes in Quill
            const Font = Quill.import('formats/font');
            const googleDocsFonts = [
                'inter',
                'arial',
                'roboto',
                'open-sans',
                'lato',
                'montserrat',
                'poppins',
                'source-sans-3',
                'raleway',
                'nunito',
                'ubuntu',
                'georgia',
                'merriweather',
                'playfair-display',
                'lora',
                'pt-serif',
                'times-new-roman',
                'cormorant-garamond',
                'eb-garamond',
                'roboto-slab',
                'inconsolata',
                'roboto-mono',
                'courier-new',
                'oswald',
                'caveat',
                'dancing-script',
                'pacifico',
                'amatic-sc',
                'lobster',
                'comfortaa',
                'comic-neue'
            ];
            Font.whitelist = googleDocsFonts;
            Quill.register(Font, true);

            // Register Font Sizes (Google Docs style sizes)
            const Size = Quill.import('attributors/style/size');
            const googleDocsSizes = ['8px', '9px', '10px', '11px', '12px', '14px', '16px', '18px', '20px', '24px', '30px', '36px', '48px'];
            Size.whitelist = googleDocsSizes;
            Quill.register(Size, true);

            const quill = new Quill('#editor-container', {
                theme: 'snow',
                modules: {
                    history: {
                        delay: 1000,
                        maxStack: 100,
                        userOnly: true
                    },
                    imageResize: {
                        displaySize: true
                    },
                    toolbar: {
                        container: [
                            // 1. History (Undo / Redo)
                            ['undo', 'redo'],
                            // 2. Font Family & Font Size
                            [{ 'font': googleDocsFonts }, { 'size': googleDocsSizes }],
                            // 3. Headings / Styles
                            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                            // 4. Character Formatting (Bold, Italic, Underline, Strikethrough)
                            ['bold', 'italic', 'underline', 'strike'],
                            // 5. Text Color & Highlight / Background Color
                            [{ 'color': [] }, { 'background': [] }],
                            // 6. Subscript & Superscript
                            [{ 'script': 'sub' }, { 'script': 'super' }],
                            // 7. Alignments (Left, Center, Right, Justify)
                            [{ 'align': '' }, { 'align': 'center' }, { 'align': 'right' }, { 'align': 'justify' }],
                            // 8. Lists (Numbered, Bulleted)
                            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                            // 9. Indent & Outdent
                            [{ 'indent': '-1' }, { 'indent': '+1' }],
                            // 10. Blocks (Blockquote, Code Block)
                            ['blockquote', 'code-block'],
                            // 11. Links, Images & Video embed
                            ['link', 'image', 'video'],
                            // 12. Clear Formatting
                            ['clean']
                        ],
                        handlers: {
                            'undo': function() {
                                quill.history.undo();
                            },
                            'redo': function() {
                                quill.history.redo();
                            }
                        }
                    }
                }
            });

            var el = document.getElementById('category-list');
            Sortable.create(el, { animation: 150, ghostClass: 'sortable-ghost' });

            /**
             * ============================================================
             * IMPORT DOCUMENT (WORD .DOCX / PDF)
             * ============================================================
             */

            function triggerImport() {
                const input = document.getElementById('doc-upload');
                if (!input) {
                    alert('Input file dokumen tidak ditemukan.');
                    return;
                }
                input.value = '';
                input.click();
            }

            document.getElementById('doc-upload').addEventListener('change', function (event) {
                const file = event.target.files[0];
                if (!file) return;

                // VALIDATE MAXIMUM FILE SIZE FROM SETTINGS
                if (file.size > maxUploadSizeBytes) {
                    const actualMB = (file.size / (1024 * 1024)).toFixed(2);
                    alert(`Gagal import: Ukuran file (${actualMB} MB) melebihi batas maksimal yang diizinkan (${maxUploadSizeMB} MB).\n\nSilakan kompres file atau ubah batas upload di menu Settings.`);
                    event.target.value = '';
                    return;
                }

                const fileName = file.name.toLowerCase();

                if (fileName.endsWith('.docx') || fileName.endsWith('.doc')) {
                    const reader = new FileReader();
                    reader.onload = function (loadEvent) {
                        importWordDocument(loadEvent.target.result);
                    };
                    reader.onerror = function () {
                        alert('Gagal membaca file Word.');
                    };
                    reader.readAsArrayBuffer(file);
                } else if (fileName.endsWith('.pdf')) {
                    const reader = new FileReader();
                    reader.onload = function (loadEvent) {
                        importPdfDocument(loadEvent.target.result, file.name);
                    };
                    reader.onerror = function () {
                        alert('Gagal membaca file PDF.');
                    };
                    reader.readAsArrayBuffer(file);
                } else {
                    alert('Format file tidak didukung. Harap pilih file Microsoft Word (.docx) atau PDF (.pdf).');
                    event.target.value = '';
                }
            });

            /**
             * ============================================================
             * CONVERT PDF → HTML TREE
             * ============================================================
             */
            async function importPdfDocument(arrayBuffer, originalFileName) {
                if (!window.pdfjsLib) {
                    alert('Library pembaca PDF belum siap. Silakan muat ulang halaman.');
                    return;
                }

                try {
                    const loadingTask = pdfjsLib.getDocument({ data: arrayBuffer });
                    const pdf = await loadingTask.promise;
                    const numPages = pdf.numPages;

                    let fullHtml = '';
                    const defaultCatName = originalFileName.replace(/\.pdf$/i, '').trim() || 'Dokumen PDF';

                    for (let pageNum = 1; pageNum <= numPages; pageNum++) {
                        const page = await pdf.getPage(pageNum);
                        const textContent = await page.getTextContent();
                        
                        // Sort items from top to bottom (Y descending), then left to right (X ascending)
                        const items = textContent.items.slice().sort((a, b) => {
                            const yDiff = b.transform[5] - a.transform[5];
                            if (Math.abs(yDiff) > 4) return yDiff;
                            return a.transform[4] - b.transform[4];
                        });

                        let pageHtml = `<h2>Halaman ${pageNum}</h2>`;
                        let currentLine = '';
                        let lastY = null;

                        items.forEach(function (item) {
                            const str = item.str || '';
                            if (!str.trim() && !str.length) return;

                            if (lastY === null || Math.abs(item.transform[5] - lastY) > 5) {
                                if (currentLine.trim()) {
                                    pageHtml += `<p style="margin-bottom: 8px; line-height: 1.6;">${escapeHtml(currentLine.trim())}</p>`;
                                }
                                currentLine = str;
                                lastY = item.transform[5];
                            } else {
                                currentLine += (currentLine.endsWith(' ') || str.startsWith(' ') ? '' : ' ') + str;
                            }
                        });

                        if (currentLine.trim()) {
                            pageHtml += `<p style="margin-bottom: 8px; line-height: 1.6;">${escapeHtml(currentLine.trim())}</p>`;
                        }

                        fullHtml += `<h1>${defaultCatName}</h1>` + pageHtml;
                    }

                    displayResult({ value: fullHtml });
                } catch (err) {
                    console.error('PDF Parse Error:', err);
                    alert('Gagal memproses file PDF: ' + (err.message || 'Format PDF tidak dapat dibaca.'));
                }
            }

            function escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }


            /**
             * ============================================================
             * CONVERT DOCX → HTML
             * ============================================================
             */

            function importWordDocument(arrayBuffer) {
                mammoth.convertToHtml({
                    arrayBuffer: arrayBuffer,
                    styleMap: [
                        "p[style-name='Heading 1'] => h1:fresh",
                        "p[style-name='Heading 2'] => h2:fresh",
                        "p[style-name='Heading 3'] => h3:fresh",
                        "p[style-name='Heading 4'] => h4:fresh",
                        "p[style-name='Heading 5'] => h5:fresh",
                        "p[style-name='Heading 6'] => h6:fresh",
                        "table => table:fresh",
                        "tr => tr:fresh",
                        "th => th:fresh",
                        "td => td:fresh",
                        "r[style-name='Strong'] => strong",
                        "r[style-name='Emphasis'] => em",
                        "p[style-name='Quote'] => blockquote:fresh",
                        "p[style-name='Intense Quote'] => blockquote:fresh"
                    ]
                })
                    .then(function (result) {
                        displayResult(result);
                    })
                    .catch(function (error) {
                        console.error('Mammoth error:', error);
                        alert('Gagal membaca file Word.\n\n' + error.message);
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
                    'Dokumen berhasil dibaca.\n\n' +
                    'Kategori utama : ' + tree.length + '\n' +
                    'Total artikel   : ' + totalArticles + '\n\n' +
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

            async function loadArticle(id) {
                let article = articles[id];
                if (!article) return;
                currentArticleId = id;

                document.getElementById('editor-container').style.display = 'block';
                document.getElementById('editor-placeholder').style.display = 'none';

                document.getElementById('article-title').value = article.title || '';
                document.getElementById('article-type').value = article.type || 'Dokumentasi';
                document.getElementById('article-title').readOnly = false;
                document.getElementById('article-type').disabled = false;

                // Setup Publish and Preview buttons
                updatePublishButton();

                let dupBtn = document.getElementById('btn-duplicate');
                if (dupBtn) {
                    dupBtn.style.display = 'inline-block';
                }

                let previewBtn = document.getElementById('btn-preview');
                if (previewBtn) {
                    previewBtn.style.display = 'inline-block';
                    previewBtn.href = `{{ route('kb.preview', $project->id) }}#article-${article.id}`;
                }

                // If content is already cached locally, use it
                if (typeof article.content !== 'undefined') {
                    quill.root.innerHTML = article.content || '';
                    return;
                }

                // Otherwise fetch content on-demand (fast & lightweight)
                quill.root.innerHTML = '<p style="color:#9ca3af;">Memuat konten artikel...</p>';
                try {
                    let res = await fetch(`{{ url('knowledge-base') }}/${projectId}/article/${id}`);
                    if (res.ok) {
                        let data = await res.json();
                        article.content = data.content || '';
                        article.title = data.title || article.title;
                        article.type = data.type || article.type;
                        article.status = data.status || article.status;
                        quill.root.innerHTML = article.content;
                        updatePublishButton();
                    } else {
                        quill.root.innerHTML = '';
                    }
                } catch (e) {
                    quill.root.innerHTML = '';
                }
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
                    let updatedTitle = document.getElementById('article-title').value;
                    // Update local cache
                    articles[currentArticleId].title = updatedTitle;
                    articles[currentArticleId].type = document.getElementById('article-type').value;
                    articles[currentArticleId].content = content;

                    // Update sidebar article item title in real-time
                    let articleTextElem = document.getElementById('article-text-' + currentArticleId);
                    if (articleTextElem) {
                        articleTextElem.textContent = updatedTitle;
                    }

                    alert('Artikel berhasil disimpan.');
                    updatePublishButton();
                } else {
                    let errData = await res.json().catch(() => ({}));
                    alert('Gagal menyimpan KB: ' + (errData.message || 'Terjadi kesalahan pada server'));
                }
            }
            function duplicateCurrentArticle() {
                if (!currentArticleId || !articles[currentArticleId]) return;
                let art = articles[currentArticleId];
                openDuplicateModal(art.id, art.title, art.category_id);
            }

            let currentPendingCatId = null;

            function openDuplicateModal(articleId, title, categoryId) {
                document.getElementById('dup-article-id').value = articleId;
                document.getElementById('dup-article-title-display').textContent = title;
                
                // Set default project to current project
                const projSelect = document.getElementById('dup-target-project');
                projSelect.value = projectId;
                currentPendingCatId = categoryId;
                
                populateCategoryOptions(projectId, categoryId);

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
                const defaultCat = (selectedProjId === projectId) ? currentPendingCatId : null;
                populateCategoryOptions(selectedProjId, defaultCat);
            }

            function populateCategoryOptions(projId, selectedCatId) {
                const catSelect = document.getElementById('dup-target-category');
                catSelect.innerHTML = '';

                const proj = allProjectsData.find(p => p.id == projId);
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
                const targetProjectId = document.getElementById('dup-target-project').value;
                const targetCategoryId = document.getElementById('dup-target-category').value;
                const submitBtn = document.getElementById('btn-submit-duplicate');

                submitBtn.disabled = true;
                submitBtn.textContent = 'Menduplikasi...';

                try {
                    let res = await fetch(`{{ url('knowledge-base') }}/${projectId}/duplicate-article/${articleId}`, {
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
                        if (targetProjectId != projectId) {
                            if (confirm('Artikel berhasil diduplikat ke project lain. Apakah Anda ingin membuka editor project tujuan sekarang?')) {
                                window.location.href = `{{ url('knowledge-base') }}/${targetProjectId}/editor?article_id=${data.article.id}`;
                                return;
                            }
                        }
                        window.location.href = `{{ route('kb.editor', $project->id) }}?article_id=${data.article.id}`;
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