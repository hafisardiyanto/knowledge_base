@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/projects.css') }}">
@endpush

@section('content')
    <div style="margin-bottom: 20px;">
        <a href="{{ route('projects.index') }}" class="back-link">← Kembali ke Daftar</a>
    </div>

    <div class="card" style="margin-bottom: 20px;">
        <div class="project-header-info" style="justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 20px;">
                @if($project->icon)
                    <img src="{{ asset($project->icon) }}"
                        style="width: 80px; height: 80px; border-radius: 12px; object-fit: cover;">
                @else
                    <div class="project-icon-large">📁</div>
                @endif
                <div>
                    <h1 class="project-title">{{ $project->name }}</h1>
                    <p class="project-desc">{{ $project->description }}</p>
                    <span
                        style="background: #f3f4f6; padding: 4px 10px; border-radius: 20px; font-size: 0.85rem; font-weight: 500;">Status:
                        {{ $project->status }}</span>
                </div>
            </div>

            <div class="header-actions">
                <a href="{{ route('kb.editor', $project->id) }}" class="btn-primary" style="background-color: #8b5cf6;">📚
                    Kelola</a>
                <a href="{{ route('projects.edit', $project->id) }}" class="btn-primary"
                    style="background-color: #f59e0b;">✏️ Edit Project</a>
                <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
                    onsubmit="return confirm('Hapus project ini?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-primary" style="background-color: #ef4444;">🗑️ Hapus Project</button>
                </form>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <!-- Stat 1 -->
        <div class="card"
            style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px;">
            <div
                style="color: #6b7280; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                Total Knowledge Base</div>
            <div style="font-size: 3.5rem; font-weight: 700; color: #1f2937;">{{ $project->total_kb }}</div>
        </div>

        <!-- Stat 2 -->
        <div class="card"
            style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 30px;">
            <div
                style="color: #6b7280; font-size: 0.9rem; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px;">
                Total Category</div>
            <div style="font-size: 3.5rem; font-weight: 700; color: #1f2937;">{{ $project->total_categories }}</div>
        </div>
    </div>
@endsection