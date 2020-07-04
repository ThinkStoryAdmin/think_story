<?php

namespace Concrete\Package\ThinkStory\Block\TSBackButton;

use Concrete\Core\Block\BlockController;
use Core;

defined('C5_EXECUTE') or die(_("Access Denied."));

class Controller extends BlockController
{
    protected $btTable = "btTSBackButton";
    protected $btInterfaceWidth = "350";
    protected $btInterfaceHeight = "240";
    protected $btDefaultSet = 'think_story';

    public function getBlockTypeName()
    {
        return t('Think Story Back Button');
    }

    public function getBlockTypeDescription()
    {
        return t('Allows visitors to go back to the previous page');
    }

    public function view(){
        $this->set('bLabel', $this->bLabel);
    }
}
