<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KNOWLEDGE BASE GENERATOR</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
</head>

<body>

    <aside class="sidebar">
        <div class="sidebar-header">
            KB (KNOWLEDGE BASE)
        </div>

        <nav class="sidebar-nav">
            <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                🏠 Dashboard
            </a>
            <a href="{{ route('projects.index') }}"
                class="nav-item {{ request()->routeIs('projects.*') ? 'active' : '' }}">
                📁 Project
            </a>
            <a href="{{ route('kb.index') }}" class="nav-item {{ request()->routeIs('kb.*') ? 'active' : '' }}">
                📖 Knowledge Base
            </a>
            <a href="{{ route('settings.index') }}"
                class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                ⚙️ Settings
            </a>
        </nav>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    🚪 Logout
                </button>
            </form>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="topbar">
            <!-- Mobile sidebar toggle button -->
            <button type="button" class="admin-mobile-toggle" onclick="toggleAdminSidebar()" title="Buka Menu">
                ☰
            </button>

            <!-- Space for user profile / breadcrumbs if needed -->
            <div style="font-weight: 500; color: #4b5563;">
                Administrator
            </div>
        </header>

        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <!-- Admin Mobile Backdrop -->
    <div id="adminMobileBackdrop" class="admin-sidebar-backdrop" onclick="toggleAdminSidebar()"></div>

    <!-- Global Share Link Modal -->
    <div id="global-share-link-modal" class="link-modal-overlay">
        <div class="link-modal-card">
            <div class="link-modal-header">
                <h3 id="link-modal-title">🔗 Link Knowledge Base</h3>
                <button type="button" class="link-modal-close" onclick="closeShareLinkModal()">&times;</button>
            </div>
            <p id="link-modal-description" class="link-modal-desc">
                Salin link di bawah ini untuk membagikan atau membuka Knowledge Base secara online.
            </p>
            <div class="link-modal-input-group">
                <input type="text" id="link-modal-url-input" class="link-modal-input" readonly>
                <button type="button" id="link-modal-btn-copy" class="link-modal-btn-copy" onclick="copyShareModalLink()">
                    📋 Salin Link
                </button>
            </div>
            <div class="link-modal-footer">
                <a href="#" id="link-modal-open-tab" target="_blank" class="link-modal-open-btn">
                    🌍 Buka di Tab Baru ↗
                </a>
                <button type="button" class="link-modal-btn-done" onclick="closeShareLinkModal()">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleAdminSidebar() {
            const sidebar = document.querySelector('.sidebar');
            const backdrop = document.getElementById('adminMobileBackdrop');
            if (sidebar) sidebar.classList.toggle('mobile-open');
            if (backdrop) backdrop.classList.toggle('active');
        }

        function openShareLinkModal(url, title, desc) {
            const modal = document.getElementById('global-share-link-modal');
            const input = document.getElementById('link-modal-url-input');
            const titleEl = document.getElementById('link-modal-title');
            const descEl = document.getElementById('link-modal-description');
            const openLink = document.getElementById('link-modal-open-tab');
            const copyBtn = document.getElementById('link-modal-btn-copy');

            input.value = url;
            openLink.href = url;
            if (title) titleEl.textContent = '🔗 ' + title;
            if (desc) descEl.textContent = desc;

            copyBtn.innerHTML = '📋 Salin Link';
            copyBtn.style.backgroundColor = '#2563eb';

            modal.style.display = 'flex';
            setTimeout(() => input.select(), 50);
        }

        function closeShareLinkModal() {
            const modal = document.getElementById('global-share-link-modal');
            if (modal) modal.style.display = 'none';
        }

        function copyShareModalLink() {
            const input = document.getElementById('link-modal-url-input');
            const copyBtn = document.getElementById('link-modal-btn-copy');

            input.select();
            input.setSelectionRange(0, 99999);

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(input.value).then(onCopySuccess).catch(() => {
                    document.execCommand('copy');
                    onCopySuccess();
                });
            } else {
                document.execCommand('copy');
                onCopySuccess();
            }

            function onCopySuccess() {
                copyBtn.innerHTML = '✅ Tersalin!';
                copyBtn.style.backgroundColor = '#16a34a';
                setTimeout(() => {
                    copyBtn.innerHTML = '📋 Salin Link';
                    copyBtn.style.backgroundColor = '#2563eb';
                }, 2000);
            }
        }

        // Close on escape key or clicking backdrop
        window.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeShareLinkModal();
        });
        document.addEventListener('click', function(e) {
            const modal = document.getElementById('global-share-link-modal');
            if (modal && e.target === modal) {
                closeShareLinkModal();
            }
        });
    </script>

    @stack('scripts')
</body>

</html>