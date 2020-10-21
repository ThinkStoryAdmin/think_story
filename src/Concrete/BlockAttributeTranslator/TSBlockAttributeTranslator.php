<?php
namespace Concrete\Package\ThinkStory\BlockAttributeTranslator;
//Gettext to load the files: https://packagist.org/packages/gettext/gettext
use Gettext\Translations;
use Gettext\Loader\PoLoader;
use Gettext\Generator\PoGenerator;

use Concrete\Core\Localization\Localization;

//THIS WON'T WORK, as the value itself will not be shown unless we use a custom View.php!
//It will honestly be easier to hard code the block strings for Print to PDF, etc.
class TSBlockAttributeTranslator {
    public static function checkDir(){
        $translations =  dirname(__FILE__, 6) . '/application/languages/site/';
        //$translations = __DIR__ 
        $translations = scandir($translations);
        Localization::activeLanguage();
        return $translations;
    }

    public static function addEntryToTranslate($attributeValue){
        //Get default locale (not needed, as need to update the strings in all existing po & mo)
        /*
            https://www.concrete5.org/community/forums/internationalization/8.4.2-how-to-get-language-of-default-locale/
            https://documentation.concrete5.org/api/8.5.2/Concrete/Core/Localization/Locale/Service.html
            $locale = Concrete\Core\Localization\Locale\Service::getDefaultLocale()->getLocaleID();
        */

        //Load the files & add the place to make a translation
        $translations = scandir(dirname(__FILE__, 6) . '/application/languages/site/');
        $loader = new PoLoader();
        $generator = new PoGenerator();
        foreach($translations as $translation){
            $extension = pathinfo($file, PATHINFO_EXTENSION);
		    if ($extension == 'po'){
                $tFile = $loader->loadFile($translation);               //Load the file
                $tFile->add(Translation::create($attributeValue));      //Add the $attributeValue    //Translation::create('comments', 'One comment', '%s comments');
                $generator->generateFile($tFile, $translation);         //Save the file
            }
        }
        
        return;
    }
}