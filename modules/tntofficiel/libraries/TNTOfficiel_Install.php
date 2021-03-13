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
 * Class TNTOfficiel_Install
 * Used in upgrade, do not rename or remove.
 */
class TNTOfficiel_Install
{
    /** @var array */
    public static $arrHookList = array(
        // Header
        'displayBackOfficeHeader',
        'actionAdminControllerSetMedia',
        'displayHeader',

        // Front-Office display carrier.
        'displayBeforeCarrier',
        'displayCarrierList',
        // Front-Office order detail.
        'displayOrderDetail',

        // Order created.
        'actionValidateOrder',
        // Order status before changed.
        'actionOrderStatusUpdate',
        'actionOrderStatusPostUpdate',

        // Back-Office order detail.
        'displayAdminOrder',
        // Carrier updated.
        'actionCarrierUpdate',

        // Add email variables. PS 1.6.1.0+.
        'actionGetExtraMailTemplateVars'
    );

    /** @var array Configuration that is Updated on Install and Deleted on Uninstall. */
    // 'preserve' => true to prevent overwrite or delete during install/uninstall process. value is a default.
    // 'global' => true for global context only.
    public static $arrConfigUpdateDeleteList = array(
        /* Shop Config */
        // Google Map API Key is created on config form submit, then preserved.
        'TNTOFFICIEL_GMAP_API_KEY' => array('value' => '', 'preserve' => true),
        // Authentication information.
        'TNTOFFICIEL_ACCOUNT_LOGIN' => array('value' => '', 'preserve' => true),
        'TNTOFFICIEL_ACCOUNT_NUMBER' => array('value' => '', 'preserve' => true),
        'TNTOFFICIEL_ACCOUNT_PASSWORD' => array('value' => '', 'preserve' => true),
        // Is credentials validated for authentication.
        'TNTOFFICIEL_CREDENTIALS_VALIDATED' => array('value' => false, 'preserve' => true),
        // Show pickup number in AdminOrdersController.
        'TNTOFFICIEL_PICKUP_DISPLAY_NUMBER' => array('value' => '', 'preserve' => true),
        /* Global Config */
        // TNTOfficiel_Carrier::$strConfigNameCarrierID
        // Carrier ID is created/updated with TNTOfficiel_Carrier::setCurrentCarrierID, then preserved.
        'TNTOFFICIEL_CARRIER_LIST' => array('value' => null, 'global' => true, 'preserve' => true),
        // Latest release installed, then preserved until a newer version is installed.
        'TNTOFFICIEL_RELEASE' => array('value' => '', 'global' => true, 'preserve' => true),
    );

    /** @var array */
    public static $arrRemoveOverrideList = array(
        'OrderHistory' => array(
            '__construct' => array(
                '764cb23eef33d3b052ae536c40f709e0', // 1.2.23 PS 1.6.0.5
                '6e1869b1e417720bedcf5e23d77613f3', // 1.2.23 PS 1.6.0.14
            ),
            'changeIdOrderState' => array(
                '77e86569667a6a9cd4dd10b2b5a28987', // 1.2.23 PS 1.6.0.5
                '535605fee2d293bd24cad582b8fd69bf', // 1.2.23 PS 1.6.0.14
            ),
        ),
        'Order' => array(
            '__construct' => array(
                '66b2424ed746b0263a43dd11ba6c4efe', // 1.2.23 PS 1.6.0.5
                '2b3061adcc78264a4f94ee940d417566', // 1.2.23 PS 1.6.0.14
            ),
            'getShipping' => array(
                '4aa55821a1db7d4f3d3f19bc2e169e12', // 1.2.23 PS 1.6.0.5
                'a660b1a60c91c57b13db5e7fcc88cccc', // 1.2.23 PS 1.6.0.14
            ),
        ),
        'AdminOrdersController' => array(
            '__construct' => array(
                'f034e7e05aad32f68c5ec0ddc02c8136', // 1.2.23 PS 1.6.0.5
                'f3b27a58e944af12b0b0d8672d2e7543', // 1.2.23 PS 1.6.0.14
            ),
            'printBtIcon' => array(
                '4613093a0ffac16346fd3aaf88e10b35', // 1.2.23 PS 1.6.0.5
                '4613093a0ffac16346fd3aaf88e10b35', // 1.2.23 PS 1.6.0.14
            ),
            'processBulkGetBT' => array(
                '0622708d6659b4899eb680a1c0b8b577', // 1.2.23 PS 1.6.0.5
                '4d0e455e0fb479d94347b293f8c85e63', // 1.2.23 PS 1.6.0.14
            ),
            'processBulkGetManifest' => array(
                '113fd58ee61737b368001a50bfdc1fec', // 1.2.23 PS 1.6.0.5
                '8ed175b483f8cb64a1cf8c51f9de7595', // 1.2.23 PS 1.6.0.14
            ),
            'processBulkUpdateOrderStatus' => array(
                'ce9689f16ca5b6336c27da48a2ddaca2', // 1.2.23 PS 1.6.0.5
                '271b7bd2da0edac313d87da9b932a5c9', // 1.2.23 PS 1.6.0.14
            ),
            'postProcess' => array(
                'fcdfb356ad5bc7b6463aa01928a195d8', // 1.2.23 PS 1.6.0.5
                'ff11b1994f1c3ad8c1f1c32955830ba5', // 1.2.23 PS 1.6.0.14
            ),
            'setMedia' => array(
            ),
        )
    );

