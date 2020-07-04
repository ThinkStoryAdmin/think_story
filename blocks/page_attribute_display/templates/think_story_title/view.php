<?php

defined('C5_EXECUTE') or die('Access Denied.');

?>

<!--<div><?= $controller->attributeHandle?></div>
<div><?= $controller->attributeTitleText?></div>
<div><?= $controller->getPlaceHolderText($controller->attributeHandle)?></div>-->

<div class="cts-pad-tmpl-title-content">
<?php
    echo $controller->getOpenTag();
    echo $controller->getContent();
    echo $controller->getCloseTag();
?>
</div>
