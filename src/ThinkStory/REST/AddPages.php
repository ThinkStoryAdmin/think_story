<?php
namespace Concrete\Package\ThinkStory\Src\ThinkStory\REST;

use Concrete\Core\Controller\Controller as RouteController;
/*
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Price as StorePrice;
use Concrete\Package\CommunityStore\Src\CommunityStore\Cart\Cart as StoreCart;
use Concrete\Package\CommunityStore\Src\CommunityStore\Tax\Tax as StoreTax;
use Concrete\Package\CommunityStore\Src\CommunityStore\Utilities\Calculator as StoreCalculator;*/

class AddPages extends RouteController
{
    /*
    public function getTaxTotal()
    {
        echo json_encode(StoreTax::getTaxes(true));
    }
    public function getShippingTotal()
    {
        $smID = $_POST['smID'];
        $total = StoreCalculator::getShippingTotal($smID);
        if ($total>0) {
            echo StorePrice::format($total);
        } else {
            echo 0;
        }
    }
    public function getCartSummary() {
        $totals = StoreCalculator::getTotals();
        $itemCount = StoreCart::getTotalItemsInCart();
        $total = $totals['total'];
        $subTotal = $totals['subTotal'];

        $data = array('subTotal'=> StorePrice::format($subTotal), 'total'=>StorePrice::format($total), 'itemCount'=>$itemCount, 'totalCents'=> $total * 100);
        echo json_encode($data);
    }*/
    public function addPages($data){
        echo json_encode("Hello");
    }
}