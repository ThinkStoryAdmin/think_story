<?php
// !!! FOR THIS PAGE TO BE INSTALLED THE SITE NEEDS TO BE RESET

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

use Symfony\Component\HttpFoundation\JsonResponse;

use ThinkStory\AttributeValidator\TSAttributeValidator; //use Concrete\Package\ThinkStory\AttributeValidator\TSAttributeValidator;

defined('C5_EXECUTE') or die("Access Denied.");

class AddPagesMultilingual extends DashboardSitePageController
{
    public $helpers = array('form');

    public function requireAssets(){
        //$this->requireAsset('css', 'bootstrap');
        //$this->requireAsset('css', 'font-awesome');
        //$this->requireAsset('javascript', 'bootstrap/*');
        //$this->requireAsset('javascript', 'jquery');
    }

    public function view()
    {
        $this->set('app', $app);
        $this->pageReportView();
    }

    public function pageReportView(){
        $this->requireAsset('core/sitemap');
        $list = MultilingualSection::getList($this->getSite());
        $sections = array();
        usort($list, function ($item) {
           if ($item->getLocale() == $this->getSite()->getDefaultLocale()->getLocale()) {
               return -1;
           } else {
               return 1;
           }
        });
        foreach ($list as $pc) {
            $sections[$pc->getCollectionID()] = $pc->getLanguageText() . " (" . $pc->getLocale() . ")";
        }
        $this->set('sections', $sections);

        $sections2 = array();
        $sections3 = array();
        foreach ($list as $pc) {
            $sections2[$pc->getLanguage()] = $pc->getLanguageText() . " (" . $pc->getLanguage() . ")";
            $sections3[$pc->getLanguage()] = $pc;
        }

        $this->set('sections2', $sections2);
        $this->set('sections3', $sections3);

        $this->set('sectionList', $list);

        if (!isset($_REQUEST['sectionID']) && (count($sections) > 0)) {
            foreach ($sections as $key => $value) {
                $sectionID = $key;
                break;
            }
        } else {
            $sectionID = $_REQUEST['sectionID'];
        }

        if (!isset($_REQUEST['targets']) && (count($sections) > 1)) {
            $i = 0;
            foreach ($sections as $key => $value) {
                if ($key != $sectionID) {
                    $targets[$key] = $key;
                    break;
                }
                ++$i;
            }
        } else {
            $targets = isset($_REQUEST['targets']) ? $_REQUEST['targets'] : null;
        }
        if (!isset($targets) || (!is_array($targets))) {
            $targets = array();
        }

        $targetList = array();
        foreach ($targets as $key => $value) {
            $targetList[] = MultilingualSection::getByID($key);
        }
        $this->set('targets', $targets);
        $this->set('targetList', $targetList);
        $this->set('sectionID', $sectionID);
        $this->set('fh', \Core::make('multilingual/interface/flag'));

        if (isset($sectionID) && $sectionID > 0) {
            $pl = new MultilingualPageList();
            $pc = \Page::getByID($sectionID);
            $pl->setSiteTreeObject($pc->getSiteTreeObject());
            $path = $pc->getCollectionPath();
            if (strlen($path) > 1) {
                $pl->filterByPath($path);
            }

            if (isset($_REQUEST['keywords']) && $_REQUEST['keywords']) {
                $pl->filterByName($_REQUEST['keywords']);
            }

            $pl->setItemsPerPage(25);
            if (!isset($_REQUEST['showAllPages']) || !$_REQUEST['showAllPages']) {
                $pl->filterByMissingTargets($targetList);
            }

            if(isset($_REQUEST['ptID']) && $_REQUEST['ptID']){
                $pl->filterByPageTypeHandle(PageType::getByID($_REQUEST['ptID'])->getPageTypeHandle());
                $this->set('ptID', $_REQUEST['ptID']);
            }

            if(isset($_REQUEST['attributeToAnalyse']) && $_REQUEST['attributeToAnalyse']){
                $this->set('attributeToAnalyse', $_REQUEST['attributeToAnalyse']);
            }
            

            $pagination = $pl->getPagination();
            $this->set('pagination', $pagination);
            $this->set('pages', $pagination->getCurrentPageResults());
            $this->set('section', MultilingualSection::getByID($sectionID));
            $this->set('pl', $pl);
            $this->loadKeys();
        }
    }

