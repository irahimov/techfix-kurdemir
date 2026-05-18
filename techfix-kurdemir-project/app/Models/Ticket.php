<?php
// app/Models/Ticket.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_number', 'title', 'description', 'category_id',
        'customer_id', 'assigned_to', 'status', 'priority',
        'sla_deadline', 'is_sla_violated', 'sla_violated_at',
        'tracking_number', 'device_model', 'device_serial',
        'rating', 'rating_comment', 'first_response_at',
        'resolved_at', 'closed_at',
    ];

    protected $casts = [
        'sla_deadline'    => 'datetime',
        'sla_violated_at' => 'datetime',
        'first_response_at' => 'datetime',
        'resolved_at'     => 'datetime',
        'closed_at'       => 'datetime',
        'is_sla_violated' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class)->orderBy('created_at', 'asc');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    // Status-u Azərbaycan dilinde qaytarır
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'new'              => 'Yeni',
            'in_progress'      => 'Baxılır',
            'waiting_agent'    => 'Agent Cavab Gözləyir',
            'waiting_customer' => 'Müştəri Cavab Gözləyir',
            'in_service'       => 'Kuryerdə/Servisdə',
            'resolved'         => 'Həll Olundu',
            'closed'           => 'Bağlandı',
            default            => 'Naməlum',
        };
    }

    // Status badge rəngi (Tailwind class)
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'new'              => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
            'in_progress'      => 'bg-yellow-500/20 text-yellow-300 border-yellow-500/30',
            'waiting_agent'    => 'bg-orange-500/20 text-orange-300 border-orange-500/30',
            'waiting_customer' => 'bg-purple-500/20 text-purple-300 border-purple-500/30',
            'in_service'       => 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30',
            'resolved'         => 'bg-green-500/20 text-green-300 border-green-500/30',
            'closed'           => 'bg-gray-500/20 text-gray-300 border-gray-500/30',
            default            => 'bg-gray-500/20 text-gray-300 border-gray-500/30',
        };
    }

    // Prioritet-i Azərbaycan dilinde qaytarır
    public function getPriorityLabelAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'Təcili',
            'high'   => 'Yüksək',
            'medium' => 'Orta',
            'low'    => 'Aşağı',
            default  => 'Orta',
        };
    }

    // Prioritet rəngi
    public function getPriorityColorAttribute(): string
    {
        return match($this->priority) {
            'urgent' => 'bg-red-500/20 text-red-300 border-red-500/30',
            'high'   => 'bg-orange-500/20 text-orange-300 border-orange-500/30',
            'medium' => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
            'low'    => 'bg-gray-500/20 text-gray-400 border-gray-500/30',
            default  => 'bg-blue-500/20 text-blue-300 border-blue-500/30',
        };
    }

    // SLA-nın nə qədər vaxtı qaldığını hesablayır
    public function getSlaRemainingAttribute(): ?string
    {
        if (!$this->sla_deadline || in_array($this->status, ['resolved', 'closed'])) {
            return null;
        }

        if ($this->is_sla_violated) {
            return 'Vaxt bitib';
        }

        $diff = now()->diffInMinutes($this->sla_deadline, false);

        if ($diff < 0) return 'Vaxt bitib';
        if ($diff < 60) return $diff . ' dəq qaldı';

        $hours = intdiv($diff, 60);
        $mins  = $diff % 60;
        return "{$hours} saat {$mins} dəq qaldı";
    }

    // SLA faizi (progress bar üçün)
    public function getSlaPercentageAttribute(): int
    {
        if (!$this->sla_deadline) return 100;

        $totalMinutes = $this->created_at->diffInMinutes($this->sla_deadline);
        $passedMinutes = $this->created_at->diffInMinutes(now());

        if ($totalMinutes <= 0) return 100;

        $percentage = ($passedMinutes / $totalMinutes) * 100;
        return min(100, max(0, (int) $percentage));
    }

    // ─── Static Methods ───────────────────────────────────────────────────────

    // SLA müddətini prioritetə görə hesablayır
    public static function calculateSlaDeadline(string $priority): Carbon
    {
        $hours = match($priority) {
            'urgent' => 2,
            'high'   => 6,
            'medium' => 12,
            'low'    => 24,
            default  => 12,
        };

        return now()->addHours($hours);
    }

    // Yeni ticket nömrəsi yaradır: TF-2024-0001
    public static function generateTicketNumber(): string
    {
        $year  = date('Y');
        $count = self::whereYear('created_at', $year)->count() + 1;
        return 'TF-' . $year . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['closed', 'resolved']);
    }

    public function scopeSlaViolated($query)
    {
        return $query->where('is_sla_violated', true);
    }

    public function scopeByAgent($query, int $agentId)
    {
        return $query->where('assigned_to', $agentId);
    }
}