<?php
defined('C5_EXECUTE') or die("Access Denied.");

$this->inc('elements/header.php');
?>

<main>
    <?php
    $a = new Area('Main');
    $a->enableGridContainer();
    $a->display($c);

    $b = new Area('Page Footer');
    $b->enableGridContainer();
    $b->display($c);
    ?>
</main>

<?php
$this->inc('elements/footer.php');
