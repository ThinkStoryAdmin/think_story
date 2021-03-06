<?php defined('C5_EXECUTE') or die("Access Denied."); ?>

<?php View::getInstance()->requireAsset('javascript', 'jquery');

$navItems = $controller->getNavItems();

// Step 2 of 2: Output menu HTML

//echo '<nav class="ccm-responsive-navigation original"><ul>'; //opens the top-level menu
//echo '<nav class="ccm-responsive-navigation original">'; //opens the top-level menu
echo '<div><nav class="cts-nav-tmpl-prim-primary">';
$firstCounter = 0;
foreach ($navItems as $ni) {
    $name = (isset($translate) && $translate == true) ? t($ni->name) : $ni->name;


    //Convert to uppercase, encoding safe
    //mb_internal_encoding('UTF-8');
    /*if(!mb_check_encoding($name, 'UTF-8')
        OR !($name === mb_convert_encoding(mb_convert_encoding($name, 'UTF-32', 'UTF-8' ), 'UTF-8', 'UTF-32'))) {

        $name = mb_convert_encoding($name, 'UTF-8'); 
    }*/

    // LE COURRIER DE SÁINT-HYÁCINTHE
    //$name =  mb_convert_case($name, MB_CASE_UPPER, "UTF-8"); 
    
    $class = 'cts-nav-tmpl-prim-menu-primary cts-theme-primary-bar-line cts-theme-primary-bar ';

    if($firstCounter == 0){
        $class = 'cts-theme-primary-bar-line-left ' . $class;
        $firstCounter +=1;
    }

    if ($ni->isCurrent){
        $class .= 'cts-nav-tmpl-prim-nav-selected';
    } else {
        $class .= 'cts-nav-tmpl-prim-nav-unselected';
    }
    $nameFixed = iconv(mb_detect_encoding($name, mb_detect_order(), true), "UTF-8", $name);
    echo '<a href="' . $ni->url . '" target="' . $ni->target . '" class="'. $class .'">' . $nameFixed . '</a>';
}

echo '</nav></div>';
