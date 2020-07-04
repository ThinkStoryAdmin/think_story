<?php
//use Concrete\Attribute\ImageFile\Controller AS  $controller
//use Application\Attribute\Timbre\Controller as TimbreController;
use Concrete\Package\ThinkStory\Attribute\Timbre\Controller as TimbreController;


?>

<fieldset>
    <legend>Timbre Settings</legend>
    <label class="control-label" for="file">File</label>
    <?php
    /*
        $al = Core::make('helper/concrete/asset_library');
        echo $al->file('ccm-file-akID-' . $controller->getAttributeKey()->getAttributeKeyID(), $this->field('value'), t('Choose File'), $file);
    */
    /*
    $app = Concrete\Core\Support\Facade\Application::getFacadeApplication();
    //print $html = $app->make('helper/concrete/file_manager');
    $form = \Core::make('helper/form');
    echo $form->file('file');*/


    $al = Core::make('helper/concrete/asset_library');
    $thing = $controller->getAttributeKey();
    if(isset($thing) && $thing){
        echo $al->file('ccm-file-akID-' . $controller->getAttributeKey()->getAttributeKeyID(), $this->field('value'), t('Choose File'), $file);
    }
    ?>

    <?php
    /*
    $htmlFileID = trim(preg_replace('/\W+/', '-', $view->field('value')), '-');
    if ($file === null) {
        ?>
        <input type="file" name="<?= h($view->field('value')) ?>" id="<?= $htmlFileID ?>" />
        <?php
    } else {
        $form = Core::make('helper/form');
        $htmlRadioReplaceID = trim(preg_replace('/\W+/', '-', $view->field('operation')), '-') . '-replace';
        $enableFileCallback = 'document.getElementById(' . json_encode($htmlFileID) . ').disabled = !document.getElementById(' . json_encode($htmlRadioReplaceID) . ').checked'
        ?>
        <input type="hidden" name="<?= $view->field('previousFile') ?>" value="<?= $file->getFileID() ?>" />
        <div class="radio">
            <label>
                <?= $form->radio($view->field('operation'), 'keep', true, ['onchange' => h($enableFileCallback)]) ?>
                <?= t('Keep existing file (%s)', h($file->getFileName())) ?>
            </label>
        </div>
        <div class="radio">
            <label>
                <?= $form->radio($view->field('operation'), 'remove', false, ['onchange' => h($enableFileCallback)]) ?>
                <?= t('Remove current file') ?>
            </label>
        </div>
        <div class="radio">
            <label>
                <?= $form->radio($view->field('operation'), 'replace', false, ['id' => $htmlRadioReplaceID, 'onchange' => h($enableFileCallback)]) ?>
                <?= t('Replace with') ?>
                <input type="file" name="<?= h($view->field('value')) ?>" id="<?= $htmlFileID ?>" disabled="disabled" />
            </label>
        </div>
        <?php
    }
    */?>

<script>
    (function() {
        var hook = window.addEventListener ?
            function (node, eventName, callback) { node.addEventListener(eventName, callback, false); } :
            function (node, eventName, callback) { node.attachEvent('on' + eventName, callback); }
        ;

        function initialize() {
        	var fileElement = document.getElementById(<?= json_encode($htmlFileID) ?>);
            if (!fileElement) {
                return false;
            }
            for (var element = fileElement; element && element != document.body; element = element.parentNode || element.parentElement) {
                if (typeof element.nodeName === 'string' && element.nodeName.toLowerCase() === 'form') {
                    if (typeof element.enctype !== 'string' || element.enctype === '' || element.enctype.toLowerCase() === 'application/x-www-form-urlencoded') {
                        element.enctype = 'multipart/form-data';
                    }
                    break;
                }
            }
        }
        if (!initialize()) {
        	hook(window, 'load', initialize);
        }
    })();
    </script>
</fieldset>