    /** @var array */
    public static $arrRemoveFileList = array(
        'libraries/exceptions/TNTOfficiel_TNTOfficielException.php',
        'libraries/exceptions/TNTOfficiel_OrderAlreadyShippedException.php',
        'libraries/TNTOfficiel_DbUtils.php',
        'libraries/TNTOfficiel_PasswordManager.php',
        'libraries/TNTOfficiel_Debug.php',
        'libraries/TNTOfficiel_SysCheck.php',
        'libraries/TNTOfficiel_String.php',
        'override/classes/order/OrderHistory.php',
        'views/templates/admin/override/controllers/admin/templates/orders/helpers/view/view.tpl',
        'views/css/Admin.css',
        'views/css/AdminTNTOfficiel.css',
        'views/css/carrier.css',
        'views/css/form.css',
        'views/css/manifest.css',
        'views/css/tracking.css',
        'views/img/depot_r8_c2.jpg',
        'views/img/domicile_r4_c2.jpg',
        'views/img/entreprise_r2_c2.jpg',
        'views/img/relais_r6_c2.jpg',
        'views/js/address.js',
        'views/js/AdminOrdersSubmitBulk.js',
        'views/js/carrier.js',
        'views/js/displayAdminOrder.js',
        'Manuel d\'install du module TNT sous Prestashop.pdf',
    );

    /** @var array */
    public static $arrRemoveDirList = array(
        'libraries/helper/',
        'libraries/smarty/',
        'views/img/modal/',
        'views/js/lib/',
        'views/templates/admin/override/',
    );


    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Clear Smarty cache.
     *
     * @return bool
     */
    public static function clearCache()
    {
        TNTOfficiel_Logstack::log();

        // Clear Smarty cache.
        Tools::clearSmartyCache();
        // Clear XML cache ('/config/xml/').
        Tools::clearXMLCache();
        // Clear current theme cache (/themes/<THEME>/cache/').
        Media::clearCache();

        // Clear class index cache ('/cache/class_index.php'). PS 1.6.0.5+.
        if (defined('_DB_PREFIX_') && Configuration::get('PS_DISABLE_OVERRIDES')) {
            PrestaShopAutoload::getInstance()->_include_override_path = false;
        }
        PrestaShopAutoload::getInstance()->generateIndex();

        return true;
    }

    /**
     * Fix unoverride process for upgrade.
     * Method may be not removed from the override class file (because no matches) :
     * - It no longer exist in a new version of the module, therefore still used.
     * - It differs in a new version of the module, not removed and can not be reinstalled.
     */
    public static function uninstallOverridesFix()
    {
        TNTOfficiel_Logstack::log();

        foreach (TNTOfficiel_Install::$arrRemoveOverrideList as $strClassName => $arrMethodListMD5) {
            TNTOfficiel_Install::removeOverrideFix($strClassName, $arrMethodListMD5);
        }
    }

    /**
     * Remove an overrided method in classes via a pre-defined hash list.
     *
     * @param $strArgClassName
     * @param $arrArgMethodListMD5
     *
     * @return bool
     */
    public static function removeOverrideFix($strArgClassName, $arrArgMethodListMD5)
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        $strPrestashopClassPath = PrestaShopAutoload::getInstance()->getClassPath($strArgClassName.'Core');
        $strOverrideClassPath = PrestaShopAutoload::getInstance()->getClassPath($strArgClassName);

        if ($strOverrideClassPath) {
            $strOverrideClassLocation = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR.$strOverrideClassPath;
        } else {
            $strOverrideClassLocation = _PS_ROOT_DIR_.DIRECTORY_SEPARATOR
                .'override'.DIRECTORY_SEPARATOR.$strPrestashopClassPath;
        }

        // Check if override file is writable.
        if (!file_exists($strOverrideClassLocation)
            || !is_writable($strOverrideClassLocation)
        ) {
            return false;
        }

        file_put_contents(
            $strOverrideClassLocation,
            preg_replace('#(\r\n|\r)#ism', "\n", file_get_contents($strOverrideClassLocation))
        );

        // Get a uniq id for the class, because you can override a class (or remove the override)
        // twice in the same session and we need to avoid redeclaration.
        do {
            $strClassUniqID = uniqid();
        } while (class_exists($strArgClassName.'OverrideOriginal_remove', false));

        $arrOverrideFileContent = file($strOverrideClassLocation);

        // Make a reflection of the override class and the module override class
        eval(preg_replace(
            array(
                '#^\s*<\?(?:php)?#',
                '#class\s+'.$strArgClassName.'\s+extends\s+([a-z0-9_]+)(\s+implements\s+([a-z0-9_]+))?#i'
            ),
            array(
                ' ',
                'class '.$strArgClassName.'OverrideOriginal_remove'.$strClassUniqID
            ),
            implode('', $arrOverrideFileContent)
        ));

        $objOverrideClassReflection = new ReflectionClass($strArgClassName.'OverrideOriginal_remove'.$strClassUniqID);

        /*
         * Remove methods from override file.
         */

