<nav class="sidebar">
    <div class="sidebar-header">
        <a href="#article-home" class="site-title nav-link">{{ $project->name }}</a>
    </div>
    <div class="sidebar-content">
        @foreach($project->categories->sortBy('order_position') as $cat)
            <div class="category-block">
                <div class="category-title">{{ $cat->name }}</div>
                @include('export.sidebar_node', ['articles' => $project->articles->where('category_id', $cat->id), 'parentId' => null, 'depth' => 0])
            </div>
        @endforeach
    </div>
</nav>