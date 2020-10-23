<?php
namespace Concrete\Package\ThinkStory\Block\TSPageSlider;

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
use Concrete\Core\Tree\Node\Type\Topic;
use Concrete\Package\TSTest\TSPageList\TSPageList;
use ThinkStory\AttributeValidator\TSAttributeValidator;
use ThinkStory\BlockAttributeTranslator\TSBlockAttributeTranslator AS BAT;

class Controller extends BlockController
{
    protected $btTable = 'btTSPageSlider';
    protected $btInterfaceWidth = 700;
    protected $btInterfaceHeight = 525;
    protected $btExportPageColumns = ['cParentID'];
    protected $btExportPageTypeColumns = ['ptID'];
    protected $btDefaultSet = 'think_story';
    protected $carouselPages;

    public function getBlockTypeDescription()
    {
        return t("Think Story Page Slider.");
    }

    public function getBlockTypeName()
    {
        return t("Think Story Page Slider");
    }

    public function registerViewAssets($outputContent = '')
    {
        $al = \Concrete\Core\Asset\AssetList::getInstance();

        $this->requireAsset('javascript', 'jquery');
        //$this->requireAsset('responsive-slides');
    }

    public function on_start(){
        $this->carouselPages = new PageList();
        $this->carouselPages->disableAutomaticSorting();
        $this->carouselPages->setNameSpace('b' . $this->bID);
        $this->carouselPages->getQueryObject()->setMaxResults($num);
        $this->carouselPages->filterByAttribute('exclude_nav',false);
        if($this->ptID){
            //$this->carouselPages->filterByPageTypeID($ptID);
            $this->carouselPages->filterByPageTypeHandle(PageType::getByID($this->ptID)->getPageTypeHandle());
        }
        
        switch($this->orderBy){
            case 'display_most_recent':
                $this->carouselPages->sortByPublicDate();
                break;
            case 'display_most_popular':
                //$this->tempSort();
                //$list->sortBy('ak_attribute_name', 'ASC|DSC');
                //$this->carouselPages->sortBy('ak_ts_view_count_np','DESC');
                if(TSAttributeValidator::checkCollectionAttributeHandleExists($this->viewCountAttribute)){
                    //Need to add 'ak_' to the start
                    $this->carouselPages->sortBy('ak_'.$this->viewCountAttribute,'DESC');
                } else {
                    $this->carouselPages->sortBy('RAND()');
                }
                break;
            case 'display_random':
                $this->carouselPages->sortBy('RAND()');
                break;
            default:
                $this->carouselPages->sortBy('RAND()');
                break;
        }
        return $this->carouselPages;
    }

    public function add()
    {
        $this->loadKeys();
    }

    public function edit()
    {
        $this->loadKeys();
    }

    protected function loadKeys()
    {
        $attributeKeys = [];
        $keys = CollectionKey::getList();
        foreach ($keys as $ak) {
            $attributeKeys[$ak->getAttributeKeyHandle()] = $ak->getAttributeKeyName();
        }
        $this->set('keyers', $keys);
        $this->set('attributeKeys', $attributeKeys);
        $this->set('chosenTopics', unserialize($this->topics));
    }

    public function view()
    {
        $list = $this->carouselPages;
        $nh = Core::make('helper/navigation');
        $this->set('nh', $nh);

        $pages = $list->getResults();
        $this->set('pages', $pages);
        $this->set('list', $list);
    }
}