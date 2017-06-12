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

    public function postProcess()
    {
        if ($this->step == 3) {
            $params = '';

            foreach ($this->context->cart->getProducts() as $product) {
                $params .= '<REF>'.$product['reference'].'<SREF1>N<SREF2> <QTE>'.$product['cart_quantity'];
            }

            $webServiceDiva = new WebServiceDiva('<ACTION>CREER_CDE', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<LOGIN>'.$this->context->cookie->login.'<PICOD>2<BLMOD> <SAMEDI> '.$params);

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
}
