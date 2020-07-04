<?php defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getCurrentPage();
?>

<div class="form-group">
    <label class="control-label" for="num"><?=t('Number of pages')?></label>
    <input type="number" step="1" class="form-control" name="num" value="<?php echo $num?>">
</div>
<div class="form-group">
    <label class="control-label" for="speed"><?=t('Speed')?></label>
    <input type="number" step="1" class="form-control" name="speed" value="<?php echo $speed?>">
</div>
<div class="form-group">
    <label class="control-label"><?=t('Sort')?></label>
    <select name="orderBy" class="form-control">
        <option value="display_most_recent" <?php if ($orderBy == 'display_most_recent') {
            ?> selected <?php
        } ?>>
            <?= t('Most recent') ?>
        </option>
        <option value="display_most_popular" <?php if ($orderBy == 'display_most_popular') {
            ?> selected <?php
        } ?>>
            <?= t('Most popular') ?>
        </option>
        <option value="display_random" <?php if ($orderBy == 'display_random') {
            ?> selected <?php
        } ?>>
            <?= t('Random') ?>
        </option>
    </select>
</div>
<div class="form-group">
    <label class="control-label"><?= t('Page Type') ?></label>
    <?php
    $ctArray = PageType::getList(false, $siteType);

    if (is_array($ctArray)) {
        ?>
        <select class="form-control" name="ptID" id="selectPTID">
            <option value="0">** <?php echo t('All') ?> **</option>
            <?php
            foreach ($ctArray as $ct) {
                ?>
                <option
                    value="<?= $ct->getPageTypeID() ?>" <?php if ($ptID == $ct->getPageTypeID()) {
                    ?> selected <?php
                }
                ?>>
                    <?= $ct->getPageTypeDisplayName() ?>
                </option>
                <?php
            }
            ?>
        </select>
        <?php
    }
    ?>
</div>
<!--<div class="form-group">
    <label class="control-label" for="viewCountAttribute"><?=t('Handle for the Page View Count Page Attribute (needed if you sort by popularity)')?></label>
    <input type="text" class="form-control" name="viewCountAttribute" value="<?php echo $viewCountAttribute?>">
</div>-->
<div class="form-group">
    <label class="control-label"><?= t('Handle for the Page View Count Page Attribute (needed if you sort by popularity)') ?></label>
    <?php
        echo $form->select('viewCountAttribute', $attributeKeys, $viewCountAttribute);
    ?>
</div>
