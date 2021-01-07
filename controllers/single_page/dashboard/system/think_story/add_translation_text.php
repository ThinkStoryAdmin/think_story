<?php
//TODO use this to manually add text to translate, as you cannot do that in the "Translate Site Interface" page
namespace Concrete\Package\ThinkStory\Controller\SinglePage\Dashboard\System\ThinkStory;

use Concrete\Core\Page\Controller\DashboardSitePageController;
use ThinkStory\BlockAttributeTranslator\TSBlockAttributeTranslator;

class AddTranslationText extends DashboardSitePageController
{
    public $helpers = array('form');

    public function view()
    {
        
    }

    public function action_AddTextToTranslate(){
        if(\is_string($this->post()['data'])){
            TSBlockAttributeTranslator::addEntryToTranslate($this->post()['data']);
            exit;
        }
        throw new \Exception("Malformed text");
    }

    public function action_RemoveText(){
        if(\is_string($this->post()['data'])){
            TSBlockAttributeTranslator::removeEntry($this->post()['data']);
            exit;
        }
        throw new \Exception("Malformed text");
        //TSBlockAttributeTranslator::removeEntry();

        //$data = $this->post();
        //$data = $data['data'];
        //$q = json_decode($this->post()['data']);

        //$data = json_decode($this->post()['data']);
        /*sleep(3);
        echo json_encode($this->post()['data']);
        exit;*/
    }
}
?>