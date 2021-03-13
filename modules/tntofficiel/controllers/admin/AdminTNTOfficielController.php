<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

// <TABNAME>Controller
class AdminTNTOfficielController extends ModuleAdminController
{
    // IFRAME URL.
    const URL_IFRAME = 'https://solutions-ecommerce.tnt.fr/login';

    /**
     * Constructor.
     */
    public function __construct()
    {
        TNTOfficiel_Logstack::log();

        // Bootstrap enable.
        $this->bootstrap = true;

        parent::__construct();
    }

    /**
     * Display page (middleware iframe).
     */
    public function display()
    {
        TNTOfficiel_Logstack::log();

        $arrTNTCredentials = TNTOfficiel_Credentials::getCredentials();

        $this->context->smarty->assign(array(
            'accountURL' => AdminTNTOfficielController::URL_IFRAME.'?username='.$arrTNTCredentials['identity']
                .'&account='.$arrTNTCredentials['merchant_number'].'&password='.$arrTNTCredentials['password'],
            'logoURL' => $this->module->getPathUri().'views/img/logo/224x124.png',
            'langTitle' => $this->l('Open in a new tab'),
            'langButton' => $this->l('Connection'),
        ));

        // _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.'/views/templates/admin/'
        // default : 'content.tpl';
        $this->template = 'AdminTNTOfficiel.tpl';

        return parent::display();
    }

    /**
     * {@inheritdoc}
     */
    public function createTemplate($tpl_name)
    {
        TNTOfficiel_Logstack::log();

        if (file_exists($this->getTemplatePath().$tpl_name) && $this->viewAccess()) {
            return $this->context->smarty->createTemplate($this->getTemplatePath().$tpl_name, $this->context->smarty);
        }

        return parent::createTemplate($tpl_name);
    }


    /**
     * Generate the manifest for an order (download).
     */
    public function processGetManifest()
    {
        TNTOfficiel_Logstack::log();

        $objManifestPDF = new TNTOfficiel_ManifestPDFCreator();
        $intOrderID = (int)Tools::getValue('id_order');
        $arrOrderIDList = array($intOrderID);
        $objManifestPDF->createManifest($arrOrderIDList);

        // We want to be sure that displaying PDF is the last thing this controller will do.
        exit;
    }

    /**
     * Get BT for an order (download).
     * /<ADMIN>/index.php?controller=AdminTNTOfficiel&action=getBT&id_order=<ID>
     */
    public function processGetBT()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('id_order');
        $objPSOrder = new Order($intOrderID);
        // Check if it's a tnt order.
        if (TNTOfficiel_Carrier::isTNTOfficielCarrierID($objPSOrder->id_carrier)) {
            // Load TNT order info for it's ID.
            $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
            if ($objTNTOrderModel !== null) {
                $strPDFBTPath = _PS_MODULE_DIR_.TNTOfficiel::PATH_MEDIA_PDF_BT;
                $strBTFileName = $objTNTOrderModel->bt_filename;
                $strPDFBTLocation = $strPDFBTPath.$strBTFileName;
                if ($strBTFileName && filesize($strPDFBTLocation) > 0) {
                    TNTOfficiel_Tools::download($strPDFBTLocation);
                }
            }
        }

