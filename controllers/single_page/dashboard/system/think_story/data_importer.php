<?php
//namespace Concrete\Controller\SinglePage\Dashboard\System\Multilingual;
namespace Concrete\Package\ThinkStory\Controller\SinglePage\Dashboard\System\ThinkStory;

use BlockType;
use CollectionAttributeKey;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Html\Service\Seo;
use Concrete\Core\Page\Feed;
use Concrete\Core\Tree\Node\Node;
use Concrete\Core\Url\SeoCanonical;
use Database;
use Core;
use PageList;
use Concrete\Core\Attribute\Key\CollectionKey;
//use Concrete\Core\Tree\Node\Type\Topic;

use Express;

use PageType;
use Concrete\Core\Page\Page as Page;
use Concrete\Core\Page\Controller\DashboardPageController;
use Concrete\Core\Multilingual\Page\Section\Section as MultilingualSection;
use Concrete\Core\Multilingual\Page\PageList as MultilingualPageList;
use Concrete\Core\Page\Controller\DashboardSitePageController;
//use Concrete\Core\Multilingual\Page\Section\Section;

use \Concrete\Core\Tree\Type\Topic as TopicTree;
use \Concrete\Core\Tree\Node\Node as TreeNode;
use \Concrete\Core\Tree\Node\Type\Topic as TopicTreeNode;
use Concrete\Core\Attribute\Key\Category;

use Concrete\Core\Attribute\Type as AttributeType;

use Concrete\Core\Tree\Tree;
use Concrete\Core\Tree\Type\Topic;

//TImbre Attribute for setting
use Concrete\Package\ThinkStory\Entity\Attribute\Key\Settings\TimbreSettings;
use Concrete\Package\ThinkStory\Entity\Attribute\Value\Value\TimbreValue;
use ThinkStory\Attributes\Timbre as TimbreSimple;

use Concrete\Package\ThinkStory\AttributeValidator\TSAttributeValidator;

defined('C5_EXECUTE') or die("Access Denied.");

class DataImporter extends DashboardSitePageController
{
    public $helpers = array('form');

    public function view()
    {
        
    }

