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
    //Make it so that Enable Grid Container is not an option for the block
    //However TOTALLY fucks the format, doesn't do what I thought
    //protected $btIgnorePageThemeGridFrameworkContainer = true;
    protected $pages;
    protected $topics_loc;
    protected $rightcolor;
    protected $categoryColorsMain;

    public $LOGGER = [];
    public $relationsTC = array();

    public function getBlockTypeName()
    {
        return t("Think Story Page List Filter");
    }

    public function getBlockTypeDescription()
    {
        return t("Block that filters the TS Page List Result block");
    }
    
    public function validate($data)
    {
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

    public function add()
    {
        $this->loadData();
        $this->requireAsset('core/topics');
        $c = Page::getCurrentPage();
        $this->set('c', $c);
        $this->set('includeName', true);    //CHECK ??
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

        //TODO Set topic -> color relations
        $tcResls = $this->categoryColorsMain->getResults();
        foreach($tcResls AS $topicColor){
            //$this->set('ENTTYPE', get_class($topicColor));
            //$this->set('ENTTYPE', $topicColor->getAttributeValue($this->expressColorsTopicsAttribute));
            $this->set('ENTTYPE', get_class($topicColor->getAttributeValue($this->expressColorsColorsAttribute)));  //ConcreteCoreEntityAttributeValueExpressValue
            //$this->set('ENTTYPE', get_class($topicColor->getAttributeValue($this->expressColorsColorsAttribute)->getValue()));
            //$this->set('ENTTYPE', $topicColor->getAttributeValue($this->expressColorsColorsAttribute)->getAttributeValueID());  //ConcreteCoreEntityAttributeValueExpressValue

            $this->set('ENTTYPE', $topicColor->getAttributeValue($this->expressColorsColorsAttribute)->getDisplayValue());              //Gets the TOPIC NAME
            $this->set('ENTTYPE', $topicColor->getAttributeValue($this->expressColorsColorsAttribute)->getValue()[0]->getTreeNodeID()); //Gets the TOPIC ID

            //$this->relationsTC[$topicColor->getAttributeValue($this->expressColorsColorsAttribute)->getDisplayValue()] = $topicColor->getAttributeValue($this->expressColorsTopicsAttribute)->getDisplayValue();
            //$this->relationsTC[$topicColor->getAttributeValue($this->expressColorsColorsAttribute)->getValue()] = $topicColor->getAttributeValue($this->expressColorsTopicsAttribute)->getDisplayValue();
            
            //ID = Topic ID
            $this->relationsTC[$topicColor->getAttributeValue($this->expressColorsColorsAttribute)->getValue()[0]->getTreeNodeID()] = $topicColor->getAttributeValue($this->expressColorsTopicsAttribute)->getDisplayValue();

            //ID = Topic Name
            //$this->relationsTC[$topicColor->getAttributeValue($this->expressColorsColorsAttribute)->getDisplayValue()] = $topicColor->getAttributeValue($this->expressColorsTopicsAttribute)->getDisplayValue();
            
        }
        $this->set('RELTC', json_encode($this->relationsTC));
    }

    //TODO put this in on_start, get FULL list of topic -> color, then just query that!
    public function getTopicColor($topicName){
        if(is_object($topicName)){
            array_push($this->$LOGGER, "TopicName class: " + get_class($topicName));
        } /*else if(!is_array($topicName)) {
            array_push($this->$LOGGER, "TopicName class: " + strval(gettype($topicName)));
        }*/
        if(gettype($topicName)){
            array_push($this->$LOGGER, "TopicName class: ");
            array_push($this->$LOGGER, strval(gettype($topicName)));
            array_push($this->$LOGGER, $topicName);
            /*if(is_array($topicName)){
                array_push($this->$LOGGER, implode($topicName));
            }*/
        }
        
        $tempcatcolor = $this->categoryColorsMain;
        if(!is_null($tempcatcolor) && !empty($tempcatcolor)){
            $tempcatcolor->filterByAttribute($this->expressColorsColorsAttribute, $topicName);      //TODO FIX THE BUG IS HERE!!!
            $colortemp = $tempcatcolor->getResults();   //TODO the bug appears here, but it is because we filtered 2 lines above!
            if(isset($colortemp[0])){//Check color existance
                return $colortemp[0]->getAttributeValue($this->expressColorsTopicsAttribute)->getDisplayValue();
            }
        } 
        array_push($this->$LOGGER, "WHY");
        return null;
    }

    public function getTopicColor2($topicName){
        return $this->relationsTC[$topicName];
    }

    public function getThemeID($theme){
        if(is_array($theme)){ 
            $themecomp = $theme[0];
            /*foreach($theme AS $themeItem){
                if($themeItem){
                    $themename = $themeItem->getTreeNodeName(); break;
                }
            }*/
        }else{
            $themecomp=$theme;
        }
        return $themecomp->getTreeNodeID();
    }

    public function getThemeName($theme){
        if(is_array($theme)){ 
            $themecomp = $theme[0];
            /*foreach($theme AS $themeItem){
                if($themeItem){
                    $themename = $themeItem->getTreeNodeName(); break;
                }
            }*/
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
        if ($this->post('topics')) {
            $topics = $this->request->post('topics');
            //Correction, filtering by multiple successive topics seems to work, but not sorting -> do manually. Topic id's to filter by are collected below
            if($this->sortType == 1 || !isset($this->sortType)){
                foreach($topics as $topic){
                    if((!(intval($topic) == -1)) && is_int(intval($topic))){
                        $templist->filterByTopic(intval($topic));
                        array_push($nums, intval($topic));
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
        
        //FOREACH PAGE : Get the colors & stuff for the page list items
        $temppages = $templist->getResults();
        $pagedata = [];
        foreach($temppages as $temppage){
            $rightcolor = $this->defaultColor;//If no color is set, set it to default
            $themename = " ";//If no theme is set, set theme name to blank text TODO make this parameter
		  	$this->$LOGGER = [];    //Hold debug information for the current page
		  	
            if ($temppage->getCollectionPointerExternalLink() != '') {
                $url = $temppage->getCollectionPointerExternalLink();
            } else {
                $url = $temppage->getCollectionLink();
            }

            $theme = $temppage->getAttribute($this->pageTopicColors);

            //If there are topics defined, and if at least one does not equal -1
            if(!is_null($topics) && !empty(array_diff($topics, [-1]))){
                if(array_intersect($nums, $this->getPageTopics($temppage)) == $nums){ //if the current page has relevant topics
				  	array_push($this->$LOGGER, 'if 1');
                    $sortOrder = 1;

                    array_push($this->$LOGGER, implode($nums));
                    array_push($this->$LOGGER, implode($this->getPageTopics($temppage)));
                    array_push($this->$LOGGER, implode(", ", array_intersect($nums, $this->getPageTopics($temppage))));

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
                    

                    

                    //TODO NEED TO GET THE TOPIC FROM THE SEARCH, NOT THE FIRST TOPIC OF THE PAGE!!!

                    /*if(is_array($theme)){ 
                        foreach($theme as $t){
                            if($t->getTreeNodeID() == $topics[0]){
                                $theme=$t;
                                break;
                            }
                        }
                    }
                    if(is_array($theme)){ 
                        array_push($this->$LOGGER, 'OH SHIT');
                    }
                    $testFix = $theme;
                    if(is_a($testFix, "Concrete\\Core\\Tree\\Node\\Type\\Topic")){ //"\Concrete\Core\Tree\Type\Topic"
						array_push($this->$LOGGER, 'if 1.2a1');
                        $testFix = $testFix->getTreeNodeID();
                        array_push($this->$LOGGER, "{$testFix}");
					} else {
						array_push($this->$LOGGER, 'if 1.2a2');
                        array_push($this->$LOGGER, "{$testFix}");
					}
                    $rightcolor = $this->getTopicColor($testFix);
                    array_push($this->$LOGGER, "RIGHT COL: {$rightcolor}");*/

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
                $cond=2;
                array_push($this->$LOGGER, 'else');
                $sortOrder = 2;

                if(!is_null($theme) && !empty($theme) && isset($theme)){
                    array_push($this->$LOGGER, 'else 1');
                    //$rightcolor = $this->getTopicColor($theme); //THIS RETURNS AN ARRAY OF Concrete\\Core\\Tree\\Node\\Type\\Topic
                    //$rightcolor = $this->getTopicColor($this->getThemeName($theme));
                    /*if(is_array($theme)){
                        $rightcolor = $this->getTopicColor($theme[0]->getTreeNodeID());
                    } else {
                        $rightcolor = $this->getTopicColor($theme->getTreeNodeID());
                    }*/

                    $themename = $this->getThemeName($theme);
                    //$rightcolor = $this->getTopicColor2($themename);
                    if(is_array($theme)){
                        $rightcolor = $this->getTopicColor2($theme[0]->getTreeNodeID());
                    } else {
                        $rightcolor = $this->getTopicColor2($theme->getTreeNodeID());
                    }
                }
            }

            //IF NOT RELEVANT (i.e. sort order == 2), then use array_unshift
            $newPageToAddToList = array(
                "cond"=>$cond,
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