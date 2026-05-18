<?php
// routes/console.php  (Laravel 11 üçün)
// Laravel 11-də app/Console/Kernel.php yoxdur, schedule routes/console.php-dədir

use Illuminate\Support\Facades\Schedule;

// SLA yoxlaması hər dəqiqə işə düşür
Schedule::command('sla:check')->everyMinute()
    ->withoutOverlapping()   // Paralel işləməni önlə
    ->runInBackground()      // Arxa planda işlə
    ->appendOutputTo(storage_path('logs/sla-cron.log')); // Logla

// Köhnə bildirişləri təmizlə (hər həftə)
Schedule::command('model:prune')->weekly();