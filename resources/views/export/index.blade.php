<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $project->name }} Documentation</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amatic+SC:wght@700&family=Caveat:wght@400;700&family=Comfortaa:wght@400;700&family=Comic+Neue:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@500;700&family=EB+Garamond:ital,wght@0,400;0,700;1,400&family=Inconsolata:wght@400;700&family=Lato:ital,wght@0,400;0,700;1,400&family=Lobster&family=Lora:ital,wght@0,400;0,700;1,400&family=Merriweather:ital,wght@0,400;0,700;1,400&family=Montserrat:ital,wght@0,400;0,600;0,700;1,400&family=Nunito:ital,wght@0,400;0,600;0,700;1,400&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400&family=Oswald:wght@400;600;700&family=Pacifico&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:ital,wght@0,400;0,600;0,700;1,400&family=PT+Sans:ital,wght@0,400;0,700;1,400&family=PT+Serif:ital,wght@0,400;0,700;1,400&family=Raleway:ital,wght@0,400;0,600;0,700;1,400&family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400&family=Roboto+Mono:wght@400;700&family=Roboto+Slab:wght@400;700&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&family=Ubuntu:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    @php
        $isExport = $isExport ?? false;
    @endphp

    @if($isExport)
        <style>
            {!! file_get_contents(public_path('css/kb-viewer.css')) !!}
        </style>
    @else
        <link rel="stylesheet" href="{{ asset('css/kb-viewer.css') }}">
    @endif
</head>