        $arrOverrideFileContent = file($strOverrideClassLocation);
        foreach ($arrArgMethodListMD5 as $strArgMethodName => $arrMethodContentMD5List) {
            if (!$objOverrideClassReflection->hasMethod($strArgMethodName)) {
                continue;
            }

            $objOverrideMethodReflection = $objOverrideClassReflection->getMethod($strArgMethodName);
            $intOverrideMethodLineCount = $objOverrideMethodReflection->getEndLine()
                - $objOverrideMethodReflection->getStartLine() + 1;

            $arrOverrideFileContentRecover = $arrOverrideFileContent;

            $strOverrideMethodContent = preg_replace(
                '/\s/',
                '',
                implode(
                    '',
                    array_splice(
                        $arrOverrideFileContent,
                        $objOverrideMethodReflection->getStartLine() - 1,
                        $intOverrideMethodLineCount,
                        array_pad(array(), $intOverrideMethodLineCount, '#--remove--#')
                    )
                )
            );

            // Recover override file content.
            if (!in_array(md5($strOverrideMethodContent), $arrMethodContentMD5List, true)) {
                TNTOfficiel_Logger::logInstall(
                    $strLogMessage.' no matching '
                    .$strArgClassName.'/'.$strArgMethodName.'/'.md5($strOverrideMethodContent)
                    .' from '.$strOverrideClassLocation,
                    false
                );
                $arrOverrideFileContent = $arrOverrideFileContentRecover;
            }
        }

        // Rewrite nice code.
        $strOverrideFinalContent = '';
        foreach ($arrOverrideFileContent as $strOverrideLineContent) {
            // Jump.
            if ($strOverrideLineContent == '#--remove--#') {
                continue;
            }

            $strOverrideFinalContent .= $strOverrideLineContent;
        }
        file_put_contents($strOverrideClassLocation, $strOverrideFinalContent);

        // Re-generate the class index.
        PrestaShopAutoload::getInstance()->generateIndex();

