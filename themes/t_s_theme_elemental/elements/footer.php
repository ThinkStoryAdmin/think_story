<?php defined('C5_EXECUTE') or die("Access Denied.");

$footerSiteTitle = new GlobalArea('Footer Site Title');
$footerSiteTitleBlocks = $footerSiteTitle->getTotalBlocksInArea();

$footerSocial = new GlobalArea('Footer Social');
$footerSocialBlocks = $footerSocial->getTotalBlocksInArea();

$displayFirstSection = $footerSiteTitleBlocks > 0 || $footerSocialBlocks > 0 || $c->isEditMode();
?>

<footer id="footer-theme" class="footer-all">
    <section>
        <div class="container">
            <div class="row">
                <div class="col-sm-2">
                    <?php
                        $a = new GlobalArea('Footer Legal');
                        $a->display();
                    ?>
                </div>
                <div class="col-sm-5">
                    <?php
                        $a = new GlobalArea('Footer Navigation Primary');
                        $a->display();
                    ?>
                </div>
                <div class="col-sm-5">
                    <?php
                        $a = new GlobalArea('Footer Navigation Secondary');
                        $a->display();
                    ?>
                </div>
            </div>
        </div>
        <div>
            <?php
                $a = new GlobalArea('Souteneurs');
                $a->display();
            ?>
        </div>
    </section>
</footer>

<?php $this->inc('elements/footer_bottom.php');?>
