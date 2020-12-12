<?php
//namespace Application\Controller\PageType;
namespace Concrete\Package\ThinkStory\Controller\PageType;

use Concrete\Core\Page\Controller\PageTypeController;
use Concrete\Package\ThinkStory\AttributeValidator\TSAttributeValidator;

class TSScenario extends PageTypeController
{
	protected $viewCountAttributeHandle = 'ts_view_count_np';

    public function view()
    {
		$c = \Page::getCurrentPage();
		if((null !== $c->getAttribute($this->viewCountAttributeHandle)) && (!$c->isEditMode()) && TSAttributeValidator::checkCollectionAttributeHandleExists($this->viewCountAttributeHandle)){
			//Si la page a ce attribut, et on n'est pas en mode "Edit"
			$viewCount = $c->getAttribute($this->viewCountAttributeHandle);
			$viewCount < 0 ? 0 : $viewCount + 1;
			$c->setAttribute($this->viewCountAttributeHandle, $viewCount);
		}
    }
}
?>
