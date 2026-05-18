{{-- resources/views/admin/categories.blade.php --}}
@extends('layouts.app')

@section('title', 'Kateqoriyalar')

@section('content')
<div class="space-y-6">
    {{-- Üst Başlıq və Yeni Kateqoriya Düyməsi --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white font-mono">🗂️ Kateqoriya İdarəetməsi</h2>
            <p class="text-xs text-slate-500 mt-1">Müraciətlərin təsnifləşdirilməsi üçün kateqoriyaların siyahısı</p>
        </div>
        
        {{-- Sadə Yeni Kateqoriya Əlavə Etmə Formu --}}
        <div class="glass p-4 rounded-xl border border-white/[0.06]">
            <form action="{{ route('admin.categories.store') }}" method="POST" class="flex gap-3 items-center">
                @csrf
                <input type="text" name="name" placeholder="Kateqoriya adı..." class="tf-input px-3 py-1.5 rounded-lg text-sm w-48" required>
                <input type="text" name="description" placeholder="Açıqlama (könüllü)..." class="tf-input px-3 py-1.5 rounded-lg text-sm w-64">
                <button type="submit" class="btn-primary text-white text-xs px-4 py-2 rounded-lg font-medium">
                    ＋ Əlavə Et
                </button>
            </form>
        </div>
    </div>

    {{-- Kateqoriyalar Cədvəli (Sənin Doğma Dizaynında) --}}
    <div class="glass rounded-2xl overflow-hidden">
        <table class="w-full tf-table">
            <thead>
                <tr>
                    <th class="text-left w-12">ID</th>
                    <th class="text-left">Kateqoriya Adı</th>
                    <th class="text-left">Açıqlama</th>
                    <th class="text-center w-32">Əməliyyatlar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $category)
                <tr class="text-sm">
                    <td class="font-mono text-slate-500">{{ $category->id }}</td>
                    <td class="font-semibold text-white">{{ $category->name }}</td>
                    <td class="text-slate-400">{{ $category->description ?? '—' }}</td>
                    <td class="text-center">
                        {{-- Silme Düyməsi --}}
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Bu kateqoriyanı silmək istədiyinizdən əminsiniz?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-300 transition-colors bg-red-500/10 px-2 py-1 rounded-md border border-red-500/20">
                                🗑️ Sil
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-8 text-slate-500 font-mono text-xs">
                        Hələ heç bir kateqoriya əlavə edilməyib.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection