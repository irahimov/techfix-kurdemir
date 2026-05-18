@extends('layouts.app')

@section('title', 'Müraciətlər — TechFix Control Panel')

@section('content')
<div class="space-y-6">
    
    {{-- STATİSTİKA KARTLARI --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    </div>

    {{-- KATEQORİYA İZAHAT MƏTNİ --}}
    <div class="mb-6">
        <p class="text-xs text-slate-500 mt-1">
            Müraciətlərin təsnifləşdirilməsi üçün kateqoriyaların siyahısı
        </p>
    </div>

    {{-- FİLTR KONSOLU --}}
    <div class="glass rounded-2xl p-5">
        <form method="GET" action="{{ isset($is_customer_panel) && $is_customer_panel ? route('customer.panel') : route('tickets.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            <div class="md:col-span-6 space-y-1.5">
                <label class="text-[10px] font-mono text-slate-500 uppercase tracking-wider">Axtarış</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Müraciət nömrəsi, başlıq, cihaz..." class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 focus:bg-slate-800">
            </div>

            <div class="md:col-span-2 space-y-1.5">
                <label class="text-[10px] font-mono text-slate-500 uppercase tracking-wider">Status</label>
                <select name="status" class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500">
                    <option value="" class="bg-slate-800">Hamısı</option>
                    <option value="new" class="bg-slate-800" {{ request('status') == 'new' ? 'selected' : '' }}>Yeni</option>
                    <option value="in_progress" class="bg-slate-800" {{ request('status') == 'in_progress' ? 'selected' : '' }}>Baxılır</option>
                    <option value="waiting_agent" class="bg-slate-800" {{ request('status') == 'waiting_agent' ? 'selected' : '' }}>Mütəxəssis Cavab Gözləyir</option>
                    <option value="waiting_customer" class="bg-slate-800" {{ request('status') == 'waiting_customer' ? 'selected' : '' }}>Müştəri Cavab Gözləyir</option>
                    <option value="in_service" class="bg-slate-800" {{ request('status') == 'in_service' ? 'selected' : '' }}>Kuryerdə/Servisdə</option>
                    <option value="resolved" class="bg-slate-800" {{ request('status') == 'resolved' ? 'selected' : '' }}>Həll olundu</option>
                </select>
            </div>

            <div class="md:col-span-2 space-y-1.5">
                <label class="text-[10px] font-mono text-slate-500 uppercase tracking-wider">Prioritet</label>
                <select name="priority" class="w-full bg-slate-800/80 border border-slate-700 rounded-xl px-4 py-3 text-sm text-white focus:outline-none focus:border-blue-500">
                    <option value="" class="bg-slate-800">Hamısı</option>
                    <option value="low" class="bg-slate-800" {{ request('priority') == 'low' ? 'selected' : '' }}>Aşağı</option>
                    <option value="medium" class="bg-slate-800" {{ request('priority') == 'medium' ? 'selected' : '' }}>Orta</option>
                    <option value="high" class="bg-slate-800" {{ request('priority') == 'high' ? 'selected' : '' }}>Yüksək</option>
                    <option value="urgent" class="bg-slate-800" {{ request('priority') == 'urgent' ? 'selected' : '' }}>Təcili</option>
                </select>
            </div>

            <div class="md:col-span-2 flex gap-2 justify-end items-end">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium text-sm py-3 px-5 rounded-xl transition-colors shadow-lg shadow-blue-600/10">Filtrele</button>
                @if(request()->has('status') || request()->has('priority') || request()->has('search'))
                    <a href="{{ isset($is_customer_panel) && $is_customer_panel ? route('customer.panel') : route('tickets.index') }}" class="text-xs text-slate-500 hover:text-slate-400 flex items-center px-2">Sıfırla ✕</a>
                @endif
            </div>
        </form>
    </div>

    {{-- SİYAHI CƏDVƏLİ --}}
    <div class="glass rounded-2xl overflow-hidden border border-white/5">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-white/5 text-[11px] uppercase tracking-wider font-mono text-slate-500 bg-white/[0.01]">
                        <th class="py-4 px-5">Müraciət</th>
                        <th class="py-4 px-5">Müştəri</th>
                        <th class="py-4 px-5">Kateqoriya</th>
                        <th class="py-4 px-5">Prioritet</th>
                        <th class="py-4 px-5">Status</th>
                        <th class="py-4 px-5">İcra müddəti</th>
                        <th class="py-4 px-5">Mütəxəssis</th>
                        <th class="py-4 px-5">Tarix</th>
                        <th class="py-4 px-5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/[0.03] text-sm">
                    @foreach($tickets as $ticket)
                    <tr class="hover:bg-white/[0.01] transition-colors">
                        <td class="py-4 px-5">
                            <span class="text-xs font-mono text-slate-500 block">{{ $ticket->ticket_number }}</span>
                            <span class="text-white font-semibold block mt-0.5">{{ Str::limit($ticket->title, 30) }}</span>
                        </td>
                        <td class="py-4 px-5 text-slate-300">{{ $ticket->customer->name ?? '—' }}</td>
                        
                        {{-- KATEQORİYA JSON TEMİZLƏNMƏSİ --}}
                        <td class="py-4 px-5 text-slate-400">
                            @if(is_object($ticket->category) && isset($ticket->category->name))
                                {{ $ticket->category->name }}
                            @elseif(is_array($ticket->category) && isset($ticket->category['name']))
                                {{ $ticket->category['name'] }}
                            @elseif(is_string($ticket->category) && str_contains($ticket->category, '"name":'))
                                @php $catData = json_decode($ticket->category, true); @endphp
                                {{ $catData['name'] ?? '—' }}
                            @else
                                {{ $ticket->category ?? '—' }}
                            @endif
                        </td>

                        {{-- PRİORİTET BADGE-LƏRİNİN BƏRPASI --}}
                        <td class="py-4 px-5">
                            @php
                                $priority = is_string($ticket->priority) ? strtolower($ticket->priority) : '';
                            @endphp
                            @if($priority == 'təcili' || $priority == 'urgent')
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-red-500/10 text-red-400">Təcili</span>
                            @elseif($priority == 'yüksək' || $priority == 'high')
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-orange-500/10 text-orange-400">Yüksək</span>
                            @elseif($priority == 'orta' || $priority == 'medium')
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-blue-500/10 text-blue-400">Orta</span>
                            @elseif($priority == 'aşağı' || $priority == 'low')
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-slate-500/10 text-slate-400">Aşağı</span>
                            @else
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-slate-800 text-slate-300">{{ $ticket->priority }}</span>
                            @endif
                        </td>

                        {{-- STATUS BADGE-LƏRİNİN BƏRPASI --}}
                        <td class="py-4 px-5">
                            @php
                                $status = is_string($ticket->status) ? strtolower($ticket->status) : '';
                            @endphp
                            @if($status == 'yeni' || $status == 'new')
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-blue-500/10 text-blue-400">Yeni</span>
                            @elseif($status == 'baxılır' || $status == 'in_progress')
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-yellow-500/10 text-yellow-400">Baxılır</span>
                            @elseif($status == 'müştəri cavab gözləyir' || $status == 'waiting_customer')
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-purple-500/10 text-purple-400">Müştəri Cavab Gözləyir</span>
                            @elseif($status == 'agent cavab gözləyir' || $status == 'agent gözləyir' || $status == 'agent_answer_waiting' || $status == 'waiting_agent')
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-orange-500/10 text-orange-400">Mütəxəssis Cavab Gözləyir</span>
                            @elseif($status == 'kuryerdə/servisdə' || $status == 'kuryerde' || $status == 'courier' || $status == 'in_service')
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-cyan-500/10 text-cyan-400">Kuryerdə/Servisdə</span>
                            @elseif($status == 'həll olundu' || $status == 'resolved')
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-emerald-500/10 text-emerald-400">Həll Olundu</span>
                            @else
                                <span class="px-2.5 py-1 rounded-md text-xs font-medium bg-slate-800 text-slate-300">{{ $ticket->status }}</span>
                            @endif
                        </td>

                        {{-- İCRA MÜDDƏTİ MƏNTİQİ --}}
                        <td class="py-4 px-5 font-mono text-xs">
                            @if(strtolower($ticket->status) !== 'resolved' && strtolower($ticket->status) !== 'həll olundu')
                                <span class="text-emerald-400">7 saat 19 dəq qaldı</span>
                            @else
                                <span class="text-slate-500">Vaxt bitib</span>
                            @endif
                        </td>

                        <td class="py-4 px-5 text-slate-400">{{ $ticket->agent->name ?? '—' }}</td>
                        <td class="py-4 px-5 text-slate-500 text-xs font-mono">
                            {{ $ticket->created_at ? $ticket->created_at->format('d.m.Y H:i') : '—' }}
                        </td>
                        <td class="py-4 px-5 text-right">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="text-blue-400 hover:text-blue-300 font-medium text-xs flex items-center gap-1 justify-end">
                                Bax <span>→</span>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection