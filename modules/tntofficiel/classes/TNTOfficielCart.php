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
 * Class TNTOfficielCart
 */
class TNTOfficielCart extends ObjectModel
{
    // id_tntofficiel_cart
    public $id;

    public $id_cart;
    public $carrier_code;
    public $delivery_point;
    public $customer_email;
    public $customer_mobile;
    public $address_building;
    public $address_accesscode;
    public $address_floor;

    public static $definition = array(
        'table' => 'tntofficiel_cart',
        'primary' => 'id_tntofficiel_cart',
        'fields' => array(
            'id_cart' => array(
                'type' => ObjectModel::TYPE_INT,
                'validate' => 'isUnsignedId',
                'required' => true
            ),
            'carrier_code' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 64
            ),
            'delivery_point' => array(
                'type' => ObjectModel::TYPE_STRING
                /*, 'validate' => 'isSerializedArray', 'size' => 65000*/
            ),
            'customer_email' => array(
                'type' => ObjectModel::TYPE_STRING,
                'validate' => 'isEmail',
                'size' => 128
            ),
            'customer_mobile' => array(
                'type' => ObjectModel::TYPE_STRING,
                'validate' => 'isPhoneNumber',
                'size' => 32
            ),
            'address_building' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 16
            ),
            'address_accesscode' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 16
            ),
            'address_floor' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 16
            ),
        ),
    );

    // cache and prevent race condition.
    private static $arrLoadedEntities = array();


    /**
     * Constructor.
     */
    public function __construct($intArgId = null, $intArgLangId = null, $intArgShopId = null)
    {
        TNTOfficiel_Logstack::log();

        parent::__construct($intArgId, $intArgLangId, $intArgShopId);
    }

    /**
     * Load existing object model or optionally create a new one for it's ID.
     *
     * @param $intArgCartID
     * @param bool $boolArgCreate
     * @param null $intArgLangID
     * @param null $intArgShopID
     *
     * @return mixed|null|TNTOfficielCart
     *
     * @throws PrestaShopDatabaseException
     */
    public static function loadCartID($intArgCartID, $boolArgCreate = true, $intArgLangID = null, $intArgShopID = null)
    {
        TNTOfficiel_Logstack::log();

        $intCartID = (int)$intArgCartID;
        $strEntityID = '_'.$intCartID.'-'.(int)$intArgLangID.'-'.(int)$intArgShopID;

        // No new cart ID.
        if (!($intCartID > 0)) {
            return null;
        }

        if (array_key_exists($strEntityID, TNTOfficielCart::$arrLoadedEntities)) {
            $objTNTCartModel = TNTOfficielCart::$arrLoadedEntities[$strEntityID];
            // Check.
            if ((int)$objTNTCartModel->id_cart === $intCartID && Validate::isLoadedObject($objTNTCartModel)) {
                return $objTNTCartModel;
            }
        }

        // Search row for cart ID.
        $objDbQuery = new DbQuery();
        $objDbQuery->select('*');
        $objDbQuery->from(TNTOfficielCart::$definition['table']);
        $objDbQuery->where('id_cart = '.$intCartID);

        $objDB = Db::getInstance();
        $arrResult = $objDB->executeS($objDbQuery);
        // If row found and match cart ID.
        if (count($arrResult)===1 && $intCartID===(int)$arrResult[0]['id_cart']) {
            // Load existing TNT cart entry.
            $objTNTCartModel = new TNTOfficielCart(
                (int)$arrResult[0]['id_tntofficiel_cart'],
                $intArgLangID,
                $intArgShopID
            );
        } elseif ($boolArgCreate === true) {
            // Create a new TNT cart entry.
            $objTNTCartModel = new TNTOfficielCart(null, $intArgLangID, $intArgShopID);
            $objTNTCartModel->id_cart = $intCartID;
            $objTNTCartModel->save();
            // Reload to get default DB values after creation.
            $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID, false, $intArgLangID, $intArgShopID);
        } else {
            $objException = new Exception('TNTOfficielCart data not found for ID #'.$intCartID);
            TNTOfficiel_Logger::logException($objException);

            return null;
        }

        // Check.
        if ((int)$objTNTCartModel->id_cart !== $intCartID || !Validate::isLoadedObject($objTNTCartModel)) {
            return null;
        }

        TNTOfficielCart::$arrLoadedEntities[$strEntityID] = $objTNTCartModel;

        return $objTNTCartModel;
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
            // Clear delivery point.
            $this->setDeliveryPoint(array());

            return $this->save();
        }

        return true;
    }

    /**
     * @return array
     */
    public function getDeliveryPoint()
    {
        TNTOfficiel_Logstack::log();

        $arrDeliveryPoint = Tools::unSerialize($this->delivery_point);
        if (!is_array($arrDeliveryPoint)) {
            $arrDeliveryPoint = array();
        }

        return $arrDeliveryPoint;
    }

    /**
     * @param $arrArgDeliveryPoint
     * @return mixed
     */
    public function setDeliveryPoint($arrArgDeliveryPoint)
    {
        TNTOfficiel_Logstack::log();

        if (!is_array($arrArgDeliveryPoint)) {
            return false;
        }

        if (isset($arrArgDeliveryPoint['xett'])) {
            unset($arrArgDeliveryPoint['pex']);
        } elseif (isset($arrArgDeliveryPoint['pex'])) {
            unset($arrArgDeliveryPoint['xett']);
        } else {
            $arrArgDeliveryPoint = array();
        }

        $this->delivery_point = serialize($arrArgDeliveryPoint);
        return $this->save();
    }
}
