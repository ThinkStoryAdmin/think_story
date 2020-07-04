<div id="page-item" class="grid-item ts-pl2-header" style="width:100%;height: 100%;background-color:<?php echo $pageTopicColor?>;">
    <div class="cts-theme-block-ptheme-display-style" style="padding-left:15px;">
        <?php 
            if(is_null($pageTopic)){
                echo "";
            } else {
                echo $pageTopic->getTreeNodeDisplayName();
            }?>
    </div>
    <div >
        <?php
            //echo ($catcolorslist);
        ?>
    </div>
</div>