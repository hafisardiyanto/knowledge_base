<nav class="sidebar">
    <div class="sidebar-header">
        <a href="#article-home" class="site-title nav-link">{{ $project->name }}</a>
    </div>
    <div class="search-container">
        <input type="text" id="searchInput" class="search-input" placeholder="🔍 Cari artikel..." autocomplete="off">
        <div id="searchResults" class="search-results"></div>
    </div>
    <div class="sidebar-content">

        @php
            function renderArticleTree($articles, $allArticles, $level = 2)
            {
                foreach ($articles as $article) {
                    $children = $allArticles->where('parent_id', $article->id)->sortBy('order_position');
                    $hasChildren = $children->count() > 0;
                    $targetId = 'article-' . $article->id;
                    $childrenId = 'children-' . $article->id;
        @endphp

        <div class="sidebar-article" data-level="{{ $level }}">
            <div class="sidebar-article-row">
                @if($hasChildren)
                    <button type="button" class="article-toggle" data-target="{{ $childrenId }}" aria-expanded="false"
                        title="Buka submenu">
                        <span class="chevron">▶</span>
                    </button>
                @else
                    <span style="width: 28px; flex-shrink: 0;"></span>
                @endif

                <a href="#{{ $targetId }}" class="nav-link">{{ $article->title }}</a>
            </div>

            @if($hasChildren)
                <div id="{{ $childrenId }}" class="sidebar-children">
                    @php
                        renderArticleTree($children, $allArticles, $level + 1);
                    @endphp
                </div>
            @endif
        </div>

        @php
                }
            }
        @endphp

        @foreach($project->categories->sortBy('order_position') as $category)
            @php
                $rootArticles = $project->articles->where('category_id', $category->id)->whereNull('parent_id')->sortBy('order_position');
            @endphp

            @if($rootArticles->isNotEmpty())
                <div class="category-block">
                    <div class="category-title">{{ $category->name }}</div>
                    @php
                        renderArticleTree($rootArticles, $project->articles);
                    @endphp
                </div>
            @endif
        @endforeach

        @php
            $uncategorizedRoots = $project->articles->whereNull('category_id')->whereNull('parent_id')->sortBy('order_position');
        @endphp
        @if($uncategorizedRoots->isNotEmpty())
            <div class="category-block">
                <div class="category-title">Umum</div>
                @php
                    renderArticleTree($uncategorizedRoots, $project->articles);
                @endphp
            </div>
        @endif

    </div>
</nav>