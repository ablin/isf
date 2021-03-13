<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 *
 * This file include modified source from https://github.com/fguillot/JsonRPC/ with methods getResult() and request()
 * License   The MIT License (MIT)
 * Copyright (c) 2015 Frederic Guillot
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_JsonRPCClient
{
    // JSON RPC URL.
    const URL_RPC = 'https://solutions-ecommerce.tnt.fr/api/handler';

    private static $arrRequestTimeoutPerServices = array(
        'isCorrectAuthentication' => 0.3,
        'getAccountInfos' => 0.3,
        'getCities' => 0.3,
        'testCity' => 0.3,
        'getCarrierQuote' => 2,
        'getRepositories' => 0.3,
        'getRelayPoints' => 0.3,
        'getShippingDate' => 2,
        'checkSaveShipment' => 4,
        'saveShipment' => 8,
    );


    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * @param $strArgService
     *
     * @return int
     */
    public static function getRequestTimeout($strArgService)
    {
        $intRequestTimeout = TNTOfficiel::REQUEST_TIMEOUT;
        if (array_key_exists($strArgService, TNTOfficiel_JsonRPCClient::$arrRequestTimeoutPerServices)) {
            $intRequestTimeout = (int)ceil(1+TNTOfficiel_JsonRPCClient::$arrRequestTimeoutPerServices[$strArgService]*4);
        }

        return $intRequestTimeout;
    }

    /**
     * Get a RPC call result
     *
     * @param array $arrArgPayload
     *
     * @return mixed
     */
    private static function getResult(array $arrArgPayload)
    {
        TNTOfficiel_Logstack::log();

        if (isset($arrArgPayload['error']['code'])) {
            switch ($arrArgPayload['error']['code']) {
                case -32601:
                    throw new BadFunctionCallException(
                        'Procedure not found: '. $arrArgPayload['error']['message']
                    );
                case -32602:
                    throw new InvalidArgumentException(
                        'Invalid arguments: '. $arrArgPayload['error']['message']
                    );
                default:
                    throw new RuntimeException(
                        'Invalid request/response: '. $arrArgPayload['error']['message'],
                        $arrArgPayload['error']['code']
                    );
            }
        }

        return isset($arrArgPayload['result']) ? $arrArgPayload['result'] : null;
    }

    /**
     * Request a TNT JSON-RPC service (Middleware).
     *
     * @param string $strArgService
     * @param array $arrArgParams
     * @param string|null $strCacheKey
     * @param int $intArgTTL
     *
     * @return array|string|null
     */
    public static function request($strArgService, $arrArgParams = array(), $strCacheKey = null, $intArgTTL = 0)
    {
        TNTOfficiel_Logstack::log();

        $arrJRPCResponse = null;

        $strAccountNumber = 'N/A';
        if (array_key_exists('merchant', $arrArgParams)
            && array_key_exists('merchant_number', $arrArgParams['merchant'])
        ) {
            $strAccountNumber = $arrArgParams['merchant']['merchant_number'];
        }

        $fltRequestTimeStart = microtime(true);

        // Check if already in cache.
        if (TNTOfficielCache::isStored($strCacheKey)) {
            $arrJRPCResponse = TNTOfficielCache::retrieve($strCacheKey);
        } else {
            try {
                // Update if needed.
                TNTOfficiel_Tools::updateCACert();

                /*
                 * Prepare the payload.
                 */

                $arrPayload = array(
                    'jsonrpc' => '2.0',
                    'method' => $strArgService,
                    'id' => mt_rand()
                );

                // Always adding date timestamp.
                $arrArgParams['date'] = time();

                if (!empty($arrArgParams)) {
                    $arrPayload['params'] = $arrArgParams;
                }

                /*
                 * cURL.
                 */

                $intRequestTimeout = TNTOfficiel_JsonRPCClient::getRequestTimeout($strArgService);

                $fltRequestTimeStart = microtime(true);
                $arrResult = TNTOfficiel_Tools::cURLRequest(
                    TNTOfficiel_JsonRPCClient::URL_RPC,
                    array(
                        CURLOPT_HTTPHEADER => array(
                            'User-Agent: PHP/cURL',
                            'Content-Type: application/json',
                            'Accept: application/json',
                            'Connection: close',
                        ),
                        CURLOPT_POST => true,
                        CURLOPT_POSTFIELDS => Tools::jsonEncode($arrPayload),
                        CURLOPT_TIMEOUT => $intRequestTimeout
                    )
                );
                $fltRequestTimeEnd = microtime(true);

                $intCURLHTTPCode = $arrResult['info']['http_code'];
                $strCURLError = ($arrResult['response'] === false) ? $arrResult['error'] : '';

                if ($arrResult['response'] === false) {
                    throw new Exception(
                        sprintf('cURL Error (%s): %s', $intCURLHTTPCode, $strCURLError)
                    );
                }

                $arrResponse = Tools::jsonDecode($arrResult['response'], true);

                TNTOfficiel_Logstack::dump(array(
                    'URL' => TNTOfficiel_JsonRPCClient::URL_RPC,
                    'Service' => $strArgService,
                    'Request' => $arrPayload,
                    'Code' => $intCURLHTTPCode,
                    'Error' => $strCURLError,
                    'Response' => $arrResponse,
                    //'responseRaw' => $arrResult['response']
                ));

                if (!$arrResponse) {
                    throw new Exception(
                        sprintf('cURL: Unable to parse JSON response (%s)', strtok($arrResult['response'], "\n"))
                    );
                }

                /*
                 * Parse the response and return the procedure result.
                 */

                $arrResponse = is_array($arrResponse) ? $arrResponse : array();

                // if we have a batch response.
                if (array_keys($arrResponse) === range(0, count($arrResponse) - 1)) {
                    $arrJRPCResponse = array();
                    foreach ($arrResponse as $response) {
                        $arrJRPCResponse[] = TNTOfficiel_JsonRPCClient::getResult($response);
                    }
                } else {
                    $arrJRPCResponse = TNTOfficiel_JsonRPCClient::getResult($arrResponse);
                }

                // Log success.
                TNTOfficiel_Logger::logRequest(
                    'JSON',
                    $strArgService,
                    true,
                    ($fltRequestTimeEnd - $fltRequestTimeStart),
                    $strAccountNumber
                );
                // Cache.
                if (is_string($strCacheKey)) {
                    TNTOfficielCache::store($strCacheKey, $arrJRPCResponse, $intArgTTL);
                }
            } catch (Exception $objException) {
                $fltRequestTimeEnd = microtime(true);
                // Log error.
                TNTOfficiel_Logger::logRequest(
                    'JSON',
                    $strArgService,
                    false,
                    ($fltRequestTimeEnd - $fltRequestTimeStart),
                    $strAccountNumber.' '
                    .'Error '.$objException->getCode().': '
                    .$objException->getMessage()
                );

                return null;
            }
        }

        return $arrJRPCResponse;
    }


    /**
     * Check credential validity with authentication.
     * No cache store.
     *
     * @return bool|null
     *
     * @throws Exception
     */
    public static function isCorrectAuthentication()
    {
        TNTOfficiel_Logstack::log();

        // Request parameters.
        $arrParams = array(
            'merchant' => TNTOfficiel_Credentials::getCredentials()
        );
        // Get Middleware Response.
        $arrJRPCResponse = TNTOfficiel_JsonRPCClient::request('isCorrectAuthentication', $arrParams);

        // If request fail.
        if ($arrJRPCResponse === null) {
            return null;
        } elseif (!array_key_exists('response', $arrJRPCResponse)) {
            return false;
        }

        return (bool)$arrJRPCResponse['response'];
    }

    /**
     * Get account info.
     * No cache store.
     *
     * @return array|false|null
     *
     * @throws Exception
     */
    public static function getAccountInfos()
    {
        TNTOfficiel_Logstack::log();

        // Request parameters.
        $arrParams = array(
            'merchant' => TNTOfficiel_Credentials::getCredentials()
        );
        // Get Middleware Response.
        $arrJRPCResponse = TNTOfficiel_JsonRPCClient::request('getAccountInfos', $arrParams);

        // If request fail.
        if ($arrJRPCResponse === null) {
            return null;
        } elseif (!array_key_exists('response', $arrJRPCResponse)) {
            return false;
        }

        return $arrJRPCResponse['response'];
    }

    /**
     * Get cities for the given postal code.
     * Store the result in cache until midnight.
     *
     * @param string $strArgPostCode the postal code
     * @param int $intArgShopID the shop ID.
     *
     * @return array
     */
    public static function getCities($strArgPostCode, $intArgShopID)
    {
        TNTOfficiel_Logstack::log();

        // Request parameters.
        $arrParams = array(
            'store' => $intArgShopID,
            'merchant' => TNTOfficiel_Credentials::getCredentials(),
            'postcode' => trim($strArgPostCode),
        );
        // Cache parameters.
        $strCacheKey = TNTOfficielCache::getKeyIdentifier(__CLASS__, __FUNCTION__, $arrParams);
        $intTTL = 60*60*24*1;
        // Get Middleware Response.
        $arrJRPCResponse = TNTOfficiel_JsonRPCClient::request('getCities', $arrParams, $strCacheKey, $intTTL);

        if (!array_key_exists('cities', $arrJRPCResponse)
            || !is_array($arrJRPCResponse['cities'])
        ) {
            return array();
        }

        return $arrJRPCResponse['cities'];
    }

    /**
     * Check if the city match the postcode.
     * Store the result in cache until midnight.
     *
     * @param string $strArgPostCode
     * @param string $strArgCity
     * @param int $intArgShopID
     *
     * @return bool
     */
    public static function testCity($strArgPostCode, $strArgCity, $intArgShopID)
    {
        TNTOfficiel_Logstack::log();

        // Request parameters.
        $arrParams = array(
            'store' => $intArgShopID,
            'merchant' => TNTOfficiel_Credentials::getCredentials(),
            'postcode' => trim($strArgPostCode),
            'city' => trim($strArgCity)
        );
        // Cache parameters.
        $strCacheKey = TNTOfficielCache::getKeyIdentifier(__CLASS__, __FUNCTION__, $arrParams);
        $intTTL = 60*60*24*1;
        // Get Middleware Response.
        $arrJRPCResponse = TNTOfficiel_JsonRPCClient::request('testCity', $arrParams, $strCacheKey, $intTTL);

        // If communication error, TNT carrier are not available,
        // but postcode/city is considered wrong and then show error "unknow postcode" on Front-Office checkout.
        // Also, return true to prevent always invalid address form.
        if ($arrJRPCResponse === null) {
            return true;
        }
        if (!array_key_exists('response', $arrJRPCResponse)) {
            return false;
        }

        return (bool)$arrJRPCResponse['response'];
    }

    /**
     * Get the relay points for the given postcode/city from the middleware or from the cache.
     * Store the result in cache until midnight.
     *
     * @param string $strArgPostCode
     * @param string $strArgCity
     * @param int $intArgShopID
     * @param string|null $strArgDPL
     *
     * @return array
     */
    public static function getRelayPoints($strArgPostCode, $strArgCity, $intArgShopID, $strArgDPL = null)
    {
        TNTOfficiel_Logstack::log();

        // Request parameters.
        $arrParams = array(
            'store' => $intArgShopID,
            'merchant' => TNTOfficiel_Credentials::getCredentials(),
            'postcode' => trim($strArgPostCode),
            'city' => trim($strArgCity),
        );
        if ($strArgDPL) {
            $arrParams['dpl'] = $strArgDPL;
        }
        // Cache parameters.
        $strCacheKey = TNTOfficielCache::getKeyIdentifier(__CLASS__, __FUNCTION__, $arrParams);
        $intTTL = TNTOfficielCache::getSecondsUntilMidnight();
        // Get Middleware Response.
        $arrJRPCResponse = TNTOfficiel_JsonRPCClient::request('getRelayPoints', $arrParams, $strCacheKey, $intTTL);
        // If request fail.
        if ($arrJRPCResponse === null) {
            return array();
        }

        // If no RelayPoints
        if ($arrJRPCResponse['relay_points'] === false) {
            $arrJRPCResponse['relay_points'] = array();
        }

        // Remove disabled Relay Points.
        // Preserve &$v reference for working with array_splice.
        foreach ($arrJRPCResponse['relay_points'] as $intItemIdx => &$arrItemRP) {
            // Default.
            if (!array_key_exists('enabled', $arrItemRP)) {
                $arrItemRP['enabled'] = true;
                continue;
            }
            if (array_key_exists('enabled', $arrItemRP) && $arrItemRP['enabled'] !== true) {
                array_splice($arrJRPCResponse['relay_points'], $intItemIdx, 1, array());
            }
        }
        unset($arrItemRP);


        // Date Today.
        $objDateTimeToday = new DateTime('midnight', new DateTimeZone('UTC'));
        // Date In one Month.
        $objDateTime1Month = new DateTime('midnight +1 month', new DateTimeZone('UTC'));

        // Check Closing an ReOpening Date, then format.
        foreach ($arrJRPCResponse['relay_points'] as $intItemIdx => &$arrItemRP) {
            // Default.
            if (!array_key_exists('closing', $arrItemRP)) {
                $arrItemRP['closing'] = null;
            }
            if (!array_key_exists('reopening', $arrItemRP)) {
                $arrItemRP['reopening'] = null;
            }

            // Closing Date exist.
            if ($arrItemRP['closing'] !== null) {
                try {
                    $objRPDateTimeClosing = new DateTime('@'.$arrItemRP['closing']);
                } catch (Exception $objException) {
                    $objRPDateTimeClosing = false;
                }
                // Check Closing Date Validity.
                // et si la date de fermeture n'est pas expirée,
                // et qu'elle n'est pas plus tard que dans un mois.
                if (is_object($objRPDateTimeClosing)
                    && $objRPDateTimeClosing->format('U') === $arrItemRP['closing']
                    && $objRPDateTimeClosing > $objDateTimeToday
                    && $objRPDateTimeClosing <= $objDateTime1Month
                ) {
                    $arrItemRP['closing'] = $objRPDateTimeClosing->format('d/m/Y');
                } else {
                    $arrItemRP['closing'] = null;
                }
            }

            // ReOpening Date exist.
            if ($arrItemRP['reopening'] !== null) {
                try {
                    $objRPDateTimeReOpening = new DateTime('@'.$arrItemRP['reopening']);
                } catch (Exception $objException) {
                    $objRPDateTimeReOpening = false;
                }
                // Check ReOpening Date Validity.
                // et si la date de réouverture n'est pas expirée,
                if (is_object($objRPDateTimeReOpening)
                    && $objRPDateTimeReOpening->format('U') === $arrItemRP['reopening']
                    && $objRPDateTimeReOpening > $objDateTimeToday
                ) {
                    $arrItemRP['reopening'] = $objRPDateTimeReOpening->format('d/m/Y');
                } else {
                    $arrItemRP['reopening'] = null;
                }
            }
        }
        unset($arrItemRP);

        return $arrJRPCResponse['relay_points'];
    }

    /**
     * Get the repositories for the given postcode from the middleware or from the cache.
     * Store the result in cache until midnight.
     *
     * @param string $strArgPostCode
     * @param int $intArgShopID
     *
     * @return array
     */
    public static function getRepositories($strArgPostCode, $intArgShopID)
    {
        TNTOfficiel_Logstack::log();

        // Request parameters.
        $arrParams = array(
            'store' => $intArgShopID,
            'merchant' => TNTOfficiel_Credentials::getCredentials(),
            'postcode' => trim($strArgPostCode),
            'city' => null,
        );
        // Cache parameters.
        $strCacheKey = TNTOfficielCache::getKeyIdentifier(__CLASS__, __FUNCTION__, $arrParams);
        $intTTL = TNTOfficielCache::getSecondsUntilMidnight();
        // Get Middleware Response.
        $arrJRPCResponse = TNTOfficiel_JsonRPCClient::request('getRepositories', $arrParams, $strCacheKey, $intTTL);
        // If request fail.
        if ($arrJRPCResponse === null) {
            return array();
        }

        // If no Repositories.
        if ($arrJRPCResponse['repositories'] === false) {
            $arrJRPCResponse['repositories'] = array();
        }

        return $arrJRPCResponse['repositories'];
    }
}
