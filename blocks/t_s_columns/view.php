<?php defined('C5_EXECUTE') or die(_("Access Denied.")) ?>

<div>
    <?php echo "Columns area" ?>
    <div class="container">
        <div class="row">
            <div class="col-sm-4 col-xs-6">
                <?php
                $a = new GlobalArea('Header Area 1');
                $a->display();
                ?>
            </div>
            <div class= "col-sm-4 col-xs-12">
                <?php
                $a = new GlobalArea('Header Area 2');
                $a->display();
                ?>
            </div>
            <div class= "col-sm-4 col-xs-12">
                <?php
                $a = new GlobalArea('Header Area 3');
                $a->display();
                ?>
            </div>
        </div>
    <?php 
        /*
        for($x = 0; $x <= $column_count; $x++){
            $a = new Area('Column' + $x + $bID);
            $a->enableGridContainer();
            $a->display($c);
        }*/
    ?>
    </div>
</div>