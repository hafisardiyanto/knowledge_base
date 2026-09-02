@foreach($articles->where('parent_id', $parentId)->sortBy('id') as $node)
    @php
        $href = '#article-' . $node->id;
        $hasChildren = $articles->where('parent_id', $node->id)->count() > 0;
    @endphp

    @if($hasChildren)
        <details class="sidebar-details">
            <summary class="sidebar-item" style="padding-left: {{ 12 + ($depth * 15) }}px;">
                <a href="{{ $href }}" class="nav-link">{{ $node->title }}</a>
                <span class="chevron">›</span>
            </summary>
            <div class="details-content">
                @include('export.sidebar_node', ['articles' => $articles, 'parentId' => $node->id, 'depth' => $depth + 1])
            </div>
        </details>
    @else
        <div class="sidebar-item" style="padding-left: {{ 12 + ($depth * 15) }}px;">
            <a href="{{ $href }}" class="nav-link">{{ $node->title }}</a>
        </div>
    @endif
@endforeach