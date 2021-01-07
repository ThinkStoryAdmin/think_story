<?php
namespace ThinkStory\BlockAttributeTranslator;
//Gettext to load the files: https://packagist.org/packages/gettext/gettext

use Sepia\PoParser\SourceHandler\FileSystem AS SepiaFileSystem;
use Sepia\PoParser\Parser AS SepiaPoParser;
use Sepia\PoParser\PoCompiler AS SepiaPoCompiler;
use Sepia\PoParser\Catalog\Catalog AS SepiaCatalog;
use Sepia\PoParser\Catalog\Entry AS SepiaEntry;

/**Small class that handles saving block attributes to translate & find the translations
 * Currently all static
 */
class TSBlockAttributeTranslator {
    private static function saveFile($catalog, $translation){
        $fileHandler = new SepiaFileSystem(dirname(__FILE__, 6) . '/application/languages/site/' . $translation);
        $compiler = new SepiaPoCompiler();
        $fileHandler->save($compiler->compile($catalog)); 
    }

    /** Adds an entry to the po files to be translated by an Administrator on the Translate Site Interface page
     * @param string $attributeValue    the attribute to save to po files
     * @param string $oldValue          (optional) the old value to delete
     */
    public static function addEntryToTranslate($attributeValue, $oldValue = null){
        //Load the files & add the place to make a translation
        $translations = scandir(dirname(__FILE__, 6) . '/application/languages/site/');
        foreach($translations as $translation){
            $extension = pathinfo($translation, PATHINFO_EXTENSION);
		    if ($extension == 'po'){
                $catalog = SepiaPoParser::parseFile(dirname(__FILE__, 6) . '/application/languages/site/' . $translation);  //Load the file

                //Delete the old entry if old entry value provided
                if(isset($oldValue)){
                    $catalog->removeEntry($catalog->getEntry($oldValue)->getMsgId());   //TODO use ID if instead?
                }
                
                //$catalog->addEntry(new SepiaEntry(str_replace(' ', '.', \strtolower($attributeValue)), $attributeValue));     // Update entry
                $catalog->addEntry(new SepiaEntry($attributeValue, null));     // Update entry with NULL, we don't want a value just yet!

                self::saveFile($catalog, $translation);    //Save the file     
            }
        }
        return;
    }

    /** Function that finds & removes an entry
     * @param string $attributeValue    the text to find & delete
     */
    public static function removeEntry($attributeValue){
        $translations = scandir(dirname(__FILE__, 6) . '/application/languages/site/');
        foreach($translations as $translation){
            $extension = pathinfo($translation, PATHINFO_EXTENSION);
		    if ($extension == 'po'){
                $catalog = SepiaPoParser::parseFile(dirname(__FILE__, 6) . '/application/languages/site/' . $translation);  //Load the file

                //Check if entry event exists
                if($catalog->getEntry($attributeValue)){
                    $catalog->removeEntry($catalog->getEntry($attributeValue)->getMsgId());
                    self::saveFile($catalog, $translation);    //Save the file
                    return;
                }
                throw new \Exception("No entry found");
            }
        }
    }

    /** Function that finds the translation of a string in a specified language (not needed)
     * findTranslation('PDF Download', \Localization::activeLanguage())
     * @param string $text          the text to find a translation for
     * @param string $language      the language to find the translation in
     * @return string               the translation of the text, null if not found
     * @deprecated just use t(), but adding translations is useful
     */
    public static function findTranslation($text, $language){
        //If params not of correct types, return
        if(!is_string($text) || !is_string($language)){
            //throw new \Exception('Parameters should be strings!');
            return NULL;
        }

        //Find if the language exists
        $translations = scandir(dirname(__FILE__, 6) . '/application/languages/site/');
        $tFile = NULL;
        foreach($translations as $translation){
            $extension = pathinfo($translation, PATHINFO_EXTENSION);
            $filename = pathinfo($translation, PATHINFO_FILENAME);
		    if ($extension == 'po' && stripos($filename, $language) !== false){
                $tFile = SepiaPoParser::parseFile(dirname(__FILE__, 6) . '/application/languages/site/' . $translation);
                break;  //If we found one, it means we found them all, so we can safely break
            }
        }

        //If no appropriate translation file was found, return
        if(!isset($tFile)){
            throw new \Exception('No translation file found!');
            return NULL;
        }

        //Param checks complete & Po file loaded, can now find translation
        if(null !== ($tFile->getEntry(strval($text)))){
            return $tFile->getEntry(strval($text))->getMsgStr();
        }
        //throw new \Exception('Why here?');
        return NULL;
    }
}