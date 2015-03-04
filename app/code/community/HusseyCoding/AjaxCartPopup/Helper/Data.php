<?php
class HusseyCoding_AjaxCartPopup_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function updateCartCount()
    {
        $count = Mage::helper('checkout/cart')->getSummaryCount();
        Mage::getSingleton('customer/session')->setCartCount($count);
    }
    
    public function getCartItemCount()
    {
        $allitems = Mage::getSingleton('checkout/cart')->getItems();
        return count($allitems);
    }
    
    public function getDeleteUrl($itemid)
    {
        return Mage::getUrl('checkout/cart/delete', array('id' => $itemid, Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => Mage::helper('core/url')->getEncodedUrl()));
    }
    
    public function getCartCount()
    {
        return Mage::helper('checkout/cart')->getSummaryCount();
    }
}