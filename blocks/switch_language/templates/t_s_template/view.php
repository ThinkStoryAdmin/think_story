<?php
defined('C5_EXECUTE') or die('Access Denied.');
?>
<div class="cts-sl-spaced-block-switch-language-flags">
    <?php
    foreach ($languageSections as $key=> $ml) {
        ?>
        <a 
            class="cts-theme-secondary-bar cts-nav-tmpl-secn-vertical-line <?php 
                if ($activeLanguage == $ml->getCollectionID())  {echo "cts-nav-tmpl-secn-nav-selected ";}
                else                                            {echo "cts-nav-tmpl-secn-nav-unselected ";}

                if($key !== count( $languageSections )){
                    echo " cts-vertical-line ";
                }
            ?>"
            href ="<?= $controller->resolve_language_url($cID, $ml->getCollectionID()) ?>" 
            title="<?= $languages[$ml->getCollectionID()] ?>"
            >
                <?php echo strtoupper($ml->getLanguage())?>
        </a>
        <?php
    }
    ?>
</div>