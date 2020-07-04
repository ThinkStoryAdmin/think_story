<?php  defined('C5_EXECUTE') or die("Access Denied."); ?>

<div class="ccm-block-topic-list-wrapper">
    <div class="ccm-block-topic-list-header">
        <h5><?php echo tc('TreeName', $title); ?></h5>
    </div>

    
    <?php
    /*
    if ($mode == 'S' && is_object($tree)) {
        $node = $tree->getRootTreeNodeObject();
        $node->populateChildren();
        if (is_object($node)) {
            foreach($node->getChildNodes() as $topic){
                ?> 
                <label>
                <?php echo $topic->getTreeNodeDisplayName(); echo $topic->getTreeNodeID();?>
                </label>

                <?php
            }
            
            ?>
            <a><?php echo $bID?></a>
            <select id="think-story-drop-down2-<?php echo $bID?>">
            <?php 
            
            foreach($node->getChildNodes() as $topic){
                ?> 
                <option
                    value= <?php echo $topic->getTreeNodeID(); ?> 
                    <?php if (isset($selectedTopicID) && $selectedTopicID == $topic->getTreeNodeID()){ echo(' selected');}?>
                >
                <?php echo $topic->getTreeNodeDisplayName();?>
                </option>
                <?php
            }
            ?>
            </select>

            <?php

            if (!isset($selectedTopicID)) {
                $selectedTopicID = null;
            }
        }
    }*/
    ?>
    <div class="cts-block-tlist-wrapper">
    
    <?php
    if ($mode == 'S' && is_object($tree)) {
        $node = $tree->getRootTreeNodeObject();
        $node->populateChildren();
        if (is_object($node)) {
            if (!isset($selectedTopicID)) {
                $selectedTopicID = null;
            }
            $walk = function ($node) use (&$walk, &$view, $selectedTopicID) {
                ?><ul class="ccm-block-topic-list-list fa-ul"><?php
                foreach ($node->getChildNodes() as $topic) {
                    if ($topic instanceof \Concrete\Core\Tree\Node\Type\Category) {
                        ?><li><?php echo $topic->getTreeNodeDisplayName(); ?>
                        <?php
                    } else {
                        ?><li><i class="fa fa-chevron-right cts-tlist-chevron" aria-hidden="true"></i>
                        <a href="<?php echo $view->controller->getTopicLink($topic); ?>" <?php
                        if (isset($selectedTopicID) && $selectedTopicID == $topic->getTreeNodeID()) {
                            ?> class="cts-block-tlist-selected "<?php
                        }
                        ?>><?php echo $topic->getTreeNodeDisplayName(); ?></a><?php
                    }
                    if (count($topic->getChildNodes())) {
                        $walk($topic);
                    } ?>
                    </li>
                    <?php
                }
                ?></ul><?php
            };
            $walk($node);
        }
    }
    ?>
    </div>
</div>
