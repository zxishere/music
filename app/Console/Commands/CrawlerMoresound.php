<?php

namespace App\Console\Commands;

use App\Jobs\GetMusic;
use App\Traits\Moresound;
use Illuminate\Console\Command;

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
            // return GetMusic::dispatch($this->option('mid'), $qq)->onQueue('low');
            return $this->get($this->option('mid'), true, $qq, $this->option('force'));
        }
        $keyword      = $this->argument('keyword');

        $data = [
            'p' => 1,
            'w' => $keyword,
            'n' => 50
        ];

        $result = $this->post($data, 'search=qq');
        $this->output->writeln('total '.$result->totalnum);
        $pages = ceil($result->totalnum/50);
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
        if ($this->option('download') == true) {
            foreach ($mids as $mid) {
                GetMusic::dispatch($mid, $qq)->onQueue('low');
                // $this->get($mid, true, $qq);
            }
        }
        for ($i=2; $i<=$pages; $i++) {
            $this->_getList($i, $keyword, $qq);
        }
    }

    private function _getList($page, $keyword, $qq)
    {
        $data = [
            'p' => $page,
            'w' => $keyword,
            'n' => 50
        ];

        $result = $this->post($data, 'search=qq');
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
        if ($this->option('download') == true) {
            foreach ($mids as $mid) {
                GetMusic::dispatch($mid, $qq)->onQueue('low');
                // $this->get($mid, true, $qq);
            }
        }
    }
}
