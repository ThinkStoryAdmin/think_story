<?php
namespace Concrete\Package\ThinkStory\Block\TSTopicList;

use Concrete\Core\Attribute\Key\CollectionKey;
use Concrete\Core\Block\BlockController;
use Concrete\Core\Tree\Tree;
use Concrete\Core\Tree\Type\Topic as TopicTree;
use Concrete\Core\Tree\Type\Topic;
use Core;
use Express;

defined('C5_EXECUTE') or die("Access Denied.");

class Controller extends BlockController
{
    public $helpers = array('form');

    protected $btInterfaceWidth = 400;
    protected $btInterfaceHeight = 400;
    protected $btTable = 'btTSTopicList';
    protected $btExportPageColumns = ['cParentID'];
    protected $btDefaultSet = 'think_story';

    public function getBlockTypeDescription()
    {
        return t("TS Displays a list of your site's topics, allowing you to click on them to filter a page list.");
    }

    public function getBlockTypeName()
    {
        return t("TS Topic List");
    }

    public function add()
    {
        $this->edit();
        $this->set('title', t('Topics'));
    }

    public function edit()
    {
        $this->requireAsset('core/topics');
        $tt = new TopicTree();
        $defaultTree = $tt->getDefault();
        $tree = $tt->getByID(Core::make('helper/security')->sanitizeInt($this->topicTreeID));
        if (!$tree) {
            $tree = $defaultTree;
        }
        $trees = $tt->getList();
        $keys = CollectionKey::getList();
        $attributeKeys = array();
        foreach ($keys as $ak) {
            //if((!is_null($ak)) && isset($ak) && !(empty($ak))){
            if(isset($ak) && !empty($ak) ){
                //Whu.... WHY does this work?
                if(($ak==null) AND ($ak==NULL)){
                    try{
                        if ($ak->getAttributeTypeHandle() == 'topics') {
                            $attributeKeys[] = $ak;
                        }
                    } catch(Exception $e){

                    }
                    
                }
            }
        }
        $this->set('attributeKeys', $attributeKeys);
        $this->set('tree', $tree);
        $this->set('trees', $trees);
    }

    public function view()
    {
        //$this->post('topic', )
        if ($this->mode == 'P') {
            $page = \Page::getCurrentPage();
            $topics = $page->getAttribute($this->topicAttributeKeyHandle);
            if (is_array($topics)) {
                $this->set('topics', $topics);
            }
        } else {
            $tt = new TopicTree();
            $tree = $tt->getByID(Core::make('helper/security')->sanitizeInt($this->topicTreeID));
            $this->set('tree', $tree);

            $arr_placeholder = array();
            $topicsb = array();
            $topicsc = array();
            $node = $tree->getRootTreeNodeObject();
            $node->populateChildren();
            if (is_object($node)) {
                foreach ($node->getChildNodes() as $topic){
                    array_push($topicsb, $topic->getTreeNodeDisplayName());
                    array_push($topicsc, $topic->getTreeNodeID());
                }
            }
            $this->set('topicsb', $topicsb);
            $this->set('topicsc', $topicsc);
        }
    }

    public function action_topic($treeNodeID = false, $topic = false, $bID = false)
    {
        $this->set('selectedTopicID', intval($treeNodeID));
        $this->view();
    }

    public function action_topic2($treeNodeID = false, $topic = false, $bID = false)
    {
        $this->set('selectedTopicID', intval($treeNodeID));
        $this->view();
    }

    public function action_topic_set($topic2)
    {
        $this->set('topicthingy', intval(17));
        //echo json_encode($topic2);
        //$this->view();
    }

    public function action_reply_test($bID = false, $treeNodeID = false, $topic = false){
        echo json_encode('Hello');
        exit;
    }

    public function action_reply_test3($parats = 0, $bID = false){
        echo json_encode($parats);
        exit;
    }

    public function action_reply_test2($input, $bID = false){
        echo json_encode($input);
        exit;
    }

    public function action_hep_me($input){
        $c = \Page::getCurrentPage();
        $this->redirect($c->getCollectionPath() + '?metier=3');
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

    public static function replaceTreeWithPlaceHolder($treeID)
    {
        if ($treeID > 0) {
            $tree = Tree::getByID($treeID);
            if (is_object($tree)) {
                return '{ccm:export:tree:' . $tree->getTreeName() . '}';
            }
        }
    }

    public function export(\SimpleXMLElement $blockNode)
    {
        $tree = Tree::getByID($this->topicTreeID);
        $data = $blockNode->addChild('data');
        $data->addChild('mode', $this->mode);
        $data->addChild("title", $this->title);
        $data->addChild('topicAttributeKeyHandle', $this->topicAttributeKeyHandle);
        if (is_object($tree)) {
            $data->addChild('tree', $tree->getTreeName());
        }
        $path = null;
        if ($this->cParentID) {
            $parent = \Page::getByID($this->cParentID);
            $path = '{ccm:export:page:' . $parent->getCollectionPath() . '}';
        }
        $data->addChild('cParentID', $path);
    }

    public function getImportData($blockNode, $page)
    {
        $args = array();
        $treeName = (string) $blockNode->data->tree;
        $page = (string) $blockNode->data->cParentID;
        $tree = Topic::getByName($treeName);
        $args['topicTreeID'] = $tree->getTreeID();
        $args['cParentID'] = 0;
        $args['title'] = (string) $blockNode->data->title;
        $args['mode'] = (string) $blockNode->data->mode;
        if (!$args['mode']) {
            $args['mode'] = 'S';
        }
        $args['topicAttributeKeyHandle'] = (string) $blockNode->data->topicAttributeKeyHandle;
        if ($page) {
            if (preg_match('/\{ccm:export:page:(.*?)\}/i', $page, $matches)) {
                $c = \Page::getByPath($matches[1]);
                $args['externalTarget'] = 1;
                $args['cParentID'] = $c->getCollectionID();
            }
        }

        return $args;
    }

    public function save($data)
    {
        $data += array(
            'externalTarget' => 0,
        );
        $externalTarget = intval($data['externalTarget']);
        if ($externalTarget === 0) {
            $data['cParentID'] = 0;
        } else {
            $data['cParentID'] = intval($data['cParentID']);
        }

        parent::save($data);
    }
}
