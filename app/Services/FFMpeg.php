<?php
namespace App\Services;

use FFMpeg\FFProbe;
use FFMpeg\Media\Video;
use FFMpeg\Driver\FFMpegDriver;
use FFMpeg\FFMpeg as BaseFFMpeg;
use FFMpeg\Exception\RuntimeException;
use FFMpeg\Exception\InvalidArgumentException;

class FFMpeg extends BaseFFMpeg
{

    /** @var FFMpegDriver */
    private $driver;
    /** @var FFProbe */
    private $ffprobe;

    public function __construct(FFMpegDriver $ffmpeg, FFProbe $ffprobe)
    {
        $this->driver = $ffmpeg;
        $this->ffprobe = $ffprobe;
    }

    public function open($pathfile)
    {
        if (null === $streams = $this->ffprobe->streams($pathfile)) {
            throw new RuntimeException(sprintf('Unable to probe "%s".', $pathfile));
        }

        if (0 < count($streams->videos())) {
            return new Video($pathfile, $this->driver, $this->ffprobe);
        } elseif (0 < count($streams->audios())) {
            return new Audio($pathfile, $this->driver, $this->ffprobe);
        }

        throw new InvalidArgumentException('Unable to detect file format, only audio and video supported');
    }
}
