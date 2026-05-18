<?php
// app/Http/Controllers/AdminController.php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    // ─── Admin Dashboard ──────────────────────────────────────────────────────
    public function dashboard()
    {
        $activeStatuses = ['new', 'in_progress', 'waiting_agent', 'waiting_customer', 'in_service'];

        $stats = [
            'total_tickets'   => Ticket::count(),
            'open_tickets'    => Ticket::whereIn('status', $activeStatuses)->count(),
            'resolved_today'  => Ticket::where('status', 'resolved')->whereDate('updated_at', today())->count(),
            'sla_violated'    => Ticket::where('is_sla_violated', true)->count(),
            'total_customers' => User::role('customer')->count(),
            'total_agents'    => User::role('support_agent')->count(),
            'avg_resolution'  => $this->getAvgResolutionHours(),
        ];

        $chartData = [
            'daily_trend'       => $this->getDailyTrend(30),
            'category_pie'      => $this->getCategoryDistribution(),
            'agent_performance' => $this->getAgentPerformance(),
            'priority_dist'     => $this->getPriorityDistribution(),
        ];

        // ── Son müraciətlər ────────────────────────────────────────────────────
        $recentTickets = Ticket::with(['customer', 'agent', 'category'])
            ->latest()->take(10)->get()
            ->map(function ($ticket) {
                $ticket->status_label = match($ticket->status) {
                    'new'              => 'Yeni',
                    'in_progress'      => 'Baxılır',
                    'waiting_agent'    => 'Mütəxəssis Gözləyir',
                    'waiting_customer' => 'Müştəri Gözləyir',
                    'in_service'       => 'Servisdə',
                    'resolved'         => 'Həll Olundu',
                    default            => $ticket->status,
                };
                $ticket->status_color = match($ticket->status) {
                    'new'              => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                    'in_progress'      => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                    'waiting_agent'    => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                    'waiting_customer' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                    'in_service'       => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                    'resolved'         => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                    default            => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                };
                return $ticket;
            });

        // ── Mütəxəssis yükü ────────────────────────────────────────────────────
        $agentLoad = User::role('support_agent')
            ->where('is_active', true)
            ->get()
            ->map(function ($agent) use ($activeStatuses) {
                $agent->active_tickets = Ticket::where('assigned_to', $agent->id)
                    ->whereIn('status', $activeStatuses)->count();
                $agent->total_tickets  = Ticket::where('assigned_to', $agent->id)->count();
                $agent->sla_violated   = Ticket::where('assigned_to', $agent->id)
                    ->where('is_sla_violated', true)->count();
                return $agent;
            });

        // ── İcra müddəti xəbərdarlıqları ──────────────────────────────────────
        $slaWarnings = Ticket::whereIn('status', $activeStatuses)
            ->where('is_sla_violated', false)
            ->whereNotNull('sla_deadline')
            ->where('sla_deadline', '<=', now()->addHours(2))
            ->with('customer')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($ticket) {
                $ticket->priority_label = match($ticket->priority) {
                    'urgent' => 'Təcili',
                    'high'   => 'Yüksək',
                    'medium' => 'Orta',
                    'low'    => 'Aşağı',
                    default  => $ticket->priority,
                };
                $ticket->priority_color = match($ticket->priority) {
                    'urgent' => 'bg-red-500/10 text-red-400 border-red-500/20',
                    'high'   => 'bg-orange-500/10 text-orange-400 border-orange-500/20',
                    'medium' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                    'low'    => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                    default  => 'bg-slate-500/10 text-slate-400 border-slate-500/20',
                };
                $ticket->sla_remaining = $ticket->sla_deadline
                    ? now()->diffForHumans($ticket->sla_deadline, true)
                    : '—';
                return $ticket;
            });

        $slaStats      = ['violated' => $stats['sla_violated'], 'fulfilled' => 0, 'rate' => 100];
        $agentKpis     = collect([]);
        $categoryStats = collect([]);
        $categories    = Category::all();
        $users         = User::all();
        $agents        = User::role('support_agent')->get();
        $days          = 30;

        return view('admin.dashboard', compact(
            'stats', 'chartData', 'recentTickets', 'slaWarnings', 'agentLoad',
            'slaStats', 'agentKpis', 'categoryStats', 'categories', 'users', 'agents', 'days'
        ));
    }

    // ─── Kateqoriyalar Səhifəsi ──────────────────────────────────────────────
    public function categories()
    {
        $categories = Category::latest()->get();
        return view('admin.categories', compact('categories'));
    }

    // ─── İstifadəçilər Səhifəsi ──────────────────────────────────────────────
    public function users(Request $request)
    {
        $users = User::latest()->get();
        return view('admin.users', compact('users'));
    }

    public function reports(Request $request)
    {
        return redirect()->route('admin.dashboard');
    }

    // ─── Kateqoriya CRUD ─────────────────────────────────────────────────────
    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
        ]);
        Category::create($validated);
        return back()->with('success', 'Kateqoriya uğurla əlavə edildi.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $category->update($request->all());
        return back()->with('success', 'Kateqoriya uğurla yeniləndi.');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();
        return back()->with('success', 'Kateqoriya silindi.');
    }

    // ─── Köməkçi metodlar ────────────────────────────────────────────────────
    private function getDailyTrend(int $days): array
    {
        $labels = [];
        $opened = [];
        $closed = [];

        for ($i = 6; $i >= 0; $i--) {
            $date     = now()->subDays($i);
            $labels[] = $date->format('d M');
            $opened[] = Ticket::whereDate('created_at', $date)->count();
            $closed[] = Ticket::whereIn('status', ['resolved'])->whereDate('updated_at', $date)->count();
        }

        return ['labels' => $labels, 'opened' => $opened, 'closed' => $closed];
    }

    private function getCategoryDistribution(): array
    {
        $rows = Ticket::select('category_id', DB::raw('count(*) as total'))
            ->with('category')
            ->groupBy('category_id')
            ->get();

        $colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#f97316'];
        $labels = [];
        $data   = [];
        $cols   = [];

        foreach ($rows as $i => $row) {
            $labels[] = optional($row->category)->name ?? 'Digər';
            $data[]   = $row->total;
            $cols[]   = $colors[$i % count($colors)];
        }

        if (empty($labels)) {
            $labels = ['Məlumat yoxdur'];
            $data   = [1];
            $cols   = ['#334155'];
        }

        return ['labels' => $labels, 'data' => $data, 'colors' => $cols];
    }

    private function getAgentPerformance(): array
    {
        $agents   = User::role('support_agent')->get();
        $labels   = [];
        $resolved = [];
        $violated = [];

        foreach ($agents as $agent) {
            $labels[]   = $agent->name;
            $resolved[] = Ticket::where('assigned_to', $agent->id)
                ->where('status', 'resolved')->count();
            $violated[] = Ticket::where('assigned_to', $agent->id)
                ->where('is_sla_violated', true)->count();
        }

        if (empty($labels)) {
            $labels   = ['Mütəxəssis yoxdur'];
            $resolved = [0];
            $violated = [0];
        }

        return ['labels' => $labels, 'resolved' => $resolved, 'violated' => $violated, 'avg_hours' => []];
    }

    private function getPriorityDistribution(): array
    {
        return [
            'urgent' => Ticket::where('priority', 'urgent')->count(),
            'high'   => Ticket::where('priority', 'high')->count(),
            'medium' => Ticket::where('priority', 'medium')->count(),
            'low'    => Ticket::where('priority', 'low')->count(),
        ];
    }

    private function getAvgResolutionHours(): float
    {
        $avg = Ticket::where('status', 'resolved')
            ->whereNotNull('resolved_at')
            ->get()
            ->avg(fn($t) => $t->created_at->diffInHours($t->resolved_at));

        return round($avg ?? 0.0, 1);
    }
}