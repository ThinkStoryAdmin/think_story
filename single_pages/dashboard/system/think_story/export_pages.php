<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<script>
    function toggleFormElements(bDisabled) { 
        var inputs = document.getElementsByTagName("input"); 
        for (var i = 0; i < inputs.length; i++) { 
            inputs[i].disabled = bDisabled;
        } 
        var selects = document.getElementsByTagName("select");
        for (var i = 0; i < selects.length; i++) {
            selects[i].disabled = bDisabled;
        }
        var textareas = document.getElementsByTagName("textarea"); 
        for (var i = 0; i < textareas.length; i++) { 
            textareas[i].disabled = bDisabled;
        }
        var buttons = document.getElementsByTagName("button");
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].disabled = bDisabled;
        }
    }

    //https://github.com/HubbleCommand/browser-extension/blob/master/src/background-bookmarks-expimp.js
    //http://www.4codev.com/javascript/download-save-json-content-to-local-file-in-javascript-idpx473668115863369846.html
    function download(content, fileName, contentType) {
        const a = document.createElement("a");
        const file = new Blob([content], { type: contentType });
        a.href = URL.createObjectURL(file);
        a.download = fileName;
        a.click();
    }

    submitFormExport(){
        event.preventDefault(); //prevent default action 
        var queryString = $('#TSExportForm').serialize();
        var url = "<?php echo $controller->action('action_exportPages')?>"

        console.log("Data: ")
        console.log(queryString)
        $.ajax({
            url: url ,
            type: request_method,
            data : form_data
        }).done(function(response){
            console.log(response)
            ConcreteAlert.notify({
                title: <?php echo json_encode(t('Pages Exported Successfully')); ?>,
                message: <?php echo json_encode(t("You should have a popup asking you to download a file.")); ?>
            });

            //Now download results
            download(results, "ExportedPages", "application/json")
        }).error(function(error){
            console.log(error)
            ConcreteAlert.error({
                title: <?php echo json_encode(t('Pages Failed to Export')); ?>,
                message: <?php echo json_encode(t('Could not export the pages. Check the logs on this page!')); ?>
            });
        });
    }
</script>
<div>
    <form id="TSExportForm" onsubmit="submitFormExport()">
        <label><?= t('Be sure to increase the PHP script execution time if you have many pages to export!')?></label><br>
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
    </form>
    <div class="ccm-dashboard-form-actions">
        <button id="btn-ts-expp" class="pull-right btn btn-success" type="submit" ><?=t('Export Pages')?></button>
    </div>
</div>