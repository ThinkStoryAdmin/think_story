<?php
//namespace Application\Attribute\ColorColor;
namespace Concrete\Package\ThinkStory\Attribute\TSColor;

use Concrete\Core\Attribute\FontAwesomeIconFormatter;
use View;
use Loader;
use Application\TSCA\Color\Color;
use Concrete\Core\Entity\Attribute\Value\Value\TextValue;
//class Controller extends \Concrete\Attribute\Number\Controller

class Controller extends \Concrete\Attribute\Text\Controller
{
    public function getIconFormatter()
    {
        return new FontAwesomeIconFormatter('tint');
    }

    public function form()
    {
        $color =  \Core::make('helper/form/color');
        $this->set('setColor', $this->getValue());
        $this->set('color', $color);
    }

    public function getValue()
    {
        $value = $this->attributeValue;
        if(!is_null($value)){
            $value = $this->attributeValue->getValueObject();
            if ($value) {
                //return new Color($value->getValue());
                //return new TSColor($value->getValue());
                //return '#791818';
                return $value;
            }
        }
        /*
        $value = $this->attributeValue->getValueObject();
        if ($value) {
            //return new Color($value->getValue());
        }*/
    }

	public function saveForm($data) {
		$this->saveValue($data['value']);
	}
}