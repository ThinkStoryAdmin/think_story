<?php
namespace Concrete\Package\ThinkStory\Block\TSPageList2;

use BlockType;
use CollectionAttributeKey;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Html\Service\Seo;
use Concrete\Core\Page\Feed;
use Concrete\Core\Tree\Node\Node;
use Concrete\Core\Url\SeoCanonical;
use Database;
use Page;
use Core;
use PageList;
use PageType;
use Concrete\Core\Attribute\Key\CollectionKey;

use Express;

use \Concrete\Core\Tree\Type\Topic as TopicTree;
use \Concrete\Core\Tree\Node\Node as TreeNode;
use \Concrete\Core\Tree\Node\Type\Topic as TopicTreeNode;
use Concrete\Core\Attribute\Key\Category;

use Concrete\Core\Tree\Tree;
use Concrete\Core\Tree\Type\Topic;
use Concrete\Package\ThinkStory\Page\TSPageList;
use ThinkStory\AttributeValidator\TSAttributeValidator;

//For Express List stuff
use Concrete\Core\Support\Facade\Facade;
use Concrete\Core\Entity\Express\Entity;

use Concrete\Controller\Element\Search\CustomizeResults;
use Concrete\Controller\Element\Search\SearchFieldSelector;
use Concrete\Core\Express\Search\SearchProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Concrete\Core\Search\Field\ManagerFactory;
use Concrete\Core\Entity\Express\ManyToManyAssociation;
use Concrete\Core\Entity\Express\ManyToOneAssociation;

class Controller extends BlockController
{
    protected $btTable = 'btTSPageList2';
    protected $btInterfaceWidth = 700;
    protected $btInterfaceHeight = 525;
    protected $btExportPageColumns = ['cParentID'];
    protected $btExportPageTypeColumns = ['ptID'];
    protected $btDefaultSet = 'think_story';
    //protected $btIgnorePageThemeGridFrameworkContainer = true;    //Makes it so that Enable Grid Container isn't option for the block, but fucks the format
    protected $pages;
    protected $topics_loc;
    protected $rightcolor;
    protected $categoryColorsMain;

    public $LOGGER = [];
    public $relationsTC = array();

    public function getBlockTypeName()  {
        return t("Think Story Page List Filter");
    }

    public function getBlockTypeDescription()   {
        return t("Block that filters the TS Page List Result block");
    }
    
    public function validate($data) {
        $e = Core::make('error');
        if(!isset($data['expressColors']) && !$data['expressColors']){
            $e->add(t('You must select an Express Object'));
        }
        if(!$data['ptID']){ //TODO need? ! -> remove
            //$e->add(t('You must select a page type'));
            //$data['ptID'] = 0;
        }
        return $e;
    }

    public function save($data){
        $data['topics'] = serialize($data['topics']);
        if(!isset($data['expressColors'])){
            $data['expressColors'] = 'FUG U';   //TODO check if this causes errors
        } else {
            $data['expressColors'] = strval($data['expressColors']);
        }

        $data += array(
            'externalTarget' => 0,
        );
        $externalTarget = intval($data['externalTarget']);
        if ($externalTarget === 0) {
            $data['cParentID'] = 0;
            $data['bPostToAnotherPage'] = 0;
        } else {
            $data['cParentID'] = intval($data['cParentID']);
            $data['bPostToAnotherPage'] = 1;
        }

        parent::save($data);
    }

    public function add()   {
        $this->loadData();
        $this->requireAsset('core/topics');
        $c = Page::getCurrentPage();
        $this->set('c', $c);
        $this->set('includeName', true);    //CHECK ??
        $this->loadKeys();
    }

    public function edit()  {
        $this->loadData();
        $this->requireAsset('core/topics');
        $b = $this->getBlockObject();
        $bCID = $b->getBlockCollectionID();
        $bID = $b->getBlockID();
        $this->set('bID', $bID);
        $c = Page::getCurrentPage();
        $this->loadKeys();
    }
    
    public function view()  {
        $this->set('bPostToAnotherPage', $this->bPostToAnotherPage);
        $this->set('cParentID', $this->cParentID);
        $this->set('cParentIDURL', \Page::getByID($this->cParentID)->getCollectionLink());

        $trees = [];
        $bruh = unserialize($this->topics);
        
        if(is_array($bruh)){
            foreach($bruh as $topic_loc_i){
                $tt     = TopicTree::getByID($topic_loc_i);
                array_push($trees, $tt);
            }
        }

        $this->set('topictrees', $trees);
    }

    public function loadData()  {
        $r = $this->entityManager->getRepository(Entity::class);
        $entityObjects = $r->findAll();
        $entities = ['' => t("** No Entity")]; //Need AT LEAST & MOST ONE Express OBJECT TO BE CHOSEN
        foreach ($entityObjects as $entity) {
            $entities[$entity->getID()] = $entity->getEntityDisplayName();
        }
        $this->set('entities', $entities);
    }

