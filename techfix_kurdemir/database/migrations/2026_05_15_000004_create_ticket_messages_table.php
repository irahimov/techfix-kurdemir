<?php
// database/migrations/2024_01_01_000004_create_ticket_messages_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->text('message');
            $table->enum('sender_type', ['customer', 'agent', 'system'])->default('customer');

            // Sistem mesajı (status dəyişikliyi, SLA xəbərdarlığı)
            $table->boolean('is_system_message')->default(false);
            $table->string('system_event')->nullable(); // 'status_changed', 'assigned', 'sla_warning'

            // Oxunma statusu
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();

            $table->timestamps();

            $table->index(['ticket_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_messages');
    }
};