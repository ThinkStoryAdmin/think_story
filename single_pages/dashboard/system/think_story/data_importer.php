<?php
defined('C5_EXECUTE') or die("Access Denied.");

$nav = Loader::helper('navigation');


?>
    <div>
        <label for="yelp"><?= t('Page Data')?></label><br>
        <textarea id="yelp" name="pageData" form="yep" rows="10" cols="100">
            
        </textarea><br>
        <!--<button id="qwop" class="btn btn-primary pull-right"><?= t('Create Pages') ?></button>-->
    </div>
    <br>
    <div id="server-results-detailed">
        <label for="yelp"><?= t('Errors')?></label><br>
        <textarea id="res" name="errors" rows="10" cols="150">
        
        </textarea><br>
    </div>
    <div class="ccm-dashboard-form-actions-wrapper">
        <div class="ccm-dashboard-form-actions">
            <button id="qwop" class="pull-right btn btn-success" type="submit" ><?=t('Import Data')?></button>
        </div>
    </div>

    <script>
    //Script to run page addition
    $(function(){
        //$("#yep").submit(function(event){
        $("#qwop").click(function(event){
            //$("#server-results").text("HELP");
            event.preventDefault(); //prevent default action 
            //var request_method = $(this).attr("method"); //get form GET/POST method
            var request_method = "POST"
            //var form_data = $(yelp).serialize(); //Encode form elements for submission
            var form_data = {
                'data': $.trim($("#yelp").val())
            }
            var url = "<?php echo $controller->action('action_createPages')?>"

            console.log("Data: ")
            console.log(form_data)
            console.log("URL : " + url)
            $.ajax({
                //url : "<?php echo $this->action('createPages') ?>",
                url: url ,
                type: request_method,
                data : form_data
            }).done(function(response){ //
                console.log(response)
                $("#server-results").text("WORKED");
                $("#res").text("No errors");
            }).error(function(error){
                console.log(error)
                $("#server-results").text("ERROR");
                $("#res").text(error.responseText);
            });
        });
    });
    </script>