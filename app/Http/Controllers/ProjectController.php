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
}
