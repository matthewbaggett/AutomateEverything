<?php

namespace AE\IpCamera;

use Predis\Client as RedisClient;

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
    
    /** @var RedisClient */
    protected $redis;

    /**
     * @return RedisClient
     */
    public function getRedis()
    {
        return $this->redis;
    }

    /**
     * @param RedisClient $redis
     * @return VideoProcess
     */
    public function setRedis(RedisClient $redis)
    {
        $this->redis = $redis;
        return $this;
    }

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
        $this->getRedis()->publish("cameras/debug", "{$this->getName()}: Starting Camera Monitoring for {$this->getName()}");
        $dir = "/app/videos/{$this->getName()}";
        if(!file_exists($dir)){
            mkdir($dir, 0777, true);
            $this->getRedis()->publish("cameras/debug", "{$this->getName()}: mkdir {$dir}");
        }
        $this->getRedis()->publish("cameras/debug", "{$this->getName()}: Segment time is {$this->getSegmentTime()}");
        $this->getRedis()->publish("cameras/debug", "{$this->getName()}: Format is {$this->getFormat()}");
        $this->getRedis()->publish("cameras/debug", "{$this->getName()}: Audio is {$this->getAudioCodec()}");
        $this->getRedis()->publish("cameras/debug", "{$this->getName()}: Source is {$this->getPath()}");

        $ffmpeg_command = "ffmpeg -i {$this->getPath()} -c copy -map 0 -acodec {$this->getAudioCodec()} -f segment -strftime 1 -segment_time {$this->getSegmentTime()} -segment_format {$this->getFormat()} {$dir}/{$this->getTimestampFormat()}.mp4 ";

        $this->getRedis()->publish("cameras/debug", "{$this->getName()}: command: {$ffmpeg_command}");

        exec($ffmpeg_command);

        $this->getRedis()->publish("cameras/debug", "{$this->name}: FFMPEG exited.");

    }
}
