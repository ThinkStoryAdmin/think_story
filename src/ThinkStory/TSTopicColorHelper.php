<?php
namespace ThinkStory;
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

/**Small class that handles working with the Topic -> Color relation in code */
class TSTopicColorHelper {
    public static function getPageTopics($page){   //TODO check if this works for heavily nested topic trees
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

    public static function getThemeID($theme){
        if(is_array($theme)){ 
            /*foreach($theme AS $themeItem){
                if($themeItem){         $themename = $themeItem->getTreeNodeName(); break;          }           }*/
            return $theme[0]->getTreeNodeID();
        } else {
            return $theme->getTreeNodeID();
        }
    }

    public static function getThemeName($theme){
        if(is_array($theme)){ 
            return $theme[0]->getTreeNodeName();
        }else{
            return $theme->getTreeNodeName();
        }
    }
}