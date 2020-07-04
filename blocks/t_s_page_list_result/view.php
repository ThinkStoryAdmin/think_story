<?php defined('C5_EXECUTE') or die(_("Access Denied.")) ?>
<?php
    if($c->isEditMode()){
        ?>
            <div> EDIT MODE</div>
        <?php
    } else {
        ?>
            <div class='container'>
            <!-- loading icon css taken from https://loading.io/css/ 
                <div id='loader' style='loader'>
                    <div class="ts-pl2-lds-ring">
                        <div></div></div>
                    </div>
                
                </div>-->
                <div id='loader' style='loader'>
                    <div class="ts-pl2-lds-ring">
                        <div></div>
                    </div>
                </div>
                <div id="tspages" class="ts-pl2-grid-container">
            </div>
        <?php
    }
?>
