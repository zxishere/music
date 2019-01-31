<?php

namespace App\Traits;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Helper\Table;

trait Moresound
{
    use Downloader, Common {
        config as commonConfig;
    }

    protected $base_uri = "http://moresound.tk";

    public function showTable($songData)
    {
        if (count($songData) > 0) {
            $table = new Table($this->output);
            $table->setHeaders(['#', '歌名', '歌手', '专辑', '长度', 'mid'])->setRows($songData);
            $table->render();
        }
    }

    public function config()
    {
        $config = $this->commonConfig();
        $config['base_uri'] = $this->base_uri;
        $config['cookies'] = true;
        $config['headers']['X-Requested-With'] = "XMLHttpRequest";
        $config['headers']['Cookie'] = "Tip_of_the_day=2; encrypt_data=72e7caf51a636214432bbea31af14f68a3a630997be63b6e82bd4bc96e308cdc5fe405683cca97926a8432d2a83c2d51a58206141c408ca3b947a768c7eb9e35d8fb043c822a93b069538dec034ee9c948e52fce07d5e78c9810b459f07fc6935b4190db2ae8e153961ee07bd763470d75f7d86558d31b0ad15549eb832e9c12";
        return $config;
    }

    public function post($data, $type)
    {
        $client = new Client($this->config());
        $response = $client->request('POST', '/music/api.php?'.$type, [
            'form_params' => $data
        ]);
        return json_decode($response->getBody()->getContents());
    }

    public function get($mid, $showprogress, $qq, $force = false)
    {
        $result = $this->post(['mid' => $mid], 'get_song=qq');
        $urls = (array)$result->url;
        if (count($urls) > 0) {
            foreach (['320MP3', '128MP3'] as $quality) {
                if (isset($urls[$quality])) {
                    $url = $urls[$quality];
                    break;
                }
            }
            if (!$url) {
                $url = array_values($urls)[0];
            }
            $client = new Client($this->config());
            $response = $client->get('/music/'.$url);
            $download = json_decode($response->getBody()->getContents());
            dump($download);
            if (isset($download->url)) {
                $result->song  = str_replace('/', ' & ', $result->song);
                $result->album = str_replace('/', ' & ', $result->album);
                if ($result->album != '' && $result->album != '空' && !str_contains($result->singer, '/')) {
                    $directory = $result->singer .' - '. $result->album;
                } else {
                    if (str_contains($result->singer, '/')) {
                        $result->singer = str_replace('/', ' & ', $result->singer);
                        $directory = $result->album;
                    }
                    if ($result->album == '' || $result->album == '空') {
                        $directory = $result->singer;
                    }
                }
                try {
                    $mp3 = $this->download($download->url, $qq.'/'.$directory, $result->singer .' - '. $result->song.'.'.$download->suffix, $showprogress);
                    $cover = $this->download($urls['专辑封面'], $qq.'/'.$directory, 'cover.jpg', $showprogress);
                    if (isset($urls['lrc'])) {
                        $this->download('/music/'.$urls['lrc'], $qq.'/'.$directory, $result->singer .' - '. $result->song.'.lrc', $showprogress, $this->config(), 'webdav');
                    }
                } catch (\Exception $e) {
                    $this->output->writeln('Downloading Error: '. $e->getMessage());
                }
            }
        }
    }
}
