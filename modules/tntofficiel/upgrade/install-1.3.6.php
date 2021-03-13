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

/**
 * This function updates the module from previous versions to this version.
 * Triggered if module is installed and source is directly updated.
 * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
 */
function upgrade_module_1_3_6($objArgTNTOfficiel_1_3_6)
{
    // Module::uninstall().
    if (!$objArgTNTOfficiel_1_3_6->uninstall()) {
        return false;
    }

    // If MultiShop and more than 1 Shop.
    if (Shop::isFeatureActive()) {
        // Define Shop context to all Shops.
        Shop::setContext(Shop::CONTEXT_ALL);
    }

    /*
     * Delete previous configuration.
     */

    if (!Configuration::deleteByName('TNTOFFICIEL_URL_MIDW_RPC')
        || !Configuration::deleteByName('TNTOFFICIEL_URL_MIDW_IFRAME')
    ) {
        return false;
    }

    // Module::install().
    if (!$objArgTNTOfficiel_1_3_6->install()) {
        return false;
    }

    // Success.
    return true;
}
