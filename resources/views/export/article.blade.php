<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->title }} - {{ $project->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        :root {
            --bg: #f4f4f5; /* Abu-abu terang untuk konten */
            --sidebar-bg: #fafafa; /* Putih sedikit abu untuk sidebar */
            --border: #e4e4e7;
            --text: #27272a;
            --text-light: #71717a;
            --accent: #09090b;
            --hover-bg: #ececf1;
        }

        * {
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: #d4d4d8;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a1a1aa;
        }

        body {
            background: var(--bg);
            color: var(--text);
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            overflow-y: auto;
            position: fixed;
            top: 0;
            bottom: 0;
            left: 0;
        }

        .sidebar-header {
            padding: 20px;
            border-bottom: 1px solid var(--border);
            position: sticky;
            top: 0;
            background: var(--sidebar-bg);
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-header::before {
            content: '◒';
            font-size: 1.4rem;
            color: var(--accent);
        }

        .site-title {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--text);
            text-decoration: none;
        }

        .sidebar-content {
            padding: 20px 0;
        }

        .category-block {
            margin-bottom: 24px;
        }

        .category-title {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-light);
            padding: 0 20px;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .sidebar-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 6px 20px;
            color: var(--text-light);
            transition: background 0.2s, color 0.2s;
            cursor: pointer;
        }

        .sidebar-item:hover {
            color: var(--text);
            background: var(--hover-bg);
        }

        .sidebar-item a {
            color: inherit;
            text-decoration: none;
            font-size: 0.9rem;
            flex: 1;
            display: block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sidebar-item a.active {
            color: var(--accent);
            font-weight: 500;
        }

        /* Dropdown styles */
        details.sidebar-details summary {
            list-style: none;
        }

        details.sidebar-details summary::-webkit-details-marker {
            display: none;
        }

        details.sidebar-details .chevron {
            font-size: 1.2rem;
            line-height: 1;
            color: #a1a1aa;
            transition: transform 0.2s;
            pointer-events: none;
        }

        details.sidebar-details[open]>summary .chevron {
            transform: rotate(90deg);
            color: var(--text);
        }

        details.sidebar-details[open]>summary {
            color: var(--text);
        }

        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 50px;
            background: var(--bg);
            min-height: 100vh;
            display: flex;
            justify-content: center;
        }

        .content-wrapper {
            width: 100%;
            max-width: 768px;
        }

        .page-title {
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -0.03em;
            margin-bottom: 1rem;
            color: #18181b;
        }

        .article-meta {
            display: inline-block;
            padding: 4px 10px;
            background: #e4e4e7;
            color: #52525b;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 20px;
            border: 1px solid #d4d4d8;
        }

        /* Quill Light Mode overrides */
        .ql-editor {
            padding: 0;
            font-family: 'Inter', sans-serif;
            font-size: 1.05rem;
            line-height: 1.7;
            color: #3f3f46;
        }

        .ql-editor img {
            max-width: 100%;
            border-radius: 8px;
            margin: 15px 0;
            border: 1px solid var(--border);
        }

        .ql-editor h1,
        .ql-editor h2,
        .ql-editor h3 {
            color: #18181b;
            margin-top: 2rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .ql-editor h2 {
            font-size: 1.6rem;
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
        }

        .ql-editor p {
            margin-bottom: 1.2em;
        }

        .ql-editor a {
            color: #2563eb;
            text-decoration: none;
        }

        .ql-editor a:hover {
            text-decoration: underline;
        }

        .ql-editor blockquote {
            border-left: 4px solid #d4d4d8;
            margin-left: 0;
            padding-left: 15px;
            color: #71717a;
            font-style: italic;
        }

        .ql-editor pre {
            background: #f1f5f9;
            color: #334155;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            overflow-x: auto;
            font-family: monospace;
            font-size: 0.9rem;
            margin: 15px 0;
        }
    </style>
</head>

<body>
    @include('export.sidebar', ['isRoot' => false])

    <div class="main-content">
        <div class="content-wrapper">
            <div class="article-meta">{{ $article->type ?? 'Dokumentasi' }}</div>
            <h1 class="page-title">{{ $article->title }}</h1>

            <div class="ql-snow">
                <div class="ql-editor">
                    {!! $article->content !!}
                </div>
            </div>
        </div>
    </div>
</body>

</html>