<?php
namespace ThinkStory\AttributeValidator; //namespace Concrete\Package\ThinkStory\AttributeValidator;
use Concrete\Core\Attribute\Key\CollectionKey;
class TSAttributeValidator {
    public static function checkCollectionAttributeHandleExists($attributeHandleToCheck){
        $keyslist = CollectionKey::getList('Collection');
        foreach($keyslist AS $key){
            if(strcmp($key->getAttributeKeyHandle(), $attributeHandleToCheck) == 0){
                return true;
            }
        }
        return false;
    }
}