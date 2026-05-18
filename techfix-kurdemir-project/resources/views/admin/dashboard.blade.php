{{-- resources/views/admin/dashboard.blade.php --}}
@extends('layouts.app')

@section('title', 'İcmal Paneli — TechFix Kürdəmir')
@section('page-title', 'İcmal Paneli')

@section('content')
<div class="space-y-6">

    {{-- ── KPI KARTLARI ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        {{-- Ümumi Müraciət --}}
        <div class="glass rounded-2xl p-5 hover:border-brand-500/20 transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-blue-500/15 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">🎫</div>
                <span class="badge bg-blue-500/10 text-blue-400 border-blue-500/20">Ümumi</span>
            </div>
            <p class="text-3xl font-bold text-white font-mono">{{ number_format($stats['total_tickets']) }}</p>
            <p class="text-xs text-slate-500 mt-1">Ümumi müraciət sayısı</p>
        </div>

        {{-- Aktiv Müraciətlər --}}
        <div class="glass rounded-2xl p-5 hover:border-yellow-500/20 transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-yellow-500/15 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">⏳</div>
                <span class="badge bg-yellow-500/10 text-yellow-400 border-yellow-500/20">Aktiv</span>
            </div>
            <p class="text-3xl font-bold text-white font-mono">{{ number_format($stats['open_tickets']) }}</p>
            <p class="text-xs text-slate-500 mt-1">Aktiv müraciətlər</p>
        </div>

        {{-- Bu gün həll --}}
        <div class="glass rounded-2xl p-5 hover:border-green-500/20 transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-green-500/15 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">✅</div>
                <span class="badge bg-green-500/10 text-green-400 border-green-500/20">Bu gün</span>
            </div>
            <p class="text-3xl font-bold text-white font-mono">{{ number_format($stats['resolved_today']) }}</p>
            <p class="text-xs text-slate-500 mt-1">Bu gün həll olundu</p>
        </div>

        {{-- Vaxt Gecikmələri --}}
        <div class="glass rounded-2xl p-5 border {{ $stats['sla_violated'] > 0 ? 'border-red-500/30 animate-glow' : 'border-white/[0.06]' }} transition-all group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-10 h-10 rounded-xl bg-red-500/15 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">🚨</div>
                @if($stats['sla_violated'] > 0)
                    <span class="badge bg-red-500/20 text-red-400 border-red-500/30 animate-pulse">Kritik</span>
                @else
                    <span class="badge bg-green-500/10 text-green-400 border-green-500/20">Normal</span>
                @endif
            </div>
            <p class="text-3xl font-bold {{ $stats['sla_violated'] > 0 ? 'text-red-400' : 'text-white' }} font-mono">{{ $stats['sla_violated'] }}</p>
            <p class="text-xs text-slate-500 mt-1">Gecikən müraciət</p>
        </div>

    </div>

    {{-- ── VAXT STATUSU XƏBƏRDARLIQLARI ───────────────────────────────────────── --}}
    @if($slaWarnings->count() > 0)
    <div class="glass rounded-2xl border border-amber-500/20 overflow-hidden">
        <div class="px-5 py-4 flex items-center gap-3 border-b border-amber-500/10">
            <span class="text-amber-400 text-lg">⚠️</span>
            <h3 class="text-sm font-semibold text-amber-300">İcra müddətinə 2 saatdan az qalan müraciətlər</h3>
            <span class="ml-auto badge bg-amber-500/10 text-amber-400 border-amber-500/20">{{ $slaWarnings->count() }} müraciət</span>
        </div>
        <div class="divide-y divide-white/[0.04]">
            @foreach($slaWarnings as $tw)
            <div class="px-5 py-3 flex items-center gap-4 hover:bg-white/[0.02] transition-colors">
                <span class="font-mono text-xs text-slate-500">{{ $tw->ticket_number }}</span>
                <span class="text-sm text-white flex-1 truncate">{{ $tw->title }}</span>
                <span class="badge {{ $tw->priority_color }}">{{ $tw->priority_label }}</span>
                <span class="text-xs text-amber-400 font-mono font-medium">{{ $tw->sla_remaining }}</span>
                <a href="{{ route('tickets.show', $tw->id) }}" class="text-xs text-blue-400 hover:text-blue-300 transition-colors">Bax →</a>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── QRAFİKLƏR ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Line Chart: Gündəlik Trend --}}
        <div class="lg:col-span-2 glass rounded-2xl p-5">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-sm font-semibold text-white">Müraciət Trendi</h3>
                    <p class="text-xs text-slate-500">Son 30 gün</p>
                </div>
                <span class="flex items-center gap-3 text-xs text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-blue-400 inline-block rounded"></span>Açılan</span>
                    <span class="flex items-center gap-1.5"><span class="w-3 h-0.5 bg-green-400 inline-block rounded"></span>Bağlanan</span>
                </span>
            </div>
            <canvas id="lineChart" height="90"></canvas>
        </div>

        {{-- Pie Chart: Kateqoriyalar --}}
        <div class="glass rounded-2xl p-5">
            <div class="mb-5">
                <h3 class="text-sm font-semibold text-white">Kateqoriya Paylanması</h3>
                <p class="text-xs text-slate-500">Ümumi müraciətlər üzrə</p>
            </div>
            <canvas id="pieChart" height="180"></canvas>
        </div>

    </div>

    {{-- Bar Chart: Mütəxəssis Performansı --}}
    <div class="glass rounded-2xl p-5">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h3 class="text-sm font-semibold text-white">Mütəxəssis Performansı</h3>
                <p class="text-xs text-slate-500">Həll olunan müraciət sayı və vaxt gecikmələri</p>
            </div>
            <a href="{{ route('admin.reports') }}" class="text-xs text-blue-400 hover:text-blue-300 transition-colors">Ətraflı →</a>
        </div>
        <canvas id="barChart" height="60"></canvas>
    </div>

    {{-- ── MÜTƏXƏSSİS YÜK CƏDVƏLİ ────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        <div class="glass rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-white/[0.04]">
                <h3 class="text-sm font-semibold text-white">Mütəxəssis Yükü</h3>
            </div>
            <table class="w-full tf-table">
                <thead>
                    <tr>
                        <th class="text-left px-5 py-3">Mütəxəssis</th>
                        <th class="text-center">Aktiv</th>
                        <th class="text-center">Ümumi</th>
                        <th class="text-center">Gecikən</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agentLoad as $agent)
                    <tr class="text-sm border-b border-white/[0.02]">
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2.5">
                                <img src="{{ $agent->avatar_url }}" class="w-7 h-7 rounded-lg" alt="">
                                <span class="text-slate-300">{{ $agent->name }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="badge {{ $agent->active_tickets > 5 ? 'bg-red-500/10 text-red-400 border-red-500/20' : 'bg-blue-500/10 text-blue-400 border-blue-500/20' }}">
                                {{ $agent->active_tickets }}
                            </span>
                        </td>
                        <td class="text-center text-slate-400">{{ $agent->total_tickets }}</td>
                        <td class="text-center">
                            @if($agent->sla_violated > 0)
                                <span class="text-red-400 font-mono text-xs">{{ $agent->sla_violated }}</span>
                            @else
                                <span class="text-green-400 text-xs">0</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-6 text-sm text-slate-500">Sistemdə aktiv mütəxəssis tapılmadı.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Son Müraciətlər --}}
        <div class="glass rounded-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-white/[0.04] flex items-center justify-between">
                <h3 class="text-sm font-semibold text-white">Son Müraciətlər</h3>
                <a href="{{ route('tickets.index') }}" class="text-xs text-blue-400 hover:text-blue-300">Hamısı →</a>
            </div>
            <div class="divide-y divide-white/[0.04]">
                @forelse($recentTickets as $ticket)
                <a href="{{ route('tickets.show', $ticket->id) }}" class="flex items-center gap-3 px-5 py-3 hover:bg-white/[0.02] transition-colors group">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="font-mono text-[10px] text-slate-600">{{ $ticket->ticket_number }}</span>
                            @if(isset($ticket->is_sla_violated) && $ticket->is_sla_violated)
                                <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-pulse"></span>
                            @endif
                        </div>
                        <p class="text-sm text-slate-300 truncate group-hover:text-white transition-colors">{{ $ticket->title }}</p>
                        <p class="text-[10px] text-slate-600 mt-0.5">{{ $ticket->customer_name ?? 'Anonim' }}</p>
                    </div>
                    <span class="badge {{ $ticket->status_color }} shrink-0">{{ $ticket->status_label }}</span>
                </a>
                @empty
                <div class="text-center py-6 text-sm text-slate-500">Hələ heç bir müraciət daxil olmayıb.</div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// ── Chart.js Global Config ────────────────────────────────────────────────────
Chart.defaults.color = '#64748b';
Chart.defaults.borderColor = 'rgba(255,255,255,0.05)';
Chart.defaults.font.family = "'IBM Plex Mono', monospace";

const chartData = @json($chartData);

// ── Line Chart ────────────────────────────────────────────────────────────────
new Chart(document.getElementById('lineChart'), {
    type: 'line',
    data: {
        labels: chartData.daily_trend.labels,
        datasets: [
            {
                label: 'Açılan',
                data: chartData.daily_trend.opened,
                borderColor: '#60a5fa',
                backgroundColor: 'rgba(96, 165, 250, 0.08)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointBackgroundColor: '#60a5fa',
                borderWidth: 2,
            },
            {
                label: 'Bağlanan',
                data: chartData.daily_trend.closed,
                borderColor: '#4ade80',
                backgroundColor: 'rgba(74, 222, 128, 0.05)',
                fill: true,
                tension: 0.4,
                pointRadius: 3,
                pointBackgroundColor: '#4ade80',
                borderWidth: 2,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(255,255,255,0.03)' }, ticks: { maxTicksLimit: 10 } },
            y: { grid: { color: 'rgba(255,255,255,0.03)' }, beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});

// ── Pie Chart ─────────────────────────────────────────────────────────────────
new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: {
        labels: chartData.category_pie.labels,
        datasets: [{
            data: chartData.category_pie.data,
            backgroundColor: chartData.category_pie.colors.map(c => c + '33'),
            borderColor: chartData.category_pie.colors,
            borderWidth: 1.5,
            hoverOffset: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { boxWidth: 10, padding: 12, font: { size: 10 } }
            }
        },
        cutout: '65%',
    }
});

// ── Bar Chart ─────────────────────────────────────────────────────────────────
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: chartData.agent_performance.labels,
        datasets: [
            {
                label: 'Həll Olunan',
                data: chartData.agent_performance.resolved,
                backgroundColor: 'rgba(74, 222, 128, 0.2)',
                borderColor: '#4ade80',
                borderWidth: 1.5,
                borderRadius: 6,
            },
            {
                label: 'Vaxt Gecikməsi',
                data: chartData.agent_performance.violated,
                backgroundColor: 'rgba(239, 68, 68, 0.2)',
                borderColor: '#ef4444',
                borderWidth: 1.5,
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
                labels: { boxWidth: 10, padding: 16, font: { size: 10 } }
            }
        },
        scales: {
            x: { grid: { display: false } },
            y: { grid: { color: 'rgba(255,255,255,0.03)' }, beginAtZero: true, ticks: { stepSize: 1 } }
        }
    }
});
</script>
@endpush