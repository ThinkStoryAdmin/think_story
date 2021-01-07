<?php
defined('C5_EXECUTE') or die("Access Denied.");

if (!$previousLinkURL && !$nextLinkURL && !$parentLabel) {
    return false;
}
use ThinkStory\BlockAttributeTranslator\TSBlockAttributeTranslator AS BAT;
?>

<div class="ccm-block-next-previous-wrapper container">
    <div class="row">
        <?php
        //print previous
        if ($previousLinkURL && $previousLabel) {
            ?>
            <i class="fa fa-chevron-left cts-npn-tmpl-icon cts-theme-icons" aria-hidden="true" style="padding-right:5px;"></i>
            <div class="ccm-block-next-previous-header cts-npn-spaced cts-npn-tmpl-prim-vertical-line">
                <a class="cts-theme-tertiary-bar" <?php echo $previousLinkURL ? 'href="' . $previousLinkURL . '"' : '' ?>
                    ><?= t($previousLabel) ?>
                </a>
            </div>
            <?php
        }
        //print next
        if ($nextLinkURL && $nextLabel) {
            ?>
            <div class="ccm-block-next-previous-header cts-npn-spaced">
                <a class="cts-theme-tertiary-bar" <?php echo $nextLinkURL ? 'href="' . $nextLinkURL . '"' : '' ?>
                    > <?= t($nextLabel) ?> 
                </a>
            </div>
            <i class="fa fa-chevron-right cts-npn-tmpl-icon cts-theme-icons" aria-hidden="true" style="padding-right:5px;"></i>
            <?php
        }
        ?>
    </div>
</div>