    public function action_createPages(){
        $data = $this->post();
        $data = $data['data'];
        $things = gettype($data);
        $q = json_decode($data, true);
        $b = array_keys($q);
        
        $control;
        $control2;
        $control3;
        $errors = [];
        foreach($q['pages'] as $page){
            $pagesToMap = [];
            $pageEntriesToMap = [];
            $pageIdsToMap=[];
            $translations = $page['translations'];

            //Create all the pages
            //Loop through all translations, create their pages, and add their IDs to the pagesToMap array
            foreach($translations as $translation){
                $language = '/'.$translation['language'];

                array_push($pagesToMap, $language);

                
                //$parentPage = \Page::getByPath($language);
                $parentPage = \Page::getByPath($translation['page_path']);
                $pageType = \PageType::getByHandle('page');
                $template = \PageTemplate::getByHandle('full');

                $pageTitle;
                if(isset($translation['titre'])){
                    $pageTitle = $translation['titre'];
                } else {
                    $pageTitle = "SAMPLE - NO TITLE WAS SUPPLIED";
                }

                $entry;
                if(isset($parentPage) && !is_null($parentPage) && !empty($parentPage->getCollectionPath())){
                    array_push($errors, "The page root : " . $parentPage->getCollectionPath());
                    
                    if(isset($page['informations']['rsvp_space_page_type'])){
                        $pageTypeCustom = \PageType::getByHandle($page['informations']['rsvp_space_page_type']);
                        if(is_null($pageTypeCustom)){
                            //New Page Template Stuff
                            $pageTemplateCustom = \PageTemplate::getByHandle(strval($page['informations']['rsvp_space_page_template']));
                            if(isset($pageTemplateCustom) && !is_null($pageTemplateCustom)){
                                $entry = $parentPage->add($pageType, array(
                                    'cName' => $pageTitle
                                ), $pageTemplateCustom);
                                array_push($errors, "The page type : " . $page['informations']['rsvp_space_page_type'] . " does not exist! Setting to default page type!");
                            } else {
                                $entry = $parentPage->add($pageType, array(
                                    'cName' => $pageTitle
                                ));
                                array_push($errors, "The page type : " . $page['informations']['rsvp_space_page_type'] . " does not exist! Setting to default page type!");
                            }
                        } else {
                            $entry = $parentPage->add($pageTypeCustom, array(
                                'cName' => $pageTitle
                            ));
                        }
                    } else {
                        $entry = $parentPage->add($pageType, array(
                            'cName' => $pageTitle
                        ));
                        //), $template);
                        array_push($errors, "No page type defined! Setting to default page type");
                    }
                } else {
                    array_push($errors, "The page root : " . $translation['page_path'] . " does not exist! Not creating page!");
                    $entry = null;
                }
                
                
                if(isset($entry) && !is_null($entry)){
                    //Set Page Attributes
                    foreach($translation as $attributeKey => $attributeValue){
                        array_push($pagesToMap, $attributeKey);
                        if(!TSAttributeValidator::checkCollectionAttributeHandleExists($attributeKey)){
                            array_push($errors, "Attribute key : " . $attributeKey . " does not exist! Not setting. Check that you have defined this attribute for your pages/collections!");
                        } else {
                            //If no attribute type defined, just setting
                            if(!isset($attributeValue['type'])){
                                $entry->setAttribute(strval($attributeKey), $attributeValue); //end me please, I have to turn the STRING into a STRING? Cool bruv
                                //$entry->setAttribute('ts_pattr_introduction', 'q');
                                if($attributeKey == 'ts_pattr_introduction'){
                                    array_push($errors, "Attribute key : " . $attributeKey . " did thingy");
                                }
                                array_push($errors, "Attribute key : " . $attributeKey . " exists! Will set to : " .$attributeValue);
                            } else {
                                array_push($errors, "Attribute key : " . $attributeKey . " has a type defined");
                                switch($attributeValue['type']){
                                    case 'topic':
                                        $topics = [];
                                        foreach($attributeValue['values'] as $topic){
                                            $topicNode = TopicTreeNode::getNodeByName(strval($topic));
                                            if(!is_null($topicNode) && isset($topicNode) && $topicNode){
                                                array_push($topics, $topicNode->getTreeNodeDisplayPath());
                                            }
                                        }
                                        $entry->setAttribute(strval($attributeKey), $topics);
                                        break;
                                    case 'timbre':
                                        $label = "Blank";$valid = false;
                                        if(isset($attributeValue['values']['valid'])){
                                            $valid = boolval($attributeValue['values']['valid']);
                                        }
                                        if(isset($attributeValue['values']['customLabel'])){
                                            $label = $attributeValue['values']['customLabel'];
                                        }
                                        $value = new TimbreSimple($label, $valid);
                                        $entry->setAttribute(strval($attributeKey), $value);
                                        break;
                                    default:
                                        array_push($errors, "Attribute key : " . $attributeKey . " of type " . $attributeValue['type'] . " is not recognized.");
                                        break;
                                }
                            }
                        }
                    }
                    array_push($pageIdsToMap, $entry->getCollectionID());
                } else {
                    array_push($errors, "Page doesn't exist, so not setting");
                }
            }

            //Map all the pages that were just created to each other
            $home = array_pop($pageIdsToMap);
            foreach($pageIdsToMap as $pageIdToMap){
                $currPageToMap = Page::getByID($pageIdToMap);

                $this->assign($home,$pageIdToMap);
            }
            $control=$pagesToMap;
            $control2=$pageEntriesToMap;
        }

        $holybob = [];
        $keyslist = CollectionKey::getList('Collection');
        foreach($keyslist AS $key){
            array_push($holybob, $key->getAttributeKeyHandle());
        }
        array_push($errors, "Keys: " . implode($holybob));

        echo json_encode(array(
            'c'=>$control,
            'd'=>$control2,
            'e'=>$control3,
            'errors'=>$errors
        ), JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        exit;
    }

    //Taken from Core
    //public function assign()
    //https://github.com/concrete5/concrete5/blob/8.1.0/concrete/controllers/backend/page/multilingual.php#L40
    public function assign($destPageID, $thisPageID)
    {
        //$pr = new PageEditResponse();
        /*
        if ($this->request->request->get('destID') == $this->page->getCollectionID()) {
            throw new \Exception(t("You cannot assign this page to itself."));
        }*/

        $destPage = \Page::getByID($destPageID);
        if (MultilingualSection::isMultilingualSection($destPage)) {
            $ms = MultilingualSection::getByID($destPage->getCollectionID());
        } else {
            $ms = MultilingualSection::getBySectionOfSite($destPage);
        }

        $thisPage = \Page::getByID($thisPageID);

        if (is_object($ms)) {
            // we need to assign/relate the source ID too, if it doesn't exist
            if (!MultilingualSection::isAssigned($thisPage)) {
                MultilingualSection::registerPage($thisPage);
            }
            MultilingualSection::relatePage($thisPage, $destPage, $ms->getLocale());
            /*
            $ih = Core::make('multilingual/interface/flag');
            $icon = (string) $ih->getSectionFlagIcon($ms);
            $pr->setAdditionalDataAttribute('name', $destPage->getCollectionName());
            $pr->setAdditionalDataAttribute('link', $destPage->getCollectionLink());
            $pr->setAdditionalDataAttribute('icon', $icon);
            $pr->setMessage(t('Page assigned.'));
            $pr->outputJSON();*/
        } else {
            throw new \Exception(t("The destination page doesn't appear to be in a valid multilingual section."));
        }
    }
}
