<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_Address
{
    /**
     * @var array
     */
    protected static $arrDays = array(
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday',
    );

    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Check the postcode/city validity for a cart.
     *
     * @param Cart $objArgCart
     *
     * @return bool
     */
    public static function checkPostCodeCityForCart($objArgCart)
    {
        TNTOfficiel_Logstack::log();

        $boolCityPostCodeIsValid = true;

        // Get delivery address ID of cart.
        $intAddressID = $objArgCart->id_address_delivery;
        // A delivery address is required.
        if (!empty($intAddressID) && $intAddressID == (int)$intAddressID && (int)$intAddressID > 0) {
            $objAddressDelivery = new Address((int)$intAddressID);
            // If delivery address object available.
            if (Validate::isLoadedObject($objAddressDelivery)) {
                // Get delivery address country code.
                $strISOCountryCode = Country::getIsoById($objAddressDelivery->id_country);
                // Only for France, check if the city match the postcode.
                if ($strISOCountryCode == 'FR') {
                    $boolMDWCheckResult = TNTOfficiel_JsonRPCClient::testCity(
                        trim($objAddressDelivery->postcode),
                        trim($objAddressDelivery->city),
                        $objArgCart->id_shop
                    );
                    $boolCityPostCodeIsValid = $boolMDWCheckResult;
                }
            }
        }

        return $boolCityPostCodeIsValid;
    }

    /**
     * Validate a Mobile Phone (FR,MC only).
     *
     * @param string $strArgISOCode The ISO Country Code.
     * @param string $strArgPhoneMobile The Mobile Phone Number.
     *
     * @return bool|string The Formated Mobile Phone String if valid, else false.
     */
    public static function validateMobilePhone($strArgISOCode, $strArgPhoneMobile)
    {
        TNTOfficiel_Logstack::log();

        if (!is_string($strArgISOCode)) {
            return false;
        }
        if (!is_string($strArgPhoneMobile)) {
            return false;
        }

        // Format apr pays.
        $arrPhoneFormatCountry = array(
            'FR' => array(
                'strCoutryCode' => '33',
                'strTrunkp' => '0',
                'strMobile' => '([67])([0-9]{8})'
            ),
            'MC' => array(
                'strCoutryCode' => '377',
                'strTrunkp' => '',
                'strMobile' => '([346])([0-9]{7,8})'
            )
        );

        $strISOCode = Tools::strtoupper($strArgISOCode);
        if (!array_key_exists($strISOCode, $arrPhoneFormatCountry)) {
            return false;
        }

        // Get Country Data.
        $arrPhoneFormat = $arrPhoneFormatCountry[ $strISOCode ];
        // Cleaning Phone Input.
        $strPhoneMobileClean = preg_replace('/[^+0-9()]/ui', '', $strArgPhoneMobile);
        // Root.
        $strRoot = '(?:(?:(?:\+|00)'.$arrPhoneFormat['strCoutryCode']
            .'(?:\('.$arrPhoneFormat['strTrunkp'].'\))?)|'.$arrPhoneFormat['strTrunkp'].')';

        if (preg_match('/^'.$strRoot.'('.$arrPhoneFormat['strMobile'].')$/ui', $strPhoneMobileClean, $matches)) {
            $strPhoneMobileID = $arrPhoneFormat['strTrunkp'].$matches[1];
            $strPhoneMobileIDLength = Tools::strlen($strPhoneMobileID);

            if ($strPhoneMobileIDLength < 1 || $strPhoneMobileIDLength > 63) {
                return false;
            }

            return $strPhoneMobileID;
        }

        return false;
    }


    /**
     * Store Extra Information of Customer Cart Delivery Address.
     *
     * @param string $strArgCustomerEmail
     * @param string $strArgCustomerMobile
     * @param string $strArgAddressBuilding
     * @param string $strArgAddressAccesscode
     * @param string $strArgAddressFloor
     *
     * @return array
     */
    public static function validateDeliveryInfo(
        $strArgCustomerEmail,
        $strArgCustomerMobile,
        $strArgAddressBuilding,
        $strArgAddressAccesscode,
        $strArgAddressFloor
    ) {
        TNTOfficiel_Logstack::log();

        $arrFormInput = array(
            'customer_email' => trim((string)$strArgCustomerEmail),
            'customer_mobile' => trim((string)$strArgCustomerMobile),
            'address_building' => trim((string)$strArgAddressBuilding),
            'address_accesscode' => trim((string)$strArgAddressAccesscode),
            'address_floor' => trim((string)$strArgAddressFloor)
        );

        $arrFormError = array();

        // Check if email is set and not empty.
        if (!isset($arrFormInput['customer_email']) || $arrFormInput['customer_email'] === '') {
            $arrFormError['customer_email'] = 'L\'email est obligatoire';
        }

        // Check if the email is valid.
        if (!filter_var($arrFormInput['customer_email'], FILTER_VALIDATE_EMAIL)) {
            // TNTOfficiel.translate.errorInvalidEMail
            $arrFormError['customer_email'] = 'L\'e-mail saisi n\'est pas valide';
        }

        // Check if mobile phone is set and not empty.
        if (!isset($arrFormInput['customer_mobile']) || $arrFormInput['customer_mobile'] === '') {
            $arrFormError['customer_mobile'] = 'Le Téléphone portable est obligatoire';
        } else {
            $arrFormInput['customer_mobile'] = preg_replace('/[\s.-]+/ui', '', $arrFormInput['customer_mobile']);
        }
        // Check if mobile phone is valid.
        $mxdPhoneValidated = TNTOfficiel_Address::validateMobilePhone('FR', $arrFormInput['customer_mobile']);
        if ($mxdPhoneValidated === false) {
            // TNTOfficiel.translate.errorInvalidPhoneNumber
            $arrFormError['customer_mobile'] = 'Le Téléphone portable doit être de 10 chiffres et commencer par 06 ou 07';
        } else {
            $arrFormInput['customer_mobile'] = $mxdPhoneValidated;
        }

        $arrFieldMaxLength = array(
            'customer_email' => array(
                'label' => 'Email de contact',
                'maxlength' => 80,
            ),
            'customer_mobile' => array(
                'label' => 'Téléphone',
                'maxlength' => 15,
            ),
            'address_building' => array(
                'label' => 'N° de batiment',
                'maxlength' => 3,
            ),
            'address_accesscode' => array(
                'label' => 'Code interphone',
                'maxlength' => 7,
            ),
            'address_floor' => array(
                'label' => 'Etage',
                'maxlength' => 2,
            ),
        );

        foreach ($arrFieldMaxLength as $strFieldName => $arrField) {
            if ($arrFormInput[ $strFieldName ]) {
                if (Tools::strlen($arrFormInput[ $strFieldName ]) > $arrField['maxlength']) {
                    $arrFormError[ $strFieldName ] = sprintf(
                        'Le champ "%s" doit avoir au maximum %s caractère(s)',
                        $arrField['label'],
                        $arrField['maxlength']
                    );
                }
            }
        }

        return array(
            'fields' => $arrFormInput,
            'errors' => $arrFormError,
            'length' => count($arrFormError)
        );
    }

    /**
     * Store Extra Information of Customer Cart Delivery Address.
     *
     * @param Cart $objArgCart
     * @param string $strArgCustomerEmail
     * @param string $strArgCustomerMobile
     * @param string $strArgAddressBuilding
     * @param string $strArgAddressAccesscode
     * @param string $strArgAddressFloor
     *
     * @return array
     */
    public static function storeDeliveryInfo(
        $objArgCart,
        $strArgCustomerEmail,
        $strArgCustomerMobile,
        $strArgAddressBuilding,
        $strArgAddressAccesscode,
        $strArgAddressFloor
    ) {
        TNTOfficiel_Logstack::log();

        $arrFormCartAddressValidate = TNTOfficiel_Address::validateDeliveryInfo(
            $strArgCustomerEmail,
            $strArgCustomerMobile,
            $strArgAddressBuilding,
            $strArgAddressAccesscode,
            $strArgAddressFloor
        );

        $boolStored = false;
        // If no errors.
        if ($arrFormCartAddressValidate['length'] === 0) {
            $intCartID = (int)$objArgCart->id;

            // Load TNT cart info or create a new one for it's ID.
            $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID);
            if ($objTNTCartModel !== null) {
                // ObjectModel self escape data with formatValue(). Do not double escape using pSQL.
                $objTNTCartModel->hydrate($arrFormCartAddressValidate['fields']);
                $boolStored = $objTNTCartModel->save();

                // Get delivery address ID of cart.
                $intAddressID = $objArgCart->id_address_delivery;
                // A delivery address is required.
                if (!empty($intAddressID) && $intAddressID == (int)$intAddressID && (int)$intAddressID > 0) {
                    $objAddressDelivery = new Address((int)$intAddressID);
                    // If delivery address object available.
                    if (Validate::isLoadedObject($objAddressDelivery)) {
                        // If cart customer mobile is valid, non empty but delivery address field is empty.
                        if (!array_key_exists('customer_mobile', $arrFormCartAddressValidate['errors'])
                            && $objTNTCartModel->customer_mobile && !$objAddressDelivery->phone_mobile
                        ) {
                            // Save mobile for next cart.
                            $objAddressDelivery->phone_mobile = $objTNTCartModel->customer_mobile;
                            $objAddressDelivery->save();
                        }
                    }
                }
            }
        }

        // Validated and stored in DB.
        $arrFormCartAddressValidate['stored'] = $boolStored;

        return $arrFormCartAddressValidate;
    }

    /**
     * Get the relay points popup.
     * DROPOFFPOINT (Commerçant partenaire) : XETT
     *
     * @param Cart $objArgCart
     *
     * @return mixed
     */
    public static function getBoxRelayPoints($objArgCart)
    {
        TNTOfficiel_Logstack::log();

        $intShopID = (int)$objArgCart->id_shop;

        $strTNTClickedCarrierCode = 'DROPOFFPOINT';
        $strPostCode = trim(pSQL(Tools::getValue('tnt_postcode')));
        $strCity = trim(pSQL(Tools::getValue('tnt_city')));

        if (!$strPostCode && !$strCity) {
            $objAddress = new Address((int)$objArgCart->id_address_delivery);
            $strPostCode = trim($objAddress->postcode);
            $strCity = trim($objAddress->city);
        }

        // Trying to get a DPL from carrier selected in cart.
        $strArgDPL = null;
        // Get the carriers from the middleware
        $objCustomer = new Customer($objArgCart->id_customer);
        $arrAvailableTNTCarriers = TNTOfficiel_Carrier::getMDWTNTCarrierList($objArgCart, $objCustomer);
        // Find
        if (!empty($arrAvailableTNTCarriers) && !empty($arrAvailableTNTCarriers['products'])) {
            // Get DPL from selected product (middleware).
            foreach ($arrAvailableTNTCarriers['products'] as $arrTNTCarrier) {
                // Found current TNT carrier code.
                if (strpos($arrTNTCarrier['id'], $strTNTClickedCarrierCode)) {
                    if ($arrTNTCarrier['due_date']) {
                        $strArgDPL = str_replace('-', '', $arrTNTCarrier['due_date']);
                    }
                    break;
                }
            }
        }

        // Call API to get list of repositories
        $arrMDWRelayPointsResult = TNTOfficiel_JsonRPCClient::getRelayPoints(
            $strPostCode,
            $strCity,
            $intShopID,
            $strArgDPL
        );
        $arrMDWCitiesResult = TNTOfficiel_JsonRPCClient::getCities($strPostCode, $intShopID);

        foreach ($arrMDWRelayPointsResult as $key => $item) {
            $arrMDWRelayPointsResult[ $key ]['schedule'] = TNTOfficiel_Address::getSchedules($item);
        }


        $objSmarty = Context::getContext()->smarty;
        // Get the relay points
        $objSmarty->assign(
            array(
                'carrier_code' => $strTNTClickedCarrierCode,
                'method_id' => 'relay_points',
                'method_name' => 'relay-points',
                'current_postcode' => $strPostCode,
                'current_city' => $strCity,
                'results' => $arrMDWRelayPointsResult,
                'cities' => $arrMDWCitiesResult,
            )
        );

        return $objSmarty->fetch(
            _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.
            '/views/templates/hook/displayCarrierList/displayAjaxBoxDeliveryPoints.tpl'
        );
    }

    /**
     * Get the repositories popup via Ajax.
     * DEPOT (Agence TNT) : PEX
     *
     * @param Cart $objArgCart
     *
     * @return mixed
     */
    public static function getBoxDropOffPoints($objArgCart)
    {
        TNTOfficiel_Logstack::log();

        $intShopID = (int)$objArgCart->id_shop;

        $strTNTClickedCarrierCode = 'DEPOT';
        $strPostCode = trim(pSQL(Tools::getValue('tnt_postcode')));
        $strCity = trim(pSQL(Tools::getValue('tnt_city')));

        if (!$strPostCode && !$strCity) {
            $objAddress = new Address((int)$objArgCart->id_address_delivery);
            $strPostCode = trim($objAddress->postcode);
            $strCity = trim($objAddress->city);
        }

        // Call API to get list of repositories
        $arrMDWRepositoriesResult = TNTOfficiel_JsonRPCClient::getRepositories($strPostCode, $intShopID);
        $arrMDWCitiesResult = TNTOfficiel_JsonRPCClient::getCities($strPostCode, $intShopID);
        foreach ($arrMDWRepositoriesResult as $key => $repository) {
            $arrMDWRepositoriesResult[ $key ]['schedule'] = TNTOfficiel_Address::getSchedules($repository);
        }


        $objSmarty = Context::getContext()->smarty;
        //
        $objSmarty->assign(
            array(
                'carrier_code' => $strTNTClickedCarrierCode,
                'method_id' => 'repositories',
                'method_name' => 'repositories',
                'current_postcode' => $strPostCode,
                'current_city' => $strCity,
                'results' => $arrMDWRepositoriesResult,
                'cities' => $arrMDWCitiesResult,
            )
        );

        return $objSmarty->fetch(
            _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME
            .'/views/templates/hook/displayCarrierList/displayAjaxBoxDeliveryPoints.tpl'
        );
    }

    /**
     * Returns an array with schedules for each days.
     *
     * @param array $arrParams Parameters of the function
     *
     * @return array
     */
    public static function getSchedules(array $arrParams = array())
    {
        TNTOfficiel_Logstack::log();

        $arrSchedules = array();

        if (array_key_exists('hours', $arrParams) && is_array($arrParams['hours'])) {
            $arrHours = $arrParams['hours'];
            $index = 0;
            // Position corresponds to the number of the day
            $position = 0;
            // Current day
            $day = null;
            // Part of the day
            $part = null;

            foreach ($arrHours as $strHour) {
                $strHour = trim($strHour);
                $day = TNTOfficiel_Address::$arrDays[$position];

                if (($index % 2 === 0) && ($index % 4 === 0)) {
                    $part = 'AM';
                } elseif (($index % 2 === 0) && ($index % 4 === 2)) {
                    $part = 'PM';
                }

                // Prepare the current Day
                if (!isset($arrSchedules[$day])) {
                    $arrSchedules[$day] = array();
                }

                // Prepare the current period of the current dya
                if (!isset($arrSchedules[$day][$part]) && $strHour) {
                    $arrSchedules[$day][$part] = array();
                }

                // If hours different from 0
                if ($strHour) {
                    // Add hour
                    $arrSchedules[$day][$part][] = $strHour;
                }

                ++$index;

                if ($index % 4 == 0) {
                    ++$position;
                }
            }
        }

        return $arrSchedules;
    }
}
