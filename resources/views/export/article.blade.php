<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->title }} - {{ $project->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Amatic+SC:wght@700&family=Caveat:wght@400;700&family=Comfortaa:wght@400;700&family=Comic+Neue:ital,wght@0,400;0,700;1,400&family=Cormorant+Garamond:ital,wght@0,400;0,700;1,400&family=Dancing+Script:wght@500;700&family=EB+Garamond:ital,wght@0,400;0,700;1,400&family=Inconsolata:wght@400;700&family=Lato:ital,wght@0,400;0,700;1,400&family=Lobster&family=Lora:ital,wght@0,400;0,700;1,400&family=Merriweather:ital,wght@0,400;0,700;1,400&family=Montserrat:ital,wght@0,400;0,600;0,700;1,400&family=Nunito:ital,wght@0,400;0,600;0,700;1,400&family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400&family=Oswald:wght@400;600;700&family=Pacifico&family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Poppins:ital,wght@0,400;0,600;0,700;1,400&family=PT+Sans:ital,wght@0,400;0,700;1,400&family=PT+Serif:ital,wght@0,400;0,700;1,400&family=Raleway:ital,wght@0,400;0,600;0,700;1,400&family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400&family=Roboto+Mono:wght@400;700&family=Roboto+Slab:wght@400;700&family=Source+Sans+3:ital,wght@0,400;0,600;0,700;1,400&family=Ubuntu:ital,wght@0,400;0,700;1,400&display=swap" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
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
