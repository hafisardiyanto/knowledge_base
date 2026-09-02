@foreach($articles->where('parent_id', $parentId) as $article)
    <div class="article-item" onclick="loadArticle({{ $article->id }})"
        style="margin-left: {{ $depth * 15 }}px; display: flex; justify-content: space-between; align-items: center;">
        <div style="flex:1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">{{ $article->title }}</div>
        <div style="display: flex; align-items: center; gap: 5px;">
            <div onclick="event.stopPropagation(); addArticle({{ $article->category_id }}, {{ $article->id }})"
                style="color: #9ca3af; font-size: 1.1rem; padding: 0 5px;" title="Tambah Sub Menu">+</div>
            <div onclick="event.stopPropagation(); deleteArticle({{ $article->id }})"
                style="color: #ef4444; font-size: 1rem; padding: 0 5px;" title="Hapus Sub Menu">🗑️</div>
        </div>
    </div>

    <div class="nested-articles" data-parent="{{ $article->id }}">
        @include('kb.article_node', ['articles' => $articles, 'parentId' => $article->id, 'depth' => $depth + 1])
    </div>
@endforeach