<?php
class AddressesController extends AddressesControllerCore
{
    /**
     * {@inheritdoc}
     */
	public function initContent()
    {
        FrontController::initContent();

        $webServiceDiva = new WebServiceDiva('<ACTION>ADR_CLI', '<DOS>1<TIERS>'.$this->context->cookie->tiers);

        try {
            $datas = $webServiceDiva->call();

            if ($datas && $datas->trouve == 1) {

                $total = 0;
                $multiple_addresses_formated = array();
                $ordered_fields = array();

                foreach ($datas->adresse as $detail) {
                    $address = new Address();
                    $address->firstname = $this->context->cookie->customer_firstname;
                    $address->lastname = $this->context->cookie->customer_lastname;
                    $address->address1 = $detail->rue;
                    $address->address2 = $detail->adrcpl1;
                    $address->address3 = $detail->adrcpl2;
                    $address->locality = $detail->loc;
                    $address->postcode = $detail->cpostal;
                    $address->city = $detail->vil;
                    $address->adrcod = $detail->adrcod;
                    $address->alias = $detail->alias;
                    $address->id = $detail->id_adr;

                    $id_country = null;

                    if ($detail->pay) {
                        $id_country = Country::getByIso($detail->pay);
                    }

                    if (!$id_country) {
                        $id_country = Configuration::get('PS_COUNTRY_DEFAULT');
                    }

                    $country = Country::getNameById(
                        $this->context->cart->id_lang ? $this->context->cart->id_lang : Configuration::get('PS_LANG_DEFAULT'),
                        $id_country ? $id_country : Configuration::get('PS_COUNTRY_DEFAULT')
                    );

                    $address->country = $country;

                    $multiple_addresses_formated[$total] = AddressFormat::getFormattedLayoutData($address);
                    unset($address);
                    ++$total;

                    // Retro theme < 1.4.2
                    $ordered_fields = AddressFormat::getOrderedAddressFields(Configuration::get('PS_COUNTRY_DEFAULT'), false, true);
                }

                // Retro theme 1.4.2
                if ($key = array_search('Country:name', $multiple_addresses_formated)) {
                    $ordered_fields[$key] = 'country';
                }

                $addresses_style = array(
                    'company' => 'address_company',
                    'vat_number' => 'address_company',
                    'firstname' => 'address_name',
                    'lastname' => 'address_name',
                    'address1' => 'address_address1',
                    'address2' => 'address_address2',
                    'address3' => 'address_address3',
                    'locality' => 'address_locality',
                    'city' => 'address_city',
                    'country' => 'address_country',
                    'phone' => 'address_phone',
                    'phone_mobile' => 'address_phone_mobile',
                    'alias' => 'address_title',
                );

                $this->context->smarty->assign(array(
                    'addresses_style' => $addresses_style,
                    'multipleAddresses' => $multiple_addresses_formated,
                    'ordered_fields' => $ordered_fields
                ));

                $this->setTemplate(_PS_THEME_DIR_.'addresses.tpl');

            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }

    }
}
