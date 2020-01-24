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

class Customer extends CustomerCore
{
    public function __construct($id = null)
    {
        $definition = self::$definition;
        $definition['fields']['firstname'] = array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32);
        $definition['fields']['lastname'] = array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32);

        self::$definition = $definition;

        parent::__construct($id);
    }

    /**
     * Return customer addresses
     *
     * @param int $id_lang Language ID
     * @return array Addresses
     */
    public function getAddresses($id_lang)
    {
        $addresses = array();

        $webServiceDiva = new WebServiceDiva('<ACTION>ADR_CLI', '<DOS>1<TIERS>'.Context::getContext()->cookie->tiers);

        try {
            $datas = $webServiceDiva->call();

            if ($datas && $datas->trouve == 1) {

                foreach ($datas->adresse as $detail) {
                    $address = array();
                    $address['id_address'] = $detail->id_adr;
                    $address['firstname'] = Context::getContext()->cookie->customer_firstname;
                    $address['lastname'] = Context::getContext()->cookie->customer_lastname;
                    $address['address1'] = $detail->rue;
                    $address['address2'] = $detail->adrcpl1;
                    $address['address3'] = $detail->adrcpl2;
                    $address['locality'] = $detail->loc;
                    $address['postcode'] = $detail->cpostal;
                    $address['city'] = $detail->vil;
                    $address['alias'] = $detail->alias;
                    $address['id'] = $detail->id_adr;

                    $id_country = null;

                    if ($detail->pay) {
                        $id_country = Country::getByIso($detail->pay);
                    }

                    if (!$id_country) {
                        $id_country = Configuration::get('PS_COUNTRY_DEFAULT');
                    }

                    $country = Country::getNameById(
                        Context::getContext()->cart->id_lang ? Context::getContext()->cart->id_lang : Configuration::get('PS_LANG_DEFAULT'),
                        $id_country ? $id_country : Configuration::get('PS_COUNTRY_DEFAULT')
                    );

                    $address['country'] = $country;
                    $address['id_country'] = $id_country;
                    $addresses[] = $address;
                }

                return $addresses;

            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }
    }

    /**
     * Count the number of addresses for a customer
     *
     * @param int $id_customer Customer ID
     * @return int Number of addresses
     */
    public static function getAddressesTotalById($id_customer)
    {
        $total = 0;

        $webServiceDiva = new WebServiceDiva('<ACTION>ADR_CLI', '<DOS>1<TIERS>'.Context::getContext()->cookie->tiers);

        try {
            $datas = $webServiceDiva->call();

            if ($datas && $datas->trouve == 1) {

                $total = count($datas->adresse);

            }

        } catch (SoapFault $fault) {
            throw new Exception('Error: SOAP Fault: (faultcode: {'.$fault->faultcode.'}, faultstring: {'.$fault->faultstring.'})');
        }

        return $total;
    }
}
