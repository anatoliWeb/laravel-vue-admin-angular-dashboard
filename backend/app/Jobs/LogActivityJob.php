<?php

namespace App\Jobs;

use App\Services\ActivityService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LogActivityJob implements ShouldQueue
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Dedicated queue for activity writes.
     *
     * WHY:
     * Isolates audit logging throughput from other async domains
     * like notifications or future realtime delivery jobs.
     */
//    public $queue = 'activity';

    /**
     * Create a new job instance.
     */
    public function __construct(
        public ?int $userId,
        public string $action,
        public ?string $description = null,
        public array $meta = [],
    ) {
        $this->onQueue('activity');
    }

    /**
     * Execute the job.
     */
    public function handle(ActivityService $activityService): void
    {
        $activityService->write(
            $this->userId,
            $this->action,
            $this->description,
            $this->meta,
        );
    }
}
