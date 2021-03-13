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
function upgrade_module_1_3_1($objArgTNTOfficiel_1_3_1)
{
    // Module::uninstall().
    if (!$objArgTNTOfficiel_1_3_1->uninstall()) {
        return false;
    }

    // If MultiShop and more than 1 Shop.
    if (Shop::isFeatureActive()) {
        // Define Shop context to all Shops.
        Shop::setContext(Shop::CONTEXT_ALL);
    }

    /*
     * Preserve previous configuration.
     */

    Configuration::updateValue(
        'TNTOFFICIEL_ACCOUNT_LOGIN',
        Configuration::get('TNT_CARRIER_USERNAME')
    );
    Configuration::updateValue(
        'TNTOFFICIEL_ACCOUNT_NUMBER',
        Configuration::get('TNT_CARRIER_ACCOUNT')
    );
    Configuration::updateValue(
        'TNTOFFICIEL_PICKUP_DISPLAY_NUMBER',
        Configuration::get('TNT_CARRIER_PICKUP_NUMBER_SHOW')
    );
    Configuration::updateValue(
        'TNTOFFICIEL_GMAP_API_KEY',
        Configuration::get('TNT_GOOGLE_MAP_API_KEY')
    );

    /*
     * Delete previous configuration.
     */

    if (!Configuration::deleteByName('TNT_CARRIER_PASSWORD')
        || !Configuration::deleteByName('TNT_CARRIER_ASSOCIATIONS')
        || !Configuration::deleteByName('TNT_CARRIER_USERNAME')
        || !Configuration::deleteByName('TNT_CARRIER_ACCOUNT')
        || !Configuration::deleteByName('TNT_CARRIER_PICKUP_NUMBER_SHOW')
        || !Configuration::deleteByName('TNT_GOOGLE_MAP_API_KEY')
        || !Configuration::deleteByName('TNTOFFICIEL_URL_SOAP_WSDL')
        || !Configuration::deleteByName('TNTOFFICIEL_PACKAGE_MAXWEIGHT_B2B')
        || !Configuration::deleteByName('TNTOFFICIEL_PACKAGE_MAXWEIGHT_B2C')
    ) {
        return false;
    }

    // Module::install().
    if (!$objArgTNTOfficiel_1_3_1->install()) {
        return false;
    }

    // Success.
    return true;
}
