<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Category;
use App\Models\Article;
use Illuminate\Http\Request;

class KbController extends Controller
{
    public function index(Request $request)
    {
        $projects = Project::orderBy('name')->get();
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
        $project->load([
            'categories' => function ($q) {
                $q->orderBy('order_position', 'asc');
            },
            'articles' => function ($q) {
                $q->orderBy('order_position', 'asc');
            }
        ]);

        return view('kb.editor', compact('project'));
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

    public function generateHtml(Request $request, Project $project)
    {
        $project->load('articles', 'categories');

        $zipFile = public_path($project->slug . '-KB.zip');
        $zip = new \ZipArchive();

        if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE)) {
            $folderName = $project->slug;

            // Compile single-page SPA view
            $indexHtml = view('export.index', compact('project'))->render();
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
            ])->deleteFileAfterSend(true);
        }

        return back()->with('error', 'Gagal membuat file ZIP');
    }

}
