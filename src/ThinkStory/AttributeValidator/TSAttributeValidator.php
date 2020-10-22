<?php
namespace Concrete\Package\ThinkStory\AttributeValidator;
use Concrete\Core\Attribute\Key\CollectionKey;
class TSAttributeValidator {
    public static function checkCollectionAttributeHandleExists($attributeHandleToCheck){
        /*$ak = \CollectionAttributeKey::getByHandle($attributeHandleToCheck);
        if ( is_null($ak) || !is_object($ak) || !intval($ak->getAttributeKeyID()) ){
            return false;
        } else {
            return true;
        }*/
        $keyslist = CollectionKey::getList('Collection');
        foreach($keyslist AS $key){
            if(strcmp($key->getAttributeKeyHandle(), $attributeHandleToCheck) == 0){
                return true;
            }
        }
        return false;
    }
}