        // We want to be sure that displaying PDF is the last thing this controller will do.
        exit;
    }

    /**
     * Downloads an archive containing all the logs files.
     * /<ADMIN>/index.php?controller=AdminTNTOfficiel&action=downloadLogs
     * /modules/tntofficiel/log/logs.zip
     */
    public function processDownloadLogs()
    {
        TNTOfficiel_Logstack::log();

        $strLogPath = _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.DIRECTORY_SEPARATOR.TNTOfficiel::PATH_LOG;
        $strZipFileName = 'logs.zip';
        $strZipLocation = $strLogPath.$strZipFileName;

        // Remove existing file archive.
        if (file_exists($strZipLocation)) {
            @unlink($strZipLocation);
        }

        // Create Zip file.
        TNTOfficiel_Logger::getZip($strZipLocation);
        // Download and exit.
        TNTOfficiel_Tools::download($strZipLocation);
    }

    /**
     * Renew all the TNT current carriers.
     * /<ADMIN>/index.php?controller=AdminTNTOfficiel&action=renewCurrentCarriers&ajax=true
     */
    public function displayAjaxRenewCurrentCarriers()
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;
        $objCookie = $objContext->cookie;

        $arrResult = array(
            'result' => true
        );

        // Create the TNT carrier (true to renew carrier).
        if (!TNTOfficiel_Carrier::installAllCurrentCarrier(true)) {
            $arrResult['result'] = false;
            $objCookie->TNTOfficielError = 'errorUnknow';
        } else {
            $objCookie->TNTOfficielSuccess = 'successRenewCarriers';
        }

        $objCookie->write();

        $arrResult['carriers'] = array_flip(TNTOfficiel_Carrier::getAllCurrentCarrierID());

        echo Tools::jsonEncode($arrResult);

        return true;
    }

    /**
     * Add a parcel.
     *
     * @return array
     */
    public function displayAjaxAddParcel()
    {
        TNTOfficiel_Logstack::log();

        $fltWeight = (float)Tools::getValue('weight');
        $intOderID = (int)Tools::getValue('orderId');

        $arrResult = array();
        try {
            $arrResult['parcel'] = TNTOfficiel_Parcel::addParcel($intOderID, $fltWeight, false);
            $arrResult['result'] = true;
        } catch (TNTOfficiel_MaxPackageWeightException $objException) {
            $arrResult['result'] = false;
            $arrResult['error'] = $objException->getMessage();
        } catch (Exception $objException) {
            TNTOfficiel_Logger::logException($objException);
            $arrResult['result'] = false;
        }

        echo Tools::jsonEncode($arrResult);

        return true;
    }

    /**
     * Remove a parcel.
     *
     * @return array
     */
    public function displayAjaxRemoveParcel()
    {
        TNTOfficiel_Logstack::log();

        $intParcelID = (int)Tools::getValue('parcelId');

        $arrResult = array();
        try {
            $arrResult['result'] = TNTOfficiel_Parcel::removeParcel($intParcelID);
        } catch (Exception $objException) {
            TNTOfficiel_Logger::logException($objException);
            $arrResult['result'] = false;
        }

        echo Tools::jsonEncode($arrResult);

        return true;
    }

    /**
     * Update a parcel.
     *
     * @return array
     */
    public function displayAjaxUpdateParcel()
    {
        TNTOfficiel_Logstack::log();

        $intParcelID = (int)Tools::getValue('parcelId');
        $fltWeight = (float)Tools::getValue('weight');
        $intOderID = (int)Tools::getValue('orderId');

        try {
            $arrResult = TNTOfficiel_Parcel::updateParcel($intParcelID, $fltWeight, $intOderID);
        } catch (TNTOfficiel_MaxPackageWeightException $objException) {
            $arrResult['result'] = false;
            $arrResult['error'] = $objException->getMessage();
        } catch (Exception $objException) {
            TNTOfficiel_Logger::logException($objException);
            $arrResult['result'] = false;
        }

        echo Tools::jsonEncode($arrResult);

        return true;
    }

    /**
     *  Checks the shipping.
     *
     * @return bool
     */
    public function displayAjaxCheckShippingDateValid()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('orderId');
        $strShippingDate = pSQL(Tools::getValue('shippingDate'));

        $arrPostDate = explode('/', $strShippingDate);
        $strFormatedShippingDate = $arrPostDate[2].'-'.$arrPostDate[1].'-'.$arrPostDate[0];

        $arrMDWShippingDate = TNTOfficiel_Order::checkSaveShipmentDate($intOrderID, $strFormatedShippingDate);
        if (!is_array($arrMDWShippingDate)) {
            echo Tools::jsonEncode(array('error' => $arrMDWShippingDate));

            return false;
        }
        if (array_key_exists('error', $arrMDWShippingDate)) {
            if ($arrMDWShippingDate['error'] == 1) {
                echo Tools::jsonEncode(array('error' => $arrMDWShippingDate['message']));

                return false;
            } else {
                $arrMDWShippingDate['shippingDate'] = $strFormatedShippingDate;
            }
        }

        // Load TNT order info or create a new one for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID);
        if ($objTNTOrderModel === null) {
            echo Tools::jsonEncode(array('error' => 'Unable to load shipping date for id #'.$intOrderID));

            return false;
        }

        $objTNTOrderModel->shipping_date = $arrMDWShippingDate['shippingDate'];
        $objTNTOrderModel->due_date = $arrMDWShippingDate['dueDate'];
        $objTNTOrderModel->save();

        if (!$arrMDWShippingDate['dueDate']) {
            $arrResult = array();
        } else {
            $tempDate = explode('-', $arrMDWShippingDate['dueDate']);
            $arrResult = array('dueDate' => $tempDate[2].'/'.$tempDate[1].'/'.$tempDate[0]);
        }

        if (array_key_exists('message', $arrMDWShippingDate)) {
            $arrResult['mdwMessage'] = $arrMDWShippingDate['message'];
        }

        echo Tools::jsonEncode($arrResult);

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

        $objContext = $this->context;

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
                    $objContext->shop->id
                );

                // If city/postcode correct.
                if ($boolMDWCheckResult) {
                    $arrResult['postcode'] = true;
                    $arrResult['cities'] = true;
                } else {
                    // Get cities from the middleware from the given postal code.
                    $arrMDWCitiesResult = TNTOfficiel_JsonRPCClient::getCities(
                        $strPostCode,
                        $objContext->shop->id
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

        $intOrderID = (int)Tools::getValue('id_order');
        $objPSOrder = new Order($intOrderID);

        $deliveryAddress = new Address($objPSOrder->id_address_delivery);
        $arrMDWCitiesResult = TNTOfficiel_JsonRPCClient::getCities(
            trim($deliveryAddress->postcode),
            $objPSOrder->id_shop
        );

        $boolCityPostCodeIsValid = TNTOfficiel_Address::checkPostCodeCityForCart($objPSOrder);

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

        $intOrderID = (int)Tools::getValue('id_order');
        $strCity = trim(pSQL(Tools::getValue('city')));

        if ($strCity && $intOrderID > 0) {
            $objPSOrder = new Order($intOrderID);
            $objAddressDelivery = new Address($objPSOrder->id_address_delivery);
            $objAddressDelivery->city = $strCity;
            $objAddressDelivery->save();
        }

        echo Tools::jsonEncode(array(
            'result' => true
        ));

        return true;
    }

    /**
     * Store Extra Information of Customer Cart Delivery Address.
     *
     * @return string
     */
    public function displayAjaxStoreDeliveryInfo()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('id_order');
        $objCart = Cart::getCartByOrderId($intOrderID);

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
     * Get the relay points popup via Ajax.
     * DROPOFFPOINT (Commerçant partenaire) : XETT
     */
    public function displayAjaxBoxRelayPoints()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('id_order');
        $objCart = Cart::getCartByOrderId($intOrderID);

        echo TNTOfficiel_Address::getBoxRelayPoints($objCart);

        return true;
    }

    /**
     * Get the repositories popup via Ajax.
     * DEPOT (Agence TNT) : PEX
     */
    public function displayAjaxBoxDropOffPoints()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('id_order');
        $objCart = Cart::getCartByOrderId($intOrderID);

        echo TNTOfficiel_Address::getBoxDropOffPoints($objCart);

        return true;
    }


    /**
     * Save delivery point XETT or PEX info.
     *
     * @return bool
     */
    public function displayAjaxSaveProductInfo()
    {
        TNTOfficiel_Logstack::log();

        $strDeliveryPoint = (string)Tools::getValue('product');
        $strDeliveryPointQS = TNTOfficiel_Tools::inflate($strDeliveryPoint);
        // Don't use JSON, but QueryString to get array only.
        parse_str($strDeliveryPointQS, $arrDeliveryPoint);

        // Check code exist.
        if (!array_key_exists('xett', $arrDeliveryPoint) && !array_key_exists('pex', $arrDeliveryPoint)) {
            return false;
        }

        $intOrderID = (int)Tools::getValue('id_order');
        $objCart = Cart::getCartByOrderId($intOrderID);
        $intCartID = (int)$objCart->id;

        // Load TNT cart info or create a new one for it's ID.
        $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID);
        if ($objTNTCartModel === null) {
            return false;
        }

        $objTNTCartModel->setDeliveryPoint($arrDeliveryPoint);

        /*
         * Save delivery code to order.
         */

        // Load TNT order info or create a new one for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID);
        if ($objTNTOrderModel === null) {
            return false;
        }

        // Create Address for delivery point.
        // DEPOT (Agence TNT) : PEX
        // DROPOFFPOINT (Commerçant artenaire) : XETT
        if (isset($arrDeliveryPoint['pex']) && strpos($objTNTOrderModel->getCarrierCode(), 'DEPOT')) {
            TNTOfficiel_Order::createNewAddress($arrDeliveryPoint, $intOrderID);
            $objTNTOrderModel->carrier_pex = $arrDeliveryPoint['pex'];
        } elseif (isset($arrDeliveryPoint['xett']) && strpos($objTNTOrderModel->getCarrierCode(), 'DROPOFFPOINT')) {
            TNTOfficiel_Order::createNewAddress($arrDeliveryPoint, $intOrderID);
            $objTNTOrderModel->carrier_xett = $arrDeliveryPoint['xett'];
        }

        // Save TNT order.
        return $objTNTOrderModel->save();
    }

    /**
     * Display the tracking popup.
     *
     * @return bool
     */
    public function displayAjaxTracking()
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)Tools::getValue('orderId');

        if (TNTOfficiel_Parcel::displayTrackingPopUp($intOrderID)) {
            return true;
        }

        // 404 fallback.
        Controller::getController('AdminNotFoundController')->run();

        return false;
    }
}
