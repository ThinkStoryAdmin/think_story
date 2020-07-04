<?php
namespace ThinkStory\Attributes;
class Timbre
{
    protected $customLabel;
    protected $valid;

    function __construct($customLabel, $valid)
    {
        $this->customLabel = $customLabel;
        $this->valid=$valid;
    }

    public function getValid()
    {
        return $this->valid;
    }

    public function getCustomLabel()
    {
        return $this->customLabel;
    }

    //Don't know why this is needed, nowhere is it documented
    //If it's not here C5 complains there's no string method
    //And if it returns a string, it complains that it isn't a number
    //So return 1? Doesn't seem to matter.
    public function __toString(){
        return "1";
    }
}