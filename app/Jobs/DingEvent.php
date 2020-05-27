<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Exception;
use App\Services\DingTalkService;

class DingEvent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $payload;

    //任务最大尝试次数。
    public $tries = 3;
    public $timeout = 30;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        //
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(DingTalkService $dtService)
    {
        //
        $dtService->doDingEvent($this->payload);

    }


    public function failed(Exception $exception)
    {

    }
}
