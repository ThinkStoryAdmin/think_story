<?php
namespace ThinkStory\BlockAttributeTranslator;
//Gettext to load the files: https://packagist.org/packages/gettext/gettext

use Sepia\PoParser\SourceHandler\FileSystem AS SepiaFileSystem;
use Sepia\PoParser\Parser AS SepiaPoParser;
use Sepia\PoParser\PoCompiler AS SepiaPoCompiler;
use Sepia\PoParser\Catalog\Catalog AS SepiaCatalog;
use Sepia\PoParser\Catalog\Entry AS SepiaEntry;

use Concrete\Core\Localization\Localization;

/**Small class that handles saving block attributes to translate & find the translations
 * 
 */
class TSBlockAttributeTranslator {
    /** Adds an entry to the po files to be translated by an Administrator on the Translate Site Interface page
     * @param string attributeValue the attribute to save to po files
     * @return bool true if succeeded, false if failed (should return void in future)
     */
    public static function addEntryToTranslate($attributeValue, $oldValue = null){
        //Load the files & add the place to make a translation
        $translations = scandir(dirname(__FILE__, 6) . '/application/languages/site/');
        foreach($translations as $translation){
            $extension = pathinfo($translation, PATHINFO_EXTENSION);
		    if ($extension == 'po'){
                $catalog = SepiaPoParser::parseFile(dirname(__FILE__, 6) . '/application/languages/site/' . $translation);  //Load the file

                //Delete the old entry if old entry value provided
                if(isset($oldValueId)){
                    $catalog->removeEntry($catalog->getEntry($oldValue)->getMsgId());   //TODO could also just use the ID if provided?
                }
                
                $catalog->addEntry(new SepiaEntry(str_replace(' ', '.', \strtolower($attributeValue)), $attributeValue));     // Update entry

                //Save the file
                $fileHandler = new SepiaFileSystem(dirname(__FILE__, 6) . '/application/languages/site/' . $translation);
                $compiler = new SepiaPoCompiler();
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
        return $tFile->getEntry($text)->getMsgStr();
    }
}