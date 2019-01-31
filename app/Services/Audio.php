<?php
namespace App\Services;

use FFMpeg\Format\FormatInterface;
use FFMpeg\Media\Audio as BaseAudio;
use FFMpeg\Filters\Audio\SimpleFilter;

class Audio extends BaseAudio
{
    protected function buildCommand(FormatInterface $format, $outputPathfile)
    {
        $commands = ['-y', '-i', $this->pathfile];

        $filters = clone $this->filters;
        $filters->add(new SimpleFilter($format->getExtraParams(), 10));

        foreach ($filters as $filter) {
            $commands = array_merge($commands, $filter->apply($this, $format));
        }

        $commands[] = '-c';
        $commands[] = 'copy';
        $commands[] = $outputPathfile;
        return $commands;
    }
}
