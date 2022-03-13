<?php
defined('C5_EXECUTE') or die("Access Denied.");

$this->inc('elements/header.php');
?>

<main>
    <div>
        <?php
            $a = new Area('Carousel');
            $a->display();
        ?>
    </div>
    <div class="container">
        <div class="row">
            <div class="col-sm-4 col-xs-6" style="border-left: 1px dotted green; border-right: 1px dotted green; height:100%; overflow:auto;">
                <?php
                $a = new Area('Sujet 1');
                $a->display();
                ?>
            </div>
            <div class= "col-sm-4 col-xs-12" style="border-right: 1px dotted green; height:100%; overflow:auto;">
                <?php
                $a = new Area('Sujet 2');
                $a->display();
                ?>
            </div>
            <div class= "col-sm-4 col-xs-12" style="border-right: 1px dotted green; height:100%; overflow:auto;">
                <?php
                $a = new Area('Sujet 3');
                $a->display();
                ?>
            </div>
        </div>
    </div>
</main>

<?php
$this->inc('elements/footer.php');
