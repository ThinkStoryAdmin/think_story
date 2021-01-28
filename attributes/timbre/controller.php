<?php
namespace Concrete\Package\ThinkStory\Attribute\Timbre;
use Concrete\Core\Attribute\FontAwesomeIconFormatter;
use ThinkStory\Attributes\Timbre;
use File;
use Concrete\Package\ThinkStory\Entity\Attribute\Key\Settings\TimbreSettings;
use Concrete\Package\ThinkStory\Entity\Attribute\Value\Value\TimbreValue;

use Concrete\Core\Attribute\Controller as AttributeController; 

//For import / export
//use Concrete\Core\Attribute\Controller as AttributeTypeController;
//use Concrete\Core\Attribute\FontAwesomeIconFormatter;
use Concrete\Core\Attribute\SimpleTextExportableAttributeInterface;
use Concrete\Core\Backup\ContentExporter;
use Concrete\Core\Entity\Attribute\Key\Settings\ImageFileSettings;
use Concrete\Core\Entity\Attribute\Value\Value\ImageFileValue;
use Concrete\Core\Entity\File\File as FileEntity;
use Concrete\Core\Error\ErrorList\Error\CustomFieldNotPresentError;
use Concrete\Core\Error\ErrorList\Error\Error;
use Concrete\Core\Error\ErrorList\Error\FieldNotPresentError;
use Concrete\Core\Error\ErrorList\ErrorList;
use Concrete\Core\Error\ErrorList\Field\AttributeField;
use Concrete\Core\File\Importer;
use Core;
//use File;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Controller extends AttributeController
{
    
    protected $searchIndexFieldDefinition = array('type' => 'integer', 'options' => array('default' => 0, 'notnull' => false));

    public function getIconFormatter()
    {
        return new FontAwesomeIconFormatter('check');
    }

    public function getAttributeKeySettingsClass()
    {
        return TimbreSettings::class;
    }

    /**
     * @param $timbre Timbre
     * @return TimbreValue
     */
    public function createAttributeValue($timbre)
    {
        $value = new TimbreValue();
        //$value->setPropertyLocationID($location->getLocationID());

        if($timbre instanceof Timbre){
            $value->setValid($timbre->getValid());
            $value->setCustomLabel($timbre->getCustomLabel());
        } else if(is_string($timbre)){
            $value->setValid(false);
            $value->setCustomLabel('');
        } else {
            $value->setValid(false);
            $value->setCustomLabel('');
        }
        
        return $value;
    }

    public function createAttributeValueFromRequest()
    {
        $value = new TimbreValue();
        $data = $this->post();
        if(isset($data['valid']) && $data['valid']){
            $value->setValid(true);
        } else {
            $value->setValid(false);
        }
        
        $value->setCustomLabel($data['customLabel']);
        //$value->setCustomLabel(($data['valid']));
        return $value;
    }

    public function getAttributeValueClass()
    {
        return TimbreValue::class;
    }

    public function getFileID(){
        return $this->getAttributeKeySettings()->getFileObject();
    }

    public function getValidity(){
        $value = $this->getAttributeValue();
        if (is_object($value)) {
            $value = $this->getAttributeValue()->getValue();
            if (is_object($value)) {
                return $value->getValid();
            }
        }
    }

    public function getDisplayValue(){
        //Influenced by some C5 core code in Image_file attribute

        //$f = $this->getAttributeValue()->getValue();

        $f = $this->getAttributeKeySettings()->getFileObject();
        if (is_object($f)) {
            $type = strtolower($f->getTypeObject()->getGenericDisplayType());

            $timbreValue = $this->attributeValue->getValueObject();
            $valid = $timbreValue->getValid();

            //If is valid, show timbre
            if(isset($valid) && $valid){
                //$value->setValid(true);
                //$this->set('valid', 1);
                /*
                return '<a target="_blank" href="' 
                    . $f->getDownloadURL() 
                    . '" class="ccm-attribute-image-file ccm-attribute-image-file-' . $type . '">' 
                    . $f->getTitle() . '</a>';*/

                $im = \Core::make('helper/image');
                $thumb = $im->getThumbnail(
                    $f,
                    250,
                    250,
                    true
                ); //<-- set these 2 numbers to max width and height of thumbnails
                //$content = "<img src=\"{$thumb->src}\" width=\"{$thumb->width}\" height=\"{$thumb->height}\" alt=\"\" />";
                //$content = "<img src=\"{$f->getForceDownloadURL()}\" width=\"{$thumb->width}\" height=\"{$thumb->height}\" alt=\"\" />";
                $content = "<img src=\"{$f->getRelativePath() }\" width=\"{$thumb->width}\" height=\"{$thumb->height}\" alt=\"\" />";
                return $content;
            } else {
                //$this->set('valid', 0);
            }
        } else {
            return '';
        }
    }

    public function getDisplayValueAbsoluteImagePath(){
        $f = $this->getAttributeKeySettings()->getFileObject();
        if (is_object($f)) {
            $type = strtolower($f->getTypeObject()->getGenericDisplayType());

            $timbreValue = $this->attributeValue->getValueObject();
            $valid = $timbreValue->getValid();

            //If is valid, show timbre
            if(isset($valid) && $valid){
                //$value->setValid(true);
                //$this->set('valid', 1);
                /*
                return '<a target="_blank" href="' 
                    . $f->getDownloadURL() 
                    . '" class="ccm-attribute-image-file ccm-attribute-image-file-' . $type . '">' 
                    . $f->getTitle() . '</a>';*/

                $im = \Core::make('helper/image');
                $thumb = $im->getThumbnail(
                    $f,
                    250,
                    250,
                    true
                ); //<-- set these 2 numbers to max width and height of thumbnails
                $content = "<img src=\"{$f->getRelativePathFromID()}\" width=\"{$thumb->width}\" height=\"{$thumb->height}\" alt=\"\" />";
                return $content;
            } else {
                //$this->set('valid', 0);
            }
        } else {
            return '';
        }
    }

    public function printValue(){
        $f = $this->getAttributeKeySettings()->getFileObject();
        if (is_object($f)) {
            $type = strtolower($f->getTypeObject()->getGenericDisplayType());

            $timbreValue = $this->attributeValue->getValueObject();
            $valid = $timbreValue->getValid();

            //If is valid, show timbre
            if(isset($valid) && $valid){
                //$value->setValid(true);
                //$this->set('valid', 1);
                /*
                return '<a target="_blank" href="' 
                    . $f->getDownloadURL() 
                    . '" class="ccm-attribute-image-file ccm-attribute-image-file-' . $type . '">' 
                    . $f->getTitle() . '</a>';*/

                $im = \Core::make('helper/image');
                $thumb = $im->getThumbnail(
                    $f,
                    250,
                    250,
                    true
                ); //<-- set these 2 numbers to max width and height of thumbnails
                $content = "<img src=\"{$thumb->src}\" width=\"{$thumb->width}\" height=\"{$thumb->height}\" alt=\"\" />";
                return $content;
            } else {
                //$this->set('valid', 0);
            }
        } else {
            return '';
        }
    }

    public function saveKey($data)
    {
        /**
         * @var $settings PropertyLocationSettings
         */
        $settings = $this->getAttributeKeySettings();

        $fID = (int) $this->post('value');
        if ($fID !== 0) {
            //return $this->createAttributeValue(File::getByID($fID));
            //$file = $fID;
            $file = File::getByID($fID);
        } else {
            //$fID = 1;
        }

        $settings->setFileObject($file);

        return $settings;
    }

    /*
    public function getValue()
    {
        /**
         * @var $value PropertyLocationValue
         
        $value = $this->attributeValue->getValueObject();
        if ($value) {
            return new TimbreA($value->getValid(), $value->getCustomLabel());
            //return new TimbreValue()
            /*
            $valRet = new TimbreValue();
            $valRet->setValid($value->getValid());
            $valRet->setCustomLabel($value->getCustomLabel());
            return $valRet;
        }
    }*/

    public function form()
    {
        //Get existing value
        if (is_object($this->attributeValue)) {
            $timbreValue = $this->attributeValue->getValueObject();
            if (is_object($timbreValue)) {
                //$this->set('valid', $valid->getValid());
                //$this->set('valid', $timbreValue->getValid());

                $valid = $timbreValue->getValid();
                if(isset($valid) && $valid){
                    //$value->setValid(true);
                    $this->set('valid', 1);
                } else {
                    $this->set('valid', 0);
                }

                $this->set('customLabel', $timbreValue->getCustomLabel());
            }
        }
        $settings = $this->getAttributeKeySettings();
        /**
         * @var $settings PropertyLocationSettings
         */
        //$this->set('file', $settings->getFormDisplayMethod());
    }

    public function type_form()
    {
        $bf = false;
        /*
        if (is_object($this->attributeSettings)) {
            $bf = $this->getAttributeValue()->getValue();
        }*/
        $settings = $this->getAttributeKeySettings();
        $bf = $settings->getValue();
        $this->set('file', $bf ?: null);

        //THIS DAMN LINE WAS ALL THAT WAS MISSING BEFORE
        //form AUTO INCLUDE THE TYPE CONTROLLER
        //type_form DOESN'T, SO NEED TO SET IT
        $this->set('controller', $this);
    }

    public function getSearchIndexValue()
    {
        $value = $this->getAttributeValue();
        if (is_object($value)) {
            $value = $this->getAttributeValue()->getValue();
            if (is_object($value)) {
                if(empty($value->getValid())){
                    if($value->getValid()){
                        return 1;
                    } else {
                        return 0;
                    }
                } else {
                    return 2;
                }
            }
        }
    }

    //Stuff to export / import
    public function exportKey($akey)
    {
        //Export TimbreSettings
        $av = $akey->addChild('file');
        $fo = $this->getAttributeKeySettings()->getValue();
        if (is_object($fo)) {
            $av->addChild('fID', ContentExporter::replaceFileWithPlaceHolder($fo->getFileID()));
        } else {
            $av->addChild('fID', 0);
        }

        return $akey;
    }

    public function exportValue(\SimpleXMLElement $akn)
    {
        $av = $akn->addChild('value');
        $val = $this->getAttributeValue()->getValue();

        //$av->addChild('ALL', var_export($val, true));

        if(is_array($val)){
            if(isset($val[1]) && is_string($val[1])){
                $av->addChild('customLabel', var_export($val[1], true));
            } else {
                $av->addChild('customLabel');
            }

            if(isset($val[0])){
                $av->addChild('valid', var_export($val[0], true));
            } else {
                $av->addChild('valid');
            }
        } else {
            $av->addChild('customLabel', var_export('', true));
            $av->addChild('valid', var_export(false, true));
        }
    }

    public function importKey(\SimpleXMLElement $akey)
    {
        $type = $this->getAttributeKeySettings();

        if (isset($akey->value->fID)) {
            $fIDVal = (string) $akv->value->fID;
            $inspector = \Core::make('import/value_inspector');
            $result = $inspector->inspect($fIDVal);
            $fID = $result->getReplacedValue();
            if ($fID) {
                $f = File::getByID($fID);
                if (is_object($f)) {
                    $type->setFileObject($f);
                }
            }
        }

        return $type;
    }

    public function importValue(\SimpleXMLElement $akv)
    {
        if (isset($akv->value->customLabel)) {
            $customLabel = (string) $akv->value->customLabel;
        } else {
            $customLabel = '';
        }

        if (isset($akv->value->valid)) {
            if($akv->value->valid){
                $valid = true;
            } else {
                $valid = false;
            }
        } else {
            $valid = false;
        }

        return new Timbre($customLabel, $valid);
    }
}
