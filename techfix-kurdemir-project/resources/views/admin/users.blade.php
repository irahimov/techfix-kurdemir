@extends('layouts.app')

@section('title', 'İstifadəçilər')

@section('content')
<div class="space-y-6">
    {{-- Üst Başlıq --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-white font-mono">👥 İstifadəçi İdarəetməsi</h2>
            <p class="text-xs text-slate-500 mt-1">Sistemə qeydiyyatdan keçmiş bütün istifadəçilərin siyahısı və rolları</p>
        </div>
        
        {{-- Statistik kiçik məlumat --}}
        <div class="glass px-4 py-2 rounded-xl border border-white/[0.06] text-xs font-mono text-slate-400">
            Ümumi İstifadəçi: <span class="text-brand-400 font-bold">{{ $users->count() }}</span>
        </div>
    </div>

    {{-- İstifadəçilər Cədvəli --}}
    <div class="glass rounded-2xl overflow-hidden">
        <table class="w-full tf-table">
            <thead>
                <tr>
                    <th class="text-left w-12">ID</th>
                    <th class="text-left">Ad Soyad</th>
                    <th class="text-left">E-poçt Ünvanı</th>
                    <th class="text-center">Rol</th>
                    <th class="text-right">Qeydiyyat Tarixi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                <tr class="text-sm">
                    {{-- ID --}}
                    <td class="font-mono text-slate-500">{{ $user->id }}</td>
                    
                    {{-- Ad Soyad və Avatar simulyasiyası --}}
                    <td>
                        <div class="flex items-center gap-3">
                            @php
                                // Ad və Soyadı boşluğa görə parçalayırıq
                                $nameParts = explode(' ', trim($user->name));
                                $initials = '';

                                if (count($nameParts) >= 2) {
                                    // Birinci sözün ilk hərfi + Sonuncu sözün ilk hərfi (Məsələn: Sevinc Əliyeva -> SƏ)
                                    $firstInit = mb_substr($nameParts[0], 0, 1, 'UTF-8');
                                    $lastInit  = mb_substr($nameParts[count($nameParts) - 1], 0, 1, 'UTF-8');
                                    $initials  = mb_strtoupper($firstInit . $lastInit, 'UTF-8');
                                } else {
                                    // Əgər soyad yoxdursa, tək adın ilk 2 hərfini götürür (Məsələn: Orxan -> OR)
                                    $initials = mb_strtoupper(mb_substr($nameParts[0], 0, 2, 'UTF-8'), 'UTF-8');
                                }
                            @endphp

                            <div class="w-8 h-8 rounded-lg bg-white/[0.06] border border-white/[0.08] flex items-center justify-center text-xs font-bold font-mono text-slate-300">
                                {{ $initials }}
                            </div>
                            <span class="font-semibold text-white">{{ $user->name }}</span>
                        </div>
                    </td>
                    
                    {{-- Email --}}
                    <td class="text-slate-400 font-mono text-xs">{{ $user->email }}</td>
                    
                    {{-- Rol (Badge dizaynı ilə) --}}
                    <td class="text-center">
                        @if($user->hasRole('super_admin') || $user->hasRole('admin'))
                            <span class="badge bg-red-500/10 text-red-400 border-red-500/20">
                                <span class="priority-dot bg-red-500"></span> Admin
                            </span>
                        @elseif($user->hasRole('support_agent') || $user->hasRole('agent'))
                            <span class="badge bg-blue-500/10 text-blue-400 border-blue-500/20">
                                <span class="priority-dot bg-blue-400"></span> Mütəxəssis
                            </span>
                        @else
                            <span class="badge bg-slate-500/10 text-slate-400 border-white/[0.08]">
                                <span class="priority-dot bg-slate-400"></span> Müştəri
                            </span>
                        @endif
                    </td>
                    
                    {{-- Qeydiyyat Tarixi --}}
                    <td class="text-right text-slate-500 font-mono text-xs">
                        {{ $user->created_at ? $user->created_at->format('d.m.Y H:i') : '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center py-8 text-slate-500 font-mono text-xs">
                        Sistemdə heç bir istifadəçi tapılmadı.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection