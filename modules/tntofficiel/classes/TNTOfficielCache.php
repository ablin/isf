<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_ . 'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

/**
 * Class TNTOfficielCache
 */
class TNTOfficielCache extends ObjectModel
{
    // Delete entries expired x days ago.
    const TIME_CLEAN_EXPIRED = 60*60*24*2;
    // Delete if at least x expired entries.
    const TIME_CLEAN_ATLEAST = 1024;

    // id_tntofficiel_cache
    public $id;

    public $str_key;
    public $str_value;
    public $int_ts;

    public static $definition = array(
        'table' => 'tntofficiel_cache',
        'primary' => 'id_tntofficiel_cache',
        'fields' => array(
            'str_key' => array(
                'type' => ObjectModel::TYPE_STRING,
                'size' => 255,
                'required' => true
            ),
            'str_value' => array(
                'type' => ObjectModel::TYPE_STRING
                /*, 'validate' => 'isSerializedArray', 'size' => 65000*/
            ),
            'int_ts' => array(
                'type' => ObjectModel::TYPE_INT
            ),
        ),
    );

    // cache and prevent race condition.
    private static $arrLoadedEntities = array();

    /**
     * Constructor.
     */
    public function __construct($intArgId = null, $intArgLangId = null, $intArgShopId = null)
    {
        TNTOfficiel_Logstack::log();

        parent::__construct($intArgId, $intArgLangId, $intArgShopId);
    }

    /**
     * Load existing object model or optionally create a new one for it's ID.
     *
     * @param $strArgKey
     * @param bool $boolArgCreate
     * @param null $intArgLangID
     * @param null $intArgShopID
     *
     * @return mixed|null|TNTOfficielCache
     *
     * @throws PrestaShopDatabaseException
     */
    public static function loadCacheKey($strArgKey, $boolArgCreate = false, $intArgLangID = null, $intArgShopID = null)
    {
        TNTOfficiel_Logstack::log();

        $strKey = (string)$strArgKey;

        $strEntityID = '_' . $strKey . '-' . (int)$intArgLangID . '-' . (int)$intArgShopID;

        // No new cache Key.
        if (preg_match('/^[0-9a-z_:]+$/ui', $strKey) !== 1) {
            return null;
        }

        if (array_key_exists($strEntityID, TNTOfficielCache::$arrLoadedEntities)) {
            $objTNTCacheModel = TNTOfficielCache::$arrLoadedEntities[$strEntityID];
            // Check.
            if ((string)$objTNTCacheModel->str_key === $strKey && Validate::isLoadedObject($objTNTCacheModel)) {
                return $objTNTCacheModel;
            }
        }

        // Search row for cache ID.
        $objDbQuery = new DbQuery();
        $objDbQuery->select('*');
        $objDbQuery->from(TNTOfficielCache::$definition['table']);
        $objDbQuery->where('str_key = \''.pSQL($strKey).'\'');

        $objDB = Db::getInstance();
        $arrDBResult = $objDB->executeS($objDbQuery);

        // If row found and match cache ID.
        if (is_array($arrDBResult) && count($arrDBResult) === 1
            && $strKey === (string)$arrDBResult[0]['str_key']
        ) {
            // Load existing TNT cache entry.
            $objTNTCacheModel = new TNTOfficielCache(
                (int)$arrDBResult[0]['id_tntofficiel_cache'],
                $intArgLangID,
                $intArgShopID
            );
        } elseif ($boolArgCreate === true) {
            // Clean.
            TNTOfficielCache::clean();

            // Create a new TNT cache entry.
            $objTNTCacheModel = new TNTOfficielCache(null, $intArgLangID, $intArgShopID);
            $objTNTCacheModel->str_key = $strKey;
            $objTNTCacheModel->int_ts = time();
            $objTNTCacheModel->save();

            // Reload to get default DB values after creation.
            $objTNTCacheModel = TNTOfficielCache::loadCacheKey($strKey, false, $intArgLangID, $intArgShopID);
        } else {
            //$objException = new Exception('TNTOfficielCache data not found for ID #' . $strKey);
            //TNTOfficiel_Logger::logException($objException);

            return null;
        }

        // Check.
        if ((string)$objTNTCacheModel->str_key !== $strKey || !Validate::isLoadedObject($objTNTCacheModel)) {
            return null;
        }

        TNTOfficielCache::$arrLoadedEntities[$strEntityID] = $objTNTCacheModel;

        return $objTNTCacheModel;
    }

