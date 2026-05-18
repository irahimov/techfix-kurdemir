{{-- resources/views/tickets/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Müraciət #' . $ticket->ticket_number)
@section('page-title', 'Müraciət Təfərrüatları')

@section('content')
<div class="space-y-5" x-data="ticketDetail()">

    {{-- Üst Naviqasiya və Geri Düyməsi --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('tickets.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-300 transition-colors">
            ← Siyahıya qayıt
        </a>
        
        <div class="flex items-center gap-2 font-mono text-xs text-slate-500">
            <span>Yaradıldı: {{ $ticket->created_at ? $ticket->created_at->format('d.m.Y H:i') : '—' }}</span>
            @if($ticket->updated_at && $ticket->created_at && $ticket->updated_at->gt($ticket->created_at))
                <span class="text-slate-600">•</span>
                <span>Yeniləndi: {{ $ticket->updated_at->format('d.m.Y H:i') }}</span>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
        
        {{-- ── SOL TƏRƏF: ÇAT VƏ MESAJLAŞMA (2 Sütun) ─────────────────────────── --}}
        <div class="lg:col-span-2 space-y-5">
            
            {{-- Əsas Problem Açıqlaması (İlk Mesaj kimi) --}}
            <div class="glass-strong rounded-2xl p-6 space-y-4">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span class="font-mono text-xs text-slate-500">{{ $ticket->ticket_number }}</span>
                        <h1 class="text-xl font-bold text-white mt-0.5">{{ $ticket->title }}</h1>
                    </div>
                    
                    {{-- Status Sığortası və Badge bərpası --}}
                    @php
                        $status = strtolower($ticket->status ?? '');
                        $statusClass = 'bg-slate-800 text-slate-300';
                        $statusLabel = $ticket->status;
                        
                        if($status == 'new' || $status == 'yeni') { $statusClass = 'bg-blue-500/10 text-blue-400'; $statusLabel = 'Yeni'; }
                        elseif($status == 'in_progress' || $status == 'baxılır') { $statusClass = 'bg-yellow-500/10 text-yellow-400'; $statusLabel = 'Baxılır'; }
                        elseif($status == 'waiting_customer') { $statusClass = 'bg-purple-500/10 text-purple-400'; $statusLabel = 'Müştəri Gözləyir'; }
                        elseif($status == 'waiting_agent') { $statusClass = 'bg-orange-500/10 text-orange-400'; $statusLabel = 'Mütəxəssis Gözləyir'; }
                        elseif($status == 'in_service') { $statusClass = 'bg-cyan-500/10 text-cyan-400'; $statusLabel = 'Kuryerdə / Servisdə'; }
                        elseif($status == 'resolved' || $status == 'həll olundu') { $statusClass = 'bg-emerald-500/10 text-emerald-400'; $statusLabel = 'Həll Olundu'; }
                        elseif($status == 'closed' || $status == 'bağlı') { $statusClass = 'bg-rose-500/10 text-rose-400'; $statusLabel = 'Bağlandı'; }
                    @endphp
                    <span class="px-2.5 py-1 rounded-md text-xs font-medium {{ $statusClass }} shrink-0">
                        {{ $statusLabel }}
                    </span>
                </div>

                <div class="text-sm text-slate-300 leading-relaxed bg-white/[0.02] border border-white/[0.04] rounded-xl p-4 whitespace-pre-line">
                    {{ $ticket->description }}
                </div>

                {{-- İlk müraciətə qoşulan fayllar --}}
                @if($ticket->attachments && count($ticket->attachments) > 0)
                <div class="pt-2 border-t border-white/[0.04]">
                    <p class="text-[10px] font-mono text-slate-500 uppercase tracking-wider mb-2">Əlavə olunmuş fayllar:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        @foreach($ticket->attachments as $attachment)
                        <a href="{{ asset('storage/' . ($attachment->file_path ?? '')) }}" target="_blank" 
                           class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/5 border border-white/[0.04] text-xs text-slate-300 hover:bg-white/10 transition-colors truncate">
                            <span>{{ \Illuminate\Support\Str::contains($attachment->original_name ?? '', '.pdf') ? '📄' : '🖼️' }}</span>
                            <span class="truncate flex-1">{{ $attachment->original_name ?? 'Fayl' }}</span>
                            <span class="text-[10px] text-slate-600 font-mono">
                                @if($attachment->file_size)
                                    {{ round($attachment->file_size / 1024, 1) }} KB
                                @else
                                    —
                                @endif
                            </span>
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- ÇAT LOGU / MESAJLAR --}}
            <div class="glass rounded-2xl p-4 space-y-4 max-h-[500px] overflow-y-auto custom-scrollbar" id="chatContainer">
                <p class="text-center text-[10px] font-mono text-slate-600 uppercase tracking-widest py-2 border-b border-white/[0.02]">
                    💬 Söhbət Tarixçəsi
                </p>

                @if(!$ticket->messages || $ticket->messages->isEmpty())
                    <div class="text-center py-8 text-xs text-slate-500 italic">
                        Hələ bir mesaj yazılmayıb. İlk qeydi və ya cavabı aşağıdan göndərə bilərsiniz.
                    </div>
                @else
                    @foreach($ticket->messages as $message)
                        @php 
                            $isMe = $message->user_id === Auth::id();
                            $isAdminMsg = $message->user ? ($message->user->is_admin || $message->user->is_agent) : false;
                        @endphp
                        
                        <div class="flex gap-3 {{ $isMe ? 'flex-row-reverse' : '' }}">
                            {{-- Avatar --}}
                            <img src="{{ $message->user->avatar_url ?? 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y' }}" class="w-8 h-8 rounded-xl shrink-0 border border-white/10" alt="">
                            
                            {{-- Mesaj Bloku --}}
                            <div class="max-w-[75%] space-y-1 {{ $isMe ? 'text-right' : '' }}">
                                <div class="flex items-center gap-1.5 text-xs {{ $isMe ? 'flex-row-reverse' : '' }}">
                                    <span class="font-medium {{ $isAdminMsg ? 'text-blue-400' : 'text-slate-300' }}">
                                        {{ $message->user->name ?? 'İstifadəçi' }}
                                    </span>
                                    <span class="text-[9px] text-slate-600 font-mono">
                                        {{ $message->created_at ? $message->created_at->format('H:i') : '' }}
                                    </span>
                                </div>
                                
                                <div class="text-sm px-4 py-2.5 rounded-2xl text-left inline-block whitespace-pre-line
                                    {{ $isMe 
                                        ? 'bg-blue-600 text-white rounded-tr-none' 
                                        : ($isAdminMsg 
                                            ? 'bg-blue-500/10 border border-blue-500/20 text-slate-200 rounded-tl-none' 
                                            : 'bg-white/5 border border-white/[0.06] text-slate-300 rounded-tl-none') }}">
                                    {{ $message->message }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- YENİ CAVAB FORMASI --}}
            @if(strtolower($ticket->status ?? '') !== 'closed')
            <div class="glass-strong rounded-2xl p-4">
                <form action="{{ route('tickets.messages.store', $ticket) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <textarea name="message" rows="3" required
                            placeholder="Cavabınızı və ya qeydinizi bura yazın..."
                            class="w-full bg-white/[0.03] border border-white/10 rounded-xl px-4 py-3 text-sm text-white placeholder-slate-500 focus:outline-none focus:border-blue-500 resize-none"
                            @keydown.enter.meta.prevent="$el.closest('form').submit()"
                            @keydown.enter.ctrl.prevent="$el.closest('form').submit()"></textarea>
                    </div>
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] text-slate-600 font-mono">Cmd/Ctrl + Enter ilə göndər</p>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-4 py-2 rounded-xl font-medium transition-colors">
                            Mesajı Göndər →
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="glass rounded-2xl p-4 text-center text-sm text-slate-500 bg-white/[0.01] border border-dashed border-white/5">
                🔒 Bu müraciət bağlandığı üçün yeni mesaj göndərilə bilməz.
            </div>
            @endif

        </div>

        {{-- ── SAĞ TƏRƏF: DETALLAR VƏ METADATA (1 Sütun) ───────────────────────── --}}
        <div class="space-y-5">
            
            {{-- İCRA MÜDDƏTİ VƏ VAXT SAYĞACI --}}
            @if(!in_array(strtolower($ticket->status ?? ''), ['resolved', 'closed']))
            <div class="glass-strong rounded-2xl p-5 border {{ ($ticket->is_sla_violated ?? false) ? 'border-red-500/20 bg-red-500/[0.01]' : 'border-white/[0.06]' }}">
                <h3 class="text-xs font-mono text-slate-500 uppercase tracking-wider mb-3">İcra müddəti Hədəfi</h3>
                
                @if($ticket->is_sla_violated ?? false)
                    <div class="flex items-center gap-2 text-red-400">
                        <span class="text-xl">🚨</span>
                        <div>
                            <p class="text-sm font-bold">Müddət Pozulub</p>
                            <p class="text-[10px] text-red-500/80 font-mono">Vaxt limiti keçib!</p>
                        </div>
                    </div>
                @else
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs font-mono">
                            <span class="text-slate-400">Qalan Vaxt:</span>
                            <span class="font-bold text-green-400">
                                7 saat 19 dəq qaldı
                            </span>
                        </div>
                        <div class="w-full bg-white/5 rounded-full h-1.5 overflow-hidden">
                            <div class="h-full bg-green-500" style="width: 45%"></div>
                        </div>
                    </div>
                @endif
            </div>
            @endif

            {{-- STATUS VƏ MÜTƏXƏSSİS IDARƏETMƏSİ --}}
            @if(Auth::user()->is_admin || Auth::user()->is_agent)
            <div class="glass-strong rounded-2xl p-5 space-y-4">
                <h3 class="text-xs font-mono text-slate-500 uppercase tracking-wider">İdarəetmə Paneli</h3>
                
                {{-- Status Güncəlləmə Formu --}}
                <form action="{{ route('tickets.update-status', $ticket) }}" method="POST" class="space-y-2">
                    @csrf
                    @method('PATCH')
                    <label class="block text-[10px] text-slate-500 font-mono uppercase">Statusu Dəyiş</label>
                    <div class="flex gap-2">
                        <select name="status" class="flex-1 bg-[#0f172a] border border-white/10 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500">
                            <option value="new" {{ $ticket->status === 'new' ? 'selected' : '' }}>Yeni</option>
                            <option value="in_progress" {{ $ticket->status === 'in_progress' ? 'selected' : '' }}>Baxılır</option>
                            <option value="waiting_agent" {{ $ticket->status === 'waiting_agent' ? 'selected' : '' }}>Mütəxəssis Gözləyir</option>
                            <option value="waiting_customer" {{ $ticket->status === 'waiting_customer' ? 'selected' : '' }}>Müştəri Gözləyir</option>
                            <option value="in_service" {{ $ticket->status === 'in_service' ? 'selected' : '' }}>Kuryerdə / Servisdə</option>
                            <option value="resolved" {{ $ticket->status === 'resolved' ? 'selected' : '' }}>Həll Olundu ✅</option>
                            <option value="closed" {{ $ticket->status === 'closed' ? 'selected' : '' }}>Bağla 🔒</option>
                        </select>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-2 rounded-xl font-medium shrink-0 transition-colors">
                            Yadda Saxla
                        </button>
                    </div>
                </form>

                {{-- Mütəxəssis Təyini Formu --}}
                <form action="{{ route('tickets.assign', $ticket) }}" method="POST" class="space-y-2 pt-2 border-t border-white/[0.04]">
                    @csrf
                    @method('PATCH')
                    <label class="block text-[10px] text-slate-500 font-mono uppercase">Məsul Mütəxəssis</label>
                    <div class="flex gap-2">
                        <select name="agent_id" class="flex-1 bg-[#0f172a] border border-white/10 rounded-xl px-3 py-2 text-xs text-white focus:outline-none focus:border-blue-500">
                            <option value="">Təyin Edilməyib</option>
                            @foreach($agents ?? [] as $agent)
                                <option value="{{ $agent->id }}" {{ $ticket->agent_id == $agent->id ? 'selected' : '' }}>
                                    {{ $agent->name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-2 rounded-xl font-medium shrink-0 transition-colors">
                            Təyin Et
                        </button>
                    </div>
                </form>
            </div>
            @endif

            {{-- CİHAZ VƏ SİSTEM METADATASI --}}
            <div class="glass rounded-2xl p-5 space-y-3.5 text-sm">
                <h3 class="text-xs font-mono text-slate-500 uppercase tracking-wider pb-2 border-b border-white/[0.04]">
                    Müraciət Məlumatları
                </h3>

                {{-- Müştəri məlumatı --}}
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs text-slate-500">Müştəri:</span>
                    <div class="flex items-center gap-2 max-w-[70%]">
                        <img src="{{ $ticket->customer->avatar_url ?? 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y' }}" class="w-5 h-5 rounded-md" alt="">
                        <span class="text-white truncate font-medium">{{ $ticket->customer->name ?? '—' }}</span>
                    </div>
                </div>

                {{-- Məsul Mütəxəssis --}}
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs text-slate-500">Məsul Mütəxəssis:</span>
                    @if($ticket->agent)
                        <div class="flex items-center gap-2 max-w-[70%]">
                            <img src="{{ $ticket->agent->avatar_url ?? 'https://www.gravatar.com/avatar/00000000000000000000000000000000?d=mp&f=y' }}" class="w-5 h-5 rounded-md" alt="">
                            <span class="text-slate-300 truncate font-medium">{{ $ticket->agent->name }}</span>
                        </div>
                    @else
                        <span class="text-xs text-slate-600 italic">Təyin edilməyib</span>
                    @endif
                </div>

                {{-- Kateqoriya --}}
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs text-slate-500">Kateqoriya:</span>
                    <span class="text-slate-300 font-medium">
                        @if(is_object($ticket->category))
                            {{ $ticket->category->name ?? '—' }}
                        @else
                            {{ $ticket->category ?? '—' }}
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection