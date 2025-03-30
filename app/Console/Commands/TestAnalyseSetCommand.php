<?php

namespace App\Console\Commands;

use App\Models\AnalyzedSet;
use App\Services\SetAnalyzerService;
use Illuminate\Console\Command;

class TestAnalyseSetCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-analyse-set-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    public function __construct(private readonly SetAnalyzerService $setAnalyzerService)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $set = AnalyzedSet::where('id', 4)->first();

        $this->setAnalyzerService
            ->analyzeSet($set);
    }
}
