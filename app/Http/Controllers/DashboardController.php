<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\Article;

class DashboardController extends Controller
{
    public function index()
    {
        $totalProjects = Project::count();
        $totalKB = Article::count();

        $projects = Project::withCount('articles as total_kb')->get();

        return view('dashboard', compact('totalProjects', 'totalKB', 'projects'));
    }
}
