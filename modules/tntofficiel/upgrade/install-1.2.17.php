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

function upgrade_module_1_2_17($objArgTNTOfficiel_1_2_17)
{
    $arrTemplateOverrideList = array(
        array(
            'fileName' => 'view.tpl',
            'directorySrc' => 'views/templates/admin/override/controllers/admin/templates/orders/helpers/view/',
            'directoryDst' => 'controllers/admin/templates/orders/helpers/view/',
        ),
    );

    //$strModuleDirSrc = _PS_MODULE_DIR_.$objTNTOfficiel->name.DIRECTORY_SEPARATOR;
    $strModuleDirSrc = $objArgTNTOfficiel_1_2_17->getLocalPath();

    foreach ($arrTemplateOverrideList as $arrTemplateOverride) {
        $strPathTemplateSrc = $strModuleDirSrc.$arrTemplateOverride['directorySrc'];
        $strFileTemplateSrc = $strPathTemplateSrc.$arrTemplateOverride['fileName'];
        $strPathTemplateDst = _PS_OVERRIDE_DIR_.$arrTemplateOverride['directoryDst'];
        $strFileTemplateDst = $strPathTemplateDst.$arrTemplateOverride['fileName'];

        try {
            // Create directory if unexist.
            if (!is_dir($strPathTemplateDst)) {
                mkdir($strPathTemplateDst, 0777, true);
            }
            // Delete previous template file if exist.
            if (file_exists($strFileTemplateDst)) {
                unlink($strFileTemplateDst);
            }
            // Copy new template file.
            copy($strFileTemplateSrc, $strFileTemplateDst);
        } catch (Exception $objException) {
            return false;
        }
    }

    // Reinstall Overrides: Module::uninstallOverrides() Module::installOverrides().
    if (!($objArgTNTOfficiel_1_2_17->uninstallOverrides() && $objArgTNTOfficiel_1_2_17->installOverrides())) {
        return false;
    }

    // Delete unused config.
    if (!Configuration::deleteByName('TNT_CARRIER_CREDENTIALS_OK')) {
        return false;
    }

    // Success.
    return true;
}
