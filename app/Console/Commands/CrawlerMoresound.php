<?php

namespace App\Console\Commands;

use GuzzleHttp\Pool;
use App\Jobs\GetMusic;
use GuzzleHttp\Client;
use App\Traits\Moresound;
use GuzzleHttp\Psr7\Request;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CrawlerMoresound extends Command
{
    use Moresound;

    protected $signature = 'crawler:moresound {keyword?} {--D|download} {--F|force} {--Q|qq=miao} {--M|mid=}';
    protected $description = 'Crawler Moresound';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $qq = $this->option('qq');
        if ($this->option('mid') !== null) {
            return GetMusic::dispatch($this->option('mid'), $qq)->onQueue('low');
            // return $this->get($this->option('mid'), true, $qq, $this->option('force'));
        }
        $keyword      = $this->argument('keyword');
        $needDownload = $this->option('download');

        $data = [
            'p' => 1,
            'w' => $keyword,
            'n' => 50
        ];

        $result = $this->post($data, 'search=qq');
        $this->output->writeln('total '.$result->totalnum);
        $songData = $mids = [];
        $count = 0;
        if (count($result->song_list) > 0) {
            foreach ($result->song_list as $song) {
                $count ++;
                $songData[] = [
                    $count,
                    explode('<sup', $song->songname)[0],
                    collect($song->singer)->pluck('name')->implode(','),
                    $song->albumname,
                    $song->interval,
                    $song->songmid
                ];
                $mids[] = $song->songmid;
            }
        }
        $this->showTable($songData);
        if ($needDownload == true) {
            foreach ($mids as $mid) {
                $this->get($mid, true, $qq);
            }
        }
    }
}
