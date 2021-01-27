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
                //return new TSColor($value->getValue());
                return $value;
            }
        }
        return NULL; //Use?
        /*
        $value = $this->attributeValue->getValueObject();
        if ($value) {
            //return new Color($value->getValue());
        }*/
    }

	public function saveForm($data) {
		$this->saveValue($data['value']);
    }
    
    //NOT NEEDED. C5 handles this manually, as the data is very simple.
    public function importValue(\SimpleXMLElement $akv)
    {
        if (isset($akv->value)) {
            return $akv->value;
        }
    }

    public function exportValue(\SimpleXMLElement $akn)
    {
        if (is_object($this->getAttributeValue()->getValue()) && get_class($this->getAttributeValue()->getValue()) === 'Concrete\Core\Entity\Attribute\Value\Value\TextValue') {
            $avn = $akn->addChild('value', $this->getAttributeValue()->getValue()->getValue());
        } else {
            $akn->addChild('value', get_class($this->getAttributeValue()->getValue()));
        }
    }
}