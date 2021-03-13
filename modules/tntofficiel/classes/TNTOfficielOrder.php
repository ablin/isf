<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

/**
 * Class TNTOfficielOrder
 */
class TNTOfficielOrder extends ObjectModel
{
    // id_tntofficiel_order
    public $id;

    public $id_order;
    public $carrier_code;
    public $carrier_label;
    public $carrier_xett;
    public $carrier_pex;
    public $bt_filename;
    public $is_shipped;
    public $pickup_number;
    public $shipping_date;
    public $due_date;
    public $start_date;

    public static $definition = array(
        'table' => 'tntofficiel_order',
        'primary' => 'id_tntofficiel_order',
        'fields' => array(
            'id_order' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true
            ),
            'carrier_code' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 64
            ),
            'carrier_label' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 255
            ),
            'carrier_xett' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 5
            ),
            'carrier_pex' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 4
            ),
            'bt_filename' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 64
            ),
            'is_shipped' => array(
                'type' => ObjectModel::TYPE_BOOL,
                'validate' => 'isBool'
            ),
            'pickup_number' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 50
            ),
            'shipping_date' => array(
                'type' => ObjectModel::TYPE_DATE,
                'validate' => 'isDateFormat',
            ),
            'due_date' => array(
                'type' => ObjectModel::TYPE_DATE,
                'validate' => 'isDateFormat',
            ),
            'start_date' => array(
                'type' => ObjectModel::TYPE_DATE,
                'validate' => 'isDateFormat',
            ),
        ),
    );

    // cache and prevent race condition.
    private static $arrLoadedEntities = array();


    /**
     * Constructor.
     */
    public function __construct($intArgID = null, $intArgLangID = null, $intArgShopID = null)
    {
        TNTOfficiel_Logstack::log();

        parent::__construct($intArgID, $intArgLangID, $intArgShopID);
    }

    /**
     * Load existing object model or optionally create a new one for it's ID.
     *
     * @param $intArgOrderID
     * @param bool $boolArgCreate
     * @param null $intArgLangID
     * @param null $intArgShopID
     *
     * @return TNTOfficielOrder|null
     *
     * @throws PrestaShopDatabaseException
     */
    public static function loadOrderID(
        $intArgOrderID,
        $boolArgCreate = true,
        $intArgLangID = null,
        $intArgShopID = null
    ) {
        TNTOfficiel_Logstack::log();

        $intOrderID = (int)$intArgOrderID;
        $strEntityID = $intOrderID.'-'.(int)$intArgLangID.'-'.(int)$intArgShopID;

        // No new order ID.
        if (!($intOrderID > 0)) {
            return null;
        }

        if (array_key_exists($strEntityID, TNTOfficielOrder::$arrLoadedEntities)) {
            $objTNTOrderModel = TNTOfficielOrder::$arrLoadedEntities[$strEntityID];
            // Check.
            if ((int)$objTNTOrderModel->id_order === $intOrderID && Validate::isLoadedObject($objTNTOrderModel)) {
                return $objTNTOrderModel;
            }
        }

        // Search row for order ID.
        $objDbQuery = new DbQuery();
        $objDbQuery->select('*');
        $objDbQuery->from(TNTOfficielOrder::$definition['table']);
        $objDbQuery->where('id_order = '.$intOrderID);

        $objDB = Db::getInstance();
        $arrResult = $objDB->executeS($objDbQuery);
        // If row found and match order ID.
        if (count($arrResult)===1 && $intOrderID===(int)$arrResult[0]['id_order']) {
            // Load existing TNT order entry.
            $objTNTOrderModel = new TNTOfficielOrder(
                (int)$arrResult[0]['id_tntofficiel_order'],
                $intArgLangID,
                $intArgShopID
            );
        } elseif ($boolArgCreate === true) {
            // Create a new TNT order entry.
            $objTNTOrderModel = new TNTOfficielOrder(null, $intArgLangID, $intArgShopID);
            $objTNTOrderModel->id_order = $intOrderID;
            $objTNTOrderModel->save();
            // Reload to get default DB values after creation.
            $objTNTOrderModel = TNTOfficielOrder::loadOrderID($intOrderID, false, $intArgLangID, $intArgShopID);
        } else {
            $objException = new Exception('TNTOfficielOrder data not found for ID #'.$intOrderID);
            TNTOfficiel_Logger::logException($objException);

            return null;
        }

        // Check.
        if ((int)$objTNTOrderModel->id_order !== $intOrderID || !Validate::isLoadedObject($objTNTOrderModel)) {
            return null;
        }

        TNTOfficielOrder::$arrLoadedEntities[$strEntityID] = $objTNTOrderModel;

        return $objTNTOrderModel;
    }

    /**
     * @return string
     */
    public function getCarrierCode()
    {
        return (string)$this->carrier_code;
    }

    /**
     * @param $arrArgDeliveryPoint
     * @return mixed
     */
    public function setCarrierCode($strCarrierCode = null)
    {
        TNTOfficiel_Logstack::log();

        // If product change
        if ($this->carrier_code !== (string)$strCarrierCode) {
            // save.
            $this->carrier_code = (string)$strCarrierCode;
            // Clear delivery point code.
            $this->carrier_xett = null;
            $this->carrier_pex = null;

            return $this->save();
        }

        return true;
    }

    /**
     * Check if a shipment was successfully done for an order.
     *
     * @return bool
     */
    public function isShipped()
    {
        return (bool)$this->is_shipped;
    }
}
