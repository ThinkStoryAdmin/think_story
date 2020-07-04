<?php

defined('C5_EXECUTE') or die('Access Denied.');
//$title = t('%s', $controller->getTitle());
$title = t('%s',mb_substr( $controller->getPlaceHolderText($controller->attributeHandle),1,-1));
$handle = $controller->attributeHandle;
$attributeKey = CollectionAttributeKey::getByHandle($handle);
//$title = t('%s', $attributeKey->getAttributeKeyName());
$title = tc('AttributeKeyName', $attributeKey->getAttributeKeyName());
?>
<div class="container cts-pad-tmpl-lb-spaced">
    <!--<p><?= $controller->getTitle()?></p>-->
    <div class="row">
        <div class="col-sm-1 col-xs-1">
            <div class="cts-pad-tmpl-lb-circle cts-theme-radial-bckgrnd cts-theme-block-pad-lb-icon-text-color" data-toggle="tooltip" data-placement="right" title="<?= $title ?>">
                <?php echo strtolower($title[0]) ?>
            </div>
        </div>
        <div class= "col-sm-9 cts-pad-tmpl-tb-idprc">
        <?php 
            echo $controller->getOpenTag();
            echo $controller->getContent();
            echo $controller->getCloseTag();
        ?>
        </div>
    </div>
</div>