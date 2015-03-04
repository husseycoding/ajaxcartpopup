<?php
class HusseyCoding_AjaxCartPopup_Block_Popup extends Mage_Checkout_Block_Cart_Sidebar
{
    private $_currentcategory;
    private $_currentproduct;
    private $_carthelper;
    private $_checkouthelper;
    private $_urlhelper;
    private $_customersession;
    private $_thisrequest;
    private $_currenturl;
    private $_extracount = 0;
    private $_taxconfig;
    
    public function _beforeToHtml()
    {
        if ($this->_getRequest()->getParam('ajaxcartpopup')):
            $this->setTemplate('ajaxcartpopup/popupbody.phtml');
        endif;
    }
    
    public function isEnabled()
    {
        return Mage::getStoreConfig('ajaxcartpopup/general/enabled');
    }
    
    private function _getCurrentCategory()
    {
        if (!isset($this->_currentcategory)):
            $this->_currentcategory = false;
            if ($category = Mage::registry('current_category')):
                $this->_currentcategory = $category;
            endif;
        endif;
        
        return $this->_currentcategory;
    }
    
    private function _getCurrentProduct()
    {
        if (!isset($this->_currentproduct)):
            $this->_currentproduct = false;
            if ($product = Mage::registry('current_product')):
                $this->_currentproduct = $product;
            endif;
        endif;
        
        return $this->_currentproduct;
    }
    
    public function isProductPage()
    {
        if ($this->_getCurrentProduct()):
            return true;
        endif;
        
        return false;
    }
    
    public function getCategoryUrl()
    {
        if ($category = $this->_getCurrentCategory()):
            return $category->getUrl();
        endif;
        
        return '';
    }
    
    public function getCategoryName()
    {
        if ($category = $this->_getCurrentCategory()):
            $name = $category->getName();
            return addslashes($name);
        endif;
        
        return '';
    }
    
    public function getProductImageUrl()
    {
        if ($product = $this->_getCurrentProduct()):
            return $this->helper('catalog/image')->init($product, 'small_image')->resize(135);
        endif;
        
        return '';
    }
    
    public function getProductName()
    {
        if ($product = $this->_getCurrentProduct()):
            $name = $product->getName();
            return addslashes($name);
        endif;
        
        return '';
    }
    
    private function _getCartHelper()
    {
        if (!isset($this->_carthelper)):
            $this->_carthelper = Mage::helper('checkout/cart');
        endif;
        
        return $this->_carthelper;
    }
    
    private function _getCheckoutHelper()
    {
        if (!isset($this->_checkouthelper)):
            $this->_checkouthelper = Mage::helper('checkout');
        endif;
        
        return $this->_checkouthelper;
    }
    
    private function _getUrlHelper()
    {
        if (!isset($this->_urlhelper)):
            $this->_urlhelper = Mage::helper('core/url');
        endif;
        
        return $this->_urlhelper;
    }
    
    private function _getCurrentUrl()
    {
        if (!isset($this->_currenturl)):
            if ($this->_getRequest()->isXmlHttpRequest() && $this->_getRequest()->getServer('HTTP_REFERER')):
                $this->_currenturl = $request->getServer('HTTP_REFERER');
            else:
                $this->_currenturl = $this->_getUrlHelper()->getCurrentUrl();
            endif;

        endif;
        
        return $this->_currenturl;
    }
    
    public function showPopup()
    {
        if ($count = $this->_getCartCount()):
            if ($count != $this->_getCustomerSession()->getCartCount()):
                $this->_updateCartCount();
                if ($this->_showPopupOnAdd()):
                    return 'true';
                endif;
            endif;
        endif;
        
        return 'false';
    }
    
    private function _getCartCount()
    {
        return $this->_getCartHelper()->getSummaryCount();
    }
    
    private function _getCustomerSession()
    {
        if (!isset($this->_customersession)):
            $this->_customersession = Mage::getSingleton('customer/session');
        endif;
        
        return $this->_customersession;
    }
    
    public function showPopupOnAdd()
    {
        if ($this->_showPopupOnAdd()):
            return 'true';
        endif;
        
        return 'false';
    }
    
    private function _showPopupOnAdd()
    {
        return Mage::getStoreConfig('ajaxcartpopup/popup/show_on_add');
    }
    
    private function _updateCartCount()
    {
        $this->_getCustomerSession()->setCartCount($this->_getCartCount());
    }
    
    private function _getRequest()
    {
        if (!isset($this->_thisrequest)):
            $this->_thisrequest = Mage::app()->getRequest();
        endif;
        
        return $this->_thisrequest;
    }
    
    public function emptyCart()
    {
        if ($this->_getCartCount()):
            return 'false';
        endif;
        
        return 'true';
    }
    
    public function getCheckoutUrl()
    {
        return $this->_getCheckoutUrl();
    }
    
    private function _getCheckoutUrl()
    {
        if (Mage::getStoreConfig('checkout/options/onepage_checkout_enabled')):
            return Mage::getUrl('checkout/onepage', array('_secure' => true));
        endif;
        
        return Mage::getUrl('checkout/multishipping', array('_secure' => true));
    }
    
    public function displayCartButton()
    {
        return Mage::getStoreConfig('ajaxcartpopup/ajax/cart_button');
    }
    
