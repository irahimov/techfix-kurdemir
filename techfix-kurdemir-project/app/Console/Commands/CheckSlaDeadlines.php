<?php
// app/Console/Commands/CheckSlaDeadlines.php

namespace App\Console\Commands;

use App\Services\SlaService;
use Illuminate\Console\Command;

class CheckSlaDeadlines extends Command
{
    protected $signature   = 'sla:check {--dry-run : Yalnız nəticəni göstər, dəyişiklik etmə}';
    protected $description = 'SLA müddətlərini yoxla və vaxtı keçmiş biletləri işarələ';

    public function __construct(private readonly SlaService $slaService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $this->info('🔍 SLA yoxlaması başladı: ' . now()->format('d.m.Y H:i:s'));

        if ($this->option('dry-run')) {
            $this->warn('DRY-RUN rejimi: Heç bir dəyişiklik edilmir.');
        }

        $result = $this->slaService->checkAndViolate();

        $this->table(
            ['Göstərici', 'Nəticə'],
            [
                ['Yoxlanan biletlər', $result['checked']],
                ['Pozuntu tapılan',   $result['violated']],
            ]
        );

        if ($result['violated'] > 0) {
            $this->error("⚠️  {$result['violated']} bilet SLA pozuntusuna keçdi!");
        } else {
            $this->info('✅ Bütün biletlər SLA daxilindədir.');
        }

        return Command::SUCCESS;
    }
}