<?php
/**
 * TNT OFFICIAL MODULE FOR PRESTASHOP.
 *
 * @author    GFI Informatique <www.gfi.world>
 * @copyright 2016-2020 GFI Informatique, 2016-2020 TNT
 * @license   https://opensource.org/licenses/MIT MIT License
 */

require_once _PS_MODULE_DIR_.'tntofficiel/libraries/TNTOfficiel_ClassLoader.php';

class TNTOfficiel_SoapClient
{
    const URL_OASIS_ROOT = 'http://docs.oasis-open.org/wss/2004/01/';
    // SOAP WSDL URL.
    const URL_WSDL = 'https://www.tnt.fr/service/?wsdl';

    /**
     * Prevent Construct.
     */
    final private function __construct()
    {
        trigger_error(sprintf('%s() %s is static.', __FUNCTION__, get_class($this)), E_USER_ERROR);
    }

    /**
     * @param $strArgUserName
     * @param $strArgPassword
     * @param string $strArgPasswordType
     *
     * @return SoapHeader
     */
    public static function getHeader($strArgUserName, $strArgPassword, $strArgPasswordType = 'PasswordDigest')
    {
        TNTOfficiel_Logstack::log();

        $strURLOASISROOT = TNTOfficiel_SoapClient::URL_OASIS_ROOT;
        $strElementCreated = '';

        $intRand = mt_rand();
        if ($strArgPasswordType !== 'PasswordDigest') {
            $strArgPasswordType = 'PasswordText';
            $strNonce = sha1($intRand);
        } else {
            $strTimestamp = gmdate('Y-m-d\TH:i:s\Z');
            $strArgPassword = base64_encode(pack('H*', sha1(
                pack('H*', $intRand).
                pack('a*', $strTimestamp).
                pack('a*', $strArgPassword)
            )));
            $strNonce = base64_encode(pack('H*', $intRand));

            $strElementCreated = <<<XML
<wsu:Created xmlns:wsu="${strURLOASISROOT}oasis-200401-wss-wssecurity-utility-1.0.xsd">${strTimestamp}</wsu:Created>
XML;
        }

        $strArgUserName = htmlspecialchars($strArgUserName);
        $strArgPassword = htmlspecialchars($strArgPassword);

        $strXMLSecurityHeader = <<<XML
<wsse:Security SOAP-ENV:mustUnderstand="1" xmlns:wsse="${strURLOASISROOT}oasis-200401-wss-wssecurity-secext-1.0.xsd">
  <wsse:UsernameToken>
    <wsse:Username>${strArgUserName}</wsse:Username>
    <wsse:Password Type="${strURLOASISROOT}oasis-200401-wss-username-token-profile-1.0#${strArgPasswordType}"
    >${strArgPassword}</wsse:Password>
    <wsse:Nonce EncodingType="${strURLOASISROOT}oasis-200401-wss-soap-message-security-1.0#Base64Binary"
    >${strNonce}</wsse:Nonce>
    ${strElementCreated}
  </wsse:UsernameToken>
</wsse:Security>
XML;

        $objSoapHeader = new SoapHeader(
            $strURLOASISROOT.'oasis-200401-wss-wssecurity-secext-1.0.xsd',
            'Security',
            new SoapVar($strXMLSecurityHeader, XSD_ANYXML)
        );
        //$objSoapHeader->mustUnderstand = true;

        return $objSoapHeader;
    }

