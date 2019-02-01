<?php

namespace App\Console\Commands;

use App\Jobs\GetMusic;
use App\Traits\Moresound;
use Illuminate\Console\Command;

class CrawlerMoresound extends Command
{
    use Moresound;

    protected $signature = 'crawler:moresound {keyword?} {--D|download} {--Q|qq=miao} {--M|mid=}';
    protected $description = 'Crawler Moresound';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $qq = $this->option('qq');
        if ($this->option('mid') !== null) {
            return GetMusic::dispatch($this->option('mid'), $qq, $keyword)->onQueue('low');
            // return $this->get($this->option('mid'), true, $qq);
        }
        $keyword = trim($this->argument('keyword'));

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
                $singers = collect($song->singer)->pluck('name')->implode(',');
                $count ++;
                $songData[] = [
                    $count,
                    explode('<sup', $song->songname)[0],
                    $singers,
                    $song->albumname,
                    $song->interval,
                    $song->songmid
                ];
                if ($this->option('download') == true && str_contains($singers, $keyword)) {
                    GetMusic::dispatch($song->songmid, $qq, $keyword)->onQueue('low');
                }
            }
        }
        $this->showTable($songData);

        for ($i=2; $i<=$pages; $i++) {
            $this->_getList($i, $keyword, $qq, $pages);
        }
    }

    private function _getList($page, $keyword, $qq, $pages)
    {
        $data = [
            'p' => $page,
            'w' => $keyword,
            'n' => 50
        ];
        $this->output->writeln('page '.$page.' / '.$pages);
        $result = $this->post($data, 'search=qq');
        $songData = $mids = [];
        $count = 0;
        if (count($result->song_list) > 0) {
            foreach ($result->song_list as $song) {
                $singers = collect($song->singer)->pluck('name')->implode(',');
                $count ++;
                $songData[] = [
                    $count,
                    explode('<sup', $song->songname)[0],
                    $singers,
                    $song->albumname,
                    $song->interval,
                    $song->songmid
                ];
                if ($this->option('download') == true && str_contains($singers, $keyword)) {
                    GetMusic::dispatch($song->songmid, $qq, $keyword)->onQueue('low');
                }
            }
        }
        $this->showTable($songData);
    }
}