    /**
     * Delete expired entries.
     *
     * @return int
     */
    public static function clean()
    {
        TNTOfficiel_Logstack::log();

        // Search for expired row.
        $objDbQuery = new DbQuery();
        $objDbQuery->select('COUNT(*) as `expired`');
        $objDbQuery->from(TNTOfficielCache::$definition['table']);
        $objDbQuery->where('int_ts < '.(time()-TNTOfficielCache::TIME_CLEAN_EXPIRED));

        $objDB = Db::getInstance();
        $arrDBResult = $objDB->executeS($objDbQuery);

        $intExpired = 0;
        if (is_array($arrDBResult) && count($arrDBResult) === 1) {
            $intExpired = (int)$arrDBResult[0]['expired'];
            if ($intExpired > TNTOfficielCache::TIME_CLEAN_ATLEAST) {
                Db::getInstance()->delete(
                    TNTOfficielCache::$definition['table'],
                    'int_ts < '.(time()-TNTOfficielCache::TIME_CLEAN_EXPIRED)
                );
            }
        }

        return $intExpired;
    }

    /**
     * Delete all entries (truncate like).
     *
     * @return mixed
     */
    public static function truncate()
    {
        TNTOfficiel_Logstack::log();

        return Db::getInstance()->delete(TNTOfficielCache::$definition['table'], 'int_ts > 0');
    }

    /**
     * Check if a valid entry exist.
     *
     * @param string $strArgKey
     *
     * @return bool|null true if available, false if unavailable, null if unable to load entry.
     */
    public static function isStored($strArgKey)
    {
        $objCache = TNTOfficielCache::loadCacheKey($strArgKey);

        if ($objCache === null) {
            return null;
        }

        if ($objCache->int_ts > time()) {
            return true;
        }

        return false;
    }

    /**
     * Store an entry.
     *
     * @param string $strArgKey
     * @param mixed $mxdArgValue
     * @param int $intArgTTL
     *
     * @return bool|null true if stored, false if not stored, null if unable to create entry.
     */
    public static function store($strArgKey, $mxdArgValue, $intArgTTL)
    {
        // No data, no store.
        if ($mxdArgValue === null) {
            return false;
        }

        // TTL must be an integer greater than 0.
        if ($intArgTTL !== (int)$intArgTTL
            || $intArgTTL <= 0
        ) {
            return false;
        }

        $objCache = TNTOfficielCache::loadCacheKey($strArgKey, true);

        if ($objCache === null) {
            return null;
        }

        $objCache->str_value = serialize($mxdArgValue);
        $objCache->int_ts = time() + $intArgTTL;

        return $objCache->save();
    }

    /**
     * Get an entry.
     *
     * @param string $strArgKey
     *
     * @return mixed|null null if no data available.
     */
    public static function retrieve($strArgKey)
    {
        $objCache = TNTOfficielCache::loadCacheKey($strArgKey);

        if ($objCache === null) {
            return null;
        }

        if ($objCache->int_ts > time()) {
            return Tools::unSerialize($objCache->str_value, true);
        }

        return null;
    }

    /**
     * Get a str_key identifier.
     *
     * @param string $strArgFQClassName
     * @param string $strFunctionName
     * @param array $arrArgParameters
     *
     * @return string
     */
    public static function getKeyIdentifier($strArgFQClassName, $strFunctionName, $arrArgParameters)
    {
        $strCNLS = strrchr($strArgFQClassName, '\\');
        $strUQCN = ($strCNLS === false ? $strArgFQClassName : Tools::substr($strCNLS, 1));

        $strCacheKey = $strUQCN.'::'.$strFunctionName.'_'.sha1(serialize($arrArgParameters));

        return $strCacheKey;
    }

    /**
     * Gets the number of seconds between the current time and next midnight.
     *
     * @return int
     */
    public static function getSecondsUntilMidnight()
    {
        $intTSNow = time();
        $intTSMidnight = strtotime('tomorrow midnight');
        $intSecondsUntilMidnight = $intTSMidnight - $intTSNow;

        return $intSecondsUntilMidnight;
    }



    /**
     * @return bool
     */
    private static function getPSCacheEnabled()
    {
        return (defined('_PS_CACHE_ENABLED_') ? !!_PS_CACHE_ENABLED_ : false);
    }
}
