<?php
// app/Services/AutoAssignmentService.php

namespace App\Services;

use App\Models\User;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AutoAssignmentService
{
    /**
     * Least-Loaded alqoritmi ilə ən az yüklü agenti tapır
     * və biletə təyin edir.
     */
    public function assign(Ticket $ticket): ?User
    {
        $agent = $this->findLeastLoadedAgent($ticket->category_id);

        if (!$agent) {
            Log::warning("AutoAssign: Uyğun agent tapılmadı. Ticket #{$ticket->ticket_number}");
            return null;
        }

        $ticket->update([
            'assigned_to' => $agent->id,
            'status'      => 'in_progress',
        ]);

        // Sistem mesajı yaz
        TicketMessage::create([
            'ticket_id'        => $ticket->id,
            'user_id'          => $agent->id,
            'message'          => "👤 Bilet avtomatik olaraq **{$agent->name}** mütəxəssisinə təyin edildi.",
            'sender_type'      => 'system',
            'is_system_message' => true,
            'system_event'     => 'assigned',
        ]);

        Log::info("AutoAssign: Ticket #{$ticket->ticket_number} → Agent: {$agent->name} (ID: {$agent->id})");

        return $agent;
    }

    /**
     * Ən az "in_progress" biletə sahib olan aktiv agenti tapır.
     * Bərabər yükdə ən son cavab verən agent seçilir (FIFO ədaləti).
     */
    private function findLeastLoadedAgent(?int $categoryId = null): ?User
    {
        $agents = User::role('support_agent')
            ->where('is_active', true)
            ->with(['assignedTickets' => function ($q) {
                $q->where('status', 'in_progress');
            }])
            ->get();

        if ($agents->isEmpty()) {
            return null;
        }

        // Hər agentin yükünü hesabla
        $ranked = $agents->map(function (User $agent) {
            return [
                'agent'         => $agent,
                'active_count'  => $agent->assignedTickets->count(),
                'last_assigned' => $agent->assignedTickets->max('created_at') ?? '2000-01-01',
            ];
        });

        // Əvvəlcə aktiv bilet sayına görə, bərabər olsa son təyin olunma tarixinə görə sırala
        $sorted = $ranked->sortBy([
            ['active_count', 'asc'],
            ['last_assigned', 'asc'],
        ]);

        return $sorted->first()['agent'];
    }

    /**
     * Manuel agent təyini (Admin tərəfindən)
     */
    public function manualAssign(Ticket $ticket, User $agent, User $assignedBy): void
    {
        $oldAgent = $ticket->agent;

        $ticket->update([
            'assigned_to' => $agent->id,
            'status'      => 'in_progress',
        ]);

        $message = $oldAgent
            ? "🔄 Bilet **{$oldAgent->name}** mütəxəssisindən **{$agent->name}** mütəxəssisinə yenidən təyin edildi."
            : "👤 Bilet **{$agent->name}** mütəxəssisinə **{$assignedBy->name}** tərəfindən təyin edildi.";

        TicketMessage::create([
            'ticket_id'        => $ticket->id,
            'user_id'          => $assignedBy->id,
            'message'          => $message,
            'sender_type'      => 'system',
            'is_system_message' => true,
            'system_event'     => 'assigned',
        ]);
    }

    /**
     * Agent yükü üzrə hesabat
     */
    public function getAgentLoadReport(): Collection
    {
        return User::role('support_agent')
            ->where('is_active', true)
            ->withCount([
                'assignedTickets as total_tickets',
                'assignedTickets as active_tickets' => fn($q) => $q->active(),
                'assignedTickets as sla_violated'   => fn($q) => $q->slaViolated(),
            ])
            ->orderBy('active_tickets', 'asc')
            ->get();
    }
}