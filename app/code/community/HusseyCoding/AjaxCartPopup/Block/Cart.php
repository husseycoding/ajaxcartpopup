<?php
class HusseyCoding_AjaxCartPopup_Block_Cart extends Mage_Core_Block_Template
{
    public function isEnabled()
    {
        return Mage::getStoreConfig('ajaxcartpopup/ajax/cart_enabled');
    }
    
    public function getUpdateUrl()
    {
        return $this->helper('ajaxcartpopup')->getUpdateUrl();
    }
}