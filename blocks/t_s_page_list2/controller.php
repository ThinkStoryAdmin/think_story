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
//use Concrete\Core\Tree\Node\Type\Topic;

use Express;

use \Concrete\Core\Tree\Type\Topic as TopicTree;
use \Concrete\Core\Tree\Node\Node as TreeNode;
use \Concrete\Core\Tree\Node\Type\Topic as TopicTreeNode;
use Concrete\Core\Attribute\Key\Category;

use Concrete\Core\Tree\Tree;
use Concrete\Core\Tree\Type\Topic;
//use Concrete\Package\TSTest\TSPageList\TSPageList;
use Concrete\Package\ThinkStory\Page\TSPageList;
use Concrete\Package\ThinkStory\AttributeValidator\TSAttributeValidator;

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
    //Make it so that Enable Grid COntainer is not an option for the block
    //However TOTALLY fucks the format, doesn't do what I thought
    //protected $btIgnorePageThemeGridFrameworkContainer = true;
    protected $pages;
    protected $topics_loc;
    protected $rightcolor;
    protected $themecomp;
    protected $categoryColorsMain;

    public function getBlockTypeDescription()
    {
        return t("Think Story Page List");
    }

    public function getBlockTypeName()
    {
        return t("Think Story Page List");
    }

    public function validate($data)
    {
        $e = Core::make('error');
        /*
        if (!$data['field1']) {
            $e->add(t('You must put something in the field 1 box.'));
        }*/
        if(!isset($data['expressColors']) && !$data['expressColors']){
            $e->add(t('You must select an Express Object'));
        }
        if(!$data['ptID']){
            //$e->add(t('You must select a page type'));
            //$data['ptID'] = 0;
        }
        return $e;
    }

    public function save($data){
        $data['topics'] = serialize($data['topics']);
        if(!isset($data['expressColors'])){
            $data['expressColors'] = 'FUG U';
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

    public function add()
    {
        $this->loadData();
        $this->requireAsset('core/topics');
        $c = Page::getCurrentPage();
        $this->set('c', $c);
        $this->set('includeName', true);
        $this->loadKeys();
    }

    public function edit()
    {
        $this->loadData();
        $this->requireAsset('core/topics');
        $b = $this->getBlockObject();
        $bCID = $b->getBlockCollectionID();
        $bID = $b->getBlockID();
        $this->set('bID', $bID);
        $c = Page::getCurrentPage();
        $this->loadKeys();
    }
    
    public function view()
    {
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

    public function getTopicLink(\Concrete\Core\Tree\Node\Node $topic = null)
    {
        if ($this->cParentID) {
            $c = \Page::getByID($this->cParentID);
        } else {
            $c = \Page::getCurrentPage();
        }
        if ($topic) {
            $nodeName = $topic->getTreeNodeName();
            $nodeName = strtolower($nodeName); // convert to lowercase
            $nodeName = preg_replace('/[[:space:]]+/', '-', $nodeName);
            $nodeName = Core::make('helper/text')->encodePath($nodeName); // urlencode
            //return \URL::page($c, 'topic', $topic->getTreeNodeID(), $nodeName);

            $urlTarget = \URL::page($c);
            $urlTarget .= '?topics[]=' . $topic->getTreeNodeID();
            return $urlTarget;
        } else {
            return \URL::page($c);
        }
    }

    public function loadData()
    {
        $r = $this->entityManager->getRepository(Entity::class);
        $entityObjects = $r->findAll();
        $entities = ['' => t("** No Entity")]; //Need AT LEAST & MOST ONE Express OBJECT TO BE CHOSEN
        foreach ($entityObjects as $entity) {
            $entities[$entity->getID()] = $entity->getEntityDisplayName();
        }
        $this->set('entities', $entities);
    }

    public function action_load_entity_data()
    {
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

    protected function getSearchPropertiesJsonArray($entity)
    {
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

    protected function loadKeys()
    {
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

    public function registerViewAssets($outputContent = '')
    {
        $al = \Concrete\Core\Asset\AssetList::getInstance();
        $this->requireAsset('javascript', 'jquery');
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');
        //$al->register('javascript', 'select2sortable', 'blocks/testimonial_stack_output/js_form/select2.sortable.js');
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

        if($this->ptID){
            $this->pages->filterByPageTypeHandle(PageType::getByID($this->ptID)->getPageTypeHandle());
        }

        //Cannot switch over object whose value could be 0, as if it is null will match with 0. Fucking weak-typed languages eh!
        //Regardless, these filter will be written over once the topic filter is applied
        //Except when it is not the sort that just sorts, and doesn't delete elements from the PageList
        //TODO : Remove this, it doesn't do anything once topic sorts are applied
        if($this->sortType === 0){
            switch($this->orderBy){
                case 'display_most_recent':
                    $this->pages->sortByPublicDate();
                    break;
                case 'display_most_popular':
                    if(TSAttributeValidator::checkCollectionAttributeHandleExists($this->viewCountAttribute)){
                        //Need to add 'ak_' to the start
                        $this->pages->sortBy('ak_'.$this->viewCountAttribute,'DESC');
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
        } else if ($this->sortType === 1) {
            switch($this->orderBy){
                case 'display_most_recent':
                    $this->pages->filterByPublicDate();
                    break;
                case 'display_most_popular':
                    if(TSAttributeValidator::checkCollectionAttributeHandleExists($this->viewCountAttribute)){
                        $this->pages->filterBy('ak_'.$this->viewCountAttribute,'DESC');
                    } else {
                        $this->pages->filterBy('RAND()');
                    }
                    break;
                case 'display_random':
                    $this->pages->filterBy('RAND()');
                    break;
                default:
                    $this->pages->filterBy('RAND()');
                    break;
            }
        } else {
            $this->pages->sortBy('RAND()');
        }
        
        if(ctype_xdigit(str_replace("-", "", $this->expressColors))){
            $entity = Express::getObjectByID($this->expressColors);
        } else { //Is probably handle
            $entity = Express::getObjectByHandle($this->expressColors);
        }
        if(!is_null($entity)){
            $listentities = new \Concrete\Core\Express\EntryList($entity);
            $categoryColors = $listentities->getResults();
            //$listentities = $listentities->filterByTopic('Publication sur Internet')->getResults();
            $this->categoryColorsMain = $listentities;
        }
        $this->set('entity', $entity);

        //return $this->pages;

        $c = Page::getCurrentPage();
        if ($c->getCollectionPointerExternalLink() != '') {
            $thisurl = $c->getCollectionPointerExternalLink();
        } else {
            $thisurl = $c->getCollectionLink();
        }
        $this->set("thisUrl", $thisurl);
    }

    public function getTopicColor(){

    }

    public function getThemeName($themeObject){
        if(is_array($theme)){ 
            $themecomp = $theme[0];
        }else{
            $themecomp=$theme;
        }
        $themename = $themecomp->getTreeNodeName();
        return $themename;
    }
    
    //Filters page list and get page topic / theme colors
    public function action_filter($data){
        $topics = null;
        $templist = $this->pages;
        $nums = [];
        if (isset($_POST['data'])) {}
        if ($this->post('topics')) {
            $topics = $this->request->post('topics');

            //Correction, filtering by multiple successive topics seems to work, but not sorting
            //So sorting is done manually in the action_filter method. Topic id's to filter by are collected here
            if($this->sortType == 1 || !isset($this->sortType)){
                foreach($topics as $topic){
                    if((!(intval($topic) == -1)) && is_int(intval($topic))){
                        $templist->filterByTopic(intval($topic));
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
        
        //Get the colors & stuff for the page list items
        $temppages = $templist->getResults();
        $pagedata = [];
        foreach($temppages as $temppage){
            if ($temppage->getCollectionPointerExternalLink() != '') {
                $url = $temppage->getCollectionPointerExternalLink();
            } else {
                $url = $temppage->getCollectionLink();
            }

            $themecomp = null;
            $themename = null;
            //$rightcolor = "b1b1b1";
            $rightcolor = null;

            $arrNegOnes = [-1];

            //Check if there are topics defined, and if at least one does not equal -1
            $goodToGo = false;
            if(is_null($topics)){

            } else if(!empty(array_diff($topics, $arrNegOnes))) {
                $goodToGo = true;
            } else {
                
            }
            if(/*count($topics) || */$goodToGo){
                //Need to get FULL LIST OF ALL ASSOCIATED TOPICS FOR THIS PAGE :
                $themes = [];
                $keys = CollectionKey::getList();
                foreach ($keys as $ak) {
                    if ($ak->getAttributeTypeHandle() == 'topics') {
                        $topicsForThisTree = $temppage->getAttribute($ak);
                        /*foreach($topicsForThisTree as $tftt){
                            array_push($themes, $tftt->getTreeNodeID());
                        }*/
                        if(is_array($topicsForThisTree)){
                            foreach($topicsForThisTree as $tftt){
                                array_push($themes, $tftt->getTreeNodeID());
                            }
                        } else if (!is_null($topicsForThisTree)){
                            array_push($themes, $topicsForThisTree->getTreeNodeID());
                        }
                    }
                }

                $theme = $temppage->getAttribute($this->pageTopicColors);
                
                $errorer = $themes;


                //Determine if the current page has relevant topics
                if(array_intersect($nums, $themes) == $nums){
                    $situation = 'if 1';
                    $sortOrder = 1;
                    $theme = $temppage->getAttribute($this->pageTopicColors);
                    if(is_array($theme)){ 
                        foreach($theme as $t){
                            if($t->getTreeNodeID() == $topics[0]){
                                $theme=$t;
                                break;
                            }
                        }
                    }else{
                        if($theme->getTreeNodeID() == $topics[0]){
                            $theme=$t;
                            break;
                        }
                    }

                    $tempcatcolor = $this->categoryColorsMain;

                    if((is_null($tempcatcolor)) || (empty($tempcatcolor))){
                        //$rightcolor = "#ff0000";
                    } else {
                        $tempcatcolor->filterByAttribute($this->expressColorsColorsAttribute, $theme);   
                        //Check color existance
                        $colortemp = $tempcatcolor->getResults();
                        //if(!is_array($colortemp)){
                        if(isset($colortemp[0])){
                            //$rightcolor = $colortemp[0]->getTsTopicColorColor();
                            //ts_topic_color_color
                            $rightcolor = $colortemp[0]->getAttributeValue($this->expressColorsTopicsAttribute)->getDisplayValue();
                            //$testera= $colortemp[0]->getTsTopicColorColor();
                                                    
                            if((is_null($rightcolor)) || (empty($rightcolor))){
                                //$rightcolor = "#ff0000";
                            } 
                            //else {
                            //    $rightcolor = "6d32a8";
                            //}
                            
                        } else {
                            //$rightcolor = "#a8328d";
                        }
                        
                    }
                    //Check theme existance
                    if(is_array($theme)){ 
                        $themecomp = $theme[0];
                    }else{
                        $themecomp=$theme;
                    }

                    //new
                    //$themename = $themecomp->getTreeNodeName();
                    if(!is_null($themecomp)){
                        $themename = $themecomp->getTreeNodeName();
                    } 
                } else {
                    $situation = 'if 2';
                    $sortOrder = 2;
                    //$rightcolor = "#b1b1b1";
                    $rightcolor = $this->defaultColor;
                    $theme = $temppage->getAttribute($this->pageTopicColors);
                    //Check theme existance
                    if(is_array($theme) ){ 
                        //$themecomp = $theme[0];
                        foreach($theme AS $themeItem){
                            if($themeItem){
                                $themename = $themeItem->getTreeNodeName();
                                break;
                            }
                        }
                    }else if(!is_null($theme)){
                        //$themecomp=$theme;
                        $themename = $theme->getTreeNodeName();
                    }
                    
                }
            } else {
                //THIS ELSE IS IF there are no topics to filter or sort by
                //If there are no topics defined, or they are all -1, then we just do as normal
                //(color should be set to that specific grey here)
                $theme = $temppage->getAttribute($this->pageTopicColors);
                $situation = 'else';
                $sortOrder = 2;

                if(is_null($theme) || empty($theme) || !(isset($theme))){
                    //$rightcolor = "#00ff00";
                } else {
                    //getAttributeValueObject
                    $tempcatcolor = $this->categoryColorsMain;
                    
                    if((is_null($tempcatcolor)) || (empty($tempcatcolor))){
                        //$rightcolor = "#ff0000";
                    } else {
                        $tempcatcolor->filterByAttribute($this->expressColorsColorsAttribute, $theme);   
                        //Check color existance
                        $colortemp = $tempcatcolor->getResults();
                        //if(!is_array($colortemp)){
                        if(isset($colortemp[0])){
                            //$rightcolor = $colortemp[0]->getTsTopicColorColor();
                            //ts_topic_color_color
                            $rightcolor = $colortemp[0]->getAttributeValue($this->expressColorsTopicsAttribute)->getDisplayValue();
                            //$testera= $colortemp[0]->getTsTopicColorColor();
                                                    
                            if((is_null($rightcolor)) || (empty($rightcolor))){
                                //$rightcolor = "#ff0000";
                            } 
                            //else {
                            //    $rightcolor = "6d32a8";
                            //}
                            
                        } else {
                            //$rightcolor = "#a8328d";
                        }
                        
                    }
                    //Check theme existance
                    if(is_array($theme)){ 
                        $themecomp = $theme[0];
                    }else{
                        $themecomp=$theme;
                    }
                    $themename = $themecomp->getTreeNodeName();
                }
            }

            if(!isset($rightcolor)){
                $rightcolor = $this->defaultColor;
                if($rightcolor[0] != '#'){
                    //$rightcolor = '#' . $rightcolor;
                }
            }

            if(!isset($themename) || is_null($themename)){
                $themename = " ";
            }

            array_push($pagedata, array(
                "title"=>$temppage->getCollectionName(), 
                "description"=>$temppage->getCollectionDescription(), 
                "url"=>$url,
                "theme"=> tc('TopicName',$themename),
                "color"=>$rightcolor,
                "sortOrder"=>$sortOrder,
                "situation"=>$situation,
                'errorers'=>$errorer
            ));
        }

        $didsort = false;
        //If there are topics to sort with, and sort type is of type sort (not filter), sort!
        //if ($this->post('topics')) {
            //CANNOT USE usort, AS IS NOT OBJECT BUT MULTIDIMENSIONAL ARRAY
            $sortOrders = array_column($pagedata, 'sortOrder');
            array_multisort($sortOrders, SORT_ASC, $pagedata);
            $crazyShit = 'Fuck MY ASS';
        //}

        echo json_encode(array("shit"=>$shitfuck, "recieved"=>$data, "topics"=>$topics,/* "count"=>count($temppages),*/ "nums"=>$nums, /*"pages"=>$temppages,*/ "pagedata" =>$pagedata));
        exit;
    }
}