    public function action_load_entity_data()   {
        $exEntityID = $this->request->request->get('expressColorsSelc');
        if ($exEntityID) {
            $entity = $this->entityManager->find(Entity::class, $exEntityID);
            if (is_object($entity)) {
                $provider = \Core::make(SearchProvider::class, [$entity, $entity->getAttributeKeyCategory()]);
                $element = new CustomizeResults($provider);
                $element->setIncludeNumberOfResults(false);
                $r = new \stdClass();
                $r->attributes = $this->getSearchPropertiesJsonArray($entity);
                $this->set('r', $r);
                return new JsonResponse($r);
            }
        }
        \Core::make('app')->shutdown();
    }

    protected function getSearchPropertiesJsonArray($entity)    {
        $attributes = $entity->getAttributeKeyCategory()->getList();
        $select = [];
        foreach ($attributes as $ak) {
            $o = new \stdClass();
            $o->akHandle = $ak->getAttributeKeyHandle();
            $o->akName = $ak->getAttributeKeyDisplayName();
            $select[] = $o;
        }
        return $select;
    }

    protected function loadKeys()   {
        $attributeKeysTopics = [];
        $attributeKeysTopicLinkedColor = [];
        $keys = CollectionKey::getList();
        foreach ($keys as $ak) {
            if ($ak->getAttributeTypeHandle() == 'topics') {
                $attributeKeysTopics[$ak->getController()->getTopicTreeID()] = $ak->getAttributeKeyDisplayName();
                $attributeKeysTopicLinkedColor[$ak->getAttributeKeyHandle()] = $ak->getAttributeKeyDisplayName();
            }
        }
        $this->set('keyers', $keys);
        $this->set('attributeKeysTopics', $attributeKeysTopics);
        $this->set('chosenTopics', unserialize($this->topics));
        $this->set('attributeKeysTopicLinkedColor', $attributeKeysTopicLinkedColor);

        //For View Counter
        $attributeKeysVC = [];
        $keysVC = CollectionKey::getList();
        foreach ($keysVC as $akVC) {
            $attributeKeysVC[$akVC->getAttributeKeyHandle()] = $akVC->getAttributeKeyName();
        }
        $this->set('keyersVC', $keysVC);
        $this->set('attributeKeysVC', $attributeKeysVC);
    }

    public function registerViewAssets($outputContent = '') {
        $al = \Concrete\Core\Asset\AssetList::getInstance();    //$al->register('javascript', 'select2sortable', 'blocks/testimonial_stack_output/js_form/select2.sortable.js');
        $this->requireAsset('javascript', 'jquery');
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');
    }

    public function registerAssets(){
        $this->requireAsset('css', 'font-awesome');
    }

    public function on_start(){
        $this->app = Facade::getFacadeApplication();
        $this->entityManager = $this->app->make('database/orm')->entityManager();
        $this->pages = new PageList();
        $this->pages->disableAutomaticSorting();
        $this->pages->setNameSpace('b' . $this->bID);
        $this->pages->getQueryObject()->setMaxResults($num);

        if($this->ptID){        $this->pages->filterByPageTypeHandle(PageType::getByID($this->ptID)->getPageTypeHandle());      }

        switch($this->orderBy){
            case 'display_most_recent':
                $this->pages->sortByPublicDate();
                break;
            case 'display_most_popular':
                if(TSAttributeValidator::checkCollectionAttributeHandleExists($this->viewCountAttribute)){
                    $this->pages->sortBy('ak_'.$this->viewCountAttribute,'DESC');   //Need to add 'ak_' to the start
                } else {
                    $this->pages->sortBy('RAND()');
                }
                break;
            case 'display_random':
                $this->pages->sortBy('RAND()');
                break;
            default:
                $this->pages->sortBy('RAND()');
                break;
        }
        
        if(ctype_xdigit(str_replace("-", "", $this->expressColors))){
            $entity = Express::getObjectByID($this->expressColors);
        } else { //Is probably handle
            $entity = Express::getObjectByHandle($this->expressColors);
        }
        
        if(!is_null($entity)){
            $listentities = new \Concrete\Core\Express\EntryList($entity);
            $this->categoryColorsMain = $listentities;
        }
        $this->set('entity', $entity);
        
        foreach($this->categoryColorsMain->getResults() AS $topicColor){    //$tcResls = $this->categoryColorsMain->getResults();
            $this->relationsTC[$topicColor->getAttributeValue($this->expressColorsColorsAttribute)->getValue()[0]->getTreeNodeID()] = $topicColor->getAttributeValue($this->expressColorsTopicsAttribute)->getDisplayValue();
        }
    }

    public function getTopicColor2($topicName){
        return $this->relationsTC[$topicName];
    }

    public function getThemeID($theme){
        if(is_array($theme)){ 
            $themecomp = $theme[0];
        } else {
            $themecomp=$theme;
        }
        return $themecomp->getTreeNodeID();
    }

