<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_Carrier
{
    /**
     * Configuration name.
     *
     * @var string
     */
    private static $strConfigNameCarrierID = 'TNTOFFICIEL_CARRIER_LIST';


    public static $arrCarrierCodeInfos = array(
        'N_ENTERPRISE' => array(
            'label'       => '08:00 Express en Entreprise',
            'delay'       => 'Livraison en entreprise dès le lendemain de l\'expédition, avant 8 heures.',
            'description' => 'Pour une livraison aux entreprises en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande, avant 8 heures.'
        ),
        'A_ENTERPRISE' => array(
            'label'       => '09:00 Express en Entreprise',
            'delay'       => 'Livraison en entreprise dès le lendemain de l\'expédition, avant 9 heures.',
            'description' => 'Pour une livraison aux entreprises en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande, avant 9 heures.'
        ),
        'T_ENTERPRISE' => array(
            'label'       => '10:00 Express en Entreprise',
            'delay'       => 'Livraison en entreprise dès le lendemain de l\'expédition, avant 10 heures.',
            'description' => 'Pour une livraison aux entreprises en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande, avant 10 heures.'
        ),
        'M_ENTERPRISE' => array(
            'label'       => '12:00 Express en Entreprise',
            'delay'       => 'Livraison en entreprise dès le lendemain de l\'expédition, avant midi.',
            'description' => 'Pour une livraison aux entreprises en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande, avant midi.'
        ),
        'J_ENTERPRISE' => array(
            'label'       => 'Express en entreprise',
            'delay'       => 'Livraison en entreprise dès le lendemain de l\'expédition.',
            'description' => 'Pour une livraison aux entreprises en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande<small> <i><sup>(1)</sup></i></small>.<br />
                              <small><i><sup>(1)</sup> avant 13 heures ou en début d\'après-midi en zone rurale.</i></small>'
        ),
        'AZ_INDIVIDUAL' => array(
            'label'       => '09:00 Express à Domicile',
            'delay'       => 'Livraison à domicile dès le lendemain de l\'expédition, avant 9 heures.',
            'description' => 'Pour une livraison à domicile en France métropolitaine.<br />
                              Livraison  en mains propres et contre signature dès le lendemain de l\'expédition de votre commande, avant 9 heures.'
        ),
        'TZ_INDIVIDUAL' => array(
            'label'       => '10:00 Express à Domicile',
            'delay'       => 'Livraison à domicile dès le lendemain de l\'expédition, avant 10 heures.',
            'description' => 'Pour une livraison à domicile en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande, avant 10 heures.'
        ),
        'MZ_INDIVIDUAL' => array(
            'label'       => '12:00 Express à Domicile',
            'delay'       => 'Livraison à domicile dès le lendemain de l\'expédition, avant midi.',
            'description' => 'Pour une livraison à domicile en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande, avant midi.'
        ),
        'JZ_INDIVIDUAL' => array(
            'label'       => 'Express à domicile',
            'delay'       => 'Livraison à domicile dès le lendemain de l\'expédition.',
            'description' => 'Pour une livraison à domicile en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande<small> <i><sup>(1)</sup></i></small>.<br />
                              <small><i><sup>(1)</sup> avant 13 heures ou en début d\'après-midi en zone rurale.</i></small>'
        ),
        'JD_DROPOFFPOINT' => array(
            'label'       => 'Express chez un commerçant partenaire',
            'delay'       => 'Livraison dès le lendemain de l\'expédition.',
            'description' => 'Mise à disposition chez l\'un  des 4500 commerçants partenaires en France métropolitaine.<br />
                              Remise contre signature et présentation d’une pièce d’identité dès le lendemain de l\'expédition de votre commande<small> <i><sup>(1)</sup></i></small>.<br />
                              <small><i><sup>(1)</sup> avant 13 heures ou en début d\'après-midi en zone rurale.</i></small>'
        ),
        'J_DEPOT' => array(
            'label'       => 'Express en agence TNT',
            'delay'       => 'Livraison dès 8 heures le lendemain de l\'expédition. Mise à votre disposition pendant 10 Jours.',
            'description' => 'Pour une livraison dans une de nos agences TNT en France métropolitaine.<br />
                              Mise à votre disposition sur présentation d\'une pièce d\'identité et contre signature dès 8 heures le lendemain de l\'expédition de votre commande et ce pendant 10 Jours.'
        ),
        /* RP */
        'AP_ENTERPRISE' => array(
            'label'       => '09:00 Express en Entreprise',
            'delay'       => 'Livraison en entreprise dès le lendemain de l\'expédition, avant 9 heures.',
            'description' => 'Pour une livraison aux entreprises en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande, avant 9 heures.<br />
                              Colis remis contre un règlement par chèque.'
        ),
        'TP_ENTERPRISE' => array(
            'label'       => '10:00 Express en Entreprise',
            'delay'       => 'Livraison en entreprise dès le lendemain de l\'expédition, avant 10 heures.',
            'description' => 'Pour une livraison aux entreprises en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande, avant 10 heures.<br />
                              Colis remis contre un règlement par chèque.'
        ),
        'MP_ENTERPRISE' => array(
            'label'       => '12:00 Express en Entreprise',
            'delay'       => 'Livraison en entreprise dès le lendemain de l\'expédition, avant midi.',
            'description' => 'Pour une livraison aux entreprises en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande, avant midi.<br />
                              Colis remis contre un règlement par chèque.'
        ),
        'JP_ENTERPRISE' => array(
            'label'       => 'Express en entreprise',
            'delay'       => 'Livraison en entreprise dès le lendemain de l\'expédition.',
            'description' => 'Pour une livraison aux entreprises en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande<small> <i><sup>(1)</sup></i></small>.<br />
                              Colis remis contre un règlement par chèque.<br />
                              <small><i><sup>(1)</sup> avant 13 heures ou en début d\'après-midi en zone rurale.</i></small>'
        ),
        'JP_DEPOT' => array(
            'label'       => 'Express en agence TNT',
            'delay'       => 'Livraison dès 8 heures le lendemain de l\'expédition. Mise à votre disposition pendant 10 Jours.',
            'description' => 'Pour une livraison dans une de nos agences TNT en France métropolitaine.<br />
                              Mise à votre disposition sur présentation d\'une pièce d\'identité et contre signature dès 8 heures le lendemain de l\'expédition de votre commande et ce pendant 10 Jours.<br />
                              Colis remis contre un règlement par chèque.'
        ),
        /* ESP */
        'AW_ENTERPRISE' => array(
            'label'       => '09:00 Express en Entreprise',
            'delay'       => 'Livraison en entreprise dès le lendemain de l\'expédition, avant 9 heures.',
            'description' => 'Pour une livraison aux entreprises en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande, avant 9 heures.<br />
                              Marchandises sensibles expédiées avec une sûreté renforcée du ramassage jusqu\'à la livraison.'
        ),
        'TW_ENTERPRISE' => array(
            'label'       => '10:00 Express en Entreprise',
            'delay'       => 'Livraison en entreprise dès le lendemain de l\'expédition, avant 10 heures.',
            'description' => 'Pour une livraison aux entreprises en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande, avant 10 heures.<br />
                              Marchandises sensibles expédiées avec une sûreté renforcée du ramassage jusqu\'à la livraison.'
        ),
        'MW_ENTERPRISE' => array(
            'label'       => '12:00 Express en Entreprise',
            'delay'       => 'Livraison en entreprise dès le lendemain de l\'expédition, avant midi.',
            'description' => 'Pour une livraison aux entreprises en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande, avant midi.<br />
                              Marchandises sensibles expédiées avec une sûreté renforcée du ramassage jusqu\'à la livraison.'
        ),
        'JW_ENTERPRISE' => array(
            'label'       => 'Express en entreprise',
            'delay'       => 'Livraison en entreprise dès le lendemain de l\'expédition.',
            'description' => 'Pour une livraison aux entreprises en France métropolitaine.<br />
                              Livraison en mains propres et contre signature dès le lendemain de l\'expédition de votre commande<small> <i><sup>(1)</sup></i></small>.<br />
                              Marchandises sensibles expédiées avec une sûreté renforcée du ramassage jusqu\'à la livraison.<br />
                              <small><i><sup>(1)</sup> avant 13 heures ou en début d\'après-midi en zone rurale.</i></small>'
        ),
        'JW_DEPOT' => array(
            'label'       => 'Express en agence TNT',
            'delay'       => 'Livraison dès 8 heures le lendemain de l\'expédition. Mise à votre disposition pendant 10 Jours.',
            'description' => 'Pour une livraison dans une de nos agences TNT en France métropolitaine.<br />
                              Mise à votre disposition sur présentation d\'une pièce d\'identité et contre signature dès 8 heures le lendemain de l\'expédition de votre commande et ce pendant 10 Jours.<br />
                              Marchandises sensibles expédiées avec une sûreté renforcée du ramassage jusqu\'à la livraison.'
        ),
    );


    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * Get available carrier code from account.
     *
     * @return array
     */
    public static function getAllAccountCarrierCode()
    {
        // Default.
        $arrAccountCarrierCode = array (
            'A_ENTERPRISE',
            'T_ENTERPRISE',
            'M_ENTERPRISE',
            'J_ENTERPRISE',
            'AZ_INDIVIDUAL',
            'TZ_INDIVIDUAL',
            'MZ_INDIVIDUAL',
            'JZ_INDIVIDUAL',
            'JD_DROPOFFPOINT',
            'J_DEPOT'
        );

        $arrAcountInfos = TNTOfficiel_JsonRPCClient::getAccountInfos();
        if (is_array($arrAcountInfos) && array_key_exists('products', $arrAcountInfos)
            && is_array($arrAcountInfos['products'])
        ) {
            $arrAccountCarrierCode = $arrAcountInfos['products'];
        }

        return $arrAccountCarrierCode;
    }

    /**
     * Get the available TNT carrier ID List for each code.
     *
     * @return array
     */
    public static function getAllAccountCarrierID()
    {
        $arrAllCurrentCarrierID = TNTOfficiel_Carrier::getAllCurrentCarrierID();
        $arrAllAccountCarrierCode = array_flip(TNTOfficiel_Carrier::getAllAccountCarrierCode());

        return array_intersect_key($arrAllCurrentCarrierID, $arrAllAccountCarrierCode);
    }

    /**
     * Create, renew or restore an existing TNT carrier.
     *
     * @param string $strArgCarrierCode
     * @param bool $boolArgForceReNew
     *
     * @return bool
     */
    public static function installCurrentCarrier($strArgCarrierCode, $boolArgForceReNew = false)
    {
        TNTOfficiel_Logstack::log();

        $boolResult = false;

        // If not a renew.
        if (!$boolArgForceReNew) {
            // Try to undelete a previously deleted carrier.
            $boolResult = TNTOfficiel_Carrier::undeleteCurrentCarrier($strArgCarrierCode);
        }

        // If undelete not succeed.
        if (!$boolResult) {
            // Delete carrier if exist.
            $boolResult = TNTOfficiel_Carrier::deleteCurrentCarrier($strArgCarrierCode);
            // Create a new one if deleted or not exist.
            $arrAccountCarrierCode = TNTOfficiel_Carrier::getAllAccountCarrierCode();
            if ($boolResult && in_array($strArgCarrierCode, $arrAccountCarrierCode)) {
                $boolResult = TNTOfficiel_Carrier::createCurrentCarrier($strArgCarrierCode);
            }
        }

        return $boolResult;
    }

    /**
     * Create, renew or restore all existing TNT carrier.
     *
     * @param bool $boolArgForceReNew
     *
     * @return bool
     */
    public static function installAllCurrentCarrier($boolArgForceReNew = false)
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__.($boolArgForceReNew?' [renew]':'');

        foreach (TNTOfficiel_Carrier::$arrCarrierCodeInfos as $strCarrierCode => $arrCarrierInfo) {
            $boolResult = TNTOfficiel_Carrier::installCurrentCarrier($strCarrierCode, $boolArgForceReNew);
            if (!$boolResult) {
                TNTOfficiel_Logger::logInstall($strLogMessage, false);

                return false;
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }

    /**
     * Delete existing TNT carrier.
     *
     * @return bool
     */
    public static function uninstallAllCurrentCarrier()
    {
        TNTOfficiel_Logstack::log();

        $strLogMessage = __CLASS__.'::'.__FUNCTION__;

        foreach (TNTOfficiel_Carrier::$arrCarrierCodeInfos as $strCarrierCode => $arrCarrierInfo) {
            // Delete carrier if exist.
            $boolResult = TNTOfficiel_Carrier::deleteCurrentCarrier($strCarrierCode);
            if (!$boolResult) {
                TNTOfficiel_Logger::logInstall($strLogMessage, false);

                return false;
            }
        }

        TNTOfficiel_Logger::logInstall($strLogMessage);

        return true;
    }


    /**
     * Create a new TNT carrier from code and save carrier ID as current one for code.
     *
     * @param string $strArgCarrierCode
     *
     * @return bool
     */
    public static function createCurrentCarrier($strArgCarrierCode)
    {
        TNTOfficiel_Logstack::log();

        if (!array_key_exists($strArgCarrierCode, TNTOfficiel_Carrier::$arrCarrierCodeInfos)) {
            return false;
        }

        // Create new carrier.
        $objCarrierNew = new Carrier();
        $objCarrierNew->active = true;
        $objCarrierNew->deleted = false;
        // Carrier used for module.
        $objCarrierNew->is_module = true;
        $objCarrierNew->external_module_name = TNTOfficiel::MODULE_NAME;
        // Carrier name.
        $objCarrierNew->name = TNTOfficiel::CARRIER_NAME.' - '.TNTOfficiel_Carrier::getCarrierCodeName($strArgCarrierCode);
        // Carrier delay description per language ISO code [1-128] characters.
        $arrLangList = Language::getLanguages(true);
        $arrDelayLang = array();
        if (is_array($arrLangList)) {
            foreach ($arrLangList as $arrLang) {
                $arrDelayLang[(int)$arrLang['id_lang']] = TNTOfficiel_Carrier::$arrCarrierCodeInfos[$strArgCarrierCode]['delay'];
            }
        }
        $objCarrierNew->delay = $arrDelayLang;
        // Carrier tracking URL.
        //$objCarrierNew->url = '';

        // Applying tax rules group (0: Disable).
        $objCarrierNew->id_tax_rules_group = 0;

        // Disable adding handling charges from config PS_SHIPPING_HANDLING.
        $objCarrierNew->shipping_handling = false;
        // Enable use of Cart getPackageShippingCost, getOrderShippingCost or getOrderShippingCostExternal
        $objCarrierNew->shipping_external = true;
        // Enable calculations for the ranges.
        $objCarrierNew->need_range = true;
        $objCarrierNew->range_behavior = 0;

        // Unable to create new carrier.
        if (!$objCarrierNew->add()) {
            return false;
        }


        $intCarrierTaxRulesGroupID = 0;
        // Find Taxe Rule Group : FR 20%, Enabled, Non-Deleted, named like 'FR\ Taux\ standard'.
        $intCountryFRID = (int)Country::getByIso('FR');
        $arrTaxRulesGroup = TaxRulesGroup::getAssociatedTaxRatesByIdCountry($intCountryFRID);
        foreach ($arrTaxRulesGroup as $intTaxRulesGroupID => $strTaxeAmount) {
            if ((int)$strTaxeAmount === 20) {
                $objTaxRulesGroup = new TaxRulesGroup((int)$intTaxRulesGroupID);
                if ($objTaxRulesGroup->active
                    && (!property_exists($objTaxRulesGroup, 'deleted') || !$objTaxRulesGroup->deleted)
                    && preg_match('/^FR\ Taux\ standard/ui', $objTaxRulesGroup->name) === 1
                ) {
                    $intCarrierTaxRulesGroupID = (int)$intTaxRulesGroupID;
                    break;
                }
            }
        }
        // Applying tax rules group (0: Disable).
        $objCarrierNew->setTaxRulesGroup($intCarrierTaxRulesGroupID);


        $objDB = Db::getInstance();

        $groups = Group::getGroups(true);
        foreach ($groups as $group) {
            $objDB->insert(
                'carrier_group',
                array(
                    'id_carrier' => (int)$objCarrierNew->id,
                    'id_group' => (int)$group['id_group'],
                )
            );
        }

        $objRangePrice = new RangePrice();
        $objRangePrice->id_carrier = $objCarrierNew->id;
        $objRangePrice->delimiter1 = '0';
        $objRangePrice->delimiter2 = '1000000';
        $objRangePrice->add();

        $objRangeWeight = new RangeWeight();
        $objRangeWeight->id_carrier = $objCarrierNew->id;
        $objRangeWeight->delimiter1 = '0';
        $objRangeWeight->delimiter2 = '1000000';
        $objRangeWeight->add();

        // Get active zones list.
        $arrZoneList = Zone::getZones(true);
        foreach ($arrZoneList as $arrZone) {
            $objDB->insert(
                'carrier_zone',
                array(
                    'id_carrier' => (int)$objCarrierNew->id,
                    'id_zone' => (int)$arrZone['id_zone']
                )
            );
            $objDB->insert(
                'delivery',
                array(
                    'id_carrier' => (int)$objCarrierNew->id,
                    'id_range_price' => (int)$objRangePrice->id,
                    'id_range_weight' => null,
                    'id_zone' => (int)$arrZone['id_zone'],
                    'price' => '0'
                ),
                true,
                false
            );
            $objDB->insert(
                'delivery',
                array(
                    'id_carrier' => (int)$objCarrierNew->id,
                    'id_range_price' => null,
                    'id_range_weight' => (int)$objRangeWeight->id,
                    'id_zone' => (int)$arrZone['id_zone'],
                    'price' => '0'
                ),
                true,
                false
            );
        }

        // Save the carrier ID.
        $boolResult = TNTOfficiel_Carrier::setCurrentCarrierID($strArgCarrierCode, $objCarrierNew->id);

        // Add carrier logo.
        $boolResult = copy(
            _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.'/views/img/carrier/93x66.png',
            _PS_SHIP_IMG_DIR_.(int)$objCarrierNew->id.'.jpg'
        ) && $boolResult;

        return $boolResult;
    }

    /**
     * Load a current carrier object model from code.
     *
     * @param string $strArgCarrierCode
     *
     * @return Carrier|null
     */
    public static function loadCurrentCarrier($strArgCarrierCode)
    {
        TNTOfficiel_Logstack::log();

        // Get current TNT carrier ID.
        $intCarrierIDTNT = TNTOfficiel_Carrier::getCurrentCarrierID($strArgCarrierCode);
        // If TNT carrier ID not available.
        if ($intCarrierIDTNT === null) {
            return null;
        }

        // Load TNT carrier.
        $objCarrierTNT = new Carrier($intCarrierIDTNT);

        // If TNT carrier object not available.
        if (!(Validate::isLoadedObject($objCarrierTNT) && (int)$objCarrierTNT->id === $intCarrierIDTNT)) {
            return null;
        }

        return $objCarrierTNT;
    }

    /**
     * Delete an existing current TNT carrier from code by setting its flag to deleted.
     *
     * @param string $strArgCarrierCode
     *
     * @return bool
     */
    public static function deleteCurrentCarrier($strArgCarrierCode)
    {
        TNTOfficiel_Logstack::log();

        // Load current TNT carrier object.
        $objCarrierTNT = TNTOfficiel_Carrier::loadCurrentCarrier($strArgCarrierCode);
        // If TNT carrier object not available.
        if ($objCarrierTNT === null) {
            // Nothing to delete.
            return true;
        }

        $objCarrierTNT->active = false;
        $objCarrierTNT->deleted = true;
        $boolResult = $objCarrierTNT->save();

        return $boolResult;
    }

    /**
     * Undelete an existing current TNT carrier from code by setting its flag to not deleted.
     *
     * @param string $strArgCarrierCode
     *
     * @return bool
     */
    public static function undeleteCurrentCarrier($strArgCarrierCode)
    {
        TNTOfficiel_Logstack::log();

        // Load current TNT carrier object.
        $objCarrierTNT = TNTOfficiel_Carrier::loadCurrentCarrier($strArgCarrierCode);
        // If TNT carrier object not available.
        if ($objCarrierTNT === null) {
            // Unable to undelete.
            return false;
        }

        $objCarrierTNT->active = true;
        $objCarrierTNT->deleted = false;
        // Always set the right module name.
        $objCarrierTNT->external_module_name = TNTOfficiel::MODULE_NAME;
        // Always set the right carrier name.
        $objCarrierTNT->name = TNTOfficiel::CARRIER_NAME.' - '.TNTOfficiel_Carrier::getCarrierCodeName($strArgCarrierCode);
        // Carrier delay description per language ISO code [1-128] caractères.
        $arrDelay = array(
            'fr' => TNTOfficiel_Carrier::$arrCarrierCodeInfos[$strArgCarrierCode]['delay']
        );
        // Always set the right carrier delay.
        $arrDelay[Configuration::get('PS_LANG_DEFAULT')] = TNTOfficiel_Carrier::$arrCarrierCodeInfos[$strArgCarrierCode]['delay'];
        $objCarrierTNT->delay = $arrDelay;

        $boolResult = $objCarrierTNT->save();

        return $boolResult;
    }


    /**
     * Get the carrier full name from code.
     *
     * @param string $strArgCarrierCode
     *
     * @return string
     */
    public static function getCarrierCodeName($strArgCarrierCode)
    {
        return strip_tags(TNTOfficiel_Carrier::$arrCarrierCodeInfos[$strArgCarrierCode]['label']);
    }


    /**
     * Get the current TNT carrier ID List for each code.
     *
     * @return array
     */
    public static function getAllCurrentCarrierID()
    {
        TNTOfficiel_Logstack::log();

        // Get current TNT carrier ID list.
        $arrCarrierCodeID = Tools::unSerialize(Configuration::get(TNTOfficiel_Carrier::$strConfigNameCarrierID));
        if (!is_array($arrCarrierCodeID)) {
            $arrCarrierCodeID = array();
        }

        // Filtering an ordering.
        $arrCarrierCodeID = array_merge(
            array_intersect_key(array_flip(array_keys(TNTOfficiel_Carrier::$arrCarrierCodeInfos)), $arrCarrierCodeID),
            $arrCarrierCodeID)
        ;

        return $arrCarrierCodeID;
    }

    /**
     * Get the current TNT carrier ID from code name.
     *
     * @param string $strArgCarrierCode
     *
     * @return int|null
     */
    public static function getCurrentCarrierID($strArgCarrierCode)
    {
        TNTOfficiel_Logstack::log();

        // Get current TNT carrier ID list.
        $arrCarrierCodeID = TNTOfficiel_Carrier::getAllCurrentCarrierID();

        // Get current TNT carrier ID from Code.
        $intCarrierIDTNT = array_key_exists($strArgCarrierCode, $arrCarrierCodeID) ? $arrCarrierCodeID[$strArgCarrierCode] : null;
        // Carrier ID must be an integer greater than 0.
        if (empty($intCarrierIDTNT) || $intCarrierIDTNT != (int)$intCarrierIDTNT || !((int)$intCarrierIDTNT > 0)) {
            return null;
        }

        return (int)$intCarrierIDTNT;
    }

    /**
     * Set the current TNT carrier ID for code.
     *
     * @param string $strArgCarrierCode
     * @param int $intArgCarrierID
     *
     * @return bool
     */
    public static function setCurrentCarrierID($strArgCarrierCode, $intArgCarrierID)
    {
        TNTOfficiel_Logstack::log();

        // Carrier ID must be an integer greater than 0.
        if (empty($intArgCarrierID) || $intArgCarrierID != (int)$intArgCarrierID || !((int)$intArgCarrierID > 0)) {
            return false;
        }

        // Get current TNT carrier ID list.
        $arrCarrierCodeID = TNTOfficiel_Carrier::getAllCurrentCarrierID();

        $arrCarrierCodeID[$strArgCarrierCode] = (int)$intArgCarrierID;

        return Configuration::updateGlobalValue(
            TNTOfficiel_Carrier::$strConfigNameCarrierID,
            serialize($arrCarrierCodeID)
        );
    }

    /**
     * Modify a current TNT carrier ID to a new one.
     *
     * @param int $intArgCarrierOldID
     * @param int $intArgCarrierNewID
     *
     * @return bool
     */
    public static function updateCurrentCarrierID($intArgCarrierOldID, $intArgCarrierNewID)
    {
        TNTOfficiel_Logstack::log();

        // Get the current TNT carrier code name from current ID.
        $strCarrierCode = TNTOfficiel_Carrier::getCurrentCarrierCode($intArgCarrierOldID);

        // If current carrier code not found from ID.
        if ($strCarrierCode === null) {
            return false;
        }

        // Set the new TNT carrier ID for code.
        return TNTOfficiel_Carrier::setCurrentCarrierID($strCarrierCode, $intArgCarrierNewID);
    }

    /**
     * Get the current TNT carrier code name from ID.
     *
     * @param int $intArgCarrierID
     *
     * @return string|null
     */
    public static function getCurrentCarrierCode($intArgCarrierID)
    {
        TNTOfficiel_Logstack::log();

        $intCarrierID = (int)$intArgCarrierID;
        // Get current TNT carrier ID list.
        $arrCarrierCodeID = TNTOfficiel_Carrier::getAllCurrentCarrierID();

        // Get current TNT carrier ID from Code.
        $strCarrierCode = array_search($intCarrierID, $arrCarrierCodeID, true);
        // Carrier ID must be an integer greater than 0.
        if (empty($strCarrierCode) || !array_key_exists($strCarrierCode, $arrCarrierCodeID)) {
            return null;
        }

        return (string)$strCarrierCode;
    }


    /**
     * Load a carrier object model from id.
     *
     * @param int $intArgCarrierID
     *
     * @return Carrier|null
     */
    public static function loadCarrier($intArgCarrierID)
    {
        TNTOfficiel_Logstack::log();

        $intCarrierID = (int)$intArgCarrierID;

        // Load carrier.
        $objCarrier = new Carrier($intCarrierID);

        // If carrier object not available.
        if (!(Validate::isLoadedObject($objCarrier) && (int)$objCarrier->id === $intCarrierID)) {
            return null;
        }

        return $objCarrier;
    }

    /**
     * Force all current carrier settings.
     *
     * @return bool
     */
    public static function forceAllCurrentCarrierDefaultValues()
    {
        TNTOfficiel_Logstack::log();

        foreach (TNTOfficiel_Carrier::$arrCarrierCodeInfos as $strCarrierCode => $arrCarrierInfo) {
            $boolResult = TNTOfficiel_Carrier::forceCurrentCarrierDefaultValues($strCarrierCode);
            if (!$boolResult) {
                return false;
            }
        }

        return true;
    }

    /**
     * Force a current carrier settings.
     *
     * @param string $strArgCarrierCode
     *
     * @return bool
     */
    public static function forceCurrentCarrierDefaultValues($strCarrierCode)
    {
        TNTOfficiel_Logstack::log();

        // Load current TNT carrier object.
        $objCarrierTNT = TNTOfficiel_Carrier::loadCurrentCarrier($strCarrierCode);
        // If TNT carrier correctly loaded.
        if ($objCarrierTNT !== null) {
            // Get all users groups associated with the TNT carrier.
            $arrCarrierGroups = $objCarrierTNT->getGroups();
            // If there is currently at least one users groups set.
            if (is_array($arrCarrierGroups) && count($arrCarrierGroups) > 0) {
                // Current users groups set.
                $arrCarrierGroupsSet = array();
                foreach ($arrCarrierGroups as $arrRowCarrierGroup) {
                    // DB request fail. stop here.
                    if (!array_key_exists('id_group', $arrRowCarrierGroup)) {
                        return false;
                    }
                    $arrCarrierGroupsSet[] = (int)$arrRowCarrierGroup['id_group'];
                }
                // Users groups to exclude.
                $arrCarrierGroupsExclude = array(
                    (int)Configuration::get('PS_UNIDENTIFIED_GROUP'),
                    //(int)Configuration::get('PS_GUEST_GROUP')
                );

                // Get groups previously set, minus groups to exclude.
                $arrCarrierGroupsApply = array_diff($arrCarrierGroupsSet, $arrCarrierGroupsExclude);

                // If groups change.
                if (count(array_diff($arrCarrierGroupsSet, $arrCarrierGroupsApply)) > 0) {
                    // Force carrier users groups (delete all, then set).
                    $objCarrierTNT->setGroups($arrCarrierGroupsApply, true);
                }
            }
        }

        return true;
    }

    /**
     * Get the carriers from the middleware (or from cache).
     *
     * @param Cart $objArgCart
     * @param Customer $objArgCustomer
     *
     * @return array|null
     *
     * @throws Exception
     */
    public static function getMDWTNTCarrierList($objArgCart, $objArgCustomer)
    {
        TNTOfficiel_Logstack::log();

        $objContext = Context::getContext();
        // Set context using the checkout step.
        $strRequestContext = 'QUOTE';
        // Step 2 : 04. Delivery.
        if (property_exists($objContext->controller, 'step') && $objContext->controller->step == 2) {
            // Response include 'relay_points' and 'repositories'.
            $strRequestContext = 'CHECKOUT';
        }


        $arrResponse = array();

        // If module not ready (validated account is required).
        if (!TNTOfficiel::isReady()) {
            return $arrResponse;
        }

        // Check if the city match the postcode in the selected delivery address.
        $boolCityPostCodeIsValid = TNTOfficiel_Address::checkPostCodeCityForCart($objArgCart);
        if (!$boolCityPostCodeIsValid) {
            return $arrResponse;
        }

        // A shipping address is required.
        $intAddressID = $objArgCart->id_address_delivery;
        if (empty($intAddressID) || $intAddressID != (int)$intAddressID || !((int)$intAddressID > 0)) {
            return $arrResponse;
        }
        $objAddressShipping = new Address((int)$intAddressID);
        // If shipping address object not available.
        if (!Validate::isLoadedObject($objAddressShipping)) {
            return $arrResponse;
        }

        // Get params (no timestamp).
        $arrParams = TNTOfficiel_Carrier::getCartData(
            $strRequestContext,
            $objArgCart,
            $objAddressShipping,
            $objArgCustomer
        );

        $boolForceDeliveryB2BType = false;
        foreach ($arrParams['quote']['items'] as $item) {
            // If item weight greater than 30 kg.
            if ($item['weight'] > TNTOfficiel_Parcel::MAXWEIGHT_B2B) {
                // Too heavy, no option.
                return;
            }
            // If item weight greater than 20 kg (and less or equal to 30 kg).
            if ($item['weight'] > TNTOfficiel_Parcel::MAXWEIGHT_B2C) {
                // Check only for B2B options.
                $boolForceDeliveryB2BType = true;
            }
        }

        $strCacheKey = TNTOfficielCache::getKeyIdentifier(__CLASS__, __FUNCTION__, $arrParams);
        // Cache 10 min (600 seconds).
        $intTTL = 60*10;
        // Get Middleware Response.
        $arrResponse = TNTOfficiel_JsonRPCClient::request('getCarrierQuote', $arrParams, $strCacheKey, $intTTL);
        // If request fail.
        if (!is_array($arrResponse) || !array_key_exists('products', $arrResponse)) {
            return;
        }

        $arrDeliveryB2BTypeFilter = array('ENTERPRISE', 'DEPOT');
        foreach ($arrResponse['products'] as $key => $arrDeliveryOption) {
            // If we care only of B2B options and current one is not.
            if ($boolForceDeliveryB2BType && !in_array($arrDeliveryOption['type'], $arrDeliveryB2BTypeFilter)) {
                // remove this option.
                unset($arrResponse['products'][$key]);
                // remove commerçant partenaire optional information.
                if (array_key_exists('relay_points', $arrResponse)) {
                    unset($arrResponse['relay_points']);
                }
                continue;
            }

            // Add delivery option carrier code.
            $arrResponse['products'][$key]['id'] = $arrDeliveryOption['code'].'_'.$arrDeliveryOption['type'];

            $arrResponse['products'][$key]['due_date'] = null;
            if ($arrDeliveryOption['due_date']) {
                $arrResponse['products'][$key]['due_date'] = $arrDeliveryOption['due_date'];
            }
        }

        return $arrResponse;
    }

    /**
     * Get all the data needed by the middleware from the cart.
     *
     * @param string $strArgRequestContext
     * @param Cart $objArgCart
     * @param Address $objArgAddressShipping
     * @param Customer $objArgCustomer
     *
     * @return array
     */
    private static function getCartData($strArgRequestContext, $objArgCart, $objArgAddressShipping, $objArgCustomer)
    {
        TNTOfficiel_Logstack::log();

        $objContext = Context::getContext();

        $strCustomerEmail = '';
        $strCustomerDOB = $intCustomerGroupID = $strCustomerGroupName = null;

        // If customer is logged in.
        if (Validate::isLoadedObject($objArgCustomer) && $objArgCustomer->isLogged()) {
            // Customer default group.
            $intGroupID = (int)Customer::getDefaultGroupId((int)$objArgCustomer->id);
            $objGroup = new Group(Configuration::get('PS_CUSTOMER_GROUP'));
            $strGroupName = $objGroup->name[$objContext->language->id];

            $strCustomerEmail = trim($objArgCustomer->email);
            $strCustomerDOB = $objArgCustomer->birthday;
            $intCustomerGroupID = $intGroupID;
            $strCustomerGroupName = trim($strGroupName);
        }


        // total discount
        $fltTotalDiscount = 0;

        // items
        $arrItems = array();
        foreach ($objArgCart->getProducts() as $arrProduct) {
            // reduction amount
            $fltDiscount = Product::getPriceStatic(
                $arrProduct['id_product'],
                false,
                $arrProduct['id_product_attribute'],
                6,
                null,
                true
            );

            // Get a list of attribute name/value from a product and its attribute ID.
            // Note : $arrProduct['attributes'] is a list comma separated,
            // so it is not parsable for attributes using comma in value (e.g.: 1,5 inch).
            $arrOptions = TNTOfficiel_Product::getAttributeCombinationsText(
                $arrProduct['id_product'],
                $arrProduct['id_product_attribute'],
                $objContext->language->id
            );

            $fltTotalDiscount += $fltDiscount;

            $arrItems[] = array(
                'qty' => $arrProduct['cart_quantity'],
                'price' => $arrProduct['price'] + $fltDiscount,
                'row_total' => $arrProduct['price_wt'] * $arrProduct['cart_quantity'],
                'tax' => $arrProduct['cart_quantity'] * $arrProduct['price'] * $arrProduct['rate'] / 100,
                'discount' => $fltDiscount,
                'attributes' => array(),
                'options' => $arrOptions,
                'weight' => $arrProduct['weight'],
            );
        }

        $arrData = array(
            'context' => $strArgRequestContext,
            'store' => $objArgCart->id_shop,
            'merchant' => TNTOfficiel_Credentials::getCredentials(),
            'customer' => array(
                // 'name' is used as the company field from the delivery address.
                // If empty or not, the response include ENTERPRISE or INDIVIDUAL product.
                'name' => trim($objArgAddressShipping->company),
                // Customer email. e.g: jdupont@gmail.com
                // Corresponds to variable {customer.email} in Owebia.
                'email' => $strCustomerEmail,
                // Date of birth, required but unused. e.g: 1986-03-26.
                // Corresponds to variable {customer.dob} in Owebia.
                'dob' => $strCustomerDOB,
                // Customer group ID. e.g: 8.
                'group_id' => $intCustomerGroupID,
                // Customer group name (optional).
                'group_code' => $strCustomerGroupName,
            ),
            'shipping' => array(
                // country_code, region_code & post_code used for 'shipto' property in Owebia rules.
                'country_code' => Country::getIsoById($objArgAddressShipping->id_country),
                'region_code' => Tools::substr(trim($objArgAddressShipping->postcode), 0, 2),
                'post_code' => trim($objArgAddressShipping->postcode),
                'city' => trim($objArgAddressShipping->city),
            ),
            'quote' => array(
                // Used to get variable {cart.weight} and {cart.qty} in Owebia.
                'items' => $arrItems,
                // Corresponds to variable {cart.price} in Owebia.
                'subtotal' => $objArgCart->getOrderTotal(false, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING),
                // Corresponds to variable {cart.tax} in Owebia.
                'tax' => $objArgCart->getOrderTotal(true, Cart::BOTH_WITHOUT_SHIPPING)
                    - $objArgCart->getOrderTotal(false, Cart::BOTH_WITHOUT_SHIPPING),
                // Corresponds to variable {cart.discount} in Owebia.
                'discount' => -($objArgCart->getOrderTotal(false, Cart::BOTH_WITHOUT_SHIPPING)
                    - $objArgCart->getOrderTotal(false, Cart::ONLY_PRODUCTS_WITHOUT_SHIPPING)),
                'coupon_code' => implode('|', TNTOfficiel_Cart::getCartDiscountCodes($objArgCart)),
                // Corresponds to variable {cart.free_shipping} in Owebia.
                'freeshipping' => '',
            ),
        );

        return $arrData;
    }

    /**
     * Check if a carrier ID is a TNTOfficel module one.
     *
     * @param int $intArgCarrierID
     *
     * @return boolean
     */
    public static function isTNTOfficielCarrierID($intArgCarrierID)
    {
        TNTOfficiel_Logstack::log();

        // Carrier ID must be an integer greater than 0.
        if (empty($intArgCarrierID) || $intArgCarrierID != (int)$intArgCarrierID || !((int)$intArgCarrierID > 0)) {
            return false;
        }

        $intCarrierID = (int)$intArgCarrierID;

        $obCarrier = new Carrier($intCarrierID);

        return $obCarrier->external_module_name === TNTOfficiel::MODULE_NAME;
    }
}
