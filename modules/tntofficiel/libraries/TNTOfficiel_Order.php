<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_Order
{
    /**
     * The directory where the BT are stored.
     */
    const BT_DIR = 'tnt_media/media/bt';

    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Create a new address and assign it to the order as the deliveray address.
     *
     * @param $arrArgDeliveryPoint
     * @param $intArgOrderID
     *
     * @return int The new address ID used for Order.
     */
    public static function createNewAddress($arrArgDeliveryPoint, $intArgOrderID)
    {
        TNTOfficiel_Logstack::log();

        $strRepoType = 'pex';
        if (array_key_exists('xett', $arrArgDeliveryPoint) && isset($arrArgDeliveryPoint['xett'])) {
            $strRepoType = 'xett';
        }

        $objPSOrder = new Order($intArgOrderID);
        $objAddressOld = new Address($objPSOrder->id_address_delivery);
        $objAddressNew = new Address();
        // Int. Required.
        $objAddressNew->id_country = (int)Context::getContext()->country->id;
        // Int.
        $objAddressNew->id_customer = 0;
        // Int.
        $objAddressNew->id_manufacturer = 0;
        // Int.
        $objAddressNew->id_supplier = 0;
        // Int.
        $objAddressNew->id_warehouse = 0;
        // Str 32. Required.
        $objAddressNew->alias = TNTOfficiel::MODULE_NAME;
        // Str 32. Required.
        $objAddressNew->lastname = $objAddressOld->lastname;
        // Str 32. Required.
        $objAddressNew->firstname = $objAddressOld->firstname;
        // Str 64.
        $objAddressNew->company = sprintf('%s - %s', $arrArgDeliveryPoint[$strRepoType], $arrArgDeliveryPoint['name']);
        // Str 128. Required.
        $objAddressNew->address1 = ($strRepoType === 'xett') ?
            $arrArgDeliveryPoint['address'] : $arrArgDeliveryPoint['address1'];
        // Str 128.
        $objAddressNew->address2 = ($strRepoType === 'xett') ? '' : $arrArgDeliveryPoint['address2'];
        // Str 64.  Required.
        $objAddressNew->city = trim($arrArgDeliveryPoint['city']);
        // Str 12.
        $objAddressNew->postcode = trim($arrArgDeliveryPoint['postcode']);

        // Copy required fields if destination is empty.
        $arrAddressRequiredFields = $objAddressNew->getFieldsRequiredDatabase();
        if (is_array($arrAddressRequiredFields)) {
            foreach ($arrAddressRequiredFields as $arrRowItem) {
                $strFieldName = pSQL($arrRowItem['field_name']);
                if (is_object($objAddressOld) && property_exists($objAddressOld, $strFieldName)
                    && is_object($objAddressNew) && property_exists($objAddressNew, $strFieldName)
                    && Tools::isEmpty($objAddressNew->$strFieldName)
                ) {
                    $objAddressNew->$strFieldName = $objAddressOld->$strFieldName;
                }
            }
        }

        // Save.
        $objAddressNew->save();

        // Assign the new delivery address to the order.
        $objPSOrder->id_address_delivery = (int)$objAddressNew->id;
        $objPSOrder->save();

        return (int)$objPSOrder->id_address_delivery;
    }

    /**
     * Send a shipment request to the middleware.
     *
     * @param $objArgOrder
     *
     * @return bool|string true if success, string if an error message.
     */
    public static function saveShipment($objArgOrder)
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)$objArgOrder->id;

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        if ($objTNTOrderModel === null) {
            return 'Order data not found for id #'.$intOrderID;
        }
        // If order already shipped.
        if ($objTNTOrderModel->isShipped()) {
            return 'Order is already shipped for id #'.$intOrderID;
        }

        // Get params (no timestamp).
        $arrParams = TNTOfficiel_Order::getMDWParam($objArgOrder, $objTNTOrderModel, $objTNTOrderModel->shipping_date);
        if (is_string($arrParams)) {
            return (string)$arrParams;
        }

        // Get Middleware Response.
        $arrResponse = TNTOfficiel_JsonRPCClient::request('saveShipment', $arrParams);
        // If request fail.
        if ($arrResponse === null) {
            return 'error-tnt-ws: request error.';
        } elseif (is_string($arrResponse)) {
            return (string)$arrResponse;
        }

        // Save the Bon de Transport.
        TNTOfficiel_Order::saveBT($arrResponse['bt'], $objArgOrder);
        // Save Tracking URL.
        try {
            // Saves the tracking URL for each parcel.
            TNTOfficiel_Parcel::saveTrackingUrls($arrResponse['parcels'], $intOrderID);
            $i = 0;
            foreach ($arrResponse['parcels'] as $arrParcelItem) {
                // Save first parcel number for Prestahop.
                if ($i === 0) {
                    $objArgOrder->shipping_number = $arrParcelItem['number'];
                    $objArgOrder->update();
                    // Update order_carrier.
                    $objPSOrderCarrier = new OrderCarrier((int)$objArgOrder->getIdOrderCarrier());
                    $objPSOrderCarrier->tracking_number = $arrParcelItem['number'];
                    $objPSOrderCarrier->update();
                }
                ++$i;
            }
        } catch (Exception $objException) {
            TNTOfficiel_Logger::logException($objException);
            return $objException->getMessage();
        }
        // Save Pickup Number.
        $objTNTOrderModel->pickup_number = (string)$arrResponse['pickUpNumber'];
        $objTNTOrderModel->save();

        return true;
    }

    /**
     * Saves the BT in the file system.
     *
     * @param $strBTContent
     * @param $objArgOrder
     *
     * @return bool
     */
    private static function saveBT($strBTContent, $objArgOrder)
    {
        TNTOfficiel_Logstack::log();

        // Creates the directory if not exists.
        if (!is_dir(_PS_MODULE_DIR_.TNTOfficiel_Order::BT_DIR)) {
            mkdir(_PS_MODULE_DIR_.TNTOfficiel_Order::BT_DIR, 0777, true);
        }

        $strDate = date('Y-m-d_H-i-s');
        $strBTFilename = sprintf('BT_%s_%s.pdf', $objArgOrder->reference, $strDate);

        $intOrderID = (int)$objArgOrder->id;

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        if ($objTNTOrderModel === null) {
            return false;
        }

        // Create the file.
        $boolBTSaved = (bool)file_put_contents(
            _PS_MODULE_DIR_.TNTOfficiel_Order::BT_DIR.DIRECTORY_SEPARATOR.$strBTFilename,
            utf8_decode($strBTContent)
        );

        if (!$boolBTSaved) {
            return false;
        }

        $objTNTOrderModel->bt_filename = $strBTFilename;
        $objTNTOrderModel->is_shipped = true;

        return $objTNTOrderModel->save();
    }

    /**
     * Get the params.
     *
     * @param $objArgOrder
     * @param $strShippingDate
     *
     * @return array|string
     */
    private static function getMDWParam($objArgOrder, $objArgTNTOrderModel, $strShippingDate = '0000-00-00')
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)$objArgOrder->id;
        $intShopID = (int)$objArgOrder->id_shop;
        $intAddressID = (int)$objArgOrder->id_address_delivery;
        $intCartID = (int)$objArgOrder->id_cart;

        $strTablePrefix = _DB_PREFIX_;
        $strSQLSelectTNTAddressData = <<<SQL
