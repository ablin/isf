<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

if (!defined('_TNTOFFICIEL_CLASSLOADER_')) {
    define('_TNTOFFICIEL_CLASSLOADER_', true);
    require_once _PS_MODULE_DIR_.'tntofficiel/tntofficiel.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/classes/TNTOfficielCache.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/classes/TNTOfficielCart.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/classes/TNTOfficielOrder.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Address.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Carrier.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Cart.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Credentials.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_JsonRPCClient.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Install.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Logger.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Logstack.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Order.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Parcel.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Product.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_SoapClient.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_Tools.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/pdf/TNTOfficiel_PDFMerger.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/pdf/manifest/TNTOfficiel_ManifestPDF.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/pdf/manifest/TNTOfficiel_ManifestPDFGenerator.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/pdf/manifest/HTMLTemplateTNTOfficielManifest.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/pdf/manifest/TNTOfficiel_ManifestPDFCreator.php';
    require_once _PS_MODULE_DIR_.'tntofficiel/libraries/exceptions/TNTOfficiel_MaxPackageWeightException.php';
}
