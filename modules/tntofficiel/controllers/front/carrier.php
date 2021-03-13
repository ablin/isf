<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficielCarrierModuleFrontController extends ModuleFrontController
{
    /**
     * TNTOfficielCarrierModuleFrontController constructor.
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
     * Get the relay points popup via Ajax.
     * DROPOFFPOINT (Commerçant partenaire) : XETT
     */
    public function displayAjaxBoxRelayPoints()
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;
        $objCart = $objContext->cart;

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

        $objContext = $this->context;
        $objCart = $objContext->cart;

        echo TNTOfficiel_Address::getBoxDropOffPoints($objCart);

        return true;
    }

    /**
     * Save delivery point XETT or PEX info.
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

        $objContext = $this->context;
        $objCart = $objContext->cart;
        $intCartID = (int)$objCart->id;

        // Load TNT cart info or create a new one for it's ID.
        $objTNTCartModel = TNTOfficielCart::loadCartID($intCartID);
        if ($objTNTCartModel !== null) {
            $objTNTCartModel->setDeliveryPoint($arrDeliveryPoint);
        }

        $this->context->smarty->assign(
            array(
                'item' => $arrDeliveryPoint,
                'method_id' => isset($arrDeliveryPoint['xett']) ? 'relay_point' : 'repository',
                'method_name' => isset($arrDeliveryPoint['xett']) ? 'relay-point' : 'repository',
            )
        );

        $this->context->smarty->display(
            _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.'/views/templates/hook/displayCarrierList/deliveryPointSet.tpl'
        );

        return true;
    }

    /**
     * Check TNT data before payment process.
     *
     * @return array
     */
    public function displayAjaxCheckPaymentReady()
    {
        TNTOfficiel_Logstack::log();

        $objContext = $this->context;
        $objCart = $objContext->cart;

        $arrResult = TNTOfficiel_Cart::isPaymentReady($objCart);

        echo Tools::jsonEncode($arrResult);

        return true;
    }
}
