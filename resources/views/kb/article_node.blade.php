@foreach($articles->where('parent_id', $parentId) as $article)
    <div class="article-item" onclick="loadArticle({{ $article->id }})"
        style="margin-left: {{ $depth * 15 }}px; display: flex; justify-content: space-between; align-items: center;">
        <div style="flex:1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
            @if($article->status === 'Published')
                <span id="badge-{{ $article->id }}"
                    style="font-size:9px; padding:2px 4px; border-radius:3px; background:#10b981; color:white; margin-right:5px;">●
                    Published</span>
            @else
                <span id="badge-{{ $article->id }}"
                    style="font-size:9px; padding:2px 4px; border-radius:3px; background:#ef4444; color:white; margin-right:5px;">●
                    Draft</span>
            @endif
            <span id="article-text-{{ $article->id }}">{{ $article->title }}</span>
        </div>
        <div style="display: flex; align-items: center; gap: 5px;">
            <div onclick="event.stopPropagation(); openDuplicateModal({{ $article->id }}, '{{ addslashes($article->title) }}', {{ $article->category_id ?? 'null' }})"
                style="color: #6366f1; font-size: 1rem; padding: 0 5px; cursor: pointer;" title="Duplikat KB">📋</div>
            <div onclick="event.stopPropagation(); addArticle({{ $article->category_id }}, {{ $article->id }})"
                style="color: #9ca3af; font-size: 1.1rem; padding: 0 5px; cursor: pointer;" title="Tambah Sub Menu">+</div>
            <div onclick="event.stopPropagation(); deleteArticle({{ $article->id }})"
                style="color: #ef4444; font-size: 1rem; padding: 0 5px; cursor: pointer;" title="Hapus Sub Menu">🗑️</div>
        </div>
    </div>

    <div class="nested-articles" data-parent="{{ $article->id }}">
        @include('kb.article_node', ['articles' => $articles, 'parentId' => $article->id, 'depth' => $depth + 1])
    </div>
@endforeach