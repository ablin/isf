<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficielTrackingModuleFrontController extends ModuleFrontController
{
    /**
     * TNTOfficielTrackingModuleFrontController constructor.
     * Controller always used for AJAX response.
     */
    public function __construct()
    {
        TNTOfficiel_Logstack::log();

        parent::__construct();

        // FO Auth is required and guest allowed.
        $this->auth = true;
        $this->guestAllowed = true;

        // SSL
        $this->ssl = Tools::usingSecureMode();
        // No header/footer.
        $this->ajax = true;

        // Do not waste time to get price for some AJAX request.
        $this->dngp = true;
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

        $objContext = $this->context;

        if ($objContext->customer->isLogged($this->guestAllowed)) {
            $objPSOrder = new Order($intOrderID);
            // If order belong to customer.
            if ((int)$objPSOrder->id_customer === (int)$objContext->customer->id) {
                if (TNTOfficiel_Parcel::displayTrackingPopUp($intOrderID)) {
                    return true;
                }
            }
        }

        // 404 fallback.
        Controller::getController('PageNotFoundController')->run();

        return false;
    }
}
