<?php defined('C5_EXECUTE') or die("Access Denied.");
$c = Page::getCurrentPage();
?>

<div class="form-group">
    <label class="control-label"><?= t('Label') ?></label>
    <?php
        echo $form->text('label', $label);
    ?>

    <label class="control-label"><?= t('Timbre To Print') ?></label>
    <?php
        echo $form->select('attributeTimbre', $attributesTimbres, $chosenTimbre);
    ?>

    <label class="control-label"><?= t('Topics To Print') ?></label>
    <?php
        echo $form->selectMultiple('attributesTopics', $attributesTopicsK, $chosenTopics);
    ?>

    <label class="control-label"><?= t('Content Attributes to Print') ?></label>
    <?php
        echo $form->selectMultiple('attributesContent', $attributes, $chosenContent, array('style' => 'height: 500px;'));
    ?>

    <label class="control-label"><?= t('PDF footer URL') ?></label>
    <?php
        echo $form->text('attributesFooterURL', $attributesFooterURL);
    ?>

    <label class="control-label"><?= t('PDF header icon') ?></label>
    <?php
        $app = \Concrete\Core\Support\Facade\Application::getFacadeApplication();
        $al = $app->make('helper/concrete/asset_library');
        echo $al->image('attributesHeaderIcon', 'attributesHeaderIcon', t("Choose Image"), $chosenHeaderIcon);
    ?>
</div>