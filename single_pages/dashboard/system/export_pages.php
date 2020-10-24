<?php defined('C5_EXECUTE') or die("Access Denied."); ?>
<script>
    $(function(){
        $("#btn-ts-expp").click(function(event){
            event.preventDefault(); //prevent default action 
            var request_method = "POST"
            var form_data = {
                'data': $.trim($("#yelp").val())
            }
            var url = "<?php echo $controller->action('action_exportPages')?>"

            console.log("Data: ")
            console.log(form_data)
            console.log("URL : " + url)
            $.ajax({
                url: url ,
                type: request_method,
                data : form_data
            }).done(function(response){ //
                console.log(response)
                ConcreteAlert.notify({
                    title: <?php echo json_encode(t('Pages Exported Successfully')); ?>,
                    message: <?php echo json_encode(t("")); ?>
                });
            }).error(function(error){
                console.log(error)
                ConcreteAlert.error({
                    title: <?php echo json_encode(t('Pages Failed to Export')); ?>,
                    message: <?php echo json_encode(t('Could not export the pages. Check the logs on this page!')); ?>
                });
            });
        });
    });
</script>
<div>
    <div>
        <label><?= t('Be sure to increase the PHP script execution time if you have many pages to export!')?></label><br>
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
    <div class="ccm-dashboard-form-actions">
        <button id="btn-ts-expp" class="pull-right btn btn-success" type="submit" ><?=t('Export Pages')?></button>
    </div>
</div>