@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/kb.css') }}">
@endpush

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h2>📖 Knowledge Base</h2>
            <p style="color: #6b7280; font-size: 0.95rem;">Lihat seluruh KB dari semua project.</p>
        </div>
        <a href="{{ route('projects.index') }}" class="btn-primary">+ Buat KB Baru (Pilih Project)</a>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('kb.index') }}" class="kb-list-form">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="🔍 Cari Knowledge Base (Title)"
                class="kb-search-input">

            <select name="project_id"
                style="padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 6px; background: white; outline: none;">
                <option value="">Semua Project ▼</option>
                @foreach($projects as $proj)
                    <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }}
                    </option>
                @endforeach
            </select>

            <select name="category_id"
                style="padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 6px; background: white; outline: none;">
                <option value="">Semua Category ▼</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}
                    </option>
                @endforeach
            </select>

            <select name="type"
                style="padding: 10px 15px; border: 1px solid #d1d5db; border-radius: 6px; background: white; outline: none;">
                <option value="">Semua Type ▼</option>
                <option value="Dokumentasi" {{ request('type') == 'Dokumentasi' ? 'selected' : '' }}>📄 Dokumentasi</option>
                <option value="Tutorial" {{ request('type') == 'Tutorial' ? 'selected' : '' }}>📖 Tutorial</option>
                <option value="FAQ" {{ request('type') == 'FAQ' ? 'selected' : '' }}>❓ FAQ</option>
                <option value="Troubleshooting" {{ request('type') == 'Troubleshooting' ? 'selected' : '' }}>🛠️
                    Troubleshooting</option>
            </select>

            <button type="submit" class="btn-primary" style="padding: 10px 20px;">Filter</button>
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
                        <div style="display: flex; gap: 10px;">
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
@endsection