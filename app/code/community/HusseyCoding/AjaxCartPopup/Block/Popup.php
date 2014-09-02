<?php
class HusseyCoding_AjaxCartPopup_Block_Popup extends Mage_Checkout_Block_Cart_Sidebar
{
    public function showPopup()
    {
        return Mage::helper('ajaxcartpopup')->showPopup();
    }
    
    public function notEmpty()
    {
        return Mage::helper('ajaxcartpopup')->notEmpty();
    }
    
    public function getPopupItems($display = null)
    {
        return Mage::helper('ajaxcartpopup')->getPopupItems($display, $this->getItems());
    }
    
    public function getExtraCount()
    {
        return Mage::helper('ajaxcartpopup')->getExtraCount();
    }
    
    public function getDeleteUrl($itemid)
    {
        return Mage::helper('ajaxcartpopup')->getDeleteUrl($itemid);
    }
    
    public function getProductUrl($product)
    {
        return Mage::helper('ajaxcartpopup')->getProductUrl($product);
    }
    
    public function getProductThumbnail($product)
    {
        return Mage::helper('ajaxcartpopup')->getProductThumbnail($product);
    }
    
    public function getCheckoutUrl()
    {
        return Mage::helper('ajaxcartpopup')->getCheckoutUrl();
    }
    
    public function ajaxEnabled()
    {
        return Mage::helper('ajaxcartpopup')->ajaxEnabled();
    }
    
    public function displayCartButton()
    {
        return Mage::helper('ajaxcartpopup')->displayCartButton();
    }
    
    public function displayCheckoutButton()
    {
        return Mage::helper('ajaxcartpopup')->displayCheckoutButton();
    }
    
    public function getProductLimit()
    {
        return Mage::helper('ajaxcartpopup')->getProductLimit();
    }
    
    public function getSlideSpeed()
    {
        return Mage::helper('ajaxcartpopup')->getSlideSpeed();
    }

    public function getConfigureProduct()
    {
        return Mage::helper('ajaxcartpopup')->getConfigureProduct();
    }
    
    public function getItemProductPrice($item)
    {
        if (Mage::getStoreConfig('ajaxcartpopup/popup/incl_tax')):
            $price = $item->getPriceInclTax();
        else:
            $price = $item->getPrice();
        endif;
        
        return Mage::helper('checkout')->formatPrice($price);
    }
    
    public function getItemRowPrice($item)
    {
        if (Mage::getStoreConfig('ajaxcartpopup/popup/incl_tax')):
            $price = $item->getRowTotalInclTax();
        else:
            $price = $item->getRowTotal();
        endif;
        
        return Mage::helper('checkout')->formatPrice($price);
    }
    
    public function getCartSubtotal()
    {
        if (Mage::getStoreConfig('ajaxcartpopup/popup/incl_tax')):
            return Mage::helper('checkout')->formatPrice($this->getSubtotal(false));
        else:
            return Mage::helper('checkout')->formatPrice($this->getSubtotal(true));
        endif;
    }
    
    public function getItemMessages($item)
    {
        $item->checkData();
        $baseMessages = $item->getMessage(false);
        if ($baseMessages) {
            foreach ($baseMessages as $message) {
                $messages[] = array(
                    'text' => $message,
                    'type' => $item->getHasError() ? 'error' : 'notice'
                );
            }
        }
        
        return isset($messages) ? $message : false;
    }

    public function getShortDescription($product)
    {
        if (Mage::helper('ajaxcartpopup')->showDescription()):
            if ($description = $product->getShortDescription()):
                return $description;
            endif;
        endif;
        
        return false;
    }
    
    public function showPopupOnAdd()
    {
        return Mage::helper('ajaxcartpopup')->showPopupOnAdd();
    }

    public function getAutoCloseTime()
    {
        return Mage::helper('ajaxcartpopup')->getAutoCloseTime();
    }

    public function getRelatedProducts($product)
    {
        $relatedProducts = false;
        if ($limit = Mage::helper('ajaxcartpopup')->getRelatedProductLimit()):
            $relatedProducts = $product->getRelatedProductCollection();
            $relatedProducts->getSelect()->limit($limit);
        endif;

        return !empty($relatedProducts) && $relatedProducts->count() ? $relatedProducts : false;
    }

}