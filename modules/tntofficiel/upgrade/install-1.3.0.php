<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Logger.php';

/**
 * This function updates the module from previous versions to this version.
 * Triggered if module is installed and source is directly updated.
 * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
 */
function upgrade_module_1_3_0($objArgTNTOfficiel_1_3_0)
{
    // Module::uninstall().
    if (!$objArgTNTOfficiel_1_3_0->uninstall()) {
        return false;
    }

    /*
     * Uninstall template override.
     */

    $arrTemplateOverrideList = array(
        array(
            'fileName' => 'view.tpl',
            'directoryDst' => 'controllers/admin/templates/orders/helpers/view/',
        ),
    );

    foreach ($arrTemplateOverrideList as $arrTemplateOverride) {
        $strPathTemplateDst = _PS_OVERRIDE_DIR_.$arrTemplateOverride['directoryDst'];
        $strFileTemplateDst = $strPathTemplateDst.$arrTemplateOverride['fileName'];

        try {
            // Delete previous template file if exist.
            if (file_exists($strFileTemplateDst)) {
                unlink($strFileTemplateDst);
            }
        } catch (Exception $objException) {
            TNTOfficiel_Logger::logException($objException);

            return false;
        }
    }


    // If MultiShop and more than 1 Shop.
    if (Shop::isFeatureActive()) {
        // Define Shop context to all Shops.
        Shop::setContext(Shop::CONTEXT_ALL);
    }

    /*
     * Delete old global carrier.
     */

    // Get current TNT carrier ID.
    $intCarrierIDTNT = Configuration::get('TNT_CARRIER_ID');
    // Carrier ID must be an integer greater than 0.
    if (!(empty($intCarrierIDTNT) || $intCarrierIDTNT != (int)$intCarrierIDTNT || !((int)$intCarrierIDTNT > 0))) {
        $intCarrierIDTNT = (int)$intCarrierIDTNT;
        // Load TNT carrier.
        $objCarrierTNT = new Carrier($intCarrierIDTNT);
        // If TNT carrier object not available.
        if (Validate::isLoadedObject($objCarrierTNT) && (int)$objCarrierTNT->id === $intCarrierIDTNT) {
            $objCarrierTNT->active = false;
            $objCarrierTNT->deleted = true;
            // Delete Carrier.
            if (!$objCarrierTNT->save()) {
                return false;
            }
        }
    }

    /*
     * Delete previous configuration.
     */

    if (!Configuration::deleteByName('TNT_CARRIER_ID')
        || !Configuration::deleteByName('TNT_CARRIER_ACTIVATED')
        || !Configuration::deleteByName('TNT_CARRIER_MIDDLEWARE_URL')
        || !Configuration::deleteByName('TNT_CARRIER_MIDDLEWARE_SHORT_URL')
        || !Configuration::deleteByName('TNT_CARRIER_SOAP_WSDL')
        || !Configuration::deleteByName('TNT_CARRIER_MAX_PACKAGE_B2B')
        || !Configuration::deleteByName('TNT_CARRIER_MAX_PACKAGE_B2C')
    ) {
        return false;
    }

    // Module::install().
    if (!$objArgTNTOfficiel_1_3_0->install()) {
        return false;
    }

    // Success.
    return true;
}
