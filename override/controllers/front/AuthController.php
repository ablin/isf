<?php
class AuthController extends AuthControllerCore
{
	/**
     * {@inheritdoc}
     */
    protected function processSubmitLogin()
    {
        Hook::exec('actionBeforeAuthentication');

        $logged = false;
        $email = trim(Tools::getValue('email'));
        $passwd = trim(Tools::getValue('passwd'));
        $_POST['passwd'] = null;

        if (empty($email)) {
            $this->errors[] = Tools::displayError('Email is required.');
        } elseif (empty($passwd)) {
            $this->errors[] = Tools::displayError('Password is required.');
        } elseif (!Validate::isPasswd($passwd)) {
            $this->errors[] = Tools::displayError('Invalid password.');
        } else {

            $webServiceDiva = new WebServiceDiva('<ACTION>IDENTIFICATION', '<DOS>1<LOGIN>'.$email.'<WEBPASS>'.sha1($passwd));

            try {
                $datas = $webServiceDiva->call();
                if ($datas && $datas->contact_trouve == 1 && $datas->identification == 1) {
                    $logged = true;
                    $customer = new Customer();
                    $identifiant = $datas->clients[0]->tiers."###".$datas->contact;
                    if (!$customer->getByLogin($identifiant)) {
                        $customer->active = 1;
                        $customer->firstname = $datas->clients[0]->prenom ? $datas->clients[0]->prenom : '.';
                        $customer->lastname = $datas->clients[0]->nom ? $datas->clients[0]->nom : '.';
                        $customer->email = $identifiant;
                        $customer->passwd = Tools::encrypt($passwd);
                        $customer->add();
                    } else {
                        $customer->firstname = $datas->clients[0]->prenom ? $datas->clients[0]->prenom : '.';
                        $customer->lastname = $datas->clients[0]->nom ? $datas->clients[0]->nom : '.';
                        $customer->passwd = Tools::encrypt($passwd);
                        $customer->update();
                    }

                    $email = $datas->email ? $datas->email : '';
                    $tabs = isset($datas->clients[0]->tabs) ? $datas->clients[0]->tabs : array();
                    $this->context->cookie->__set('tiers', $datas->clients[0]->tiers);
                    $this->context->cookie->__set('clients', serialize($datas->clients));
                    $this->context->cookie->__set('paiement', $datas->paiement);
                    $this->context->cookie->__set('tabs', serialize($tabs));
                    $this->context->cookie->__set('orderNumberRequired', $datas->clients[0]->orderNumberRequired);

                } else {
                    $email = 'fail@erreur.com';
                }

            } catch (SoapFault $fault) {
                throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
            }

            if ($logged) {
                $this->context->cookie->id_compare = isset($this->context->cookie->id_compare) ? $this->context->cookie->id_compare: CompareProduct::getIdCompareByIdCustomer($customer->id);
                $this->context->cookie->id_customer = (int)($customer->id);
                $this->context->cookie->customer_lastname = $customer->lastname;
                $this->context->cookie->customer_firstname = $customer->firstname;
                $this->context->cookie->logged = 1;
                $customer->logged = 1;
                $this->context->cookie->is_guest = $customer->isGuest();
                $this->context->cookie->passwd = $customer->passwd;
                $this->context->cookie->email = $email;

                // Add customer to the context
                $this->context->customer = $customer;

                if (Configuration::get('PS_CART_FOLLOWING') && (empty($this->context->cookie->id_cart) || Cart::getNbProducts($this->context->cookie->id_cart) == 0) && $id_cart = (int)Cart::lastNoneOrderedCart($this->context->customer->id)) {
                    if (count($datas->clients[0]->panier->references) > 0) {
                        $cart = new Cart($id_cart);
                        $cart->id_customer = $customer->id;
                        $cart->id_shop = Configuration::get('PS_SHOP_DEFAULT');
                        $cart->id_currency = Configuration::get('PS_CURRENCY_DEFAULT');
                        $cart->id_lang = Configuration::get('PS_LANG_DEFAULT');
                        Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'cart_product` WHERE `id_cart` = '.$id_cart);
                        foreach ($datas->clients[0]->panier->references as $reference) {
                            $productId = Product::getProductByReference((string) $reference->ref);
                            $productAttributeId = $this->getProductAttribute((string) $reference->sref1, $productId);
                            $cart->updateQty((int) $reference->qte, $productId, $productAttributeId);
                        }
                        $this->context->cart = $cart;
                    }
                } else {
                    $id_carrier = (int)$this->context->cart->id_carrier;
                    $this->context->cart->id_carrier = 0;
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

                Hook::exec('actionAuthentication', array('customer' => $this->context->customer));

                // Login information have changed, so we check if the cart rules still apply
                CartRule::autoRemoveFromCart($this->context);
                CartRule::autoAddToCart($this->context);

                if (!$this->ajax) {
                    $back = Tools::getValue('back','my-account');

                    if ($back == Tools::secureReferrer($back)) {
                        Tools::redirect(html_entity_decode($back));
                    }

                    Tools::redirect('index.php?controller='.(($this->authRedirection !== false) ? urlencode($this->authRedirection) : $back));
                }
            } else {
                $this->errors[] = Tools::displayError('Authentication failed.');
            }
        }
        
        if ($this->ajax) {
            $return = array(
                'hasError' => !empty($this->errors),
                'errors' => $this->errors,
                'token' => Tools::getToken(false)
            );
            $this->ajaxDie(Tools::jsonEncode($return));
        } else {
            $this->context->smarty->assign('authentification_error', $this->errors);
        }

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
