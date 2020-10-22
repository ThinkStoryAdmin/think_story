<?php
namespace ThinkStory\BlockAttributeTranslator;
//Gettext to load the files: https://packagist.org/packages/gettext/gettext

use Gettext\Translation;
use Gettext\Translations;
use Gettext\Loader\PoLoader;
use Gettext\Generator\PoGenerator;

//Try 2 using another library >:(
use Sepia\PoParser\SourceHandler\FileSystem AS SepiaFileSystem;
use Sepia\PoParser\Parser AS SepiaPoParser;
use Sepia\PoParser\PoCompiler AS SepiaPoCompiler;

use Concrete\Core\Localization\Localization;

//THIS WON'T WORK, as the value itself will not be shown unless we use a custom View.php!
//It will honestly be easier to hard code the block strings for Print to PDF, etc.
/**Small class that handles saving block attributes to translate & find the translations
 * 
 */
class TSBlockAttributeTranslator {
    public static function checkDir(){
        $translations =  dirname(__FILE__, 6) . '/application/languages/site/';
        //$translations = __DIR__ 
        $translations = scandir($translations);
        //Localization::activeLanguage();

        $q = [];
        foreach($translations as $translation){
            $extension = pathinfo($translation, PATHINFO_EXTENSION);
		    if ($extension == 'po'){
                array_push($q, $extension);
            }
        }

        return $q;
    }

    /** Adds an entry to the po files to be translated by an Administrator on the Translate Site Interface page
     * @param string attributeValue the attribute to save to po files
     * @return bool true if succeeded, false if failed (should return void in future)
     */
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
            $extension = pathinfo($translation, PATHINFO_EXTENSION);
		    if ($extension == 'po'){
                $tFile = $loader->loadFile($translation);               //Load the file
                $tFile->add(Translation::create($attributeValue));      //Add the $attributeValue    //Translation::create('comments', 'One comment', '%s comments');
                $generator->generateFile($tFile, $translation);         //Save the file
            }
        }
        
        return;
    }

    /** Adds an entry to the po files to be translated by an Administrator on the Translate Site Interface page
     * @param string attributeValue the attribute to save to po files
     * @return bool true if succeeded, false if failed (should return void in future)
     */
    public static function addEntryToTranslateSepia($attributeValue){
        //Load the files & add the place to make a translation
        $translations = scandir(dirname(__FILE__, 6) . '/application/languages/site/');
        foreach($translations as $translation){
            $extension = pathinfo($translation, PATHINFO_EXTENSION);
		    if ($extension == 'po'){
                //Load the file
                $catalog = SepiaPoParser::parseFile(dirname(__FILE__, 6) . '/application/languages/site/' . $translation);

                // Update entry
                $entry = new Entry('welcome.user', 'Welcome User!');
                $catalog->setEntry($entry);

                //Save the file
                $fileHandler = new Sepia\PoParser\SourceHandler\FileSystem(dirname(__FILE__, 6) . '/application/languages/site/' . $translation);
                $compiler = new Sepia\PoParser\PoCompiler();
                $fileHandler->save($compiler->compile($catalog));       
            }
        }
        return;
    }

    /**
     * Function that finds the translation of a string in a specified language
     * @param string the text to find a translation for
     * @param string the language to find the translation in
     * @return string the translation of the text, null if not found
     */
    public static function findTranslation($text, $language){
        //If params not of correct types, return
        if(!is_string($text) || !is_string($language)){
            return 1;//NULL;
        }

        //Find if the language exists
        $translations = scandir(dirname(__FILE__, 6) . '/application/languages/site/');
        $tFile = NULL;
        foreach($translations as $translation){
            $extension = pathinfo($translation, PATHINFO_EXTENSION);
            $filename = pathinfo($translation, PATHINFO_FILENAME);
		    if ($extension == 'po' && stripos($filename, $language) !== false){
                //$loader = new PoLoader();
                //$tFile = $loader->loadFile(dirname(__FILE__, 6) . '/application/languages/site/' . $translation);               //Load the file

                $tFile = SepiaPoParser::parseFile(dirname(__FILE__, 6) . '/application/languages/site/' . $translation);
                //If we found one, it means we found them all, so we can safely break
                //break;
            }
        }

        //If no appropriate translation file was found, return
        if(!isset($tFile)){
            return 2;//NULL;    
        }

        //Param checks complete & Po file loaded, can now find translation
        //return $tFile->find(null, $text)->getTranslation();
        return $tFile->getEntry($text)->getMsgStr();
    }
}