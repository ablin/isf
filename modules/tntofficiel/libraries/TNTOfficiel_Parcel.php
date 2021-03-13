<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_Parcel
{
    // URL de suivi des colis.
    const TRACKING_URL = 'https://www.tnt.fr/public/suivi_colis/recherche/visubontransport.do?bonTransport=';
    // Max weight (kg) per parcel for B2B (ENTREPRISE et DEPOT).
    const MAXWEIGHT_B2B = 30.0;
    // Max weight (kg) per parcel for B2C (NDIVIDUAL et DROPOFFPOINT).
    const MAXWEIGHT_B2C = 20.0;

    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Creates the parcels for an order.
     *
     * @param $objCart
     * @param $intArgOrderID
     *
     * @return bool
     */
    public static function createParcels($objCart, $intArgOrderID)
    {
        TNTOfficiel_Logstack::log();

        // Get the parcels
        $arrTNTParcelList = TNTOfficiel_Parcel::getParcelsFromAnOrder($intArgOrderID);

        // Already created.
        if (is_array($arrTNTParcelList) && count($arrTNTParcelList) > 0) {
            return false;
        }

        $fltMaxParcelWeight = TNTOfficiel_Parcel::getMaxPackageWeight($intArgOrderID);

        // Order the list of products by weight.
        $arrOrderedProductList = $objCart->getProducts();
        usort($arrOrderedProductList, array(__CLASS__, 'compareProductByWeight'));

        // Set all product in an array of products
        $arrProductUnitList = array();
        foreach ($arrOrderedProductList as $arrOrderedProduct) {
            $productQuantity = $arrOrderedProduct['quantity'];
            for ($i = 0; $i < $productQuantity; ++$i) {
                $arrProductUnitList[] = $arrOrderedProduct;
            }
        }

        // Init parcel list with one empty parcel.
        $arrParcelList = array(
            0.0
        );
        // Loop over each product.
        foreach ($arrProductUnitList as $arrProductUnit) {
            $boolProductAdded = false;
            // If no product weight.
            if ((float)$arrProductUnit['weight'] === 0.0) {
                // skip this one.
                continue;
            }
            // Loop over each parcel.
            foreach ($arrParcelList as /*$intParcelIndex =>*/ &$intParcelWeight) {
                // Check if parcel's weight exceeds the maximum parcel weight
                // Break the loop on parcel and loop again on product
                if ((($intParcelWeight + (float)$arrProductUnit['weight']) <= $fltMaxParcelWeight)
                    // If current parcel is empty, product weight out of range is added.
                    || $intParcelWeight === 0.0
                ) {
                    // Update the parcel's weight.
                    $intParcelWeight += $arrProductUnit['weight'];
                    $boolProductAdded = true;
                    break;
                }
            }
            unset($intParcelWeight);
            // If any product can't be added in the parcel.
            // We create another one and loop again on product
            if (!$boolProductAdded) {
                // add weight
                $arrParcelList[] = (float)$arrProductUnit['weight'];
            }
        }

        // Save the parcels in the database.
        foreach ($arrParcelList as $fltParcelWeight) {
            //insert into the parcel table
            TNTOfficiel_Parcel::addParcel($intArgOrderID, $fltParcelWeight, true);
        }

        return true;
    }

    /**
     * Compare two products by their weight.
     *
     * @param $productA
     * @param $productB
     *
     * @return int
     */
    public static function compareProductByWeight($productA, $productB)
    {
        TNTOfficiel_Logstack::log();

        if ($productA['weight'] == $productB['weight']) {
            return 0;
        }

        return ($productA['weight'] > $productB['weight']) ? -1 : 1;
    }

    /**
     * Get the parcels of an order.
     *
     * @param $intArgOrderID int
     *
     * @return array
     */
    public static function getParcelsFromAnOrder($intArgOrderID)
    {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)$intArgOrderID;

        $strTablePrefix = _DB_PREFIX_;
        $strSQLSelectTNTParcels = <<<SQL
SELECT *
FROM `${strTablePrefix}tntofficiel_order_parcels`
WHERE id_order = ${intOrderID};
SQL;
        $arrParcelsResult = Db::getInstance()->executeS($strSQLSelectTNTParcels);

        return $arrParcelsResult;
    }

    /**
     * Get Tracking URL for all parcels of an order.
     *
     * @param $intArgOrderID
     *
     * @return null|string. null if no tracking url available (not a TNT carrier, not shipped or no parcel numbers).
     */
    public static function getTrackingURLFromAnOrder($intArgOrderID)
    {
        TNTOfficiel_Logstack::log();

        $strTrackingURL = null;

        // If Order does not have a TNT carrier.
        if (!TNTOfficiel_Carrier::isTNTOfficielCarrierID($intArgOrderID)) {
            return $strTrackingURL;
        }

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intArgOrderID, false);
        // If order not found or not already shipped.
        if ($objTNTOrderModel === null || !$objTNTOrderModel->isShipped()) {
            return $strTrackingURL;
        }

        $arrParcelNumberList = array();

        // Get the parcels
        $arrTNTParcelList = TNTOfficiel_Parcel::getParcelsFromAnOrder($intArgOrderID);
        foreach ($arrTNTParcelList as $arrParcelInfoItem) {
            //$arrParcelInfoItem['tracking_url'];
            if (is_string($arrParcelInfoItem['parcel_number'])
                && Tools::strlen($arrParcelInfoItem['parcel_number']) > 0
            ) {
                $arrParcelNumberList[] = $arrParcelInfoItem['parcel_number'];
            }
        }

        if (count($arrParcelNumberList) > 0) {
            $strTrackingURL = TNTOfficiel_Parcel::TRACKING_URL.implode('%0d%0a', $arrParcelNumberList);
        }

        return $strTrackingURL;
    }


    /**
     * Get a parcel.
     * @param $intArgParcelID
     * @return mixed
     */
    public static function getParcel($intArgParcelID)
    {
        TNTOfficiel_Logstack::log();

        $strSQLParcel = 'SELECT * FROM '._DB_PREFIX_.'tntofficiel_order_parcels WHERE id_parcel = '.$intArgParcelID;

        return Db::getInstance()->executeS($strSQLParcel);
    }

    /**
     * Remove a parcel.
     *
     * @param $parcelId int
     *
     * @return bool
     */
    public static function removeParcel($parcelId)
    {
        TNTOfficiel_Logstack::log();

        return Db::getInstance()->delete('tntofficiel_order_parcels', 'id_parcel = '.(int)$parcelId);
    }

    /**
     * Add a parcel.
     *
     * @param $intArgOrderID int
     * @param $fltArgWeight float
     * @param $isOrderCreation
     *
     * @return array
     *
     * @throws TNTOfficiel_MaxPackageWeightException
     */
    public static function addParcel($intArgOrderID, $fltArgWeight, $isOrderCreation = true)
    {
        TNTOfficiel_Logstack::log();

        $fltMaxPackageWeight = TNTOfficiel_Parcel::getMaxPackageWeight($intArgOrderID);
        //Does not throw an exception when this is an order creation
        //the parcel can contains one product which weight exceeds the maximum weight
        if ((float)$fltArgWeight > $fltMaxPackageWeight && !$isOrderCreation) {
            throw new TNTOfficiel_MaxPackageWeightException(
                'Le poids d\'un colis ne peut dépasser '.$fltMaxPackageWeight.'Kg'
            );
        }

        Db::getInstance()->insert('tntofficiel_order_parcels', array(
            'id_order' => (int)$intArgOrderID,
            'weight' => max(round((float)$fltArgWeight, 1, PHP_ROUND_HALF_UP), 0.1),
        ));
        $intParcelID = Db::getInstance()->Insert_ID();

        return TNTOfficiel_Parcel::getParcel($intParcelID);
    }

    /**
     * Update a parcel.
     *
     * @param $parcelId int
     * @param $weight float
     * @param $intArgOrderID int
     *
     * @return bool
     *
     * @throws TNTOfficiel_MaxPackageWeightException
     */
    public static function updateParcel($parcelId, $weight, $intArgOrderID)
    {
        TNTOfficiel_Logstack::log();

        $fltMaxPackageWeight = TNTOfficiel_Parcel::getMaxPackageWeight($intArgOrderID);
        if ((float)$weight > $fltMaxPackageWeight) {
            throw new TNTOfficiel_MaxPackageWeightException(
                'Le poids d\'un colis ne peut dépasser '.$fltMaxPackageWeight.'Kg'
            );
        }
        $weight = max(round($weight, 1, PHP_ROUND_HALF_UP), 0.1);
        $result = array();
        $result['result'] = Db::getInstance()->update(
            'tntofficiel_order_parcels',
            array('weight' => (float)$weight),
            'id_parcel = '.(int)$parcelId
        );
        $result['weight'] = $weight;

        return $result;
    }

    /**
     * Get the maximum package weight for the order.
     *
     * @param $intArgOrderID
     *
     * @return float
     */
    private static function getMaxPackageWeight($intArgOrderID)
    {
        TNTOfficiel_Logstack::log();

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intArgOrderID, false);
        if ($objTNTOrderModel !== null) {
            $strCarrierCode = $objTNTOrderModel->getCarrierCode();
            // If carrier code is B2B.
            if ($strCarrierCode !== null
                && (strpos($strCarrierCode, 'ENTERPRISE') || strpos($strCarrierCode, 'DEPOT'))
            ) {
                return TNTOfficiel_Parcel::MAXWEIGHT_B2B;
            } else {
                return TNTOfficiel_Parcel::MAXWEIGHT_B2C;
            }
        }

        return min(TNTOfficiel_Parcel::MAXWEIGHT_B2B, TNTOfficiel_Parcel::MAXWEIGHT_B2C);
    }

    /**
     * Saves the tracking URL for each parcel.
     *
     * @param $arrArgParcelList
     * @param $intArgOrderID
     *
     * @throws Exception
     */
    public static function saveTrackingUrls($arrArgParcelList, $intArgOrderID)
    {
        TNTOfficiel_Logstack::log();

        $arrTNTParcelList = TNTOfficiel_Parcel::getParcelsFromAnOrder($intArgOrderID);

        $i = 0;
        foreach ($arrArgParcelList as $arrParcelInfoItem) {
            try {
                Db::getInstance()->update(
                    'tntofficiel_order_parcels',
                    array(
                        'tracking_url' => pSQL($arrParcelInfoItem['tracking_url']),
                        'parcel_number' => pSQL($arrParcelInfoItem['number']),
                    ),
                    'id_parcel = '.pSQL($arrTNTParcelList[$i]['id_parcel'])
                );
            } catch (Exception $objException) {
                TNTOfficiel_Logger::logException($objException);

                throw $objException;
            }
            ++$i;
        }
    }

    /**
     * Return All Status de display : depends on status -> maximum 5 status.
     *
     * @param $status
     *
     * @return array
     */
    protected static function getAllStatus($status)
    {
        TNTOfficiel_Logstack::log();

        $statusArray = array(
            1 => TNTOfficiel_Parcel::translate('Colis chez l’expéditeur'),
            2 => TNTOfficiel_Parcel::translate('Ramassage du Colis'),
            3 => TNTOfficiel_Parcel::translate('Acheminement'),
            4 => TNTOfficiel_Parcel::translate('Livraison en cours'),
            5 => TNTOfficiel_Parcel::translate('Livré'),
        );

        switch ($status) {
            case 6:
                $statusArray[5] = TNTOfficiel_Parcel::translate('Incident');
                break;
            case 7:
                $statusArray[5] = TNTOfficiel_Parcel::translate("Retourné à l'expéditeur");
                break;
        }

        return $statusArray;
    }

    /**
     * @param $parcel
     *
     * @return bool|int
     */
    protected static function getStatus($parcel)
    {
        TNTOfficiel_Logstack::log();

        $parcel = (array)$parcel;
        $statusLabel = isset($parcel['shortStatus']) ? $parcel['shortStatus'] : false;
        if (!$statusLabel) {
            return false;
        }

        $mapping = array(
            TNTOfficiel_Parcel::translate('En attente')                       => 1,
            '--' => 2,
            TNTOfficiel_Parcel::translate('En cours d\'acheminement')         => 3,
            TNTOfficiel_Parcel::translate('En cours de livraison')            => 4,
            TNTOfficiel_Parcel::translate('En agence TNT')                    => 4,
            TNTOfficiel_Parcel::translate('Récupéré à l\'agence TNT')         => 5,
            TNTOfficiel_Parcel::translate('Livré')                            => 5,
            TNTOfficiel_Parcel::translate('En attente de vos instructions')   => 6,
            TNTOfficiel_Parcel::translate('En attente d\'instructions')       => 6,
            TNTOfficiel_Parcel::translate('En attente d\'instructions')       => 6,
            TNTOfficiel_Parcel::translate('Incident de livraison')            => 6,
            TNTOfficiel_Parcel::translate('Incident intervention')            => 6,
            TNTOfficiel_Parcel::translate('Colis refusé par le destinataire') => 6,
            TNTOfficiel_Parcel::translate('Livraison reprogrammée')           => 6,
            TNTOfficiel_Parcel::translate('Prise de rendez-vous en cours')    => 6,
            TNTOfficiel_Parcel::translate('Prise de rendez-vous en cours')    => 6,
            TNTOfficiel_Parcel::translate('Problème douane')                  => 6,
            TNTOfficiel_Parcel::translate('Enlevé au dépôt')                  => 3,
            TNTOfficiel_Parcel::translate('En dépôt restant')                 => 3,
            TNTOfficiel_Parcel::translate('Retourné à l\'expéditeur')         => 7,
        );

        return isset($mapping[$statusLabel]) ? $mapping[$statusLabel] : 1;
    }

    /**
     * @param $parcel
     *
     * @return bool
     */
    protected static function getHistory($parcel)
    {
        TNTOfficiel_Logstack::log();

        if (!$parcel->events) {
            return false;
        }

        $history = array();
        $states = array(1 => 'request', 2 => 'process', 3 => 'arrival', 4 => 'deliveryDeparture', 5 => 'delivery');
        $events = (array)$parcel->events;
        foreach ($states as $idx => $state) {
            if ((isset($events[$state.'Center']) || isset($events[$state.'Date']))
                && Tools::strlen($events[$state.'Date'])
            ) {
                $history[$idx] = array(
                    'label' => TNTOfficiel_Parcel::getLabel($state),
                    'date' => isset($events[$state.'Date']) ? $events[$state.'Date'] : '',
                    'center' => isset($events[$state.'Center']) ? $events[$state.'Center'] : '',
                );
            }
        }

        return $history;
    }

    protected static function getLabel($state)
    {
        TNTOfficiel_Logstack::log();

        $labels = array(
            'request' => TNTOfficiel_Parcel::translate('Colis chez l’expéditeur'),
            'process' => TNTOfficiel_Parcel::translate('Ramassage du colis'),
            'arrival' => TNTOfficiel_Parcel::translate('Acheminement du colis'),
            'deliveryDeparture' => TNTOfficiel_Parcel::translate('Livraison du colis en cours'),
            'delivery' => TNTOfficiel_Parcel::translate('Livraison du colis'),
        );

        return isset($labels[$state]) ? $labels[$state] : '';
    }

    /**
     * Save the PDL.
     *
     * @param $data
     * @param $parcelId
     *
     * @return bool
     */
    protected static function savePdl($data, $parcelId)
    {
        TNTOfficiel_Logstack::log();

        $result = false;
        $pdl = TNTOfficiel_Parcel::getPdl($data);
        if ($pdl) {
            $result = Db::getInstance()->update(
                'tntofficiel_order_parcels',
                array('pdl' => pSQL($pdl)),
                'id_parcel = '.(int)$parcelId
            );
        }

        return $result;
    }

    /**
     * Get POD Url from parcel data.
     *
     * @param $data
     *
     * @return bool|string
     */
    protected static function getPdl($data)
    {
        TNTOfficiel_Logstack::log();

        $data = (array)$data;

        return isset($data['primaryPODUrl']) && $data['primaryPODUrl'] ? $data['primaryPODUrl'] :
            (isset($data['secondaryPODUrl']) && $data['secondaryPODUrl'] ? $data['secondaryPODUrl'] : false);
    }

    /**
     * get translation.
     *
     * @param $string
     *
     * @return string
     */
    public static function translate($string)
    {
        TNTOfficiel_Logstack::log();

        // TODO
        return Translate::getModuleTranslation(TNTOfficiel::MODULE_NAME, $string, 'parcelshelper');
    }


    /**
     * Get the tracking data for popup.
     *
     * @param $intOrderID
     *
     * @return array|null
     */
    public static function getTrackingData($intOrderID)
    {
        TNTOfficiel_Logstack::log();

        // Load TNT order info for it's ID.
        $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
        if ($objTNTOrderModel === null) {
            return null;
        }

        // Get parcels and for each parcel get its tracking data
        $arrTNTParcelList = TNTOfficiel_Parcel::getParcelsFromAnOrder($intOrderID);
        foreach ($arrTNTParcelList as &$arrParcelInfoItem) {
            $arrResponse = (array)TNTOfficiel_SoapClient::trackingByConsignment($arrParcelInfoItem['parcel_number']);
            $arrTrackingData = '';
            if (count($arrResponse) && isset($arrResponse['Parcel'])) {
                $arrTrackingData = $arrResponse['Parcel'];
            }
            if ($arrTrackingData) {
                TNTOfficiel_Parcel::savePdl($arrTrackingData, $arrParcelInfoItem['id_parcel']);
                $arrTrackingData = array(
                    'history' => TNTOfficiel_Parcel::getHistory($arrTrackingData),
                    'status' => TNTOfficiel_Parcel::getStatus($arrTrackingData),
                    'allStatus' => TNTOfficiel_Parcel::getAllStatus(TNTOfficiel_Parcel::getStatus($arrTrackingData)),
                );
            }

            $arrParcelInfoItem['trackingData'] = $arrTrackingData;
        }
        unset($arrParcelInfoItem);

        return $arrTNTParcelList;
    }


    /**
     * Display the tracking popup.
     *
     * @param $intArgOrderID
     *
     * @return bool
     */
    public static function displayTrackingPopUp($intArgOrderID)
    {
        TNTOfficiel_Logstack::log();

        //$objContext = $this->context;
        $objContext = Context::getContext();

        // Get parcels and for each parcel get its tracking data
        $arrTNTParcelList = TNTOfficiel_Parcel::getTrackingData($intArgOrderID);
        if ($arrTNTParcelList === null) {
            return false;
        }

        $objContext->smarty->assign(array(
            'parcels' => $arrTNTParcelList
        ));
        $objContext->smarty->display(
            _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.'/views/templates/front/displayAjaxTracking.tpl'
        );

        return true;
    }
}
