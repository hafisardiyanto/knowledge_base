<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Project;
use App\Models\Category;
use App\Models\Article;

class KbController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::with(['categories' => function ($q) {
            $q->orderBy('order_position', 'asc');
        }])->orderBy('name')->get();
        // Just load everything for filter dropdowns.
        $categories = Category::orderBy('name')->get();

        $query = Article::with(['project', 'category'])->orderBy('updated_at', 'desc');

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $articles = $query->paginate(20);

        return view('kb.index', compact('articles', 'projects', 'categories'));
    }

    public function editor(Project $project)
    {
        @ini_set('memory_limit', '512M');

        $project->load([
            'categories' => function ($q) {
                $q->orderBy('order_position', 'asc');
            },
            'articles' => function ($q) {
                $q->select(['id', 'project_id', 'category_id', 'parent_id', 'title', 'type', 'status', 'order_position'])
                  ->orderBy('order_position', 'asc');
            }
        ]);

        $allProjects = Project::with(['categories' => function ($q) {
            $q->orderBy('order_position', 'asc');
        }])->orderBy('name')->get();

        return view('kb.editor', compact('project', 'allProjects'));
    }

    public function getArticleContent(Project $project, Article $article)
    {
        if ($article->project_id != $project->id) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json([
            'id' => $article->id,
            'title' => $article->title,
            'type' => $article->type,
            'status' => $article->status,
            'content' => $article->content ?? ''
        ]);
    }

    public function saveCategory(Request $request, Project $project)
    {
        $request->validate(['name' => 'required|max:255']);

        if ($request->has('category_id')) {
            $category = Category::where('project_id', $project->id)->findOrFail($request->category_id);
            $category->update(['name' => $request->name]);
        } else {
            $category = Category::create([
                'project_id' => $project->id,
                'name' => $request->name,
                'order_position' => Category::where('project_id', $project->id)->max('order_position') + 1
            ]);
        }

        return response()->json($category);
    }

    public function saveArticleInfo(Request $request, Project $project)
    {
        $article = Article::create([
            'project_id' => $project->id,
            'category_id' => $request->category_id,
            'parent_id' => $request->parent_id ?? null,
            'title' => $request->title,
            'slug' => \Illuminate\Support\Str::slug($request->title . '-' . uniqid()),
            'type' => 'Dokumentasi',
            'status' => 'Draft',
            'order_position' => Article::where('category_id', $request->category_id)->max('order_position') + 1
        ]);
        return response()->json($article);
    }

    public function deleteCategory(Request $request, Project $project, Category $category)
    {
        $category->delete();
        return response()->json(['success' => true]);
    }

    public function deleteArticle(Request $request, Project $project, Article $article)
    {
        $article->delete();
        return response()->json(['success' => true]);
    }

    public function duplicateArticle(Request $request, Project $project, Article $article)
    {
        $targetProjectId = $request->input('target_project_id', $project->id);
        $targetCategoryId = $request->input('target_category_id', $article->category_id);
        $targetParentId = $request->input('target_parent_id', $article->parent_id);

        // If target project is different and category is not belonging to target project, adjust category
        if ($targetProjectId != $project->id && $targetCategoryId) {
            $catExists = Category::where('id', $targetCategoryId)->where('project_id', $targetProjectId)->exists();
            if (!$catExists) {
                $targetCategoryId = null;
            }
        }

        DB::beginTransaction();
        try {
            $newArticle = $this->recursivelyDuplicateArticle($article, $targetProjectId, $targetCategoryId, $targetParentId);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Artikel berhasil diduplikat.',
                'article' => $newArticle,
                'target_project_id' => $targetProjectId
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menduplikat artikel: ' . $e->getMessage()
            ], 500);
        }
    }

    private function recursivelyDuplicateArticle(Article $sourceArticle, $projectId, $categoryId, $parentId = null, $isRoot = true)
    {
        $title = $isRoot ? ($sourceArticle->title . ' (Copy)') : $sourceArticle->title;
        $maxOrder = Article::where('category_id', $categoryId)
            ->where('parent_id', $parentId)
            ->max('order_position') ?? 0;

        $newArticle = Article::create([
            'project_id' => $projectId,
            'category_id' => $categoryId,
            'parent_id' => $parentId,
            'title' => $title,
            'slug' => Str::slug($title . '-' . uniqid()),
            'type' => $sourceArticle->type,
            'status' => 'Draft',
            'content' => $sourceArticle->content,
            'order_position' => $maxOrder + 1
        ]);

        // Duplicate children recursively
        $children = Article::where('parent_id', $sourceArticle->id)->orderBy('order_position', 'asc')->get();
        foreach ($children as $child) {
            $this->recursivelyDuplicateArticle($child, $projectId, $categoryId, $newArticle->id, false);
        }

        return $newArticle;
    }

    public function save(Request $request, Project $project)
    {
        $article = Article::where('project_id', $project->id)->findOrFail($request->article_id);
        $article->update([
            'title' => $request->title,
            'type' => $request->type,
            'content' => $request->input('content')
        ]);

        return response()->json(['success' => true]);
    }

    public function publishAll(Request $request, Project $project)
    {
        Article::where('project_id', $project->id)->update(['status' => 'Published']);
        return response()->json(['success' => true, 'message' => 'Seluruh artikel berhasil di-publish!']);
    }

    public function togglePublish(Request $request, Article $article)
    {
        $newStatus = $article->status === 'Published' ? 'Draft' : 'Published';
        $article->update(['status' => $newStatus]);

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => 'Status artikel berhasil diubah menjadi ' . $newStatus
        ]);
    }

    public function importDocx(Request $request, Project $project)
    {
        $tree = $request->input('tree');

        if (!is_array($tree) || empty($tree)) {
            return response()->json([
                'success' => false,
                'message' => 'Data hasil import Word tidak valid.'
            ], 400);
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | CATEGORY ORDER
            |--------------------------------------------------------------------------
            */

            $orderCategory = (
                Category::where('project_id', $project->id)
                    ->max('order_position') ?? 0
            ) + 1;


            /*
            |--------------------------------------------------------------------------
            | IMPORT CATEGORY
            |--------------------------------------------------------------------------
            */

            $defaultType = \App\Models\Setting::where('key', 'default_article_type')->value('value') ?? 'Dokumentasi';

            foreach ($tree as $categoryData) {

                $categoryName = trim(
                    $categoryData['name'] ?? ''
                );

                if ($categoryName === '') {
                    continue;
                }

                $category = Category::create([
                    'project_id' => $project->id,
                    'name' => $categoryName,
                    'order_position' => $orderCategory++
                ]);


                /*
                |--------------------------------------------------------------------------
                | IMPORT ARTICLE
                |--------------------------------------------------------------------------
                */

                $orderArticle = 1;

                foreach (
                    ($categoryData['articles'] ?? [])
                    as $articleData
                ) {

                    $this->createImportedArticle(
                        project: $project,
                        category: $category,
                        articleData: $articleData,
                        parentId: null,
                        orderPosition: $orderArticle++,
                        defaultType: $defaultType
                    );
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Dokumen Word berhasil diimport.'
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            report($e);

            return response()->json([
                'success' => false,
                'message' => 'Gagal melakukan import dokumen Word.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function createImportedArticle(
        Project $project,
        Category $category,
        array $articleData,
        $parentId = null,
        $orderPosition = 1,
        $defaultType = 'Dokumentasi'
    ) {
        $title = trim(
            $articleData['title'] ?? ''
        );

        /*
        |--------------------------------------------------------------------------
        | Abaikan artikel tanpa judul
        |--------------------------------------------------------------------------
        */

        if ($title === '') {
            $title = 'Artikel Import';
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE ARTICLE
        |--------------------------------------------------------------------------
        */

        $article = Article::create([
            'project_id' => $project->id,

            'category_id' => $category->id,

            /*
            | null  = artikel utama
            | ID    = child dari artikel sebelumnya
            */
            'parent_id' => $parentId,

            'title' => $title,

            'slug' => Str::slug(
                $title . '-' . uniqid()
            ),

            'content' => $articleData['content'] ?? '',
            'type' => $defaultType,
            'status' => 'Draft',
            'order_position' => $orderPosition
        ]);


        /*
        |--------------------------------------------------------------------------
        | CREATE CHILD ARTICLE
        |--------------------------------------------------------------------------
        */

        $childOrder = 1;

        foreach (
            ($articleData['children'] ?? [])
            as $childArticle
        ) {

            $this->createImportedArticle(
                project: $project,
                category: $category,
                articleData: $childArticle,
                parentId: $article->id,
                orderPosition: $childOrder++,
                defaultType: $defaultType
            );
        }

        return $article;
    }

    public function generateHtml(Request $request, Project $project)
    {
        $project->load([
            'articles' => function ($q) {
                $q->where('status', 'Published')->orderBy('order_position', 'asc');
            },
            'categories'
        ]);

        $zipFile = public_path($project->slug . '-KB.zip');
        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            $folderName = $project->slug;

            // Compile single-page SPA view
            $indexHtml = view('export.index', ['project' => $project, 'isExport' => true])->render();

            // Extract base64 images to physical files in the ZIP to save massive space
            $indexHtml = preg_replace_callback('/src=["\']data:image\/(.*?);base64,(.*?)["\']/is', function ($matches) use ($zip, $folderName) {
                $ext = strtolower($matches[1]);
                if ($ext === 'jpeg') {
                    $ext = 'jpg';
                }

                $base64Data = $matches[2];
                $binaryData = base64_decode($base64Data);

                // Deduplicate identical images to save even more space
                $filename = 'img_' . md5($binaryData) . '.' . $ext;

                // Save inside an images folder in ZIP
                $zip->addFromString($folderName . '/images/' . $filename, $binaryData);

                return 'src="images/' . $filename . '"';
            }, $indexHtml);

            // Put it inside the project folder
            $zip->addFromString($folderName . '/index.html', $indexHtml);

            $zip->close();

            // Save history
            \App\Models\GenerateHistory::create([
                'project_id' => $project->id,
                'file_name' => $project->slug . '-KB.zip',
                'generated_at' => now(),
            ]);

            return response()->download($zipFile, $project->slug . '-KB.zip', [
                'Content-Type' => 'application/zip',
                'Content-Disposition' => 'attachment; filename="' . $project->slug . '-KB.zip"'
            ]);
        }

        return back()->with('error', 'Gagal membuat file ZIP');
    }

    public function preview(Project $project)
    {
        @ini_set('memory_limit', '512M');

        $project->load([
            'articles' => function ($q) {
                $q->orderBy('order_position', 'asc');
            },
            'categories'
        ]);

        return view('export.index', compact('project'));
    }

    public function showOnlineKb(Request $request, $slug)
    {
        @ini_set('memory_limit', '512M');

        $project = Project::where('slug', $slug)->firstOrFail();

        // Prevent access if there are no published articles at all
        $publishedExists = $project->articles()->where('status', 'Published')->exists();
        if (!$publishedExists) {
            abort(404, 'Knowledge Base ini belum dipublikasikan (Masih berstatus Draft).');
        }

        $project->load([
            'articles' => function ($q) {
                $q->where('status', 'Published')->orderBy('order_position', 'asc');
            },
            'categories'
        ]);

        return view('export.index', compact('project'));
    }
}
