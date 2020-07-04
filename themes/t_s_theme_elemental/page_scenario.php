<?php
defined('C5_EXECUTE') or die("Access Denied.");

$this->inc('elements/header.php');

/* TODO : put this as a dashboard page?
NOPE! This functionality is actually available by going to : 
    "Stacks & Blocks" -> "Block Types" -> click on the block -> under Usage Count on Active pages, click on number hyperlink
    Then can delete block type on pages you want

    
// Method to delete blocks on pages of certain types
$c = Page::getCurrentPage();
echo $c->getCollectionName();
$blocks = $c->getBlocks();
if(is_array($blocks)){
    foreach($blocks AS $block){
        $blockType = $block->getBlockTypeHandle();
        echo $blockType;
        if($blockType == 't_s_print_page_to_pdf'){
            //$c->removeBlock($block);
            $block->deleteBlock();
        }
        
    }
    echo "guy";
} else {
    echo "y do dis";
}*/
?>

<main>
    <div style="padding-bottom:15px;">
        <?php
        $a = new GlobalArea('Topic Filter');
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
                        $a = new GlobalArea('Nav Next and Back');
                        $a->display();
                        ?>
                    </div>
                    <div class= "col-sm-3 col-xs-12">
                        <?php
                        $a = new GlobalArea('Back Button');
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
            <!--<div class="cts-theme-pscen-attr-1 cts-text-breaker">
                <?php
                //$a = new GlobalArea('IDPRC');
                //$a->display();
                ?>
            </div>
            <div class="cts-theme-pscen-attr-1 cts-text-breaker">
                <?php
                //$a = new Area('Introduction');
                //$a->display();
                ?>
            </div>
            <div class="cts-theme-pscen-attr-1 cts-text-breaker">
                <?php
                //$a = new Area('Declencheur');
                //$a->display();
                ?>
            </div>
            <div class="cts-theme-pscen-attr-1 cts-text-breaker">
                <?php
                //$a = new Area('Peripetie');
                //$a->display();
                ?>
            </div>
            <div class="cts-theme-pscen-attr-1 cts-text-breaker">
                <?php
                //$a = new Area('Resolution');
                //$a->display();
                ?>
            </div>
            <div class="cts-theme-pscen-attr-1 cts-text-breaker">
                <?php
                //$a = new Area('Conclusion');
                //$a->display();
                ?>
            </div>
            <div class="cts-theme-pscen-attr-1 cts-text-breaker">
                <?php
                //$a = new Area('Timbre');
                //$a->display();
                ?>
            </div>-->
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
                <!--<div class="col-sm-4 col-xs-6 cts-theme-pscen-attr-2 cts-theme-pscen-attr-3 cts-text-breaker">
                    <?php
                    //$a = new GlobalArea('Recommendations');
                    //$a->display();
                    ?>
                </div>
                <div class= "col-sm-4 col-xs-12 cts-theme-pscen-attr-2 cts-text-breaker">
                    <?php
                    //$a = new GlobalArea('Principles de base');
                    //$a->display();
                    ?>
                </div>
                <div class= "col-sm-4 col-xs-12 cts-theme-pscen-attr-2 cts-text-breaker">
                    <?php
                    //$a = new GlobalArea('Ressources');
                    //$a->display();
                    ?>
                </div>-->
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
