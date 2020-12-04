<?php
namespace Concrete\Package\ThinkStory\Block\TSPageThemeDisplay;

use ThinkStory\TSTopicColorHelper;

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
    protected $btTable = 'btTSPageThemeDisplay';
    protected $btInterfaceWidth = 700;
    protected $btInterfaceHeight = 525;
    protected $btExportPageColumns = ['cParentID'];
    protected $btExportPageTypeColumns = ['ptID'];
    protected $btDefaultSet = 'think_story';
    protected $categoryColorsMain;
    protected $pageTopics = NULL;
    public $relationsTC = array();

    public function getBlockTypeDescription()
    {
        return t("Shows the first page theme, with the theme's associated color as the background.");
    }

    public function getBlockTypeName()
    {
        return t("Think Story Page Theme Display");
    }
    
    public function save($data){
        parent::save($data);
    }

    public function add()
    {
        $this->loadData();
        $this->requireAsset('core/topics');
        $c = Page::getCurrentPage();
        $uh = Core::make('helper/concrete/urls');
        $this->set('c', $c);
        $this->set('uh', $uh);
        $this->set('includeDescription', true);
        $this->set('includeName', true);
        $this->set('bt', BlockType::getByHandle('page_list'));
        $this->set('featuredAttribute', CollectionAttributeKey::getByHandle('is_featured'));
        $this->set('thumbnailAttribute', CollectionAttributeKey::getByHandle('thumbnail'));
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
        if ((!$this->cThis) && (!$this->cThisParent) && ($this->cParentID != 0)) {
            $isOtherPage = true;
            $this->set('isOtherPage', true);
        }
        $uh = Core::make('helper/concrete/urls');
        $this->set('uh', $uh);
        $this->set('bt', BlockType::getByHandle('page_list'));
        $this->loadKeys();
    }

    protected function loadKeys()
    {
        $attributeKeys = [];
        $keys = CollectionKey::getList();
        foreach ($keys as $ak) {
            if ($ak->getAttributeTypeHandle() == 'topics') {
                $attributeKeys[] = $ak;
            }
        }
        $this->set('keyers', $keys);
        $this->set('attributeKeys', $attributeKeys);
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
        $found = false;
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
                $found = true;
                return new JsonResponse($r);
            }
        }
        if(!$found){
            $r = new \stdClass();
            $r->attributes = null;
            return new JsonResponse($r);
        }
        //\Core::make('app')->shutdown();
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

    public function registerViewAssets($outputContent = '')
    {
        $al = \Concrete\Core\Asset\AssetList::getInstance();

        $this->requireAsset('javascript', 'jquery');
        $this->requireAsset('css', 'select2');
        $this->requireAsset('javascript', 'select2');
        //$al->register('javascript', 'select2sortable', 'blocks/testimonial_stack_output/js_form/select2.sortable.js');
    }

    public function on_start(){
        $this->app = Facade::getFacadeApplication();
        $this->entityManager = $this->app->make('database/orm')->entityManager();

        if(ctype_xdigit(str_replace("-", "", $this->expressColors))){
            $entity = Express::getObjectByID($this->expressColors);
        } else { //Is probably handle
            $entity = Express::getObjectByHandle($this->expressColors);
        }
        if(!is_null($entity)){
            $listentities = new \Concrete\Core\Express\EntryList($entity);
            $categoryColors = $listentities->getResults();
            $this->categoryColorsMain = $listentities;
        }

        foreach($this->categoryColorsMain->getResults() AS $topicColor){    //$tcResls = $this->categoryColorsMain->getResults();
            try{
                $this->relationsTC[$topicColor->getAttributeValue($this->expressColorsTopicsAttribute)->getValue()[0]->getTreeNodeID()] = $topicColor->getAttributeValue($this->expressColorsColorsAttribute)->getDisplayValue();
            } catch(\Exception $e){} catch(\Throwable $e){} //NEED TO USE \ OR IT DOESN'T CATCH
        }
    }

    //TODO put following methods in seperate class, as the are duplicated in t_s_page_theme_display
    public function getTopicColor2($topicName){
        return $this->relationsTC[$topicName];
    }

    public function action_gettopcols($data){
        $temppage = \Page::getCurrentPage();
        $theme = $temppage->getAttribute($this->topic);

        $nums = [];
        if ($this->request->post('topics')) {    //Correction, filtering by multiple successive topics seems to work, but not sorting -> do manually. Topic id's to filter by are collected below
            $topics = $this->request->post('topics');

            foreach($topics as $topic){
                if((!(intval($topic) == -1)) && is_int(intval($topic))){
                    array_push($nums, intval($topic));
                }
            }
        }
        
        if(!is_null($topics) && !empty(array_diff($topics, [-1]))){     //If there are topics defined, and if at least one does not equal -1
            if(array_intersect($nums, TSTopicColorHelper::getPageTopics($temppage)) == $nums){ //if the current page has relevant topics
                $correctTopic = array_intersect($nums, TSTopicColorHelper::getPageTopics($temppage))[0];
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
                    $themename = TSTopicColorHelper::getThemeName($theme);
                    $rightcolor = $this->getTopicColor2(TSTopicColorHelper::getThemeID($theme));
                }
            } else {    //Else no matching topics
                $rightcolor = $this->defaultColor;
                $themename = TSTopicColorHelper::getThemeName($theme);
            }
        } else {//ELSE there are no topics to filter or sort by
            if(!is_null($theme) && !empty($theme) && isset($theme)){
                $themename = TSTopicColorHelper::getThemeName($theme);
                if(is_array($theme)){
                    $rightcolor = $this->getTopicColor2($theme[0]->getTreeNodeID());
                } else {
                    $rightcolor = $this->getTopicColor2($theme->getTreeNodeID());
                }
            }
        }
        echo json_encode(array('data' => $this->request->post(), 'theme' => $themename, 'color' => $rightcolor));
        exit;
    }

    //FOR THIS TO WORK, need to add a / after the url, before params, which isn't great
    //https://documentation.concrete5.org/developers/working-with-blocks/creating-a-new-block-type/interactive-blocks/passing-data
    /*public function getPassThruActionAndParameters($parameters) 
    {
        $method = "action_add";
        return [$method, $parameters];
    }
    public function action_add($parameters){
        $this->pageTopics = "MAH MAN";
        $this->view();
    }*/

    public function view()    {
        $temppage = \Page::getCurrentPage();
        $this->set('pageTopic', $temppage->getAttribute($this->topic)[0]);
        $this->set('bID', $this->bID);
        
        //TODO a block of code was copied here from t_s_page_list2 for topic color stuff (check deletions)
        $theme = $temppage->getAttribute($this->topic);
        if(!is_null($theme) && !empty($theme) && isset($theme)){
            if(is_array($theme)){
                $rightcolor = $this->getTopicColor2($theme[0]->getTreeNodeID());
            } else {
                $rightcolor = $this->getTopicColor2($theme->getTreeNodeID());
            }
        }

        $this->set('pageTopicColor', $rightcolor);
    }
}