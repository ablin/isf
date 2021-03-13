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

function upgrade_module_1_2_20($objArgTNTOfficiel_1_2_20)
{
    $strTablePrefix = _DB_PREFIX_;

    // Dop unused log table.
    $strSQLTableLogDropTable = <<<SQL
DROP TABLE IF EXISTS `${strTablePrefix}tnt_log`;
SQL;

    // Add column to tnt_extra_address_data table.
    $strSQLTableExtraAddColumns = <<<SQL
ALTER TABLE `${strTablePrefix}tnt_extra_address_data`
  ADD COLUMN `carrier_code`   VARCHAR(64) NOT NULL DEFAULT '' AFTER `id_address`,
  ADD COLUMN `carrier_label`  VARCHAR(255) NOT NULL DEFAULT '' AFTER `carrier_code`,
  ADD COLUMN `delivery_point` TEXT NULL AFTER `carrier_label`,
  ADD COLUMN `delivery_price` DECIMAL(20,6) NOT NULL DEFAULT '0.000000' AFTER `delivery_point`;
SQL;
    // Rename and change existing columns to tnt_extra_address_data table.
    $strSQLTableExtraChangeColumns = <<<SQL
ALTER TABLE `${strTablePrefix}tnt_extra_address_data`
  CHANGE COLUMN `id_extra_address_data` `id_tntofficiel_cart` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `id_address` `id_cart`                INT(10) UNSIGNED NOT NULL AFTER `id_tntofficiel_cart`,
  CHANGE COLUMN `email` `customer_email`              VARCHAR(128) NOT NULL DEFAULT '' AFTER `delivery_price`,
  CHANGE COLUMN `mobile_phone` `customer_mobile`      VARCHAR(32) NOT NULL DEFAULT '' AFTER `customer_email`,
  CHANGE COLUMN `building_number` `address_building`  VARCHAR(16) NOT NULL DEFAULT '' AFTER `customer_mobile`,
  CHANGE COLUMN `intercom_code` `address_accesscode`  VARCHAR(16) NOT NULL DEFAULT '' AFTER `address_building`,
  CHANGE COLUMN `floor` `address_floor`               VARCHAR(16) NOT NULL DEFAULT '' AFTER `address_accesscode`;
SQL;
    // Rename tnt_extra_address_data table to tntofficiel_cart.
    $strSQLTableExtraRenameTable = <<<SQL
ALTER TABLE `${strTablePrefix}tnt_extra_address_data`
  RENAME `${strTablePrefix}tntofficiel_cart`;
SQL;

    // Rename and change existing columns to tnt_order table.
    $strSQLTableOrderChangeColumns = <<<SQL
ALTER TABLE `${strTablePrefix}tnt_order`
  CHANGE COLUMN `id_tnt_order` `id_tntofficiel_order` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `id_order` `id_order`                 INT(10) UNSIGNED NOT NULL AFTER `id_tntofficiel_order`,
  CHANGE COLUMN `tnt_product_code` `carrier_code`     VARCHAR(64) NOT NULL DEFAULT '' AFTER `id_order`,
  CHANGE COLUMN `tnt_product_label` `carrier_label`   VARCHAR(255) NOT NULL DEFAULT '' AFTER `carrier_code`,
  CHANGE COLUMN `tnt_xett` `carrier_xett`             VARCHAR(5) NOT NULL DEFAULT '' AFTER `carrier_label`,
  CHANGE COLUMN `tnt_pex` `carrier_pex`               VARCHAR(4) NOT NULL DEFAULT '' AFTER `carrier_xett`,
  CHANGE COLUMN `bt` `bt_filename`                    VARCHAR(64) NOT NULL DEFAULT '' AFTER `carrier_pex`,
  CHANGE COLUMN `shipped` `is_shipped`                TINYINT(1) NOT NULL DEFAULT '0' AFTER `bt_filename`,
  CHANGE COLUMN `previous_state` `previous_state`     INT(10) UNSIGNED NULL DEFAULT NULL AFTER `is_shipped`,
  CHANGE COLUMN `pickup_number` `pickup_number`       VARCHAR(50) NOT NULL DEFAULT '' AFTER `previous_state`,
  CHANGE COLUMN `shipping_date` `shipping_date`       VARCHAR(10) NOT NULL DEFAULT '' AFTER `pickup_number`,
  CHANGE COLUMN `due_date` `due_date`                 VARCHAR(10) NOT NULL DEFAULT '' AFTER `shipping_date`,
  CHANGE COLUMN `start_date` `start_date`             VARCHAR(10) NOT NULL DEFAULT '' AFTER `due_date`;
SQL;
    // Rename tnt_order table to tntofficiel_order.
    $strSQLTableOrderRenameTable = <<<SQL
ALTER TABLE `${strTablePrefix}tnt_order`
  RENAME `${strTablePrefix}tntofficiel_order`;
SQL;


    // Change DataType weight column to DECIMAL.
    $strSQLTableParcelChangeColumns = <<<SQL
ALTER TABLE `${strTablePrefix}tnt_parcels`
  CHANGE COLUMN `id_parcel` `id_parcel`   INT(10) UNSIGNED NOT NULL AUTO_INCREMENT FIRST,
  CHANGE COLUMN `id_order` `id_order`     INT(10) UNSIGNED NOT NULL AFTER `id_parcel`,
  CHANGE COLUMN `weight` `weight`         DECIMAL(20,6) NOT NULL DEFAULT '0.000000';
SQL;
    // Rename tnt_parcels table to tntofficiel_order_parcels.
    $strSQLTableParcelRenameTable = <<<SQL
ALTER TABLE `${strTablePrefix}tnt_parcels`
  RENAME `${strTablePrefix}tntofficiel_order_parcels`;
SQL;

    $objDB = Db::getInstance();

    if (!$objDB->execute($strSQLTableLogDropTable)
        || !$objDB->execute($strSQLTableExtraAddColumns)
        || !$objDB->execute($strSQLTableExtraChangeColumns)
        || !$objDB->execute($strSQLTableExtraRenameTable)
        || !$objDB->execute($strSQLTableOrderChangeColumns)
        || !$objDB->execute($strSQLTableOrderRenameTable)
        || !$objDB->execute($strSQLTableParcelChangeColumns)
        || !$objDB->execute($strSQLTableParcelRenameTable)
    ) {
        return false;
    }

    $arrTemplateOverrideList = array(
        array(
            'fileName' => 'view.tpl',
            'directorySrc' => 'views/templates/admin/override/controllers/admin/templates/orders/helpers/view/',
            'directoryDst' => 'controllers/admin/templates/orders/helpers/view/',
        ),
    );

    $boolCopy = true;

    //$strModuleDirSrc = _PS_MODULE_DIR_.$objTNTOfficiel->name.DIRECTORY_SEPARATOR;
    $strModuleDirSrc = $objArgTNTOfficiel_1_2_20->getLocalPath();

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
            $boolCopy = $boolCopy && copy($strFileTemplateSrc, $strFileTemplateDst);
        } catch (Exception $objException) {
            return false;
        }
    }

    // If at least a file copy has fail.
    if (!$boolCopy) {
        return false;
    }

    // Reinstall Overrides: Module::uninstallOverrides() Module::installOverrides().
    if (!($objArgTNTOfficiel_1_2_20->uninstallOverrides() && $objArgTNTOfficiel_1_2_20->installOverrides())) {
        return false;
    }

    // Success.
    return true;
}
