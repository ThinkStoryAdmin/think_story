<div class="form-inline">
    <!--<label><?=t('Custom Label')?></label>
    <input class="form-control" type="text" value="<?=$customLabel?>" name="<?=$view->field('customLabel')?>">-->

    <legend><?= t('Validated') ?></legend>
    <div class="form-group">
        Validated:
        <select class="form-control" name="<?=$view->field('valid')?>">
            <!--<option value="">Select if Valid or Not</option>-->
            <option value="0" <?php if (isset($valid) && $valid == 0) { ?>selected<?php }  ?>>Not Validated</option>
            <option value="1" <?php if (isset($valid) && $valid == 1) { ?>selected<?php }  ?>>Validated</option>
        </select>
    </div> <br>
    <!--<div> Valid: 
        <?php
            echo $valid;
        ?>
    </div>-->
</div>
