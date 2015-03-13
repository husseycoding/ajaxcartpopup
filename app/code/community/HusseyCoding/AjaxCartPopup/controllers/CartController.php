<?php
include_once("Mage/Checkout/controllers/CartController.php");
class HusseyCoding_AjaxCartPopup_CartController extends Mage_Checkout_CartController
{
    public function addAction()
    {
        if (!Mage::getStoreConfig('ajaxcartpopup/general/enabled') || !Mage::getStoreConfig('ajaxcartpopup/ajax/ajax_enabled')):
            return parent::addAction();
        endif;
        
        $items = Mage::getSingleton('checkout/cart')->init()->getItems();
        $countbefore = count($items);
        
        parent::addAction();
        
        $this->getResponse()
            ->clearHeaders()
            ->clearBody();
        $this->getResponse()->setHeader("Content-Type", "text/html; charset=UTF-8")
            ->setHttpResponseCode(200)
            ->isRedirect(0);
        
        $lastmessage = Mage::getSingleton('checkout/session')->getMessages()->getLastAddedMessage();
        $result = $lastmessage->getType() == 'success' ? 'success' : false;
        
        $message = '';
        $linktext = '';
        $popuphtml = '';
        $imageurl = '';
        $productname = '';
        $itemid = '';
        $deleteurl = '';
        $product = $this->_initProduct();
        if ($result == 'success'):
            $this->loadLayout()->_initLayoutMessages('checkout/session');
            $message = Mage::app()->getLayout()->getMessagesBlock()->toHtml();
            $message = strip_tags($message);
            $linktext = $this->_getLinkText();
            $popuphtml = $this->getLayout()->getBlock('ajaxcartpopup')->toHtml();
            if (Mage::app()->getRequest()->getParam('imagedetail')):
                $imageurl = (string) Mage::helper('catalog/image')->init($product, 'small_image')->resize(135);
                $productname = addslashes($product->getName());
            endif;
        elseif ($this->getRequest()->getParam('isproductpage')):
            $this->loadLayout()->_initLayoutMessages('checkout/session');
            $message = Mage::app()->getLayout()->getMessagesBlock()->toHtml();
            $message = strip_tags($message);
        else:
            $result = $product->getProductUrl();
        endif;
        
        Mage::helper('ajaxcartpopup')->updateCartCount();
        
        if (Mage::helper('ajaxcartpopup')->getCartItemCount() > $countbefore):
            $allitems = Mage::getSingleton('checkout/cart')->getItems()->getData();
            $itemid = array_pop($allitems);
            $itemid = $itemid['item_id'];
            $deleteurl = Mage::helper('ajaxcartpopup')->getDeleteUrl($itemid);
        endif;
        
        $this->getResponse()->setBody(Zend_Json::encode(array(
            'result' => $result,
            'message' => $message,
            'linktext' => $linktext,
            'popuphtml' => $popuphtml,
            'imageurl' => $imageurl,
            'productname' => $productname,
            'itemid' => $itemid,
            'deleteurl' => $deleteurl
        )));
    }
    
    public function deleteAction()
    {
        parent::deleteAction();
        
        if ($this->getRequest()->getParam('ajaxcartpopup')):
            $result = 'success';
            foreach (Mage::getSingleton('checkout/session')->getMessages()->getItems() as $message):
                if ($message->getType() == 'error'):
                    $result = Mage::helper('checkout/cart')->getCartUrl();
                endif;
                break;
            endforeach;
            
            $this->getResponse()
                ->clearHeaders()
                ->clearBody();
            $this->getResponse()
                ->setHeader("Content-Type", "text/html; charset=UTF-8")
                ->setHttpResponseCode(200)
                ->isRedirect(0);

            $linktext = '';
            $popuphtml = '';
            $emptycart = '';
            if ($result == 'success'):
                $this->loadLayout()->_initLayoutMessages('checkout/session');
                $linktext = $this->_getLinkText();
                $popuphtml = $this->getLayout()->getBlock('ajaxcartpopup')->toHtml();
                $emptycart = Mage::helper('ajaxcartpopup')->getCartCount() ? false : true;
            endif;

            $this->getResponse()->setBody(Zend_Json::encode(array(
                'result' => $result,
                'linktext' => $linktext,
                'popuphtml' => $popuphtml,
                'emptycart' => $emptycart
            )));
        endif;
    }
    
    public function updatePostAction()
    {
        parent::updatePostAction();
        
        if ($this->getRequest()->getParam('ajaxcartpopup') && $this->getRequest()->getParam('ajaxupdatequantity')):
            $result = 'success';
            $this->loadLayout()->_initLayoutMessages('checkout/session');
            foreach (Mage::getSingleton('checkout/session')->getMessages()->getItems() as $message):
                if ($message->getType() == 'error' || $message->getType() == 'exception'):
                    $result = Mage::helper('checkout/cart')->getCartUrl();
                endif;
                break;
            endforeach;
            
            $this->getResponse()
                ->clearHeaders()
                ->clearBody();
            $this->getResponse()
                ->setHeader("Content-Type", "text/html; charset=UTF-8")
                ->setHttpResponseCode(200)
                ->isRedirect(0);

            $linktext = '';
            $popuphtml = '';
            $emptycart = '';
            if ($result == 'success'):
                $this->loadLayout()->_initLayoutMessages('checkout/session');
                $linktext = $this->_getLinkText();
                $popuphtml = $this->getLayout()->getBlock('ajaxcartpopup')->toHtml();
                $emptycart = Mage::helper('ajaxcartpopup')->getCartCount() ? false : true;
            endif;

            $this->getResponse()->setBody(Zend_Json::encode(array(
                'result' => $result,
                'linktext' => $linktext,
                'popuphtml' => $popuphtml,
                'emptycart' => $emptycart
            )));
        endif;
    }
    
    private function _getLinkText()
    {
        if ($block = $this->getLayout()->getBlock('minicart_head')):
            $cartlink = $block->toHtml();
            preg_match('/<a.+skip-cart.+>(.+)<\/a>/Us', $cartlink, $linktext);
        else:
            $toplinks = $this->getLayout()->getBlock('top.links')->toHtml();
            preg_match('/<a.+top-link-cart.+>(.+)<\/a>/Us', $toplinks, $linktext);
        endif;
        
        return $linktext[1];
    }
}