<?php
// database/migrations/2024_01_01_000006_create_sla_configs_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_configs', function (Blueprint $table) {
            $table->id();
            $table->enum('priority', ['urgent', 'high', 'medium', 'low'])->unique();
            $table->integer('response_hours');    // İlk cavab müddəti (saat)
            $table->integer('resolution_hours'); // Həll müddəti (saat)
            $table->string('label_az');           // Azərbaycan dilində ad
            $table->string('color');              // Badge rəngi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_configs');
    }
};