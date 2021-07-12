<?php
/*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

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
        unset($this->_content['id_cart']);
        unset($this->_content['id_address_invoice']);
        unset($this->_content['id_address_delivery']);
        unset($this->_content['tiers']);
        unset($this->_content['login']);
        unset($this->_content['tabs']);
        unset($this->_content['last_cart_params']);
        unset($this->_content['cart_datas']);
        unset($this->_content['montant_total']);
        unset($this->_content['montant_ht']);
        unset($this->_content['montant_ttc']);
        unset($this->_content['montant_tva']);
        unset($this->_content['poids_total']);
        unset($this->_content['cart_delivery_option']);
        unset($this->_content['id_address_delivery']);
        $this->_modified = true;
    }
}
