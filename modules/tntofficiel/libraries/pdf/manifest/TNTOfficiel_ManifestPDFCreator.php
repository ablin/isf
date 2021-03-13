<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_ManifestPDFCreator
{
    public function __construct()
    {
        TNTOfficiel_Logstack::log();
    }

    /**
     * @param $orderList array
     */
    public function createManifest($orderList)
    {
        TNTOfficiel_Logstack::log();

        $arrParcelInfoList = array();
        foreach ($orderList as $intOrderID) {
            $intOrderID = (int)$intOrderID;
            $objPSOrder = new Order($intOrderID);
            if (!TNTOfficiel_Carrier::isTNTOfficielCarrierID($objPSOrder->id_carrier)) {
                continue;
            }
            // Load TNT order info for it's ID.
            $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false);
            if ($objTNTOrderModel === null) {
                continue;
            }
            $strCarrierLabel = TNTOfficiel_Carrier::getCarrierCodeName($objTNTOrderModel->getCarrierCode());
            $objAddress = new Address($objPSOrder->id_address_delivery);
            $arrTNTParcelList = TNTOfficiel_Parcel::getParcelsFromAnOrder($intOrderID);
            foreach ($arrTNTParcelList as $arrParcelInfoItem) {
                $arrParcelInfoItem['address'] = $objAddress;
                $arrParcelInfoItem['carrier_label'] = $strCarrierLabel;
                $arrParcelInfoList[] = $arrParcelInfoItem;
            }
        }
        $carrierAccount = Configuration::get('TNTOFFICIEL_ACCOUNT_NUMBER');
        $carrierName = Configuration::get('TNTOFFICIEL_ACCOUNT_LOGIN');

        // Total weight for the parcels.
        $fltTotalWeight = (float)0;
        foreach ($arrParcelInfoList as $arrParcelInfoItem) {
            $fltTotalWeight += $arrParcelInfoItem['weight'];
        }

        $objManifestPDF = new TNTOfficiel_ManifestPDF(
            array(
                'manifestData' => array(
                    'parcelsData' => $arrParcelInfoList,
                    'carrierAccount' => $carrierAccount,
                    'carrierName' => $carrierName,
                    'address' => array(
                        'name' => Configuration::get('PS_SHOP_NAME'),
                        'address1' => Configuration::get('PS_SHOP_ADDR1'),
                        'address2' => Configuration::get('PS_SHOP_ADDR2'),
                        'postcode' => Configuration::get('PS_SHOP_CODE'),
                        'city' => Configuration::get('PS_SHOP_CITY'),
                        'country' => Configuration::get('PS_SHOP_COUNTRY'),
                    ),
                    'totalWeight' => $fltTotalWeight,
                    'parcelsNumber' => count($arrParcelInfoList)
                )
            ),
            'TNTOfficielManifest',
            Context::getContext()->smarty
        );

        $objManifestPDF->render();
    }

}