    public function displayCheckoutButton()
    {
        return Mage::getStoreConfig('ajaxcartpopup/ajax/checkout_button');
    }
    
    public function getCartUrl()
    {
        return $this->_getCartUrl();
    }
    
    private function _getCartUrl()
    {
        return $this->_getCartHelper()->getCartUrl();
    }
    
    public function ajaxEnabled()
    {
        if (Mage::getStoreConfig('ajaxcartpopup/ajax/ajax_enabled')):
            if (!$this->_isCartEditPage()):
                return 'true';
            endif;
        endif;
        
        return 'false';
    }
    
    private function _isCartEditPage()
    {
        $currenturl = $this->_getCurrentUrl();
        $editpage = $this->_getCartUrl() . 'configure/';
        
        return strpos($currenturl, $editpage) === 0 ? true : false;
    }
    
    public function getSlideSpeed()
    {
        $speed = (float) Mage::getStoreConfig('ajaxcartpopup/popup/slide_speed');
        
        return !empty($speed) ? $speed : 0.3;
    }
    
    public function getUpdateUrl()
    {
        return $this->getUrl('checkout/cart/updatePost');
    }
    
    public function getAutoCloseTime()
    {
        $timer = (float) Mage::getStoreConfig('ajaxcartpopup/popup/popup_close_timer');
        
        return !empty($timer) ? $timer : 'false';
    }
    
    private function _getProductLimit()
    {
        $limit = (int) Mage::getStoreConfig('ajaxcartpopup/popup/product_limit');
        if ($limit <= 0):
            $limit = 3;
        elseif ($limit > 10):
            $limit = 10;
        endif;
        
        return $limit;
    }
    
    public function getProductLimit()
    {
        return $this->_getProductLimit();
    }
    
    public function getDeleteUrl($itemid)
    {
        return Mage::getUrl('checkout/cart/delete', array('id' => $itemid, Mage_Core_Controller_Front_Action::PARAM_NAME_URL_ENCODED => $this->_getUrlHelper()->getEncodedUrl()));
    }
    
    public function getPopupItems()
    {
        $items = $this->_getItems();
        $limit = $this->getProductLimit();
        $extra = count($items) - $limit;
        $extra = $extra < 0 ? 0 : $extra;
        $this->_extracount = $extra;
        $items = array_slice($items, 0, $limit);
        
        return $items;
    }
    
    private function _getItems()
    {
        $items = parent::getItems();
        
        return array_reverse($items);
    }
    
    public function getExtraCount()
    {
        return $this->_extracount;
    }
    
    public function getProductUrl($item)
    {   
        return $this->_getProductFromItem($item)->getProductUrl();
    }
    
    public function getShortDescription($item)
    {
        if (Mage::getStoreConfig('ajaxcartpopup/popup/short_description')):
            if ($description = $this->_getProductFromItem($item)->getShortDescription()):
                return $description;
            endif;
        endif;
        
        return false;
    }
    
    private function _getProductFromItem($item)
    {
        $product = Mage::getModel('catalog/product')->load($item->getProduct()->getId());
        
        return $product;
    }
    
    public function getItemMessages($item)
    {
        $item->checkData();
        $itemmessages = $item->getMessage(false);
        $messages = array();
        if (!empty($itemmessages)) {
            foreach ($itemmessages as $message) {
                $message = $this->escapeHtml($message);
                $messages[] = array(
                    'text' => $message,
                    'type' => $item->getHasError() ? 'error' : 'notice'
                );
            }
        }
        
        return $messages;
    }
    
    public function getItemProductPrice($item)
    {
        if ($this->_getTaxConfig()):
            $price = $item->getPriceInclTax();
        else:
            $price = $item->getPrice();
        endif;
        
        return $this->_formatPrice($price);
    }
    
    public function getItemRowPrice($item)
    {
        if ($this->_getTaxConfig()):
            $price = $item->getRowTotalInclTax();
        else:
            $price = $item->getRowTotal();
        endif;
        
        return $this->_formatPrice($price);
    }
    
    private function _formatPrice($price)
    {
        return $this->_getCheckoutHelper()->formatPrice($price);
    }

    public function getRelatedProducts($item)
    {
        $limit = (int) Mage::getStoreConfig('ajaxcartpopup/popup/related_product_limit');
        if ($limit < 0):
            $limit = 0;
        elseif ($limit > 10):
            $limit = 10;
        endif;
        
        if (!empty($limit)):
            $related = $this->_getProductFromItem($item)->getRelatedProductCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('thumbnail')
                ->setPositionOrder()
                ->addStoreFilter();
            $related->getSelect()->limit($limit);
            
            if ($related->count()):
                return $related;
            endif;
        endif;

        return false;
    }
    
    public function getCartSubtotal()
    {
        if ($this->_getTaxConfig()):
            return $this->_formatPrice($this->getSubtotal(false));
        else:
            return $this->_formatPrice($this->getSubtotal(true));
        endif;
    }
    
    private function _getTaxConfig()
    {
        if (!isset($this->_taxconfig)):
            $this->_taxconfig = Mage::getStoreConfig('ajaxcartpopup/popup/incl_tax');
        endif;
        
        return $this->_taxconfig;
    }
    
    public function getClickOpen()
    {
        if (Mage::getStoreConfig('ajaxcartpopup/popup/click_open')):
            return 'true';
        endif;
        
        return 'false';
    }
}