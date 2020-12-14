<?php
    defined('C5_EXECUTE') or die("Access Denied.");
?>
<script>
    function enableButtons(){
        $('#submit-sub').attr('disabled', false);
        $('#submit-add').attr('disabled', false);
    }

    function disableButtons(){
        $('#submit-sub').attr('disabled', true);
        $('#submit-add').attr('disabled', true);
    }

    $(function(){
        $("#submit-add").click(function(event){
            disableButtons();
            event.preventDefault(); //prevent default action
            var form_data = {
                'data': $.trim($("#text-to-translate").val())
            }

            $.ajax({
                url: "<?php echo $controller->action('action_AddTextToTranslate')?>",
                type: "POST",
                data : form_data
            }).done(function(response){ //
                enableButtons();
                console.log(response)
                ConcreteAlert.notify({
                    title: <?php echo json_encode(t('Added text to translate')); ?>,
                    message: <?php echo json_encode(t("You can now translate in Dashboard/System/Multilingual/Translate Site Interface.")); ?>
                });
            }).error(function(error){
                enableButtons();
                console.log(error)
                ConcreteAlert.error({
                    title: <?php echo json_encode(t('Could not add text to translate!')); ?>,
                    message: <?php echo json_encode(t('Check the logs on this page for more details.')); ?>
                });
            });
        });

        $("#submit-sub").click(function(event){
            disableButtons();
            event.preventDefault(); //prevent default action
            var form_data = {
                'data': $.trim($("#text-to-translate").val())
            }
            console.log(form_data)

            $.ajax({
                url: "<?php echo $controller->action('action_RemoveText')?>",
                type: "POST",
                data : form_data
            }).done(function(response){ //
                enableButtons();
                console.log(response)
                ConcreteAlert.notify({
                    title: <?php echo json_encode(t('Removed text from translations')); ?>,
                    message: <?php echo json_encode(t("The text should no longer appear in Dashboard/System/Multilingual/Translate Site Interface.")); ?>
                });
            }).error(function(error){
                enableButtons();
                console.log(error)
                ConcreteAlert.error({
                    title: <?php echo json_encode(t('Could not add text to translate!')); ?>,
                    message: <?php echo json_encode(t('Check the logs on this page for more details.')); ?>
                });
            });
        });
    });
</script>

<div>
    <label for="text-to-translate"><?= t("Text to translate:") ?></label>
    <input type="text" id="text-to-translate" name="text-to-translate"><br><br>

    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button id="submit-sub" class="pull-right btn btn-danger" type="submit" ><?=t('Remove text to translate')?></button>
            <button id="submit-add" class="pull-right btn btn-success" type="submit" ><?=t('Add text to translate')?></button>
        </div>
    </div>
</div>
