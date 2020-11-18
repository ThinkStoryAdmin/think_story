<?php defined('C5_EXECUTE') or die(_("Access Denied.")) ?>
<script>
    let urlForThisPage = '<?php echo $thisUrl?>'
    let urlFilterAction = '<?php echo $this->action('filter') ?>'
    let sendToAnotherPage = '<?= $bPostToAnotherPage?>'
    let sendToAnotherPageID = '<?= $cParentID?>'
    let sendToAnotherPageIDURL = '<?= $cParentIDURL?>'
    let testHelpFixFilt = '<?= $testHelpFixFilt?>'
	console.log("testHelpFixFilt : " + testHelpFixFilt)
    console.log("URL filter action: " + urlFilterAction)
    console.log("URL for this page: " + urlForThisPage)
    console.log('sendToAnotherPage' + sendToAnotherPage)
    console.log('sendToAnotherPageID' + sendToAnotherPageID)
    console.log('sendToAnotherPageIDURL' + sendToAnotherPageIDURL)
    console.log("View.php ready!")
    //All other JS code is in the view.js
</script>
<div>
    <div class="cts-theme-tertiary-bar-background">
        <div class="container">
            <div class="topic_select_parent cts-block-pl2-main ">
                    <?php
                        foreach($topictrees as $topictree){
                            if(!(is_null($topictree))){
                                $node = $topictree->getRootTreeNodeObject();
                                $node->populateChildren();
                                if (is_object($node)) {                    
                                    //in the ID of the select below, need to use getTreeId instead of getTreeName, as if tree name has spaces this causes following issues
                                        //Cannot reset select
                                        //Select params are not registered
                                        //Select changes are not registered
                                    ?>
                                    <select 
                                        class="pagelist2 ts-pl2-topic-dropdown" 
                                        style="width:<?php echo intval(1/count($topictrees) * 100)-10?>%;margin-right:5px;" 
                                        
                                        id="think-story-drop-down3-<?php echo $bID?>-<?php echo $topictree->getTreeId()?>" 
                                        data-action="topic-select2">
                                    <?php 
                                    //Add null for first option
                                    ?><option value=-1><?php echo t('All ') . tc('TreeName', $topictree->getTreeName());?><?php
                    
                                    //Add all topics as options
                                    foreach($node->getChildNodes() as $topic){
                                        ?>
                                        <option
                                            value= <?php echo $topic->getTreeNodeID(); ?> 
                                            <?php if (isset($selectedTopicID) && $selectedTopicID == $topic->getTreeNodeID()){ echo(' selected');}?>
                                        >
                                        <?php 
                                            //echo $topic->getTreeNodeName();
                                            //echo tc('TopicName', '%s', $topic->getTreeNodeName());
                                            echo tc('TopicName', $topic->getTreeNodeName());
                                            ?>
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
                            }
                        }
                    ?>
                    <i class="fa fa-refresh cts-theme-icons" aria-hidden="true" style="padding-right:5px;cursor:pointer;"></i><a id="tsreload" style="cursor:pointer;" class="cts-theme-tertiary-bar"><?= t("Reset") ?></a>
            </div>
        </div>
    </div>
    <!-- <div class='container'>
        loading icon css taken from https://loading.io/css/ 
        <div id='loader' style='loader'>
            <div class="ts-pl2-lds-ring"><div></div></div>
            </div>
        <div id="tspages" class="ts-pl2-grid-container">
        </div>
    </div> -->
</div>