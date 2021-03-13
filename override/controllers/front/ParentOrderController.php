<?php
class ParentOrderController extends ParentOrderControllerCore
{
    protected function _assignAddress()
    {
        //if guest checkout disabled and flag is_guest  in cookies is actived
        if (Configuration::get('PS_GUEST_CHECKOUT_ENABLED') == 0 && ((int)$this->context->customer->is_guest != Configuration::get('PS_GUEST_CHECKOUT_ENABLED'))) {
            $this->context->customer->logout();
            Tools::redirect('');
        } elseif (!Customer::getAddressesTotalById($this->context->customer->id)) {
            $multi = (int)Tools::getValue('multi-shipping');
            Tools::redirect('index.php?controller=address&back='.urlencode('order.php?step=1'.($multi ? '&multi-shipping='.$multi : '')));
        }

        $customer = $this->context->customer;
        if (Validate::isLoadedObject($customer)) {
            /* Getting customer addresses */
            $customerAddresses = $customer->getAddresses($this->context->language->id);

            // Getting a list of formated address fields with associated values
            $formatedAddressFieldsValuesList = array();

            foreach ($customerAddresses as $i => $address) {
                $tmpAddress = new Address($address['id_address']);
                $formatedAddressFieldsValuesList[$address['id_address']]['ordered_fields'] = AddressFormat::getOrderedAddressFields($address['id_country']);
                $formatedAddressFieldsValuesList[$address['id_address']]['formated_fields_values'] = AddressFormat::getFormattedAddressFieldsValues(
                    $tmpAddress,
                    $formatedAddressFieldsValuesList[$address['id_address']]['ordered_fields']);

                unset($tmpAddress);
            }

            $customerAddresses = array_values($customerAddresses);

            if (!count($customerAddresses) && !Tools::isSubmit('ajax')) {
                $bad_delivery = false;
                if (($bad_delivery = (bool)!Address::isCountryActiveById((int)$this->context->cart->id_address_delivery)) || !Address::isCountryActiveById((int)$this->context->cart->id_address_invoice)) {
                    $params = array();
                    if ($this->step) {
                        $params['step'] = (int)$this->step;
                    }
                    if ($multi = (int)Tools::getValue('multi-shipping')) {
                        $params['multi-shipping'] = $multi;
                    }
                    $back_url = $this->context->link->getPageLink('order', true, (int)$this->context->language->id, $params);

                    $params = array('back' => $back_url, 'id_address' => ($bad_delivery ? (int)$this->context->cart->id_address_delivery : (int)$this->context->cart->id_address_invoice));
                    if ($multi) {
                        $params['multi-shipping'] = $multi;
                    }

                    Tools::redirect($this->context->link->getPageLink('address', true, (int)$this->context->language->id, $params));
                }
            }
            $this->context->smarty->assign(array(
                'addresses' => $customerAddresses,
                'formatedAddressFieldsValuesList' => $formatedAddressFieldsValuesList)
            );

            /* Setting default addresses for cart */
            if (count($customerAddresses)) {
                if ((!isset($this->context->cart->id_address_delivery) || empty($this->context->cart->id_address_delivery)) || !Address::isCountryActiveById((int)$this->context->cart->id_address_delivery)) {
                    $this->context->cart->id_address_delivery = (int)$customerAddresses[0]['id_address'];
                    $update = 1;
                }
                if ((!isset($this->context->cart->id_address_invoice) || empty($this->context->cart->id_address_invoice)) || !Address::isCountryActiveById((int)$this->context->cart->id_address_invoice)) {
                    $this->context->cart->id_address_invoice = (int)$customerAddresses[0]['id_address'];
                    $update = 1;
                }

                /* Update cart addresses only if needed */
                if (isset($update) && $update) {
                    $this->context->cart->update();
                    if (!$this->context->cart->isMultiAddressDelivery()) {
                        $this->context->cart->setNoMultishipping();
                    }
                    // Address has changed, so we check if the cart rules still apply
                    CartRule::autoRemoveFromCart($this->context);
                    CartRule::autoAddToCart($this->context);
                }
            }

            /* If delivery address is valid in cart, assign it to Smarty */
            if (isset($this->context->cart->id_address_delivery)) {
                $deliveryAddress = new Address((int)$this->context->cart->id_address_delivery);
                if ($deliveryAddress) {
                    $this->context->smarty->assign('delivery', $deliveryAddress);
                }
            }

            /* If invoice address is valid in cart, assign it to Smarty */
            if (isset($this->context->cart->id_address_invoice)) {
                $invoiceAddress = new Address((int)$this->context->cart->id_address_invoice);
                if ($invoiceAddress) {
                    $this->context->smarty->assign('invoice', $invoiceAddress);
                }
            }
        }
        if ($oldMessage = Message::getMessageByCartId((int)$this->context->cart->id)) {
            $this->context->smarty->assign('oldMessage', $oldMessage['message']);
        }
    }

