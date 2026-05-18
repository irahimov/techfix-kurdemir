<?php
// app/Models/TicketMessage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TicketMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_id', 'user_id', 'message', 'sender_type',
        'is_system_message', 'system_event', 'is_read', 'read_at',
    ];

    protected $casts = [
        'is_system_message' => 'boolean',
        'is_read'           => 'boolean',
        'read_at'           => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    // Sistem mesajı ikonları
    public function getSystemIconAttribute(): string
    {
        return match($this->system_event) {
            'status_changed' => '🔄',
            'assigned'       => '👤',
            'sla_warning'    => '⚠️',
            'sla_violated'   => '🚨',
            'resolved'       => '✅',
            'closed'         => '🔒',
            default          => 'ℹ️',
        };
    }
}