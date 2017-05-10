<?php
class Cookie extends CookieCore
{
    /**
     * Soft logout, delete everything links to the customer
     * but leave there affiliate's informations.
     * As of version 1.5 don't call this function, use Customer::mylogout() instead;
     */
    public function mylogout()
    {
        unset($this->_content['id_compare']);
        unset($this->_content['id_customer']);
        unset($this->_content['id_guest']);
        unset($this->_content['is_guest']);
        unset($this->_content['id_connections']);
        unset($this->_content['customer_lastname']);
        unset($this->_content['customer_firstname']);
        unset($this->_content['passwd']);
        unset($this->_content['logged']);
        unset($this->_content['email']);
        unset($this->_content['id_address_invoice']);
        unset($this->_content['id_address_delivery']);
        $this->_modified = true;
    }
}