    protected function _assignPayment()
    {
        if ((bool)Configuration::get('PS_ADVANCED_PAYMENT_API')) {
            $this->addJS(_THEME_JS_DIR_.'advanced-payment-api.js');

            // TOS
            $cms = new CMS(Configuration::get('PS_CONDITIONS_CMS_ID'), $this->context->language->id);
            $this->link_conditions = $this->context->link->getCMSLink($cms, $cms->link_rewrite, (bool)Configuration::get('PS_SSL_ENABLED'));
            if (!strpos($this->link_conditions, '?')) {
                $this->link_conditions .= '?content_only=1';
            } else {
                $this->link_conditions .= '&content_only=1';
            }

            $this->context->smarty->assign(array(
                'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
                'HOOK_ADVANCED_PAYMENT' => Hook::exec('advancedPaymentOptions', array(), null, true),
                'link_conditions' => $this->link_conditions
            ));
        } else {
            $this->context->smarty->assign(array(
                'HOOK_TOP_PAYMENT' => Hook::exec('displayPaymentTop'),
                'HOOK_PAYMENT' => Hook::exec('displayPayment'),
                'display_payment' => (int)$this->context->cookie->paiement
            ));
        }

        $this->addJS(_THEME_JS_DIR_.'order-payment.js');
    }

    protected function _assignSummaryInformations()
    {
        $summary = $this->context->cart->getSummaryDetails();
        $customizedDatas = Product::getAllCustomizedDatas($this->context->cart->id);

        // override customization tax rate with real tax (tax rules)
        if ($customizedDatas) {
            foreach ($summary['products'] as &$productUpdate) {
                $productId = (int)isset($productUpdate['id_product']) ? $productUpdate['id_product'] : $productUpdate['product_id'];
                $productAttributeId = (int)isset($productUpdate['id_product_attribute']) ? $productUpdate['id_product_attribute'] : $productUpdate['product_attribute_id'];

                if (isset($customizedDatas[$productId][$productAttributeId])) {
                    $productUpdate['tax_rate'] = Tax::getProductTaxRate($productId, $this->context->cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')});
                }
            }

            Product::addCustomizationPrice($summary['products'], $customizedDatas);
        }

        $cart_product_context = Context::getContext()->cloneContext();
        foreach ($summary['products'] as $key => &$product) {
            $product['quantity'] = $product['cart_quantity'];// for compatibility with 1.2 themes

            if ($cart_product_context->shop->id != $product['id_shop']) {
                $cart_product_context->shop = new Shop((int)$product['id_shop']);
            }
            $product['price_without_specific_price'] = Product::getPriceStatic(
                $product['id_product'],
                !Product::getTaxCalculationMethod(),
                $product['id_product_attribute'],
                6,
                null,
                false,
                false,
                1,
                false,
                null,
                null,
                null,
                $null,
                true,
                true,
                $cart_product_context);

            if (Product::getTaxCalculationMethod()) {
                $product['is_discounted'] = Tools::ps_round($product['price_without_specific_price'], _PS_PRICE_COMPUTE_PRECISION_) != Tools::ps_round($product['price'], _PS_PRICE_COMPUTE_PRECISION_);
            } else {
                $product['is_discounted'] = Tools::ps_round($product['price_without_specific_price'], _PS_PRICE_COMPUTE_PRECISION_) != Tools::ps_round($product['price_wt'], _PS_PRICE_COMPUTE_PRECISION_);
            }
        }

        // Get available cart rules and unset the cart rules already in the cart
        $available_cart_rules = CartRule::getCustomerCartRules($this->context->language->id, (isset($this->context->customer->id) ? $this->context->customer->id : 0), true, true, true, $this->context->cart, false, true);
        $cart_cart_rules = $this->context->cart->getCartRules();
        foreach ($available_cart_rules as $key => $available_cart_rule) {
            foreach ($cart_cart_rules as $cart_cart_rule) {
                if ($available_cart_rule['id_cart_rule'] == $cart_cart_rule['id_cart_rule']) {
                    unset($available_cart_rules[$key]);
                    continue 2;
                }
            }
        }

        $show_option_allow_separate_package = (!$this->context->cart->isAllProductsInStock(true) && Configuration::get('PS_SHIP_WHEN_AVAILABLE'));
        $advanced_payment_api = (bool)Configuration::get('PS_ADVANCED_PAYMENT_API');

        $this->context->smarty->assign($summary);
        $this->context->smarty->assign(array(
            'token_cart' => Tools::getToken(false),
            'isLogged' => $this->isLogged,
            'isVirtualCart' => $this->context->cart->isVirtualCart(),
            'productNumber' => $this->context->cart->nbProducts(),
            'voucherAllowed' => CartRule::isFeatureActive(),
            'shippingCost' => $this->context->cart->getTotalShippingCost(),
            'shippingCostTaxExc' => $this->context->cart->getTotalShippingCost(null, false),
            'customizedDatas' => $customizedDatas,
            'CUSTOMIZE_FILE' => Product::CUSTOMIZE_FILE,
            'CUSTOMIZE_TEXTFIELD' => Product::CUSTOMIZE_TEXTFIELD,
            'lastProductAdded' => $this->context->cart->getLastProduct(),
            'displayVouchers' => $available_cart_rules,
            'show_option_allow_separate_package' => $show_option_allow_separate_package,
            'smallSize' => Image::getSize(ImageType::getFormatedName('small')),
            'advanced_payment_api' => $advanced_payment_api

        ));

        $this->context->smarty->assign(array(
            'HOOK_SHOPPING_CART' => Hook::exec('displayShoppingCartFooter', $summary),
            'HOOK_SHOPPING_CART_EXTRA' => Hook::exec('displayShoppingCart', $summary)
        ));
    }
}
