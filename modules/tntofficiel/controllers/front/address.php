<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficielAddressModuleFrontController extends ModuleFrontController
{
    /**
     * TNTOfficielAddressModuleFrontController constructor.
     * Controller always used for AJAX response.
     */
    public function __construct()
    {
        TNTOfficiel_Logstack::log();

        parent::__construct();

        // SSL
        $this->ssl = Tools::usingSecureMode();
        // No header/footer.
        $this->ajax = true;

        // Do not waste time to get price for some AJAX request.
        $this->dngp = true;
    }

    /**
     * Store Extra Information of Customer Cart Delivery Address.
     *
     * @return string
     */
    public function displayAjaxStoreDeliveryInfo()
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;
        $objCart = $objContext->cart;
        //$intCartID = (int)$objCart->id;

        $arrFormCartAddressValidate = TNTOfficiel_Address::storeDeliveryInfo(
            $objCart,
            (string)Tools::getValue('customer_email'),
            (string)Tools::getValue('customer_mobile'),
            (string)Tools::getValue('address_building'),
            (string)Tools::getValue('address_accesscode'),
            (string)Tools::getValue('address_floor')
        );

        echo Tools::jsonEncode($arrFormCartAddressValidate);

        return true;
    }

    /**
     * Check if the city match the postcode.
     *
     * @return string
     */
    public function displayAjaxCheckPostcodeCity()
    {
        TNTOfficiel_Logstack::log();

        $arrResult = array(
            'required' => false,
            'postcode' => false,
            'cities' => false,
        );

        // check the country
        $intCountryID = (int)Tools::getValue('countryId');
        $strCountryISO = Country::getIsoById($intCountryID);
        $strPostCode = trim(pSQL(Tools::getValue('postcode')));
        $strCity = trim(pSQL(Tools::getValue('city')));

        if ($strCountryISO === 'FR') {
            // Check is required for France.
            $arrResult['required'] = true;
            // PostCode is NNNNN
            if (preg_match('/^[0-9]{5}$/ui', $strPostCode) === 1) {
                // Check the city/postcode.
                $boolMDWCheckResult = TNTOfficiel_JsonRPCClient::testCity(
                    $strPostCode,
                    $strCity,
                    $this->context->shop->id
                );

                // If city/postcode correct.
                if ($boolMDWCheckResult) {
                    $arrResult['postcode'] = true;
                    $arrResult['cities'] = true;
                } else {
                    // Get cities from the middleware from the given postal code.
                    $arrMDWCitiesResult = TNTOfficiel_JsonRPCClient::getCities(
                        $strPostCode,
                        $this->context->shop->id
                    );

                    if (count($arrMDWCitiesResult) > 0) {
                        $arrResult['postcode'] = true;
                    }

                    $arrResult['cities'] = $arrMDWCitiesResult;
                }
            }
        }

        echo Tools::jsonEncode($arrResult);

        return true;
    }

    /**
     * Get cities for a postcode.
     *
     * @return string
     */
    public function displayAjaxGetCities()
    {
        TNTOfficiel_Logstack::log();

        $objCart = $this->context->cart;
        $deliveryAddress = new Address($objCart->id_address_delivery);
        $arrMDWCitiesResult = TNTOfficiel_JsonRPCClient::getCities(
            trim($deliveryAddress->postcode),
            $this->context->shop->id
        );

        $boolCityPostCodeIsValid = TNTOfficiel_Address::checkPostCodeCityForCart($objCart);

        echo Tools::jsonEncode(array(
            'valid' => $boolCityPostCodeIsValid,
            'cities' => $arrMDWCitiesResult,
            'postcode' => trim($deliveryAddress->postcode)
        ));

        return true;
    }

    /**
     * Update the city for the current delivery address.
     *
     * @return array
     */
    public function displayAjaxUpdateDeliveryAddress()
    {
        TNTOfficiel_Logstack::log();

        $strCity = trim(pSQL(Tools::getValue('city')));

        if ($strCity) {
            $objCart = $this->context->cart;
            $objAddressDelivery = new Address($objCart->id_address_delivery);
            $objAddressDelivery->city = $strCity;
            $objAddressDelivery->save();
        }

        echo Tools::jsonEncode(array(
            'result' => true
        ));

        return true;
    }
}
