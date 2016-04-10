<?php

namespace AE\IpCamera;

class VideoProcess
{

    protected $name;
    protected $path;

    protected $segment_time = 60;

    // Storage settings
    protected $format = 'mp4';
    protected $audio_codec = 'mp2';
    protected $timestamp_format = '%Y-%m-%d_%H:%M:%S';

    // Streaming settings
    protected $stream_resolution = 'hd480';

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     * @return VideoProcess
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param mixed $path
     * @return VideoProcess
     */
    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return int
     */
    public function getSegmentTime()
    {
        return $this->segment_time;
    }

    /**
     * @param int $segment_time
     * @return VideoProcess
     */
    public function setSegmentTime($segment_time)
    {
        $this->segment_time = $segment_time;
        return $this;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @param string $format
     * @return VideoProcess
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @return string
     */
    public function getAudioCodec()
    {
        return $this->audio_codec;
    }

    /**
     * @param string $audio_codec
     * @return VideoProcess
     */
    public function setAudioCodec($audio_codec)
    {
        $this->audio_codec = $audio_codec;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimestampFormat()
    {
        return $this->timestamp_format;
    }

    /**
     * @param string $timestamp_format
     * @return VideoProcess
     */
    public function setTimestampFormat($timestamp_format)
    {
        $this->timestamp_format = $timestamp_format;
        return $this;
    }

    /**
     * @return string
     */
    public function getStreamResolution()
    {
        return $this->stream_resolution;
    }

    /**
     * @param string $stream_resolution
     * @return VideoProcess
     */
    public function setStreamResolution($stream_resolution)
    {
        $this->stream_resolution = $stream_resolution;
        return $this;
    }

    public function __construct($name, $path)
    {
        $this->name = $name;
        $this->path = $path;
    }

    public function run()
    {
        //$ffmpeg_command = "ffmpeg -i {$this->path} -b:a 32k -b:v 64k -maxrate 128k -bufsize 512k -vf scale={$this->stream_resolution} http://localhost:8090/feed1.ffm";
        $dir = "/app/videos/{$this->name}";
        if(!file_exists($dir)){
            mkdir($dir, 0777, true);
        }
        $ffmpeg_command = "ffmpeg -i {$this->path} -c copy -map 0 -acodec {$this->audio_codec} -f segment -strftime 1 -segment_time {$this->segment_time} -segment_format {$this->format} {$dir}/{$this->timestamp_format}.mp4 ";

        echo "\n\n\nCommand: {$ffmpeg_command}\n\n\n\n";

        exec($ffmpeg_command);
    }
}
