<?php
// database/migrations/2024_01_01_000005_create_attachments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ticket_id')->constrained()->onDelete('cascade');
            $table->foreignId('ticket_message_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('uploaded_by')->constrained('users')->onDelete('cascade');

            $table->string('original_name');              // İstifadəçinin yüklədiyi fayl adı
            $table->string('stored_name');                // Sistemdə saxlanılan ad (UUID)
            $table->string('file_path');                  // storage/app/public/tickets/...
            $table->string('file_type');                  // image/jpeg, application/pdf
            $table->string('mime_type');
            $table->unsignedBigInteger('file_size');      // Bayt ilə

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};