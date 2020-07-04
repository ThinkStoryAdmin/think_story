<?php
//namespace Application\Attribute\Timbre;
namespace Concrete\Package\ThinkStory\Attribute\Timbre;

use Concrete\Core\Attribute\FontAwesomeIconFormatter;
//use ThinkStory\Attrs\TimbreA;
use ThinkStory\Attributes\Timbre;

use File;

//use Application\Entity\Attribute\Key\Settings\TimbreSettings;
use Concrete\Package\ThinkStory\Entity\Attribute\Key\Settings\TimbreSettings;
//use Application\Entity\Attribute\Value\Value\TimbreValue;
use Concrete\Package\ThinkStory\Entity\Attribute\Value\Value\TimbreValue;

use Concrete\Core\Attribute\Controller as AttributeController; 
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


        /*
        if ($this->getAttributeKeySettings()->isModeHtmlInput()) {
            $previousFileID = (int) $this->post('previousFile');
            if ($previousFileID !== 0) {
                $operation = $this->post('operation') ?: 'replace';
                if ($operation === 'remove') {
                    return $this->createAttributeValue(null);
                }
                if ($operation === 'keep') {
                    return $this->createAttributeValue(File::getByID($previousFileID));
                }
            }
            $uploadedFile = array_get($this->request->files->all(), "akID.{$this->attributeKey->getAttributeKeyID()}.value");
            if ($uploadedFile instanceof UploadedFile && $uploadedFile->isValid()) {
                $importer = new Importer();
                $f = $importer->import($uploadedFile->getPathname(), $uploadedFile->getClientOriginalName());
                if (is_object($f)) {
                    return $this->createAttributeValue($f->getFile());
                }
            }
        }

        return $this->createAttributeValue(null);
        */

        /*
        $previousFileID = (int) $this->post('previousFile');
        if ($previousFileID !== 0) {
            $operation = $this->post('operation') ?: 'replace';
            if ($operation === 'remove') {
                //return $this->createAttributeValue(null);
            }
            if ($operation === 'keep') {
                //return $this->createAttributeValue(File::getByID($previousFileID));
                $file = $data[$previousFileID];
            }
        }
        $uploadedFile = array_get($this->request->files->all(), "akID.{$this->attributeKey->getAttributeKeyID()}.value");
        if ($uploadedFile instanceof UploadedFile && $uploadedFile->isValid()) {
            $importer = new Importer();
            $f = $importer->import($uploadedFile->getPathname(), $uploadedFile->getClientOriginalName());
            if (is_object($f)) {
                //return $this->createAttributeValue($f->getFile());
                $file = $data[$f];
            }
        }
        */

        //return $this->createAttributeValue(null);

        //$formDisplayMethod = 'select';
        /*if (isset($data['value']) && $data['value']) {
            $file = $data['value'];
        }*/

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
                    //return $value->getValid();
                } else {
                    return 2;
                }
            }
        }
    }
}

/*
public function validateForm($data)
{
    if ($this->getAttributeKeySettings()->isModeFileManager()) {
        if ((int) ($data['value']) > 0) {
            $f = File::getByID((int) ($data['value']));
            if (is_object($f) && !$f->isError()) {
                return true;
            } else {
                return new Error(t('You must specify a valid file for %s', $this->getAttributeKey()->getAttributeKeyDisplayName()),
                    new AttributeField($this->getAttributeKey())
                );
            }
        } else {
            return new FieldNotPresentError(new AttributeField($this->getAttributeKey()));
        }
    }
    if ($this->getAttributeKeySettings()->isModeHtmlInput()) {
        $previousFileID = empty($data['previousFile']) ? 0 : (int) $data['previousFile'];
        if ($previousFileID !== 0) {
            $operation = empty($data['operation']) ? 'replace' : $data['operation'];
            if (in_array($operation, ['keep', 'remove'], true)) {
                return true;
            }
        }
        $uploadedFile = array_get($this->request->files->all(), "akID.{$this->attributeKey->getAttributeKeyID()}.value");
        if (!$uploadedFile instanceof UploadedFile || !$uploadedFile->isValid()) {
            return new FieldNotPresentError(new AttributeField($this->getAttributeKey()));
        }
        $name = $uploadedFile->getClientOriginalName();
        $fh = $this->app->make('helper/validation/file');
        if (!$fh->extension($name)) {
            return new Error(t('Invalid file extension.'),
                new AttributeField($this->getAttributeKey())
            );
        }
        return true;
    }
}*/

/*
public function createAttributeValueFromRequest()
    {
        if ($this->getAttributeKeySettings()->isModeFileManager()) {
            $fID = (int) $this->post('value');
            if ($fID !== 0) {
                return $this->createAttributeValue(File::getByID($fID));
            }
        }
        if ($this->getAttributeKeySettings()->isModeHtmlInput()) {
            $previousFileID = (int) $this->post('previousFile');
            if ($previousFileID !== 0) {
                $operation = $this->post('operation') ?: 'replace';
                if ($operation === 'remove') {
                    return $this->createAttributeValue(null);
                }
                if ($operation === 'keep') {
                    return $this->createAttributeValue(File::getByID($previousFileID));
                }
            }
            $uploadedFile = array_get($this->request->files->all(), "akID.{$this->attributeKey->getAttributeKeyID()}.value");
            if ($uploadedFile instanceof UploadedFile && $uploadedFile->isValid()) {
                $importer = new Importer();
                $f = $importer->import($uploadedFile->getPathname(), $uploadedFile->getClientOriginalName());
                if (is_object($f)) {
                    return $this->createAttributeValue($f->getFile());
                }
            }
        }

        return $this->createAttributeValue(null);
    }
    */