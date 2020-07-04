<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php View::getInstance()->requireAsset('javascript', 'jquery');

$navItems = $controller->getNavItems();

echo '<div><nav class="cts-nav-tmpl-secn-primary">';
$firstCounter = 0;
foreach ($navItems as $ni) {
    $name = (isset($translate) && $translate == true) ? t($ni->name) : $ni->name;

    $class = 'cts-nav-tmpl-secn-menu-primary cts-nav-tmpl-secn-vertical-line cts-theme-secondary-bar ';

    if($firstCounter == 0){
        $class = 'cts-nav-tmpl-secn-vertical-line-left ' . $class;
        $firstCounter +=1;
    }

    if ($ni->isCurrent){
        $class .= 'cts-nav-tmpl-secn-nav-selected';
    } else {
        $class .= 'cts-nav-tmpl-secn-nav-unselected';
    }
    $nameFixed = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name);
    echo '<a href="' . $ni->url . '" target="' . $ni->target . '" class="'. $class .'">' . $nameFixed . '</a>';
}

echo '</nav></div>';
