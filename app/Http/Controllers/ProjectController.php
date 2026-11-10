<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::withCount('articles as total_kb');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $projects = $query->get();

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        return view('projects.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'required',
            'status' => 'required|in:Draft,Active',
            'icon' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('projects'), $filename);
            $validated['icon'] = 'projects/' . $filename;
        }

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        Project::create($validated);

        return redirect()->route('projects.index')->with('success', 'Project berhasil dibuat');
    }

    public function show(Project $project)
    {
        $project->loadCount('articles as total_kb', 'categories as total_categories');
        return view('projects.show', compact('project'));
    }

    public function edit(Project $project)
    {
        return view('projects.create', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|max:255',
            'description' => 'required',
            'status' => 'required|in:Draft,Active',
            'icon' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('projects'), $filename);
            $validated['icon'] = 'projects/' . $filename;
        } elseif ($request->has('remove_icon')) {
            if ($project->icon && file_exists(public_path($project->icon))) {
                @unlink(public_path($project->icon));
            }
            $validated['icon'] = null;
        }

        $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        $project->update($validated);
        return redirect()->route('projects.index')->with('success', 'Project berhasil diupdate');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return redirect()->route('projects.index')->with('success', 'Project berhasil dihapus');
    }

    public function duplicate(Request $request, Project $project)
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(300);

        $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
            'status' => 'required|in:Draft,Active'
        ]);

        \Illuminate\Support\Facades\DB::beginTransaction();
        try {
            // 1. Create duplicate of Project
            $newProject = Project::create([
                'name' => $request->name,
                'slug' => \Illuminate\Support\Str::slug($request->name . '-' . uniqid()),
                'description' => $request->description ?? $project->description,
                'status' => $request->status,
                'icon' => $project->icon
            ]);

            // 2. Duplicate Categories and map old Category ID -> new Category ID
            $categoryMap = [];
            $categories = \App\Models\Category::where('project_id', $project->id)
                ->orderBy('order_position', 'asc')
                ->get();

            foreach ($categories as $cat) {
                $newCat = \App\Models\Category::create([
                    'project_id' => $newProject->id,
                    'name' => $cat->name,
                    'order_position' => $cat->order_position
                ]);
                $categoryMap[$cat->id] = $newCat->id;
            }

            // 3. Fast & Memory-efficient article duplication preserving parent-child hierarchy
            $sourceArticles = \App\Models\Article::where('project_id', $project->id)
                ->select(['id', 'category_id', 'parent_id', 'title', 'type', 'status', 'content', 'order_position'])
                ->orderBy('id', 'asc')
                ->get();

            // Group articles by parent_id (null for roots)
            $groupedByParent = [];
            foreach ($sourceArticles as $art) {
                $pId = $art->parent_id ?? 0;
                $groupedByParent[$pId][] = $art;
            }

            // Recursive closure to copy articles
            $copyLevel = function ($oldParentId, $newParentId) use (&$copyLevel, &$groupedByParent, $newProject, &$categoryMap) {
                if (empty($groupedByParent[$oldParentId])) {
                    return;
                }

                foreach ($groupedByParent[$oldParentId] as $item) {
                    $newCategoryId = $item->category_id ? ($categoryMap[$item->category_id] ?? null) : null;
                    $newArt = \App\Models\Article::create([
                        'project_id' => $newProject->id,
                        'category_id' => $newCategoryId,
                        'parent_id' => $newParentId,
                        'title' => $item->title,
                        'slug' => \Illuminate\Support\Str::slug($item->title . '-' . uniqid()),
                        'type' => $item->type,
                        'status' => $item->status,
                        'content' => $item->content,
                        'order_position' => $item->order_position
                    ]);

                    // Recursively duplicate child articles
                    $copyLevel($item->id, $newArt->id);
                }
            };

            // Start from roots (parent_id IS NULL -> key 0)
            $copyLevel(0, null);

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('projects.index')
                ->with('success', "Project '{$newProject->name}' berhasil diduplikat dari '{$project->name}' beserta seluruh kategori dan artikelnya!");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menduplikat project: ' . $e->getMessage());
        }
    }
}
