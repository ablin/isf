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

class OrderController extends OrderControllerCore
{

    /**
     * Assign template vars related to page content
     * @see FrontController::initContent()
     */
    public function initContent()
    {
        FrontController::initContent();

        if (Tools::isSubmit('ajax') && Tools::getValue('method') == 'updateExtraCarrier') {
            // Change virtualy the currents delivery options
            $delivery_option = $this->context->cart->getDeliveryOption();
            $delivery_option[(int)Tools::getValue('id_address')] = Tools::getValue('id_delivery_option');
            $this->context->cart->setDeliveryOption($delivery_option);
            $this->context->cart->save();
            $return = array(
                'content' => Hook::exec(
                    'displayCarrierList',
                    array(
                        'address' => new Address((int)Tools::getValue('id_address'))
                    )
                )
            );
            $this->ajaxDie(Tools::jsonEncode($return));
        }

        if ($this->nbProducts) {
            $this->context->smarty->assign('virtual_cart', $this->context->cart->isVirtualCart());
        }

        if (!Tools::getValue('multi-shipping')) {
            $this->context->cart->setNoMultishipping();
        }

        // Check for alternative payment api
        $is_advanced_payment_api = (bool)Configuration::get('PS_ADVANCED_PAYMENT_API');

                    Context::getContext()->cookie->cart_delivery_option = "oui";
        // 4 steps to the order
        switch ((int)$this->step) {

            case OrderController::STEP_SUMMARY_EMPTY_CART:
                $this->context->smarty->assign('empty', 1);
                $this->setTemplate(_PS_THEME_DIR_.'shopping-cart.tpl');
            break;

            case OrderController::STEP_ADDRESSES:
                $this->_assignAddress();
                $this->processAddressFormat();
                if (Tools::getValue('multi-shipping') == 1) {
                    $this->_assignSummaryInformations();
                    $this->context->smarty->assign('product_list', $this->context->cart->getProducts());
                    $this->setTemplate(_PS_THEME_DIR_.'order-address-multishipping.tpl');
                } else {
                    $this->setTemplate(_PS_THEME_DIR_.'order-address.tpl');
                }
            break;

            case OrderController::STEP_DELIVERY:
                if (Tools::isSubmit('processAddress')) {
                    $this->processAddress();
                }
                $this->autoStep();
                $this->_assignCarrier();
                $this->setTemplate(_PS_THEME_DIR_.'order-carrier.tpl');
            break;

            case OrderController::STEP_PAYMENT:
                // Check that the conditions (so active) were accepted by the customer
                $cgv = Tools::getValue('cgv') || $this->context->cookie->check_cgv;

                if ($is_advanced_payment_api === false && Configuration::get('PS_CONDITIONS')
                    && (!Validate::isBool($cgv) || $cgv == false)) {
                    Tools::redirect('index.php?controller=order&step=2');
                }

                if ($is_advanced_payment_api === false) {
                    Context::getContext()->cookie->check_cgv = true;
                }

                if (Tools::getValue('delivery_option')) {
                    $this->context->cart->delivery_option = serialize(array(Tools::getValue('delivery_option')));
                    $this->context->cart->save();
                }

                // Check the delivery option is set
                if ($this->context->cart->isVirtualCart() === false) {
                    if (!Tools::getValue('delivery_option') && !$this->context->cart->delivery_option) {
                        Tools::redirect('index.php?controller=order&step=2');
                    } else {
                        $this->context->cart->carrier = Tools::getValue('delivery_option');
                    }
                }

                $this->autoStep();
                $this->_assignPayment();

                if ($is_advanced_payment_api === true) {
                    $this->_assignAddress();
                }

                // assign some informations to display cart
                $this->_assignSummaryInformations();
                $this->setTemplate(_PS_THEME_DIR_.'order-payment.tpl');
            break;

            default:
                $this->_assignSummaryInformations();
                $this->setTemplate(_PS_THEME_DIR_.'shopping-cart.tpl');
            break;
        }
    }

