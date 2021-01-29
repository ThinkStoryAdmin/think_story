<?php
namespace Concrete\Package\ThinkStory;

use Concrete\Core\Package\Package;
use Concrete\Core\Block\BlockType\BlockType;
use BlockTypeSet;
use PageTheme;

use View;
use Loader;
use Log;
use Concrete\Core\Backup\ContentImporter;
use \Concrete\Core\Page\Template;
use \Concrete\Core\Page\Feed;
use \Concrete\Core\Page\Type\Type;
use \Concrete\Core\Tree\Type\Topic;
use \Concrete\Core\Attribute\Key\CollectionKey as CollectionAttributeKey;
use \Concrete\Core\Page\Single as SinglePage;

use Concrete\Core\Attribute\TypeFactory;
use Concrete\Core\Application\Application;

use PageType;
use PageTemplate;
use \Concrete\Core\Page\Type\PublishTarget\Type\Type as PublishTargetType;

use Express;    //For installing sample Topic Colors, as using CIF doesn't seem to work
use \Concrete\Core\Tree\Type\Topic as TopicTree; //for express stuff, to find topic tree
use \Concrete\Core\Tree\Node\Node as TreeNode;
use \Concrete\Core\Tree\Node\Type\Topic as TopicTreeNode;

//Import all custom code namespaces
use ThinkStory\REST\RouteList;
use ThinkStory\REST\Timbre;

defined('C5_EXECUTE') or die('Access Denied.');
//require_once __DIR__ . '/vendor/autoload.php'; belongs in on_start()
//Look at C5 Documentation : https://documentation.concrete5.org/developers/packages/overview
//And : https://github.com/cryophallion/C5-BoilerplatePackageController/blob/master/packageName/controller.php
ini_set("memory_limit","256M");
class Controller extends Package
{
    protected $pkgHandle = 'think_story';
    protected $appVersionRequired = '8.0'; //SHOULD BE ABOVE 8, otherwise the attribute autoload stuff won't work!!!
    protected $pkgVersion = '1.0.3.7';
    protected $pkgAllowsFullContentSwap = true;   //CONSISTENTLY causes errors, don't bother using

    //Importing Custom Code namespaces with PSR-4 autoloader (to include REST routes & Timbre class for Timbre attribute type)
    protected $pkgAutoloaderRegistries = [
        'src/ThinkStory' => 'ThinkStory\\'
    ];

    protected $packageDependencies = [
        'customizable_twitter_feed'=>true
    ];

    public function getPackageDescription()
    {
        return t("Un package qui permet la création rapide d'un site de Story Telling.");
    }

    public function getPackageName()
    {
        return t('Think Story');
    }

    //https://documentation.concrete5.org/developers/packages/installation/package-dependencies
    public function getPackageDependencies()
    {
        return [
            //'customizable_twitter_feed' => '1.0.4'
        ];
    }

    public function on_start(){
        $this->setupAutoloader();   //Load necessary Composer packages
        ini_set("memory_limit","256M");
        /*  Router for API Extensions, look for src/ThinkStory/REST/RouteList.php in this package...
        $router = $this->app->make('router');
        $list = new RouteList();
        $list->loadRoutes($router);
        Route::register('/stuff/addPages', '\Concrete\Package\ThinkStory\Src\ThinkStory\REST\AddPages::getCartSummary');
        */
    }

    private function setupAutoloader()
    {
        if (file_exists($this->getPackagePath() . '/vendor')) {
            require_once $this->getPackagePath() . '/vendor/autoload.php';
        }
    }

    public function install()
    {
        $r = \Request::getInstance();

        //Do initial checks (https://documentation.concrete5.org/developers/packages/installation/overview)
        /*if( ! in_array($r->request->get('installContentLevel'), ["none", "basic", "full"]) ) {
            throw new Exception(t('You must select which level of sample content you want to install!'));
        }*/

        //If there are no errors, can now install!        
        $pkg = parent::install();
        
        SinglePage::add('/dashboard/system/think_story', $pkg);
        SinglePage::add('/dashboard/system/think_story/page_report', $pkg);
        SinglePage::add('/dashboard/system/think_story/data_importer', $pkg);
        SinglePage::add('/dashboard/system/think_story/add_pages_multilingual', $pkg);
        //SinglePage::add('/dashboard/system/think_story/export_pages', $pkg);
        SinglePage::add('/dashboard/system/think_story/add_translation_text', $pkg);

        //Install Attribute Types
        $factory = $this->app->make('Concrete\Core\Attribute\TypeFactory');
        $type = $factory->getByHandle('t_s_color');
        if (!is_object($type)) {
            $type = $factory->add('t_s_color', 'TS Color', $pkg);

            $service = $this->app->make('Concrete\Core\Attribute\Category\CategoryService');
            $category = $service->getByHandle('collection')->getController();
            $category->associateAttributeKeyType($type);
        }

        $factoryTimbre = $this->app->make('Concrete\Core\Attribute\TypeFactory');
        $typeTimbre = $factoryTimbre->getByHandle('timbre');
        if (!is_object($typeTimbre)) {
            $typeTimbre = $factoryTimbre->add('timbre', 'Timbre de Validation', $pkg);

            $serviceTimbre = $this->app->make('Concrete\Core\Attribute\Category\CategoryService');
            $categoryTimbre = $serviceTimbre->getByHandle('collection')->getController();
            $categoryTimbre->associateAttributeKeyType($typeTimbre);
        }

        //Add Block Set 
        if (!BlockTypeSet::getByHandle('think_story')) {
            BlockTypeSet::add('think_story', 'Think Story', $pkg);
        }
        //Add Block Types, ignored block(s) : t_s_columns
        $this->addBlockType('t_s_topic_list', $pkg);
        $this->addBlockType('t_s_page_list2', $pkg);
        $this->addBlockType('t_s_print_page_to_pdf', $pkg);
        $this->addBlockType('t_s_page_slider', $pkg);
        $this->addBlockType('t_s_back_button', $pkg);
        $this->addBlockType('t_s_page_theme_display', $pkg);
        $this->addBlockType('t_s_page_list_result', $pkg);

        //Add theme
        PageTheme::add('t_s_theme_elemental', $pkg);    //Theme::add('urbanic', $pkg); ???

        ///Add Express Topic Color & Sample Objects
        //https://documentation.concrete5.org/developers/express/programmatically-creating-express-objects
        //https://documentation.concrete5.org/developers/express/creating-reading-searching-updating-and-deleting-express-entries
        //Need to first add topic tree
        //Adding topic trees taken from concrete/controllers/single_page/system/attributes/topics/add.php
        $tree = TopicTree::add('Subjects');

        $topicTree = TopicTree::getByName('Subjects');
        $topicCategory = TreeNode::getByID($topicTree->getRootTreeNodeObject()->treeNodeID);

        $topic1 = TopicTreeNode::add('HR Management', $topicCategory);
        $topic2 = TopicTreeNode::add('Professional emails', $topicCategory);
        $topic3 = TopicTreeNode::add('Biometrics', $topicCategory);
        $topic4 = TopicTreeNode::add('Data Access', $topicCategory);
        
        /*TopicTree::create(TopicTreeNode::add('Subjects', $topicCategory));

        $topicTree = TopicTree::getByName('Subjects');
        $topicCategory = TreeNode::getByID($topicTree->getRootTreeNodeObject()->treeNodeID);*/
        
        //Create object
        $settings = new \Concrete\Core\Entity\Attribute\Key\Settings\TopicsSettings();
        $settings->setAllowMultipleValues(true);
        $settings->setTopicTreeID($topicTree->getRootTreeNodeObject()->treeNodeID);

        $object = Express::buildObject('tstopiccolor', 'tstopiccolors', 'Topic Color', $pkg);
        $object->addAttribute('topics', 'Topic', 'ts_topic_color_topic', $settings);
        $object->addAttribute('t_s_color', 'Color', 'ts_topic_color_color');
        $objectEntity = $object->save();

        //Create object form
        $form = $object->buildForm('Form');
        $form->addFieldset('Basics')
            ->addAttributeKeyControl('ts_topic_color_topic')
            ->addAttributeKeyControl('ts_topic_color_color');
        $form = $form->save();

        //May not be needed, see very bottom of https://documentation.concrete5.org/developers/express/programmatically-creating-express-objects
        $entityManager = $object->getEntityManager();
        $objectEntity->setDefaultViewForm($form);
        $objectEntity->setDefaultEditForm($form);
        $entityManager->persist($objectEntity);
        $entityManager->flush();
        

        //Create entities
        $entry1 = Express::buildEntry('tstopiccolor')
        ->setTsTopicColorTopic(TopicTreeNode::getNodeByName('HR Management'))
        ->setTsTopicColorColor('#1d96b4')
        ->save();
        $entry2 = Express::buildEntry('tstopiccolor')
        ->setTsTopicColorTopic(TopicTreeNode::getNodeByName('Professional emails'))
        ->setTsTopicColorColor('#e1e03c')
        ->save();
        $entry3 = Express::buildEntry('tstopiccolor')
        ->setTsTopicColorTopic(TopicTreeNode::getNodeByName('Biometrics'))
        ->setTsTopicColorColor('#beccd0')
        ->save();
        

        ///Do same for My Scenario form?
        
        /** NOTE: Installing the page type controller cannot be directly done
         * Page types are installed here, and once the page type is linked to the package, 
         * then Concrete5 will look into the package directory for the controller
         */
        //Install sample content
        /*if ($r->request->get('installSampleContent')) {
            $this->installContentFile('/install/content.xml');
        } else {
            //Install the base content (express objects & attributes) needed for the package to work
            //$this->installContentFile('/install/export-beutify-cleaned.xml'); //still need to install topic trees! there are dependents!
            $this->installContentFile('/install/content.xml');
        }*/
        /*switch($r->request->get('installContentLevel')) {
            case "none":
            case "basic":
            case "full":
            default:
                throw new Exception(t('You must set DB_TYPE to mysqlt in site.php.'));
        }*/
    }

    public function upgrade()
    {
        parent::upgrade();
        $pkg = Package::getByHandle('think_story');
        
        SinglePage::add('/dashboard/system/think_story', $pkg);
        SinglePage::add('/dashboard/system/think_story/page_report', $pkg);
        SinglePage::add('/dashboard/system/think_story/data_importer', $pkg);
        SinglePage::add('/dashboard/system/think_story/add_pages_multilingual', $pkg);
        //SinglePage::add('/dashboard/system/think_story/export_pages', $pkg);
        SinglePage::add('/dashboard/system/think_story/add_translation_text', $pkg);
    }

    public function uninstall()
    {
        parent::uninstall();

        $r = \Request::getInstance();
        if ($r->request->get('removeTables')) {
            /*$db = \Database::connection();
            $db->query('drop table btTSTopicList');
            $db->query('drop table btTSPageList2');
            $db->query('drop table btTSPrintPageToPdf');*/
        }

        if ($r->request->get('removeTopics')) {
            if (is_object(Topic:: getByName('Themes'))){
                $deleteTopic = Topic::getByName('Themes');
                $deleteTopic->delete();
            }
            if (is_object(Topic:: getByName('Metiers'))){
                $deleteTopic = Topic::getByName('Metiers');
                $deleteTopic->delete();
            }
            if (is_object(Topic:: getByName('Donnees'))){
                $deleteTopic = Topic::getByName('Donnees');
                $deleteTopic->delete();
            }
        }

        if (BlockTypeSet::getByHandle('think_story')) {
            $set = BlockTypeSet::getByHandle('think_story');
            $set->delete();
        }
    }

    protected function addBlockType($handle, $pkg)
    {
        $bt = BlockType::getByHandle($handle);
        if (!is_object($bt)) {
            $bt = BlockType::installBlockType($handle, $pkg);
        }

        return $bt;
    }

    public function getPassThruActionAndParameters($parameters)
    {

    }
}