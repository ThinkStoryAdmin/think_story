<?php

defined('C5_EXECUTE') or die('Access Denied.');
$handle = $controller->attributeHandle;
$attributeKey = CollectionAttributeKey::getByHandle($handle);
$title = tc('AttributeKeyName', $attributeKey->getAttributeKeyName());
?>

<div class="cts-pad-tmpl-tb-title"><?= $title?></div>
<div class="cts-pad-tmpl-tb-content">
<?php
echo $controller->getOpenTag();
echo $controller->getContent();
echo $controller->getCloseTag();
?>
</div>