    public function getThemeName($theme){
        if(is_array($theme)){ 
            $themecomp = $theme[0];
            /*foreach($theme AS $themeItem){
                if($themeItem){         $themename = $themeItem->getTreeNodeName(); break;          }           }*/
        }else{
            $themecomp=$theme;
        }
        return $themecomp->getTreeNodeName();
    }

    public function getPageTopics($page){   //TODO check if this works for heavily nested topic trees
        $themes = [];
        $keys = CollectionKey::getList();
        foreach ($keys as $ak) {
            if ($ak->getAttributeTypeHandle() == 'topics') {
                $topicsForThisTree = $page->getAttribute($ak);
                if(is_array($topicsForThisTree)){
                    foreach($topicsForThisTree as $tftt){
                        array_push($themes, $tftt->getTreeNodeID());
                    }
                } else if (!is_null($topicsForThisTree)){
                    array_push($themes, $topicsForThisTree->getTreeNodeID());
                }
            }
        }
        return $themes;
    }
    
    //Filters page list and get page topic / theme colors, 0 IS SORT, 1 IS FILTER
    public function action_filter($data){
        $topics = null;
        $templist = $this->pages;
        $nums = [];
        if ($this->post('topics')) {    //Correction, filtering by multiple successive topics seems to work, but not sorting -> do manually. Topic id's to filter by are collected below
            $topics = $this->request->post('topics');
            if($this->sortType == 1 || !isset($this->sortType)){
                foreach($topics as $topic){
                    if((!(intval($topic) == -1)) && is_int(intval($topic))){
                        $templist->filterByTopic(intval($topic));
                        array_push($nums, intval($topic));  //Still need to collect topics for page block headers
                    }
                }
            } else {
                foreach($topics as $topic){
                    if((!(intval($topic) == -1)) && is_int(intval($topic))){
                        array_push($nums, intval($topic));
                    }
                }
            }
        }
        
        $temppages = $templist->getResults();
        $pagedata = [];
        foreach($temppages as $temppage){   //FOREACH PAGE : Get the colors & stuff for the page list items
            $rightcolor = $this->defaultColor;  //If no color is set, set it to default
            $themename = " ";                   //If no theme is set, set theme name to blank text TODO make this parameter
		  	$this->$LOGGER = [];                //Hold debug information for the current page
		  	
            if ($temppage->getCollectionPointerExternalLink() != '') {
                $url = $temppage->getCollectionPointerExternalLink();
            } else {
                $url = $temppage->getCollectionLink();
            }

            $theme = $temppage->getAttribute($this->pageTopicColors);

            if(!is_null($topics) && !empty(array_diff($topics, [-1]))){     //If there are topics defined, and if at least one does not equal -1
                if(array_intersect($nums, $this->getPageTopics($temppage)) == $nums){ //if the current page has relevant topics
				  	array_push($this->$LOGGER, 'if 1');
                    $sortOrder = 1;
                    $correctTopic = array_intersect($nums, $this->getPageTopics($temppage))[0];
                    $found = false;
                    if(is_array($theme)){
                        foreach($theme AS $t){
                            if($t->getTreeNodeID() == $correctTopic){
                                $found = true;
                                $themename = $t->getTreeNodeName();
                                $rightcolor = $this->getTopicColor2($t->getTreeNodeID());
                            }
                        }
                    }
                    if(!$found){
                        $themename = $this->getThemeName($theme);
                        $rightcolor = $this->getTopicColor2($this->getThemeID($theme));
                    }
                } else {    //Else no matching topics
                    array_push($this->$LOGGER, 'if 2');
                    $sortOrder = 2;
                    $rightcolor = $this->defaultColor;
                    $themename = $this->getThemeName($theme);
                }
            } else {//THIS ELSE IS IF there are no topics to filter or sort by. If there are no topics / all -1, then default color (i.e. gray)
                $sortOrder = 2;
                if(!is_null($theme) && !empty($theme) && isset($theme)){
                    array_push($this->$LOGGER, 'else 1');
                    $themename = $this->getThemeName($theme);
                    if(is_array($theme)){
                        $rightcolor = $this->getTopicColor2($theme[0]->getTreeNodeID());
                    } else {
                        $rightcolor = $this->getTopicColor2($theme->getTreeNodeID());
                    }
                }
            }

            $newPageToAddToList = array(
                "title"=>$temppage->getCollectionName(), 
                "description"=>$temppage->getCollectionDescription(), 
                "url"=>$url,
                "theme"=> tc('TopicName',$themename),
                "color"=>$rightcolor,
                "LOGGER"=> is_array($this->$LOGGER) ? implode('; ', $this->$LOGGER) : $this->$LOGGER,
                "sortOrder"=>$sortOrder
            );

            if($sortOrder == 1){
                array_unshift($pagedata, $newPageToAddToList);      //If page IS relevant to search, then add to start of list
            } else {
                array_push($pagedata, $newPageToAddToList);         //If page IS NOT relevant to search, then add to end of list
            }
        }

        echo json_encode(array("recieved"=>$data, "topics"=>$topics, "nums"=>$nums, "pagedata" =>$pagedata));
        exit;
    }
}