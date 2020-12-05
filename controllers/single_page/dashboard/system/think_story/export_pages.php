<?php
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

use Concrete\Package\ThinkStory\AttributeValidator\TSAttributeValidator;

defined('C5_EXECUTE') or die("Access Denied.");

class ExportPages extends DashboardSitePageController
{
    public $helpers = array('form');

    public function view()
    {
        
    }

    public function action_exportPages(){
        
    }
}