SELECT a.*, tc.*
FROM `${strTablePrefix}orders` o
INNER JOIN `${strTablePrefix}address` a on a.id_address = o.id_address_delivery
INNER JOIN `${strTablePrefix}tntofficiel_cart` tc on tc.id_cart = o.id_cart
WHERE a.id_address = ${intAddressID} and tc.id_cart = ${intCartID};
SQL;

        $arrTNTAddress = Db::getInstance()->getRow($strSQLSelectTNTAddressData, false);
        if (!$arrTNTAddress || count($arrTNTAddress) == 0) {
            return 'No address was found for the address '.$intAddressID.' - '.$intCartID;
        }

        $arrTNTParcelList = TNTOfficiel_Parcel::getParcelsFromAnOrder($intOrderID);
        $arrParcelsData = array();
        foreach ($arrTNTParcelList as $arrParcelInfoItem) {
            $arrParcelsData[] = array(
                'reference' => $objArgOrder->reference,
                'weight' => $arrParcelInfoItem['weight'],
            );
        }

        $typeId = (empty($objArgTNTOrderModel->carrier_xett)) ?
            $objArgTNTOrderModel->carrier_pex : $objArgTNTOrderModel->carrier_xett;

        $fltTotalInclTax = (float)$objArgOrder->total_paid_tax_incl;
        $fltTotalAllRelInclTax = (float)$objArgOrder->getOrdersTotalPaid();
        $fltTotalPaid = (float)$objArgOrder->total_paid_real;
        $fltTotalAllRelPaid = (float)$objArgOrder->getTotalPaid();

        $fltTotalToPay = $fltTotalInclTax - $fltTotalPaid;
        $fltTotalAllRelToPay = $fltTotalAllRelInclTax - $fltTotalAllRelPaid;

        $fltPaybackAmount = (string)max(min($fltTotalToPay, $fltTotalAllRelToPay), 0.0);

        $arrParams = array(
            'store' => $intShopID,
            'merchant' => TNTOfficiel_Credentials::getCredentials(),
            'product' => $objArgTNTOrderModel->getCarrierCode(),
            'recipient' => array(
                'xett' => $objArgTNTOrderModel->carrier_xett,
                'pex' => $objArgTNTOrderModel->carrier_pex,
                'firstname' => $arrTNTAddress['firstname'],
                'lastname' => $arrTNTAddress['lastname'],
                'address1' => Tools::substr($arrTNTAddress['address1'], 0, 32),
                'address2' => Tools::substr($arrTNTAddress['address2'], 0, 32),
                'post_code' => trim($arrTNTAddress['postcode']),
                'city' => trim($arrTNTAddress['city']),
                'email' => $arrTNTAddress['customer_email'],
                'phone' => $arrTNTAddress['customer_mobile'],
                'access_code' => $arrTNTAddress['address_accesscode'],
                'floor' => $arrTNTAddress['address_floor'],
                'building' => $arrTNTAddress['address_building'],
                'name' => trim($arrTNTAddress['company']),
                'typeId' => $typeId,
            ),
            'parcels' => $arrParcelsData,
            'paybackAmount' => $fltPaybackAmount
        );

        if ($strShippingDate && $strShippingDate !== '0000-00-00') {
            $arrParams['shipping_date'] = $strShippingDate;
        }

        return $arrParams;
    }

    /**
     * Get the first available shipping date.
     * REGULAR :
     * - shippingDate : the date of the day or the next day if the pickup driver time is over
     * - cutOffTime : the pickup driver time
     * OCCASIONAL :
     * - shippingDate and cutOffTime from WS getPickupContext(zipCode, city)
     *
     * @param $intArgOrderID
     *
     * @return array|string string if an error message.
     */
    public static function getShippingDate($intArgOrderID)
    {
        TNTOfficiel_Logstack::log();

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intArgOrderID, false);
        if ($objTNTOrderModel === null) {
            return 'Order data not found for id #'.$intArgOrderID;
        }
        // If order already shipped.
        if ($objTNTOrderModel->isShipped()) {
            return 'Order is already shipped for id #'.$intArgOrderID;
        }

        $objPSOrder = new Order($intArgOrderID);

        // Get params (no timestamp).
        $arrParams = TNTOfficiel_Order::getMDWParam($objPSOrder, $objTNTOrderModel);
        if (is_string($arrParams)) {
            return (string)$arrParams;
        }

        // Get Middleware Response.
        $arrResponse = TNTOfficiel_JsonRPCClient::request('getShippingDate', $arrParams);
        // If request fail.
        if ($arrResponse === null) {
            return 'error-tnt-ws: request error.';
        } elseif (is_string($arrResponse)) {
            return (string)$arrResponse;
        }

        return (array)$arrResponse;
    }

    /**
     * Check if process to save shipment is ok using the saved or first available shipping date.
     * Then update shipping date in DB if available.
     *
     * @param $intArgOrderID
     *
     * @return array shippingDate and dueDate from feasibility if ok for shipment.
     */
    public static function updateShippingDate($intArgOrderID)
    {
        TNTOfficiel_Logstack::log();

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intArgOrderID, false);
        if ($objTNTOrderModel === null) {
            return array('error' => 1, 'message' => 'Order data not found for id #'.$intArgOrderID);
        }
        if ($objTNTOrderModel->isShipped()) {
            return array('error' => 0, 'message' => 'Order is already shipped for id #'.$intArgOrderID);
        }

        // Default.
        $arrShippingDateResult = array('error' => 1, 'message' => 'Unable to get a new shipping date.');

        // If existing shipping date.
        if ($objTNTOrderModel->shipping_date !== '0000-00-00') {
            // check the existing shipping date.
            $arrShippingDateResult = TNTOfficiel_Order::checkSaveShipmentDate(
                $intArgOrderID,
                $objTNTOrderModel->shipping_date
            );
        }

        // If no saved shipping date or check not succeed
        if (!is_array($arrShippingDateResult)
            || !array_key_exists('shippingDate', $arrShippingDateResult)
            || !array_key_exists('dueDate', $arrShippingDateResult)
        ) {
            // try using the first available shipping date.
            $arrShippingDate = TNTOfficiel_Order::getShippingDate($intArgOrderID);
            // If the response is a string, there is an error.
            if (is_string($arrShippingDate)) {
                return array('error' => 1, 'message' => $arrShippingDate);
            }
            $arrShippingDateResult = TNTOfficiel_Order::checkSaveShipmentDate(
                $intArgOrderID,
                $arrShippingDate['shippingDate']
            );
        }

        $objDateTimeToday = new DateTime('midnight', new DateTimeZone('UTC'));
        $strDateStart = $objDateTimeToday->format('Y-m-d');

        // If succeed.
        if (is_array($arrShippingDateResult)
            && array_key_exists('shippingDate', $arrShippingDateResult)
            && array_key_exists('dueDate', $arrShippingDateResult)
        ) {
            // Update shipping & due date.
            $objTNTOrderModel->shipping_date = $arrShippingDateResult['shippingDate'];
            $objTNTOrderModel->due_date = $arrShippingDateResult['dueDate'];
            $objTNTOrderModel->start_date = $strDateStart;
            $objTNTOrderModel->save();
        } else {
            // Start date = today
            $objTNTOrderModel->start_date = $strDateStart;
            $objTNTOrderModel->save();
        }

        return $arrShippingDateResult;
    }

    /**
     * Check if process to save shipment is ok using a given shipping date.
     *
     * @param $intArgOrderID
     * @param $shippingDate
     *
     * @return array shippingDate and dueDate from feasibility or error information.
     */
    public static function checkSaveShipmentDate($intArgOrderID, $shippingDate)
    {
        TNTOfficiel_Logstack::log();

        $objPSOrder = new Order($intArgOrderID);

        // If carrier is not belonging to module.
        if (!TNTOfficiel_Carrier::isTNTOfficielCarrierID($objPSOrder->id_carrier)) {
            return array('error' => 1, 'message' => 'Unrecognized carrier');
        }

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intArgOrderID, false);
        if ($objTNTOrderModel === null) {
            return array('error' => 1, 'message' => 'Order data not found for id #'.$intArgOrderID);
        }
        // If order already shipped.
        if ($objTNTOrderModel->isShipped()) {
            return array('error' => 0, 'message' => 'Order is already shipped for id #'.$intArgOrderID);
        }

        // Get params (no timestamp).
        $arrParams = TNTOfficiel_Order::getMDWParam($objPSOrder, $objTNTOrderModel, $shippingDate);
        if (is_string($arrParams)) {
            return array('error' => 1, 'message' => (string)$arrParams);
        }

        // Get Middleware Response.
        $arrResponse = TNTOfficiel_JsonRPCClient::request('checkSaveShipment', $arrParams);
        // If request fail.
        if ($arrResponse === null) {
            return array('error' => 1, 'message' => 'error-tnt-ws: request error.');
        } elseif (is_string($arrResponse)) {
            return array('error' => 1, 'message' => (string)$arrResponse);
        }

        return (array)$arrResponse;
    }

    /**
     * @param $objArgOrder
     *
     * @return array
     */
    public static function checkShippingDateBeforeUpdateOrderStatus($objArgOrder)
    {
        TNTOfficiel_Logstack::log();

        $arrResult = array(
            'errors' => array(),
            'warnings' => array()
        );

        // If module not ready.
        if (!TNTOfficiel::isReady()) {
            $arrResult['warnings'][] = 'Module is not ready';

            return $arrResult;
        }

        // check the shipping date.
        $arrShippingDateResult = TNTOfficiel_Order::updateShippingDate($objArgOrder->id);

        // if
        if (is_array($arrShippingDateResult)) {
            if (array_key_exists('error', $arrShippingDateResult)) {
                // If true error.
                if ($arrShippingDateResult['error'] == 1) {
                    $arrResult['errors'][] = $arrShippingDateResult['message'];
                } else {
                    // If normal error.
                    $arrResult['warnings'][] = $arrShippingDateResult['message'];
                }
            }
        } else {
            // If request fail ?
            $arrResult['errors'][] = 'Communication Error';
        }

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($objArgOrder->id, false);
        if ($objTNTOrderModel === null) {
            $arrResult['errors'][] = 'Order data not found for id #'.$objArgOrder->id;
        } elseif ($objTNTOrderModel->shipping_date === '0000-00-00') {
            // If no existing shipping date.
            $arrResult['errors'][] = 'Shipping date is missing';
        }

        return $arrResult;
/*
        if (count($arrResult['errors']) > 0) {
            return;
        }

        if (count($arrResult['warnings']) > 0) {
            //
        }
*/
    }
}
