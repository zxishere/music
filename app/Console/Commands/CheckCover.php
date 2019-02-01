<?php

namespace App\Console\Commands;

use App\Services\FFMpeg;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckCover extends Command
{
    protected $signature = 'check:cover {--Q|qq=hiro}';
    protected $description = 'CheckCover';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $qq = $this->option('qq');
        $files = Storage::disk('music')->allFiles($qq);
        $folders = [];
        foreach ($files as $key => $file) {
            $folders[explode('/', $file)[1]][] = explode('/', $file)[2];
        }
        unset($files);
        foreach ($folders as $key => $folder) {
            $files = collect($folder);
            if ($files->contains('cover.jpg')) {
                $directory = public_path('music/'.$qq.'/'.$key.'/');
                $cover = $directory.'cover.jpg';
                $ffprobe = \FFMpeg\FFProbe::create([
                     'ffmpeg.binaries'  => '/usr/bin/ffmpeg',
                     'ffprobe.binaries' => '/usr/bin/ffprobe'
                ]);
                foreach ($files as $mp3) {
                    if (substr(strrchr($mp3, '.'), 1) == 'mp3') {
                        try {
                            $newName = str_replace('.mp3', '_coverd.mp3', $mp3);
                            if ($ffprobe->format($directory.$mp3)->get('nb_streams') == 1) {
                                $ffmpeg = FFMpeg::create();
                                $audio = $ffmpeg->open($directory.$mp3);
                                $audio->filters()->addMetadata(["artwork" => $cover]);
                                $audio->save(new \FFMpeg\Format\Audio\Mp3(), $directory.$newName);
                                Storage::disk('music')->delete($qq.'/'.$key.'/'.$mp3);
                                Storage::disk('music')->move($qq.'/'.$key.'/'.$newName, $qq.'/'.$key.'/'.$mp3);
                                $this->output->writeln("<info>Ser cover complete : ".$mp3."</info>");
                            } else {
                                $this->output->writeln("<info>Cover exist : ".$mp3."</info>");
                            }
                        } catch (\Exception $e) {
                            $this->output->writeln('<error>Ser Cover Error: '. $e->getMessage().'</error>');
                        }
                    }
                }
            }
        }
    }
}
