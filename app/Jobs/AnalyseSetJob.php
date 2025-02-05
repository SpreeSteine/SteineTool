<?php

namespace App\Jobs;

use App\Models\AnalyzedSet;
use App\Services\SetAnalyzerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class AnalyseSetJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 600;

    public function __construct(private readonly AnalyzedSet $set) {}

    public function handle(SetAnalyzerService $setAnalyzerService): void
    {
        $setAnalyzerService->analyzeSet($this->set);
    }
}
