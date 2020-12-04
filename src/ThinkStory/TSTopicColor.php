<?php
namespace ThinkStory;

/**Small class that handles working with the Topic -> Color relation in code */
class TSTopicColor {
    protected $topic;
    protected $color;

    function __construct($topic, $color){
        $this->$topic = $topic;
        $this->$color = $color;
    }

    public function getColor(){
        return $this->$color;
    }

    public function getTopic(){
        return $this->$topic;
    }
}