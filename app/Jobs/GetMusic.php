<?php

namespace App\Jobs;

use App\Traits\Moresound;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class GetMusic implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Moresound;

    public $tries = 3;

    protected $mid;
    protected $qq;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($mid, $qq)
    {
        $this->mid = $mid;
        $this->qq  = $qq;
    }

    public function tags()
    {
        return ['mp3', 'mid:'.$this->mid];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->get($this->mid, false, $this->qq);
        return ;
    }
}
