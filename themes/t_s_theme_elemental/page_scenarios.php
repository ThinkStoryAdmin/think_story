<?php
defined('C5_EXECUTE') or die("Access Denied.");

$this->inc('elements/header.php');
?>

<main>
    <!-- !!! MOVE PAGE LIST / RESULT BLOCKS OUT OF THE GlobalArea AND INTO THE Area !!! -->
    <div>
        <?php
            $a = new GlobalArea('Main');
            $a->display($c);
        ?>
    </div>
    <div>
        <?php
            $a = new Area('Main');
            $a->display($c);
        ?>
    </div>
</main>

<?php
$this->inc('elements/footer.php');
