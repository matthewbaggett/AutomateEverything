<?php

namespace AE\IpCamera;

class VideoProcess{

    protected $name;
    protected $path;

    protected $segment_time = 60;

    // Storage settings
    protected $format = 'mp4';
    protected $audio_codec = 'mp2';
    protected $timestamp_format = '%Y-%m-%d_%H-%M-%S';

    // Streaming settings
    protected $stream_resolution = 'hd480';

    public function __construct($name, $path){
        $this->name = $name;
        $this->path = $path;
    }

    public function run(){
        $ffmpeg_command = "ffmpeg -i {$this->path} -b:a 32k -b:v 64k -maxrate 128k -bufsize 512k -vf scale={$this->stream_resolution} http://localhost:8090/feed1.ffm";
        //    "-c copy -map 0 -acodec {$this->audio_codec} -f segment -strftime 1 -segment_time {$this->segment_time} -segment_format {$this->format} /app/videos/{$this->name}_{$this->timestamp_format}.mp4 " .

        ;
        echo "\n\n\nCommand: {$ffmpeg_command}\n\n\n\n";

        exec($ffmpeg_command);
    }
}