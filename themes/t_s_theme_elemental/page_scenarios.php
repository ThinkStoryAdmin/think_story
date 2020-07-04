<?php
defined('C5_EXECUTE') or die("Access Denied.");

$this->inc('elements/header.php');
?>

<main>
    <div>
    <?php
    $a = new GlobalArea('Main');
    $a->display($c);
    ?>
    </div>
</main>

<?php
$this->inc('elements/footer.php');
