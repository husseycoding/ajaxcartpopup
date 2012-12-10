<?php
class HusseyCoding_AjaxCartPopup_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_showpopup = false;
    protected $_notempty = false;
    protected $_updatequantity = false;
    protected $_customersession;
    protected $_customercart;
    protected $_extracount = 0;
    
    public function __construct()
    {
        $this->_customersession = Mage::getSingleton('customer/session');
        $this->_customercart = Mage::getSingleton('checkout/cart');
        
        $sessioncount = $this->getCustomerSession()->getCartCount();
        if (!isset($sessioncount)):
            $cartcount = $this->getCartCount() ? $this->getCartCount() : 0;
            $this->getCustomerSession()->setCartCount($cartcount);
        else:
            if ($cartcount = $this->getCartCount()):
                if ($cartcount != $this->getCustomerSession()->getCartCount()):
                    $this->updateCartCount();
                    $request = Mage::helper('core/url')->getCurrentUrl();
                    $carturl = Mage::helper('checkout/cart')->getCartUrl();
                    $checkouturl = $this->getCheckoutUrl();
                    if ($request != $carturl && $request != $checkouturl):
                        $this->_showpopup = true;
                    endif;
                else:
                    $request = Mage::helper('core/url')->getCurrentUrl();
                    $carturl = Mage::helper('checkout/cart')->getCartUrl();
                    $checkouturl = $this->getCheckoutUrl();
                    if ($request != $carturl && $request != $checkouturl):
                        $this->_notempty = true;
                    endif;
                endif;
            else:
                $this->getCustomerSession()->setCartCount(0);
            endif;
        endif;
    }
    
    public function getCart()
    {
        return $this->_customercart;
    }
    
    public function getCartCount()
    {
        return $this->getCart()->getSummaryQty() ? $this->getCart()->getSummaryQty() : $this->getCart()->getSummaryCount();
    }
    
    public function showPopup()
    {
        return $this->_showpopup;
    }
    
    public function notEmpty()
    {
        return $this->_notempty;
    }
    
    public function getCustomerSession()
    {
        return $this->_customersession;
    }
    
    public function getPopupItems($display = null, $items = null)
    {
        if (!$items || !count($items)):
            $this->getCustomerSession()->setPopupItems();
            return array();
        endif;
        
        if ($sorteditems = $this->_sortItems($items)):
            if (isset($display) && count($sorteditems) > $display):
                $this->_extracount = count($sorteditems) - $display;
                $sorteditems = array_slice($sorteditems, 0, $display);
            endif;
            return $sorteditems;
        endif;
        return array();
    }
    
    protected function _sortItems($items)
    {
        return $this->_updateSessionItems($items);
    }
    
    protected function _updateSessionItems($items)
    {
        $allitems = array();
        if (!$this->getCustomerSession()->getPopupItems()):
            $nextorder = 0;
            foreach ($items as $item):
                $allitems[$item->getProductId()]['qty'] = $item->getQty();
                $allitems[$item->getProductId()]['order'] = $nextorder;
                $nextorder++;
            endforeach;
            $this->getCustomerSession()->setPopupItems($allitems);
            return $items;
        else:
            $sessionitems = $this->getCustomerSession()->getPopupItems();
            if (count($sessionitems) > count($items)):
                foreach ($items as $item):
                    $allitems[$item->getProductId()] = $sessionitems[$item->getProductId()];
                endforeach;
                return $this->_updateOrder($items, $allitems);
            elseif (count($sessionitems) < count($items)):
                $nextorder = count($sessionitems);
                foreach ($items as $item):
                    if (!array_key_exists($item->getProductId(), $sessionitems)):
                        $sessionitems[$item->getProductId()]['qty'] = $item->getQty();
                        $sessionitems[$item->getProductId()]['order'] = $nextorder;
                        $nextorder++;
                    endif;
                endforeach;
                $items = $this->_updateOrder($items, $sessionitems);
                $sessionitems = $this->getCustomerSession()->getPopupItems();
            endif;
                
            $nextorder = count($sessionitems) - 1;
            $updateitems = array();
            foreach ($items as $item):
                if ($sessionitems[$item->getProductId()]['qty'] != $item->getQty()):
                    $sessionitems[$item->getProductId()]['qty'] = $item->getQty();
                    $sessionitems[$item->getProductId()]['order'] = $nextorder;
                    $nextorder--;
                else:
                    $updateitems[] = $item->getProductId();
                endif;
            endforeach;

            foreach ($updateitems as $updateitem):
                $sessionitems[$updateitem]['order'] = $sessionitems[$updateitem]['order'] - $nextorder;
            endforeach;
            return $this->_updateOrder($items, $sessionitems);
        endif;
    }
    
    protected function _updateOrder($collection, $sessionitems)
    {
        $currentcounts = array();
        foreach ($sessionitems as $productid => $item):
            $currentcounts[$item['order']]['productid'] = $productid;
            $currentcounts[$item['order']]['qty'] = $item['qty'];
        endforeach;
        ksort($currentcounts);
        
        $sessionitems = array();
        $newcount = 0;
        foreach ($currentcounts as $currentcount):
            $sessionitems[$currentcount['productid']]['qty'] = $currentcount['qty'];
            $sessionitems[$currentcount['productid']]['order'] = $newcount;
            $newcount++;
        endforeach;
        
        $this->getCustomerSession()->setPopupItems($sessionitems);
        
        $newcollection = array();
        foreach ($collection as $item):
            $newcollection[$sessionitems[$item->getProductId()]['order']] = $item;
        endforeach;
        ksort($newcollection);
        $newcollection = array_reverse($newcollection);
        
        return $newcollection;
    }
    
    public function getExtraCount()
    {
        return $this->_extracount;
    }
    
    public function getDeleteUrl($itemid)
    {
        return Mage::getUrl(
            'checkout/cart/delete',
            array(
                'id' => $itemid,
                Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => Mage::helper('core/url')->getEncodedUrl()
            )
        );
    }
    
    public function getProductUrl($product)
    {
        return $product->getProductUrl();
    }
    
    public function getProductThumbnail($product)
    {
        if ($parentid = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($product->getId())):
            $product = Mage::getModel('catalog/product')->load(reset($parentid));
        elseif ($parentid = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($product->getId())):
            $product = Mage::getModel('catalog/product')->load(reset($parentid));
        endif;
        
        return Mage::helper('catalog/image')->init($product, 'thumbnail')->resize(60);
    }
    
    public function getCheckoutUrl()
    {
        if (Mage::getStoreConfig('checkout/options/onepage_checkout_enabled')):
            return Mage::getUrl('checkout/onepage', array('_secure'=>true));
        else:
            return Mage::getUrl('checkout/multishipping', array('_secure'=>true));
        endif;
    }
    
    public function ajaxEnabled()
    {
        return Mage::getStoreConfig('ajaxcartpopup/ajax/ajax_enabled') ? true : false;
    }
    
    public function displayCartButton()
    {
        return Mage::getStoreConfig('ajaxcartpopup/ajax/cart_button') ? true : false;
    }
    
    public function displayCheckoutButton()
    {
        return Mage::getStoreConfig('ajaxcartpopup/ajax/checkout_button') ? true : false;
    }
    
    public function updateCartCount()
    {
        $this->getCustomerSession()->setCartCount($this->getCartCount());
    }
    
    public function getProductLimit()
    {
        $limit = (int) Mage::getStoreConfig('ajaxcartpopup/popup/product_limit');
        return !$limit || $limit > 10 ? 10 : $limit;
    }
    
    public function getSlideSpeed()
    {
        $speed = (float) Mage::getStoreConfig('ajaxcartpopup/popup/slide_speed');
        return !$speed ? 0.3 : $speed;
    }
    
    public function getConfigureProduct()
    {
        $request = Mage::helper('core/url')->getCurrentUrl();
        $compare = Mage::helper('checkout/cart')->getCartUrl() . 'configure/';
        
        return strpos($request, $compare) === 0 ? true : false;
    }
}