    /**
     * Request a TNT SOAP service.
     *
     * @param string $strArgService
     * @param array $arrArgParams
     * @param string|null $strCacheKey
     * @param int $intArgTTL
     *
     * @return stdClass SOAP Response, null for Communication Error, false for Authentication Error, string for Webservice Error Message.
     *
     * @throws Exception
     */
    private static function request($strArgService, $arrArgParams = array(), $strCacheKey = null, $intArgTTL = 0)
    {
        TNTOfficiel_Logstack::log();

        $arrTNTCredentials = TNTOfficiel_Credentials::getCredentials();

        $objSoapClient = null;
        $objStdClassResponseSOAP = null;
        $objException = null;

        $fltRequestTimeStart = microtime(true);

        // Check if already in cache.
        if (TNTOfficielCache::isStored($strCacheKey)) {
            $objStdClassResponseSOAP = TNTOfficielCache::retrieve($strCacheKey);
        } else {
            try {
                // Check extension.
                if (!extension_loaded('soap')) {
                    throw new Exception(sprintf('PHP SOAP extension is required'));
                }

                // Update if needed.
                TNTOfficiel_Tools::updateCACert();

                // Set expiration timeout (in seconds).
                ini_set('default_socket_timeout', TNTOfficiel::REQUEST_TIMEOUT);

                $objSoapClient = new SoapClient(
                    TNTOfficiel_SoapClient::URL_WSDL,
                    array(
                        'soap_version' => SOAP_1_1,
                        //'cache_wsdl' => WSDL_CACHE_NONE,
                        'trace' => true,
                        // Throw exceptions on error ?
                        //'exceptions' => 1,
                        // Compress request and response.
                        //'compression' => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
                        'stream_context' => stream_context_create(array(
                            // Apply for HTTPS and FTPS.
                            'ssl' => array(
                                // Path to Certificate Authority (CA) bundle.
                                'cafile' => _PS_MODULE_DIR_.TNTOfficiel::MODULE_NAME.'/libraries/certs/cacert.pem',
                                // Check server peer's certificate authenticity through certification authority (CA) for SSL/TLS.
                                'verify_peer' => true,
                                // Check server certificate's name against host. PHP 5.6.0+
                                //'verify_peer_name' => false,
                            ),
                            //'http' => array(),
                            //'https' => array(
                                //'timeout' => 1,
                                // Force IPV4.
                                //'socket' => array(
                                    //'bindto' => '0:0' // PHP 5.1.0+
                                //)
                            //)
                        )),
                        // Proxy.
                        //'proxy_host' => '<HOST>',
                        //'proxy_port' => 80,
                        //'proxy_login' => null,
                        //'proxy_password' => null,
                        // Set connection timeout (in seconds).
                        'connection_timeout' => TNTOfficiel::REQUEST_CONNECTTIMEOUT,
                        'user_agent' => 'PHP/SOAP',
                    )
                );

                // Add WS-Security Header
                $objSoapClient->__setSOAPHeaders(TNTOfficiel_SoapClient::getHeader(
                    $arrTNTCredentials['identity'],
                    $arrTNTCredentials['password'],
                    'PasswordDigest'
                ));

                // Call.
                $fltRequestTimeStart = microtime(true);
                $objStdClassResponseSOAP = $objSoapClient->__soapCall($strArgService, array($arrArgParams));
                $fltRequestTimeEnd = microtime(true);

                // Log success.
                TNTOfficiel_Logger::logRequest(
                    'SOAP',
                    $strArgService,
                    true,
                    ($fltRequestTimeEnd - $fltRequestTimeStart),
                    $arrTNTCredentials['merchant_number']
                );

                // Cache.
                if (is_string($strCacheKey)) {
                    TNTOfficielCache::store($strCacheKey, $objStdClassResponseSOAP, $intArgTTL);
                }
            } catch (Exception $objException) {
                $fltRequestTimeEnd = microtime(true);
                // Log error.
                TNTOfficiel_Logger::logRequest(
                    'SOAP',
                    $strArgService,
                    false,
                    ($fltRequestTimeEnd - $fltRequestTimeStart),
                    $arrTNTCredentials['merchant_number'].' '
                    .((get_class($objException) === 'SoapFault') ? '['.$objException->faultcode .'] ' : '')
                    .'Error '.$objException->getCode().': '
                    .$objException->getMessage()
                );
            }

            ini_restore('default_socket_timeout');

            TNTOfficiel_Logstack::dump(array(
                'URL' => TNTOfficiel_SoapClient::URL_WSDL,
                'Service' => $strArgService,
                'Parameters' => $arrArgParams,
                'Response' => $objStdClassResponseSOAP,
                'ResponseTime' => $fltRequestTimeEnd - $fltRequestTimeStart,
                'SoapFault' => $objException,
                'LastRequestHeaders' => $objSoapClient ? $objSoapClient->__getLastRequestHeaders() : null,
                'LastRequest' => $objSoapClient ? $objSoapClient->__getLastRequest() : null,
                'LastResponseHeaders' => $objSoapClient ? $objSoapClient->__getLastResponseHeaders() : null,
                'LastResponse' => $objSoapClient ? $objSoapClient->__getLastResponse() : null
            ));
        }

        if ($objException !== null) {
            // SoapFault Exception.
            if (get_class($objException) === 'SoapFault') {
                if ($objException->faultcode === 'ns1:FailedAuthentication'
                    || trim($objException->getMessage()) === 'The field \'accountNumber\' is not valid.'
                    || preg_match('/^[0-9]{8}$/ui', $arrTNTCredentials['merchant_number']) !== 1
                ) {
                    // Authentication Error.
                    return false;
                } elseif ($objException->faultcode === 'WSDL') {
                    // Communication Error (Connection Timeout).
                    return null;
                }
                // Webservice Error.
                return trim(preg_replace('/[[:cntrl:]]+/', ' ', $objException->getMessage()));
            }
            // Communication Error.
            return null;
        }

        return $objStdClassResponseSOAP;
    }

    /**
     * @param $strArgParcelNumber
     * @return bool|array
     */
    public static function trackingByConsignment($strArgParcelNumber)
    {
        TNTOfficiel_Logstack::log();

        $arrParamRequest = array('parcelNumber' => trim($strArgParcelNumber));

        $objStdClassResponse = TNTOfficiel_SoapClient::request('trackingByConsignment', $arrParamRequest);

        return $objStdClassResponse;
    }
}