    protected function loadKeys()
    {
        $attributeKeys = [];
        $keys = CollectionKey::getList();
        foreach ($keys as $ak) {
            $attributeKeys[$ak->getAttributeKeyHandle()] = $ak->getAttributeKeyName();
        }
        $this->set('keyersVC', $keys);
        $this->set('attributesKeysToAnalyse', $attributeKeys);
    }

    public function action_createPages2(){
        $data = $this->post();
        $data = $data['pageData'];
        //$q = json_decode($data, true);
        $errors = [];
        $pageIdsToMap = [];
        $messages = [];

        //Create the pages
        foreach($data AS $pagesattributes){
            $parentPage;// = \Page::getByPath($translation['page_path']);
            $pageType;// = \PageType::getByHandle('page');
            $template;// = \PageTemplate::getByHandle('full');
            $pageTitle;

            //First iterator to get the needed attributes (can )
            foreach($pagesattributes AS $pageattribute){
                switch($pageattribute['name']){
                    case 'rsvp-name':
                        $pageTitle = $pageattribute['value'][0];
                        //array_push($messages, $name);
                        break;
                    case 'rsvp-location':
                        $parentPage = \Page::getByID(intval($pageattribute['value'][0]));
                        //array_push($messages, $name);
                        break;
                    case 'rsvp-description':
                        //Does this need to be here?
                        //$pageTitle = $pageattribute['value'];
                        //array_push($messages, $name);
                        break; 
                    case 'rsvp-ptid':
                        $pageType = \PageType::getByID($pageattribute['value'][0]);
                        //array_push($messages, $name);
                        break;
                    case 'rsvp-ptemplate':
                        $template = \PageTemplate::getByHandle(strval($pageattribute['value'][0]));
                        break; 
                    default:
                        break;
                }
            }
            
            array_push($messages, $pagesattributes);
            
            //Create Page
            if(!isset($pageTitle) || empty($pageTitle)){
                $pageTitle = 'TITLE';
            }

            
            if(isset($parentPage)){
                if(isset($pageType)){
                    if(isset($template)){
                        $entry = $parentPage->add($pageType, array(
                            'cName' => $pageTitle
                        ), $template);
                        array_push($errors, "Setting to defined page type");
                    } else {
                        $entry = $parentPage->add($pageType, array(
                            'cName' => $pageTitle
                        ));
                        array_push($errors, "Setting to defined page type");
                    }
                } else {
                    $entry = $parentPage->add( \PageType::getByHandle('page'), array(
                        'cName' => $pageTitle
                    ));
                    array_push($errors, "No page type defined! Setting to default page type");
                }
            } else {
                array_push($errors, "The page root : " . $parentPage . " does not exist! Not creating page!");
            }

            //Set Page Attributes
            if(isset($entry)){
                //Add page to array of pages to map
                array_push($pageIdsToMap, $entry->getCollectionID());
                

                //Set Page Attributes
                foreach($pagesattributes AS $pageattribute){
                    //As name actually refers to the attribute handle, there cannot be any crazy symbols (specificallz square brackets [])
                    //So we can remove all square brackts: this is important for the multi-topic select, where the name
                        //has square brackets at the end to specify that it is an array
                    $brackets = array('[', ']');
                    $name=str_replace($brackets, "", $pageattribute['name']);
                    array_push($messages, $name);
                    switch($name){
                        case 'rsvp-name':
                            //array_push($messages, $name);
                            break;
                        case 'rsvp-location':
                            //array_push($messages, $name);
                            break;
                        case 'rsvp-description':
                            //array_push($messages, $name);
                            break; 
                        case 'rsvp-ptid':
                            //array_push($messages, $name);
                            //Do nothing, page type is handled before
                            break; 
                        case 'rsvp-ptemplate':
                            break; 
                        case '':
                            break; 
                        default:
                            /*$nameToCheckWith = '';
                            $nameSplitted = str_split($name);
                            foreach($nameSplitted AS $character){
                                $nameToCheckWith .= $character;
                            }*/
                            //$name = trim($name); //strip_tags or trim
                            
                            if(!TSAttributeValidator::checkCollectionAttributeHandleExists($name)){
                                array_push(
                                    $errors, 
                                    "Attribute key : " . $name . " does not exist! Not setting. Check that you have defined this attribute for your pages/collections!"
                                    . "Attribute key : " . $name . " of type " . $collectionAttributeTypeHandle . " NOT recognized."
                                    . ' length:' . strlen($name) . ' equal to meta title ' . $qtb
                                    //. ' ' . var_dump($name)
                                );
                            } else {
                                $collectionAttribute = CollectionKey::getByHandle($name);
                                $collectionAttributeTypeHandle = $collectionAttribute->getAttributeType()->getAttributeTypeHandle();

                                switch($collectionAttributeTypeHandle){
                                    case 'topics':
                                        if($collectionAttribute->getAttributeKeySettings()->allowMultipleValues()){
                                            $topicNumbers = array_map('intval', $pageattribute['value']);
                                            $topics = [];
                                            foreach($topicNumbers as $topic){
                                                $topicNode = TopicTreeNode::getByID($topic);
                                                if(!is_null($topicNode) && isset($topicNode) && $topicNode){
                                                    array_push($topics, $topicNode->getTreeNodeDisplayPath());
                                                }
                                            }
                                            array_push($messages, "Attribute key : " . $name . " of type " . $collectionAttributeTypeHandle . " recognized, with values " . implode($pageattribute['value']));
                                            $entry->setAttribute($name, $topics);
                                        } else {
                                            $topicNode = TopicTreeNode::getByID($pageattribute['value'][0]);
                                            if(!is_null($topicNode) && isset($topicNode) && $topicNode){
                                                array_push($topics, $topicNode->getTreeNodeDisplayPath());
                                            }
                                            $entry->setAttribute($name, intval($pageattribute['value'][0]));
                                        }
                                        break;
                                    case 'timbre':
                                        $valid = false;
                                        if(isset($pageattribute['value'][0])){
                                            $valid = $pageattribute['value'][0];
                                        }
                                        $value = new TimbreSimple('', $valid);
                                        $entry->setAttribute($name, $value);
                                        break;
                                    case('number'):
                                        $entry->setAttribute($name, intval($pageattribute['value'][0]));
                                        break;
                                    case('text'):
                                        if($name == 'meta_title'){$qtb = 'true';}else{$qtb='false';}
                                        array_push(
                                            $messages, "Attribute key : " . $name . " of type " . $collectionAttributeTypeHandle . " recognized."
                                            . ' length:' . strlen($name) . ' equal to meta title ' . $qtb
                                            //. ' ' . var_dump('meta_title') . ' ' . var_dump($name)
                                        );
                                        $entry->setAttribute($name, strval($pageattribute['value'][0]));
                                        break;
                                    case('textarea'):
                                        //If the text area's mode is rich text, create rich text editor, otherwise use basic textarea editor (no formatting)
                                        array_push($messages, "Attribute key : " . $name . " of type " . $collectionAttributeTypeHandle . " recognized, with value: " . $pageattribute['value'][0]);
                                        $entry->setAttribute($name, strval($pageattribute['value'][0]));
                                        break;
                                    case('boolean'):
                                        
                                        break;
                                    default:
                                        array_push($errors, "Attribute key : " . $name . " of type " . $collectionAttributeTypeHandle . " is not recognized.");
                                        break;
                                }
                            }
                            break;
                    }
                }
                
                if(TSAttributeValidator::checkCollectionAttributeHandleExists('meta_title')){
                    array_push($errors, "Attribute key : meta_title result: true");
                } else {
                    array_push($errors, "Attribute key : meta_title result: false");
                }
                
                //$entry->setAttribute('meta_title', 'bruv');
                //$entry->setAttribute('abbbhuikaoid', 'bruv');
            } else {
                array_push($errors, ("No page created... nothing to do"));
            }
            
        }

        //Map all the pages that were just created to each other
        $home = null;
        $home = array_pop($pageIdsToMap);   //To make it simple, pop last element of array, and assign all remaining pages
        foreach($pageIdsToMap as $pageIdToMap){
            $currPageToMap = Page::getByID($pageIdToMap);
            $this->assign($home,$pageIdToMap);
        }
        $control=$pagesToMap;

        $r = new \stdClass();
        $r->messages = $messages;
        $r->errors   = $errors;
        return new JsonResponse($r);
        \Core::make('app')->shutdown();
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