        return true;
    }

    /**
     * Check class override state.
     *
     * @return bool
     */
    public static function checkOverride()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        if ((bool)Configuration::get('PS_DISABLE_OVERRIDES')) {
            TNTOfficiel_Logger::logInstall($strLogMessage.' PS_DISABLE_OVERRIDES', false);

            return false;
        }

        // Check cache '/cache/class_index.php'
        $objPSAutoload = PrestaShopAutoload::getInstance();
        if (!$objPSAutoload->_include_override_path) {
            TNTOfficiel_Logger::logInstall($strLogMessage.' _include_override_path', false);

            return false;
        }

        $arrCheckOverride = array(
            'AdminOrdersController' => 'override/controllers/admin/AdminOrdersController.php',
            'Order' => 'override/classes/order/Order.php',
        );

        foreach ($arrCheckOverride as $strClassName => $strPath) {
            if ($objPSAutoload->getClassPath($strClassName) === $strPath) {
                unset($arrCheckOverride[$strClassName]);
            } else {
                TNTOfficiel_Logger::logInstall($strLogMessage.' Autoload '.$strClassName, false);
            }
        }

        if (count($arrCheckOverride) === 0) {
            TNTOfficiel_Logger::logInstall($strLogMessage, true);
        }

        return true;
    }

    /**
     * Remove unused files and unused dirs.
     *
     * @return bool
     */
    public static function uninstallDeprecatedFiles()
    {
        return TNTOfficiel_Install::removeFiles(
            _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.DIRECTORY_SEPARATOR,
            TNTOfficiel_Install::$arrRemoveFileList,
            TNTOfficiel_Install::$arrRemoveDirList
        );
    }

    /**
     * Remove a list of files or directories.
     *
     * @param $strModuleDirSrc
     * @param array $arrRemoveFileList
     * @param array $arrRemoveDirList
     *
     * @return bool
     */
    public static function removeFiles($strModuleDirSrc, $arrRemoveFileList = array(), $arrRemoveDirList = array())
    {
        foreach ($arrRemoveFileList as $strFile) {
            $strFQFile = $strModuleDirSrc.$strFile;

            try {
                // Delete file if exist.
                if (file_exists($strFQFile)) {
                    Tools::deleteFile($strFQFile);
                }
            } catch (Exception $objException) {
                TNTOfficiel_Logger::logException($objException);

                return false;
            }
        }

        foreach ($arrRemoveDirList as $strDir) {
            $strFQDir = $strModuleDirSrc.$strDir;

            try {
                // Delete dir if exist.
                if (file_exists($strFQDir)) {
                    Tools::deleteDirectory($strFQDir);
                }
            } catch (Exception $objException) {
                TNTOfficiel_Logger::logException($objException);

                return false;
            }
        }

        return true;
    }

    /**
     * Update settings fields.
     *
     * @return bool
     */
    public static function updateSettings()
    {
        TNTOfficiel_Logstack::log();

        $boolUpdated = true;
        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        foreach (TNTOfficiel_Install::$arrConfigUpdateDeleteList as $strCfgName => $arrConfig) {
            // Must be preserved ?
            $boolPreserve = array_key_exists('preserve', $arrConfig) && $arrConfig['preserve'] === true;
            $boolExist = Configuration::get($strCfgName) !== false;
            // if no need to preserve or not exist.
            if (!$boolPreserve || !$boolExist) {
                // Is global ?
                $boolGlobal = array_key_exists('global', $arrConfig) && $arrConfig['global'] === true;
                // Get value.
                $mxdValue = array_key_exists('value', $arrConfig) ? $arrConfig['value'] : '';

                if ($boolGlobal) {
                    $boolUpdated = $boolUpdated && Configuration::updateGlobalValue($strCfgName, $mxdValue);
                } else {
                    $boolUpdated = $boolUpdated && Configuration::updateValue($strCfgName, $mxdValue);
                }
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage, $boolUpdated);

        return $boolUpdated;
    }

    /**
     * Delete settings fields.
     *
     * @return bool
     */
    public static function deleteSettings()
    {
        TNTOfficiel_Logstack::log();

        $boolDeleted = true;
        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        foreach (TNTOfficiel_Install::$arrConfigUpdateDeleteList as $strCfgName => $arrConfig) {
            // Must be preserved ?
            $boolPreserve = array_key_exists('preserve', $arrConfig) && $arrConfig['preserve'] === true;
            if (!$boolPreserve) {
                $boolDeleted = $boolDeleted && Configuration::deleteByName($strCfgName);
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage, $boolDeleted);

        return $boolDeleted;
    }


    /**
     * Creates the admin Tab.
     *
     * @return bool
     */
    public static function createTab()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        // Set displayed Tab name for each existing language.
        $arrTabNameLang = array();
        $arrLangList = Language::getLanguages(false);
        if (is_array($arrLangList)) {
            foreach ($arrLangList as $arrLang) {
                $arrTabNameLang[(int)$arrLang['id_lang']] = TNTOfficiel::CARRIER_NAME;
            }
        }
        //$arrTabNameLang[(int)Configuration::get('PS_LANG_DEFAULT')] = TNTOfficiel::CARRIER_NAME;

        // Creates the parent tab.
        $parentTab = new Tab();
        $parentTab->class_name = 'AdminTNTOfficiel';
        $parentTab->name = $arrTabNameLang;
        $parentTab->module = TNTOfficiel::MODULE_NAME;
        $parentTab->id_parent = 0;
        // TODO : AdminParentShipping as parent ?
        //$parentTab->id_parent = Tab::getIdFromClassName('AdminParentShipping');
        $boolResult = (bool)($parentTab->add());

        TNTOfficiel_Logger::logInstall($strLogMessage, $boolResult);

        return $boolResult;
    }

    /**
     * Delete the admin Tab.
     *
     * @return bool
     */
    public static function deleteTab()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        $objTabsPSCollection = Tab::getCollectionFromModule(TNTOfficiel::MODULE_NAME)->getAll();
        foreach ($objTabsPSCollection as $tab) {
            if (!$tab->delete()) {
                TNTOfficiel_Logger::logInstall($strLogMessage, false);

                return false;
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }


    /**
     * Update table to 1.2.20.
     *
     * Remove `tnt_log` table if exist.
     * Update `tnt_extra_address_data` table if exist, to `tntofficiel_cart` table.
     * Update `tnt_order` table if exist, to `tntofficiel_order` table.
     * Update `tnt_parcel` table if exist, to `tntofficiel_order_parcels` table.
     *
     * @return bool
     */
    public static function upgradeTables_1_2_20()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        $strTablePrefix = _DB_PREFIX_;

        // Test if table tnt_extra_address_data exist.
        $strSQLTableExtraExist = <<<SQL
SHOW TABLES LIKE '${strTablePrefix}tnt_extra_address_data';
SQL;
        // Test if table tnt_order exist.
        $strSQLTableOrderExist = <<<SQL
SHOW TABLES LIKE '${strTablePrefix}tnt_order';
SQL;
        // Test if table tnt_parcel exist.
        $strSQLTableParcelExist = <<<SQL
SHOW TABLES LIKE '${strTablePrefix}tnt_parcel';
SQL;


        // Drop unused log table.
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


        // Change columns.
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

        // Remove tnt_log table if exist.
        if (!$objDB->execute($strSQLTableLogDropTable)) {
            TNTOfficiel_Logger::logInstall($strLogMessage.' DROP tnt_log', false);
            TNTOfficiel_Logger::logInstall($objDB->getMsgError(), false);

            return false;
        }

        // Update tnt_extra_address_data table if exist, to tntofficiel_cart table.
        $arrDBResult = $objDB->executeS($strSQLTableExtraExist);
        if (count($arrDBResult) === 1) {
            if (!$objDB->execute($strSQLTableExtraAddColumns)
                || !$objDB->execute($strSQLTableExtraChangeColumns)
                || !$objDB->execute($strSQLTableExtraRenameTable)
            ) {
                TNTOfficiel_Logger::logInstall(
                    $strLogMessage.' ALTER tnt_extra_address_data TO tntofficiel_cart',
                    false
                );
                TNTOfficiel_Logger::logInstall($objDB->getMsgError(), false);

                return false;
            }
        }

        // Update tnt_order table if exist, to tntofficiel_order table.
        $arrDBResult = $objDB->executeS($strSQLTableOrderExist);
        if (count($arrDBResult) === 1) {
            if (!$objDB->execute($strSQLTableOrderChangeColumns)
                || !$objDB->execute($strSQLTableOrderRenameTable)
            ) {
                TNTOfficiel_Logger::logInstall(
                    $strLogMessage.' ALTER tnt_order TO tntofficiel_order',
                    false
                );
                TNTOfficiel_Logger::logInstall($objDB->getMsgError(), false);

                return false;
            }
        }

        // Update tnt_parcel table if exist, to tntofficiel_order_parcels table.
        $arrDBResult = $objDB->executeS($strSQLTableParcelExist);
        if (count($arrDBResult) === 1) {
            if (!$objDB->execute($strSQLTableParcelChangeColumns)
                || !$objDB->execute($strSQLTableParcelRenameTable)
            ) {
                TNTOfficiel_Logger::logInstall(
                    $strLogMessage.' ALTER tnt_parcels TO tntofficiel_order_parcels',
                    false
                );
                TNTOfficiel_Logger::logInstall($objDB->getMsgError(), false);

                return false;
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }


    /**
     * Update table to 1.2.23.
     * Used in upgrade, do not rename or remove.
     *
     * If `tntofficiel_cart` table and its `carrier_label` column exist,
     * then remove `tntofficiel_cart`.`carrier_label` column.
     *
     * @return bool
     */
    public static function upgradeTables_1_2_23()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        $strTablePrefix = _DB_PREFIX_;

        // Test if table tntofficiel_cart exist.
        $strSQLTableCartExist = <<<SQL
SHOW TABLES LIKE '${strTablePrefix}tntofficiel_cart';
SQL;

        // List columns to check carrier_label exist in table tntofficiel_cart.
        $strSQLTableCartColumns = <<<SQL
SHOW COLUMNS FROM `${strTablePrefix}tntofficiel_cart`;
SQL;

        // Remove column from tntofficiel_cart table.
        $strSQLTableCartDropColumns = <<<SQL
ALTER TABLE `${strTablePrefix}tntofficiel_cart`
  DROP COLUMN `carrier_label`;
SQL;

        $objDB = Db::getInstance();

        // If table `tntofficiel_cart` exist.
        $arrDBResult = $objDB->executeS($strSQLTableCartExist);
        if (count($arrDBResult) === 1) {
            // Search label columns.
            $boolCartColumnsLabelExist = false;
            $arrDBResultCartColumns = $objDB->executeS($strSQLTableCartColumns);
            foreach ($arrDBResultCartColumns as $arrRowCartColumns) {
                if (array_key_exists('Field', $arrRowCartColumns)
                    && $arrRowCartColumns['Field'] === 'carrier_label'
                ) {
                    // Found.
                    $boolCartColumnsLabelExist = true;
                }
            }
            // If `carrier_label` label exist in `tntofficiel_cart` table, it has to be removed.
            if ($boolCartColumnsLabelExist) {
                if (!$objDB->execute($strSQLTableCartDropColumns)) {
                    TNTOfficiel_Logger::logInstall(
                        $strLogMessage.' DROP COLUMN tntofficiel_cart.carrier_label',
                        false
                    );
                    TNTOfficiel_Logger::logInstall($objDB->getMsgError(), false);

                    return false;
                }
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }

    /**
     * Update table to 1.3.00.
     */
    public static function upgradeTables_1_3_00()
    {
        $boolCart = TNTOfficiel_Install::upgradeTables_1_3_00_cart();
        $boolCartIndex = TNTOfficiel_Install::upgradeTables_1_3_00_cart_index();
        $boolOrder = TNTOfficiel_Install::upgradeTables_1_3_00_order();
        $boolOrderIndex = TNTOfficiel_Install::upgradeTables_1_3_00_order_index();

        return ($boolCart && $boolCartIndex && $boolOrder && $boolOrderIndex);
    }

    /**
     * Update table to 1.3.00.
     *
     * If `tntofficiel_cart` table and its `delivery_price` column exist,
     * then remove `tntofficiel_cart`.`delivery_price` column.
     *
     * @return bool
     */
    public static function upgradeTables_1_3_00_cart()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        $strTablePrefix = _DB_PREFIX_;

        // Test if table tntofficiel_cart exist.
        $strSQLTableCartExist = <<<SQL
SHOW TABLES LIKE '${strTablePrefix}tntofficiel_cart';
SQL;

        // List columns to check delivery_price exist in table tntofficiel_cart.
        $strSQLTableCartColumns = <<<SQL
SHOW COLUMNS FROM `${strTablePrefix}tntofficiel_cart`;
SQL;

        // Remove column from tntofficiel_cart table.
        $strSQLTableCartDropColumns = <<<SQL
ALTER TABLE `${strTablePrefix}tntofficiel_cart`
  DROP COLUMN `delivery_price`;
SQL;

        $objDB = Db::getInstance();

        // If table `tntofficiel_cart` exist.
        $arrDBResult = $objDB->executeS($strSQLTableCartExist);
        if (count($arrDBResult) === 1) {
            // Search label columns.
            $boolCartColumnsLabelExist = false;
            $arrDBResultCartColumns = $objDB->executeS($strSQLTableCartColumns);
            foreach ($arrDBResultCartColumns as $arrRowCartColumns) {
                if (array_key_exists('Field', $arrRowCartColumns)
                    && $arrRowCartColumns['Field'] === 'delivery_price'
                ) {
                    // Found.
                    $boolCartColumnsLabelExist = true;
                }
            }
            // If `delivery_price` label exist in `tntofficiel_cart` table, it has to be removed.
            if ($boolCartColumnsLabelExist) {
                if (!$objDB->execute($strSQLTableCartDropColumns)) {
                    TNTOfficiel_Logger::logInstall(
                        $strLogMessage.' DROP COLUMN tntofficiel_cart.delivery_price',
                        false
                    );
                    TNTOfficiel_Logger::logInstall($objDB->getMsgError(), false);

                    return false;
                }
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }

    /**
     * Update table to 1.3.00.
     *
     * If `tntofficiel_order` table and its `previous_state` column exist,
     * then remove `tntofficiel_order`.`previous_state` column.
     *
     * @return bool
     */
    public static function upgradeTables_1_3_00_order()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        $strTablePrefix = _DB_PREFIX_;

        // Test if table tntofficiel_order exist.
        $strSQLTableOrderExist = <<<SQL
SHOW TABLES LIKE '${strTablePrefix}tntofficiel_order';
SQL;

        // List columns to check previous_state exist in table tntofficiel_order.
        $strSQLTableOrderColumns = <<<SQL
SHOW COLUMNS FROM `${strTablePrefix}tntofficiel_order`;
SQL;

        // Remove column from tntofficiel_order table.
        $strSQLTableOrderDropColumns = <<<SQL
ALTER TABLE `${strTablePrefix}tntofficiel_order`
  DROP COLUMN `previous_state`;
SQL;

        $objDB = Db::getInstance();

        // If table `tntofficiel_order` exist.
        $arrDBResult = $objDB->executeS($strSQLTableOrderExist);
        if (count($arrDBResult) === 1) {
            // Search label columns.
            $boolOrderColumnsLabelExist = false;
            $arrDBResultOrderColumns = $objDB->executeS($strSQLTableOrderColumns);
            foreach ($arrDBResultOrderColumns as $arrRowOrderColumns) {
                if (array_key_exists('Field', $arrRowOrderColumns)
                    && $arrRowOrderColumns['Field'] === 'previous_state'
                ) {
                    // Found.
                    $boolOrderColumnsLabelExist = true;
                }
            }
            // If `previous_state` label exist in `tntofficiel_order` table, it has to be removed.
            if ($boolOrderColumnsLabelExist) {
                if (!$objDB->execute($strSQLTableOrderDropColumns)) {
                    TNTOfficiel_Logger::logInstall(
                        $strLogMessage.' DROP COLUMN tntofficiel_order.previous_state',
                        false
                    );
                    TNTOfficiel_Logger::logInstall($objDB->getMsgError(), false);

                    return false;
                }
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }


    /**
     * Update cart table to 1.3.00.
     *
     * If `tntofficiel_cart` table exist and a unique index on `id_cart` column don't exist,
     * and if no duplicate value in `id_cart` column.
     * then add `id_cart` unique index.
     *
     * @return bool
     */
    public static function upgradeTables_1_3_00_cart_index()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        $strTablePrefix = _DB_PREFIX_;

        // Test if table tntofficiel_cart exist.
        $strSQLTableCartExist = <<<SQL
SHOW TABLES LIKE '${strTablePrefix}tntofficiel_cart';
SQL;
        // List indexes to check id_cart exist in table tntofficiel_cart.
        $strSQLTableCartIndexes = <<<SQL
SHOW INDEX FROM `${strTablePrefix}tntofficiel_cart`;
SQL;
        // Remove index from tntofficiel_cart table.
        $strSQLTableCartDropIndexes = <<<SQL
DROP INDEX `id_cart` ON `${strTablePrefix}tntofficiel_cart`;
SQL;
        // Check unique id_cart value from tntofficiel_cart table.
        $strSQLTableCartCheckUniqueIndexes = <<<SQL
SELECT `id_cart`, COUNT(`id_cart`) as `total`
FROM `${strTablePrefix}tntofficiel_cart`
GROUP BY `id_cart`
HAVING COUNT(`id_cart`) > 1;
SQL;
        // Create index from tntofficiel_cart table.
        $strSQLTableCartCreateUniqueIndexes = <<<SQL
CREATE UNIQUE INDEX `id_cart` ON `${strTablePrefix}tntofficiel_cart` (`id_cart`);
SQL;

        $objDB = Db::getInstance();

        // If tntofficiel_cart table exist.
        $arrDBResult = $objDB->executeS($strSQLTableCartExist);
        if (count($arrDBResult) === 1) {
            // Search existing name index.
            $boolCartIndexesLabelExist = false;
            $boolCartIndexesLabelUnique = false;
            $arrDBResultCartIndexes = $objDB->executeS($strSQLTableCartIndexes);
            foreach ($arrDBResultCartIndexes as $arrRowCartIndexes) {
                if (array_key_exists('Key_name', $arrRowCartIndexes)
                    && $arrRowCartIndexes['Key_name'] === 'id_cart'
                    //&& array_key_exists('Column_name', $arrRowCartIndexes)
                    //&& $arrRowCartIndexes['Column_name'] === 'id_cart'
                ) {
                    // Found.
                    $boolCartIndexesLabelExist = true;
                    if (array_key_exists('Non_unique', $arrRowCartIndexes)
                        && $arrRowCartIndexes['Non_unique'] === 0
                    ) {
                        // Found unique.
                        $boolCartIndexesLabelUnique = true;
                    }
                }
            }
            // If name do not exist in index or exist but not unique.
            if (!$boolCartIndexesLabelExist
                || !$boolCartIndexesLabelUnique
            ) {
                // If non unique index name exist.
                if ($boolCartIndexesLabelExist
                    && !$boolCartIndexesLabelUnique
                ) {
                    // remove index.
                    if (!$objDB->execute($strSQLTableCartDropIndexes)) {
                        TNTOfficiel_Logger::logInstall(
                            $strLogMessage.' >> DROP INDEX id_cart',
                            false
                        );
                        TNTOfficiel_Logger::logInstall($objDB->getMsgError(), false);

                        return false;
                    }
                }

                // If duplicate id_cart exist.
                $arrDBResult = $objDB->executeS($strSQLTableCartCheckUniqueIndexes);
                if (count($arrDBResult) > 0) {
                    $arrCartDupIndex = array();
                    foreach ($arrDBResult as $arrRow) {
                        $arrCartDupIndex[] = sprintf('%s:x%s', $arrRow['id_cart'], $arrRow['total']);
                    }
                    TNTOfficiel_Logger::logInstall(
                        sprintf($strLogMessage.' >> DUPLICATE id_cart : %s', implode(', ', $arrCartDupIndex)),
                        false
                    );

                    return false;
                }

                // Create index.
                if (!$objDB->execute($strSQLTableCartCreateUniqueIndexes)) {
                    TNTOfficiel_Logger::logInstall($strLogMessage, false);
                    TNTOfficiel_Logger::logInstall($objDB->getMsgError(), false);

                    return false;
                }
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }

    /**
     * Update order table to 1.3.00.
     *
     * If `tntofficiel_order` table exist and a unique index on `id_order` column don't exist,
     * and if no duplicate value in `id_order` column.
     * then add `id_order` unique index.
     *
     * @return bool
     */
    public static function upgradeTables_1_3_00_order_index()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        $strTablePrefix = _DB_PREFIX_;

        // Test if table tntofficiel_order exist.
        $strSQLTableOrderExist = <<<SQL
SHOW TABLES LIKE '${strTablePrefix}tntofficiel_order';
SQL;
        // List indexes to check id_order exist in table tntofficiel_order.
        $strSQLTableOrderIndexes = <<<SQL
SHOW INDEX FROM `${strTablePrefix}tntofficiel_order`;
SQL;
        // Remove index from tntofficiel_order table.
        $strSQLTableOrderDropIndexes = <<<SQL
DROP INDEX `id_order` ON `${strTablePrefix}tntofficiel_order`;
SQL;
        // Check unique id_order value from tntofficiel_order table.
        $strSQLTableOrderCheckUniqueIndexes = <<<SQL
SELECT `id_order`, COUNT(`id_order`) as `total`
FROM `${strTablePrefix}tntofficiel_order`
GROUP BY `id_order`
HAVING COUNT(`id_order`) > 1;
SQL;
        // Create index from tntofficiel_order table.
        $strSQLTableOrderCreateUniqueIndexes = <<<SQL
CREATE UNIQUE INDEX `id_order` ON `${strTablePrefix}tntofficiel_order` (`id_order`);
SQL;

        $objDB = Db::getInstance();

        // If table tntofficiel_order exist.
        $arrDBResult = $objDB->executeS($strSQLTableOrderExist);
        if (count($arrDBResult) === 1) {
            // Search existing name index.
            $boolOrderIndexesLabelExist = false;
            $boolOrderIndexesLabelUnique = false;
            $arrDBResultOrderIndexes = $objDB->executeS($strSQLTableOrderIndexes);
            foreach ($arrDBResultOrderIndexes as $arrRowOrderIndexes) {
                if (array_key_exists('Key_name', $arrRowOrderIndexes)
                    && $arrRowOrderIndexes['Key_name'] === 'id_order'
                    //&& array_key_exists('Column_name', $arrRowOrderIndexes)
                    //&& $arrRowOrderIndexes['Column_name'] === 'id_order'
                ) {
                    // Found.
                    $boolOrderIndexesLabelExist = true;
                    if (array_key_exists('Non_unique', $arrRowOrderIndexes)
                        && $arrRowOrderIndexes['Non_unique'] === 0
                    ) {
                        // Found unique.
                        $boolOrderIndexesLabelUnique = true;
                    }
                }
            }
            // If name do not exist in index or exist but not unique.
            if (!$boolOrderIndexesLabelExist
                || !$boolOrderIndexesLabelUnique
            ) {
                // If non unique index name exist.
                if ($boolOrderIndexesLabelExist
                    && !$boolOrderIndexesLabelUnique
                ) {
                    // remove index.
                    if (!$objDB->execute($strSQLTableOrderDropIndexes)) {
                        TNTOfficiel_Logger::logInstall(
                            $strLogMessage.' >> DROP INDEX id_order',
                            false
                        );

                        return false;
                    }
                }

                // If duplicate id_order exist.
                $arrDBResult = $objDB->executeS($strSQLTableOrderCheckUniqueIndexes);
                if (count($arrDBResult) > 0) {
                    $arrOrderDupIndex = array();
                    foreach ($arrDBResult as $arrRow) {
                        $arrOrderDupIndex[] = sprintf('%s:x%s', $arrRow['id_order'], $arrRow['total']);
                    }
                    TNTOfficiel_Logger::logInstall(
                        sprintf($strLogMessage.' >> DUPLICATE id_order : %s', implode(', ', $arrOrderDupIndex)),
                        false
                    );

                    return false;
                }

                // Create index.
                if (!$objDB->execute($strSQLTableOrderCreateUniqueIndexes)) {
                    TNTOfficiel_Logger::logInstall($strLogMessage, false);
                    TNTOfficiel_Logger::logInstall($objDB->getMsgError(), false);

                    return false;
                }
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }

    /**
     * Update table to 1.3.01.
     */
    public static function upgradeTables_1_3_01()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        $strTablePrefix = _DB_PREFIX_;

        // Test if table tnt_order exist.
        $strSQLTableOrderExist = <<<SQL
SHOW TABLES LIKE '${strTablePrefix}tnt_order';
SQL;

        // Rename and change existing columns to tnt_extra_address_data table.
        $strSQLTableOrderChangeColumns = <<<SQL
ALTER TABLE `${strTablePrefix}tntofficiel_order`
CHANGE COLUMN `shipping_date` `shipping_date` DATE NOT NULL DEFAULT '0000-00-00' AFTER `pickup_number`,
CHANGE COLUMN `due_date` `due_date` DATE NOT NULL DEFAULT '0000-00-00' AFTER `shipping_date`,
CHANGE COLUMN `start_date` `start_date` DATE NOT NULL DEFAULT '0000-00-00' AFTER `due_date`;
SQL;

        $objDB = Db::getInstance();

        // Update tnt_parcel table if exist, to tntofficiel_order_parcels table.
        $arrDBResult = $objDB->executeS($strSQLTableOrderExist);
        if (count($arrDBResult) === 1) {
            if (!$objDB->execute($strSQLTableOrderChangeColumns)) {
                TNTOfficiel_Logger::logInstall(
                    $strLogMessage.' ALTER tntofficiel_order using DATE',
                    false
                );
                TNTOfficiel_Logger::logInstall($objDB->getMsgError(), false);

                return false;
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }

    /**
     * Creates the tables needed by the module.
     *
     * @return bool
     */
    public static function createTables()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        // Update if required.
        TNTOfficiel_Install::upgradeTables_1_2_20();
        TNTOfficiel_Install::upgradeTables_1_2_23();
        TNTOfficiel_Install::upgradeTables_1_3_00();
        TNTOfficiel_Install::upgradeTables_1_3_01();

        $strTablePrefix = _DB_PREFIX_;
        $strTableEngine = _MYSQL_ENGINE_;

        // Create tntofficiel_cart table.
        $strSQLCreateCart = <<<SQL
CREATE TABLE IF NOT EXISTS `${strTablePrefix}tntofficiel_cart` (
    `id_tntofficiel_cart`   INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_cart`               INT(10) UNSIGNED NOT NULL,
    `carrier_code`          VARCHAR(64) NOT NULL DEFAULT '',
    `delivery_point`        TEXT NULL,
    `customer_email`        VARCHAR(128) NOT NULL DEFAULT '',
    `customer_mobile`       VARCHAR(32) NOT NULL DEFAULT '',
    `address_building`      VARCHAR(16) NOT NULL DEFAULT '',
    `address_accesscode`    VARCHAR(16) NOT NULL DEFAULT '',
    `address_floor`         VARCHAR(16) NOT NULL DEFAULT '',
    PRIMARY KEY (`id_tntofficiel_cart`),
    UNIQUE INDEX `id_cart` (`id_cart`)
) ENGINE = ${strTableEngine} DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';
SQL;

        // Create tntofficiel_order table.
        $strSQLCreateOrder = <<<SQL
CREATE TABLE IF NOT EXISTS `${strTablePrefix}tntofficiel_order` (
    `id_tntofficiel_order`  INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_order` INT(10)      UNSIGNED NOT NULL,
    `carrier_code`          VARCHAR(64) NOT NULL DEFAULT '',
    `carrier_label`         VARCHAR(255) NOT NULL DEFAULT '',
    `carrier_xett`          VARCHAR(5) NOT NULL DEFAULT '',
    `carrier_pex`           VARCHAR(4) NOT NULL DEFAULT '',
    `bt_filename`           VARCHAR(64) NOT NULL DEFAULT '',
    `is_shipped`            TINYINT(1) NOT NULL DEFAULT '0',
    `pickup_number`         VARCHAR(50) NOT NULL DEFAULT '',
    `shipping_date`         DATE NOT NULL DEFAULT '0000-00-00',
    `due_date`              DATE NOT NULL DEFAULT '0000-00-00',
    `start_date`            DATE NOT NULL DEFAULT '0000-00-00',
    PRIMARY KEY (`id_tntofficiel_order`),
    UNIQUE INDEX `id_order` (`id_order`)
) ENGINE = ${strTableEngine} DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';
SQL;

        // Create tntofficiel_order_parcels table.
        $strSQLCreateParcels = <<<SQL
CREATE TABLE IF NOT EXISTS `${strTablePrefix}tntofficiel_order_parcels` (
    `id_parcel`             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_order`              INT(10) UNSIGNED NOT NULL,
    `weight`                DECIMAL(20,6) NOT NULL DEFAULT '0.000000',
    `tracking_url`          TEXT,
    `parcel_number`         VARCHAR(16),
    `pdl`                   TEXT,
    PRIMARY KEY (`id_parcel`)
) ENGINE = ${strTableEngine} DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';
SQL;

        // Create tntofficiel_cache table.
        $strSQLCreateCache = <<<SQL
CREATE TABLE IF NOT EXISTS `${strTablePrefix}tntofficiel_cache` (
    `id_tntofficiel_cache`  INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `str_key`               VARCHAR(255) NOT NULL,
    `str_value`             TEXT NULL,
    `int_ts`                INT(10) UNSIGNED NOT NULL,
-- Key.
    PRIMARY KEY (`id_tntofficiel_cache`),
    UNIQUE INDEX `str_key` (`str_key`)
) ENGINE = ${strTableEngine} DEFAULT CHARSET='utf8' COLLATE='utf8_general_ci';
SQL;

        $objDB = Db::getInstance();

        if (!$objDB->execute($strSQLCreateCart)
            || !$objDB->execute($strSQLCreateOrder)
            || !$objDB->execute($strSQLCreateParcels)
            || !$objDB->execute($strSQLCreateCache)
        ) {
            TNTOfficiel_Logger::logInstall($strLogMessage, false);

            return false;
        }

        // Flush cache.
        TNTOfficielCache::truncate();

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }
}
