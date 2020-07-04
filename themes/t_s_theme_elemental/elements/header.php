<?php defined('C5_EXECUTE') or die("Access Denied.");

$this->inc('elements/header_top.php');

?>

<header>
    <div class="container">
        <div class="row">
            <div class="col-sm-6 col-xs-6">
                <?php
                $a = new GlobalArea('Header Site Title');
                $a->display();
                ?>
            </div>
            <div class='col-sm-6 col-xs-6'>
                <div>
                    <?php
                        $a = new GlobalArea('Header Navigation');
                        $a->display();
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div class="cts-primary-nav">
        <div class="container" style='height:50px;'>
            <div class="row">
                <div class= "col-sm-12 col-xs-12" >
                    <?php
                    $a = new GlobalArea('Primary Navigation');
                    $a->display();
                    ?>
                </div>
            </div>
        </div>
    </div>
</header>