<body>
    <!-- Mobile Header Bar -->
    <div class="mobile-header-bar">
        <button type="button" class="mobile-menu-btn" onclick="toggleMobileSidebar()" title="Buka Menu">
            ☰
        </button>
        <a href="#article-home" class="mobile-site-title">{{ $project->name }}</a>
        <span style="width: 32px;"></span>
    </div>

    <!-- Mobile Backdrop -->
    <div class="mobile-sidebar-backdrop" id="mobileBackdrop" onclick="toggleMobileSidebar()"></div>

    @include('export.sidebar')

    <div class="main-content">
        <div class="content-wrapper">

            <!-- Home Section -->
            <div id="article-home" class="article-section active">
                <h1 class="page-title">{{ $project->name }}</h1>
                <p class="page-desc">{{ $project->description }}</p>

                <div
                    style="margin-top: 40px; padding: 20px; border: 1px solid var(--border); border-radius: 8px; background: #fafafa;">
                    <h3 style="margin-bottom: 15px; font-weight: 600; color: #18181b;">Getting Started</h3>
                    <p style="color: var(--text-light); font-size: 0.95rem;">Select an article from the sidebar to start
                        reading.</p>
                </div>
            </div>

            <!-- Articles -->
            @foreach($project->articles as $article)
                <div id="article-{{ $article->id }}" class="article-section">
                    <div class="breadcrumb" id="breadcrumb-{{ $article->id }}"></div>
                    <div class="article-meta">{{ $article->type ?? 'Dokumentasi' }}</div>
                    <h1 class="page-title">{{ $article->title }}</h1>
                    <div id="toc-{{ $article->id }}"></div>
                    <div class="ql-snow">
                        <div class="ql-editor">
                            {!! $article->content !!}
                        </div>
                    </div>
                    <div class="article-nav" id="nav-{{ $article->id }}"></div>
                </div>
            @endforeach

        </div>
    </div>

    <script>
        function toggleMobileSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.getElementById('mobileBackdrop');
            if (sidebar) sidebar.classList.toggle('mobile-open');
            if (backdrop) backdrop.classList.toggle('active');
        }

        function showArticle(id) {
            // Auto close mobile drawer if open
            if (window.innerWidth <= 768) {
                const sidebar = document.querySelector('.sidebar');
                const backdrop = document.getElementById('mobileBackdrop');
                if (sidebar) sidebar.classList.remove('mobile-open');
                if (backdrop) backdrop.classList.remove('active');
            }

            // Hide all articles
            document.querySelectorAll('.article-section').forEach(el => el.classList.remove('active'));
            // Remove active state from nav
            document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));

            // Show target article
            const target = document.getElementById('article-' + id);
            if (target) {
                target.classList.add('active');
            } else {
                document.getElementById('article-home').classList.add('active');
            }

            // Highlight nav item
            const nav = document.querySelector('a[href="#article-' + id + '"]');
            if (nav) {
                nav.classList.add('active');
                // Open parent details if nested
                let parentDetails = nav.closest('details');
                while (parentDetails) {
                    parentDetails.setAttribute('open', '');
                    parentDetails = parentDetails.parentElement.closest('details');
                }
            }

            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        window.addEventListener('hashchange', () => {
            let hash = window.location.hash.substring(1); // remove #
            if (hash && hash.startsWith('article-')) {
                let id = hash.replace('article-', '');
                showArticle(id);
            } else {
                showArticle('home');
            }
        });

        // initial load
        if (window.location.hash && window.location.hash.startsWith('#article-')) {
            let id = window.location.hash.replace('#article-', '');
            showArticle(id);
        } else {
            showArticle('home');
        }

        // Dropdown Logic
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.article-toggle').forEach(function (button) {
                button.addEventListener('click', function (event) {
                    event.preventDefault();
                    event.stopPropagation();

                    const targetId = button.getAttribute('data-target');
                    const target = document.getElementById(targetId);

                    if (!target) return;

                    const isOpen = button.getAttribute('aria-expanded') === 'true';

                    if (isOpen) {
                        button.setAttribute('aria-expanded', 'false');
                        target.classList.remove('open');
                    } else {
                        button.setAttribute('aria-expanded', 'true');
                        target.classList.add('open');
                    }
                });
            });
        });

        // ==========================================
        // BREADCRUMB BUILDER & SEARCH ENGINE
        // ==========================================
        function buildBreadcrumb(id) {
            let crumbs = [];

            if (id === 'home') {
                crumbs.push({ text: 'Home', href: '#article-home' });
                crumbs.push({ text: `{{ $project->name }}`, href: '#article-home' });
                return crumbs;
            }

            let nav = document.querySelector('a[href="#article-' + id + '"]');
            if (!nav) return [];

            crumbs.unshift({ text: nav.textContent.trim(), href: '#article-' + id });

            let parentDetails = nav.closest('.sidebar-children');
            while (parentDetails) {
                let parentToggle = document.querySelector('.article-toggle[data-target="' + parentDetails.id + '"]');
                if (parentToggle && parentToggle.nextElementSibling) {
                    let parentLink = parentToggle.nextElementSibling;
                    crumbs.unshift({ text: parentLink.textContent.trim(), href: parentLink.getAttribute('href') });
                }
                parentDetails = parentDetails.parentElement.closest('.sidebar-children');
            }

            let categoryBlock = nav.closest('.category-block');
            if (categoryBlock) {
                let catTitle = categoryBlock.querySelector('.category-title').textContent.trim();
                let firstLink = categoryBlock.querySelector('.nav-link');
                let catHref = firstLink ? firstLink.getAttribute('href') : null;
                crumbs.unshift({ text: catTitle, href: catHref });
            }

            crumbs.unshift({ text: `{{ $project->name }}`, href: '#article-home' });
            crumbs.unshift({ text: 'Home', href: '#article-home' });

            return crumbs;
        }

        const searchInput = document.getElementById('searchInput');
        const searchResults = document.getElementById('searchResults');
        let allDocs = [];

        document.addEventListener('DOMContentLoaded', () => {
            // Indexing for search
            document.querySelectorAll('.article-section:not(#article-home)').forEach(section => {
                let sid = section.id.replace('article-', '');
                let stitle = section.querySelector('.page-title').textContent.trim();
                let stxt = section.querySelector('.ql-editor').textContent.trim();
                allDocs.push({ id: sid, title: stitle, txt: stxt });
            });
        });

        searchInput.addEventListener('input', (e) => {
            let q = e.target.value.toLowerCase().trim();
            if (!q) {
                searchResults.style.display = 'none';
                return;
            }
            // Search title and text
            let matches = allDocs.filter(d =>
                d.title.toLowerCase().includes(q) || d.txt.toLowerCase().includes(q)
            ).slice(0, 10);

            if (matches.length > 0) {
                searchResults.innerHTML = matches.map(m => {
                    let pathObj = buildBreadcrumb(m.id);
                    let path = pathObj.map(c => c.text).join(' / ');
                    return `<a href="#article-${m.id}" class="search-result-item" onclick="searchResults.style.display='none'; searchInput.value=''; window.location.hash='#article-${m.id}'; showArticle('${m.id}'); return false;">
                        <div class="search-result-title">${m.title}</div>
                        <div class="search-result-excerpt">${path}</div>
                    </a>`;
                }).join('');
                searchResults.style.display = 'block';
            } else {
                searchResults.innerHTML = `<div style="padding:15px; font-size:0.85rem; color:gray;">Tidak ditemukan hasil untuk "${e.target.value}".</div>`;
                searchResults.style.display = 'block';
            }
        });

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                searchResults.style.display = 'none';
            }
        });

        // Ensure breadcrumb is attached when displaying article
        const originalShowArticle = showArticle;
        showArticle = function (id) {
            originalShowArticle(id);
            const breadcrumbEl = document.getElementById('breadcrumb-' + id);
            if (breadcrumbEl) {
                let crumbs = buildBreadcrumb(id);
                breadcrumbEl.innerHTML = crumbs.map((c, i) => {
                    if (i === crumbs.length - 1) {
                        return `<span>${c.text}</span>`; // Active page not linked
                    }
                    if (c.href) {
                        return `<a href="${c.href}" onclick="window.location.hash='${c.href}'; showArticle('${c.href.replace('#article-', '')}'); return false;" style="color:var(--text-light); text-decoration:none;" onmouseover="this.style.color='var(--text)'" onmouseout="this.style.color='var(--text-light)'">${c.text}</a>`;
                    }
                    return c.text;
                }).join(' <span style="color:#d4d4d8;">></span> ');
            }

            // Build Table of Contents
            const tocEl = document.getElementById('toc-' + id);
            const editorEl = document.querySelector('#article-' + id + ' .ql-editor');
            if (tocEl && editorEl && tocEl.innerHTML === '') {
                let headings = editorEl.querySelectorAll('h1, h2, h3');
                if (headings.length > 1) {
                    let tocHtml = '<div style="background:#fafafa; border:1px solid var(--border); border-radius:8px; padding:15px; margin-bottom:20px;">';
                    tocHtml += '<div style="font-weight:600; font-size:0.9rem; margin-bottom:10px; color:var(--text-light);">Daftar Isi</div><ul style="list-style:none; margin:0; padding:0;">';

                    headings.forEach((h, i) => {
                        let text = h.textContent.trim();
                        let tag = h.tagName.toLowerCase();
                        let ml = tag === 'h1' ? 0 : (tag === 'h2' ? 15 : 30);
                        let hid = 'heading-' + id + '-' + i;
                        h.id = hid;
                        tocHtml += `<li style="margin-left:${ml}px; margin-bottom:8px;"><a href="#${hid}" style="color:var(--text); text-decoration:none; font-size:0.95rem;">${text}</a></li>`;
                    });
                    tocHtml += '</ul></div>';
                    tocEl.innerHTML = tocHtml;
                }
            }

            // Build Next/Prev Navigation
            const navEl = document.getElementById('nav-' + id);
            if (navEl && navEl.innerHTML === '') {
                let links = Array.from(document.querySelectorAll('.sidebar .nav-link')).filter(a => a.getAttribute('href').startsWith('#article-') && a.getAttribute('href') !== '#article-home');
                let currentIndex = links.findIndex(a => a.getAttribute('href') === '#article-' + id);
                if (currentIndex !== -1) {
                    let navHtml = '<div style="display:flex; justify-content:space-between; margin-top:50px; border-top:1px solid var(--border); padding-top:20px;">';

                    if (currentIndex > 0) {
                        let prev = links[currentIndex - 1];
                        navHtml += `<a href="${prev.getAttribute('href')}" onclick="window.location.hash='${prev.getAttribute('href')}'; showArticle('${prev.getAttribute('href').replace('#article-', '')}'); return false;" style="text-decoration:none; color:var(--text); max-width:45%;"><div style="font-size:0.8rem; color:var(--text-light);">← Sebelumnya</div><div style="font-weight:600; margin-top:5px;">${prev.textContent.trim()}</div></a>`;
                    } else {
                        navHtml += `<div></div>`;
                    }

                    if (currentIndex < links.length - 1) {
                        let next = links[currentIndex + 1];
                        navHtml += `<a href="${next.getAttribute('href')}" onclick="window.location.hash='${next.getAttribute('href')}'; showArticle('${next.getAttribute('href').replace('#article-', '')}'); return false;" style="text-decoration:none; color:var(--text); max-width:45%; text-align:right;"><div style="font-size:0.8rem; color:var(--text-light);">Selanjutnya →</div><div style="font-weight:600; margin-top:5px;">${next.textContent.trim()}</div></a>`;
                    }

                    navHtml += '</div>';
                    navEl.innerHTML = navHtml;
                }
            }
        };
    </script>
</body>

</html>