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

class ChangeClientController extends FrontController
{
    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        parent::initContent();
        $customer = new Customer();
        $identifiant = Tools::getValue('client')."###".$this->context->cookie->contact;
        $nb = 0;
        $clients = unserialize($this->context->cookie->clients);
        foreach ($clients as $client) {
            if (Tools::getValue('client') == $client->tiers) {
                break;
            }
            $nb++;
        }
        if (!$customer->getByLogin($identifiant)) {
            $customer->active = 1;
            $customer->firstname = $clients[$nb]->prenom ? $clients[$nb]->prenom : '.';
            $customer->lastname = $clients[$nb]->nom ? $clients[$nb]->nom : '.';
            $customer->email = $identifiant;
            $customer->passwd = Tools::encrypt($this->context->cookie->passwd);
            $customer->add();
        } else {
            $customer->firstname = $clients[$nb]->prenom ? $clients[$nb]->prenom : '.';
            $customer->lastname = $clients[$nb]->nom ? $clients[$nb]->nom : '.';
            $customer->passwd = Tools::encrypt($this->context->cookie->passwd);
            $customer->update();
        }
        $tabs = isset($clients[$nb]->tabs) ? $clients[$nb]->tabs : array();
        $this->context->cookie->__set('tiers', $clients[$nb]->tiers);
        $this->context->cookie->__set('tabs', serialize($tabs));
        $this->context->cookie->__set('orderNumberRequired', $clients[$nb]->orderNumberRequired);

        $this->context->cookie->id_compare = isset($this->context->cookie->id_compare) ? $this->context->cookie->id_compare: CompareProduct::getIdCompareByIdCustomer($customer->id);
        $this->context->cookie->id_customer = (int)($customer->id);
        $this->context->cookie->customer_lastname = $customer->lastname;
        $this->context->cookie->customer_firstname = $customer->firstname;
        $this->context->cookie->logged = 1;
        $customer->logged = 1;
        $this->context->cookie->is_guest = $customer->isGuest();
        $this->context->cookie->passwd = $customer->passwd;

        // Add customer to the context
        $this->context->customer = $customer;

        if (Configuration::get('PS_CART_FOLLOWING') && $id_cart = (int)Cart::lastNoneOrderedCart($this->context->customer->id)) {
            $cart = new Cart($id_cart);
            if (count($clients[$nb]->panier->references) > 0) {
                $cart->id_customer = $customer->id;
                $cart->id_shop = Configuration::get('PS_SHOP_DEFAULT');
                $cart->id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
                $cart->id_lang = Configuration::get('PS_LANG_DEFAULT');
                Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'cart_product` WHERE `id_cart` = '.$id_cart);
                foreach ($clients[$nb]->panier->references as $reference) {
                    $productId = Product::getProductByReference((string) $reference->ref);
                    $productAttributeId = $this->getProductAttribute((string) $reference->sref1, $productId);
                    $cart->updateQty((int) $reference->qte, $productId, $productAttributeId);
                }
            }
            $this->context->cart = $cart;
        } else {
            $cart = new Cart($id_cart);
            $cart->id_customer = $customer->id;
            $cart->id_shop = Configuration::get('PS_SHOP_DEFAULT');
            $cart->id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
            $cart->id_lang = Configuration::get('PS_LANG_DEFAULT');
            $this->context->cart = $cart;
            $id_carrier = (int)$this->context->cart->id_carrier;
            $this->context->cart->id_carrier = 0;
            $this->context->cart->id_guest = (int)$this->context->cookie->id_guest;
            $cart->id_shop_group = (int)$this->context->shop->id_shop_group;
            $this->context->cart->setDeliveryOption(null);
            $this->context->cart->id_address_delivery = (int)Address::getFirstCustomerAddressId((int)($customer->id));
            $this->context->cart->id_address_invoice = (int)Address::getFirstCustomerAddressId((int)($customer->id));
        }
        $this->context->cart->id_customer = (int)$customer->id;
        $this->context->cart->secure_key = $customer->secure_key;

        if ($this->ajax && isset($id_carrier) && $id_carrier && Configuration::get('PS_ORDER_PROCESS_TYPE')) {
            $delivery_option = array($this->context->cart->id_address_delivery => $id_carrier.',');
            $this->context->cart->setDeliveryOption($delivery_option);
        }

        $this->context->cart->save();
        $this->context->cookie->id_cart = (int) $this->context->cart->id;
        $this->context->cookie->write();
        $this->context->cart->autosetProductAddress();

        $this->ajaxDie('1');
    }

    private function getProductAttribute($attribute, $productId)
    {
        $sql = sprintf(
            "SELECT pac.id_product_attribute FROM %sattribute_lang al
                            INNER JOIN %sproduct_attribute_combination pac USING (id_attribute)
                            INNER JOIN %sproduct_attribute pa USING (id_product_attribute)
                            INNER JOIN %sattribute a USING (id_attribute)
                            WHERE al.name = '%s' AND al.id_lang = %d AND pa.id_product = %d",
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            (string) $attribute,
            (int) Context::getContext()->language->id,
            $productId
        );

        return Db::getInstance()->getValue($sql);
    }
}
