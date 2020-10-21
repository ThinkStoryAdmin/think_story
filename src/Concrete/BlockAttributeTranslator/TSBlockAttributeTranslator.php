<?php
namespace Concrete\Package\ThinkStory\BlockAttributeTranslator;
//Gettext to load the files: https://packagist.org/packages/gettext/gettext
use Gettext\Loader\PoLoader;
use Gettext\Generator\MoGenerator;
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
            $locale = LocaleService::getDefaultLocale()->getLocaleID();
        */

        

        //Load the files...
        $translations = scandir(dirname(__FILE__, 6) . '/application/languages/site/');

        $toUpdate = [];

        foreach($translations as $translation){
            $extension = pathinfo($file, PATHINFO_EXTENSION);
		    if ($extension == 'po'){
                array_push($toUpdate, $translation);

                //$loader = new PoLoader();
                //$translations = $loader->loadFile('locales/gl.po');
            }
        }
        
        return;
    }
}