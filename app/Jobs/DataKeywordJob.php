<?php

namespace App\Jobs;

use App\Models\Keyword;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DataKeywordJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $data;
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        foreach ($this->data as $item) {
            Keyword::create([
                'request_id' => $item['request_id'],
                'q' => $item['q'],
                'domain' => $item['domain'],
                'position' => $item['position'] ?? null,
            ]);
        }
    }
}
