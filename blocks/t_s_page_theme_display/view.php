<script>
    console.log("REPORTING FROM PTDISPL")
    let actionURL = '<?php echo $this->action('gettopcols') ?>';
    let bID = <?php echo $bID ?>;
</script>
<div id="page-item-<?php echo $bID ?>-display-color" class="grid-item ts-pl2-header" style="width:100%;height: 100%;background-color:<?php echo $pageTopicColor?>;">
    <div id="page-item-<?php echo $bID ?>-display-theme" class="cts-theme-block-ptheme-display-style" style="padding-left:15px;">
        <?php 
            if(is_null($pageTopic)){
                echo "";
            } else {
                echo $pageTopic->getTreeNodeDisplayName();
            }
        ?>
    </div>
</div>