    public function postProcess()
    {
        if ($this->step == 4) {
            $params = '';

            foreach ($this->context->cart->getProducts() as $product) {
                $params .= '<REF>'.$product['reference'].'<SREF1>'.$product['sous_reference'].'<SREF2> <QTE>'.$product['cart_quantity'];
            }

            $customer = new Customer();

            foreach ($customer->getAddresses((int)Configuration::get('PS_LANG_DEFAULT')) as $address_delivery) {
                if ($address_delivery['id_address'] == $this->context->cart->id_address_delivery) {
                    $params .= '<ADRLIV>' . $address_delivery['adrcod'];
                    break;
                }
            }

            foreach ($customer->getAddresses((int)Configuration::get('PS_LANG_DEFAULT')) as $address_invoice) {
                if ($address_invoice['id_address'] == $this->context->cart->id_address_invoice) {
                    $params .= '<ADRFA>' . $address_invoice['adrcod'];
                    break;
                }
            }

            $webServiceDiva = new WebServiceDiva('<ACTION>CREER_CDE', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<LOGIN>'.$this->context->cookie->login.'<PICOD>2<BLMOD>'.unserialize($this->context->cart->delivery_option)[0].'<SAMEDI> '.$params);

            try {
                $datas = $webServiceDiva->call();

                if ($datas && $datas->num_cde) {
                    $this->context->cart->delete();
                    if ($datas->mail) {
                        $this->context->cookie->__set('ordermessage', $datas->mail);
                    }
                    Tools::redirect('index.php?controller=history-detail&id='.$datas->num_cde.'&picod=2&order=1');
                }

            } catch (SoapFault $fault) {
                throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
            }
        }
    }

    /**
     * Manage address
     */
    public function processAddress()
    {
        $same = Tools::isSubmit('same');
        if (!Tools::getValue('id_address_invoice', false) && !$same) {
            $same = true;
        }

        $this->context->cart->id_address_delivery = (int)Tools::getValue('id_address_delivery');
        $this->context->cart->id_address_invoice = $same ? $this->context->cart->id_address_delivery : (int)Tools::getValue('id_address_invoice');

        CartRule::autoRemoveFromCart($this->context);
        CartRule::autoAddToCart($this->context);

        if (!$this->context->cart->update()) {
            $this->errors[] = Tools::displayError('An error occurred while updating your cart.', !Tools::getValue('ajax'));
        }

        if (!$this->context->cart->isMultiAddressDelivery()) {
            $this->context->cart->setNoMultishipping();
        } // If there is only one delivery address, set each delivery address lines with the main delivery address

        if (Tools::isSubmit('message')) {
            $this->_updateMessage(Tools::getValue('message'));
        }

        // Add checking for all addresses
        $errors = array();
        $address_without_carriers = $this->context->cart->getDeliveryAddressesWithoutCarriers(false, $errors);
        if (count($address_without_carriers) && !$this->context->cart->isVirtualCart()) {
            $flag_error_message = false;
            foreach ($errors as $error) {
                if ($error == Carrier::SHIPPING_WEIGHT_EXCEPTION && !$flag_error_message) {
                    $this->errors[] = sprintf(Tools::displayError('The product selection cannot be delivered by the available carrier(s): it is too heavy. Please amend your cart to lower its weight.', !Tools::getValue('ajax')));
                    $flag_error_message = true;
                } elseif ($error == Carrier::SHIPPING_PRICE_EXCEPTION && !$flag_error_message) {
                    $this->errors[] = sprintf(Tools::displayError('The product selection cannot be delivered by the available carrier(s). Please amend your cart.', !Tools::getValue('ajax')));
                    $flag_error_message = true;
                } elseif ($error == Carrier::SHIPPING_SIZE_EXCEPTION && !$flag_error_message) {
                    $this->errors[] = sprintf(Tools::displayError('The product selection cannot be delivered by the available carrier(s): its size does not fit. Please amend your cart to reduce its size.', !Tools::getValue('ajax')));
                    $flag_error_message = true;
                }
            }
            if (count($address_without_carriers) > 1 && !$flag_error_message) {
                $this->errors[] = sprintf(Tools::displayError('There are no carriers that deliver to some addresses you selected.', !Tools::getValue('ajax')));
            } elseif ($this->context->cart->isMultiAddressDelivery() && !$flag_error_message) {
                $this->errors[] = sprintf(Tools::displayError('There are no carriers that deliver to one of the address you selected.', !Tools::getValue('ajax')));
            } elseif (!$flag_error_message) {
                $this->errors[] = sprintf(Tools::displayError('There are no carriers that deliver to the address you selected.', !Tools::getValue('ajax')));
            }
        }

        if ($this->ajax) {
            $this->ajaxDie(true);
        }
    }

    /**
     * Carrier step
     */
    protected function _assignCarrier()
    {
        $carriers = array();

        $params = '<POIDS>'.Context::getContext()->cookie->poids_total;

        $customer = new Customer();
        foreach ($customer->getAddresses((int)Configuration::get('PS_LANG_DEFAULT')) as $address_delivery) {
            if ($address_delivery['id_address'] == $this->context->cart->id_address_delivery) {
                $address_label = $address_delivery['alias'];
                $params .= '<ADRLIV>' . $address_delivery['adrcod'];
                break;
            }
        }

        foreach ($this->context->cart->getProducts() as $product) {
            $params .= '<REF>'.$product['reference'].'<SREF1>'.$product['sous_reference'].'<SREF2> <QTE>'.$product['cart_quantity'];
        }

        $webServiceDiva = new WebServiceDiva('<ACTION>LIVRAISON', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<LOGIN>'.$this->context->cookie->login.$params);

        try {
            $datas = $webServiceDiva->call();

            if ($datas && $datas->carriers) {
                foreach ($datas->carriers as $carrier) {
                    $carriers[] = array(
                        'code' => $carrier->code,
                        'label' => $carrier->label,
                        'texte' => $carrier->texte,
                        'tarif' => $carrier->tarif
                    );
                }

            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }

        $cms = new CMS(Configuration::get('PS_CONDITIONS_CMS_ID'), $this->context->language->id);
        $this->link_conditions = $this->context->link->getCMSLink($cms, $cms->link_rewrite, (bool)Configuration::get('PS_SSL_ENABLED'));
        if (!strpos($this->link_conditions, '?')) {
            $this->link_conditions .= '?content_only=1';
        } else {
            $this->link_conditions .= '&content_only=1';
        }

        $this->context->smarty->assign(array(
            'carriers' => $carriers,
            'address_label' => $address_label,
            'checkedTOS' => (int)$this->context->cookie->checkedTOS,
            'recyclablePackAllowed' => (int)Configuration::get('PS_RECYCLABLE_PACK'),
            'giftAllowed' => (int)Configuration::get('PS_GIFT_WRAPPING'),
            'cms_id' => (int)Configuration::get('PS_CONDITIONS_CMS_ID'),
            'conditions' => (int)Configuration::get('PS_CONDITIONS'),
            'link_conditions' => $this->link_conditions
        ));
    }

    public function setMedia()
    {
        parent::setMedia();
        if ($this->step == 2) {
            $this->addJS(_THEME_JS_DIR_.'order-carrier.js');
        }
    }
}
