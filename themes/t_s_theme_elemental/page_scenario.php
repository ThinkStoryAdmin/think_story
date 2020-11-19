<?php
    defined('C5_EXECUTE') or die("Access Denied.");
    $this->inc('elements/header.php');
?>

<main>
    <div style="padding-bottom:15px;">
        <?php
        //TODO use GlobalArea (as this is used in a Page Template, the functionality WILL need to be different based on language (as we can only define func in one page))
        $a = new Area('Topic Filter');
        $a->display($c);
        ?>
    </div>
    <div>
        <div >
            <div class="container cts-theme-tertiary-bar-background" style ="margin-bottom:15px;">
                <div class="row" >
                    <div class="col-sm-3 col-xs-6" style="padding:0;">
                        <?php
                        $a = new Area('Theme Print');
                        $a->display();
                        ?>
                    </div>
                    <div class= "col-sm-4 col-xs-12">
                        <?php
                        $a = new Area('Nav Next and Back');
                        $a->display();
                        ?>
                    </div>
                    <div class= "col-sm-3 col-xs-12">
                        <?php
                        $a = new Area('Back Button');
                        $a->display();
                        ?>
                    </div>
                    <div class= "col-sm-2 col-xs-12">
                        <?php
                        $b = new Area('Print to PDF');
                        $b->display();
                        ?>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="container">
            <div class="cts-theme-pscen-attr-1 cts-text-breaker">
                <?php
                $a = new Area('Title');
                $a->display();
                ?>
            </div>
            <div class="cts-theme-pscen-attr-1 cts-text-breaker">
                <?php
                $a = new Area('IDPRC Content');
                $a->display();
                ?>
            </div>
        <div class="container">
            <div class="row">
                <div class="col-sm-4 col-xs-6 cts-theme-pscen-attr-2 cts-theme-pscen-attr-3 cts-text-breaker">
                    <?php
                    $a = new Area('Recommendations');
                    $a->display();
                    ?>
                </div>
                <div class= "col-sm-4 col-xs-12 cts-theme-pscen-attr-2 cts-text-breaker">
                    <?php
                    $a = new Area('Principes de base');
                    $a->display();
                    ?>
                </div>
                <div class= "col-sm-4 col-xs-12 cts-theme-pscen-attr-2 cts-text-breaker">
                    <?php
                    $a = new Area('Ressources');
                    $a->display();
                    ?>
                </div>
            </div>
        </div>
    </div>
    <div>
        <?php
            $a = new Area('Filter Result');
            $a->display($c);
        ?>
    </div>
</main>

<?php
$this->inc('elements/footer.php');
