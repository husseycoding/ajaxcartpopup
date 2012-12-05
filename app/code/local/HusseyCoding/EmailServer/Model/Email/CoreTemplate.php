<?php
class HusseyCoding_EmailServer_Model_Email_CoreTemplate extends Mage_Core_Model_Email_Template
{
    public function getMail()
    {
        if (is_null($this->_mail)) {
            
            $host = Mage::getStoreConfig('system/smtp/host');
            $port = Mage::getStoreConfig('system/smtp/port');
            $username = Mage::getStoreConfig('system/smtp/username');
            $password = Mage::getStoreConfig('system/smtp/password');
            $config = array(
                'port' => $port,
                'auth' => 'login',
                'username' => $username,
                'password' => $password
            );
            $transport = new Zend_Mail_Transport_Smtp($host, $config);
            Zend_Mail::setDefaultTransport($transport);
            
            $this->_mail = new Zend_Mail('utf-8');
        }
        return $this->_mail;
    }
}