<?php
// app/Services/SlaService.php

namespace App\Services;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SlaService
{
    /**
     * Bütün aktiv biletlərin SLA statusunu yoxlayır.
     * Bu metod cron tərəfindən hər dəqiqə çağırılır.
     */
    public function checkAndViolate(): array
    {
        $violated = 0;
        $checked  = 0;

        // SLA vaxtı bitmiş, hələ pozulmamış, aktiv biletlər
        $tickets = Ticket::active()
            ->where('is_sla_violated', false)
            ->whereNotNull('sla_deadline')
            ->where('sla_deadline', '<', now())
            ->get();

        foreach ($tickets as $ticket) {
            $this->markAsViolated($ticket);
            $violated++;
            $checked++;
        }

        // SLA-ya 30 dəqiqə qalan biletlər üçün xəbərdarlıq mesajı
        $this->sendSlaWarnings();

        Log::info("SLA Check: {$checked} bilet yoxlandı, {$violated} pozuntu tapıldı.");

        return ['checked' => $checked, 'violated' => $violated];
    }

    /**
     * Bilet üçün SLA pozuntusunu qeydə alır
     */
    public function markAsViolated(Ticket $ticket): void
    {
        $ticket->update([
            'is_sla_violated' => true,
            'sla_violated_at' => now(),
        ]);

        // Sistem mesajı əlavə et
        TicketMessage::create([
            'ticket_id'        => $ticket->id,
            'user_id'          => $ticket->assigned_to ?? $ticket->customer_id,
            'message'          => "🚨 SLA müddəti bitdi! Bu bilet vaxtında cavablandırılmayıb. " .
                                  "Bilet: {$ticket->ticket_number}",
            'sender_type'      => 'system',
            'is_system_message' => true,
            'system_event'     => 'sla_violated',
        ]);

        Log::warning("SLA VIOLATED: Ticket #{$ticket->ticket_number}");
    }

    /**
     * SLA-ya 30 dəqiqə qalan biletlər üçün xəbərdarlıq göndərir
     */
    private function sendSlaWarnings(): void
    {
        $warningTime = now()->addMinutes(30);

        $tickets = Ticket::active()
            ->where('is_sla_violated', false)
            ->whereNotNull('sla_deadline')
            ->whereBetween('sla_deadline', [now(), $warningTime])
            ->whereDoesntHave('messages', function ($q) {
                $q->where('system_event', 'sla_warning')
                  ->where('created_at', '>', now()->subHour());
            })
            ->get();

        foreach ($tickets as $ticket) {
            $remaining = now()->diffInMinutes($ticket->sla_deadline);

            TicketMessage::create([
                'ticket_id'        => $ticket->id,
                'user_id'          => $ticket->assigned_to ?? $ticket->customer_id,
                'message'          => "⚠️ Diqqət! Bu biletin SLA müddətinə {$remaining} dəqiqə qalıb.",
                'sender_type'      => 'system',
                'is_system_message' => true,
                'system_event'     => 'sla_warning',
            ]);
        }
    }

    /**
     * Agent cavab verdikdə SLA-nı sıfırlayır
     */
    public function resetOnAgentResponse(Ticket $ticket): void
    {
        if (in_array($ticket->status, ['resolved', 'closed'])) {
            return;
        }

        // Prioritetə görə yeni SLA hesablanır
        $newDeadline = Ticket::calculateSlaDeadline($ticket->priority);

        $ticket->update([
            'sla_deadline'    => $newDeadline,
            'is_sla_violated' => false,
            'sla_violated_at' => null,
        ]);
    }

    /**
     * SLA Hesabatı - Admin üçün statistika
     */
    public function getStatistics(int $days = 30): array
    {
        $from = now()->subDays($days);

        $total      = Ticket::where('created_at', '>=', $from)->count();
        $violated   = Ticket::where('created_at', '>=', $from)->where('is_sla_violated', true)->count();
        $onTime     = $total - $violated;
        $compliance = $total > 0 ? round(($onTime / $total) * 100, 1) : 100;

        return [
            'total'      => $total,
            'violated'   => $violated,
            'on_time'    => $onTime,
            'compliance' => $compliance,
        ];
    }
}