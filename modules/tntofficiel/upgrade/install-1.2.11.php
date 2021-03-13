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

function upgrade_module_1_2_11($objTNT)
{
    $templateOverrides = array(
        array(
            'fileName' => 'view.tpl',
            'directorySrc' => 'views/templates/admin/override/controllers/admin/templates/orders/helpers/view/',
            'directoryDst' => 'controllers/admin/templates/orders/helpers/view/',
        ),
    );

    foreach ($templateOverrides as $template) {
        try {
            $directoryDst = _PS_OVERRIDE_DIR_.$template['directoryDst'];
            if (!is_dir($directoryDst)) {
                mkdir($directoryDst, 0777, true);
            }
            $overrideDestination = $directoryDst.$template['fileName'];
            if (file_exists($overrideDestination)) {
                unlink($overrideDestination);
            }
        } catch (Exception $objException) {
            return false;
        }

        try {
            $directoryDst = _PS_OVERRIDE_DIR_.$template['directoryDst'];
            if (!is_dir($directoryDst)) {
                mkdir($directoryDst, 0777, true);
            }
            $overrideSrc = $objTNT->getLocalPath().$template['directorySrc'].$template['fileName'];
            $overrideDestination = $directoryDst.$template['fileName'];

            copy($overrideSrc, $overrideDestination);
        } catch (Exception $objException) {
            return false;
        }
    }

    // Reinstall Overrides
    if (!$objTNT->uninstallOverrides()) {
        return false;
    }
    if (!$objTNT->installOverrides()) {
        return false;
    }

    return true;
}
