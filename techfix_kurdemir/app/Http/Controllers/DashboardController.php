<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $hasTickets = Schema::hasTable('tickets');
        $hasUsers = Schema::hasTable('users');

        // 1. KPI Statistikaları
        $stats = [
            'total_tickets'   => $hasTickets ? DB::table('tickets')->count() : 0,
            'open_tickets'    => $hasTickets ? DB::table('tickets')->whereIn('status', ['open', 'pending'])->count() : 0,
            'closed_tickets'  => $hasTickets ? DB::table('tickets')->where('status', 'closed')->count() : 0,
            'resolved_today'  => $hasTickets ? DB::table('tickets')->where('status', 'resolved')->whereDate('updated_at', today())->count() : 0,
            'sla_violated'    => $hasTickets ? DB::table('tickets')->where('is_sla_violated', true)->count() : 0,
        ];

        // 2. Son Müraciətlər (Müştəri əlaqəsi ilə birlikdə)
        $recentTickets = collect([]);
        if ($hasTickets) {
            $recentTickets = DB::table('tickets')
                ->leftJoin('users as customers', 'tickets.customer_id', '=', 'customers.id')
                ->select('tickets.*', 'customers.name as customer_name')
                ->orderBy('tickets.created_at', 'desc')
                ->take(6)
                ->get()
                ->map(function($ticket) {
                    $ticket->status_label = ucfirst($ticket->status);
                    $ticket->status_color = match($ticket->status) {
                        'open' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                        'pending' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/20',
                        'closed', 'resolved' => 'bg-green-500/10 text-green-400 border-green-500/20',
                        default => 'bg-slate-500/10 text-slate-400 border-slate-500/20'
                    };
                    return $ticket;
                });
        }

        // 3. SLA Xəbərdarlıqları (İcra müddətinə az qalanlar)
        $slaWarnings = collect([]);
        if ($hasTickets) {
            $slaWarnings = DB::table('tickets')
                ->where('status', 'open')
                ->where('is_sla_violated', false)
                ->orderBy('created_at', 'asc')
                ->take(5)
                ->get()
                ->map(function($tw) {
                    $tw->priority_label = 'Yüksək';
                    $tw->priority_color = 'bg-red-500/10 text-red-400';
                    $tw->sla_remaining = '1 saat 45 dəq';
                    return $tw;
                });
        }

        // 4. Mütəxəssis (Agent) Yükü Cədvəli
        $agentLoad = collect([]);
        if ($hasTickets && $hasUsers) {
            $agentLoad = DB::table('users')
                ->where('role', 'agent')
                ->get()
                ->map(function($agent) {
                    $agent->avatar_url = 'https://ui-avatars.com/api/?name=' . urlencode($agent->name) . '&background=random&color=fff';
                    $agent->active_tickets = DB::table('tickets')->where('agent_id', $agent->id)->whereIn('status', ['open', 'pending'])->count();
                    $agent->total_tickets = DB::table('tickets')->where('agent_id', $agent->id)->count();
                    $agent->sla_violated = DB::table('tickets')->where('agent_id', $agent->id)->where('is_sla_violated', true)->count();
                    return $agent;
                });
        }

        // 5. Qrafik dataları
        $chartData = [
            'daily_trend' => [
                'labels' => ['01 May', '05 May', '10 May', '15 May', '17 May'],
                'opened' => [5, 12, 8, 15, $stats['total_tickets']],
                'closed' => [3, 8, 7, 10, $stats['resolved_today']],
            ],
            'category_pie' => [
                'labels' => ['Texniki Dəstək', 'Maliyyə', 'Sual/Təklif'],
                'data' => [40, 25, 35],
                'colors' => ['#60a5fa', '#34d399', '#f59e0b']
            ],
            'agent_performance' => [
                'labels' => $agentLoad->pluck('name')->toArray(),
                'resolved' => $agentLoad->pluck('total_tickets')->toArray(),
                'violated' => $agentLoad->pluck('sla_violated')->toArray(),
            ]
        ];

        if (empty($chartData['agent_performance']['labels'])) {
            $chartData['agent_performance']['labels'] = ['Sistem'];
            $chartData['agent_performance']['resolved'] = [0];
            $chartData['agent_performance']['violated'] = [0];
        }

        // 🔥 DÜZƏLİŞ BURADADIR:
        // Əgər linkin sonu '/admin' ilə bitmirsə (yəni sadəcə /dashboard-dırsa),
        // admin olsa belə onu birbaşa MÜŞTƏRİ panelinə (customer.dashboard) göndəririk!
        if ($user && ($user->role === 'super_admin' || $user->role === 'admin') && $request->is('admin*')) {
            return view('admin.dashboard', compact('stats', 'recentTickets', 'slaWarnings', 'agentLoad', 'chartData'));
        }

        // Müştəri paneli üçün olan blade görünüşü yüklənir
        return view('customer.dashboard', compact('stats', 'recentTickets', 'slaWarnings', 'agentLoad', 'chartData'));
    }
}