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
            <!-- Space for user profile / breadcrumbs if needed -->
            <div style="font-weight: 500; color: #4b5563;">
                Administrator
            </div>
        </header>

        <main class="main-content">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>

</html>