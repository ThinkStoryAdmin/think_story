<?php defined('C5_EXECUTE') or die(_("Access Denied.")) ?>
<link href='https://fonts.googleapis.com/css?family=Aguafina Script' rel='stylesheet'>
<script>
    var speed = <?= $speed ?>;
</script>
<div>
    <!--<img src='<?php
        $urlHelper = Core::make('helper/concrete/urls');
        $blockType = BlockType::getByHandle('t_s_page_slider');
        $localPath = $urlHelper->getBlockTypeAssetsURL($blockType);
        echo $localPath . '/coma_1.gif';
    ?>'>-->
    <?php
    if(is_array($BAT)){
        print_r($BAT);
    } else {
        echo $BAT;
    }
    ?>
    <div class='cts-theme-pslider'>
        <div class="ts-image-slider-container ts-image-slider-container-slider">
            <div class="ts-image-slider-row">
                <div class="ts-image-slider-column ts-image-slider-left">
                    <div class="ts-image-slider-column-left">
                        <?php 
                            foreach($pages as $page){
                                ?>
                                <!--Removed classes: , -->
                                <a class="ts-image-slider-mySlides ts-image-slider-page-slide ts-image-slider-child ts-image-slider-padding ts-image-slider-normal cts-theme-pslider-style" href="<?php echo $page->getCollectionLink()?>">
                                    <?php echo $page->getCollectionName();?>
                                </a>
                                <?php
                            }
                        ?>
                    </div>
                </div>
                <div class="ts-image-slider-column ts-image-slider-right">
                    <div class="ts-image-slider-column-right">
                        <div class="ts-image-slider-buttons">
                            <p class="ts-image-slider-circle cts-theme-radial-bckgrnd" onclick="plusDivs(-1)">❮</p>
                            <p class="ts-image-slider-circle cts-theme-radial-bckgrnd" onclick="plusDivs(1)">❯</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>