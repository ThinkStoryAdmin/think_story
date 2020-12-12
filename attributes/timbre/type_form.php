<?php
//use Concrete\Attribute\ImageFile\Controller AS  $controller
//use Application\Attribute\Timbre\Controller as TimbreController;
use Concrete\Package\ThinkStory\Attribute\Timbre\Controller as TimbreController;
?>

<fieldset>
    <legend>Timbre Settings</legend>
    <label class="control-label" for="file">File</label>
    <?php
        $al = Core::make('helper/concrete/asset_library');
        $thing = $controller->getAttributeKey();
        if(isset($thing) && $thing){
            echo $al->file('ccm-file-akID-' . $controller->getAttributeKey()->getAttributeKeyID(), $this->field('value'), t('Choose File'), $file);
        }
    ?>

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
