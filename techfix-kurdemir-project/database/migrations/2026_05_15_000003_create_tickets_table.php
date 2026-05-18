<?php
// database/migrations/2024_01_01_000003_create_tickets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();        // TF-2024-001
            $table->string('title');
            $table->text('description');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('customer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');

            // Status sütunu - biletin həyat dövrü
            $table->enum('status', [
                'new',           // Yeni
                'in_progress',   // Baxılır
                'waiting_agent', // Agent Cavab Gözləyir
                'waiting_customer', // Müştəri Cavab Gözləyir
                'in_service',    // Kuryerdə/Servisdə
                'resolved',      // Həll Olundu
                'closed'         // Bağlandı
            ])->default('new');

            // Prioritet sütunu
            $table->enum('priority', [
                'urgent',   // Təcili  - 2 saat
                'high',     // Yüksək  - 6 saat
                'medium',   // Orta    - 12 saat
                'low'       // Aşağı   - 24 saat
            ])->default('medium');

            // SLA (Service Level Agreement) sütunları
            $table->timestamp('sla_deadline')->nullable();   // SLA son tarixi
            $table->boolean('is_sla_violated')->default(false); // SLA pozulubmu?
            $table->timestamp('sla_violated_at')->nullable(); // Nə vaxt pozuldu?

            // Kargo/Servis izləmə
            $table->string('tracking_number')->nullable();   // Kuryer nömrəsi
            $table->string('device_model')->nullable();      // Cihaz modeli
            $table->string('device_serial')->nullable();     // Serial nömrəsi

            // Qiymətləndirmə (müştəri tərəfindən)
            $table->tinyInteger('rating')->nullable();       // 1-5 ulduz
            $table->text('rating_comment')->nullable();

            // Tarixlər
            $table->timestamp('first_response_at')->nullable();  // İlk cavab vaxtı
            $table->timestamp('resolved_at')->nullable();         // Həll olunma vaxtı
            $table->timestamp('closed_at')->nullable();           // Bağlanma vaxtı
            $table->timestamps();

            // İndekslər (sürətli sorğu üçün)
            $table->index(['status', 'priority']);
            $table->index(['assigned_to', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index('sla_deadline');
            $table->index('is_sla_violated');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};