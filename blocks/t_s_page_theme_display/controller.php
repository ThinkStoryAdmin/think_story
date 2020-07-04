<?php
namespace Concrete\Package\ThinkStory\Block\TSPageThemeDisplay;

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
        return t("Shows the first page theme, with the theme's associated color as the background.");
    }

    public function getBlockTypeName()
    {
        return t("Think Story Page Theme Display");
    }

    public function validate($data)
    {
        $e = Core::make('error');
        if(!$data['expressColors']){
            //$e->
        }
        return $e;
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
        
        /*$entity = Express::getObjectByHandle('tstopiccolor');
        $listentities = new \Concrete\Core\Express\EntryList($entity);
        $categoryColors = $listentities->getResults();
        //$listentities = $listentities->filterByTopic('Publication sur Internet')->getResults();
        $this->categoryColorsMain = $listentities;*/

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

        $c = Page::getCurrentPage();
        if ($c->getCollectionPointerExternalLink() != '') {
            $thisurl = $c->getCollectionPointerExternalLink();
        } else {
            $thisurl = $c->getCollectionLink();
        }
        $this->set("thisUrl", $thisurl);
    }

    public function view()
    {
        /*$entity = Express::getObjectByHandle('tstopiccolor');
        $listentities = new \Concrete\Core\Express\EntryList($entity);
        $categoryColors = $listentities->getResults();*/

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
        
        $this->set('categorycolors_pl2', $categoryColors);
        $this->set('catcolorslist', $listentities);
        $this->set("topicAttr", $this->topic);
        $c = Page::getCurrentPage();
        
        $this->set('pageTopic', $c->getAttribute($this->topic)[0]);
        

        $temppage = \Page::getCurrentPage();

        //This is held in topic (bd)topic
        //$theme = $temppage->getAttribute('ts_pattr_topic_theme');
        $theme = $temppage->getAttribute($this->topic);
        if(is_null($theme) || empty($theme) || !(isset($theme))){
            //$rightcolor = "#00ff00";
            $rightcolor = '';
        } else {
            $tempcatcolor = $listentities; //$this->categoryColorsMain;  
            //$tempcatcolor->filterByAttribute('ts_topic_color_topic', $theme); 
            $tempcatcolor->filterByAttribute($this->expressColorsTopicsAttribute, $theme); 
            if((is_null($tempcatcolor)) || (empty($tempcatcolor))){
                $rightcolor = "#ff0000";
                //$rightcolor = '';
            } else {
                //Check color existance
                $colortemp = $tempcatcolor->getResults();
                if(isset($colortemp[0])){
                    //$rightcolor = $colortemp[0]->getAttributeValue('ts_topic_color_color')->getDisplayValue();
                    $rightcolor = $colortemp[0]->getAttributeValue($this->expressColorsColorsAttribute)->getDisplayValue();
                    if((is_null($rightcolor)) || (empty($rightcolor))){
                        $rightcolor = "#ff0000";
                        //$rightcolor = '';
                    }
                } else {
                    $rightcolor = "#a8328d";
                    //$rightcolor = '';
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
        $this->set('pageTopicColor', $rightcolor);
    }
}