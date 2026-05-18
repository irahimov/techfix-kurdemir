<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'avatar',
        'is_active', 'role', 'last_seen_at',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_seen_at'      => 'datetime',
        'password'          => 'hashed',
        'is_active'         => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'customer_id');
    }

    public function assignedTickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'assigned_to');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    // Agentin aktiv bilet sayı (auto-assignment üçün)
    public function getActiveTicketsCountAttribute(): int
    {
        return $this->assignedTickets()
            ->where('status', 'in_progress')
            ->count();
    }

    // Avatar URL
    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        // Baş hərflərə görə placeholder
        $initials = collect(explode(' ', $this->name))
            ->map(fn($w) => strtoupper($w[0]))
            ->take(2)
            ->join('');
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->name)
            . '&background=1e293b&color=60a5fa&bold=true&size=128';
    }

    // Agentin KPI məlumatları
    public function getKpiAttribute(): array
    {
        $tickets = $this->assignedTickets();

        $resolved = (clone $tickets)->where('status', 'resolved')
            ->whereNotNull('resolved_at')->get();

        $avgResolutionHours = $resolved->avg(function ($t) {
            return $t->created_at->diffInHours($t->resolved_at);
        }) ?? 0;

        return [
            'total'           => (clone $tickets)->count(),
            'resolved'        => $resolved->count(),
            'sla_violated'    => (clone $tickets)->where('is_sla_violated', true)->count(),
            'avg_resolution'  => round($avgResolutionHours, 1),
            'active'          => (clone $tickets)->active()->count(),
        ];
    }

    // ─── Role Helpers ─────────────────────────────────────────────────────────

    public function isAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isAgent(): bool
    {
        return $this->hasRole('support_agent');
    }

    public function isCustomer(): bool
    {
        return $this->hasRole('customer');
    }
}