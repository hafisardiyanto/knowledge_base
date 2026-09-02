@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/projects.css') }}">
@endpush

@section('content')
    <div class="page-header">
        <h2>{{ isset($project) ? 'Edit Project' : 'Buat Project Baru' }}</h2>
    </div>

    <div class="card form-card">
        <form action="{{ isset($project) ? route('projects.update', $project->id) : route('projects.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @if(isset($project))
                @method('PUT')
            @endif

            @if($errors->any())
                <div class="alert-error">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="form-group">
                <label class="form-label">Icon Project (Upload Gambar)</label>
                <input type="file" accept="image/*" name="icon" class="form-input">
                @if(isset($project) && $project->icon)
                    <div style="margin-top: 10px; display: flex; align-items: center; gap: 15px;">
                        <img src="{{ asset($project->icon) }}"
                            style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;"
                            onerror="this.style.display='none'">
                        <label
                            style="display: flex; align-items: center; gap: 5px; color: #ef4444; font-size: 0.9rem; cursor: pointer;">
                            <input type="checkbox" name="remove_icon" value="1"> Hapus gambar ini
                        </label>
                    </div>
                @endif
            </div>

            <div class="form-group">
                <label class="form-label">Nama Project *</label>
                <input type="text" name="name" value="{{ old('name', $project->name ?? '') }}" required class="form-input">
            </div>

            <div class="form-group">
                <label class="form-label">Description *</label>
                <textarea name="description" rows="4" required class="form-input"
                    style="font-family: inherit;">{{ old('description', $project->description ?? '') }}</textarea>
            </div>

            <div class="form-group" style="margin-bottom: 30px;">
                <label class="form-label">Status Project</label>
                <select name="status" class="form-select">
                    <option value="Active" {{ old('status', $project->status ?? 'Active') == 'Active' ? 'selected' : '' }}>
                        Aktif</option>
                    <option value="Draft" {{ old('status', $project->status ?? 'Draft') == 'Draft' ? 'selected' : '' }}>Tidak
                        Aktif (Draft)</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">Simpan Project</button>
                <a href="{{ route('projects.index') }}" class="btn-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection