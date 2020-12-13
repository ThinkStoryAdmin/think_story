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

//Import all custom code namespaces
use ThinkStory\REST\RouteList;
use ThinkStory\REST\Timbre;

defined('C5_EXECUTE') or die('Access Denied.');
//require_once __DIR__ . '/vendor/autoload.php'; belongs in on_start()
//Look at C5 Documentation : https://documentation.concrete5.org/developers/packages/overview
//And : https://github.com/cryophallion/C5-BoilerplatePackageController/blob/master/packageName/controller.php

class Controller extends Package
{
    protected $pkgHandle = 'think_story';
    protected $appVersionRequired = '8.0'; //SHOULD BE ABOVE 8, otherwise the attribute autoload stuff won't work!!!
    protected $pkgVersion = '1.0.3.7';
    //protected $pkgAllowsFullContentSwap = true;   //CONSISTENTLY causes errors, don't bother using

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
        $pkg = parent::install();
        $r = \Request::getInstance();

        SinglePage::add('/dashboard/system/think_story', $pkg);
        SinglePage::add('/dashboard/system/think_story/page_report', $pkg);
        SinglePage::add('/dashboard/system/think_story/data_importer', $pkg);
        SinglePage::add('/dashboard/system/think_story/add_pages_multilingual', $pkg);
        SinglePage::add('/dashboard/system/think_story/export_pages', $pkg);
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
        
        /** NOTE: Installing the page type controller cannot be directly done
         * Page types are installed here, and once the page type is linked to the package, 
         * then Concrete5 will look into the package directory for the controller
         */
        //Install sample content
        if ($r->request->get('installSampleContent')) {
            $this->installContentFile('/install/content.xml');
        } else {
            //Install the base content (express objects & attributes) needed for the package to work
            $this->installContentFile('/install/export-beutify-cleaned.xml');
        }
    }

    public function upgrade()
    {
        parent::upgrade();
        $pkg = Package::getByHandle('think_story');
        
        SinglePage::add('/dashboard/system/think_story', $pkg);
        SinglePage::add('/dashboard/system/think_story/page_report', $pkg);
        SinglePage::add('/dashboard/system/think_story/data_importer', $pkg);
        SinglePage::add('/dashboard/system/think_story/add_pages_multilingual', $pkg);
        SinglePage::add('/dashboard/system/think_story/export_pages', $pkg);
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