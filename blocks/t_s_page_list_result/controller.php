<?php
namespace Concrete\Package\ThinkStory\Block\TSPageListResult;
use Concrete\Core\Block\BlockController;
use Page;

class Controller extends BlockController
{
    protected $btTable = 'btTSPageListResult';
    protected $btInterfaceWidth = 700;
    protected $btInterfaceHeight = 525;
    protected $btDefaultSet = 'think_story';

    public function getBlockTypeDescription()
    {
        return t("Shows the result of the Page List TS block");
    }

    public function getBlockTypeName()
    {
        return t("Think Story Page List Result");
    }

    public function registerViewAssets($outputContent = '')
    {
        $al = \Concrete\Core\Asset\AssetList::getInstance();
        $this->requireAsset('javascript', 'jquery');
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');
        //$al->register('javascript', 'select2sortable', 'blocks/testimonial_stack_output/js_form/select2.sortable.js');
    }

    public function registerAssets(){
        $this->requireAsset('css', 'font-awesome');
    }

    public function view(){
        $this->set('c', Page::getCurrentPage());
    }
}