<?php
class AddressController extends AddressControllerCore
{
     /**
     * {@inheritdoc}
     */
    public function init()
    {
        FrontController::init();

        $this->assignAddressFormat();

        // Get address ID
        $id_address = 0;
        if ($this->ajax && Tools::isSubmit('type')) {
            if (Tools::getValue('type') == 'delivery' && isset($this->context->cart->id_address_delivery)) {
                $id_address = (int)$this->context->cart->id_address_delivery;
            } elseif (Tools::getValue('type') == 'invoice' && isset($this->context->cart->id_address_invoice)
                        && $this->context->cart->id_address_invoice != $this->context->cart->id_address_delivery) {
                $id_address = (int)$this->context->cart->id_address_invoice;
            }
        } else {
            $id_address = (int)Tools::getValue('id_address', 0);
        }

        // Initialize address
        if ($id_address) {
            $this->hydrateAddress($id_address);
            if ($this->_address->id) {
                if (Tools::isSubmit('delete')) {
                    $webServiceDiva = new WebServiceDiva('<ACTION>SUPPR_ADR_CLI', '<DOS>1<TIERS>'.$this->context->cookie->tiers.'<ADRCOD>'.strtoupper($this->_address->adrcod));

                    try {
                        if ($datas = $webServiceDiva->call()) {
                            if ($this->context->cart->id_address_invoice == $this->_address->id) {
                                unset($this->context->cart->id_address_invoice);
                            }
                            if ($this->context->cart->id_address_delivery == $this->_address->id) {
                                unset($this->context->cart->id_address_delivery);
                                $this->context->cart->updateAddressId($this->_address->id, (int)Address::getFirstCustomerAddressId(Context::getContext()->customer->id));
                            }
                            Tools::redirect('index.php?controller=addresses');
                        } else {
                            $this->errors[] = Tools::displayError('This address cannot be deleted.');
                        }
                    } catch (SoapFault $fault) {
                        $this->errors[] = Tools::displayError('An error occurred while updating your address: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
                    }
                }
            } elseif ($this->ajax) {
                exit;
            } else {
                Tools::redirect('index.php?controller=addresses');
            }
        }
    }

    /**
     * Hydrate address by id
     *
     * @param int id_address
     */
    private function hydrateAddress($id_address)
    {
        $webServiceDiva = new WebServiceDiva('<ACTION>ADR_CLI', '<DOS>1<TIERS>'.$this->context->cookie->tiers);

        try {
            $datas = $webServiceDiva->call();

            if ($datas && $datas->trouve == 1) {

                foreach ($datas->adresse as $detail) {
                    if ($detail->id_adr == $id_address) {
                        $this->_address = new Address();
                        $this->_address->firstname = $this->context->cookie->customer_firstname;
                        $this->_address->lastname = $this->context->cookie->customer_lastname;
                        $this->_address->address1 = $detail->rue;
                        $this->_address->address2 = $detail->adrcpl1;
                        $this->_address->address3 = $detail->adrcpl2;
                        $this->_address->locality = $detail->loc;
                        $this->_address->postcode = $detail->cpostal;
                        $this->_address->city = $detail->vil;
                        $this->_address->country = $detail->pay;
                        $this->_address->adrcod = $detail->adrcod;
                        $this->_address->alias = $detail->alias;
                        $this->_address->id = $detail->id_adr;
                    }
                }
            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function processSubmitAddress()
    {
        $address = new Address();
        $this->errors = $address->validateController();
        $address->id_customer = (int)$this->context->customer->id;

        // Check page token
        if ($this->context->customer->isLogged() && !$this->isTokenValid()) {
            $this->errors[] = Tools::displayError('Invalid token.');
        }

        if ($address->id_country) {
            // Check country
            if (!($country = new Country($address->id_country)) || !Validate::isLoadedObject($country)) {
                throw new PrestaShopException('Country cannot be loaded with address->id_country');
            }

            if ((int)$country->contains_states && !(int)$address->id_state) {
                $this->errors[] = Tools::displayError('This country requires you to chose a State.');
            }

            if (!$country->active) {
                $this->errors[] = Tools::displayError('This country is not active.');
            }

            $postcode = Tools::getValue('postcode');
            /* Check zip code format */
            if ($country->zip_code_format && !$country->checkZipCode($postcode)) {
                $this->errors[] = sprintf(Tools::displayError('The Zip/Postal code you\'ve entered is invalid. It must follow this format: %s'), str_replace('C', $country->iso_code, str_replace('N', '0', str_replace('L', 'A', $country->zip_code_format))));
            } elseif (empty($postcode) && $country->need_zip_code) {
                $this->errors[] = Tools::displayError('A Zip/Postal code is required.');
            } elseif ($postcode && !Validate::isPostCode($postcode)) {
                $this->errors[] = Tools::displayError('The Zip/Postal code is invalid.');
            }

            // Check country DNI
            if ($country->isNeedDni() && (!Tools::getValue('dni') || !Validate::isDniLite(Tools::getValue('dni')))) {
                $this->errors[] = Tools::displayError('The identification number is incorrect or has already been used.');
            } elseif (!$country->isNeedDni()) {
                $address->dni = null;
            }
        }

        // Don't continue this process if we have errors !
        if ($this->errors && !$this->ajax) {
            return;
        }

        $addressFormatted = '<ADRCOD>'.strtoupper(Tools::getValue('adrcod'));
        $addressFormatted .= '<ALIAS>'.strtoupper(Tools::getValue('alias'));
        $addressFormatted .='<NOM>'.Tools::getValue('firstname').' '.Tools::getValue('lastname');
        $addressFormatted .='<RUE>'.Tools::getValue('address1');
        $addressFormatted .='<ADRCPL1>'.Tools::getValue('address2');
        $addressFormatted .='<ADRCPL2>'.Tools::getValue('address3');
        $addressFormatted .='<LOC>'.Tools::getValue('locality');
        $addressFormatted .='<CPOSTAL>'.Tools::getValue('postcode');
        $addressFormatted .='<VIL>'.Tools::getValue('city');
        $addressFormatted .='<PAY>'.$country->iso_code;
        $addressFormatted .='<DV>2';
        $addressFormatted .='<CD>2';
        $addressFormatted .='<BL>2';
        $addressFormatted .='<FA>2';

        // Save address
        $webServiceDiva = new WebServiceDiva('<ACTION>MAJ_ADR_CLI', '<DOS>1<TIERS>'.$this->context->cookie->tiers.$addressFormatted);

        try {
            if ($datas = $webServiceDiva->call()) {

                $this->context->cookie->customer_firstname = Tools::getValue('firstname');
                $this->context->cookie->customer_lastname = Tools::getValue('lastname');
                $this->context->cart->update();

                unset(Context::getContext()->cookie->addresses);

                 // Redirect to old page or current page
                if ($back = Tools::getValue('back')) {
                    if ($back == Tools::secureReferrer(Tools::getValue('back'))) {
                        Tools::redirect(html_entity_decode($back));
                    }
                    $mod = Tools::getValue('mod');
                    Tools::redirect('index.php?controller='.$back.($mod ? '&back='.$mod : ''));
                } else {
                    Tools::redirect('index.php?controller=addresses');
                }
            }
        } catch (SoapFault $fault) {
            $this->errors[] = Tools::displayError('An error occurred while updating your address: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }

        $this->errors[] = Tools::displayError('An error occurred while updating your address.');
    }

    /**
     * {@inheritdoc}
     */
    public function initContent()
    {
        FrontController::initContent();

        $this->assignCountries();

        // Assign common vars
        $this->context->smarty->assign(array(
            'address_validation' => Address::$definition['fields'],
            'ajaxurl' => _MODULE_DIR_,
            'errors' => $this->errors,
            'token' => Tools::getToken(false),
            'address' => $this->_address,
            'id_address' => (Validate::isLoadedObject($this->_address)) ? $this->_address->id : 0,
            'adrcod' => (Validate::isLoadedObject($this->_address)) ? $this->_address->adrcod : 0
        ));

        if ($back = Tools::getValue('back')) {
            $this->context->smarty->assign('back', Tools::safeOutput($back));
        }
        if ($mod = Tools::getValue('mod')) {
            $this->context->smarty->assign('mod', Tools::safeOutput($mod));
        }
        if (isset($this->context->cookie->account_created)) {
            $this->context->smarty->assign('account_created', 1);
            unset($this->context->cookie->account_created);
        }

        $this->setTemplate(_PS_THEME_DIR_.'address.tpl');
    }
}
